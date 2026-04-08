<?php

namespace Database\Seeders;

use App\Models\Warehouse;
use Illuminate\Database\Seeder;

class WarehouseSeeder extends Seeder
{
    public function run(): void
    {
        $warehouses = [
            [
                'code' => 'DC-BARAT',
                'name' => 'DC Barat',
                'type' => Warehouse::TYPE_DISTRIBUTION_CENTER,
                'city' => 'Jakarta',
                'address' => 'Jl. Logistik Barat No. 1, Jakarta',
            ],
            [
                'code' => 'DC-TIMUR',
                'name' => 'DC Timur',
                'type' => Warehouse::TYPE_DISTRIBUTION_CENTER,
                'city' => 'Surabaya',
                'address' => 'Jl. Logistik Timur No. 8, Surabaya',
            ],
            [
                'code' => 'OUT-JKT01',
                'name' => 'Jakarta Flagship',
                'type' => Warehouse::TYPE_OUTLET,
                'city' => 'Jakarta',
                'address' => 'Jl. Sudirman No. 88, Jakarta',
            ],
            [
                'code' => 'OUT-SBY01',
                'name' => 'Surabaya Tunjungan',
                'type' => Warehouse::TYPE_OUTLET,
                'city' => 'Surabaya',
                'address' => 'Jl. Tunjungan No. 31, Surabaya',
            ],
        ];

        foreach ($warehouses as $warehouse) {
            Warehouse::query()->updateOrCreate(
                ['code' => $warehouse['code']],
                $warehouse + ['is_active' => true]
            );
        }
    }
}
