<?php

namespace App\Services;

use DB;
use Illuminate\Database\Events\QueryExecuted;

/**
 * Hooks into DB::beforeExecuting() (fires just before a query runs) and
 * DB::listen() (fires just after) to build INSERT/UPDATE/DELETE audit
 * rows automatically, without any existing controller being touched.
 *
 * ============================================================
 * HONEST LIMITATIONS - READ BEFORE RELYING ON OLD/NEW DATA
 * ============================================================
 * A query listener only ever sees the final SQL string and its
 * positional (?) bindings. It has NO access to the row's previous
 * state unless we go and fetch it ourselves, before the query runs.
 *
 * That pre-fetch is only attempted when we can confidently identify a
 * single target table and a numeric primary key from a WHERE clause
 * that is exactly `WHERE id = ?` (optionally backtick-quoted). This
 * covers the overwhelming majority of this app's existing
 * `table::x()->where('id', $id)->update([...])` /
 * `->delete()` calls.
 *
 * For anything more complex - multiple WHERE conditions, joins,
 * subqueries, a WHERE on some column other than id - we deliberately
 * do NOT guess. The query is still logged (table, action, new values,
 * bindings), but old_data/changed_fields are left null rather than
 * fabricated. This is intentional per the "never guess old values"
 * requirement, not a bug.
 *
 * For INSERT: "old data" doesn't exist by definition. The
 * auto-increment id is only ever returned to whoever called
 * insertGetId() - a query listener has no way to intercept that return
 * value, so record_id stays null for plain insert() calls. This is a
 * hard limitation of query-listening as a technique, not something any
 * amount of regex parsing can fix.
 * ============================================================
 */
class QueryAuditor
{
    /** @var array<int, array|null> FIFO queue of pre-fetched old rows, one slot per query in flight. */
    protected static array $pending = [];

    public static function beforeExecuting(string $sql, array $bindings, $connection): void
    {
        $table = self::extractTable($sql);

        if (!self::shouldTrack($table)) {
            self::$pending[] = null;
            return;
        }

        $old = null;

        if (self::isUpdateOrDelete($sql)) {
            $id = self::extractSimpleIdCondition($sql, $bindings);

            if ($id !== null) {
                try {
                    $row = DB::connection($connection->getName())
                        ->table($table)
                        ->where('id', $id)
                        ->first();

                    $old = $row ? (array) $row : null;
                } catch (\Throwable $e) {
                    $old = null;
                }
            }
        }

        self::$pending[] = $old;
    }

    public static function afterExecuting(QueryExecuted $event): void
    {
        // Always shift, even when we're about to bail out below, so the
        // FIFO queue never grows unbounded or gets out of sync with
        // beforeExecuting() for later queries in the same request.
        $old = array_shift(self::$pending);

        $sql = $event->sql;
        $bindings = $event->bindings;
        $table = self::extractTable($sql);

        if (!self::shouldTrack($table)) {
            return;
        }

        if (self::isInsert($sql)) {
            self::recordInsert($table, $sql, $bindings);
        } elseif (self::isUpdate($sql)) {
            self::recordUpdate($table, $sql, $bindings, $old);
        } elseif (self::isDelete($sql)) {
            self::recordDelete($table, $sql, $bindings, $old);
        }
    }

    protected static function recordInsert(string $table, string $sql, array $bindings): void
    {
        $columns = self::extractInsertColumns($sql);

        if (!$columns) {
            AuditService::log([
                'action'      => 'create',
                'severity'    => 'success',
                'category'    => AuditService::category($table),
                'module'      => AuditService::category($table),
                'table_name'  => $table,
                'description' => "Created a new record in {$table} (column names unavailable)",
            ]);
            return;
        }

        $columnCount = count($columns);
        $totalBindings = count($bindings);

        // FIX: batch inserts (e.g. table::x()->insert([$row1, $row2, ...])
        // - used routinely in this app, e.g. all 7 weekly_shifts rows per
        // schedule are written in ONE insert() call) produce a single SQL
        // statement with one VALUES group per row. This used to always
        // zip only the FIRST $columnCount bindings against the column
        // list, silently dropping every row after the first and logging
        // a multi-row insert as if it were a single-row create.
        if ($columnCount === 0 || $totalBindings % $columnCount !== 0) {
            // Doesn't divide evenly - our column-count assumption
            // doesn't hold for this statement (e.g. an
            // ON DUPLICATE KEY UPDATE clause adding its own bindings).
            // Log that an insert happened without guessing at row data.
            AuditService::log([
                'action'      => 'create',
                'severity'    => 'success',
                'category'    => AuditService::category($table),
                'module'      => AuditService::category($table),
                'table_name'  => $table,
                'description' => "Created record(s) in {$table} (row data unavailable - binding count didn't cleanly divide by column count)",
            ]);
            return;
        }

        $rowCount = intdiv($totalBindings, $columnCount);

        if ($rowCount <= 1) {
            AuditService::log([
                'action'      => 'create',
                'severity'    => 'success',
                'category'    => AuditService::category($table),
                'module'      => AuditService::category($table),
                'table_name'  => $table,
                // Not knowable from a query listener for plain insert()
                // calls - see class docblock.
                'record_id'   => null,
                'description' => "Created a new record in {$table}",
                'new_data'    => array_combine($columns, array_slice($bindings, 0, $columnCount)),
            ]);
            return;
        }

        // Multi-row batch insert - one log entry covering every row (not
        // just the first), with an explicit row count so this reads as
        // the bulk operation it is rather than a mislabeled single create.
        $rows = [];
        for ($i = 0; $i < $rowCount; $i++) {
            $rows[] = array_combine($columns, array_slice($bindings, $i * $columnCount, $columnCount));
        }

        AuditService::log([
            'action'      => 'bulk_create',
            'severity'    => 'success',
            'category'    => AuditService::category($table),
            'module'      => AuditService::category($table),
            'table_name'  => $table,
            'description' => "Bulk-created {$rowCount} records in {$table}",
            'new_data'    => $rows,
            'metadata'    => ['bulk_count' => $rowCount],
        ]);
    }

    protected static function recordUpdate(string $table, string $sql, array $bindings, ?array $old): void
    {
        $setColumns = self::extractUpdateSetColumns($sql);
        $newValues = $setColumns ? @array_combine($setColumns, array_slice($bindings, 0, count($setColumns))) : [];
        $newValues = $newValues ?: [];

        $id = self::extractSimpleIdCondition($sql, $bindings);

        $changedFields = [];
        if ($old) {
            foreach ($newValues as $col => $newVal) {
                $oldVal = $old[$col] ?? null;
                if ((string) $oldVal !== (string) $newVal) {
                    $changedFields[] = $col;
                }
            }
        }

        AuditService::log([
            'action'      => 'update',
            'severity'    => 'info',
            'category'    => AuditService::category($table),
            'module'      => AuditService::category($table),
            'table_name'  => $table,
            'record_id'   => $id,
            'description' => $old
                ? "Updated record #{$id} in {$table}"
                : "Updated a record in {$table} (old values unavailable - WHERE clause too complex to safely pre-fetch)",
            // FIX: array_intersect_key($old, []) returns [] (not null)
            // when $newValues couldn't be parsed, which then serialised
            // as "[]" - implying "we captured the old data and it was
            // empty" rather than "we don't have it". Only set old_data
            // when there are actual columns to compare against.
            'old_data'    => ($old && $newValues) ? array_intersect_key($old, $newValues) : null,
            'new_data'    => $newValues ?: null,
            'metadata'    => $changedFields ? ['changed_fields' => $changedFields] : null,
        ]);
    }

    protected static function recordDelete(string $table, string $sql, array $bindings, ?array $old): void
    {
        $id = self::extractSimpleIdCondition($sql, $bindings);

        if ($id !== null) {
            AuditService::log([
                'action'      => 'delete',
                'severity'    => 'danger',
                'category'    => AuditService::category($table),
                'module'      => AuditService::category($table),
                'table_name'  => $table,
                'record_id'   => $id,
                'description' => $old
                    ? "Deleted record #{$id} from {$table}"
                    : "Deleted a record from {$table} (row unavailable before delete)",
                'old_data'    => $old,
            ]);
            return;
        }

        // FIX: whereIn('id', $ids)->delete() (e.g. the bulk-delete
        // feature on the Audit Log / Live Sessions pages themselves)
        // produces `WHERE id IN (?, ?, ?)`, which extractSimpleIdCondition()
        // never matches (it only handles `id = ?`). This used to fall
        // through to the generic branch below and log a whole bulk
        // delete as "Deleted a record" (singular, no ids) - detected
        // and reported accurately here instead.
        $ids = self::extractIdInCondition($sql, $bindings);

        if ($ids) {
            AuditService::log([
                'action'      => 'bulk_delete',
                'severity'    => 'danger',
                'category'    => AuditService::category($table),
                'module'      => AuditService::category($table),
                'table_name'  => $table,
                'description' => 'Bulk-deleted '.count($ids)." record(s) from {$table}",
                'metadata'    => ['deleted_ids' => $ids, 'bulk_count' => count($ids)],
            ]);
            return;
        }

        AuditService::log([
            'action'      => 'delete',
            'severity'    => 'danger',
            'category'    => AuditService::category($table),
            'module'      => AuditService::category($table),
            'table_name'  => $table,
            'description' => "Deleted record(s) from {$table} (WHERE clause too complex to identify which rows)",
        ]);
    }

    protected static function shouldTrack(?string $table): bool
    {
        if (!$table) {
            return false;
        }

        return !in_array($table, config('audit.excluded_tables', []), true);
    }

    protected static function isInsert(string $sql): bool
    {
        return (bool) preg_match('/^\s*insert\s+into/i', $sql);
    }

    protected static function isUpdate(string $sql): bool
    {
        return (bool) preg_match('/^\s*update\s+/i', $sql);
    }

    protected static function isDelete(string $sql): bool
    {
        return (bool) preg_match('/^\s*delete\s+from/i', $sql);
    }

    protected static function isUpdateOrDelete(string $sql): bool
    {
        return self::isUpdate($sql) || self::isDelete($sql);
    }

    protected static function extractTable(string $sql): ?string
    {
        if (preg_match('/^\s*insert\s+into\s+`?([a-zA-Z0-9_]+)`?/i', $sql, $m)) {
            return $m[1];
        }

        if (preg_match('/^\s*update\s+`?([a-zA-Z0-9_]+)`?/i', $sql, $m)) {
            return $m[1];
        }

        if (preg_match('/^\s*delete\s+from\s+`?([a-zA-Z0-9_]+)`?/i', $sql, $m)) {
            return $m[1];
        }

        return null;
    }

    protected static function extractInsertColumns(string $sql): ?array
    {
        if (preg_match('/^\s*insert\s+into\s+`?[a-zA-Z0-9_]+`?\s*\(([^)]+)\)\s*values/i', $sql, $m)) {
            return array_map(fn ($c) => trim($c, ' `'), explode(',', $m[1]));
        }

        return null;
    }

    protected static function extractUpdateSetColumns(string $sql): ?array
    {
        if (!preg_match('/^\s*update\s+`?[a-zA-Z0-9_]+`?\s+set\s+(.+?)\s+where\s/is', $sql, $m)) {
            return null;
        }

        $columns = [];

        foreach (explode(',', $m[1]) as $assignment) {
            if (preg_match('/`?([a-zA-Z0-9_]+)`?\s*=\s*\?/', trim($assignment), $cm)) {
                $columns[] = $cm[1];
            } else {
                // An assignment we can't confidently map to a single "?"
                // placeholder (e.g. `col = col + 1`) - bail out entirely
                // rather than misaligning every column after it.
                return null;
            }
        }

        return $columns;
    }

    /**
     * Detects a WHERE clause of the exact form `id IN (?, ?, ...)` and
     * returns the deleted ids by taking the last N bindings, where N is
     * the number of placeholders found. Returns null for anything else
     * (joins, additional conditions, IN on a different column) rather
     * than guessing.
     */
    protected static function extractIdInCondition(string $sql, array $bindings): ?array
    {
        if (!preg_match('/where\s+`?id`?\s+in\s*\(([^)]*)\)\s*$/i', trim($sql), $m)) {
            return null;
        }

        $placeholderCount = substr_count($m[1], '?');

        if ($placeholderCount === 0) {
            return null;
        }

        $ids = array_slice($bindings, -$placeholderCount);

        return array_values(array_filter(array_map(
            fn ($v) => is_numeric($v) ? (int) $v : null,
            $ids
        )));
    }

    /**
     * Only ever returns a value for the narrow, safe case of a WHERE
     * clause that is exactly `id = ?`. Anything more complex returns
     * null on purpose - see class docblock.
     */
    protected static function extractSimpleIdCondition(string $sql, array $bindings): ?int
    {
        if (!preg_match('/where\s+`?id`?\s*=\s*\?\s*$/i', trim($sql))) {
            return null;
        }

        $value = end($bindings);

        return is_numeric($value) ? (int) $value : null;
    }
}