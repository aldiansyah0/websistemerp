<?php

namespace App\Services;

use App\Models\Product;
use App\Models\StockTransfer;
use App\Models\User;
use Carbon\Carbon;
use DomainException;
use Illuminate\Support\Facades\DB;

class StockTransferWorkflowService
{
    public function __construct(
        private readonly InventoryPostingService $inventoryPostingService,
    ) {
    }

    public function store(array $attributes, array $items, string $intent = 'draft'): StockTransfer
    {
        return DB::transaction(function () use ($attributes, $items, $intent): StockTransfer {
            $transfer = new StockTransfer($attributes);
            $transfer->transfer_number = $this->generateNumber();
            $transfer->requested_by = $this->actorId();

            $this->applyIntent($transfer, $intent);
            $transfer->save();

            $this->syncItems($transfer, $items);

            return $transfer->fresh(['sourceWarehouse', 'destinationWarehouse', 'items.product']);
        });
    }

    public function update(StockTransfer $transfer, array $attributes, array $items, string $intent = 'draft'): StockTransfer
    {
        if (! $transfer->canBeEdited()) {
            throw new DomainException('Transfer stok ini sudah tidak bisa diubah.');
        }

        return DB::transaction(function () use ($transfer, $attributes, $items, $intent): StockTransfer {
            $transfer->fill($attributes);
            $this->applyIntent($transfer, $intent);
            $transfer->save();

            $this->syncItems($transfer, $items);

            return $transfer->fresh(['sourceWarehouse', 'destinationWarehouse', 'items.product']);
        });
    }

    public function submit(StockTransfer $transfer): StockTransfer
    {
        if (! in_array($transfer->status, [StockTransfer::STATUS_DRAFT, StockTransfer::STATUS_REJECTED], true)) {
            throw new DomainException('Hanya transfer draft atau rejected yang bisa disubmit ulang.');
        }

        $transfer->status = StockTransfer::STATUS_PENDING_APPROVAL;
        $transfer->submitted_at = Carbon::now();
        $transfer->approved_at = null;
        $transfer->approved_by = null;
        $transfer->save();

        return $transfer;
    }

    public function approve(StockTransfer $transfer): StockTransfer
    {
        if (! $transfer->canBeApproved()) {
            throw new DomainException('Transfer stok ini tidak berada pada status pending approval.');
        }

        $transfer->status = StockTransfer::STATUS_APPROVED;
        $transfer->approved_at = Carbon::now();
        $transfer->approved_by = $this->actorId();
        $transfer->save();

        return $transfer;
    }

    public function reject(StockTransfer $transfer, ?string $reason = null): StockTransfer
    {
        if (! $transfer->canBeApproved()) {
            throw new DomainException('Hanya transfer stok pending approval yang bisa ditolak.');
        }

        $transfer->status = StockTransfer::STATUS_REJECTED;
        $transfer->approved_at = null;
        $transfer->approved_by = null;
        $transfer->notes = $this->appendReason($transfer->notes, 'Rejected', $reason);
        $transfer->save();

        return $transfer;
    }

    public function cancel(StockTransfer $transfer, ?string $reason = null): StockTransfer
    {
        if (! $transfer->canBeCancelled()) {
            throw new DomainException('Transfer stok ini tidak bisa dibatalkan pada status saat ini.');
        }

        $transfer->status = StockTransfer::STATUS_CANCELLED;
        $transfer->notes = $this->appendReason($transfer->notes, 'Cancelled', $reason);
        $transfer->save();

        return $transfer;
    }

    public function receive(StockTransfer $transfer, array $items, ?string $notes = null): StockTransfer
    {
        if (! $transfer->canBeReceived()) {
            throw new DomainException('Transfer stok ini belum siap diterima.');
        }

        $transfer->loadMissing('items.product');

        return DB::transaction(function () use ($transfer, $items, $notes): StockTransfer {
            $postedAny = false;
            $mappedItems = collect($items)->keyBy('stock_transfer_item_id');

            foreach ($transfer->items as $transferItem) {
                $payload = $mappedItems->get($transferItem->id);
                $receivedQuantity = (float) ($payload['received_quantity'] ?? 0);
                $outstanding = max((float) $transferItem->requested_quantity - (float) $transferItem->received_quantity, 0);

                if ($receivedQuantity <= 0) {
                    continue;
                }

                if ($receivedQuantity - $outstanding > 0.0001) {
                    throw new DomainException('Qty terima transfer melebihi outstanding item.');
                }

                $this->inventoryPostingService->post(
                    (int) $transferItem->product_id,
                    (int) $transfer->source_warehouse_id,
                    'transfer_out',
                    'stock_transfer',
                    (int) $transfer->id,
                    -1 * $receivedQuantity,
                    (float) $transferItem->unit_cost,
                    'Transfer out ' . $transfer->transfer_number,
                    Carbon::now(),
                );

                $this->inventoryPostingService->post(
                    (int) $transferItem->product_id,
                    (int) $transfer->destination_warehouse_id,
                    'transfer_in',
                    'stock_transfer',
                    (int) $transfer->id,
                    $receivedQuantity,
                    (float) $transferItem->unit_cost,
                    'Transfer in ' . $transfer->transfer_number,
                    Carbon::now(),
                );

                $transferItem->received_quantity = (float) $transferItem->received_quantity + $receivedQuantity;
                $transferItem->save();
                $postedAny = true;
            }

            if (! $postedAny) {
                throw new DomainException('Masukkan minimal satu qty terima untuk menyelesaikan transfer stok.');
            }

            $transfer->notes = $notes ? $this->appendReason($transfer->notes, 'Receiving', $notes) : $transfer->notes;

            if ($transfer->items->every(fn ($item): bool => (float) $item->received_quantity >= (float) $item->requested_quantity)) {
                $transfer->status = StockTransfer::STATUS_RECEIVED;
                $transfer->received_at = Carbon::now();
                $transfer->received_by = $this->actorId();
            }

            $transfer->save();

            return $transfer->fresh(['sourceWarehouse', 'destinationWarehouse', 'items.product']);
        });
    }

    private function syncItems(StockTransfer $transfer, array $items): void
    {
        $transfer->items()->delete();

        $normalizedItems = collect($items)->map(function (array $item): array {
            $product = Product::query()->findOrFail($item['product_id']);
            $requestedQuantity = (float) $item['requested_quantity'];
            $unitCost = (float) $product->cost_price;

            return [
                'product_id' => $product->id,
                'requested_quantity' => $requestedQuantity,
                'received_quantity' => 0,
                'unit_cost' => $unitCost,
                'line_total' => $requestedQuantity * $unitCost,
                'notes' => $item['notes'] ?? null,
            ];
        });

        $transfer->items()->createMany($normalizedItems->all());
        $transfer->load('items');
        $transfer->recalculateTotals($transfer->items);
        $transfer->save();
    }

    private function applyIntent(StockTransfer $transfer, string $intent): void
    {
        if ($intent === 'submit') {
            $transfer->status = StockTransfer::STATUS_PENDING_APPROVAL;
            $transfer->submitted_at = $transfer->submitted_at ?? Carbon::now();
            $transfer->approved_at = null;
            $transfer->approved_by = null;

            return;
        }

        $transfer->status = StockTransfer::STATUS_DRAFT;
        $transfer->submitted_at = null;
        $transfer->approved_at = null;
        $transfer->approved_by = null;
    }

    private function generateNumber(): string
    {
        $prefix = 'ST-' . Carbon::now('Asia/Jakarta')->format('ym');
        $latest = StockTransfer::query()
            ->where('transfer_number', 'like', $prefix . '-%')
            ->orderByDesc('transfer_number')
            ->value('transfer_number');

        $lastSequence = $latest ? (int) substr($latest, -3) : 0;

        return sprintf('%s-%03d', $prefix, $lastSequence + 1);
    }

    private function actorId(): int
    {
        return (int) User::query()->firstOrCreate(
            ['email' => 'system.inventory@webstellar.local'],
            ['name' => 'System Inventory', 'password' => 'password']
        )->id;
    }

    private function appendReason(?string $existing, string $label, ?string $reason): string
    {
        $detail = trim((string) $reason);
        $line = '[' . $label . '] ' . ($detail !== '' ? $detail : 'Aksi dilakukan dari workflow transfer stok.');

        return trim(trim((string) $existing) . PHP_EOL . $line);
    }
}
