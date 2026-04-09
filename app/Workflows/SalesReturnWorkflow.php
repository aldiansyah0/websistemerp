<?php

namespace App\Workflows;

use App\Models\SalesReturn;
use App\Models\SalesTransaction;
use App\Services\SalesReturnService;

class SalesReturnWorkflow
{
    public function __construct(
        private readonly SalesReturnService $service,
    ) {
    }

    public function store(SalesTransaction $salesTransaction, array $attributes, array $items, string $intent = 'submit'): SalesReturn
    {
        return $this->service->store($salesTransaction, $attributes, $items, $intent);
    }

    public function submit(SalesReturn $salesReturn): SalesReturn
    {
        return $this->service->submit($salesReturn);
    }

    public function approve(SalesReturn $salesReturn): SalesReturn
    {
        return $this->service->approve($salesReturn);
    }

    public function reject(SalesReturn $salesReturn, ?string $reason = null): SalesReturn
    {
        return $this->service->reject($salesReturn, $reason);
    }
}
