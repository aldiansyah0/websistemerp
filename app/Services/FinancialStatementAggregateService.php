<?php

namespace App\Services;

use App\Models\AccountingJournalEntry;
use App\Models\AccountingJournalLine;
use App\Models\BalanceSheetAggregate;
use App\Models\ProfitLossAggregate;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class FinancialStatementAggregateService
{
    public function applyJournalEntry(AccountingJournalEntry $journalEntry): void
    {
        DB::transaction(function () use ($journalEntry): void {
            /** @var AccountingJournalEntry|null $entry */
            $entry = AccountingJournalEntry::query()
                ->lockForUpdate()
                ->with('lines')
                ->find($journalEntry->id);

            if ($entry === null || $entry->aggregated_at !== null) {
                return;
            }

            $tenantId = (int) ($entry->tenant_id ?? 0);
            $locationId = (int) ($entry->location_id ?? 0);
            $reportDate = $entry->entry_date?->toDateString() ?? now('Asia/Jakarta')->toDateString();

            $profitLoss = $this->profitLossMovement($entry->lines);
            $balanceSheet = $this->balanceSheetMovement($entry->lines, $profitLoss['net_profit_amount']);

            $this->storeProfitLossMovement($tenantId, $locationId, $reportDate, $profitLoss);
            $this->storeBalanceSheetMovement($tenantId, $locationId, $reportDate, $balanceSheet);

            $entry->aggregated_at = now('Asia/Jakarta');
            $entry->save();
        });
    }

    /**
     * @return array<string, float>
     */
    public function profitLossSummary(CarbonInterface $startDate, CarbonInterface $endDate): array
    {
        $row = ProfitLossAggregate::query()
            ->selectRaw('COALESCE(SUM(revenue_amount), 0) as revenue_amount')
            ->selectRaw('COALESCE(SUM(cogs_amount), 0) as cogs_amount')
            ->selectRaw('COALESCE(SUM(payroll_expense_amount), 0) as payroll_expense_amount')
            ->selectRaw('COALESCE(SUM(operating_expense_amount), 0) as operating_expense_amount')
            ->selectRaw('COALESCE(SUM(other_income_amount), 0) as other_income_amount')
            ->selectRaw('COALESCE(SUM(net_profit_amount), 0) as net_profit_amount')
            ->whereBetween('report_date', [$startDate->toDateString(), $endDate->toDateString()])
            ->first();

        $revenue = (float) ($row->revenue_amount ?? 0);
        $cogs = (float) ($row->cogs_amount ?? 0);
        $payrollExpense = (float) ($row->payroll_expense_amount ?? 0);
        $operatingExpense = (float) ($row->operating_expense_amount ?? 0);
        $otherIncome = (float) ($row->other_income_amount ?? 0);
        $netProfit = (float) ($row->net_profit_amount ?? 0);

        return [
            'revenue_amount' => $revenue,
            'cogs_amount' => $cogs,
            'gross_profit_amount' => $revenue - $cogs,
            'payroll_expense_amount' => $payrollExpense,
            'operating_expense_amount' => $operatingExpense,
            'other_income_amount' => $otherIncome,
            'operating_profit_amount' => ($revenue - $cogs) - $payrollExpense - $operatingExpense + $otherIncome,
            'net_profit_amount' => $netProfit,
        ];
    }

    /**
     * @return array<string, float>
     */
    public function balanceSheetSummary(CarbonInterface $asOfDate): array
    {
        $row = BalanceSheetAggregate::query()
            ->selectRaw('COALESCE(SUM(cash_movement), 0) as cash_movement')
            ->selectRaw('COALESCE(SUM(accounts_receivable_movement), 0) as accounts_receivable_movement')
            ->selectRaw('COALESCE(SUM(inventory_movement), 0) as inventory_movement')
            ->selectRaw('COALESCE(SUM(accounts_payable_movement), 0) as accounts_payable_movement')
            ->selectRaw('COALESCE(SUM(payroll_payable_movement), 0) as payroll_payable_movement')
            ->selectRaw('COALESCE(SUM(equity_movement), 0) as equity_movement')
            ->selectRaw('COALESCE(SUM(retained_earnings_movement), 0) as retained_earnings_movement')
            ->whereDate('report_date', '<=', $asOfDate->toDateString())
            ->first();

        $cash = (float) ($row->cash_movement ?? 0);
        $receivable = (float) ($row->accounts_receivable_movement ?? 0);
        $inventory = (float) ($row->inventory_movement ?? 0);
        $payable = (float) ($row->accounts_payable_movement ?? 0);
        $payrollPayable = (float) ($row->payroll_payable_movement ?? 0);
        $equity = (float) ($row->equity_movement ?? 0);
        $retainedEarnings = (float) ($row->retained_earnings_movement ?? 0);

        $totalAssets = $cash + $receivable + $inventory;
        $totalLiabilities = $payable + $payrollPayable;
        $totalEquity = $equity + $retainedEarnings;

        return [
            'cash_balance' => $cash,
            'accounts_receivable_balance' => $receivable,
            'inventory_balance' => $inventory,
            'accounts_payable_balance' => $payable,
            'payroll_payable_balance' => $payrollPayable,
            'equity_balance' => $equity,
            'retained_earnings_balance' => $retainedEarnings,
            'total_assets' => $totalAssets,
            'total_liabilities' => $totalLiabilities,
            'total_equity' => $totalEquity,
            'balance_delta' => $totalAssets - ($totalLiabilities + $totalEquity),
        ];
    }

    /**
     * @return array<string, float>
     */
    public function profitLossSummaryChunked(CarbonInterface $startDate, CarbonInterface $endDate, int $chunkSize = 1000): array
    {
        $totals = [
            'revenue_amount' => 0.0,
            'cogs_amount' => 0.0,
            'payroll_expense_amount' => 0.0,
            'operating_expense_amount' => 0.0,
            'other_income_amount' => 0.0,
            'net_profit_amount' => 0.0,
        ];

        $query = ProfitLossAggregate::query()
            ->whereBetween('report_date', [$startDate->toDateString(), $endDate->toDateString()])
            ->orderBy('id');

        $this->chunkAggregateQuery($query, $chunkSize, function ($rows) use (&$totals): void {
            foreach ($rows as $row) {
                $totals['revenue_amount'] += (float) $row->revenue_amount;
                $totals['cogs_amount'] += (float) $row->cogs_amount;
                $totals['payroll_expense_amount'] += (float) $row->payroll_expense_amount;
                $totals['operating_expense_amount'] += (float) $row->operating_expense_amount;
                $totals['other_income_amount'] += (float) $row->other_income_amount;
                $totals['net_profit_amount'] += (float) $row->net_profit_amount;
            }
        });

        $totals['gross_profit_amount'] = $totals['revenue_amount'] - $totals['cogs_amount'];
        $totals['operating_profit_amount'] = $totals['gross_profit_amount']
            - $totals['payroll_expense_amount']
            - $totals['operating_expense_amount']
            + $totals['other_income_amount'];

        return $totals;
    }

    /**
     * @return array<string, float>
     */
    public function balanceSheetSummaryChunked(CarbonInterface $asOfDate, int $chunkSize = 1000): array
    {
        $totals = [
            'cash_balance' => 0.0,
            'accounts_receivable_balance' => 0.0,
            'inventory_balance' => 0.0,
            'accounts_payable_balance' => 0.0,
            'payroll_payable_balance' => 0.0,
            'equity_balance' => 0.0,
            'retained_earnings_balance' => 0.0,
        ];

        $query = BalanceSheetAggregate::query()
            ->whereDate('report_date', '<=', $asOfDate->toDateString())
            ->orderBy('id');

        $this->chunkAggregateQuery($query, $chunkSize, function ($rows) use (&$totals): void {
            foreach ($rows as $row) {
                $totals['cash_balance'] += (float) $row->cash_movement;
                $totals['accounts_receivable_balance'] += (float) $row->accounts_receivable_movement;
                $totals['inventory_balance'] += (float) $row->inventory_movement;
                $totals['accounts_payable_balance'] += (float) $row->accounts_payable_movement;
                $totals['payroll_payable_balance'] += (float) $row->payroll_payable_movement;
                $totals['equity_balance'] += (float) $row->equity_movement;
                $totals['retained_earnings_balance'] += (float) $row->retained_earnings_movement;
            }
        });

        $totals['total_assets'] = $totals['cash_balance'] + $totals['accounts_receivable_balance'] + $totals['inventory_balance'];
        $totals['total_liabilities'] = $totals['accounts_payable_balance'] + $totals['payroll_payable_balance'];
        $totals['total_equity'] = $totals['equity_balance'] + $totals['retained_earnings_balance'];
        $totals['balance_delta'] = $totals['total_assets'] - ($totals['total_liabilities'] + $totals['total_equity']);

        return $totals;
    }

    /**
     * @param \Illuminate\Support\Collection<int, AccountingJournalLine> $lines
     * @return array<string, float>
     */
    private function profitLossMovement($lines): array
    {
        $movement = [
            'revenue_amount' => 0.0,
            'cogs_amount' => 0.0,
            'payroll_expense_amount' => 0.0,
            'operating_expense_amount' => 0.0,
            'other_income_amount' => 0.0,
            'net_profit_amount' => 0.0,
        ];

        foreach ($lines as $line) {
            $code = (string) $line->account_code;
            $debit = (float) $line->debit;
            $credit = (float) $line->credit;
            $debitNet = $debit - $credit;
            $creditNet = $credit - $debit;

            if (str_starts_with($code, '4')) {
                $movement['revenue_amount'] += $creditNet;
                continue;
            }

            if ($code === '5101') {
                $movement['cogs_amount'] += $debitNet;
                continue;
            }

            if ($code === '5201') {
                $movement['payroll_expense_amount'] += $debitNet;
                continue;
            }

            if (str_starts_with($code, '5') || str_starts_with($code, '6')) {
                $movement['operating_expense_amount'] += $debitNet;
                continue;
            }

            if (str_starts_with($code, '7')) {
                $movement['other_income_amount'] += $creditNet;
            }
        }

        $movement['net_profit_amount'] = $movement['revenue_amount']
            - $movement['cogs_amount']
            - $movement['payroll_expense_amount']
            - $movement['operating_expense_amount']
            + $movement['other_income_amount'];

        return $movement;
    }

    /**
     * @param \Illuminate\Support\Collection<int, AccountingJournalLine> $lines
     * @return array<string, float>
     */
    private function balanceSheetMovement($lines, float $netProfitAmount): array
    {
        $movement = [
            'cash_movement' => 0.0,
            'accounts_receivable_movement' => 0.0,
            'inventory_movement' => 0.0,
            'accounts_payable_movement' => 0.0,
            'payroll_payable_movement' => 0.0,
            'equity_movement' => 0.0,
            'retained_earnings_movement' => $netProfitAmount,
        ];

        foreach ($lines as $line) {
            $code = (string) $line->account_code;
            $debit = (float) $line->debit;
            $credit = (float) $line->credit;
            $debitNet = $debit - $credit;
            $creditNet = $credit - $debit;

            if ($code === '1101' || $code === '1102') {
                $movement['cash_movement'] += $debitNet;
                continue;
            }

            if (str_starts_with($code, '1103') || str_starts_with($code, '1104')) {
                $movement['accounts_receivable_movement'] += $debitNet;
                continue;
            }

            if (str_starts_with($code, '12')) {
                $movement['inventory_movement'] += $debitNet;
                continue;
            }

            if ($code === '2101') {
                $movement['accounts_payable_movement'] += $creditNet;
                continue;
            }

            if ($code === '2102') {
                $movement['payroll_payable_movement'] += $creditNet;
                continue;
            }

            if (str_starts_with($code, '3')) {
                $movement['equity_movement'] += $creditNet;
            }
        }

        return $movement;
    }

    /**
     * @param array<string, float> $movement
     */
    private function storeProfitLossMovement(int $tenantId, int $locationId, string $reportDate, array $movement): void
    {
        $reportDay = Carbon::parse($reportDate, 'Asia/Jakarta')->toDateString();

        $aggregate = ProfitLossAggregate::query()
            ->withoutTenantLocation()
            ->where('tenant_id', $tenantId)
            ->where('location_id', $locationId)
            ->whereDate('report_date', $reportDay)
            ->first();

        if ($aggregate === null) {
            $aggregate = new ProfitLossAggregate([
                'tenant_id' => $tenantId,
                'location_id' => $locationId,
                'report_date' => $reportDay,
            ]);
        }

        $aggregate->revenue_amount = (float) $aggregate->revenue_amount + $movement['revenue_amount'];
        $aggregate->cogs_amount = (float) $aggregate->cogs_amount + $movement['cogs_amount'];
        $aggregate->payroll_expense_amount = (float) $aggregate->payroll_expense_amount + $movement['payroll_expense_amount'];
        $aggregate->operating_expense_amount = (float) $aggregate->operating_expense_amount + $movement['operating_expense_amount'];
        $aggregate->other_income_amount = (float) $aggregate->other_income_amount + $movement['other_income_amount'];
        $aggregate->net_profit_amount = (float) $aggregate->net_profit_amount + $movement['net_profit_amount'];
        $aggregate->journal_entries_count = (int) $aggregate->journal_entries_count + 1;
        $aggregate->save();
    }

    /**
     * @param array<string, float> $movement
     */
    private function storeBalanceSheetMovement(int $tenantId, int $locationId, string $reportDate, array $movement): void
    {
        $reportDay = Carbon::parse($reportDate, 'Asia/Jakarta')->toDateString();

        $aggregate = BalanceSheetAggregate::query()
            ->withoutTenantLocation()
            ->where('tenant_id', $tenantId)
            ->where('location_id', $locationId)
            ->whereDate('report_date', $reportDay)
            ->first();

        if ($aggregate === null) {
            $aggregate = new BalanceSheetAggregate([
                'tenant_id' => $tenantId,
                'location_id' => $locationId,
                'report_date' => $reportDay,
            ]);
        }

        $aggregate->cash_movement = (float) $aggregate->cash_movement + $movement['cash_movement'];
        $aggregate->accounts_receivable_movement = (float) $aggregate->accounts_receivable_movement + $movement['accounts_receivable_movement'];
        $aggregate->inventory_movement = (float) $aggregate->inventory_movement + $movement['inventory_movement'];
        $aggregate->accounts_payable_movement = (float) $aggregate->accounts_payable_movement + $movement['accounts_payable_movement'];
        $aggregate->payroll_payable_movement = (float) $aggregate->payroll_payable_movement + $movement['payroll_payable_movement'];
        $aggregate->equity_movement = (float) $aggregate->equity_movement + $movement['equity_movement'];
        $aggregate->retained_earnings_movement = (float) $aggregate->retained_earnings_movement + $movement['retained_earnings_movement'];
        $aggregate->journal_entries_count = (int) $aggregate->journal_entries_count + 1;
        $aggregate->save();
    }

    private function chunkAggregateQuery(Builder $query, int $chunkSize, callable $handler): void
    {
        $query->chunkById(max($chunkSize, 100), function ($rows) use ($handler): void {
            $handler($rows);
        });
    }
}
