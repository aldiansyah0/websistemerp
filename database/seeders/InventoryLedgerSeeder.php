<?php

namespace Database\Seeders;

use App\Models\InventoryLedger;
use App\Models\Product;
use App\Models\Warehouse;
use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;

class InventoryLedgerSeeder extends Seeder
{
    public function run(): void
    {
        $products = Product::query()->pluck('id', 'sku');
        $warehouses = Warehouse::query()->pluck('id', 'code');
        $balances = [];

        $entries = [
            ['sku' => 'MIN-2201', 'warehouse' => 'DC-BARAT', 'type' => 'opening', 'qty' => 320, 'date' => '-30 days'],
            ['sku' => 'MIN-2201', 'warehouse' => 'DC-BARAT', 'type' => 'transfer_out', 'qty' => -180, 'date' => '-5 days'],
            ['sku' => 'MIN-2201', 'warehouse' => 'OUT-JKT01', 'type' => 'transfer_in', 'qty' => 180, 'date' => '-5 days'],
            ['sku' => 'MIN-2201', 'warehouse' => 'OUT-JKT01', 'type' => 'sale', 'qty' => -132, 'date' => '-1 days'],

            ['sku' => 'BEV-4410', 'warehouse' => 'DC-BARAT', 'type' => 'opening', 'qty' => 180, 'date' => '-28 days'],
            ['sku' => 'BEV-4410', 'warehouse' => 'OUT-JKT01', 'type' => 'transfer_in', 'qty' => 72, 'date' => '-7 days'],
            ['sku' => 'BEV-4410', 'warehouse' => 'OUT-JKT01', 'type' => 'sale', 'qty' => -28, 'date' => '-1 days'],

            ['sku' => 'SNK-1808', 'warehouse' => 'DC-BARAT', 'type' => 'opening', 'qty' => 260, 'date' => '-24 days'],
            ['sku' => 'SNK-1808', 'warehouse' => 'OUT-JKT01', 'type' => 'transfer_in', 'qty' => 120, 'date' => '-6 days'],
            ['sku' => 'SNK-1808', 'warehouse' => 'OUT-JKT01', 'type' => 'sale', 'qty' => -74, 'date' => '-1 days'],

            ['sku' => 'SNK-2014', 'warehouse' => 'DC-BARAT', 'type' => 'opening', 'qty' => 210, 'date' => '-26 days'],
            ['sku' => 'SNK-2014', 'warehouse' => 'OUT-JKT01', 'type' => 'transfer_in', 'qty' => 90, 'date' => '-10 days'],
            ['sku' => 'SNK-2014', 'warehouse' => 'OUT-JKT01', 'type' => 'sale', 'qty' => -46, 'date' => '-2 days'],

            ['sku' => 'HOU-0330', 'warehouse' => 'DC-TIMUR', 'type' => 'opening', 'qty' => 144, 'date' => '-40 days'],
            ['sku' => 'HOU-0330', 'warehouse' => 'OUT-SBY01', 'type' => 'transfer_in', 'qty' => 54, 'date' => '-9 days'],
            ['sku' => 'HOU-0330', 'warehouse' => 'OUT-SBY01', 'type' => 'sale', 'qty' => -28, 'date' => '-2 days'],

            ['sku' => 'HOU-0520', 'warehouse' => 'DC-TIMUR', 'type' => 'opening', 'qty' => 190, 'date' => '-35 days'],
            ['sku' => 'HOU-0520', 'warehouse' => 'OUT-SBY01', 'type' => 'transfer_in', 'qty' => 66, 'date' => '-8 days'],
            ['sku' => 'HOU-0520', 'warehouse' => 'OUT-SBY01', 'type' => 'sale', 'qty' => -31, 'date' => '-1 days'],

            ['sku' => 'PER-0724', 'warehouse' => 'DC-BARAT', 'type' => 'opening', 'qty' => 84, 'date' => '-50 days'],
            ['sku' => 'PER-0724', 'warehouse' => 'OUT-JKT01', 'type' => 'transfer_in', 'qty' => 36, 'date' => '-12 days'],
            ['sku' => 'PER-0724', 'warehouse' => 'OUT-JKT01', 'type' => 'sale', 'qty' => -12, 'date' => '-1 days'],

            ['sku' => 'PER-4419', 'warehouse' => 'DC-BARAT', 'type' => 'opening', 'qty' => 210, 'date' => '-22 days'],
            ['sku' => 'PER-4419', 'warehouse' => 'OUT-JKT01', 'type' => 'transfer_in', 'qty' => 96, 'date' => '-4 days'],
            ['sku' => 'PER-4419', 'warehouse' => 'OUT-JKT01', 'type' => 'sale', 'qty' => -55, 'date' => '-1 days'],

            ['sku' => 'FRE-1091', 'warehouse' => 'DC-TIMUR', 'type' => 'opening', 'qty' => 54, 'date' => '-10 days'],
            ['sku' => 'FRE-1091', 'warehouse' => 'OUT-SBY01', 'type' => 'transfer_in', 'qty' => 32, 'date' => '-3 days'],
            ['sku' => 'FRE-1091', 'warehouse' => 'OUT-SBY01', 'type' => 'sale', 'qty' => -10, 'date' => '-1 days'],

            ['sku' => 'FRE-1188', 'warehouse' => 'DC-TIMUR', 'type' => 'opening', 'qty' => 42, 'date' => '-6 days'],
            ['sku' => 'FRE-1188', 'warehouse' => 'OUT-SBY01', 'type' => 'transfer_in', 'qty' => 18, 'date' => '-2 days'],
            ['sku' => 'FRE-1188', 'warehouse' => 'OUT-SBY01', 'type' => 'sale', 'qty' => -8, 'date' => '-1 days'],
        ];

        foreach ($entries as $entry) {
            $key = $entry['sku'] . ':' . $entry['warehouse'];
            $balances[$key] = ($balances[$key] ?? 0) + $entry['qty'];

            $product = Product::query()->findOrFail($products[$entry['sku']]);

            InventoryLedger::query()->create([
                'product_id' => $products[$entry['sku']],
                'warehouse_id' => $warehouses[$entry['warehouse']],
                'movement_type' => $entry['type'],
                'reference_type' => $entry['type'] === 'purchase' ? 'purchase_order' : null,
                'reference_id' => null,
                'quantity' => $entry['qty'],
                'unit_cost' => $product->cost_price,
                'balance_after' => $balances[$key],
                'notes' => 'Seeded ledger movement for ' . $entry['sku'],
                'transaction_at' => CarbonImmutable::now()->add($entry['date']),
                'created_at' => CarbonImmutable::now()->add($entry['date']),
                'updated_at' => CarbonImmutable::now()->add($entry['date']),
            ]);
        }
    }
}
