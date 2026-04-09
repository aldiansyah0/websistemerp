<?php

namespace App\Workflows;

use App\Models\GoodsReceipt;
use App\Models\PurchaseOrder;
use App\Services\GoodsReceiptService;

class GoodsReceiptWorkflow
{
    public function __construct(
        private readonly GoodsReceiptService $service,
    ) {
    }

    public function receive(PurchaseOrder $purchaseOrder, array $attributes, array $items): GoodsReceipt
    {
        return $this->service->receive($purchaseOrder, $attributes, $items);
    }
}
