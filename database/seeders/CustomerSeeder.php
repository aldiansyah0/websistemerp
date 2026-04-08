<?php

namespace Database\Seeders;

use App\Models\Customer;
use Illuminate\Database\Seeder;

class CustomerSeeder extends Seeder
{
    public function run(): void
    {
        $customers = [
            ['code' => 'CUST-001', 'name' => 'PT Nusantara Mart', 'email' => 'ap@nusantaramart.local', 'phone' => '021-5551001', 'segment' => 'Corporate', 'city' => 'Jakarta', 'address' => 'Jl. Sudirman Kav. 21, Jakarta', 'credit_limit' => 85_000_000, 'payment_term_days' => 21, 'status' => Customer::STATUS_ACTIVE, 'notes' => 'Akun B2B untuk kebutuhan hampers dan corporate order.'],
            ['code' => 'CUST-002', 'name' => 'Komunitas Sehat Bandung', 'email' => 'finance@sehatbandung.local', 'phone' => '022-7001002', 'segment' => 'Community', 'city' => 'Bandung', 'address' => 'Jl. Riau No. 88, Bandung', 'credit_limit' => 24_000_000, 'payment_term_days' => 14, 'status' => Customer::STATUS_ACTIVE, 'notes' => 'Sering melakukan pembelian event dan snack box.'],
            ['code' => 'CUST-003', 'name' => 'Hotel Surya Kencana', 'email' => 'purchasing@suryakencana.local', 'phone' => '031-8801003', 'segment' => 'Corporate', 'city' => 'Surabaya', 'address' => 'Jl. Pemuda No. 12, Surabaya', 'credit_limit' => 40_000_000, 'payment_term_days' => 30, 'status' => Customer::STATUS_ACTIVE, 'notes' => 'Order reguler untuk minibar dan retail desk.'],
            ['code' => 'CUST-004', 'name' => 'Member Premium Bekasi', 'email' => 'member.bekasi@example.test', 'phone' => '081300000444', 'segment' => 'Membership', 'city' => 'Bekasi', 'address' => 'Cluster Emerald Garden, Bekasi', 'credit_limit' => 7_500_000, 'payment_term_days' => 7, 'status' => Customer::STATUS_ACTIVE, 'notes' => 'Customer loyal dengan pola invoice mingguan.'],
            ['code' => 'CUST-005', 'name' => 'Prospek Jogja Collective', 'email' => 'hello@jogjacollective.local', 'phone' => '0274-7001005', 'segment' => 'Wholesale', 'city' => 'Yogyakarta', 'address' => 'Jl. Kaliurang Km 7, Sleman', 'credit_limit' => 15_000_000, 'payment_term_days' => 14, 'status' => Customer::STATUS_PROSPECT, 'notes' => 'Masih tahap negosiasi untuk pembelian grosir.'],
        ];

        foreach ($customers as $customer) {
            Customer::query()->updateOrCreate(
                ['code' => $customer['code']],
                $customer
            );
        }
    }
}
