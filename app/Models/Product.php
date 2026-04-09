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

        static::created(function (self $product): void {
            $product->variants()->firstOrCreate(
                ['is_default' => true],
                [
                    'tenant_id' => $product->tenant_id,
                    'sku' => $product->sku,
                    'barcode' => $product->barcode,
                    'variant_name' => 'Default',
                    'size' => null,
                    'color' => null,
                    'attributes' => null,
                    'unit_of_measure' => $product->unit_of_measure,
                    'cost_price' => $product->cost_price,
                    'selling_price' => $product->selling_price,
                    'reorder_level' => $product->reorder_level,
                    'reorder_quantity' => $product->reorder_quantity,
                    'status' => in_array($product->status, [self::STATUS_ACTIVE, self::STATUS_INACTIVE, self::STATUS_DISCONTINUED], true)
                        ? $product->status
                        : self::STATUS_ACTIVE,
                ],
            );
        });

        static::updated(function (self $product): void {
            $defaultVariant = $product->defaultVariant;
            if ($defaultVariant === null) {
                return;
            }

            if ($defaultVariant->variant_name !== 'Default') {
                return;
            }

            $defaultVariant->forceFill([
                'tenant_id' => $product->tenant_id,
                'sku' => $product->sku,
                'barcode' => $product->barcode,
                'unit_of_measure' => $product->unit_of_measure,
                'cost_price' => $product->cost_price,
                'selling_price' => $product->selling_price,
                'reorder_level' => $product->reorder_level,
                'reorder_quantity' => $product->reorder_quantity,
                'status' => in_array($product->status, [self::STATUS_ACTIVE, self::STATUS_INACTIVE, self::STATUS_DISCONTINUED], true)
                    ? $product->status
                    : self::STATUS_ACTIVE,
            ])->save();
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
