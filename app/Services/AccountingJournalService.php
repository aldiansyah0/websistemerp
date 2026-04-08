<?php

namespace App\Services;

use App\Models\AccountingJournalEntry;
use App\Models\PayrollRun;
use App\Models\PurchaseReturn;
use App\Models\SalesTransaction;
use App\Models\SalesReturn;
use Carbon\CarbonInterface;
use DomainException;
use Illuminate\Support\Facades\DB;

class AccountingJournalService
{
    public function __construct(
        private readonly FinancialStatementAggregateService $financialStatementAggregateService,
        private readonly PeriodLockService $periodLockService,
    ) {
    }

    public function postPosSale(
        SalesTransaction $transaction,
        float $cashReceived,
        float $salesAmount,
        float $costOfGoodsSold,
        CarbonInterface|string|null $entryDate = null,
    ): AccountingJournalEntry {
        $existing = AccountingJournalEntry::query()
            ->where('reference_type', 'sales_transaction')
            ->where('reference_id', $transaction->id)
            ->first();

        if ($existing !== null) {
            if ($existing->aggregated_at === null) {
                $this->financialStatementAggregateService->applyJournalEntry($existing);
            }
            return $existing->load('lines');
        }

        $lines = [
            ['account_code' => '1101', 'account_name' => 'Kas & Bank', 'debit' => $cashReceived, 'credit' => 0],
            ['account_code' => '4101', 'account_name' => 'Pendapatan Penjualan', 'debit' => 0, 'credit' => $salesAmount],
            ['account_code' => '5101', 'account_name' => 'Harga Pokok Penjualan', 'debit' => $costOfGoodsSold, 'credit' => 0],
            ['account_code' => '1201', 'account_name' => 'Persediaan Barang Dagang', 'debit' => 0, 'credit' => $costOfGoodsSold],
        ];

        return $this->postEntry(
            header: [
                'tenant_id' => $transaction->tenant_id,
                'location_id' => $transaction->location_id,
                'entry_date' => $entryDate ?? now(),
                'reference_type' => 'sales_transaction',
                'reference_id' => $transaction->id,
                'description' => 'POS ' . $transaction->transaction_number,
            ],
            lines: $lines,
            unbalancedErrorMessage: 'Jurnal akuntansi tidak balance. Posting POS dibatalkan.',
        );
    }

    public function postPayrollAccrual(PayrollRun $payrollRun, CarbonInterface|string|null $entryDate = null): AccountingJournalEntry
    {
        $existing = AccountingJournalEntry::query()
            ->where('reference_type', 'payroll_run_accrual')
            ->where('reference_id', $payrollRun->id)
            ->first();

        if ($existing !== null) {
            if ($existing->aggregated_at === null) {
                $this->financialStatementAggregateService->applyJournalEntry($existing);
            }
            return $existing->load('lines');
        }

        $netPayroll = (float) $payrollRun->total_net;
        $lines = [
            ['account_code' => '5201', 'account_name' => 'Beban Gaji', 'debit' => $netPayroll, 'credit' => 0],
            ['account_code' => '2102', 'account_name' => 'Utang Gaji', 'debit' => 0, 'credit' => $netPayroll],
        ];

        return $this->postEntry(
            header: [
                'tenant_id' => $payrollRun->tenant_id,
                'location_id' => $payrollRun->location_id,
                'entry_date' => $entryDate ?? now(),
                'reference_type' => 'payroll_run_accrual',
                'reference_id' => $payrollRun->id,
                'description' => 'Akrual payroll ' . $payrollRun->code,
            ],
            lines: $lines,
            unbalancedErrorMessage: 'Jurnal akrual payroll tidak balance. Approval payroll dibatalkan.',
        );
    }

    public function postPayrollDisbursement(PayrollRun $payrollRun, CarbonInterface|string|null $entryDate = null): AccountingJournalEntry
    {
        $existing = AccountingJournalEntry::query()
            ->where('reference_type', 'payroll_run_payment')
            ->where('reference_id', $payrollRun->id)
            ->first();

        if ($existing !== null) {
            if ($existing->aggregated_at === null) {
                $this->financialStatementAggregateService->applyJournalEntry($existing);
            }
            return $existing->load('lines');
        }

        $netPayroll = (float) $payrollRun->total_net;
        $lines = [
            ['account_code' => '2102', 'account_name' => 'Utang Gaji', 'debit' => $netPayroll, 'credit' => 0],
            ['account_code' => '1101', 'account_name' => 'Kas & Bank', 'debit' => 0, 'credit' => $netPayroll],
        ];

        return $this->postEntry(
            header: [
                'tenant_id' => $payrollRun->tenant_id,
                'location_id' => $payrollRun->location_id,
                'entry_date' => $entryDate ?? now(),
                'reference_type' => 'payroll_run_payment',
                'reference_id' => $payrollRun->id,
                'description' => 'Pembayaran payroll ' . $payrollRun->code,
            ],
            lines: $lines,
            unbalancedErrorMessage: 'Jurnal pembayaran payroll tidak balance. Proses payout dibatalkan.',
        );
    }

    public function postPosRefund(
        SalesReturn $salesReturn,
        float $refundAmount,
        float $costOfGoodsReturned,
        CarbonInterface|string|null $entryDate = null,
    ): AccountingJournalEntry {
        $existing = AccountingJournalEntry::query()
            ->where('reference_type', 'sales_return')
            ->where('reference_id', $salesReturn->id)
            ->first();

        if ($existing !== null) {
            if ($existing->aggregated_at === null) {
                $this->financialStatementAggregateService->applyJournalEntry($existing);
            }
            return $existing->load('lines');
        }

        $lines = [
            ['account_code' => '4101', 'account_name' => 'Pendapatan Penjualan', 'debit' => $refundAmount, 'credit' => 0],
            ['account_code' => '1101', 'account_name' => 'Kas & Bank', 'debit' => 0, 'credit' => $refundAmount],
            ['account_code' => '1201', 'account_name' => 'Persediaan Barang Dagang', 'debit' => $costOfGoodsReturned, 'credit' => 0],
            ['account_code' => '5101', 'account_name' => 'Harga Pokok Penjualan', 'debit' => 0, 'credit' => $costOfGoodsReturned],
        ];

        return $this->postEntry(
            header: [
                'tenant_id' => $salesReturn->tenant_id,
                'location_id' => $salesReturn->location_id,
                'entry_date' => $entryDate ?? $salesReturn->return_date ?? now(),
                'reference_type' => 'sales_return',
                'reference_id' => $salesReturn->id,
                'description' => 'Refund ' . $salesReturn->return_number,
            ],
            lines: $lines,
            unbalancedErrorMessage: 'Jurnal refund penjualan tidak balance. Approval retur dibatalkan.',
        );
    }

    public function postPurchaseReturn(
        PurchaseReturn $purchaseReturn,
        float $returnAmount,
        CarbonInterface|string|null $entryDate = null,
    ): AccountingJournalEntry {
        $existing = AccountingJournalEntry::query()
            ->where('reference_type', 'purchase_return')
            ->where('reference_id', $purchaseReturn->id)
            ->first();

        if ($existing !== null) {
            if ($existing->aggregated_at === null) {
                $this->financialStatementAggregateService->applyJournalEntry($existing);
            }
            return $existing->load('lines');
        }

        $lines = [
            ['account_code' => '2101', 'account_name' => 'Hutang Dagang', 'debit' => $returnAmount, 'credit' => 0],
            ['account_code' => '1201', 'account_name' => 'Persediaan Barang Dagang', 'debit' => 0, 'credit' => $returnAmount],
        ];

        return $this->postEntry(
            header: [
                'tenant_id' => $purchaseReturn->tenant_id,
                'location_id' => $purchaseReturn->location_id,
                'entry_date' => $entryDate ?? $purchaseReturn->return_date ?? now(),
                'reference_type' => 'purchase_return',
                'reference_id' => $purchaseReturn->id,
                'description' => 'Retur pembelian ' . $purchaseReturn->return_number,
            ],
            lines: $lines,
            unbalancedErrorMessage: 'Jurnal retur pembelian tidak balance. Approval retur dibatalkan.',
        );
    }

    /**
     * @param array<string, mixed> $header
     * @param array<int, array<string, float|int|string>> $lines
     */
    private function postEntry(array $header, array $lines, string $unbalancedErrorMessage): AccountingJournalEntry
    {
        $this->periodLockService->assertDateIsOpen($header['entry_date'] ?? now(), 'Posting jurnal akuntansi');

        /** @var AccountingJournalEntry $entry */
        $entry = DB::transaction(function () use ($header, $lines, $unbalancedErrorMessage): AccountingJournalEntry {
            $entry = AccountingJournalEntry::query()->create([
                'tenant_id' => $header['tenant_id'] ?? null,
                'location_id' => $header['location_id'] ?? null,
                'entry_number' => $this->generateEntryNumber(),
                'entry_date' => $header['entry_date'] ?? now(),
                'reference_type' => $header['reference_type'] ?? null,
                'reference_id' => $header['reference_id'] ?? null,
                'description' => $header['description'] ?? null,
                'total_debit' => 0,
                'total_credit' => 0,
                'aggregated_at' => null,
            ]);

            foreach ($lines as $lineNo => $line) {
                $entry->lines()->create($line + ['line_no' => $lineNo + 1]);
            }

            $entry->load('lines');
            $entry->total_debit = (float) $entry->lines->sum('debit');
            $entry->total_credit = (float) $entry->lines->sum('credit');

            if (abs((float) $entry->total_debit - (float) $entry->total_credit) > 0.01) {
                throw new DomainException($unbalancedErrorMessage);
            }

            $entry->save();
            $this->financialStatementAggregateService->applyJournalEntry($entry);

            return $entry->fresh('lines');
        });

        return $entry;
    }

    private function generateEntryNumber(): string
    {
        $prefix = 'JV-' . now('Asia/Jakarta')->format('Ymd');
        $latest = AccountingJournalEntry::query()
            ->where('entry_number', 'like', $prefix . '-%')
            ->orderByDesc('entry_number')
            ->value('entry_number');

        $last = $latest ? (int) substr($latest, -4) : 0;

        return sprintf('%s-%04d', $prefix, $last + 1);
    }
}
