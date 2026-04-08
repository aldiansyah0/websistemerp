<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Supplier;
use App\Models\User;
use App\Models\Warehouse;
use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;

class PurchaseOrderSeeder extends Seeder
{
    public function run(): void
    {
        $userId = User::query()->value('id');
        $suppliers = Supplier::query()->pluck('id', 'code');
        $warehouses = Warehouse::query()->pluck('id', 'code');
        $products = Product::query()->pluck('id', 'sku');

        $orders = [
            [
                'po_number' => 'PO-2404-018',
                'supplier' => 'SUP-001',
                'warehouse' => 'DC-BARAT',
                'status' => PurchaseOrder::STATUS_PENDING_APPROVAL,
                'order_date' => '2026-04-08',
                'expected_date' => '2026-04-10',
                'submitted_at' => CarbonImmutable::parse('2026-04-08 09:00'),
                'items' => [
                    ['sku' => 'MIN-2201', 'qty' => 240, 'cost' => 7600],
                    ['sku' => 'BEV-4410', 'qty' => 72, 'cost' => 18100],
                ],
                'notes' => 'Top-up fast moving beverages untuk cluster barat.',
            ],
            [
                'po_number' => 'PO-2404-021',
                'supplier' => 'SUP-003',
                'warehouse' => 'DC-TIMUR',
                'status' => PurchaseOrder::STATUS_DRAFT,
                'order_date' => '2026-04-08',
                'expected_date' => '2026-04-12',
                'submitted_at' => null,
                'items' => [
                    ['sku' => 'HOU-0330', 'qty' => 96, 'cost' => 42300],
                ],
                'notes' => 'Review harga supplier masih berjalan.',
            ],
            [
                'po_number' => 'PO-2404-024',
                'supplier' => 'SUP-004',
                'warehouse' => 'DC-TIMUR',
                'status' => PurchaseOrder::STATUS_PENDING_APPROVAL,
                'order_date' => '2026-04-08',
                'expected_date' => '2026-04-09',
                'submitted_at' => CarbonImmutable::parse('2026-04-08 08:15'),
                'items' => [
                    ['sku' => 'FRE-1091', 'qty' => 84, 'cost' => 24300],
                    ['sku' => 'FRE-1188', 'qty' => 60, 'cost' => 19500],
                ],
                'notes' => 'Urgent replenishment untuk cluster fresh timur.',
            ],
            [
                'po_number' => 'PO-2404-027',
                'supplier' => 'SUP-005',
                'warehouse' => 'DC-BARAT',
                'status' => PurchaseOrder::STATUS_APPROVED,
                'order_date' => '2026-04-06',
                'expected_date' => '2026-04-14',
                'submitted_at' => CarbonImmutable::parse('2026-04-06 11:40'),
                'approved_at' => CarbonImmutable::parse('2026-04-06 15:10'),
                'items' => [
                    ['sku' => 'PER-0724', 'qty' => 72, 'cost' => 28700],
                    ['sku' => 'PER-4419', 'qty' => 180, 'cost' => 9400],
                ],
                'notes' => 'Approved by finance dan siap inbound minggu depan.',
            ],
            [
                'po_number' => 'PO-2404-011',
                'supplier' => 'SUP-002',
                'warehouse' => 'DC-BARAT',
                'status' => PurchaseOrder::STATUS_RECEIVED,
                'order_date' => '2026-04-02',
                'expected_date' => '2026-04-05',
                'submitted_at' => CarbonImmutable::parse('2026-04-02 09:00'),
                'approved_at' => CarbonImmutable::parse('2026-04-02 12:20'),
                'received_at' => CarbonImmutable::parse('2026-04-05 10:30'),
                'items' => [
                    ['sku' => 'SNK-1808', 'qty' => 180, 'cost' => 15500, 'received' => 180],
                    ['sku' => 'SNK-2014', 'qty' => 150, 'cost' => 9800, 'received' => 150],
                ],
                'notes' => 'Inbound snack berjalan sesuai rencana.',
            ],
            [
                'po_number' => 'PO-2403-098',
                'supplier' => 'SUP-003',
                'warehouse' => 'DC-TIMUR',
                'status' => PurchaseOrder::STATUS_REJECTED,
                'order_date' => '2026-03-31',
                'expected_date' => '2026-04-04',
                'submitted_at' => CarbonImmutable::parse('2026-03-31 10:00'),
                'items' => [
                    ['sku' => 'HOU-0520', 'qty' => 140, 'cost' => 12100],
                ],
                'notes' => 'Rejected karena price variance di atas batas toleransi.',
            ],
        ];

        foreach ($orders as $orderData) {
            $purchaseOrder = PurchaseOrder::query()->updateOrCreate(
                ['po_number' => $orderData['po_number']],
                [
                    'supplier_id' => $suppliers[$orderData['supplier']],
                    'warehouse_id' => $warehouses[$orderData['warehouse']],
                    'created_by' => $userId,
                    'approved_by' => in_array($orderData['status'], [PurchaseOrder::STATUS_APPROVED, PurchaseOrder::STATUS_RECEIVED], true) ? $userId : null,
                    'order_date' => $orderData['order_date'],
                    'expected_date' => $orderData['expected_date'],
                    'submitted_at' => $orderData['submitted_at'] ?? null,
                    'approved_at' => $orderData['approved_at'] ?? null,
                    'received_at' => $orderData['received_at'] ?? null,
                    'status' => $orderData['status'],
                    'terms' => 'Pembayaran mengikuti termin supplier.',
                    'notes' => $orderData['notes'],
                ]
            );

            $purchaseOrder->items()->delete();

            $items = collect($orderData['items'])->map(function (array $item) use ($purchaseOrder, $products): PurchaseOrderItem {
                $lineTotal = ($item['qty'] * $item['cost']);

                return $purchaseOrder->items()->create([
                    'product_id' => $products[$item['sku']],
                    'ordered_quantity' => $item['qty'],
                    'received_quantity' => $item['received'] ?? 0,
                    'unit_cost' => $item['cost'],
                    'discount_amount' => 0,
                    'line_total' => $lineTotal,
                    'notes' => null,
                ]);
            });

            $purchaseOrder->recalculateTotals($items);
            $purchaseOrder->save();
        }
    }
}
