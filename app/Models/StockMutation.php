<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenantLocation;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockMutation extends Model
{
    use HasFactory;
    use BelongsToTenantLocation;

    protected bool $locationScoped = true;

    protected $with = ['productVariant', 'location', 'relatedLocation'];

    protected $fillable = [
        'tenant_id',
        'product_variant_id',
        'location_id',
        'related_location_id',
        'transfer_id',
        'mutation_type',
        'transfer_status',
        'quantity',
        'unit_cost',
        'balance_after',
        'reference_type',
        'reference_id',
        'notes',
        'occurred_at',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:2',
            'unit_cost' => 'decimal:2',
            'balance_after' => 'decimal:2',
            'occurred_at' => 'datetime',
        ];
    }

    public function productVariant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class);
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function relatedLocation(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'related_location_id');
    }

    public function transfer(): BelongsTo
    {
        return $this->belongsTo(LocationTransfer::class);
    }
}
