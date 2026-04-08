<?php

use App\Models\AccountingPeriod;
use App\Models\AuditLog;
use App\Models\Employee;
use App\Models\InventoryLedger;
use App\Models\Outlet;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\SalesReturn;
use App\Models\SalesTransaction;
use App\Models\StockTransfer;
use App\Models\User;
use App\Models\Warehouse;
use App\Services\SalesTransactionService;
use Carbon\CarbonImmutable;

beforeEach(function (): void {
    $this->seed();
});

test('retail daily cycle keeps posting atomic from pos to period closing', function () {
    $owner = User::query()->where('email', 'owner@webstellar.local')->firstOrFail();

    [
        $outlet,
        $product,
        $paymentMethod,
        $cashier,
        $sourceWarehouse,
        $destinationWarehouse,
        $transferQuantity,
    ] = fixtureDailyCycleContext();

    $owner->update([
        'tenant_id' => null,
        'location_id' => null,
    ]);
    $this->actingAs($owner->fresh());

    $soldAt = CarbonImmutable::now('Asia/Jakarta')->startOfHour();

    $this->post(route('sales-transactions.store'), [
        'outlet_id' => $outlet->id,
        'cashier_employee_id' => $cashier?->id,
        'sold_at' => $soldAt->toDateTimeString(),
        'items' => [
            [
                'product_id' => $product->id,
                'quantity' => 1,
                'unit_price' => (float) $product->selling_price,
                'discount_amount' => 0,
                'notes' => 'Daily cycle POS',
            ],
        ],
        'payments' => [
            [
                'payment_method_id' => $paymentMethod->id,
                'amount' => (float) $product->selling_price,
                'reference_number' => 'DAILY-CYCLE-POS-1',
                'approval_code' => null,
            ],
        ],
    ])->assertRedirect(route('pos-transactions'));

    $transaction = SalesTransaction::query()->withoutTenantLocation()->with('items')->latest('id')->firstOrFail();
    $transactionItem = $transaction->items->firstOrFail();

    $this->post(route('stock-transfers.store'), [
        'source_warehouse_id' => $sourceWarehouse->id,
        'destination_warehouse_id' => $destinationWarehouse->id,
        'request_date' => $soldAt->toDateString(),
        'expected_receipt_date' => $soldAt->addDay()->toDateString(),
        'notes' => 'Daily cycle transfer',
        'intent' => 'submit',
        'items' => [
            [
                'product_id' => $product->id,
                'requested_quantity' => $transferQuantity,
                'notes' => 'Rebalancing stok outlet',
            ],
        ],
    ])->assertRedirect(route('stock-mutation'));

    $transfer = StockTransfer::query()->withoutTenantLocation()->with('items')->latest('id')->firstOrFail();

    expect($transfer->status)->toBe(StockTransfer::STATUS_PENDING_APPROVAL);

    $this->post(route('stock-transfers.approve', $transfer))
        ->assertRedirect(route('stock-mutation'));

    $transfer->refresh();
    expect($transfer->status)->toBe(StockTransfer::STATUS_APPROVED);

    $transferItem = $transfer->items()->firstOrFail();

    $this->post(route('stock-transfers.receive', $transfer), [
        'notes' => 'Daily cycle receiving',
        'items' => [
            [
                'stock_transfer_item_id' => $transferItem->id,
                'received_quantity' => $transferQuantity,
            ],
        ],
    ])->assertRedirect(route('stock-mutation'));

    $transfer->refresh();
    expect($transfer->status)->toBe(StockTransfer::STATUS_RECEIVED);

    $this->post(route('sales-returns.store', $transaction), [
        'return_date' => $soldAt->toDateString(),
        'notes' => 'Daily cycle refund',
        'intent' => 'submit',
        'items' => [
            [
                'sales_transaction_item_id' => $transactionItem->id,
                'quantity' => 1,
                'unit_price' => (float) $transactionItem->unit_price,
                'reason' => 'Customer changed mind',
                'notes' => 'Reverse POS transaction',
            ],
        ],
    ])->assertRedirect(route('sales-return'));

    $salesReturn = SalesReturn::query()->withoutTenantLocation()->latest('id')->firstOrFail();
    expect($salesReturn->status)->toBe(SalesReturn::STATUS_PENDING_APPROVAL);

    $this->post(route('sales-returns.approve', $salesReturn))
        ->assertRedirect(route('sales-return'));

    $salesReturn->refresh();
    expect($salesReturn->status)->toBe(SalesReturn::STATUS_APPROVED);

    $this->assertDatabaseHas('accounting_journal_entries', [
        'reference_type' => 'sales_transaction',
        'reference_id' => $transaction->id,
    ]);
    $this->assertDatabaseHas('accounting_journal_entries', [
        'reference_type' => 'sales_return',
        'reference_id' => $salesReturn->id,
    ]);
    $this->assertDatabaseHas('stock_mutations', [
        'reference_type' => 'stock_transfer',
        'reference_id' => $transfer->id,
        'transfer_status' => 'sent',
    ]);
    $this->assertDatabaseHas('stock_mutations', [
        'reference_type' => 'stock_transfer',
        'reference_id' => $transfer->id,
        'transfer_status' => 'received',
    ]);

    $periodCode = 'CL' . $soldAt->format('YmdHis');

    $this->post(route('period-closing.close'), [
        'period_code' => $periodCode,
        'start_date' => $soldAt->toDateString(),
        'end_date' => $soldAt->toDateString(),
        'notes' => 'Daily cycle close',
    ])->assertRedirect(route('period-closing'));

    $closedPeriod = AccountingPeriod::query()
        ->withoutTenantLocation()
        ->where('period_code', $periodCode)
        ->firstOrFail();
    expect($closedPeriod->status)->toBe(AccountingPeriod::STATUS_CLOSED);

    $salesCountBeforeBlockedPost = SalesTransaction::query()->withoutTenantLocation()->count();
    $salesTransactionService = app(SalesTransactionService::class);

    expect(function () use ($salesTransactionService, $outlet, $cashier, $soldAt, $product, $paymentMethod): void {
        $salesTransactionService->store(
            attributes: [
                'outlet_id' => $outlet->id,
                'cashier_employee_id' => $cashier?->id,
                'sold_at' => $soldAt->toDateTimeString(),
                'notes' => 'DAILY-CYCLE-POS-LOCK',
            ],
            items: [
                [
                    'product_id' => $product->id,
                    'quantity' => 1,
                    'unit_price' => (float) $product->selling_price,
                    'discount_amount' => 0,
                ],
            ],
            payments: [
                [
                    'payment_method_id' => $paymentMethod->id,
                    'amount' => (float) $product->selling_price,
                    'reference_number' => 'DAILY-CYCLE-POS-LOCK',
                ],
            ],
        );
    })->toThrow(\DomainException::class);

    expect(SalesTransaction::query()->withoutTenantLocation()->count())->toBe($salesCountBeforeBlockedPost);

    expect(AuditLog::query()->where('action', 'pos.store')->exists())->toBeTrue()
        ->and(AuditLog::query()->where('action', 'stock_transfer.approve')->exists())->toBeTrue()
        ->and(AuditLog::query()->where('action', 'stock_transfer.receive')->exists())->toBeTrue()
        ->and(AuditLog::query()->where('action', 'sales_return.approve')->exists())->toBeTrue()
        ->and(AuditLog::query()->where('action', 'period.close')->exists())->toBeTrue();
});

function fixtureDailyCycleContext(): array
{
    $outlet = Outlet::query()->whereNotNull('warehouse_id')->orderBy('id')->firstOrFail();
    $sourceWarehouse = Warehouse::query()->findOrFail((int) $outlet->warehouse_id);
    $destinationWarehouse = Warehouse::query()
        ->withoutTenantLocation()
        ->whereKeyNot($sourceWarehouse->id)
        ->orderBy('id')
        ->firstOrFail();

    $stockRow = InventoryLedger::query()
        ->selectRaw('product_id, SUM(quantity) as on_hand')
        ->where('warehouse_id', $sourceWarehouse->id)
        ->groupBy('product_id')
        ->havingRaw('SUM(quantity) > 1')
        ->orderByDesc('on_hand')
        ->firstOrFail();

    $product = Product::query()->findOrFail((int) $stockRow->product_id);
    $paymentMethod = PaymentMethod::query()->where('is_active', true)->orderBy('id')->firstOrFail();
    $cashier = Employee::query()->where('status', Employee::STATUS_ACTIVE)->orderBy('id')->first();

    $transferQuantity = min(max((float) $stockRow->on_hand - 1, 0.1), 1.0);

    return [
        $outlet,
        $product,
        $paymentMethod,
        $cashier,
        $sourceWarehouse,
        $destinationWarehouse,
        round($transferQuantity, 2),
    ];
}
