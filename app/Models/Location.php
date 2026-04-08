<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenantLocation;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Location extends Model
{
    use HasFactory;
    use BelongsToTenantLocation;

    protected bool $locationScoped = false;

    public const TYPE_OUTLET = 'outlet';
    public const TYPE_WAREHOUSE = 'warehouse';

    protected $fillable = [
        'tenant_id',
        'type',
        'code',
        'name',
        'warehouse_subtype',
        'region',
        'city',
        'phone',
        'manager_name',
        'opening_date',
        'status',
        'is_fulfillment_hub',
        'address',
        'parent_location_id',
        'fulfillment_location_id',
        'legacy_warehouse_id',
        'legacy_outlet_id',
        'meta',
    ];

    protected function casts(): array
    {
        return [
            'opening_date' => 'date',
            'is_fulfillment_hub' => 'boolean',
            'meta' => 'array',
        ];
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_location_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_location_id');
    }

    public function fulfillmentLocation(): BelongsTo
    {
        return $this->belongsTo(self::class, 'fulfillment_location_id');
    }

    public function fulfillmentOutlets(): HasMany
    {
        return $this->hasMany(self::class, 'fulfillment_location_id');
    }

    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class);
    }

    public function attendanceLogs(): HasMany
    {
        return $this->hasMany(AttendanceLog::class);
    }

    public function shifts(): HasMany
    {
        return $this->hasMany(Shift::class);
    }

    public function shiftAssignments(): HasMany
    {
        return $this->hasMany(EmployeeShiftAssignment::class);
    }

    public function stockMutations(): HasMany
    {
        return $this->hasMany(StockMutation::class);
    }

    public function sourceTransfers(): HasMany
    {
        return $this->hasMany(LocationTransfer::class, 'source_location_id');
    }

    public function destinationTransfers(): HasMany
    {
        return $this->hasMany(LocationTransfer::class, 'destination_location_id');
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'legacy_warehouse_id');
    }

    public function outlet(): BelongsTo
    {
        return $this->belongsTo(Outlet::class, 'legacy_outlet_id');
    }
}
