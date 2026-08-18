<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payrolls', function (Blueprint $table) {
            $table->decimal('contracted_monthly_gross', 10, 2)->nullable()->after('rate');
            $table->json('contracted_breakdown')->nullable()->after('contracted_monthly_gross');
        });
    }

    public function down(): void
    {
        Schema::table('payrolls', function (Blueprint $table) {
            $table->dropColumn(['contracted_monthly_gross', 'contracted_breakdown']);
        });
    }
};