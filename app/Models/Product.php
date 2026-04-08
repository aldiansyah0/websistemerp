<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenantLocation;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class Product extends Model
{
    use HasFactory;
    use BelongsToTenantLocation;

    protected bool $locationScoped = false;

    protected $with = [
        'category',
        'primarySupplier',
        'defaultVariant',
    ];

    public const STATUS_ACTIVE = 'active';
    public const STATUS_INACTIVE = 'inactive';
    public const STATUS_DISCONTINUED = 'discontinued';

    protected $fillable = [
        'tenant_id',
        'category_id',
        'primary_supplier_id',
        'sku',
        'barcode',
        'name',
        'slug',
        'description',
        'unit_of_measure',
        'cost_price',
        'selling_price',
        'daily_run_rate',
        'reorder_level',
        'reorder_quantity',
        'shelf_life_days',
        'status',
        'is_featured',
    ];

    protected function casts(): array
    {
        return [
            'cost_price' => 'decimal:2',
            'selling_price' => 'decimal:2',
            'daily_run_rate' => 'decimal:2',
            'reorder_level' => 'decimal:2',
            'reorder_quantity' => 'decimal:2',
            'is_featured' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (self $product): void {
            if (blank($product->slug)) {
                $product->slug = Str::slug($product->name . '-' . $product->sku);
            }
        });
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function primarySupplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'primary_supplier_id');
    }

    public function inventoryLedgers(): HasMany
    {
        return $this->hasMany(InventoryLedger::class);
    }

    public function purchaseOrderItems(): HasMany
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    public function salesTransactionItems(): HasMany
    {
        return $this->hasMany(SalesTransactionItem::class);
    }

    public function stockTransferItems(): HasMany
    {
        return $this->hasMany(StockTransferItem::class);
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function defaultVariant(): HasOne
    {
        return $this->hasOne(ProductVariant::class)->where('is_default', true);
    }

    public static function statusOptions(): array
    {
        return [
            self::STATUS_ACTIVE => 'Active',
            self::STATUS_INACTIVE => 'Inactive',
            self::STATUS_DISCONTINUED => 'Discontinued',
        ];
    }
}
