<?php

namespace App\Http\Controllers;

use App\Http\Requests\PurchaseReturnRequest;
use App\Models\PurchaseReturn;
use App\Services\PurchaseReturnService;
use App\Services\RetailOperationsService;
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

    public function store(PurchaseReturnRequest $request, PurchaseReturnService $purchaseReturnService): RedirectResponse
    {
        try {
            $purchaseReturn = $purchaseReturnService->store(
                attributes: $request->headerData(),
                items: $request->lineItems(),
                intent: $request->intent(),
            );
        } catch (DomainException $exception) {
            return redirect()->route('purchase-return')->with('error', $exception->getMessage());
        }

        return redirect()->route('purchase-return')->with('success', 'Retur pembelian ' . $purchaseReturn->return_number . ' berhasil dibuat.');
    }

    public function submit(PurchaseReturn $purchaseReturn, PurchaseReturnService $purchaseReturnService): RedirectResponse
    {
        try {
            $purchaseReturnService->submit($purchaseReturn);
        } catch (DomainException $exception) {
            return redirect()->route('purchase-return')->with('error', $exception->getMessage());
        }

        return redirect()->route('purchase-return')->with('success', 'Retur pembelian ' . $purchaseReturn->return_number . ' berhasil disubmit.');
    }

    public function approve(PurchaseReturn $purchaseReturn, PurchaseReturnService $purchaseReturnService): RedirectResponse
    {
        try {
            $purchaseReturnService->approve($purchaseReturn);
        } catch (DomainException $exception) {
            return redirect()->route('purchase-return')->with('error', $exception->getMessage());
        }

        return redirect()->route('purchase-return')->with('success', 'Retur pembelian ' . $purchaseReturn->return_number . ' berhasil di-approve.');
    }

    public function reject(Request $request, PurchaseReturn $purchaseReturn, PurchaseReturnService $purchaseReturnService): RedirectResponse
    {
        $payload = $request->validate([
            'reason' => ['nullable', 'string', 'max:500'],
        ]);

        try {
            $purchaseReturnService->reject($purchaseReturn, $payload['reason'] ?? null);
        } catch (DomainException $exception) {
            return redirect()->route('purchase-return')->with('error', $exception->getMessage());
        }

        return redirect()->route('purchase-return')->with('success', 'Retur pembelian ' . $purchaseReturn->return_number . ' berhasil ditolak.');
    }
}

