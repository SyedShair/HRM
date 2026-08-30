<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('user_name')->nullable();
            $table->string('role', 50)->nullable();

            $table->string('category', 50)->default('Other');
            $table->string('action', 50); // login, logout, failed_login, create, update, delete, request, export, upload, download, security, other
            $table->string('severity', 20)->default('info'); // info, success, warning, danger

            $table->string('module', 100)->nullable();
            $table->string('table_name', 100)->nullable();
            $table->unsignedBigInteger('record_id')->nullable();

            $table->text('description')->nullable();

            $table->string('url', 500)->nullable();
            $table->string('route_name', 150)->nullable();
            $table->string('http_method', 10)->nullable();

            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->string('browser', 100)->nullable();
            $table->string('browser_version', 50)->nullable();
            $table->string('operating_system', 100)->nullable();
            $table->string('device', 50)->nullable();

            // JSON payloads - kept as longText rather than a json()
            // column so this works identically on older MySQL/MariaDB
            // versions that don't fully support the JSON type.
            $table->longText('old_data')->nullable();
            $table->longText('new_data')->nullable();
            $table->longText('metadata')->nullable();

            $table->timestamps();

            // Indexes for the filters/search the dashboard needs.
            $table->index('user_id');
            $table->index('role');
            $table->index('category');
            $table->index('action');
            $table->index('severity');
            $table->index('module');
            $table->index('table_name');
            $table->index('record_id');
            $table->index('ip_address');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};
