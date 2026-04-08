<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PayrollRunItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'payroll_run_id',
        'employee_id',
        'base_salary',
        'allowance_amount',
        'overtime_amount',
        'sales_bonus_amount',
        'deduction_amount',
        'attendance_deduction_amount',
        'late_deduction_amount',
        'absence_deduction_amount',
        'net_salary',
        'payment_status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'base_salary' => 'decimal:2',
            'allowance_amount' => 'decimal:2',
            'overtime_amount' => 'decimal:2',
            'sales_bonus_amount' => 'decimal:2',
            'deduction_amount' => 'decimal:2',
            'attendance_deduction_amount' => 'decimal:2',
            'late_deduction_amount' => 'decimal:2',
            'absence_deduction_amount' => 'decimal:2',
            'net_salary' => 'decimal:2',
        ];
    }

    public function payrollRun(): BelongsTo
    {
        return $this->belongsTo(PayrollRun::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
