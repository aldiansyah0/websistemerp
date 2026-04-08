<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenantLocation;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeShiftAssignment extends Model
{
    use HasFactory;
    use BelongsToTenantLocation;

    protected bool $locationScoped = true;

    protected $with = ['employee', 'shift', 'location'];

    public const WORKFLOW_SCHEDULED = 'scheduled';
    public const WORKFLOW_CHECKED_IN = 'checked_in';
    public const WORKFLOW_CHECKED_OUT = 'checked_out';
    public const WORKFLOW_CLOSED = 'closed';
    public const WORKFLOW_CANCELLED = 'cancelled';

    public const ATTENDANCE_OFF = 'off';
    public const ATTENDANCE_PRESENT = 'present';
    public const ATTENDANCE_LATE = 'late';
    public const ATTENDANCE_LEAVE = 'leave';
    public const ATTENDANCE_ABSENT = 'absent';

    protected $fillable = [
        'tenant_id',
        'location_id',
        'employee_id',
        'shift_id',
        'shift_date',
        'scheduled_start',
        'scheduled_end',
        'clock_in_at',
        'clock_out_at',
        'workflow_status',
        'attendance_status',
        'late_minutes',
        'overtime_minutes',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'shift_date' => 'date',
            'scheduled_start' => 'datetime',
            'scheduled_end' => 'datetime',
            'clock_in_at' => 'datetime',
            'clock_out_at' => 'datetime',
            'late_minutes' => 'integer',
            'overtime_minutes' => 'integer',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class);
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }
}
