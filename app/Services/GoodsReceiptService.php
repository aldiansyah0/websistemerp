<?php

namespace App\Services;

use App\Models\GoodsReceipt;
use App\Models\PurchaseOrder;
use App\Models\User;
use Carbon\Carbon;
use DomainException;
use Illuminate\Support\Facades\DB;

class GoodsReceiptService
{
    public function __construct(
        private readonly StockService $stockService,
        private readonly AnalyticsCacheService $analyticsCacheService,
        private readonly PeriodLockService $periodLockService,
        private readonly AuditLogService $auditLogService,
    ) {
    }

    public function receive(PurchaseOrder $purchaseOrder, array $attributes, array $items): GoodsReceipt
    {
        if (! $purchaseOrder->canBeReceived()) {
            throw new DomainException('Purchase order ini belum siap menerima barang.');
        }

        $purchaseOrder->loadMissing(['items.product', 'warehouse']);

        return DB::transaction(function () use ($purchaseOrder, $attributes, $items): GoodsReceipt {
            $receivedAt = isset($attributes['received_at']) ? Carbon::parse($attributes['received_at']) : Carbon::now();
            $this->periodLockService->assertDateIsOpen($receivedAt, 'Posting penerimaan barang');
            $goodsReceipt = new GoodsReceipt([
                'receipt_number' => $this->generateNumber(),
                'tenant_id' => $purchaseOrder->tenant_id,
                'location_id' => $purchaseOrder->location_id,
                'purchase_order_id' => $purchaseOrder->id,
                'warehouse_id' => $purchaseOrder->warehouse_id,
                'received_by' => $this->actorId(),
                'received_at' => $receivedAt,
                'notes' => $attributes['notes'] ?? null,
            ]);
            $goodsReceipt->save();

            $mappedItems = collect($items)->keyBy('purchase_order_item_id');
            $createdLines = [];

            foreach ($purchaseOrder->items as $purchaseOrderItem) {
                $payload = $mappedItems->get($purchaseOrderItem->id);
                $receivedQuantity = (float) ($payload['received_quantity'] ?? 0);
                $outstanding = max((float) $purchaseOrderItem->ordered_quantity - (float) $purchaseOrderItem->received_quantity, 0);

                if ($receivedQuantity <= 0) {
                    continue;
                }

                if ($receivedQuantity - $outstanding > 0.0001) {
                    throw new DomainException('Qty receiving melebihi outstanding purchase order.');
                }

                $line = $goodsReceipt->items()->create([
                    'purchase_order_item_id' => $purchaseOrderItem->id,
                    'product_id' => $purchaseOrderItem->product_id,
                    'product_variant_id' => $purchaseOrderItem->product_variant_id,
                    'received_quantity' => $receivedQuantity,
                    'unit_cost' => (float) $purchaseOrderItem->unit_cost,
                    'line_total' => $receivedQuantity * (float) $purchaseOrderItem->unit_cost,
                    'notes' => $payload['notes'] ?? null,
                ]);

                $this->stockService->post(
                    (int) $purchaseOrderItem->product_id,
                    (int) $purchaseOrder->warehouse_id,
                    'purchase',
                    'goods_receipt',
                    (int) $goodsReceipt->id,
                    $receivedQuantity,
                    (float) $purchaseOrderItem->unit_cost,
                    'PO receiving ' . $purchaseOrder->po_number,
                    $receivedAt,
                    null,
                    $purchaseOrderItem->product_variant_id ? (int) $purchaseOrderItem->product_variant_id : null,
                );

                $purchaseOrderItem->received_quantity = (float) $purchaseOrderItem->received_quantity + $receivedQuantity;
                $purchaseOrderItem->save();
                $createdLines[] = $line;
            }

            if ($createdLines === []) {
                throw new DomainException('Masukkan minimal satu qty receiving untuk memproses barang datang.');
            }

            $goodsReceipt->load('items');
            $goodsReceipt->recalculateTotals($goodsReceipt->items);
            $goodsReceipt->save();

            $allReceived = $purchaseOrder->items()->get()->every(function ($item): bool {
                return (float) $item->received_quantity >= (float) $item->ordered_quantity;
            });

            $purchaseOrder->status = $allReceived ? PurchaseOrder::STATUS_RECEIVED : PurchaseOrder::STATUS_PARTIALLY_RECEIVED;
            $purchaseOrder->received_at = $allReceived ? $receivedAt : null;
            $purchaseOrder->save();
            $this->analyticsCacheService->invalidate();
            $this->auditLogService->log('procurement', 'goods_receipt.post', 'Penerimaan barang diposting', $goodsReceipt, [
                'po_number' => $purchaseOrder->po_number,
                'receipt_number' => $goodsReceipt->receipt_number,
                'total_received_qty' => (float) $goodsReceipt->total_quantity,
            ]);

            return $goodsReceipt->fresh(['purchaseOrder', 'warehouse', 'items.product', 'items.productVariant']);
        });
    }

    private function generateNumber(): string
    {
        $prefix = 'GR-' . Carbon::now('Asia/Jakarta')->format('ym');
        $latest = GoodsReceipt::query()
            ->where('receipt_number', 'like', $prefix . '-%')
            ->orderByDesc('receipt_number')
            ->value('receipt_number');

        $lastSequence = $latest ? (int) substr($latest, -3) : 0;

        return sprintf('%s-%03d', $prefix, $lastSequence + 1);
    }

    private function actorId(): int
    {
        return (int) User::query()->firstOrCreate(
            ['email' => 'system.receiving@webstellar.local'],
            ['name' => 'System Receiving', 'password' => 'password']
        )->id;
    }
}
