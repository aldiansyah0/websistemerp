<?php

namespace App\Http\Controllers;

use App\Http\Requests\PurchaseOrderRequest;
use App\Http\Requests\PurchaseOrderPaymentRequest;
use App\Models\PurchaseOrder;
use App\Services\PurchaseOrderPaymentService;
use App\Services\RetailOperationsService;
use App\Workflows\PurchaseOrderWorkflow;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PurchaseOrderController extends Controller
{
    public function create(): View
    {
        return view('pages.operations.livewire.purchase-order-form', [
            'title' => 'Buat Purchase Order',
            'purchaseOrderId' => null,
        ]);
    }

    public function store(PurchaseOrderRequest $request, PurchaseOrderWorkflow $workflow): RedirectResponse
    {
        $purchaseOrder = $workflow->store($request->headerData(), $request->lineItems(), $request->intent());

        return redirect()->route('purchase-orders')->with('success', 'Purchase order ' . $purchaseOrder->po_number . ' berhasil dibuat.');
    }

    public function edit(PurchaseOrder $purchaseOrder): View|RedirectResponse
    {
        if (! $purchaseOrder->canBeEdited()) {
            return redirect()->route('purchase-orders')->with('error', 'Purchase order pada status ini tidak bisa diedit lagi.');
        }

        return view('pages.operations.livewire.purchase-order-form', [
            'title' => 'Edit Purchase Order',
            'purchaseOrderId' => (int) $purchaseOrder->id,
        ]);
    }

    public function update(PurchaseOrderRequest $request, PurchaseOrder $purchaseOrder, PurchaseOrderWorkflow $workflow): RedirectResponse
    {
        try {
            $workflow->update($purchaseOrder, $request->headerData(), $request->lineItems(), $request->intent());
        } catch (DomainException $exception) {
            return redirect()->route('purchase-orders')->with('error', $exception->getMessage());
        }

        return redirect()->route('purchase-orders')->with('success', 'Purchase order ' . $purchaseOrder->po_number . ' berhasil diperbarui.');
    }

    public function submit(PurchaseOrder $purchaseOrder, PurchaseOrderWorkflow $workflow): RedirectResponse
    {
        try {
            $workflow->submit($purchaseOrder);
        } catch (DomainException $exception) {
            return redirect()->route('purchase-orders')->with('error', $exception->getMessage());
        }

        return redirect()->route('purchase-orders')->with('success', 'Purchase order ' . $purchaseOrder->po_number . ' berhasil dikirim ke approval.');
    }

    public function approve(PurchaseOrder $purchaseOrder, PurchaseOrderWorkflow $workflow): RedirectResponse
    {
        try {
            $workflow->approve($purchaseOrder);
        } catch (DomainException $exception) {
            return redirect()->route('purchase-orders')->with('error', $exception->getMessage());
        }

        return redirect()->route('purchase-orders')->with('success', 'Purchase order ' . $purchaseOrder->po_number . ' berhasil di-approve.');
    }

    public function reject(Request $request, PurchaseOrder $purchaseOrder, PurchaseOrderWorkflow $workflow): RedirectResponse
    {
        $payload = $request->validate([
            'reason' => ['nullable', 'string', 'max:500'],
        ]);

        try {
            $workflow->reject($purchaseOrder, $payload['reason'] ?? null);
        } catch (DomainException $exception) {
            return redirect()->route('purchase-orders')->with('error', $exception->getMessage());
        }

        return redirect()->route('purchase-orders')->with('success', 'Purchase order ' . $purchaseOrder->po_number . ' ditandai sebagai rejected.');
    }

    public function cancel(Request $request, PurchaseOrder $purchaseOrder, PurchaseOrderWorkflow $workflow): RedirectResponse
    {
        $payload = $request->validate([
            'reason' => ['nullable', 'string', 'max:500'],
        ]);

        try {
            $workflow->cancel($purchaseOrder, $payload['reason'] ?? null);
        } catch (DomainException $exception) {
            return redirect()->route('purchase-orders')->with('error', $exception->getMessage());
        }

        return redirect()->route('purchase-orders')->with('success', 'Purchase order ' . $purchaseOrder->po_number . ' berhasil dibatalkan.');
    }

    public function paymentForm(PurchaseOrder $purchaseOrder, RetailOperationsService $retailOperationsService): View|RedirectResponse
    {
        if ((float) $purchaseOrder->balance_due <= 0) {
            return redirect()->route('receivables-payables')->with('error', 'Purchase order ini sudah lunas.');
        }

        return view('pages.operations.purchase-order-payment-form', $retailOperationsService->purchaseOrderPaymentFormData($purchaseOrder));
    }

    public function storePayment(PurchaseOrderPaymentRequest $request, PurchaseOrder $purchaseOrder, PurchaseOrderPaymentService $paymentService): RedirectResponse
    {
        try {
            $paymentService->store($purchaseOrder, $request->validated());
        } catch (DomainException $exception) {
            return redirect()->route('receivables-payables')->with('error', $exception->getMessage());
        }

        return redirect()->route('receivables-payables')->with('success', 'Pembayaran supplier untuk ' . $purchaseOrder->po_number . ' berhasil dicatat.');
    }
}
