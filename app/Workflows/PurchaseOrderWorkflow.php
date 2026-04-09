<?php

namespace App\Workflows;

use App\Models\PurchaseOrder;
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
        return $this->service->approve($purchaseOrder);
    }

    public function reject(PurchaseOrder $purchaseOrder, ?string $reason = null): PurchaseOrder
    {
        return $this->service->reject($purchaseOrder, $reason);
    }

    public function cancel(PurchaseOrder $purchaseOrder, ?string $reason = null): PurchaseOrder
    {
        return $this->service->cancel($purchaseOrder, $reason);
    }
}
