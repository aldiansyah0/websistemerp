<?php

namespace Database\Seeders;

use App\Models\Outlet;
use App\Models\Warehouse;
use Illuminate\Database\Seeder;

class OutletSeeder extends Seeder
{
    public function run(): void
    {
        $warehouses = Warehouse::query()->pluck('id', 'code');

        $outlets = [
            [
                'code' => 'OTL-JKT01',
                'name' => 'Jakarta Flagship',
                'region' => 'Central',
                'city' => 'Jakarta',
                'phone' => '021-5500101',
                'manager_name' => 'Rina Hartanto',
                'warehouse_id' => $warehouses['OUT-JKT01'] ?? null,
                'opening_date' => '2022-02-10',
                'status' => Outlet::STATUS_ACTIVE,
                'daily_sales_target' => 38_000_000,
                'service_level' => 97.8,
                'inventory_accuracy' => 98.6,
                'is_fulfillment_hub' => true,
                'address' => 'Jl. Sudirman No. 88, Jakarta',
            ],
            [
                'code' => 'OTL-BDG01',
                'name' => 'Bandung Dago',
                'region' => 'West',
                'city' => 'Bandung',
                'phone' => '022-7011002',
                'manager_name' => 'Maya Salsabila',
                'warehouse_id' => null,
                'opening_date' => '2023-05-16',
                'status' => Outlet::STATUS_ACTIVE,
                'daily_sales_target' => 24_000_000,
                'service_level' => 96.5,
                'inventory_accuracy' => 97.2,
                'is_fulfillment_hub' => false,
                'address' => 'Jl. Ir. H. Djuanda No. 54, Bandung',
            ],
            [
                'code' => 'OTL-SBY01',
                'name' => 'Surabaya Tunjungan',
                'region' => 'East',
                'city' => 'Surabaya',
                'phone' => '031-8803400',
                'manager_name' => 'Arif Nugroho',
                'warehouse_id' => $warehouses['OUT-SBY01'] ?? null,
                'opening_date' => '2022-11-08',
                'status' => Outlet::STATUS_ACTIVE,
                'daily_sales_target' => 29_000_000,
                'service_level' => 97.1,
                'inventory_accuracy' => 97.9,
                'is_fulfillment_hub' => true,
                'address' => 'Jl. Tunjungan No. 31, Surabaya',
            ],
            [
                'code' => 'OTL-BKS01',
                'name' => 'Bekasi Summarecon',
                'region' => 'West',
                'city' => 'Bekasi',
                'phone' => '021-8802204',
                'manager_name' => 'Dimas Putra',
                'warehouse_id' => null,
                'opening_date' => '2024-01-20',
                'status' => Outlet::STATUS_ACTIVE,
                'daily_sales_target' => 21_000_000,
                'service_level' => 95.8,
                'inventory_accuracy' => 96.7,
                'is_fulfillment_hub' => false,
                'address' => 'Jl. Boulevard Ahmad Yani Blok M, Bekasi',
            ],
            [
                'code' => 'OTL-YGY01',
                'name' => 'Yogyakarta Malioboro',
                'region' => 'Central',
                'city' => 'Yogyakarta',
                'phone' => '0274-910011',
                'manager_name' => 'Sinta Wibowo',
                'warehouse_id' => null,
                'opening_date' => '2024-08-14',
                'status' => Outlet::STATUS_RENOVATION,
                'daily_sales_target' => 18_000_000,
                'service_level' => 92.4,
                'inventory_accuracy' => 95.1,
                'is_fulfillment_hub' => false,
                'address' => 'Jl. Malioboro No. 120, Yogyakarta',
            ],
        ];

        foreach ($outlets as $outlet) {
            Outlet::query()->updateOrCreate(
                ['code' => $outlet['code']],
                $outlet
            );
        }
    }
}
