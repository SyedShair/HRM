<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payrolls', function (Blueprint $table) {
            $table->id();

            // Employee identity - mirrors how tbl_people_attendance /
            // tbl_company_data reference an employee, so payroll can be
            // joined the same way the rest of the app already does.
            $table->unsignedInteger('reference');   // tbl_people.id
            $table->string('idno', 20);              // tbl_company_data.idno
            $table->string('employee');              // name snapshot at generation time

            $table->date('period_start');
            $table->date('period_end');

            $table->enum('pay_type', ['hourly', 'salaried']);
            $table->decimal('rate', 10, 2);          // perhourpay OR accountpay used for this run

            // Schedule vs actual, for the payslip breakdown
            $table->decimal('scheduled_hours', 8, 2)->default(0);
            $table->unsignedInteger('scheduled_days')->default(0);
            $table->decimal('worked_hours', 8, 2)->default(0);
            $table->decimal('regular_hours', 8, 2)->default(0);
            $table->decimal('overtime_hours', 8, 2)->default(0);
            $table->decimal('restday_hours', 8, 2)->default(0);

            $table->unsignedInteger('unapproved_absence_days')->default(0);
            $table->decimal('absence_deduction', 10, 2)->default(0);
            $table->decimal('overtime_pay', 10, 2)->default(0);
            $table->decimal('restday_pay', 10, 2)->default(0);

            $table->decimal('gross_pay', 10, 2)->default(0);
            $table->decimal('income_tax', 10, 2)->default(0);
            $table->decimal('employee_ni', 10, 2)->default(0);
            $table->decimal('total_deductions', 10, 2)->default(0);
            $table->decimal('net_pay', 10, 2)->default(0);

            $table->enum('status', ['Pending', 'Approved', 'Paid'])->default('Pending');
            $table->text('notes')->nullable();

            $table->timestamp('generated_at')->nullable();
            $table->timestamps();

            // One payroll row per employee per period - re-generating
            // overwrites rather than duplicates.
            $table->unique(['reference', 'period_start', 'period_end'], 'payroll_period_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payrolls');
    }
};