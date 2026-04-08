<?php

namespace App\Services;

use App\Models\CashReconciliation;
use App\Models\PayrollRun;
use App\Models\PurchaseOrderPayment;
use App\Models\SalesPayment;
use Carbon\CarbonImmutable;
use DomainException;
use Illuminate\Support\Facades\DB;

class CashReconciliationService
{
    public function __construct(
        private readonly AuditLogService $auditLogService,
    ) {
    }

    /**
     * @param array<string, mixed> $attributes
     */
    public function store(array $attributes): CashReconciliation
    {
        $user = auth()->user();
        $locationId = $attributes['location_id'] ?? $user?->location_id;
        $tenantId = $user?->tenant_id;

        if ($locationId === null) {
            throw new DomainException('Lokasi rekonsiliasi harus ditentukan.');
        }

        $date = CarbonImmutable::parse((string) $attributes['reconciliation_date'])->toDateString();
        $openingBalance = (float) $attributes['opening_balance'];
        $countedEnding = (float) $attributes['counted_ending_balance'];

        return DB::transaction(function () use ($attributes, $tenantId, $locationId, $date, $openingBalance, $countedEnding): CashReconciliation {
            $expectedInflows = $this->expectedInflows((int) $locationId, $date);
            $expectedOutflows = $this->expectedOutflows((int) $locationId, $date);
            $expectedEnding = $openingBalance + $expectedInflows - $expectedOutflows;
            $difference = $countedEnding - $expectedEnding;

            $reconciliation = CashReconciliation::query()->withoutTenantLocation()->updateOrCreate(
                [
                    'location_id' => (int) $locationId,
                    'reconciliation_date' => $date,
                ],
                [
                    'tenant_id' => $tenantId,
                    'opening_balance' => $openingBalance,
                    'expected_inflows' => $expectedInflows,
                    'expected_outflows' => $expectedOutflows,
                    'expected_ending_balance' => $expectedEnding,
                    'counted_ending_balance' => $countedEnding,
                    'difference_amount' => $difference,
                    'status' => CashReconciliation::STATUS_DRAFT,
                    'submitted_at' => null,
                    'approved_at' => null,
                    'approved_by' => null,
                    'notes' => $attributes['notes'] ?? null,
                ]
            );

            $this->auditLogService->log('finance', 'cash_reconciliation.store', 'Draft rekonsiliasi kas disimpan', $reconciliation, [
                'reconciliation_date' => $date,
                'expected_inflows' => $expectedInflows,
                'expected_outflows' => $expectedOutflows,
                'difference_amount' => $difference,
            ]);

            return $reconciliation;
        });
    }

    public function submit(CashReconciliation $cashReconciliation): CashReconciliation
    {
        if ($cashReconciliation->status !== CashReconciliation::STATUS_DRAFT) {
            throw new DomainException('Hanya draft rekonsiliasi kas yang bisa disubmit.');
        }

        $cashReconciliation->status = CashReconciliation::STATUS_SUBMITTED;
        $cashReconciliation->submitted_at = now('Asia/Jakarta');
        $cashReconciliation->save();

        $this->auditLogService->log('finance', 'cash_reconciliation.submit', 'Rekonsiliasi kas disubmit', $cashReconciliation, [
            'status' => $cashReconciliation->status,
        ]);

        return $cashReconciliation;
    }

    public function approve(CashReconciliation $cashReconciliation): CashReconciliation
    {
        if (! in_array($cashReconciliation->status, [CashReconciliation::STATUS_SUBMITTED, CashReconciliation::STATUS_DRAFT], true)) {
            throw new DomainException('Status rekonsiliasi kas tidak bisa di-approve.');
        }

        $cashReconciliation->status = CashReconciliation::STATUS_APPROVED;
        $cashReconciliation->approved_at = now('Asia/Jakarta');
        $cashReconciliation->approved_by = auth()->id();
        $cashReconciliation->save();

        $this->auditLogService->log('finance', 'cash_reconciliation.approve', 'Rekonsiliasi kas disetujui', $cashReconciliation, [
            'status' => $cashReconciliation->status,
            'difference_amount' => (float) $cashReconciliation->difference_amount,
        ]);

        return $cashReconciliation;
    }

    public function reject(CashReconciliation $cashReconciliation, ?string $reason = null): CashReconciliation
    {
        if (! in_array($cashReconciliation->status, [CashReconciliation::STATUS_SUBMITTED, CashReconciliation::STATUS_DRAFT], true)) {
            throw new DomainException('Status rekonsiliasi kas tidak bisa ditolak.');
        }

        $cashReconciliation->status = CashReconciliation::STATUS_REJECTED;
        $cashReconciliation->approved_at = null;
        $cashReconciliation->approved_by = null;
        $cashReconciliation->notes = trim((string) $cashReconciliation->notes . PHP_EOL . '[Rejected] ' . trim((string) $reason));
        $cashReconciliation->save();

        $this->auditLogService->log('finance', 'cash_reconciliation.reject', 'Rekonsiliasi kas ditolak', $cashReconciliation, [
            'status' => $cashReconciliation->status,
            'reason' => $reason,
        ]);

        return $cashReconciliation;
    }

    private function expectedInflows(int $locationId, string $date): float
    {
        return (float) SalesPayment::query()
            ->where('location_id', $locationId)
            ->whereDate('settled_at', $date)
            ->sum('amount');
    }

    private function expectedOutflows(int $locationId, string $date): float
    {
        $purchasePayments = (float) PurchaseOrderPayment::query()
            ->whereDate('payment_date', $date)
            ->whereHas('purchaseOrder', fn ($query) => $query->where('location_id', $locationId))
            ->sum('amount');

        $payrollPayments = (float) PayrollRun::query()
            ->where('location_id', $locationId)
            ->where('status', PayrollRun::STATUS_PAID)
            ->whereDate('paid_at', $date)
            ->sum('total_net');

        return $purchasePayments + $payrollPayments;
    }
}

