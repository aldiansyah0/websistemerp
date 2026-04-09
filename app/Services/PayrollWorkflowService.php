<?php

namespace App\Services;

use App\Models\PayrollRun;
use Carbon\Carbon;
use DomainException;
use Illuminate\Support\Facades\DB;

class PayrollWorkflowService
{
    public function submit(PayrollRun $payrollRun): PayrollRun
    {
        if ($payrollRun->status !== PayrollRun::STATUS_DRAFT) {
            throw new DomainException('Hanya payroll draft yang bisa diproses ke approval.');
        }

        $payrollRun->status = PayrollRun::STATUS_PROCESSING;
        $payrollRun->processed_at = Carbon::now();
        $payrollRun->save();

        return $payrollRun;
    }

    public function approve(PayrollRun $payrollRun): PayrollRun
    {
        if (! in_array($payrollRun->status, [PayrollRun::STATUS_DRAFT, PayrollRun::STATUS_PROCESSING], true)) {
            throw new DomainException('Payroll run ini tidak berada pada tahap yang bisa di-approve.');
        }

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

            return $payrollRun->fresh('items');
        });
    }

    public function pay(PayrollRun $payrollRun): PayrollRun
    {
        if ($payrollRun->status !== PayrollRun::STATUS_APPROVED) {
            throw new DomainException('Payroll run harus approved sebelum ditandai paid.');
        }

        return DB::transaction(function () use ($payrollRun): PayrollRun {
            $payrollRun->status = PayrollRun::STATUS_PAID;
            $payrollRun->paid_at = Carbon::now();
            $payrollRun->save();

            $payrollRun->items()->update([
                'payment_status' => 'paid',
            ]);

            return $payrollRun->fresh('items');
        });
    }
}
