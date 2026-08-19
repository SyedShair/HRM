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
        Schema::table('tbl_people', function (Blueprint $table) {
           

            $table->timestamp('sharecode_expires_at')
                  ->nullable()
                  ->after('sharecode');

            $table->index('sharecode_expires_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tbl_people', function (Blueprint $table) {
            $table->dropIndex(['sharecode_expires_at']);
           
            $table->dropColumn([ 'sharecode_expires_at']);
        });
    }
};