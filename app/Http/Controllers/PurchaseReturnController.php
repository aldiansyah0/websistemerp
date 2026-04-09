<?php

namespace App\Http\Controllers;

use App\Http\Requests\PurchaseReturnRequest;
use App\Models\PurchaseReturn;
use App\Services\RetailOperationsService;
use App\Workflows\PurchaseReturnWorkflow;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PurchaseReturnController extends Controller
{
    public function create(RetailOperationsService $retailOperationsService): View
    {
        return view('pages.operations.purchase-return-form', $retailOperationsService->purchaseReturnFormData());
    }

    public function store(PurchaseReturnRequest $request, PurchaseReturnWorkflow $workflow): RedirectResponse
    {
        try {
            $purchaseReturn = $workflow->store(
                attributes: $request->headerData(),
                items: $request->lineItems(),
                intent: $request->intent(),
            );
        } catch (DomainException $exception) {
            return redirect()->route('purchase-return')->with('error', $exception->getMessage());
        }

        return redirect()->route('purchase-return')->with('success', 'Retur pembelian ' . $purchaseReturn->return_number . ' berhasil dibuat.');
    }

    public function submit(PurchaseReturn $purchaseReturn, PurchaseReturnWorkflow $workflow): RedirectResponse
    {
        try {
            $workflow->submit($purchaseReturn);
        } catch (DomainException $exception) {
            return redirect()->route('purchase-return')->with('error', $exception->getMessage());
        }

        return redirect()->route('purchase-return')->with('success', 'Retur pembelian ' . $purchaseReturn->return_number . ' berhasil disubmit.');
    }

    public function approve(PurchaseReturn $purchaseReturn, PurchaseReturnWorkflow $workflow): RedirectResponse
    {
        try {
            $workflow->approve($purchaseReturn);
        } catch (DomainException $exception) {
            return redirect()->route('purchase-return')->with('error', $exception->getMessage());
        }

        return redirect()->route('purchase-return')->with('success', 'Retur pembelian ' . $purchaseReturn->return_number . ' berhasil di-approve.');
    }

    public function reject(Request $request, PurchaseReturn $purchaseReturn, PurchaseReturnWorkflow $workflow): RedirectResponse
    {
        $payload = $request->validate([
            'reason' => ['nullable', 'string', 'max:500'],
        ]);

        try {
            $workflow->reject($purchaseReturn, $payload['reason'] ?? null);
        } catch (DomainException $exception) {
            return redirect()->route('purchase-return')->with('error', $exception->getMessage());
        }

        return redirect()->route('purchase-return')->with('success', 'Retur pembelian ' . $purchaseReturn->return_number . ' berhasil ditolak.');
    }
}
