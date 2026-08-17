<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDocFieldsToTblAddressHistoryTable extends Migration
{
    /**
     * Each address period can carry its own supporting document:
     *  - doc_reference: a free-text ID/reference printed on the document
     *    itself (e.g. a bank statement or utility bill account number)
     *  - doc_file: the stored path of an uploaded scan/photo of that document
     */
    public function up()
    {
        Schema::table('tbl_address_history', function (Blueprint $table) {
            $table->string('doc_reference', 255)->nullable()->after('date_to');
            $table->string('doc_file', 255)->nullable()->after('doc_reference');
        });
    }

    public function down()
    {
        Schema::table('tbl_address_history', function (Blueprint $table) {
            $table->dropColumn(['doc_reference', 'doc_file']);
        });
    }
}