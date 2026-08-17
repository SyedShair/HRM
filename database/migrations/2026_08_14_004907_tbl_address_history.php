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
         Schema::create('tbl_address_history', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('reference');       // FK -> tbl_people.id
            $table->string('address_line', 500);
            $table->date('date_from');
            $table->date('date_to')->nullable();          // null = still current address
            $table->boolean('is_current')->default(false);
            $table->timestamps();
 
            $table->index('reference');
            $table->index(['reference', 'date_from']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
                Schema::dropIfExists('tbl_address_history');

    }
};
