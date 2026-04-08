<?php

namespace Database\Seeders;

use App\Models\PaymentMethod;
use Illuminate\Database\Seeder;

class PaymentMethodSeeder extends Seeder
{
    public function run(): void
    {
        $methods = [
            ['code' => 'CASH', 'name' => 'Cash', 'category' => 'cash', 'provider' => null, 'fee' => 0, 'settlement' => 0],
            ['code' => 'QRIS', 'name' => 'QRIS', 'category' => 'qris', 'provider' => 'Midtrans', 'fee' => 0.70, 'settlement' => 1],
            ['code' => 'DEBIT-BCA', 'name' => 'Debit Card BCA', 'category' => 'card', 'provider' => 'BCA EDC', 'fee' => 1.20, 'settlement' => 2],
            ['code' => 'CC-VISA', 'name' => 'Credit Card Visa', 'category' => 'card', 'provider' => 'Mandiri EDC', 'fee' => 2.10, 'settlement' => 3],
            ['code' => 'GOPAY', 'name' => 'GoPay', 'category' => 'ewallet', 'provider' => 'GoTo', 'fee' => 1.00, 'settlement' => 1],
            ['code' => 'TRANSFER', 'name' => 'Bank Transfer', 'category' => 'bank_transfer', 'provider' => 'Virtual Account', 'fee' => 0.40, 'settlement' => 1],
            ['code' => 'VOUCHER', 'name' => 'Voucher Store', 'category' => 'voucher', 'provider' => 'Internal', 'fee' => 0, 'settlement' => 0],
        ];

        foreach ($methods as $method) {
            PaymentMethod::query()->updateOrCreate(
                ['code' => $method['code']],
                [
                    'name' => $method['name'],
                    'category' => $method['category'],
                    'provider' => $method['provider'],
                    'transaction_fee_rate' => $method['fee'],
                    'settlement_days' => $method['settlement'],
                    'is_active' => true,
                ]
            );
        }
    }
}
