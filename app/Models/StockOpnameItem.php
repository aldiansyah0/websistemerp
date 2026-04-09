<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockOpnameItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'stock_opname_id',
        'product_id',
        'product_variant_id',
        'system_quantity',
        'physical_quantity',
        'variance_quantity',
        'unit_cost',
        'variance_value',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'product_variant_id' => 'integer',
            'system_quantity' => 'decimal:2',
            'physical_quantity' => 'decimal:2',
            'variance_quantity' => 'decimal:2',
            'unit_cost' => 'decimal:2',
            'variance_value' => 'decimal:2',
        ];
    }

    public function stockOpname(): BelongsTo
    {
        return $this->belongsTo(StockOpname::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function productVariant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class);
    }
}
