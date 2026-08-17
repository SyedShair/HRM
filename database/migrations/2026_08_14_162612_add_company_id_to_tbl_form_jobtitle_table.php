<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('tbl_form_jobtitle', 'company_id')) {
            Schema::table('tbl_form_jobtitle', function (Blueprint $table) {
                $table->unsignedBigInteger('company_id')
                    ->nullable()
                    ->after('id');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('tbl_form_jobtitle', 'company_id')) {
            Schema::table('tbl_form_jobtitle', function (Blueprint $table) {
                $table->dropColumn('company_id');
            });
        }
    }
};