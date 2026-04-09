<?php

namespace App\Http\Controllers;

use App\Http\Requests\SalesReturnRequest;
use App\Models\SalesReturn;
use App\Models\SalesTransaction;
use App\Services\RetailOperationsService;
use App\Workflows\SalesReturnWorkflow;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SalesReturnController extends Controller
{
    public function create(SalesTransaction $salesTransaction, RetailOperationsService $retailOperationsService): View
    {
        return view('pages.operations.sales-return-form', $retailOperationsService->salesReturnFormData($salesTransaction));
    }

    public function store(SalesReturnRequest $request, SalesTransaction $salesTransaction, SalesReturnWorkflow $workflow): RedirectResponse
    {
        try {
            $salesReturn = $workflow->store(
                salesTransaction: $salesTransaction,
                attributes: $request->headerData(),
                items: $request->lineItems(),
                intent: $request->intent(),
            );
        } catch (DomainException $exception) {
            return redirect()->route('sales-return')->with('error', $exception->getMessage());
        }

        return redirect()->route('sales-return')->with('success', 'Retur penjualan ' . $salesReturn->return_number . ' berhasil dibuat.');
    }

    public function submit(SalesReturn $salesReturn, SalesReturnWorkflow $workflow): RedirectResponse
    {
        try {
            $workflow->submit($salesReturn);
        } catch (DomainException $exception) {
            return redirect()->route('sales-return')->with('error', $exception->getMessage());
        }

        return redirect()->route('sales-return')->with('success', 'Retur penjualan ' . $salesReturn->return_number . ' berhasil disubmit.');
    }

    public function approve(SalesReturn $salesReturn, SalesReturnWorkflow $workflow): RedirectResponse
    {
        try {
            $workflow->approve($salesReturn);
        } catch (DomainException $exception) {
            return redirect()->route('sales-return')->with('error', $exception->getMessage());
        }

        return redirect()->route('sales-return')->with('success', 'Retur penjualan ' . $salesReturn->return_number . ' berhasil di-approve.');
    }

    public function reject(Request $request, SalesReturn $salesReturn, SalesReturnWorkflow $workflow): RedirectResponse
    {
        $payload = $request->validate([
            'reason' => ['nullable', 'string', 'max:500'],
        ]);

        try {
            $workflow->reject($salesReturn, $payload['reason'] ?? null);
        } catch (DomainException $exception) {
            return redirect()->route('sales-return')->with('error', $exception->getMessage());
        }

        return redirect()->route('sales-return')->with('success', 'Retur penjualan ' . $salesReturn->return_number . ' berhasil ditolak.');
    }
}
