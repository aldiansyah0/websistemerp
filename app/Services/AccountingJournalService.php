<?php

namespace App\Services;

use App\Models\AccountMapping;
use App\Models\AccountingJournalEntry;
use App\Models\GoodsReceipt;
use App\Models\PayrollRun;
use App\Models\PurchaseOrder;
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

        $tenantId = (int) $transaction->tenant_id;

        // Get GL accounts from mapping
        $cashAccount = AccountMapping::getAccount($tenantId, 'sales_transaction', 'sales_cash');
        $revenueAccount = AccountMapping::getAccount($tenantId, 'sales_transaction', 'sales_revenue');
        $cogsAccount = AccountMapping::getAccount($tenantId, 'sales_transaction', 'sales_cogs');
        $inventoryAccount = AccountMapping::getAccount($tenantId, 'sales_transaction', 'sales_inventory');

        if (!$cashAccount || !$revenueAccount || !$cogsAccount || !$inventoryAccount) {
            throw new DomainException('GL account mapping untuk sales_transaction belum dikonfigurasi. Hubungi finance untuk setup account mapping.');
        }

        $lines = [
            [
                'gl_account_id' => $cashAccount->id,
                'account_code' => $cashAccount->account_code,
                'account_name' => $cashAccount->account_name,
                'debit' => $cashReceived,
                'credit' => 0,
            ],
            [
                'gl_account_id' => $revenueAccount->id,
                'account_code' => $revenueAccount->account_code,
                'account_name' => $revenueAccount->account_name,
                'debit' => 0,
                'credit' => $salesAmount,
            ],
            [
                'gl_account_id' => $cogsAccount->id,
                'account_code' => $cogsAccount->account_code,
                'account_name' => $cogsAccount->account_name,
                'debit' => $costOfGoodsSold,
                'credit' => 0,
            ],
            [
                'gl_account_id' => $inventoryAccount->id,
                'account_code' => $inventoryAccount->account_code,
                'account_name' => $inventoryAccount->account_name,
                'debit' => 0,
                'credit' => $costOfGoodsSold,
            ],
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

        $tenantId = (int) $payrollRun->tenant_id;
        $netPayroll = (float) $payrollRun->total_net;

        // Get GL accounts from mapping
        $expenseAccount = AccountMapping::getAccount($tenantId, 'payroll', 'payroll_expense');
        $payableAccount = AccountMapping::getAccount($tenantId, 'payroll', 'payroll_payable');

        if (!$expenseAccount || !$payableAccount) {
            throw new DomainException('GL account mapping untuk payroll belum dikonfigurasi. Hubungi finance untuk setup account mapping.');
        }

        $lines = [
            [
                'gl_account_id' => $expenseAccount->id,
                'account_code' => $expenseAccount->account_code,
                'account_name' => $expenseAccount->account_name,
                'debit' => $netPayroll,
                'credit' => 0,
            ],
            [
                'gl_account_id' => $payableAccount->id,
                'account_code' => $payableAccount->account_code,
                'account_name' => $payableAccount->account_name,
                'debit' => 0,
                'credit' => $netPayroll,
            ],
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

        $tenantId = (int) $payrollRun->tenant_id;
        $netPayroll = (float) $payrollRun->total_net;

        // Get GL accounts from mapping
        $payableAccount = AccountMapping::getAccount($tenantId, 'payroll', 'payroll_payable');
        $cashAccount = AccountMapping::getAccount($tenantId, 'payroll', 'payroll_cash');

        if (!$payableAccount || !$cashAccount) {
            throw new DomainException('GL account mapping untuk payroll cash belum dikonfigurasi. Hubungi finance untuk setup account mapping.');
        }

        $lines = [
            [
                'gl_account_id' => $payableAccount->id,
                'account_code' => $payableAccount->account_code,
                'account_name' => $payableAccount->account_name,
                'debit' => $netPayroll,
                'credit' => 0,
            ],
            [
                'gl_account_id' => $cashAccount->id,
                'account_code' => $cashAccount->account_code,
                'account_name' => $cashAccount->account_name,
                'debit' => 0,
                'credit' => $netPayroll,
            ],
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

        $tenantId = (int) $salesReturn->tenant_id;

        // Get GL accounts from mapping
        $revenueAccount = AccountMapping::getAccount($tenantId, 'sales_return', 'return_revenue');
        $cashAccount = AccountMapping::getAccount($tenantId, 'sales_return', 'return_cash');
        $inventoryAccount = AccountMapping::getAccount($tenantId, 'sales_return', 'return_inventory');
        $cogsAccount = AccountMapping::getAccount($tenantId, 'sales_return', 'return_cogs');

        if (!$revenueAccount || !$cashAccount || !$inventoryAccount || !$cogsAccount) {
            throw new DomainException('GL account mapping untuk sales_return belum dikonfigurasi. Hubungi finance untuk setup account mapping.');
        }

        $lines = [
            [
                'gl_account_id' => $revenueAccount->id,
                'account_code' => $revenueAccount->account_code,
                'account_name' => $revenueAccount->account_name,
                'debit' => $refundAmount,
                'credit' => 0,
            ],
            [
                'gl_account_id' => $cashAccount->id,
                'account_code' => $cashAccount->account_code,
                'account_name' => $cashAccount->account_name,
                'debit' => 0,
                'credit' => $refundAmount,
            ],
            [
                'gl_account_id' => $inventoryAccount->id,
                'account_code' => $inventoryAccount->account_code,
                'account_name' => $inventoryAccount->account_name,
                'debit' => $costOfGoodsReturned,
                'credit' => 0,
            ],
            [
                'gl_account_id' => $cogsAccount->id,
                'account_code' => $cogsAccount->account_code,
                'account_name' => $cogsAccount->account_name,
                'debit' => 0,
                'credit' => $costOfGoodsReturned,
            ],
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

        $tenantId = (int) $purchaseReturn->tenant_id;

        // Get GL accounts from mapping
        $apAccount = AccountMapping::getAccount($tenantId, 'purchase_return', 'pr_supplier');
        $inventoryAccount = AccountMapping::getAccount($tenantId, 'purchase_return', 'pr_inventory');

        if (!$apAccount || !$inventoryAccount) {
            throw new DomainException('GL account mapping untuk purchase_return belum dikonfigurasi. Hubungi finance untuk setup account mapping.');
        }

        $lines = [
            [
                'gl_account_id' => $apAccount->id,
                'account_code' => $apAccount->account_code,
                'account_name' => $apAccount->account_name,
                'debit' => $returnAmount,
                'credit' => 0,
            ],
            [
                'gl_account_id' => $inventoryAccount->id,
                'account_code' => $inventoryAccount->account_code,
                'account_name' => $inventoryAccount->account_name,
                'debit' => 0,
                'credit' => $returnAmount,
            ],
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

    public function postGoodsReceipt(
        GoodsReceipt $goodsReceipt,
        float $receivedAmount,
        CarbonInterface|string|null $entryDate = null,
    ): AccountingJournalEntry {
        $existing = AccountingJournalEntry::query()
            ->where('reference_type', 'goods_receipt')
            ->where('reference_id', $goodsReceipt->id)
            ->first();

        if ($existing !== null) {
            if ($existing->aggregated_at === null) {
                $this->financialStatementAggregateService->applyJournalEntry($existing);
            }
            return $existing->load('lines');
        }

        $tenantId = (int) $goodsReceipt->tenant_id;

        // Get GL accounts from mapping
        $inventoryAccount = AccountMapping::getAccount($tenantId, 'goods_receipt', 'gr_inventory');
        $apAccount = AccountMapping::getAccount($tenantId, 'goods_receipt', 'gr_supplier');

        if (!$inventoryAccount || !$apAccount) {
            throw new DomainException('GL account mapping untuk goods_receipt belum dikonfigurasi. Hubungi finance untuk setup account mapping.');
        }

        $lines = [
            [
                'gl_account_id' => $inventoryAccount->id,
                'account_code' => $inventoryAccount->account_code,
                'account_name' => $inventoryAccount->account_name,
                'debit' => $receivedAmount,
                'credit' => 0,
            ],
            [
                'gl_account_id' => $apAccount->id,
                'account_code' => $apAccount->account_code,
                'account_name' => $apAccount->account_name,
                'debit' => 0,
                'credit' => $receivedAmount,
            ],
        ];

        return $this->postEntry(
            header: [
                'tenant_id' => $goodsReceipt->tenant_id,
                'location_id' => $goodsReceipt->location_id,
                'entry_date' => $entryDate ?? now(),
                'reference_type' => 'goods_receipt',
                'reference_id' => $goodsReceipt->id,
                'description' => 'GR ' . $goodsReceipt->receipt_number,
            ],
            lines: $lines,
            unbalancedErrorMessage: 'Jurnal penerimaan barang tidak balance. Posting GR dibatalkan.',
        );
    }

    public function postPurchaseOrder(
        PurchaseOrder $purchaseOrder,
        float $orderedAmount,
        CarbonInterface|string|null $entryDate = null,
    ): AccountingJournalEntry {
        $existing = AccountingJournalEntry::query()
            ->where('reference_type', 'purchase_order')
            ->where('reference_id', $purchaseOrder->id)
            ->first();

        if ($existing !== null) {
            if ($existing->aggregated_at === null) {
                $this->financialStatementAggregateService->applyJournalEntry($existing);
            }
            return $existing->load('lines');
        }

        $tenantId = (int) $purchaseOrder->tenant_id;

        // Get GL accounts from mapping
        $inventoryAccount = AccountMapping::getAccount($tenantId, 'purchase_order', 'po_inventory');
        $apAccount = AccountMapping::getAccount($tenantId, 'purchase_order', 'po_supplier');

        if (!$inventoryAccount || !$apAccount) {
            throw new DomainException('GL account mapping untuk purchase_order belum dikonfigurasi. Hubungi finance untuk setup account mapping.');
        }

        $lines = [
            [
                'gl_account_id' => $inventoryAccount->id,
                'account_code' => $inventoryAccount->account_code,
                'account_name' => $inventoryAccount->account_name,
                'debit' => $orderedAmount,
                'credit' => 0,
            ],
            [
                'gl_account_id' => $apAccount->id,
                'account_code' => $apAccount->account_code,
                'account_name' => $apAccount->account_name,
                'debit' => 0,
                'credit' => $orderedAmount,
            ],
        ];

        return $this->postEntry(
            header: [
                'tenant_id' => $purchaseOrder->tenant_id,
                'location_id' => $purchaseOrder->location_id,
                'entry_date' => $entryDate ?? now(),
                'reference_type' => 'purchase_order',
                'reference_id' => $purchaseOrder->id,
                'description' => 'PO ' . $purchaseOrder->po_number,
            ],
            lines: $lines,
            unbalancedErrorMessage: 'Jurnal purchase order tidak balance. Posting PO dibatalkan.',
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
