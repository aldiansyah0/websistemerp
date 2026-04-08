<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenantLocation;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Outlet extends Model
{
    use HasFactory;
    use BelongsToTenantLocation;

    protected bool $locationScoped = true;

    protected $with = ['warehouse', 'location'];

    public const STATUS_ACTIVE = 'active';
    public const STATUS_INACTIVE = 'inactive';
    public const STATUS_RENOVATION = 'renovation';

    protected $fillable = [
        'tenant_id',
        'location_id',
        'code',
        'name',
        'region',
        'city',
        'phone',
        'manager_name',
        'warehouse_id',
        'opening_date',
        'status',
        'daily_sales_target',
        'service_level',
        'inventory_accuracy',
        'is_fulfillment_hub',
        'address',
    ];

    protected function casts(): array
    {
        return [
            'opening_date' => 'date',
            'daily_sales_target' => 'decimal:2',
            'service_level' => 'decimal:2',
            'inventory_accuracy' => 'decimal:2',
            'is_fulfillment_hub' => 'boolean',
        ];
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class);
    }

    public function attendanceLogs(): HasMany
    {
        return $this->hasMany(AttendanceLog::class);
    }

    public function salesTransactions(): HasMany
    {
        return $this->hasMany(SalesTransaction::class);
    }

    public function location(): HasOne
    {
        return $this->hasOne(Location::class, 'legacy_outlet_id');
    }

    public static function statusOptions(): array
    {
        return [
            self::STATUS_ACTIVE => 'Active',
            self::STATUS_INACTIVE => 'Inactive',
            self::STATUS_RENOVATION => 'Renovation',
        ];
    }
}
