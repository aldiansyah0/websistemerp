<?php

namespace App\Services;

use App\Models\PayrollRun;
use Carbon\Carbon;
use DomainException;
use Illuminate\Support\Facades\DB;

class PayrollWorkflowService
{
    public function __construct(
        private readonly AnalyticsCacheService $analyticsCacheService,
        private readonly AccountingJournalService $accountingJournalService,
        private readonly PeriodLockService $periodLockService,
        private readonly AuditLogService $auditLogService,
    ) {
    }

    public function submit(PayrollRun $payrollRun): PayrollRun
    {
        if ($payrollRun->status !== PayrollRun::STATUS_DRAFT) {
            throw new DomainException('Hanya payroll draft yang bisa diproses ke approval.');
        }

        $payrollRun->status = PayrollRun::STATUS_PROCESSING;
        $payrollRun->processed_at = Carbon::now();
        $payrollRun->save();
        $this->analyticsCacheService->invalidate();
        $this->auditLogService->log('hr', 'payroll.submit', 'Payroll dikirim ke approval', $payrollRun, [
            'status' => $payrollRun->status,
            'code' => $payrollRun->code,
        ]);

        return $payrollRun;
    }

    public function approve(PayrollRun $payrollRun): PayrollRun
    {
        if (! in_array($payrollRun->status, [PayrollRun::STATUS_DRAFT, PayrollRun::STATUS_PROCESSING], true)) {
            throw new DomainException('Payroll run ini tidak berada pada tahap yang bisa di-approve.');
        }

        $this->periodLockService->assertDateIsOpen($payrollRun->period_end, 'Approval payroll');

        return DB::transaction(function () use ($payrollRun): PayrollRun {
            if ($payrollRun->status === PayrollRun::STATUS_DRAFT) {
                $payrollRun->processed_at = $payrollRun->processed_at ?? Carbon::now();
            }

            $payrollRun->status = PayrollRun::STATUS_APPROVED;
            $payrollRun->approved_at = Carbon::now();
            $payrollRun->save();

            $payrollRun->items()->where('payment_status', 'pending')->update([
                'payment_status' => 'approved',
            ]);
            $this->accountingJournalService->postPayrollAccrual($payrollRun, $payrollRun->approved_at);
            $this->analyticsCacheService->invalidate();
            $this->auditLogService->log('finance', 'payroll.approve', 'Payroll disetujui finance', $payrollRun, [
                'status' => $payrollRun->status,
                'code' => $payrollRun->code,
                'total_net' => (float) $payrollRun->total_net,
            ]);

            return $payrollRun->fresh('items');
        });
    }

    public function pay(PayrollRun $payrollRun): PayrollRun
    {
        if ($payrollRun->status !== PayrollRun::STATUS_APPROVED) {
            throw new DomainException('Payroll run harus approved sebelum ditandai paid.');
        }

        $this->periodLockService->assertDateIsOpen(now('Asia/Jakarta'), 'Pembayaran payroll');

        return DB::transaction(function () use ($payrollRun): PayrollRun {
            $payrollRun->status = PayrollRun::STATUS_PAID;
            $payrollRun->paid_at = Carbon::now();
            $payrollRun->save();

            $payrollRun->items()->update([
                'payment_status' => 'paid',
            ]);
            $this->accountingJournalService->postPayrollDisbursement($payrollRun, $payrollRun->paid_at);
            $this->analyticsCacheService->invalidate();
            $this->auditLogService->log('finance', 'payroll.pay', 'Payroll dibayarkan', $payrollRun, [
                'status' => $payrollRun->status,
                'code' => $payrollRun->code,
                'total_net' => (float) $payrollRun->total_net,
            ]);

            return $payrollRun->fresh('items');
        });
    }
}
