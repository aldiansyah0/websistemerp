<?php

namespace App\Workflows;

use App\Models\PurchaseReturn;
use App\Services\PurchaseReturnService;

class PurchaseReturnWorkflow
{
    public function __construct(
        private readonly PurchaseReturnService $service,
    ) {
    }

    public function store(array $attributes, array $items, string $intent = 'submit'): PurchaseReturn
    {
        return $this->service->store($attributes, $items, $intent);
    }

    public function submit(PurchaseReturn $purchaseReturn): PurchaseReturn
    {
        return $this->service->submit($purchaseReturn);
    }

    public function approve(PurchaseReturn $purchaseReturn): PurchaseReturn
    {
        return $this->service->approve($purchaseReturn);
    }

    public function reject(PurchaseReturn $purchaseReturn, ?string $reason = null): PurchaseReturn
    {
        return $this->service->reject($purchaseReturn, $reason);
    }
}
