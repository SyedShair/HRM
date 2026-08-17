<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCompanyToTblDepartmentTable extends Migration
{
    /**
     * Scopes each department to a company (stored as the company name,
     * matching the value used by the company <select> throughout the
     * app - see tbl_jobtitle.dept_code for the same text-matching
     * pattern one level down).
     */
    public function up()
    {
        Schema::table('tbl_form_department', function (Blueprint $table) {
            $table->string('company', 100)->nullable()->after('department');
        });
    }

    public function down()
    {
        Schema::table('tbl_form_department', function (Blueprint $table) {
            $table->dropColumn('company');
        });
    }
}