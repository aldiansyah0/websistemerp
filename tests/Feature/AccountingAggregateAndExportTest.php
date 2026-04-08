<?php

use App\Models\AccountingJournalEntry;
use App\Models\BalanceSheetAggregate;
use App\Models\Employee;
use App\Models\InventoryLedger;
use App\Models\Outlet;
use App\Models\PaymentMethod;
use App\Models\PayrollRun;
use App\Models\ProfitLossAggregate;
use App\Models\Product;

beforeEach(function (): void {
    $this->seed();
});

test('payroll approval and payment automatically generate accounting journal entries', function () {
    $employee = Employee::query()->where('status', Employee::STATUS_ACTIVE)->firstOrFail();

    $run = PayrollRun::query()->create([
        'tenant_id' => $employee->tenant_id,
        'location_id' => $employee->location_id,
        'code' => 'PAY-2026-04-UT',
        'period_start' => '2026-04-01',
        'period_end' => '2026-04-30',
        'status' => PayrollRun::STATUS_DRAFT,
        'notes' => 'UT payroll journal',
    ]);

    $item = $run->items()->create([
        'employee_id' => $employee->id,
        'base_salary' => 5_000_000,
        'allowance_amount' => 0,
        'overtime_amount' => 0,
        'sales_bonus_amount' => 150_000,
        'deduction_amount' => 50_000,
        'attendance_deduction_amount' => 50_000,
        'late_deduction_amount' => 50_000,
        'absence_deduction_amount' => 0,
        'net_salary' => 5_100_000,
        'payment_status' => 'pending',
        'notes' => 'UT payroll item',
    ]);

    $run->recalculateTotals(collect([$item]));
    $run->save();

    $this->post(route('payroll-runs.approve', $run))
        ->assertRedirect(route('payroll-list'));

    $this->assertDatabaseHas('accounting_journal_entries', [
        'reference_type' => 'payroll_run_accrual',
        'reference_id' => $run->id,
    ]);

    $this->post(route('payroll-runs.pay', $run->fresh()))
        ->assertRedirect(route('payroll-list'));

    $this->assertDatabaseHas('accounting_journal_entries', [
        'reference_type' => 'payroll_run_payment',
        'reference_id' => $run->id,
    ]);
});

test('pos transaction updates aggregate tables in real time', function () {
    $outlet = Outlet::query()->whereNotNull('warehouse_id')->orderBy('id')->firstOrFail();
    $productId = InventoryLedger::query()
        ->select('product_id')
        ->where('warehouse_id', $outlet->warehouse_id)
        ->groupBy('product_id')
        ->havingRaw('SUM(quantity) > 0')
        ->value('product_id');
    if ($productId === null) {
        $productId = Product::query()->orderBy('id')->value('id');
    }
    $product = Product::query()->findOrFail($productId);
    $paymentMethod = PaymentMethod::query()->where('is_active', true)->orderBy('id')->firstOrFail();
    $cashier = Employee::query()->where('status', Employee::STATUS_ACTIVE)->orderBy('id')->first();
    $unitPrice = (float) $product->selling_price;

    $this->post(route('sales-transactions.store'), [
        'outlet_id' => $outlet->id,
        'cashier_employee_id' => $cashier?->id,
        'sold_at' => now()->toDateTimeString(),
        'items' => [
            [
                'product_id' => $product->id,
                'quantity' => 1,
                'unit_price' => $unitPrice,
                'discount_amount' => 0,
                'notes' => null,
            ],
        ],
        'payments' => [
            [
                'payment_method_id' => $paymentMethod->id,
                'amount' => $unitPrice,
                'reference_number' => 'AGG-POS-001',
                'approval_code' => null,
            ],
        ],
    ])->assertRedirect(route('pos-transactions'));

    $entry = AccountingJournalEntry::query()
        ->where('reference_type', 'sales_transaction')
        ->latest('id')
        ->firstOrFail();
    $entryDate = $entry->entry_date?->toDateString();

    expect($entry->aggregated_at)->not->toBeNull();
    expect(ProfitLossAggregate::query()->whereDate('report_date', $entryDate)->exists())->toBeTrue();
    expect(BalanceSheetAggregate::query()->whereDate('report_date', $entryDate)->exists())->toBeTrue();
});

test('financial report export works for excel and pdf with chunking enabled', function () {
    config()->set('erp.export.chunk_size', 1);
    $today = now()->toDateString();

    $excelResponse = $this->get(route('financial-report.export', [
        'format' => 'excel',
        'start_date' => $today,
        'end_date' => $today,
    ]));
    $excelResponse->assertOk()
        ->assertHeader('content-type', 'text/csv; charset=UTF-8');

    $pdfResponse = $this->get(route('financial-report.export', [
        'format' => 'pdf',
        'start_date' => $today,
        'end_date' => $today,
    ]));
    $pdfResponse->assertOk();
});
