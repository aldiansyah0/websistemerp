<?php

use App\Models\PayrollRun;
use App\Models\User;

beforeEach(function (): void {
    $this->seed();
});

test('cashier is forbidden to approve payroll finance workflow', function () {
    $cashier = User::query()->where('email', 'cashier@webstellar.local')->firstOrFail();
    $payrollRun = PayrollRun::query()->firstOrFail();
    $cashier->update(['location_id' => $payrollRun->location_id]);

    $this->actingAs($cashier)
        ->post(route('payroll-runs.approve', $payrollRun))
        ->assertForbidden();
});

test('warehouse manager cannot access pos transaction form', function () {
    $warehouseManager = User::query()->where('email', 'warehouse@webstellar.local')->firstOrFail();

    $this->actingAs($warehouseManager)
        ->get(route('sales-transactions.create'))
        ->assertForbidden();
});

test('cashier can access pos transaction form while owner can access payroll approval endpoint', function () {
    $cashier = User::query()->where('email', 'cashier@webstellar.local')->firstOrFail();
    $owner = User::query()->where('email', 'owner@webstellar.local')->firstOrFail();
    $payrollRun = PayrollRun::query()->firstOrFail();
    $owner->update(['location_id' => $payrollRun->location_id]);

    $this->actingAs($cashier)
        ->get(route('sales-transactions.create'))
        ->assertOk();

    $this->actingAs($owner)
        ->post(route('payroll-runs.approve', $payrollRun))
        ->assertStatus(302);
});

test('finance can close period but cannot access pos transaction form', function () {
    $finance = User::query()->where('email', 'finance@webstellar.local')->firstOrFail();

    $this->actingAs($finance)
        ->post(route('period-closing.close'), [
            'period_code' => '209912',
            'start_date' => '2099-12-01',
            'end_date' => '2099-12-31',
            'notes' => 'Finance period close smoke test.',
        ])
        ->assertStatus(302);

    $this->assertDatabaseHas('accounting_periods', [
        'period_code' => '209912',
        'status' => 'closed',
        'closed_by' => $finance->id,
    ]);

    $this->actingAs($finance)
        ->get(route('sales-transactions.create'))
        ->assertForbidden();
});

test('warehouse manager can access stock transfer but cannot close finance period', function () {
    $warehouseManager = User::query()->where('email', 'warehouse@webstellar.local')->firstOrFail();

    $this->actingAs($warehouseManager)
        ->get(route('stock-transfers.create'))
        ->assertOk();

    $this->actingAs($warehouseManager)
        ->post(route('period-closing.close'), [
            'period_code' => '209911',
            'start_date' => '2099-11-01',
            'end_date' => '2099-11-30',
        ])
        ->assertForbidden();
});

test('cashier is forbidden to open stock transfer workflow', function () {
    $cashier = User::query()->where('email', 'cashier@webstellar.local')->firstOrFail();

    $this->actingAs($cashier)
        ->get(route('stock-transfers.create'))
        ->assertForbidden();
});

test('cashier is forbidden to open audit trail workspace', function () {
    $cashier = User::query()->where('email', 'cashier@webstellar.local')->firstOrFail();

    $this->actingAs($cashier)
        ->get(route('audit-trail'))
        ->assertForbidden();
});
