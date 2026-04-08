<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenantLocation;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductVariant extends Model
{
    use HasFactory;
    use BelongsToTenantLocation;

    protected bool $locationScoped = false;

    protected $fillable = [
        'tenant_id',
        'product_id',
        'sku',
        'barcode',
        'variant_name',
        'size',
        'color',
        'attributes',
        'unit_of_measure',
        'cost_price',
        'selling_price',
        'reorder_level',
        'reorder_quantity',
        'status',
        'is_default',
    ];

    protected function casts(): array
    {
        return [
            'attributes' => 'array',
            'cost_price' => 'decimal:2',
            'selling_price' => 'decimal:2',
            'reorder_level' => 'decimal:2',
            'reorder_quantity' => 'decimal:2',
            'is_default' => 'boolean',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function transferItems(): HasMany
    {
        return $this->hasMany(LocationTransferItem::class);
    }

    public function stockMutations(): HasMany
    {
        return $this->hasMany(StockMutation::class);
    }
}
