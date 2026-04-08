<?php

namespace App\Http\Controllers;

use App\Http\Requests\SalesTransactionRequest;
use App\Http\Requests\SalesSettlementRequest;
use App\Models\SalesTransaction;
use App\Services\RetailOperationsService;
use App\Services\SalesSettlementService;
use App\Services\SalesTransactionService;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SalesTransactionController extends Controller
{
    public function create(): View
    {
        return view('pages.operations.livewire.pos-transaction-form', [
            'title' => 'Buat Transaksi POS',
        ]);
    }

    public function store(SalesTransactionRequest $request, SalesTransactionService $salesTransactionService): RedirectResponse
    {
        try {
            $transaction = $salesTransactionService->store(
                $request->headerData(),
                $request->lineItems(),
                $request->paymentLines(),
            );
        } catch (DomainException $exception) {
            return redirect()->route('pos-transactions')->with('error', $exception->getMessage());
        }

        return redirect()->route('pos-transactions')->with('success', 'Transaksi POS ' . $transaction->transaction_number . ' berhasil diposting.');
    }

    public function invoicePaymentForm(SalesTransaction $salesTransaction, RetailOperationsService $retailOperationsService): View|RedirectResponse
    {
        if ((float) $salesTransaction->balance_due <= 0) {
            return redirect()->route('sales-invoices')->with('error', 'Invoice ini sudah lunas.');
        }

        return view('pages.operations.sales-invoice-payment-form', $retailOperationsService->salesInvoicePaymentFormData($salesTransaction));
    }

    public function storeInvoicePayment(SalesSettlementRequest $request, SalesTransaction $salesTransaction, SalesSettlementService $settlementService): RedirectResponse
    {
        try {
            $settlementService->store($salesTransaction, $request->validated());
        } catch (DomainException $exception) {
            return redirect()->route('sales-invoices')->with('error', $exception->getMessage());
        }

        return redirect()->route('sales-invoices')->with('success', 'Pembayaran invoice ' . ($salesTransaction->invoice_number ?? $salesTransaction->transaction_number) . ' berhasil dicatat.');
    }
}
