<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payroll extends Model
{
    protected $table = 'payrolls';

    protected $fillable = [
        'reference', 'idno', 'employee',
        'period_start', 'period_end',
        'pay_type', 'rate',
        'scheduled_hours', 'scheduled_days',
        'worked_hours', 'regular_hours', 'overtime_hours', 'restday_hours',
        'unapproved_absence_days', 'absence_deduction',
        'overtime_pay', 'restday_pay',
        'gross_pay', 'income_tax', 'employee_ni', 'total_deductions', 'net_pay',
        'status', 'notes', 'generated_at',
    ];

    protected $casts = [
        'period_start'  => 'date',
        'period_end'    => 'date',
        'generated_at'  => 'datetime',
        'rate'          => 'float',
        'gross_pay'     => 'float',
        'income_tax'    => 'float',
        'employee_ni'   => 'float',
        'net_pay'       => 'float',
        'contracted_breakdown' => 'array',

    ];
}