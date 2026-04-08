<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenantLocation;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

class GoodsReceipt extends Model
{
    use HasFactory;
    use BelongsToTenantLocation;

    protected bool $locationScoped = true;

    protected $with = ['purchaseOrder', 'warehouse'];

    protected $fillable = [
        'tenant_id',
        'location_id',
        'receipt_number',
        'purchase_order_id',
        'warehouse_id',
        'received_by',
        'received_at',
        'total_quantity',
        'total_cost',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'received_at' => 'datetime',
            'total_quantity' => 'decimal:2',
            'total_cost' => 'decimal:2',
        ];
    }

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(GoodsReceiptItem::class);
    }

    public function recalculateTotals(?Collection $items = null): void
    {
        $items = $items ?? $this->items;

        $this->total_quantity = $items->sum(fn (GoodsReceiptItem $item): float => (float) $item->received_quantity);
        $this->total_cost = $items->sum(fn (GoodsReceiptItem $item): float => (float) $item->line_total);
    }
}
