<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('tbl_people_attendance', function (Blueprint $table) {
            $table->integer('early_in_minutes')->default(0)->after('late_minutes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tbl_people_attendance', function (Blueprint $table) {
            $table->dropColumn('early_in_minutes');
        });
    }
};