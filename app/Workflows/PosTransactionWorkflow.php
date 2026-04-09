<?php

namespace App\Workflows;

use App\Models\SalesTransaction;
use App\Services\SalesTransactionService;

class PosTransactionWorkflow
{
    public function __construct(
        private readonly SalesTransactionService $service,
    ) {
    }

    public function store(array $attributes, array $items, array $payments): SalesTransaction
    {
        return $this->service->store($attributes, $items, $payments);
    }
}
