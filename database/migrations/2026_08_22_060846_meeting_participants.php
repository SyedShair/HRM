<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('meeting_participants', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('meeting_id');
            $table->unsignedInteger('employee_id')->nullable(); // null for outside guests (e.g. job candidates)
            $table->string('name');
            $table->string('email');
            $table->enum('role', ['interviewer', 'candidate', 'attendee'])->default('attendee');
            $table->boolean('attended')->nullable();
            $table->timestamps();

            $table->foreign('meeting_id')->references('id')->on('meetings')->onDelete('cascade');
            $table->index('email');
        });
    }

    public function down()
    {
        Schema::dropIfExists('meeting_participants');
    }
};