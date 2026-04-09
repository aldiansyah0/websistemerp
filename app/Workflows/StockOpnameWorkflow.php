<?php

namespace App\Workflows;

use App\Models\StockOpname;
use App\Services\StockOpnameService;

class StockOpnameWorkflow
{
    public function __construct(
        private readonly StockOpnameService $service,
    ) {
    }

    public function store(array $attributes, array $items, string $intent = 'draft'): StockOpname
    {
        return $this->service->store($attributes, $items, $intent);
    }

    public function update(StockOpname $opname, array $attributes, array $items, string $intent = 'draft'): StockOpname
    {
        return $this->service->update($opname, $attributes, $items, $intent);
    }

    public function submit(StockOpname $opname): StockOpname
    {
        return $this->service->submit($opname);
    }

    public function approve(StockOpname $opname): StockOpname
    {
        return $this->service->approve($opname);
    }

    public function reject(StockOpname $opname, ?string $reason = null): StockOpname
    {
        return $this->service->reject($opname, $reason);
    }
}
