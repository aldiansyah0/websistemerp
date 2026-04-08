<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenantLocation;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryLedger extends Model
{
    use HasFactory;
    use BelongsToTenantLocation;

    protected bool $locationScoped = true;

    protected $with = ['product', 'warehouse'];

    protected $fillable = [
        'tenant_id',
        'location_id',
        'product_id',
        'warehouse_id',
        'movement_type',
        'reference_type',
        'reference_id',
        'quantity',
        'unit_cost',
        'balance_after',
        'notes',
        'transaction_at',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:2',
            'unit_cost' => 'decimal:2',
            'balance_after' => 'decimal:2',
            'transaction_at' => 'datetime',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }
}
