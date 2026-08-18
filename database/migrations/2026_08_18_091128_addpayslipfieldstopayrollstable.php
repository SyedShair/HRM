<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payrolls', function (Blueprint $table) {
            // Contracted reference figures
           
            // Payslip header fields
            $table->string('department')->nullable()->after('idno');
            $table->string('ni_number')->nullable()->after('department');
            $table->string('tax_code')->nullable()->after('ni_number');
            $table->string('period_label')->nullable()->after('tax_code');

            // Pay split shown on a real payslip
            $table->decimal('taxable_pay', 10, 2)->nullable()->after('gross_pay');
            $table->decimal('non_taxable_pay', 10, 2)->nullable()->after('taxable_pay');
            $table->decimal('niable_pay', 10, 2)->nullable()->after('non_taxable_pay');
            $table->decimal('employer_ni', 10, 2)->nullable()->after('employee_ni');

            // Year-to-date columns
            $table->decimal('ytd_gross', 10, 2)->nullable()->after('net_pay');
            $table->decimal('ytd_taxable_pay', 10, 2)->nullable()->after('ytd_gross');
            $table->decimal('ytd_tax', 10, 2)->nullable()->after('ytd_taxable_pay');
            $table->decimal('ytd_employee_ni', 10, 2)->nullable()->after('ytd_tax');
            $table->decimal('ytd_employer_ni', 10, 2)->nullable()->after('ytd_employee_ni');
            $table->decimal('ytd_niable_pay', 10, 2)->nullable()->after('ytd_employer_ni');
        });
    }

    public function down(): void
    {
        Schema::table('payrolls', function (Blueprint $table) {
            $table->dropColumn([
                'contracted_monthly_gross',
                'contracted_breakdown',
                'department',
                'ni_number',
                'tax_code',
                'period_label',
                'taxable_pay',
                'non_taxable_pay',
                'niable_pay',
                'employer_ni',
                'ytd_gross',
                'ytd_taxable_pay',
                'ytd_tax',
                'ytd_employee_ni',
                'ytd_employer_ni',
                'ytd_niable_pay',
            ]);
        });
    }
};