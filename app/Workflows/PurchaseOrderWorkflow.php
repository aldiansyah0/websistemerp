<?php

namespace App\Workflows;

use App\Models\PurchaseOrder;
use App\Models\User;
use App\Notifications\PurchaseOrderStatusNotification;
use App\Services\PurchaseOrderService;

class PurchaseOrderWorkflow
{
    public function __construct(
        private readonly PurchaseOrderService $service,
    ) {
    }

    public function store(array $attributes, array $items, string $intent = 'draft'): PurchaseOrder
    {
        return $this->service->store($attributes, $items, $intent);
    }

    public function update(PurchaseOrder $purchaseOrder, array $attributes, array $items, string $intent = 'draft'): PurchaseOrder
    {
        return $this->service->update($purchaseOrder, $attributes, $items, $intent);
    }

    public function submit(PurchaseOrder $purchaseOrder): PurchaseOrder
    {
        return $this->service->submit($purchaseOrder);
    }

    public function approve(PurchaseOrder $purchaseOrder): PurchaseOrder
    {
        $approved = $this->service->approve($purchaseOrder);

        if ($approved->created_by !== null) {
            $creator = User::find($approved->created_by);
            if ($creator !== null) {
                $creator->notify(new PurchaseOrderStatusNotification($approved, 'approved'));
            }
        }

        return $approved;
    }

    public function reject(PurchaseOrder $purchaseOrder, ?string $reason = null): PurchaseOrder
    {
        $rejected = $this->service->reject($purchaseOrder, $reason);

        if ($rejected->created_by !== null) {
            $creator = User::find($rejected->created_by);
            if ($creator !== null) {
                $creator->notify(new PurchaseOrderStatusNotification($rejected, 'rejected'));
            }
        }

        return $rejected;
    }

    public function cancel(PurchaseOrder $purchaseOrder, ?string $reason = null): PurchaseOrder
    {
        return $this->service->cancel($purchaseOrder, $reason);
    }
}
