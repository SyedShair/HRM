<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Adds a real foreign key (jobtitle_id) to tbl_company_data, mirroring
 * the company_id fix.
 *
 * BEFORE this migration: an employee's job duties could only be found
 * by text-matching tbl_company_data.jobposition against
 * tbl_form_jobtitle.jobtitle - fragile, and ambiguous if two companies
 * share a job title name.
 *
 * AFTER this migration: tbl_company_data.jobtitle_id is a real FK to
 * tbl_form_jobtitle, so Job Duties (and any other job-title master
 * data) can be pulled via a join instead of a text match.
 *
 * NOTE: uses unsignedInteger (INT UNSIGNED), matching
 * tbl_form_jobtitle.id's actual type (int(11) UNSIGNED).
 * Using unsignedBigInteger here would cause MySQL error 1005/150
 * ("foreign key constraint is incorrectly formed") since the FK
 * column type must exactly match the referenced column type.
 */
return new class extends Migration
{
    public function up()
    {
        Schema::table('tbl_company_data', function (Blueprint $table) {
            $table->unsignedInteger('jobtitle_id')->nullable()->after('jobposition');
            $table->index('jobtitle_id');

            $table->foreign('jobtitle_id')
                ->references('id')
                ->on('tbl_form_jobtitle')
                ->nullOnDelete();
        });

        // Best-effort backfill: match existing free-text jobposition
        // values to tbl_form_jobtitle, scoped to the same company_id
        // where possible so identical job title names in different
        // companies don't get cross-matched. Falls back to a
        // company-agnostic match only if company_id is still null.
        $jobtitles = DB::table('tbl_form_jobtitle')->get(['id', 'jobtitle', 'company_id']);

        foreach ($jobtitles as $jt) {
            $query = DB::table('tbl_company_data')
                ->whereRaw('UPPER(TRIM(jobposition)) = ?', [mb_strtoupper(trim($jt->jobtitle))])
                ->whereNull('jobtitle_id');

            if (!is_null($jt->company_id)) {
                $query->where(function ($q) use ($jt) {
                    $q->where('company_id', $jt->company_id)
                      ->orWhereNull('company_id');
                });
            }

            $query->update(['jobtitle_id' => $jt->id]);
        }
    }

    public function down()
    {
        Schema::table('tbl_company_data', function (Blueprint $table) {
            $table->dropForeign(['jobtitle_id']);
            $table->dropIndex(['jobtitle_id']);
            $table->dropColumn('jobtitle_id');
        });
    }
};