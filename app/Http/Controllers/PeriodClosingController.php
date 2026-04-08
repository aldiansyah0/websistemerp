<?php

namespace App\Http\Controllers;

use App\Http\Requests\PeriodClosingRequest;
use App\Models\AccountingPeriod;
use App\Services\PeriodLockService;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PeriodClosingController extends Controller
{
    public function close(PeriodClosingRequest $request, PeriodLockService $periodLockService): RedirectResponse
    {
        try {
            $period = $periodLockService->closePeriod($request->validated());
        } catch (DomainException $exception) {
            return redirect()->route('period-closing')->with('error', $exception->getMessage());
        }

        return redirect()->route('period-closing')->with('success', 'Periode ' . $period->period_code . ' berhasil ditutup.');
    }

    public function reopen(Request $request, AccountingPeriod $accountingPeriod, PeriodLockService $periodLockService): RedirectResponse
    {
        $payload = $request->validate([
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        try {
            $periodLockService->reopenPeriod($accountingPeriod, $payload['notes'] ?? null);
        } catch (DomainException $exception) {
            return redirect()->route('period-closing')->with('error', $exception->getMessage());
        }

        return redirect()->route('period-closing')->with('success', 'Periode ' . $accountingPeriod->period_code . ' berhasil dibuka kembali.');
    }
}

