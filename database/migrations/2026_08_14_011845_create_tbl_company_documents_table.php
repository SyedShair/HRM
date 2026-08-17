<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTblCompanyDocumentsTable extends Migration
{
    /**
     * A company can have any number of supporting documents attached
     * (sponsor licence certificate, incorporation certificate, etc.).
     * One row per document.
     */
    public function up()
    {
        Schema::create('tbl_company_documents', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('company_id');   // FK -> tbl_company.id
            $table->string('doc_label', 255);         // e.g. "Sponsor Licence Certificate"
            $table->string('doc_file', 255);           // stored path on the "public" disk
            $table->timestamps();

            $table->index('company_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('tbl_company_documents');
    }
}