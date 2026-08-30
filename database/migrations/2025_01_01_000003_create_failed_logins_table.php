<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('failed_logins', function (Blueprint $table) {
            $table->id();

            $table->string('username', 150)->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->string('browser', 100)->nullable();
            $table->string('device', 50)->nullable();
            $table->string('failure_reason', 150)->nullable();
            $table->timestamp('attempted_at')->nullable();

            $table->timestamps();

            $table->index('username');
            $table->index('ip_address');
            $table->index('attempted_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('failed_logins');
    }
};
