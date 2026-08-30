<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('login_sessions', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('user_id');
            $table->string('session_id', 100);

            $table->timestamp('login_at')->nullable();
            $table->timestamp('logout_at')->nullable();
            $table->timestamp('last_activity_at')->nullable();

            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->string('browser', 100)->nullable();
            $table->string('device', 50)->nullable();

            // online, offline, expired
            $table->string('status', 20)->default('online');

            $table->timestamps();

            $table->index('user_id');
            $table->index('session_id');
            $table->index('last_activity_at');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('login_sessions');
    }
};
