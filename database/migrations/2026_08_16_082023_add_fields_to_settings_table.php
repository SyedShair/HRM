<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            // Branding
            $table->string('app_name')->nullable()->after('id');
            $table->string('app_logo')->nullable()->after('app_name');
            $table->string('currency', 10)->nullable()->after('app_logo');

        });
    }

    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn([
                'app_name', 'app_logo', 'currency',
            ]);
        });
    }
};