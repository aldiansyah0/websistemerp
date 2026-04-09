<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SalesReturnItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'sales_return_id',
        'sales_transaction_item_id',
        'product_id',
        'product_variant_id',
        'quantity',
        'unit_price',
        'unit_cost',
        'line_total',
        'reason',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'sales_transaction_item_id' => 'integer',
            'product_id' => 'integer',
            'product_variant_id' => 'integer',
            'quantity' => 'decimal:2',
            'unit_price' => 'decimal:2',
            'unit_cost' => 'decimal:2',
            'line_total' => 'decimal:2',
        ];
    }

    public function salesReturn(): BelongsTo
    {
        return $this->belongsTo(SalesReturn::class);
    }

    public function salesTransactionItem(): BelongsTo
    {
        return $this->belongsTo(SalesTransactionItem::class);
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
