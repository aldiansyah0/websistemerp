<?php

use App\Models\User;

beforeEach(function (): void {
    $this->seed();
});

test('browser regression key role pages for cashier warehouse manager and finance are reachable with expected controls', function () {
    $cashier = User::query()->where('email', 'cashier@webstellar.local')->firstOrFail();
    $warehouseManager = User::query()->where('email', 'warehouse@webstellar.local')->firstOrFail();
    $finance = User::query()->where('email', 'finance@webstellar.local')->firstOrFail();

    $this->actingAs($cashier)
        ->get(route('pos-transactions'))
        ->assertOk()
        ->assertSee('POS Transaction')
        ->assertSee('Buat Transaksi POS');

    $this->actingAs($warehouseManager)
        ->get(route('purchase-orders'))
        ->assertOk()
        ->assertSee('Purchase Order')
        ->assertSee('Buat PO');

    $this->actingAs($finance)
        ->get(route('period-closing'))
        ->assertOk()
        ->assertSee('Period Closing')
        ->assertSee('Tutup Periode');

    $this->actingAs($finance)
        ->get(route('audit-trail'))
        ->assertOk()
        ->assertSee('Audit Trail');
});
