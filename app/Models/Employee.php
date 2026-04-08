<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenantLocation;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Employee extends Model
{
    use HasFactory;
    use BelongsToTenantLocation;

    protected bool $locationScoped = true;

    protected $with = ['outlet', 'location'];

    public const STATUS_ACTIVE = 'active';
    public const STATUS_LEAVE = 'leave';
    public const STATUS_RESIGNED = 'resigned';

    protected $fillable = [
        'tenant_id',
        'location_id',
        'outlet_id',
        'sales_bonus_rate',
        'employee_code',
        'full_name',
        'email',
        'phone',
        'department',
        'position_title',
        'employment_type',
        'join_date',
        'base_salary',
        'overtime_rate',
        'late_penalty_per_minute',
        'absence_penalty_amount',
        'status',
        'emergency_contact',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'join_date' => 'date',
            'base_salary' => 'decimal:2',
            'sales_bonus_rate' => 'decimal:3',
            'overtime_rate' => 'decimal:2',
            'late_penalty_per_minute' => 'decimal:2',
            'absence_penalty_amount' => 'decimal:2',
        ];
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function outlet(): BelongsTo
    {
        return $this->belongsTo(Outlet::class);
    }

    public function attendanceLogs(): HasMany
    {
        return $this->hasMany(AttendanceLog::class);
    }

    public function payrollRunItems(): HasMany
    {
        return $this->hasMany(PayrollRunItem::class);
    }

    public function shiftAssignments(): HasMany
    {
        return $this->hasMany(EmployeeShiftAssignment::class);
    }

    public function cashierTransactions(): HasMany
    {
        return $this->hasMany(SalesTransaction::class, 'cashier_employee_id');
    }

    public static function statusOptions(): array
    {
        return [
            self::STATUS_ACTIVE => 'Active',
            self::STATUS_LEAVE => 'Leave',
            self::STATUS_RESIGNED => 'Resigned',
        ];
    }

    public static function employmentTypeOptions(): array
    {
        return [
            'permanent' => 'Permanent',
            'contract' => 'Contract',
            'part_time' => 'Part Time',
            'intern' => 'Intern',
        ];
    }
}
