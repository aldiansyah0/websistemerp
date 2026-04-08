<?php

namespace App\Http\Controllers;

use App\Http\Requests\PayrollGenerateRequest;
use App\Models\PayrollRun;
use App\Services\PayrollCalculationService;
use App\Services\PayrollWorkflowService;
use DomainException;
use Illuminate\Http\RedirectResponse;

class PayrollController extends Controller
{
    public function generate(PayrollGenerateRequest $request, PayrollCalculationService $payrollCalculationService): RedirectResponse
    {
        try {
            $payrollRun = $payrollCalculationService->generate($request->validated());
        } catch (DomainException $exception) {
            return redirect()->route('payroll-list')->with('error', $exception->getMessage());
        }

        return redirect()
            ->route('payroll-list')
            ->with('success', 'Payroll otomatis ' . $payrollRun->code . ' berhasil digenerate dari data POS dan absensi.');
    }

    public function submit(PayrollRun $payrollRun, PayrollWorkflowService $workflow): RedirectResponse
    {
        try {
            $workflow->submit($payrollRun);
        } catch (DomainException $exception) {
            return redirect()->route('payroll-list')->with('error', $exception->getMessage());
        }

        return redirect()->route('payroll-list')->with('success', 'Payroll ' . $payrollRun->code . ' berhasil dikirim ke proses approval.');
    }

    public function approve(PayrollRun $payrollRun, PayrollWorkflowService $workflow): RedirectResponse
    {
        try {
            $workflow->approve($payrollRun);
        } catch (DomainException $exception) {
            return redirect()->route('payroll-list')->with('error', $exception->getMessage());
        }

        return redirect()->route('payroll-list')->with('success', 'Payroll ' . $payrollRun->code . ' berhasil di-approve finance.');
    }

    public function pay(PayrollRun $payrollRun, PayrollWorkflowService $workflow): RedirectResponse
    {
        try {
            $workflow->pay($payrollRun);
        } catch (DomainException $exception) {
            return redirect()->route('payroll-list')->with('error', $exception->getMessage());
        }

        return redirect()->route('payroll-list')->with('success', 'Payroll ' . $payrollRun->code . ' berhasil ditandai paid.');
    }
}
