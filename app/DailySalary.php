<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DailySalary extends Model
{
    protected $fillable = [
        'employee_id', 'idno', 'date', 'total_hours',
        'rate', 'daily_salary', 'status'
    ];
    
}
