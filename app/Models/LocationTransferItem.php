<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LocationTransferItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'location_transfer_id',
        'product_variant_id',
        'requested_quantity',
        'sent_quantity',
        'received_quantity',
        'unit_cost',
        'line_total',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'requested_quantity' => 'decimal:2',
            'sent_quantity' => 'decimal:2',
            'received_quantity' => 'decimal:2',
            'unit_cost' => 'decimal:2',
            'line_total' => 'decimal:2',
        ];
    }

    public function transfer(): BelongsTo
    {
        return $this->belongsTo(LocationTransfer::class, 'location_transfer_id');
    }

    public function productVariant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class);
    }
}
