<?php
namespace App\Traits;

use DB;
use Carbon\Carbon;
use App\Classes\table;

/**
 * =====================================================================
 * Shared attendance/shift logic.
 * =====================================================================
 *
 * Used by BOTH:
 *   - App\Http\Controllers\Admin\AttendanceController (manual entry / edit)
 *   - App\Http\Controllers\ClockController            (QR self clock-in)
 *
 * These two entry points must agree on (a) what "now" is, and (b) how a
 * clock-in/out compares to the employee's rota. Before this trait existed
 * they didn't: ClockController built its own timestamp with raw PHP
 * date() (ignoring the org's saved timezone) and compared against the
 * flat schedule intime/outime only, never the weekly_shifts rota. That's
 * what caused the ~1 hour discrepancy on QR clock-ins - not a bug in
 * AttendanceController, which was already fixed.
 *
 * Keeping this in one place means a future change to how shifts are
 * resolved, or how late/early is calculated, only has to happen once.
 */
trait AttendanceShiftHelpers
{
    /**
     * The org's configured timezone (tbl settings.timezone, e.g.
     * "Europe/London"). Falls back to config('app.timezone') only if
     * the settings row has nothing set, so a missing/blank value never
     * silently produces UTC-vs-local drift.
     */
    protected function orgTimezone()
    {
        $tz = table::settings()->value('timezone');

        return $tz ?: config('app.timezone', 'UTC');
    }

    /**
     * The current instant, in the org's timezone. Use this instead of
     * date()/now()/Carbon::now() anywhere a clock-in/out timestamp is
     * generated server-side (i.e. ClockController::add()), so it can
     * never drift from what the org has configured.
     */
    protected function orgNow()
    {
        return Carbon::now($this->orgTimezone());
    }

    /**
     * Parse any incoming date/time string (local wall-clock OR an ISO
     * string with its own offset/Z) and return it as a naive
     * "Y-m-d H:i:s" string in the org's timezone, ready for storage.
     */
    protected function parseInOrgTz($value)
    {
        if ($value == null || $value === '') {
            return null;
        }

        $tz = $this->orgTimezone();

        return Carbon::parse($value, $tz)->setTimezone($tz)->format('Y-m-d H:i:s');
    }

    protected function parseTimeInOrgTz($value)
    {
        $full = $this->parseInOrgTz($value);
        return $full === null ? null : Carbon::createFromFormat('Y-m-d H:i:s', $full)->format('H:i:s');
    }

    protected function parseDateInOrgTz($value)
    {
        $full = $this->parseInOrgTz($value);
        return $full === null ? null : Carbon::createFromFormat('Y-m-d H:i:s', $full)->format('Y-m-d');
    }

    /**
     * Resolve which shift applies to an employee on a given date, using
     * their active schedule (tbl_people_schedules, archive = 0) and that
     * schedule's weekly_shifts row for the date's day-of-week.
     *
     * Return shapes:
     *   null                              -> employee has no active schedule at all
     *   ['is_off' => true]                -> this is a rest day under the rota
     *   ['is_off' => false,
     *     'time_in' => ..., 'time_out' => ...]  -> the shift to compare against
     *
     * Falls back to the schedule's flat intime/outime if there's no
     * weekly_shifts row for that specific day.
     *
     * time_in / time_out are normalized to 24-hour "H:i:s" so every
     * caller downstream works against the same canonical format.
     */
    protected function resolveShift($emp_idno, $date)
    {
        // Only a schedule that's actually valid for this date counts -
        // tbl_people_schedules has datefrom/dateto, and archive=0 alone
        // doesn't mean the date range hasn't already lapsed (or hasn't
        // started yet).
        $schedule = table::schedules()->where([
            ['idno', '=', $emp_idno],
            ['archive', '=', '0'],
        ])
        ->where(function ($q) use ($date) {
            $q->whereNull('datefrom')->orWhere('datefrom', '<=', $date);
        })
        ->where(function ($q) use ($date) {
            $q->whereNull('dateto')->orWhere('dateto', '>=', $date);
        })
        ->first();

        if ($schedule == null) {
            return null;
        }

        $dayName = Carbon::parse($date)->format('l'); // "Monday" .. "Sunday"

        $shift = DB::table('weekly_shifts')
            ->where('schedual_id', $schedule->id)
            ->where('day', $dayName)
            ->where('active', 1)
            ->first();

        if ($shift !== null) {
            if ($shift->is_off) {
                return ['is_off' => true];
            }

            if ($shift->time_in == null || $shift->time_out == null) {
                return null;
            }

            return [
                'is_off' => false,
                'time_in' => Carbon::parse($shift->time_in)->format('H:i:s'),
                'time_out' => Carbon::parse($shift->time_out)->format('H:i:s'),
            ];
        }

        // No weekly_shifts row for this schedule/day (this is the normal
        // case right now - none of the current tbl_people_schedules rows
        // have a matching schedual_id in weekly_shifts). Fall back to the
        // flat schedule: its own comma-separated restday list first
        // ("Monday", "Tuesday, Wednesday", etc.), then intime/outime.
        if ($this->isFlatScheduleRestDay($schedule->restday ?? null, $dayName)) {
            return ['is_off' => true];
        }

        if ($schedule->intime == null || $schedule->outime == null) {
            return null;
        }

        return [
            'is_off' => false,
            'time_in' => Carbon::parse($schedule->intime)->format('H:i:s'),
            'time_out' => Carbon::parse($schedule->outime)->format('H:i:s'),
        ];
    }

    /**
     * tbl_people_schedules.restday holds a comma-separated list of day
     * names (e.g. "Monday", "Tuesday, Wednesday"). Used as the rest-day
     * source for schedules that don't have a weekly_shifts breakdown.
     */
    private function isFlatScheduleRestDay($restdayField, $dayName)
    {
        if ($restdayField == null || trim($restdayField) === '') {
            return false;
        }

        $restDays = array_map('trim', explode(',', $restdayField));

        return in_array($dayName, $restDays);
    }

    /**
     * Compares an actual clock-in against the rota's scheduled clock-in.
     *
     *   actual > scheduled  -> 'Late In',   lateMinutes set, earlyMinutes 0
     *   actual < scheduled  -> 'Early In',  earlyMinutes set, lateMinutes 0
     *   actual == scheduled -> 'In Time',   both 0
     */
    protected function lateStats(Carbon $schedIn, Carbon $actualIn)
    {
        if ($actualIn->greaterThan($schedIn)) {
            return ['status' => 'Late In', 'lateMinutes' => $schedIn->diffInMinutes($actualIn), 'earlyMinutes' => 0];
        }

        if ($actualIn->lessThan($schedIn)) {
            return ['status' => 'Early In', 'lateMinutes' => 0, 'earlyMinutes' => $actualIn->diffInMinutes($schedIn)];
        }

        return ['status' => 'In Time', 'lateMinutes' => 0, 'earlyMinutes' => 0];
    }

    /**
     * Compares an actual clock-out against the scheduled clock-out.
     * Handles overnight shifts (schedOut <= schedIn, or actualOut <
     * actualIn) by rolling the relevant time to the next day before
     * comparing.
     */
    protected function outStats(Carbon $schedIn, Carbon $schedOut, Carbon $actualIn, Carbon $actualOut)
    {
        $schedOut = $schedOut->copy();
        if ($schedOut->lessThanOrEqualTo($schedIn)) {
            $schedOut->addDay();
        }

        $out = $actualOut->copy();
        if ($out->lessThan($actualIn)) {
            $out->addDay();
        }

        if ($out->lessThan($schedOut)) {
            return ['status' => 'Early Out', 'earlyMinutes' => $out->diffInMinutes($schedOut), 'overtimeMinutes' => 0];
        }

        return ['status' => 'On Time', 'earlyMinutes' => 0, 'overtimeMinutes' => $schedOut->diffInMinutes($out)];
    }

    /**
     * When an employee clocks in before their scheduled shift start,
     * paid/total hours should count from the SCHEDULED start time, not
     * the earlier actual clock-in - the status still reports "Early In"
     * so it's visible in reports, but total hours worked isn't inflated
     * by time spent on-site before the shift began.
     */
    protected function effectiveClockIn(?Carbon $schedIn, Carbon $actualIn)
    {
        if ($schedIn !== null && $actualIn->lessThan($schedIn)) {
            return $schedIn->copy();
        }

        return $actualIn;
    }

    /**
     * Format a canonical "H:i:s" (or full datetime) string for display,
     * honoring the org's time_format setting (1 = 12-hour, else 24-hour).
     */
    protected function formatForDisplay($value, $tf)
    {
        if ($value == null) {
            return null;
        }

        return ($tf == 1)
            ? Carbon::parse($value)->format('h:i:s A')
            : Carbon::parse($value)->format('H:i:s');
    }
}