<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('email_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('reference'); // tbl_people.id
            $table->string('type', 50); // passport_expiry, visa_expiry, rota_notification, custom
            $table->string('milestone', 50)->nullable(); // 5_months, 20_days - null for manual/rota/custom sends
            $table->date('document_date')->nullable(); // the expiry date this reminder was about - lets a renewed document (new expiry date) trigger a fresh reminder cycle instead of being silenced forever by an old log row
            $table->string('to_email');
            $table->string('subject');
            $table->unsignedInteger('sent_by')->nullable(); // users.id - null when sent automatically by the scheduler
            $table->string('status', 20)->default('sent'); // sent, failed
            $table->string('error', 500)->nullable();
            $table->timestamps();

            $table->index(['reference']);
            $table->index(['type', 'milestone']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('email_logs');
    }
};