<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenantLocation;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttendanceLog extends Model
{
    use HasFactory;
    use BelongsToTenantLocation;

    protected bool $locationScoped = true;

    protected $with = ['employee', 'outlet', 'location'];

    protected $fillable = [
        'tenant_id',
        'location_id',
        'employee_id',
        'outlet_id',
        'shift_date',
        'shift_name',
        'scheduled_start',
        'scheduled_end',
        'clock_in_at',
        'clock_out_at',
        'late_minutes',
        'overtime_minutes',
        'attendance_status',
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
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function outlet(): BelongsTo
    {
        return $this->belongsTo(Outlet::class);
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }
}
