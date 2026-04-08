<?php

namespace App\Services;

use App\Models\PaymentMethod;
use App\Models\SalesTransaction;
use Carbon\CarbonImmutable;
use DomainException;
use Illuminate\Support\Facades\DB;

class SalesSettlementService
{
    public function __construct(
        private readonly AnalyticsCacheService $analyticsCacheService,
        private readonly PeriodLockService $periodLockService,
        private readonly AuditLogService $auditLogService,
    ) {
    }

    public function store(SalesTransaction $salesTransaction, array $attributes): SalesTransaction
    {
        if ((float) $salesTransaction->balance_due <= 0) {
            throw new DomainException('Invoice ini sudah lunas.');
        }

        return DB::transaction(function () use ($salesTransaction, $attributes): SalesTransaction {
            $paymentMethod = PaymentMethod::query()->findOrFail($attributes['payment_method_id']);
            $amount = (float) $attributes['amount'];

            if ($amount - (float) $salesTransaction->balance_due > 0.0001) {
                throw new DomainException('Nominal pembayaran melebihi saldo piutang invoice.');
            }

            $paymentDate = isset($attributes['payment_date'])
                ? CarbonImmutable::parse($attributes['payment_date'])
                : CarbonImmutable::now('Asia/Jakarta');
            $this->periodLockService->assertDateIsOpen($paymentDate, 'Posting pembayaran invoice penjualan');

            $salesTransaction->payments()->create([
                'tenant_id' => $salesTransaction->tenant_id,
                'location_id' => $salesTransaction->location_id,
                'payment_method_id' => $paymentMethod->id,
                'amount' => $amount,
                'reference_number' => $attributes['reference_number'] ?? null,
                'approval_code' => $attributes['approval_code'] ?? null,
                'settled_at' => $paymentDate->addDays((int) ($paymentMethod->settlement_days ?? 0)),
            ]);

            $salesTransaction->load('payments');
            $salesTransaction->recalculateSettlement($salesTransaction->payments);
            $salesTransaction->save();
            $this->analyticsCacheService->invalidate();
            $this->auditLogService->log('sales', 'invoice.settle', 'Pembayaran invoice penjualan dicatat', $salesTransaction, [
                'transaction_number' => $salesTransaction->transaction_number,
                'amount' => $amount,
                'payment_method_id' => $paymentMethod->id,
            ]);

            return $salesTransaction->fresh(['customer', 'outlet', 'payments.paymentMethod']);
        });
    }
}
