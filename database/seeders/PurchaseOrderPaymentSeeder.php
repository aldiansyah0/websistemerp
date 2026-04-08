<?php

namespace Database\Seeders;

use App\Models\PaymentMethod;
use App\Models\PurchaseOrder;
use Illuminate\Database\Seeder;

class PurchaseOrderPaymentSeeder extends Seeder
{
    public function run(): void
    {
        $paymentMethods = PaymentMethod::query()->pluck('id', 'code');

        $payments = [
            ['po' => 'PO-2604-001', 'method' => 'TRANSFER', 'date' => '2026-04-08 16:10:00', 'amount' => 1850000, 'reference' => 'SUP-TRF-001', 'invoice' => 'INV-SUP-001'],
            ['po' => 'PO-2604-002', 'method' => 'DEBIT-BCA', 'date' => '2026-04-08 17:00:00', 'amount' => 920000, 'reference' => 'SUP-TRF-002', 'invoice' => 'INV-SUP-002'],
            ['po' => 'PO-2604-004', 'method' => 'TRANSFER', 'date' => '2026-04-07 15:45:00', 'amount' => 640000, 'reference' => 'SUP-TRF-004', 'invoice' => 'INV-SUP-004'],
        ];

        foreach ($payments as $payload) {
            $purchaseOrder = PurchaseOrder::query()->where('po_number', $payload['po'])->first();

            if (! $purchaseOrder) {
                continue;
            }

            $purchaseOrder->payments()->updateOrCreate(
                ['reference_number' => $payload['reference']],
                [
                    'payment_method_id' => $paymentMethods[$payload['method']] ?? null,
                    'payment_date' => $payload['date'],
                    'amount' => $payload['amount'],
                    'approval_code' => 'PO-' . substr(md5($payload['reference']), 0, 6),
                    'notes' => 'Seed payment supplier',
                ]
            );

            $paidAmount = (float) $purchaseOrder->payments()->sum('amount');
            $purchaseOrder->supplier_invoice_number = $purchaseOrder->supplier_invoice_number ?: $payload['invoice'];
            $purchaseOrder->paid_amount = $paidAmount;
            $purchaseOrder->balance_due = max((float) $purchaseOrder->total_amount - $paidAmount, 0);
            $purchaseOrder->payment_status = match (true) {
                (float) $purchaseOrder->balance_due <= 0.0001 => 'paid',
                $paidAmount > 0 => 'partial',
                default => 'unpaid',
            };
            $purchaseOrder->save();
        }
    }
}
