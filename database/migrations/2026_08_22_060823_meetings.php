<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('meetings', function (Blueprint $table) {
            $table->id();
            $table->string('zoom_meeting_id')->nullable()->unique();
            $table->string('topic');
            $table->text('agenda')->nullable();
            $table->enum('category', ['interview', 'internal', 'client', 'other'])->default('internal');
            $table->unsignedInteger('host_employee_id')->nullable();
            $table->dateTime('start_time');
            $table->unsignedInteger('duration')->default(30); // minutes
            $table->string('timezone')->default('Europe/London');
            $table->text('join_url')->nullable();
            $table->text('start_url')->nullable();
            $table->string('password')->nullable();
            $table->enum('status', ['scheduled', 'started', 'ended', 'cancelled'])->default('scheduled');
            $table->text('recording_url')->nullable();
            $table->string('recording_password')->nullable();
            $table->text('transcript_url')->nullable();
            $table->text('notes')->nullable();
            $table->unsignedInteger('created_by')->nullable();
            $table->boolean('archive')->default(0);
            $table->timestamps();

            $table->index('start_time');
            $table->index('status');
        });
    }

    public function down()
    {
        Schema::dropIfExists('meetings');
    }
};