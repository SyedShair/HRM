<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Adds numeric minute counters alongside the existing text status columns
 * (status_timein / status_timeout) on the attendance table, so "Late In"
 * / "Early Out" / overtime can be reported on and summed, not just shown
 * as a label.
 *
 * IMPORTANT: adjust the table name below ('tbl_people_attendance') if your
 * table::attendance() query builder actually points at a different table
 * name - it wasn't visible in what was shared, so this assumes the same
 * tbl_ prefix as tbl_people / tbl_people_schedules.
 */
class AddLateOvertimeToAttendanceTable extends Migration
{
    public function up()
    {
        Schema::table('tbl_people_attendance', function (Blueprint $table) {
            $table->unsignedInteger('late_minutes')->nullable()->default(0)->after('status_timein');
            $table->unsignedInteger('early_minutes')->nullable()->default(0)->after('status_timeout');
            $table->unsignedInteger('overtime_minutes')->nullable()->default(0)->after('early_minutes');
        });
    }

    public function down()
    {
        Schema::table('tbl_people_attendance', function (Blueprint $table) {
            $table->dropColumn(['late_minutes', 'early_minutes', 'overtime_minutes']);
        });
    }
}