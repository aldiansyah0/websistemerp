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

    /**
     * Check if receiving exceeds PO quantity for an item
     * Allows 5% tolerance for rounding
     */
    public function validateItemOverage(int $poItemId, float $receivedQty): bool
    {
        $poItem = $this->purchaseOrder
            ?->items()
            ->where('id', $poItemId)
            ->first();

        if (!$poItem) {
            return false;
        }

        // Calculate total already received
        $alreadyReceived = GoodsReceiptItem::whereHas('goodsReceipt', function ($q) {
            $q->where('purchase_order_id', $this->purchase_order_id)
                ->where('id', '!=', $this->id ?? 0);
        })->where('purchase_order_item_id', $poItemId)
            ->sum('received_quantity');

        $totalToReceive = $alreadyReceived + $receivedQty;
        $tolerance = $poItem->order_quantity * 0.05; // 5% tolerance

        return $totalToReceive <= ($poItem->order_quantity + $tolerance);
    }

    /**
     * Get overage summary if any
     */
    public function getOverageSummary(): array
    {
        $po = $this->purchaseOrder;
        if (!$po) {
            return [];
        }

        $overages = [];

        foreach ($this->items as $grItem) {
            $poItem = $po->items()->where('id', $grItem->purchase_order_item_id)->first();
            if (!$poItem) continue;

            $alreadyReceived = GoodsReceiptItem::whereHas('goodsReceipt', function ($q) {
                $q->where('purchase_order_id', $this->purchase_order_id)
                    ->where('id', '!=', $this->id);
            })->where('purchase_order_item_id', $poItem->id)
                ->sum('received_quantity');

            $totalReceived = $alreadyReceived + $grItem->received_quantity;

            if ($totalReceived > $poItem->order_quantity) {
                $overages[] = [
                    'product_name' => $grItem->product?->name,
                    'po_qty' => $poItem->order_quantity,
                    'received_qty' => $totalReceived,
                    'overage_qty' => $totalReceived - $poItem->order_quantity,
                ];
            }
        }

        return $overages;
    }
}
