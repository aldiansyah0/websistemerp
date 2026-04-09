<?php

namespace App\Services;

use App\Models\PurchaseOrder;
use App\Models\User;
use DomainException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class PurchaseOrderWorkflowService
{
    public function store(array $attributes, array $items, string $intent = 'draft'): PurchaseOrder
    {
        return DB::transaction(function () use ($attributes, $items, $intent): PurchaseOrder {
            $purchaseOrder = new PurchaseOrder($attributes);
            $purchaseOrder->po_number = $this->generateNumber();
            $purchaseOrder->created_by = $this->actorId();

            $this->applyIntent($purchaseOrder, $intent);
            $purchaseOrder->save();

            $this->syncItems($purchaseOrder, $items);

            return $purchaseOrder->fresh(['supplier', 'warehouse', 'items.product']);
        });
    }

    public function update(PurchaseOrder $purchaseOrder, array $attributes, array $items, string $intent = 'draft'): PurchaseOrder
    {
        if (! $purchaseOrder->canBeEdited()) {
            throw new DomainException('Purchase order ini sudah tidak bisa diubah.');
        }

        return DB::transaction(function () use ($purchaseOrder, $attributes, $items, $intent): PurchaseOrder {
            $purchaseOrder->fill($attributes);
            $this->applyIntent($purchaseOrder, $intent);
            $purchaseOrder->save();

            $this->syncItems($purchaseOrder, $items);

            return $purchaseOrder->fresh(['supplier', 'warehouse', 'items.product']);
        });
    }

    public function submit(PurchaseOrder $purchaseOrder): PurchaseOrder
    {
        if (! in_array($purchaseOrder->status, [PurchaseOrder::STATUS_DRAFT, PurchaseOrder::STATUS_REJECTED], true)) {
            throw new DomainException('Hanya draft atau purchase order yang ditolak yang bisa disubmit ulang.');
        }

        $purchaseOrder->status = PurchaseOrder::STATUS_PENDING_APPROVAL;
        $purchaseOrder->submitted_at = Carbon::now();
        $purchaseOrder->approved_at = null;
        $purchaseOrder->approved_by = null;
        $purchaseOrder->save();

        return $purchaseOrder;
    }

    public function approve(PurchaseOrder $purchaseOrder): PurchaseOrder
    {
        if (! $purchaseOrder->canBeApproved()) {
            throw new DomainException('Purchase order ini tidak berada pada status pending approval.');
        }

        $purchaseOrder->status = PurchaseOrder::STATUS_APPROVED;
        $purchaseOrder->approved_at = Carbon::now();
        $purchaseOrder->approved_by = $this->actorId();
        $purchaseOrder->save();

        return $purchaseOrder;
    }

    public function reject(PurchaseOrder $purchaseOrder, ?string $reason = null): PurchaseOrder
    {
        if (! $purchaseOrder->canBeApproved()) {
            throw new DomainException('Hanya purchase order pending approval yang bisa ditolak.');
        }

        $purchaseOrder->status = PurchaseOrder::STATUS_REJECTED;
        $purchaseOrder->approved_at = null;
        $purchaseOrder->approved_by = null;
        $purchaseOrder->notes = $this->appendReason($purchaseOrder->notes, 'Rejected', $reason);
        $purchaseOrder->save();

        return $purchaseOrder;
    }

    public function cancel(PurchaseOrder $purchaseOrder, ?string $reason = null): PurchaseOrder
    {
        if (! $purchaseOrder->canBeCancelled()) {
            throw new DomainException('Purchase order ini tidak bisa dibatalkan pada status saat ini.');
        }

        $purchaseOrder->status = PurchaseOrder::STATUS_CANCELLED;
        $purchaseOrder->notes = $this->appendReason($purchaseOrder->notes, 'Cancelled', $reason);
        $purchaseOrder->save();

        return $purchaseOrder;
    }

    private function syncItems(PurchaseOrder $purchaseOrder, array $items): void
    {
        $purchaseOrder->items()->delete();

        $normalizedItems = collect($items)->map(function (array $item): array {
            $orderedQuantity = (float) $item['ordered_quantity'];
            $unitCost = (float) $item['unit_cost'];
            $discountAmount = (float) ($item['discount_amount'] ?? 0);

            return [
                'product_id' => $item['product_id'],
                'ordered_quantity' => $orderedQuantity,
                'received_quantity' => 0,
                'unit_cost' => $unitCost,
                'discount_amount' => $discountAmount,
                'line_total' => max(($orderedQuantity * $unitCost) - $discountAmount, 0),
                'notes' => $item['notes'] ?? null,
            ];
        });

        $purchaseOrder->items()->createMany($normalizedItems->all());
        $purchaseOrder->load('items');
        $purchaseOrder->recalculateTotals($purchaseOrder->items);
        $purchaseOrder->save();
    }

    private function applyIntent(PurchaseOrder $purchaseOrder, string $intent): void
    {
        if ($intent === 'submit') {
            $purchaseOrder->status = PurchaseOrder::STATUS_PENDING_APPROVAL;
            $purchaseOrder->submitted_at = $purchaseOrder->submitted_at ?? Carbon::now();
            $purchaseOrder->approved_at = null;
            $purchaseOrder->approved_by = null;

            return;
        }

        $purchaseOrder->status = PurchaseOrder::STATUS_DRAFT;
        $purchaseOrder->submitted_at = null;
        $purchaseOrder->approved_at = null;
        $purchaseOrder->approved_by = null;
    }

    private function generateNumber(): string
    {
        $prefix = 'PO-' . Carbon::now('Asia/Jakarta')->format('ym');
        $latest = PurchaseOrder::query()
            ->where('po_number', 'like', $prefix . '-%')
            ->orderByDesc('po_number')
            ->value('po_number');

        $lastSequence = $latest ? (int) substr($latest, -3) : 0;

        return sprintf('%s-%03d', $prefix, $lastSequence + 1);
    }

    private function actorId(): int
    {
        return (int) User::query()->firstOrCreate(
            ['email' => 'system.procurement@webstellar.local'],
            ['name' => 'System Procurement', 'password' => 'password']
        )->id;
    }

    private function appendReason(?string $existing, string $label, ?string $reason): string
    {
        $detail = trim((string) $reason);
        $line = '[' . $label . '] ' . ($detail !== '' ? $detail : 'Aksi dilakukan dari workflow purchase order.');

        return trim(trim((string) $existing) . PHP_EOL . $line);
    }
}
