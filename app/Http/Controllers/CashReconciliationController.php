<?php

namespace App\Http\Controllers;

use App\Http\Requests\CashReconciliationRequest;
use App\Models\CashReconciliation;
use App\Services\CashReconciliationService;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CashReconciliationController extends Controller
{
    public function store(CashReconciliationRequest $request, CashReconciliationService $cashReconciliationService): RedirectResponse
    {
        try {
            $reconciliation = $cashReconciliationService->store($request->validated());
        } catch (DomainException $exception) {
            return redirect()->route('cash-reconciliation')->with('error', $exception->getMessage());
        }

        return redirect()->route('cash-reconciliation')->with('success', 'Draft rekonsiliasi kas ' . $reconciliation->reconciliation_date?->format('Y-m-d') . ' berhasil disimpan.');
    }

    public function submit(CashReconciliation $cashReconciliation, CashReconciliationService $cashReconciliationService): RedirectResponse
    {
        try {
            $cashReconciliationService->submit($cashReconciliation);
        } catch (DomainException $exception) {
            return redirect()->route('cash-reconciliation')->with('error', $exception->getMessage());
        }

        return redirect()->route('cash-reconciliation')->with('success', 'Rekonsiliasi kas berhasil disubmit.');
    }

    public function approve(CashReconciliation $cashReconciliation, CashReconciliationService $cashReconciliationService): RedirectResponse
    {
        try {
            $cashReconciliationService->approve($cashReconciliation);
        } catch (DomainException $exception) {
            return redirect()->route('cash-reconciliation')->with('error', $exception->getMessage());
        }

        return redirect()->route('cash-reconciliation')->with('success', 'Rekonsiliasi kas berhasil di-approve.');
    }

    public function reject(Request $request, CashReconciliation $cashReconciliation, CashReconciliationService $cashReconciliationService): RedirectResponse
    {
        $payload = $request->validate([
            'reason' => ['nullable', 'string', 'max:500'],
        ]);

        try {
            $cashReconciliationService->reject($cashReconciliation, $payload['reason'] ?? null);
        } catch (DomainException $exception) {
            return redirect()->route('cash-reconciliation')->with('error', $exception->getMessage());
        }

        return redirect()->route('cash-reconciliation')->with('success', 'Rekonsiliasi kas berhasil ditolak.');
    }
}

