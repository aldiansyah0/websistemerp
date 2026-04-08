<?php

namespace App\Services;

use App\Models\PaymentMethod;
use App\Models\PurchaseOrder;
use Carbon\CarbonImmutable;
use DomainException;
use Illuminate\Support\Facades\DB;

class PurchaseOrderPaymentService
{
    public function __construct(
        private readonly AnalyticsCacheService $analyticsCacheService,
        private readonly PeriodLockService $periodLockService,
        private readonly AuditLogService $auditLogService,
    ) {
    }

    public function store(PurchaseOrder $purchaseOrder, array $attributes): PurchaseOrder
    {
        if ((float) $purchaseOrder->balance_due <= 0) {
            throw new DomainException('Purchase order ini sudah lunas.');
        }

        return DB::transaction(function () use ($purchaseOrder, $attributes): PurchaseOrder {
            $paymentMethod = PaymentMethod::query()->findOrFail($attributes['payment_method_id']);
            $amount = (float) $attributes['amount'];

            if ($amount - (float) $purchaseOrder->balance_due > 0.0001) {
                throw new DomainException('Nominal pembayaran melebihi saldo hutang purchase order.');
            }

            $paymentDate = isset($attributes['payment_date'])
                ? CarbonImmutable::parse($attributes['payment_date'])
                : CarbonImmutable::now('Asia/Jakarta');
            $this->periodLockService->assertDateIsOpen($paymentDate, 'Posting pembayaran hutang supplier');

            $purchaseOrder->payments()->create([
                'payment_method_id' => $paymentMethod->id,
                'payment_date' => $paymentDate,
                'amount' => $amount,
                'reference_number' => $attributes['reference_number'] ?? null,
                'approval_code' => $attributes['approval_code'] ?? null,
                'notes' => $attributes['notes'] ?? null,
            ]);

            if (! empty($attributes['supplier_invoice_number']) && blank($purchaseOrder->supplier_invoice_number)) {
                $purchaseOrder->supplier_invoice_number = $attributes['supplier_invoice_number'];
            }

            $paidAmount = (float) $purchaseOrder->payments()->sum('amount');
            $purchaseOrder->paid_amount = $paidAmount;
            $purchaseOrder->balance_due = max((float) $purchaseOrder->total_amount - $paidAmount, 0);
            $purchaseOrder->payment_status = match (true) {
                (float) $purchaseOrder->balance_due <= 0.0001 => 'paid',
                $paidAmount > 0 => 'partial',
                default => 'unpaid',
            };
            $purchaseOrder->save();
            $this->analyticsCacheService->invalidate();
            $this->auditLogService->log('procurement', 'purchase_order.payment', 'Pembayaran supplier dicatat', $purchaseOrder, [
                'po_number' => $purchaseOrder->po_number,
                'amount' => $amount,
                'payment_method_id' => $paymentMethod->id,
            ]);

            return $purchaseOrder->fresh(['supplier', 'warehouse', 'payments.paymentMethod']);
        });
    }
}
