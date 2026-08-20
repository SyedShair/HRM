<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Adds a real foreign key (company_id) to tbl_company_data.
 *
 * BEFORE this migration: an employee's company was only stored as a
 * free-text string (tbl_company_data.company), copied at insert time
 * from the companies master table. Any drift between that string and
 * the master table (extra spaces, "Ltd" suffix, casing, a company
 * later renamed) breaks any filter/report that tries to match on
 * text. This is exactly the bug behind the Employees page company
 * filter not matching reliably.
 *
 * AFTER this migration: tbl_company_data.company_id is a real FK back
 * to tbl_form_company, so filtering/joining is done on IDs, which
 * can never mismatch. The old `company` text column is left in place
 * (nullable use continues) for backward compatibility / historical
 * records and reporting, and is backfilled below on a best-effort
 * basis, but the ID is now the source of truth going forward.
 *
 * NOTE: uses unsignedInteger (INT UNSIGNED), matching
 * tbl_form_company.id's actual type (int(11) UNSIGNED). Using
 * unsignedBigInteger here causes MySQL error 1005/150 ("foreign key
 * constraint is incorrectly formed") since the FK column type must
 * exactly match the referenced column type.
 */
return new class extends Migration
{
    public function up()
    {
        Schema::table('tbl_company_data', function (Blueprint $table) {
            $table->unsignedInteger('company_id')->nullable()->after('reference');
            $table->index('company_id');

            $table->foreign('company_id')
                ->references('id')
                ->on('tbl_form_company')
                ->nullOnDelete();
        });

        // Best-effort backfill: match existing free-text company names
        // to the master table (tbl_form_company) by exact, case-
        // insensitive, trimmed match. Any row that doesn't find a
        // confident match is left NULL and will just fall outside the
        // company filter until fixed manually (safer than guessing wrong).
        $companies = DB::table('tbl_form_company')->get(['id', 'company']);

        foreach ($companies as $company) {
            DB::table('tbl_company_data')
                ->whereRaw('UPPER(TRIM(company)) = ?', [mb_strtoupper(trim($company->company))])
                ->update(['company_id' => $company->id]);
        }
    }

    public function down()
    {
        Schema::table('tbl_company_data', function (Blueprint $table) {
            $table->dropForeign(['company_id']);
            $table->dropIndex(['company_id']);
            $table->dropColumn('company_id');
        });
    }
};