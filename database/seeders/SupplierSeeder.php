<?php

namespace Database\Seeders;

use App\Models\Supplier;
use Illuminate\Database\Seeder;

class SupplierSeeder extends Seeder
{
    public function run(): void
    {
        $suppliers = [
            [
                'code' => 'SUP-001',
                'name' => 'PT Prima Rasa',
                'contact_person' => 'Mira Kurnia',
                'email' => 'purchasing@primarasa.test',
                'phone' => '021-555-1201',
                'city' => 'Jakarta',
                'address' => 'Jl. Industri Raya No. 18, Jakarta',
                'lead_time_days' => 3,
                'payment_term_days' => 30,
                'fill_rate' => 97.20,
                'reject_rate' => 0.60,
                'rating' => 4.80,
            ],
            [
                'code' => 'SUP-002',
                'name' => 'CV Rasa Nusantara',
                'contact_person' => 'Arman Wijaya',
                'email' => 'sales@rasanusantara.test',
                'phone' => '022-777-8801',
                'city' => 'Bandung',
                'address' => 'Jl. Mekar Rasa No. 7, Bandung',
                'lead_time_days' => 4,
                'payment_term_days' => 21,
                'fill_rate' => 96.40,
                'reject_rate' => 0.80,
                'rating' => 4.50,
            ],
            [
                'code' => 'SUP-003',
                'name' => 'CV Sumber Bersih',
                'contact_person' => 'Rahma Dewi',
                'email' => 'ops@sumberbersih.test',
                'phone' => '031-444-1902',
                'city' => 'Surabaya',
                'address' => 'Jl. Bersih Sentosa No. 10, Surabaya',
                'lead_time_days' => 6,
                'payment_term_days' => 21,
                'fill_rate' => 92.80,
                'reject_rate' => 1.40,
                'rating' => 4.10,
            ],
            [
                'code' => 'SUP-004',
                'name' => 'PT Fresh Harvest',
                'contact_person' => 'Nadia Pertiwi',
                'email' => 'support@freshharvest.test',
                'phone' => '0361-881-004',
                'city' => 'Denpasar',
                'address' => 'Jl. Panen Segar No. 2, Denpasar',
                'lead_time_days' => 2,
                'payment_term_days' => 14,
                'fill_rate' => 89.30,
                'reject_rate' => 2.10,
                'rating' => 3.80,
            ],
            [
                'code' => 'SUP-005',
                'name' => 'PT Derma Supply',
                'contact_person' => 'Raka Mahendra',
                'email' => 'account@dermasupply.test',
                'phone' => '021-888-5102',
                'city' => 'Jakarta',
                'address' => 'Jl. Sehat Sejahtera No. 22, Jakarta',
                'lead_time_days' => 4,
                'payment_term_days' => 30,
                'fill_rate' => 95.90,
                'reject_rate' => 0.90,
                'rating' => 4.40,
            ],
        ];

        foreach ($suppliers as $supplier) {
            Supplier::query()->updateOrCreate(
                ['code' => $supplier['code']],
                $supplier + ['is_active' => true]
            );
        }
    }
}
