<?php

use App\Models\Location;
use App\Models\LocationTransfer;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\StockMutation;
use Illuminate\Support\Facades\Schema;

beforeEach(function (): void {
    $this->seed();
});

test('multi outlet and multi warehouse schema tables are available', function () {
    expect(Schema::hasTable('locations'))->toBeTrue()
        ->and(Schema::hasTable('product_variants'))->toBeTrue()
        ->and(Schema::hasTable('location_transfers'))->toBeTrue()
        ->and(Schema::hasTable('location_transfer_items'))->toBeTrue()
        ->and(Schema::hasTable('stock_mutations'))->toBeTrue()
        ->and(Schema::hasColumn('locations', 'type'))->toBeTrue()
        ->and(Schema::hasColumn('product_variants', 'sku'))->toBeTrue()
        ->and(Schema::hasColumn('product_variants', 'barcode'))->toBeTrue()
        ->and(Schema::hasColumn('stock_mutations', 'location_id'))->toBeTrue()
        ->and(Schema::hasColumn('stock_mutations', 'transfer_status'))->toBeTrue();
});

test('stock mutation ledger supports sent in-transit and received lifecycle', function () {
    $source = Location::query()->create([
        'type' => Location::TYPE_WAREHOUSE,
        'code' => 'TST-WH-SRC',
        'name' => 'Test Warehouse Source',
        'status' => 'active',
    ]);
    $destination = Location::query()->create([
        'type' => Location::TYPE_OUTLET,
        'code' => 'TST-OT-DST',
        'name' => 'Test Outlet Destination',
        'status' => 'active',
    ]);
    $product = Product::query()->firstOrFail();
    $variant = ProductVariant::query()->create([
        'product_id' => $product->id,
        'sku' => 'VAR-TST-' . $product->id,
        'variant_name' => 'Variant Test',
        'unit_of_measure' => 'pcs',
        'cost_price' => 10000,
        'selling_price' => 15000,
        'status' => 'active',
        'is_default' => false,
    ]);
    $transfer = LocationTransfer::query()->create([
        'transfer_number' => uniqid('LTR-', true),
        'source_location_id' => $source->id,
        'destination_location_id' => $destination->id,
        'request_date' => now()->toDateString(),
        'status' => LocationTransfer::STATUS_SENT,
        'total_quantity' => 12,
        'total_cost' => 120000,
    ]);

    $transfer->update([
        'status' => LocationTransfer::STATUS_IN_TRANSIT,
        'in_transit_at' => now(),
    ]);
    $transfer->update([
        'status' => LocationTransfer::STATUS_RECEIVED,
        'received_at' => now(),
    ]);

    StockMutation::query()->create([
        'product_variant_id' => $variant->id,
        'location_id' => $source->id,
        'related_location_id' => $destination->id,
        'transfer_id' => $transfer->id,
        'mutation_type' => 'transfer',
        'transfer_status' => 'sent',
        'quantity' => 12,
        'unit_cost' => 10000,
        'occurred_at' => now(),
    ]);
    StockMutation::query()->create([
        'product_variant_id' => $variant->id,
        'location_id' => $source->id,
        'related_location_id' => $destination->id,
        'transfer_id' => $transfer->id,
        'mutation_type' => 'transfer',
        'transfer_status' => 'in_transit',
        'quantity' => 12,
        'unit_cost' => 10000,
        'occurred_at' => now(),
    ]);
    StockMutation::query()->create([
        'product_variant_id' => $variant->id,
        'location_id' => $destination->id,
        'related_location_id' => $source->id,
        'transfer_id' => $transfer->id,
        'mutation_type' => 'transfer',
        'transfer_status' => 'received',
        'quantity' => 12,
        'unit_cost' => 10000,
        'occurred_at' => now(),
    ]);

    $this->assertDatabaseHas('location_transfers', [
        'id' => $transfer->id,
        'status' => LocationTransfer::STATUS_RECEIVED,
    ]);
    $this->assertDatabaseHas('stock_mutations', [
        'transfer_id' => $transfer->id,
        'transfer_status' => 'sent',
    ]);
    $this->assertDatabaseHas('stock_mutations', [
        'transfer_id' => $transfer->id,
        'transfer_status' => 'in_transit',
    ]);
    $this->assertDatabaseHas('stock_mutations', [
        'transfer_id' => $transfer->id,
        'transfer_status' => 'received',
    ]);
});
