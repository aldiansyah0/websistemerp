<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\Supplier;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $categories = Category::query()->pluck('id', 'code');
        $suppliers = Supplier::query()->pluck('id', 'code');

        $products = [
            ['sku' => 'MIN-2201', 'barcode' => '8991002201001', 'name' => 'Sparkling Tea 330ml', 'category' => 'BEV', 'supplier' => 'SUP-001', 'cost' => 7600, 'price' => 12900, 'run_rate' => 42, 'reorder_level' => 120, 'reorder_quantity' => 300, 'uom' => 'pcs', 'featured' => true, 'shelf_life_days' => 180],
            ['sku' => 'BEV-4410', 'barcode' => '8991004410004', 'name' => 'Cold Brew Bottle 1L', 'category' => 'BEV', 'supplier' => 'SUP-001', 'cost' => 18100, 'price' => 28900, 'run_rate' => 18, 'reorder_level' => 48, 'reorder_quantity' => 144, 'uom' => 'pcs', 'featured' => false, 'shelf_life_days' => 120],
            ['sku' => 'SNK-1808', 'barcode' => '8992001808008', 'name' => 'Almond Bites 90gr', 'category' => 'SNK', 'supplier' => 'SUP-002', 'cost' => 15500, 'price' => 23900, 'run_rate' => 27, 'reorder_level' => 80, 'reorder_quantity' => 240, 'uom' => 'pcs', 'featured' => true, 'shelf_life_days' => 270],
            ['sku' => 'SNK-2014', 'barcode' => '8992002014009', 'name' => 'Sea Salt Chips 65gr', 'category' => 'SNK', 'supplier' => 'SUP-002', 'cost' => 9800, 'price' => 15900, 'run_rate' => 22, 'reorder_level' => 70, 'reorder_quantity' => 210, 'uom' => 'pcs', 'featured' => false, 'shelf_life_days' => 240],
            ['sku' => 'HOU-0330', 'barcode' => '8993000330002', 'name' => 'Laundry Pods 12pcs', 'category' => 'HOU', 'supplier' => 'SUP-003', 'cost' => 42300, 'price' => 58900, 'run_rate' => 11, 'reorder_level' => 36, 'reorder_quantity' => 96, 'uom' => 'box', 'featured' => false, 'shelf_life_days' => 365],
            ['sku' => 'HOU-0520', 'barcode' => '8993000520003', 'name' => 'Dish Soap 750ml', 'category' => 'HOU', 'supplier' => 'SUP-003', 'cost' => 12100, 'price' => 18900, 'run_rate' => 16, 'reorder_level' => 44, 'reorder_quantity' => 120, 'uom' => 'btl', 'featured' => false, 'shelf_life_days' => 365],
            ['sku' => 'PER-0724', 'barcode' => '8994000724004', 'name' => 'Facial Wash 100ml', 'category' => 'PER', 'supplier' => 'SUP-005', 'cost' => 28700, 'price' => 44900, 'run_rate' => 9, 'reorder_level' => 24, 'reorder_quantity' => 72, 'uom' => 'pcs', 'featured' => true, 'shelf_life_days' => 540],
            ['sku' => 'PER-4419', 'barcode' => '8994004419008', 'name' => 'Travel Sanitizer', 'category' => 'PER', 'supplier' => 'SUP-005', 'cost' => 9400, 'price' => 14900, 'run_rate' => 23, 'reorder_level' => 60, 'reorder_quantity' => 180, 'uom' => 'pcs', 'featured' => false, 'shelf_life_days' => 365],
            ['sku' => 'FRE-1091', 'barcode' => '8995001091005', 'name' => 'Greek Yogurt Plain', 'category' => 'FRE', 'supplier' => 'SUP-004', 'cost' => 24300, 'price' => 31900, 'run_rate' => 14, 'reorder_level' => 36, 'reorder_quantity' => 96, 'uom' => 'cup', 'featured' => false, 'shelf_life_days' => 21],
            ['sku' => 'FRE-1188', 'barcode' => '8995001188007', 'name' => 'Chicken Caesar Wrap', 'category' => 'FRE', 'supplier' => 'SUP-004', 'cost' => 19500, 'price' => 28900, 'run_rate' => 10, 'reorder_level' => 30, 'reorder_quantity' => 72, 'uom' => 'pcs', 'featured' => false, 'shelf_life_days' => 7],
        ];

        foreach ($products as $product) {
            Product::query()->updateOrCreate(
                ['sku' => $product['sku']],
                [
                    'category_id' => $categories[$product['category']],
                    'primary_supplier_id' => $suppliers[$product['supplier']],
                    'barcode' => $product['barcode'],
                    'name' => $product['name'],
                    'slug' => Str::slug($product['name'] . '-' . $product['sku']),
                    'description' => $product['name'] . ' untuk operasional retail dan channel online.',
                    'unit_of_measure' => $product['uom'],
                    'cost_price' => $product['cost'],
                    'selling_price' => $product['price'],
                    'daily_run_rate' => $product['run_rate'],
                    'reorder_level' => $product['reorder_level'],
                    'reorder_quantity' => $product['reorder_quantity'],
                    'shelf_life_days' => $product['shelf_life_days'],
                    'status' => Product::STATUS_ACTIVE,
                    'is_featured' => $product['featured'],
                ]
            );
        }
    }
}
