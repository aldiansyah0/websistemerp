<?php

namespace App\Http\Controllers;

use App\Http\Requests\StockOpnameRequest;
use App\Models\StockOpname;
use App\Services\RetailOperationsService;
use App\Services\StockOpnameWorkflowService;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StockOpnameController extends Controller
{
    public function create(RetailOperationsService $retailOperationsService): View
    {
        return view('pages.operations.stock-opname-form', $retailOperationsService->stockOpnameFormData());
    }

    public function store(StockOpnameRequest $request, StockOpnameWorkflowService $workflow): RedirectResponse
    {
        try {
            $opname = $workflow->store($request->headerData(), $request->lineItems(), $request->intent());
        } catch (DomainException $exception) {
            return redirect()->route('stock-opname')->with('error', $exception->getMessage());
        }

        return redirect()->route('stock-opname')->with('success', 'Stock opname ' . $opname->opname_number . ' berhasil dibuat.');
    }

    public function edit(StockOpname $stockOpname, RetailOperationsService $retailOperationsService): View|RedirectResponse
    {
        if (! $stockOpname->canBeEdited()) {
            return redirect()->route('stock-opname')->with('error', 'Stock opname pada status ini tidak bisa diedit.');
        }

        return view('pages.operations.stock-opname-form', $retailOperationsService->stockOpnameFormData($stockOpname));
    }

    public function update(StockOpnameRequest $request, StockOpname $stockOpname, StockOpnameWorkflowService $workflow): RedirectResponse
    {
        try {
            $workflow->update($stockOpname, $request->headerData(), $request->lineItems(), $request->intent());
        } catch (DomainException $exception) {
            return redirect()->route('stock-opname')->with('error', $exception->getMessage());
        }

        return redirect()->route('stock-opname')->with('success', 'Stock opname ' . $stockOpname->opname_number . ' berhasil diperbarui.');
    }

    public function submit(StockOpname $stockOpname, StockOpnameWorkflowService $workflow): RedirectResponse
    {
        try {
            $workflow->submit($stockOpname);
        } catch (DomainException $exception) {
            return redirect()->route('stock-opname')->with('error', $exception->getMessage());
        }

        return redirect()->route('stock-opname')->with('success', 'Stock opname ' . $stockOpname->opname_number . ' berhasil disubmit.');
    }

    public function approve(StockOpname $stockOpname, StockOpnameWorkflowService $workflow): RedirectResponse
    {
        try {
            $workflow->approve($stockOpname);
        } catch (DomainException $exception) {
            return redirect()->route('stock-opname')->with('error', $exception->getMessage());
        }

        return redirect()->route('stock-opname')->with('success', 'Stock opname ' . $stockOpname->opname_number . ' berhasil di-approve.');
    }

    public function reject(Request $request, StockOpname $stockOpname, StockOpnameWorkflowService $workflow): RedirectResponse
    {
        $payload = $request->validate([
            'reason' => ['nullable', 'string', 'max:500'],
        ]);

        try {
            $workflow->reject($stockOpname, $payload['reason'] ?? null);
        } catch (DomainException $exception) {
            return redirect()->route('stock-opname')->with('error', $exception->getMessage());
        }

        return redirect()->route('stock-opname')->with('success', 'Stock opname ' . $stockOpname->opname_number . ' berhasil ditolak.');
    }
}

