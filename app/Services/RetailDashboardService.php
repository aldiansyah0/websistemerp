<?php

namespace App\Services;

use App\Models\AttendanceLog;
use App\Models\Employee;
use App\Models\InventoryLedger;
use App\Models\Outlet;
use App\Models\PaymentMethod;
use App\Models\PayrollRun;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\SalesTransaction;
use App\Models\Supplier;
use App\Models\Warehouse;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class RetailDashboardService
{
    public function __construct(
        private readonly AnalyticsCacheService $analyticsCacheService,
    ) {
    }

    public function build(): array
    {
        return $this->analyticsCacheService->rememberDashboard(function (): array {
            $products = $this->productsWithStock();
            $warehouseRows = $this->warehouseRows();
            $purchaseOrders = $this->purchaseOrders();
            $suppliers = $this->suppliers();
            $salesDate = $this->latestSalesDate();
            $attendanceDate = $this->latestAttendanceDate();
            $salesMonthStart = $salesDate->startOfMonth();

            $outlets = Outlet::query()
            ->with(['employees', 'salesTransactions.payments.paymentMethod'])
            ->orderBy('name')
            ->get();
            $employees = Employee::query()
            ->with('outlet')
            ->orderBy('full_name')
            ->get();
            $attendanceLogs = AttendanceLog::query()
            ->with(['employee', 'outlet'])
            ->whereDate('shift_date', $attendanceDate->toDateString())
            ->get();
            $latestPayroll = PayrollRun::query()
            ->with(['items.employee.outlet'])
            ->orderByDesc('period_end')
            ->first();
            $salesTransactions = SalesTransaction::query()
            ->with(['outlet', 'payments.paymentMethod'])
            ->where('status', 'paid')
            ->orderByDesc('sold_at')
            ->get();
            $paymentMethods = PaymentMethod::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $activeProducts = $products->where('status', Product::STATUS_ACTIVE);
        $positiveRows = $warehouseRows->where('on_hand', '>', 0);
        $activeSuppliers = $suppliers->where('is_active', true);
        $openPurchaseOrders = $purchaseOrders->whereIn('status', [
            PurchaseOrder::STATUS_DRAFT,
            PurchaseOrder::STATUS_PENDING_APPROVAL,
            PurchaseOrder::STATUS_APPROVED,
            PurchaseOrder::STATUS_PARTIALLY_RECEIVED,
        ]);
        $pendingApproval = $purchaseOrders->where('status', PurchaseOrder::STATUS_PENDING_APPROVAL);
        $approvedOrders = $purchaseOrders->where('status', PurchaseOrder::STATUS_APPROVED);
        $lowCoverProducts = $activeProducts->filter(fn (Product $product): bool => $this->daysCover($product) !== null && $this->daysCover($product) <= 3);
        $replenishmentCandidates = $activeProducts
            ->filter(fn (Product $product): bool => $this->daysCover($product) !== null)
            ->sortBy(fn (Product $product): float => $this->daysCover($product) ?? 9999);
        $inventoryValue = $activeProducts->sum(fn (Product $product): float => $this->stockValue($product));
        $onHandAvailability = $this->percent($activeProducts->where('current_stock', '>', 0)->count(), max($activeProducts->count(), 1));
        $warehouseCount = Warehouse::query()->where('is_active', true)->count();
        $agingValue = $this->agingValue($positiveRows, 30);
        $atRiskSuppliers = $activeSuppliers->filter(fn (Supplier $supplier): bool => (float) $supplier->fill_rate < 93 || (float) $supplier->reject_rate > 1.5);
        $activeOutlets = $outlets->where('status', Outlet::STATUS_ACTIVE);
        $activeEmployees = $employees->where('status', Employee::STATUS_ACTIVE);
        $mtdTransactions = $salesTransactions->filter(fn (SalesTransaction $transaction): bool => $transaction->sold_at !== null && $transaction->sold_at->between($salesMonthStart->startOfDay(), $salesDate->endOfDay()));
        $splitTransactions = $salesTransactions->where('split_payment_count', '>', 1);
        $payments = $salesTransactions->flatMap(fn (SalesTransaction $transaction): Collection => $transaction->payments);
        $attendanceReady = $this->percent($attendanceLogs->whereIn('attendance_status', ['present', 'late'])->count(), max($attendanceLogs->count(), 1));
        $averageServiceLevel = (float) $activeOutlets->avg('service_level');
        $gatewayFee = $payments->sum(function ($payment): float {
            $feeRate = (float) ($payment->paymentMethod?->transaction_fee_rate ?? 0);

            return ((float) $payment->amount * $feeRate) / 100;
        });
        $digitalPaymentAmount = $payments
            ->filter(fn ($payment): bool => in_array($payment->paymentMethod?->category, ['qris', 'card', 'ewallet', 'bank_transfer'], true))
            ->sum('amount');
        $laborCostRatio = $this->percent((float) ($latestPayroll?->total_net ?? 0), max($mtdTransactions->sum('net_amount'), 1));
        $attendanceIssues = $attendanceLogs->whereIn('attendance_status', ['late', 'leave', 'absent']);
        $overtimeExposure = $attendanceLogs->sum(function (AttendanceLog $log): float {
            return (float) ($log->employee?->overtime_rate ?? 0) * ((float) $log->overtime_minutes / 60);
        });
        $topStaffedOutlet = $activeEmployees
            ->whereNotNull('outlet_id')
            ->groupBy(fn (Employee $employee): string => $employee->outlet?->name ?? 'Head Office')
            ->map(fn (Collection $group): int => $group->count())
            ->sortDesc();
        $outletCards = $activeOutlets
            ->map(function (Outlet $outlet) use ($salesMonthStart, $salesDate): array {
                $transactions = $outlet->salesTransactions
                    ->where('status', 'paid')
                    ->filter(fn (SalesTransaction $transaction): bool => $transaction->sold_at !== null && $transaction->sold_at->between($salesMonthStart->startOfDay(), $salesDate->endOfDay()));
                $sales = (float) $transactions->sum('net_amount');

                return [
                    'name' => $outlet->name,
                    'region' => $outlet->region ?? '-',
                    'sales_raw' => $sales,
                    'sales' => $this->formatCompactCurrency($sales),
                    'split_ratio' => $this->formatPercent($this->percent($transactions->where('split_payment_count', '>', 1)->count(), max($transactions->count(), 1))),
                    'service_level' => $this->formatPercent((float) $outlet->service_level),
                    'headcount' => number_format($outlet->employees->where('status', Employee::STATUS_ACTIVE)->count(), 0, ',', '.') . ' aktif',
                    'status' => $this->outletStatus($outlet),
                ];
            })
            ->sortByDesc('sales_raw')
            ->take(4)
            ->values()
            ->map(function (array $card): array {
                unset($card['sales_raw']);

                return $card;
            })
            ->all();
        $workforceCards = [
            [
                'title' => 'Payroll run terakhir',
                'value' => $latestPayroll?->code ?? '-',
                'note' => 'Net salary ' . $this->formatCompactCurrency($latestPayroll?->total_net) . ' untuk ' . number_format((int) ($latestPayroll?->employee_count ?? 0), 0, ',', '.') . ' karyawan.',
            ],
            [
                'title' => 'Attendance risk',
                'value' => number_format($attendanceIssues->count(), 0, ',', '.'),
                'note' => 'Keterlambatan, cuti, atau absen pada tanggal operasi terakhir yang perlu ditutup agar floor coverage aman.',
            ],
            [
                'title' => 'Overtime exposure',
                'value' => $this->formatCompactCurrency($overtimeExposure),
                'note' => 'Estimasi lembur yang akan memengaruhi payroll bila pola roster saat ini tidak ditata ulang.',
            ],
            [
                'title' => 'Top staffed outlet',
                'value' => $topStaffedOutlet->keys()->first() ?? '-',
                'note' => number_format((int) ($topStaffedOutlet->first() ?? 0), 0, ',', '.') . ' karyawan aktif menjadi tulang punggung outlet dengan cakupan tim terbesar.',
            ],
        ];
        $paymentChannelCards = $paymentMethods
            ->map(function (PaymentMethod $paymentMethod) use ($payments): array {
                $methodPayments = $payments->filter(fn ($payment): bool => $payment->payment_method_id === $paymentMethod->id);
                $amount = (float) $methodPayments->sum('amount');

                return [
                    'name' => $paymentMethod->name,
                    'provider' => $paymentMethod->provider ?? 'Internal',
                    'amount_raw' => $amount,
                    'amount' => $this->formatCompactCurrency($amount),
                    'share' => $this->formatPercent($this->percent($amount, max($payments->sum('amount'), 1))),
                    'transactions' => number_format($methodPayments->count(), 0, ',', '.'),
                    'fee' => $this->formatPercent((float) $paymentMethod->transaction_fee_rate),
                ];
            })
            ->sortByDesc('amount_raw')
            ->take(4)
            ->values()
            ->map(function (array $card): array {
                unset($card['amount_raw']);

                return $card;
            })
            ->all();

        return [
            'title' => 'Dashboard',
            'pageTitle' => 'Executive Dashboard Retail',
            'pageEyebrow' => 'ERP Retail Control Tower',
            'pageDescription' => 'Seluruh KPI di dashboard ini sekarang dibaca langsung dari master produk, inventory ledger, supplier, warehouse, purchase order, outlet, SDM, payroll, dan transaksi pembayaran POS.',
            'generatedAt' => $this->timestamp(),
            'heroStats' => [
                ['label' => 'Outlet aktif', 'value' => number_format($activeOutlets->count(), 0, ',', '.'), 'caption' => 'Cabang retail yang saat ini melayani transaksi dan replenishment'],
                ['label' => 'Headcount aktif', 'value' => number_format($activeEmployees->count(), 0, ',', '.'), 'caption' => 'Karyawan aktif lintas outlet dan support function'],
                ['label' => 'Sales MTD', 'value' => $this->formatCompactCurrency($mtdTransactions->sum('net_amount')), 'caption' => 'Omzet retail berjalan dari transaksi POS yang sudah dibayar'],
                ['label' => 'Open PO', 'value' => number_format($openPurchaseOrders->count(), 0, ',', '.'), 'caption' => 'Dokumen pembelian yang masih hidup di pipeline operasional'],
            ],
            'executiveKpis' => [
                [
                    'label' => 'Inventory Value',
                    'value' => $this->formatCompactCurrency($inventoryValue),
                    'trend' => number_format($lowCoverProducts->count(), 0, ',', '.') . ' SKU low cover',
                    'direction' => $lowCoverProducts->isEmpty() ? 'up' : 'down',
                    'support' => number_format((float) $positiveRows->sum('on_hand'), 0, ',', '.') . ' unit on hand di seluruh lokasi',
                    'footnote' => 'Valuasi dihitung dari stok netto di ledger dikalikan cost produk.',
                ],
                [
                    'label' => 'On-Hand Availability',
                    'value' => $this->formatPercent($onHandAvailability),
                    'trend' => number_format($activeProducts->where('current_stock', '<=', 0)->count(), 0, ',', '.') . ' SKU kosong',
                    'direction' => $onHandAvailability >= 95 ? 'up' : 'down',
                    'support' => 'Mengukur seberapa banyak SKU aktif yang benar-benar tersedia untuk dijual atau dipindahkan.',
                    'footnote' => 'Semakin tinggi availability, semakin kecil potensi lost sales dan emergency buying.',
                ],
                [
                    'label' => 'Average Service Level',
                    'value' => $this->formatPercent($averageServiceLevel),
                    'trend' => number_format($activeOutlets->where('status', Outlet::STATUS_RENOVATION)->count(), 0, ',', '.') . ' outlet terhambat',
                    'direction' => $averageServiceLevel >= 96 ? 'up' : 'neutral',
                    'support' => 'Meringkas kualitas layanan antar outlet agar penjualan tidak dibaca tanpa konteks manpower dan readiness cabang.',
                    'footnote' => 'Service level perlu dibaca bersama kehadiran tim, stock availability, dan beban transaksi outlet.',
                ],
                [
                    'label' => 'Attendance Readiness',
                    'value' => $this->formatPercent($attendanceReady),
                    'trend' => number_format($attendanceIssues->count(), 0, ',', '.') . ' roster bermasalah',
                    'direction' => $attendanceReady >= 95 ? 'up' : 'down',
                    'support' => 'Kesiapan hadir tim outlet dan support function pada tanggal operasi terakhir.',
                    'footnote' => 'Absensi yang bergeser langsung memengaruhi service floor, lembur, dan potensi lost sales.',
                ],
                [
                    'label' => 'Split Payment Share',
                    'value' => $this->formatPercent($this->percent($splitTransactions->count(), max($salesTransactions->count(), 1))),
                    'trend' => $this->formatCompactCurrency($gatewayFee) . ' fee',
                    'direction' => 'neutral',
                    'support' => 'Porsi transaksi yang dibayar dengan dua metode atau lebih untuk membaca fleksibilitas checkout dan kompleksitas settlement.',
                    'footnote' => 'Split payment perlu dikontrol karena berdampak ke fee gateway, rekonsiliasi, dan pengalaman kasir di outlet.',
                ],
                [
                    'label' => 'Pending Approval Value',
                    'value' => $this->formatCompactCurrency($pendingApproval->sum('total_amount')),
                    'trend' => number_format($pendingApproval->count(), 0, ',', '.') . ' PO menunggu',
                    'direction' => $pendingApproval->isEmpty() ? 'up' : 'neutral',
                    'support' => 'Nilai pembelian yang belum bisa dilepas ke supplier karena workflow belum selesai.',
                    'footnote' => 'Approval yang lambat langsung menekan cover stok kritikal di gudang dan outlet.',
                ],
                [
                    'label' => 'Approved Inbound',
                    'value' => $this->formatCompactCurrency($approvedOrders->sum('total_amount')),
                    'trend' => number_format($approvedOrders->count(), 0, ',', '.') . ' PO siap inbound',
                    'direction' => $approvedOrders->isEmpty() ? 'neutral' : 'up',
                    'support' => 'Komitmen suplai yang sudah disetujui dan siap dikawal ke receiving.',
                    'footnote' => 'Ini menjadi jembatan utama antara buyer, supplier, warehouse, dan inventory control.',
                ],
                [
                    'label' => 'Payroll Exposure',
                    'value' => $this->formatCompactCurrency($latestPayroll?->total_net),
                    'trend' => $this->formatPercent($laborCostRatio) . ' labor cost ratio',
                    'direction' => $laborCostRatio <= 12 ? 'up' : 'neutral',
                    'support' => 'Eksposur take home pay dari payroll run terbaru terhadap kemampuan penjualan retail saat ini.',
                    'footnote' => 'Payroll harus dibaca sebagai bagian dari profitabilitas outlet, bukan laporan HR yang berdiri sendiri.',
                ],
                [
                    'label' => 'Supplier Fill Rate',
                    'value' => $this->formatPercent($activeSuppliers->avg('fill_rate')),
                    'trend' => number_format($atRiskSuppliers->count(), 0, ',', '.') . ' supplier at risk',
                    'direction' => $atRiskSuppliers->isEmpty() ? 'up' : 'neutral',
                    'support' => 'Rata-rata kemampuan vendor memenuhi kuantitas yang dipesan buyer.',
                    'footnote' => 'Lead time, fill rate, dan reject rate wajib dibaca bersamaan sebelum membuka PO baru.',
                ],
                [
                    'label' => 'Aging Stock > 30 Hari',
                    'value' => $this->formatCompactCurrency($agingValue),
                    'trend' => $this->formatPercent($this->percent($agingValue, max($inventoryValue, 1))),
                    'direction' => $agingValue > 0 ? 'down' : 'up',
                    'support' => 'Nilai stok yang sudah terlalu lama tidak bergerak dan berpotensi menekan margin.',
                    'footnote' => 'Aging inventory perlu dihubungkan ke promo, markdown, transfer, atau stop-buy decision.',
                ],
            ],
            'replenishmentQueue' => $replenishmentCandidates
                ->take(6)
                ->map(fn (Product $product): array => [
                    'sku' => $product->sku,
                    'name' => $product->name,
                    'category' => $product->category?->name ?? '-',
                    'supplier' => $product->primarySupplier?->name ?? '-',
                    'stock' => number_format((float) $product->current_stock, 0, ',', '.') . ' ' . $product->unit_of_measure,
                    'days_cover' => $this->formatNullableDays($this->daysCover($product)),
                    'action' => 'Review reorder ' . number_format((float) $product->reorder_quantity, 0, ',', '.') . ' ' . $product->unit_of_measure,
                ])->values()->all(),
            'procurementQueue' => $purchaseOrders
                ->filter(fn (PurchaseOrder $purchaseOrder): bool => in_array($purchaseOrder->status, [PurchaseOrder::STATUS_PENDING_APPROVAL, PurchaseOrder::STATUS_APPROVED, PurchaseOrder::STATUS_DRAFT], true))
                ->take(6)
                ->map(fn (PurchaseOrder $purchaseOrder): array => [
                    'po_number' => $purchaseOrder->po_number,
                    'supplier' => $purchaseOrder->supplier?->name ?? '-',
                    'warehouse' => $purchaseOrder->warehouse?->name ?? '-',
                    'eta' => $purchaseOrder->expected_date?->format('d M Y') ?? '-',
                    'amount' => $this->formatCompactCurrency((float) $purchaseOrder->total_amount),
                    'status' => PurchaseOrder::statusOptions()[$purchaseOrder->status] ?? ucfirst($purchaseOrder->status),
                ])->values()->all(),
            'categoryMix' => $this->categoryMix($activeProducts),
            'warehouseCards' => $this->warehouseCards($positiveRows),
            'outletCards' => $outletCards,
            'workforceCards' => $workforceCards,
            'paymentChannelCards' => $paymentChannelCards,
            'supplierCards' => $suppliers
                ->take(4)
                ->map(fn (Supplier $supplier): array => [
                    'name' => $supplier->name,
                    'fill_rate' => $this->formatPercent($supplier->fill_rate),
                    'lead_time' => $this->formatDecimal($supplier->lead_time_days) . ' hari',
                    'open_value' => $this->formatCompactCurrency((float) $supplier->open_po_total),
                    'status' => $this->supplierStatus($supplier),
                ])->values()->all(),
            'alerts' => $this->alerts($lowCoverProducts, $pendingApproval, $purchaseOrders, $atRiskSuppliers, $agingValue, $attendanceLogs, $latestPayroll),
            'moduleMap' => [
                ['name' => 'Master Produk', 'status' => 'Live', 'detail' => number_format($activeProducts->count(), 0, ',', '.') . ' SKU aktif dengan pricing, supplier, dan reorder policy terhubung.'],
                ['name' => 'Inventory Ledger', 'status' => 'Live', 'detail' => number_format(InventoryLedger::query()->count(), 0, ',', '.') . ' pergerakan stok menjadi sumber valuasi, aging, dan cover.'],
                ['name' => 'Multi Outlet', 'status' => 'Live', 'detail' => number_format($outlets->count(), 0, ',', '.') . ' outlet kini menjadi fondasi target sales, staffing, dan performance cabang.'],
                ['name' => 'Supplier Directory', 'status' => 'Live', 'detail' => number_format($activeSuppliers->count(), 0, ',', '.') . ' supplier aktif dengan SLA pembelian dan spend yang bisa dievaluasi.'],
                ['name' => 'Purchase Order', 'status' => 'Live', 'detail' => number_format($purchaseOrders->count(), 0, ',', '.') . ' dokumen PO sudah ikut memengaruhi dashboard dan workflow approval.'],
                ['name' => 'HR Module', 'status' => 'Live', 'detail' => number_format($employees->count(), 0, ',', '.') . ' profil karyawan, absensi, dan payroll sudah terhubung ke operasional retail.'],
                ['name' => 'Split Payment POS', 'status' => 'Live', 'detail' => $this->formatPercent($this->percent($digitalPaymentAmount, max($payments->sum('amount'), 1))) . ' payment amount sudah terbaca dari kanal digital dan split checkout.'],
            ],
            'formulaCards' => [
                ['name' => 'Inventory Value', 'formula' => 'On hand x cost price', 'why' => 'Menentukan besar modal kerja yang tertahan di stok.'],
                ['name' => 'Days Cover', 'formula' => 'Current stock / daily run rate', 'why' => 'Membaca seberapa cepat SKU akan masuk risiko stockout.'],
                ['name' => 'Sales vs Target', 'formula' => 'Sales MTD / target MTD outlet', 'why' => 'Mengukur apakah jaringan outlet bergerak sesuai rencana komersial.'],
                ['name' => 'Attendance Rate', 'formula' => 'Present + late / scheduled staff', 'why' => 'Membaca kesiapan tim sebelum service level dan payroll dibahas lebih jauh.'],
                ['name' => 'Pending Approval Value', 'formula' => 'Total PO pending approval', 'why' => 'Menunjukkan belanja yang tertahan di workflow dan belum menjadi inbound.'],
                ['name' => 'Split Payment Share', 'formula' => 'Transaksi split / total transaksi paid', 'why' => 'Membaca kompleksitas checkout dan kebutuhan rekonsiliasi payment channel.'],
                ['name' => 'Labor Cost Ratio', 'formula' => 'Payroll net / sales MTD', 'why' => 'Menjaga biaya tenaga kerja tetap seimbang terhadap produktivitas outlet.'],
                ['name' => 'Supplier Fill Rate', 'formula' => 'Qty terpenuhi / qty dipesan', 'why' => 'Menguji apakah supplier sanggup mengimbangi demand retail.'],
            ],
        ];
        });
    }

    private function productsWithStock(): Collection
    {
        return Product::query()
            ->with(['category', 'primarySupplier'])
            ->leftJoinSub($this->productStockSubquery(), 'stock_positions', function ($join): void {
                $join->on('stock_positions.product_id', '=', 'products.id');
            })
            ->select('products.*')
            ->selectRaw('COALESCE(stock_positions.current_stock, 0) as current_stock')
            ->orderBy('products.name')
            ->get();
    }

    private function productStockSubquery(): Builder
    {
        return InventoryLedger::query()
            ->select('product_id')
            ->selectRaw('SUM(quantity) as current_stock')
            ->groupBy('product_id');
    }

    private function warehouseRows(): Collection
    {
        $query = DB::table('inventory_ledgers')
            ->join('products', 'products.id', '=', 'inventory_ledgers.product_id')
            ->join('warehouses', 'warehouses.id', '=', 'inventory_ledgers.warehouse_id')
            ->select(
                'products.id as product_id',
                'products.name as product_name',
                'products.cost_price',
                'products.daily_run_rate',
                'products.reorder_level',
                'warehouses.id as warehouse_id',
                'warehouses.name as warehouse_name',
                'warehouses.type as warehouse_type'
            )
            ->selectRaw('SUM(inventory_ledgers.quantity) as on_hand')
            ->selectRaw('MAX(inventory_ledgers.transaction_at) as last_movement_at');

        $user = auth()->user();
        if ($user !== null && Schema::hasColumn('inventory_ledgers', 'tenant_id') && filled($user->tenant_id)) {
            $query->where('inventory_ledgers.tenant_id', (int) $user->tenant_id);
        }
        if ($user !== null && Schema::hasColumn('inventory_ledgers', 'location_id') && filled($user->location_id)) {
            $query->where('inventory_ledgers.location_id', (int) $user->location_id);
        }

        return $query
            ->groupBy('products.id', 'products.name', 'products.cost_price', 'products.daily_run_rate', 'products.reorder_level', 'warehouses.id', 'warehouses.name', 'warehouses.type')
            ->get()
            ->map(function (object $row): object {
                $row->on_hand = (float) $row->on_hand;
                $row->cost_price = (float) $row->cost_price;
                $row->daily_run_rate = (float) $row->daily_run_rate;
                $row->reorder_level = (float) $row->reorder_level;
                $row->last_movement_at = $row->last_movement_at ? CarbonImmutable::parse($row->last_movement_at) : null;

                return $row;
            });
    }

    private function purchaseOrders(): Collection
    {
        return PurchaseOrder::query()
            ->with(['supplier', 'warehouse'])
            ->orderByRaw(
                "case status
                    when ? then 1
                    when ? then 2
                    when ? then 3
                    when ? then 4
                    when ? then 5
                    when ? then 6
                    when ? then 7
                    else 99
                end",
                [
                    PurchaseOrder::STATUS_PENDING_APPROVAL,
                    PurchaseOrder::STATUS_DRAFT,
                    PurchaseOrder::STATUS_APPROVED,
                    PurchaseOrder::STATUS_PARTIALLY_RECEIVED,
                    PurchaseOrder::STATUS_RECEIVED,
                    PurchaseOrder::STATUS_REJECTED,
                    PurchaseOrder::STATUS_CANCELLED,
                ]
            )
            ->orderByDesc('order_date')
            ->get();
    }

    private function suppliers(): Collection
    {
        return Supplier::query()
            ->withSum(['purchaseOrders as open_po_total' => function ($query): void {
                $query->whereIn('status', [
                    PurchaseOrder::STATUS_DRAFT,
                    PurchaseOrder::STATUS_PENDING_APPROVAL,
                    PurchaseOrder::STATUS_APPROVED,
                    PurchaseOrder::STATUS_PARTIALLY_RECEIVED,
                ]);
            }], 'total_amount')
            ->orderByDesc('fill_rate')
            ->get();
    }

    private function categoryMix(Collection $products): array
    {
        $totalValue = max($products->sum(fn (Product $product): float => $this->stockValue($product)), 1);

        return $products
            ->groupBy(fn (Product $product): string => $product->category?->name ?? 'Tanpa Kategori')
            ->map(function (Collection $group, string $name) use ($totalValue): array {
                $stockValue = $group->sum(fn (Product $product): float => $this->stockValue($product));
                $lowCover = $group->filter(fn (Product $product): bool => $this->daysCover($product) !== null && $this->daysCover($product) <= 3)->count();

                return [
                    'name' => $name,
                    'share' => (int) round(($stockValue / $totalValue) * 100),
                    'stock_value' => $this->formatCompactCurrency($stockValue),
                    'margin' => $this->formatPercent($group->avg(fn (Product $product): float => $this->marginPercent($product))),
                    'low_cover' => number_format($lowCover, 0, ',', '.') . ' SKU',
                ];
            })
            ->sortByDesc('share')
            ->take(5)
            ->values()
            ->all();
    }

    private function warehouseCards(Collection $rows): array
    {
        return $rows
            ->groupBy('warehouse_id')
            ->map(function (Collection $warehouseRows): array {
                $first = $warehouseRows->first();
                $stockValue = $warehouseRows->sum(fn (object $row): float => $row->on_hand * $row->cost_price);
                $critical = $warehouseRows->filter(fn (object $row): bool => $row->on_hand <= $row->reorder_level)->count();

                return [
                    'name' => $first->warehouse_name,
                    'type' => Warehouse::typeOptions()[$first->warehouse_type] ?? ucfirst(str_replace('_', ' ', $first->warehouse_type)),
                    'stock_value' => $this->formatCompactCurrency($stockValue),
                    'active_skus' => number_format($warehouseRows->count(), 0, ',', '.') . ' SKU',
                    'critical' => number_format($critical, 0, ',', '.') . ' kritikal',
                ];
            })
            ->sortByDesc(fn (array $card): string => $card['stock_value'])
            ->values()
            ->all();
    }

    private function alerts(Collection $lowCoverProducts, Collection $pendingApproval, Collection $purchaseOrders, Collection $atRiskSuppliers, float $agingValue, Collection $attendanceLogs, ?PayrollRun $latestPayroll): array
    {
        $alerts = [];

        if ($lowCoverProducts->isNotEmpty()) {
            $product = $lowCoverProducts->first();
            $alerts[] = [
                'severity' => 'critical',
                'title' => $product->name . ' masuk zona cover tipis',
                'detail' => 'SKU ' . $product->sku . ' hanya punya cover ' . $this->formatNullableDays($this->daysCover($product)) . ' dan perlu diputuskan replenishment hari ini.',
            ];
        }

        if ($pendingApproval->isNotEmpty()) {
            $alerts[] = [
                'severity' => 'high',
                'title' => number_format($pendingApproval->count(), 0, ',', '.') . ' PO masih menunggu approval',
                'detail' => 'Nilai tertahan ' . $this->formatCompactCurrency($pendingApproval->sum('total_amount')) . ' belum bisa bergerak ke supplier sampai approval selesai.',
            ];
        }

        $overdue = $purchaseOrders->filter(fn (PurchaseOrder $purchaseOrder): bool => $purchaseOrder->expected_date !== null && $purchaseOrder->expected_date->isPast() && in_array($purchaseOrder->status, [PurchaseOrder::STATUS_PENDING_APPROVAL, PurchaseOrder::STATUS_APPROVED], true));
        if ($overdue->isNotEmpty()) {
            $alerts[] = [
                'severity' => 'high',
                'title' => number_format($overdue->count(), 0, ',', '.') . ' PO melewati ETA',
                'detail' => 'Dokumen pembelian overdue berpotensi menggerus availability outlet dan memicu emergency buying.',
            ];
        }

        $attendanceIssues = $attendanceLogs->whereIn('attendance_status', ['late', 'leave', 'absent']);
        if ($attendanceIssues->isNotEmpty()) {
            $alerts[] = [
                'severity' => 'high',
                'title' => number_format($attendanceIssues->count(), 0, ',', '.') . ' roster butuh follow-up HR dan outlet',
                'detail' => 'Keterlambatan, cuti, atau absen dapat langsung menekan service level outlet dan memicu lembur tambahan.',
            ];
        }

        if ($atRiskSuppliers->isNotEmpty()) {
            $supplier = $atRiskSuppliers->first();
            $alerts[] = [
                'severity' => 'medium',
                'title' => $supplier->name . ' perlu review SLA',
                'detail' => 'Fill rate ' . $this->formatPercent($supplier->fill_rate) . ' dan reject rate ' . $this->formatPercent($supplier->reject_rate) . ' menunjukkan performa supplier mulai melemah.',
            ];
        }

        if ($agingValue > 0) {
            $alerts[] = [
                'severity' => 'medium',
                'title' => 'Aging inventory masih menahan modal kerja',
                'detail' => 'Stok di atas 30 hari bernilai ' . $this->formatCompactCurrency($agingValue) . ' dan sebaiknya dihubungkan ke promo, markdown, atau transfer.',
            ];
        }

        $pendingPayroll = $latestPayroll?->items?->where('payment_status', '!=', 'paid')->count() ?? 0;
        if ($pendingPayroll > 0) {
            $alerts[] = [
                'severity' => 'medium',
                'title' => number_format($pendingPayroll, 0, ',', '.') . ' item payroll belum ditandai paid',
                'detail' => 'Pastikan approval dan eksekusi pembayaran gaji sinkron dengan cash planning dan laporan keuangan.',
            ];
        }

        return array_slice($alerts, 0, 5);
    }

    private function agingValue(Collection $rows, int $minimumDays): float
    {
        return $rows
            ->filter(fn (object $row): bool => ($row->last_movement_at?->diffInDays(now()) ?? 0) > $minimumDays)
            ->sum(fn (object $row): float => $row->on_hand * $row->cost_price);
    }

    private function supplierStatus(Supplier $supplier): string
    {
        if (! $supplier->is_active) {
            return 'Inactive';
        }

        if ((float) $supplier->fill_rate < 90 || (float) $supplier->reject_rate > 2) {
            return 'Critical';
        }

        if ((float) $supplier->fill_rate < 93 || (float) $supplier->reject_rate > 1.2) {
            return 'Watch';
        }

        return (float) $supplier->rating >= 4.4 ? 'Preferred' : 'Stable';
    }

    private function outletStatus(Outlet $outlet): string
    {
        return match ($outlet->status) {
            Outlet::STATUS_ACTIVE => $outlet->is_fulfillment_hub ? 'Hub' : 'Active',
            Outlet::STATUS_RENOVATION => 'Renovation',
            default => 'Inactive',
        };
    }

    private function latestSalesDate(): CarbonImmutable
    {
        $soldAt = SalesTransaction::query()->max('sold_at');

        return $soldAt ? CarbonImmutable::parse($soldAt) : CarbonImmutable::now('Asia/Jakarta');
    }

    private function latestAttendanceDate(): CarbonImmutable
    {
        $shiftDate = AttendanceLog::query()->max('shift_date');

        return $shiftDate ? CarbonImmutable::parse($shiftDate) : CarbonImmutable::now('Asia/Jakarta');
    }

    private function stockValue(Product $product): float
    {
        return (float) $product->current_stock * (float) $product->cost_price;
    }

    private function daysCover(Product $product): ?float
    {
        return (float) $product->daily_run_rate > 0 ? (float) $product->current_stock / (float) $product->daily_run_rate : null;
    }

    private function marginPercent(Product $product): float
    {
        $sellingPrice = (float) $product->selling_price;

        return $sellingPrice > 0 ? (($sellingPrice - (float) $product->cost_price) / $sellingPrice) * 100 : 0.0;
    }

    private function percent(float|int|null $value, float|int|null $total): float
    {
        return (float) $total === 0.0 ? 0.0 : ((float) $value / (float) $total) * 100;
    }

    private function timestamp(): string
    {
        return CarbonImmutable::now('Asia/Jakarta')->locale('id')->translatedFormat('d F Y, H:i');
    }

    private function formatCompactCurrency(float|int|string|null $value): string
    {
        $value = (float) $value;
        $absValue = abs($value);

        return match (true) {
            $absValue >= 1_000_000_000 => 'Rp ' . $this->formatDecimal($value / 1_000_000_000, 2) . ' Miliar',
            $absValue >= 1_000_000 => 'Rp ' . $this->formatDecimal($value / 1_000_000) . ' Jt',
            $absValue >= 1_000 => 'Rp ' . $this->formatDecimal($value / 1_000) . ' Rb',
            default => 'Rp ' . number_format($value, 0, ',', '.'),
        };
    }

    private function formatPercent(float|int|string|null $value): string
    {
        return $this->formatDecimal((float) $value) . '%';
    }

    private function formatNullableDays(float|int|string|null $value): string
    {
        return $value === null ? '-' : $this->formatDecimal((float) $value) . ' hari';
    }

    private function formatDecimal(float|int|string|null $value, int $precision = 1): string
    {
        $formatted = number_format((float) $value, $precision, ',', '.');
        $formatted = preg_replace('/,0+$/', '', $formatted);

        return preg_replace('/(,\d*[1-9])0+$/', '$1', $formatted) ?? $formatted;
    }
}
