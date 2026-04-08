<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenantLocation;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Shift extends Model
{
    use HasFactory;
    use BelongsToTenantLocation;

    protected bool $locationScoped = true;

    protected $fillable = [
        'tenant_id',
        'location_id',
        'code',
        'name',
        'start_time',
        'end_time',
        'is_overnight',
        'grace_minutes',
        'max_overtime_minutes',
        'is_active',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'is_overnight' => 'boolean',
            'grace_minutes' => 'integer',
            'max_overtime_minutes' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(EmployeeShiftAssignment::class);
    }
}
