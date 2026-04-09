<?php

namespace App\Http\Controllers;

use App\Http\Requests\StockTransferReceiveRequest;
use App\Http\Requests\StockTransferRequest;
use App\Models\StockTransfer;
use App\Services\RetailOperationsService;
use App\Workflows\StockTransferWorkflow;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StockTransferController extends Controller
{
    public function create(RetailOperationsService $retailOperationsService): View
    {
        return view('pages.operations.stock-transfer-form', $retailOperationsService->stockTransferFormData());
    }

    public function store(StockTransferRequest $request, StockTransferWorkflow $workflow): RedirectResponse
    {
        $transfer = $workflow->store($request->headerData(), $request->lineItems(), $request->intent());

        return redirect()->route('stock-mutation')->with('success', 'Transfer stok ' . $transfer->transfer_number . ' berhasil dibuat.');
    }

    public function edit(StockTransfer $stockTransfer, RetailOperationsService $retailOperationsService): View|RedirectResponse
    {
        if (! $stockTransfer->canBeEdited()) {
            return redirect()->route('stock-mutation')->with('error', 'Transfer stok pada status ini tidak bisa diedit lagi.');
        }

        return view('pages.operations.stock-transfer-form', $retailOperationsService->stockTransferFormData($stockTransfer));
    }

    public function update(StockTransferRequest $request, StockTransfer $stockTransfer, StockTransferWorkflow $workflow): RedirectResponse
    {
        try {
            $workflow->update($stockTransfer, $request->headerData(), $request->lineItems(), $request->intent());
        } catch (DomainException $exception) {
            return redirect()->route('stock-mutation')->with('error', $exception->getMessage());
        }

        return redirect()->route('stock-mutation')->with('success', 'Transfer stok ' . $stockTransfer->transfer_number . ' berhasil diperbarui.');
    }

    public function submit(StockTransfer $stockTransfer, StockTransferWorkflow $workflow): RedirectResponse
    {
        try {
            $workflow->submit($stockTransfer);
        } catch (DomainException $exception) {
            return redirect()->route('stock-mutation')->with('error', $exception->getMessage());
        }

        return redirect()->route('stock-mutation')->with('success', 'Transfer stok ' . $stockTransfer->transfer_number . ' berhasil dikirim ke approval.');
    }

    public function approve(StockTransfer $stockTransfer, StockTransferWorkflow $workflow): RedirectResponse
    {
        try {
            $workflow->approve($stockTransfer);
        } catch (DomainException $exception) {
            return redirect()->route('stock-mutation')->with('error', $exception->getMessage());
        }

        return redirect()->route('stock-mutation')->with('success', 'Transfer stok ' . $stockTransfer->transfer_number . ' berhasil di-approve.');
    }

    public function reject(Request $request, StockTransfer $stockTransfer, StockTransferWorkflow $workflow): RedirectResponse
    {
        $payload = $request->validate([
            'reason' => ['nullable', 'string', 'max:500'],
        ]);

        try {
            $workflow->reject($stockTransfer, $payload['reason'] ?? null);
        } catch (DomainException $exception) {
            return redirect()->route('stock-mutation')->with('error', $exception->getMessage());
        }

        return redirect()->route('stock-mutation')->with('success', 'Transfer stok ' . $stockTransfer->transfer_number . ' ditandai sebagai rejected.');
    }

    public function cancel(Request $request, StockTransfer $stockTransfer, StockTransferWorkflow $workflow): RedirectResponse
    {
        $payload = $request->validate([
            'reason' => ['nullable', 'string', 'max:500'],
        ]);

        try {
            $workflow->cancel($stockTransfer, $payload['reason'] ?? null);
        } catch (DomainException $exception) {
            return redirect()->route('stock-mutation')->with('error', $exception->getMessage());
        }

        return redirect()->route('stock-mutation')->with('success', 'Transfer stok ' . $stockTransfer->transfer_number . ' berhasil dibatalkan.');
    }

    public function receiveForm(StockTransfer $stockTransfer, RetailOperationsService $retailOperationsService): View|RedirectResponse
    {
        if (! $stockTransfer->canBeReceived()) {
            return redirect()->route('stock-mutation')->with('error', 'Transfer stok ini belum siap diterima.');
        }

        return view('pages.operations.stock-transfer-receive-form', $retailOperationsService->stockTransferReceiveFormData($stockTransfer));
    }

    public function receive(StockTransferReceiveRequest $request, StockTransfer $stockTransfer, StockTransferWorkflow $workflow): RedirectResponse
    {
        try {
            $workflow->receive($stockTransfer, $request->lineItems(), $request->validated('notes'));
        } catch (DomainException $exception) {
            return redirect()->route('stock-mutation')->with('error', $exception->getMessage());
        }

        return redirect()->route('stock-mutation')->with('success', 'Transfer stok ' . $stockTransfer->transfer_number . ' berhasil diproses ke receiving.');
    }
}
