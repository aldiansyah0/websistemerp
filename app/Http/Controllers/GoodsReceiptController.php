<?php

namespace App\Http\Controllers;

use App\Http\Requests\GoodsReceiptRequest;
use App\Models\PurchaseOrder;
use App\Services\RetailOperationsService;
use App\Workflows\GoodsReceiptWorkflow;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class GoodsReceiptController extends Controller
{
    public function create(PurchaseOrder $purchaseOrder, RetailOperationsService $retailOperationsService): View|RedirectResponse
    {
        if (! $purchaseOrder->canBeReceived()) {
            return redirect()->route('purchase-orders')->with('error', 'Purchase order ini belum siap menerima barang.');
        }

        return view('pages.operations.goods-receipt-form', $retailOperationsService->goodsReceiptFormData($purchaseOrder));
    }

    public function store(GoodsReceiptRequest $request, PurchaseOrder $purchaseOrder, GoodsReceiptWorkflow $workflow): RedirectResponse
    {
        try {
            $receipt = $workflow->receive($purchaseOrder, $request->headerData(), $request->lineItems());
        } catch (DomainException $exception) {
            return redirect()->route('purchase-orders')->with('error', $exception->getMessage());
        }

        return redirect()->route('goods-receipts')->with('success', 'Receiving ' . $receipt->receipt_number . ' berhasil diposting ke stok.');
    }
}
