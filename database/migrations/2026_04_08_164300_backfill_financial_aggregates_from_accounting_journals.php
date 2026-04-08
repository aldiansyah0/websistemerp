<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (
            ! Schema::hasTable('accounting_journal_entries')
            || ! Schema::hasTable('accounting_journal_lines')
            || ! Schema::hasTable('profit_loss_aggregates')
            || ! Schema::hasTable('balance_sheet_aggregates')
        ) {
            return;
        }

        DB::table('accounting_journal_entries')
            ->orderBy('id')
            ->chunkById(300, function ($entries): void {
                foreach ($entries as $entry) {
                    $lines = DB::table('accounting_journal_lines')
                        ->where('journal_entry_id', $entry->id)
                        ->get();

                    if ($lines->isEmpty()) {
                        DB::table('accounting_journal_entries')
                            ->where('id', $entry->id)
                            ->update(['aggregated_at' => now()]);
                        continue;
                    }

                    $tenantId = (int) ($entry->tenant_id ?? 0);
                    $locationId = (int) ($entry->location_id ?? 0);
                    $reportDate = $entry->entry_date;

                    $profitLoss = [
                        'revenue_amount' => 0.0,
                        'cogs_amount' => 0.0,
                        'payroll_expense_amount' => 0.0,
                        'operating_expense_amount' => 0.0,
                        'other_income_amount' => 0.0,
                        'net_profit_amount' => 0.0,
                    ];
                    $balanceSheet = [
                        'cash_movement' => 0.0,
                        'accounts_receivable_movement' => 0.0,
                        'inventory_movement' => 0.0,
                        'accounts_payable_movement' => 0.0,
                        'payroll_payable_movement' => 0.0,
                        'equity_movement' => 0.0,
                        'retained_earnings_movement' => 0.0,
                    ];

                    foreach ($lines as $line) {
                        $code = (string) $line->account_code;
                        $debit = (float) $line->debit;
                        $credit = (float) $line->credit;
                        $debitNet = $debit - $credit;
                        $creditNet = $credit - $debit;

                        if (str_starts_with($code, '4')) {
                            $profitLoss['revenue_amount'] += $creditNet;
                        } elseif ($code === '5101') {
                            $profitLoss['cogs_amount'] += $debitNet;
                        } elseif ($code === '5201') {
                            $profitLoss['payroll_expense_amount'] += $debitNet;
                        } elseif (str_starts_with($code, '5') || str_starts_with($code, '6')) {
                            $profitLoss['operating_expense_amount'] += $debitNet;
                        } elseif (str_starts_with($code, '7')) {
                            $profitLoss['other_income_amount'] += $creditNet;
                        }

                        if ($code === '1101' || $code === '1102') {
                            $balanceSheet['cash_movement'] += $debitNet;
                        } elseif (str_starts_with($code, '1103') || str_starts_with($code, '1104')) {
                            $balanceSheet['accounts_receivable_movement'] += $debitNet;
                        } elseif (str_starts_with($code, '12')) {
                            $balanceSheet['inventory_movement'] += $debitNet;
                        } elseif ($code === '2101') {
                            $balanceSheet['accounts_payable_movement'] += $creditNet;
                        } elseif ($code === '2102') {
                            $balanceSheet['payroll_payable_movement'] += $creditNet;
                        } elseif (str_starts_with($code, '3')) {
                            $balanceSheet['equity_movement'] += $creditNet;
                        }
                    }

                    $profitLoss['net_profit_amount'] = $profitLoss['revenue_amount']
                        - $profitLoss['cogs_amount']
                        - $profitLoss['payroll_expense_amount']
                        - $profitLoss['operating_expense_amount']
                        + $profitLoss['other_income_amount'];
                    $balanceSheet['retained_earnings_movement'] = $profitLoss['net_profit_amount'];

                    $plExisting = DB::table('profit_loss_aggregates')
                        ->where('tenant_id', $tenantId)
                        ->where('location_id', $locationId)
                        ->where('report_date', $reportDate)
                        ->first();

                    if ($plExisting === null) {
                        DB::table('profit_loss_aggregates')->insert(array_merge($profitLoss, [
                            'tenant_id' => $tenantId,
                            'location_id' => $locationId,
                            'report_date' => $reportDate,
                            'journal_entries_count' => 1,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]));
                    } else {
                        DB::table('profit_loss_aggregates')
                            ->where('id', $plExisting->id)
                            ->update([
                                'revenue_amount' => (float) $plExisting->revenue_amount + $profitLoss['revenue_amount'],
                                'cogs_amount' => (float) $plExisting->cogs_amount + $profitLoss['cogs_amount'],
                                'payroll_expense_amount' => (float) $plExisting->payroll_expense_amount + $profitLoss['payroll_expense_amount'],
                                'operating_expense_amount' => (float) $plExisting->operating_expense_amount + $profitLoss['operating_expense_amount'],
                                'other_income_amount' => (float) $plExisting->other_income_amount + $profitLoss['other_income_amount'],
                                'net_profit_amount' => (float) $plExisting->net_profit_amount + $profitLoss['net_profit_amount'],
                                'journal_entries_count' => (int) $plExisting->journal_entries_count + 1,
                                'updated_at' => now(),
                            ]);
                    }

                    $bsExisting = DB::table('balance_sheet_aggregates')
                        ->where('tenant_id', $tenantId)
                        ->where('location_id', $locationId)
                        ->where('report_date', $reportDate)
                        ->first();

                    if ($bsExisting === null) {
                        DB::table('balance_sheet_aggregates')->insert(array_merge($balanceSheet, [
                            'tenant_id' => $tenantId,
                            'location_id' => $locationId,
                            'report_date' => $reportDate,
                            'journal_entries_count' => 1,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]));
                    } else {
                        DB::table('balance_sheet_aggregates')
                            ->where('id', $bsExisting->id)
                            ->update([
                                'cash_movement' => (float) $bsExisting->cash_movement + $balanceSheet['cash_movement'],
                                'accounts_receivable_movement' => (float) $bsExisting->accounts_receivable_movement + $balanceSheet['accounts_receivable_movement'],
                                'inventory_movement' => (float) $bsExisting->inventory_movement + $balanceSheet['inventory_movement'],
                                'accounts_payable_movement' => (float) $bsExisting->accounts_payable_movement + $balanceSheet['accounts_payable_movement'],
                                'payroll_payable_movement' => (float) $bsExisting->payroll_payable_movement + $balanceSheet['payroll_payable_movement'],
                                'equity_movement' => (float) $bsExisting->equity_movement + $balanceSheet['equity_movement'],
                                'retained_earnings_movement' => (float) $bsExisting->retained_earnings_movement + $balanceSheet['retained_earnings_movement'],
                                'journal_entries_count' => (int) $bsExisting->journal_entries_count + 1,
                                'updated_at' => now(),
                            ]);
                    }

                    DB::table('accounting_journal_entries')
                        ->where('id', $entry->id)
                        ->update(['aggregated_at' => now()]);
                }
            });
    }

    public function down(): void
    {
        if (Schema::hasTable('balance_sheet_aggregates')) {
            DB::table('balance_sheet_aggregates')->truncate();
        }

        if (Schema::hasTable('profit_loss_aggregates')) {
            DB::table('profit_loss_aggregates')->truncate();
        }

        if (Schema::hasTable('accounting_journal_entries') && Schema::hasColumn('accounting_journal_entries', 'aggregated_at')) {
            DB::table('accounting_journal_entries')->update(['aggregated_at' => null]);
        }
    }
};
