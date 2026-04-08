<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenantLocation;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Warehouse extends Model
{
    use HasFactory;
    use BelongsToTenantLocation;

    protected bool $locationScoped = true;

    protected $with = ['location'];

    public const TYPE_DISTRIBUTION_CENTER = 'distribution_center';
    public const TYPE_OUTLET = 'outlet';
    public const TYPE_STORE_BACKROOM = 'store_backroom';

    protected $fillable = [
        'tenant_id',
        'location_id',
        'code',
        'name',
        'type',
        'city',
        'address',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function inventoryLedgers(): HasMany
    {
        return $this->hasMany(InventoryLedger::class);
    }

    public function purchaseOrders(): HasMany
    {
        return $this->hasMany(PurchaseOrder::class);
    }

    public function sourceStockTransfers(): HasMany
    {
        return $this->hasMany(StockTransfer::class, 'source_warehouse_id');
    }

    public function destinationStockTransfers(): HasMany
    {
        return $this->hasMany(StockTransfer::class, 'destination_warehouse_id');
    }

    public function goodsReceipts(): HasMany
    {
        return $this->hasMany(GoodsReceipt::class);
    }

    public function stockOpnames(): HasMany
    {
        return $this->hasMany(StockOpname::class);
    }

    public function purchaseReturns(): HasMany
    {
        return $this->hasMany(PurchaseReturn::class);
    }

    public function location(): HasOne
    {
        return $this->hasOne(Location::class, 'legacy_warehouse_id');
    }

    public static function typeOptions(): array
    {
        return [
            self::TYPE_DISTRIBUTION_CENTER => 'Distribution Center',
            self::TYPE_OUTLET => 'Outlet',
            self::TYPE_STORE_BACKROOM => 'Store Backroom',
        ];
    }
}
