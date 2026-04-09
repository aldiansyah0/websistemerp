<?php

use App\Models\AccountingJournalEntry;
use App\Models\Employee;
use App\Models\InventoryLedger;
use App\Models\Outlet;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\SalesTransaction;
use App\Workflows\PosTransactionWorkflow;

beforeEach(function (): void {
    $this->seed();
});

test('pos form exposes barcode centric checkout section', function () {
    $this->get(route('sales-transactions.create'))
        ->assertOk()
        ->assertSee('Barcode Centric Checkout')
        ->assertSee('Scan Barcode / SKU');
});

test('pos transaction is rejected when total payment is not equal to total cart', function () {
    [$outlet, $product, $paymentMethod, $cashier] = fixturePosContext();
    $lineAmount = (float) $product->selling_price;

    $this->from(route('sales-transactions.create'))
        ->post(route('sales-transactions.store'), [
            'outlet_id' => $outlet->id,
            'cashier_employee_id' => $cashier?->id,
            'sold_at' => now()->toDateTimeString(),
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 1,
                    'unit_price' => $lineAmount,
                    'discount_amount' => 0,
                    'notes' => null,
                ],
            ],
            'payments' => [
                [
                    'payment_method_id' => $paymentMethod->id,
                    'amount' => $lineAmount - 1000,
                    'reference_number' => 'MISMATCH-001',
                    'approval_code' => null,
                ],
            ],
        ])
        ->assertRedirect(route('sales-transactions.create'))
        ->assertSessionHasErrors('payments');
});

test('pos transaction creates split payments and accounting journal atomically', function () {
    [$outlet, $product, $paymentMethod, $cashier] = fixturePosContext();
    $unitPrice = (float) $product->selling_price;

    $this->post(route('sales-transactions.store'), [
        'outlet_id' => $outlet->id,
        'cashier_employee_id' => $cashier?->id,
        'sold_at' => now()->toDateTimeString(),
        'items' => [
            [
                'product_id' => $product->id,
                'quantity' => 2,
                'unit_price' => $unitPrice,
                'discount_amount' => 0,
                'notes' => null,
            ],
        ],
        'payments' => [
            [
                'payment_method_id' => $paymentMethod->id,
                'amount' => $unitPrice,
                'reference_number' => 'SPLIT-A',
                'approval_code' => null,
            ],
            [
                'payment_method_id' => $paymentMethod->id,
                'amount' => $unitPrice,
                'reference_number' => 'SPLIT-B',
                'approval_code' => null,
            ],
        ],
    ])->assertRedirect(route('pos-transactions'));

    $transaction = SalesTransaction::query()->latest('id')->firstOrFail();
    $journal = AccountingJournalEntry::query()
        ->where('reference_type', 'sales_transaction')
        ->where('reference_id', $transaction->id)
        ->first();

    expect($transaction->payment_status)->toBe('paid')
        ->and((float) $transaction->balance_due)->toBe(0.0)
        ->and($journal)->not->toBeNull()
        ->and(abs((float) $journal->total_debit - (float) $journal->total_credit) < 0.01)->toBeTrue();

    $this->assertDatabaseHas('transaction_payments', [
        'transaction_id' => $transaction->id,
        'reference_number' => 'SPLIT-A',
    ]);
    $this->assertDatabaseHas('transaction_payments', [
        'transaction_id' => $transaction->id,
        'reference_number' => 'SPLIT-B',
    ]);
    expect($journal->lines()->count())->toBe(4);
});

test('pos transaction rolls back fully when stock posting fails', function () {
    [$outlet, $product, $paymentMethod, $cashier] = fixturePosContext();
    $workflow = app(PosTransactionWorkflow::class);
    $sentinelNotes = 'ATOMIC-ROLLBACK-POS';
    $reference = 'ROLLBACK-REF-001';
    $startingCount = SalesTransaction::query()->count();

    try {
        $workflow->store(
            attributes: [
                'outlet_id' => $outlet->id,
                'cashier_employee_id' => $cashier?->id,
                'sold_at' => now()->toDateTimeString(),
                'notes' => $sentinelNotes,
            ],
            items: [
                [
                    'product_id' => $product->id,
                    'quantity' => 999999,
                    'unit_price' => (float) $product->selling_price,
                    'discount_amount' => 0,
                ],
            ],
            payments: [
                [
                    'payment_method_id' => $paymentMethod->id,
                    'amount' => 999999 * (float) $product->selling_price,
                    'reference_number' => $reference,
                ],
            ],
        );
    } catch (\DomainException $exception) {
        // expected rollback path
    }

    expect(SalesTransaction::query()->count())->toBe($startingCount);
    $this->assertDatabaseMissing('sales_transactions', ['notes' => $sentinelNotes]);
    $this->assertDatabaseMissing('transaction_payments', ['reference_number' => $reference]);
});

function fixturePosContext(): array
{
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

    return [$outlet, $product, $paymentMethod, $cashier];
}
