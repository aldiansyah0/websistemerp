<?php

namespace App\Workflows;

use App\Models\StockTransfer;
use App\Services\StockTransferService;

class StockTransferWorkflow
{
    public function __construct(
        private readonly StockTransferService $service,
    ) {
    }

    public function store(array $attributes, array $items, string $intent = 'draft'): StockTransfer
    {
        return $this->service->store($attributes, $items, $intent);
    }

    public function update(StockTransfer $transfer, array $attributes, array $items, string $intent = 'draft'): StockTransfer
    {
        return $this->service->update($transfer, $attributes, $items, $intent);
    }

    public function submit(StockTransfer $transfer): StockTransfer
    {
        return $this->service->submit($transfer);
    }

    public function approve(StockTransfer $transfer): StockTransfer
    {
        return $this->service->approve($transfer);
    }

    public function reject(StockTransfer $transfer, ?string $reason = null): StockTransfer
    {
        return $this->service->reject($transfer, $reason);
    }

    public function cancel(StockTransfer $transfer, ?string $reason = null): StockTransfer
    {
        return $this->service->cancel($transfer, $reason);
    }

    public function receive(StockTransfer $transfer, array $items, ?string $notes = null): StockTransfer
    {
        return $this->service->receive($transfer, $items, $notes);
    }
}
