<?php

namespace App\Services;

use App\Helpers\MenuHelper;
use App\Models\AccountingPeriod;
use App\Models\AccountingJournalEntry;
use App\Models\AttendanceLog;
use App\Models\AuditLog;
use App\Models\CashReconciliation;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Employee;
use App\Models\GoodsReceipt;
use App\Models\InventoryLedger;
use App\Models\Location;
use App\Models\Outlet;
use App\Models\PaymentMethod;
use App\Models\PayrollRun;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderPayment;
use App\Models\PurchaseReturn;
use App\Models\ReportExport;
use App\Models\SalesReturn;
use App\Models\SalesTransaction;
use App\Models\Shift;
use App\Models\EmployeeShiftAssignment;
use App\Models\StockOpname;
use App\Models\StockTransfer;
use App\Models\Supplier;
use App\Models\User;
use App\Models\Warehouse;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class RetailOperationsService
{
    public function __construct(
        private readonly AnalyticsCacheService $analyticsCacheService,
        private readonly FinancialStatementAggregateService $financialStatementAggregateService,
    ) {
    }

    public function resolve(string $pageKey): ?array
    {
        return match ($pageKey) {
            'warehouse' => [
                'view' => 'pages.operations.connected-board',
                'data' => $this->warehouseOverviewData(),
            ],
            'outlet' => [
                'view' => 'pages.operations.outlet-directory',
                'data' => $this->outletDirectoryData(),
            ],
            'kategori' => [
                'view' => 'pages.operations.livewire.category-directory',
                'data' => $this->categoryDirectoryData(),
            ],
            'produk' => [
                'view' => 'pages.operations.livewire.product-catalog',
                'data' => $this->productCatalogData(),
            ],
            'customer-directory' => [
                'view' => 'pages.operations.customer-directory',
                'data' => $this->customerDirectoryData(),
            ],
            'stock-summary' => [
                'view' => 'pages.operations.inventory-summary',
                'data' => $this->inventorySummaryData(),
            ],
            'stock-mutation' => [
                'view' => 'pages.operations.stock-transfers',
                'data' => $this->stockTransferIndexData(),
            ],
            'stock-opname' => [
                'view' => 'pages.operations.stock-opnames',
                'data' => $this->stockOpnameIndexData(),
            ],
            'store-warehouse' => [
                'view' => 'pages.operations.connected-board',
                'data' => $this->connectedWorkspaceData('store-warehouse'),
            ],
            'purchase-return' => [
                'view' => 'pages.operations.purchase-returns',
                'data' => $this->purchaseReturnIndexData(),
            ],
            'supplier' => [
                'view' => 'pages.operations.livewire.supplier-directory',
                'data' => $this->supplierDirectoryData(),
            ],
            'purchase-orders' => [
                'view' => 'pages.operations.livewire.purchase-orders',
                'data' => $this->purchaseOrderIndexData(),
            ],
            'goods-receipts' => [
                'view' => 'pages.operations.goods-receipts',
                'data' => $this->goodsReceiptIndexData(),
            ],
            'pos-transactions' => [
                'view' => 'pages.operations.livewire.pos-transactions',
                'data' => $this->posTransactionIndexData(),
            ],
            'sales-invoices' => [
                'view' => 'pages.operations.sales-invoices',
                'data' => $this->salesInvoiceIndexData(),
            ],
            'sales-return' => [
                'view' => 'pages.operations.sales-returns',
                'data' => $this->salesReturnIndexData(),
            ],
            'employee-management' => [
                'view' => 'pages.operations.employee-management',
                'data' => $this->employeeManagementData(),
            ],
            'attendance-log' => [
                'view' => 'pages.operations.attendance-log',
                'data' => $this->attendanceLogData(),
            ],
            'shift-attendance' => [
                'view' => 'pages.operations.shift-attendance',
                'data' => $this->shiftAttendanceData(),
            ],
            'schedule-request' => [
                'view' => 'pages.operations.connected-board',
                'data' => $this->connectedWorkspaceData('schedule-request'),
            ],
            'leave-request' => [
                'view' => 'pages.operations.connected-board',
                'data' => $this->connectedWorkspaceData('leave-request'),
            ],
            'payroll-list' => [
                'view' => 'pages.operations.payroll-list',
                'data' => $this->payrollListData(),
            ],
            'resign-data' => [
                'view' => 'pages.operations.connected-board',
                'data' => $this->connectedWorkspaceData('resign-data'),
            ],
            'my-home' => [
                'view' => 'pages.operations.connected-board',
                'data' => $this->connectedWorkspaceData('my-home'),
            ],
            'my-leave' => [
                'view' => 'pages.operations.connected-board',
                'data' => $this->connectedWorkspaceData('my-leave'),
            ],
            'my-schedule' => [
                'view' => 'pages.operations.connected-board',
                'data' => $this->connectedWorkspaceData('my-schedule'),
            ],
            'salary-slip' => [
                'view' => 'pages.operations.connected-board',
                'data' => $this->connectedWorkspaceData('salary-slip'),
            ],
            'resign-request' => [
                'view' => 'pages.operations.connected-board',
                'data' => $this->connectedWorkspaceData('resign-request'),
            ],
            'financial-report' => [
                'view' => 'pages.operations.connected-board',
                'data' => $this->financialReportData(),
            ],
            'cashflow' => [
                'view' => 'pages.operations.connected-board',
                'data' => $this->cashflowData(),
            ],
            'receivables-payables' => [
                'view' => 'pages.operations.receivables-payables',
                'data' => $this->receivablesPayablesData(),
            ],
            'split-payment' => [
                'view' => 'pages.operations.split-payment',
                'data' => $this->splitPaymentData(),
            ],
            'period-closing' => [
                'view' => 'pages.operations.period-closings',
                'data' => $this->periodClosingData(),
            ],
            'cash-reconciliation' => [
                'view' => 'pages.operations.cash-reconciliations',
                'data' => $this->cashReconciliationData(),
            ],
            'audit-trail' => [
                'view' => 'pages.operations.livewire.audit-trail',
                'data' => $this->auditTrailPageData(),
            ],
            default => null,
        };
    }

    public function productCatalogData(): array
    {
        $page = $this->basePage('produk');
        $products = $this->productsWithStock();
        $activeProducts = $products->where('status', Product::STATUS_ACTIVE)->count();
        $barcodeComplete = $products->filter(fn (Product $product): bool => filled($product->barcode))->count();
        $averageMargin = $products->avg(fn (Product $product): float => $this->marginPercent($product));
        $totalStockValue = $products->sum(fn (Product $product): float => $this->stockValue($product));
        $lowCoverProducts = $products->filter(fn (Product $product): bool => $this->daysCover($product) !== null && $this->daysCover($product) <= 3)->count();
        $categoryMix = $this->categoryMix($products);

        return array_merge($page, [
            'generatedAt' => $this->timestamp(),
            'heroStats' => [
                ['label' => 'SKU aktif', 'value' => number_format($activeProducts, 0, ',', '.'), 'caption' => 'Produk hidup yang siap dipakai untuk POS dan procurement'],
                ['label' => 'Kategori aktif', 'value' => number_format(Category::query()->where('is_active', true)->count(), 0, ',', '.'), 'caption' => 'Kategori retail yang saat ini punya produk aktif'],
                ['label' => 'Stok katalog', 'value' => $this->formatCompactCurrency($totalStockValue), 'caption' => 'Nilai stok dari seluruh produk aktif di database'],
                ['label' => 'Low cover SKU', 'value' => number_format($lowCoverProducts, 0, ',', '.'), 'caption' => 'Produk dengan estimasi cover di bawah 3 hari'],
            ],
            'metrics' => [
                ['label' => 'Margin rata-rata', 'value' => $this->formatPercent($averageMargin), 'note' => 'Rata-rata gross margin berdasarkan cost dan selling price produk aktif'],
                ['label' => 'Barcode completeness', 'value' => $this->formatPercent($this->percent($barcodeComplete, max($products->count(), 1))), 'note' => 'Persentase SKU yang siap discan di POS dan receiving'],
                ['label' => 'Featured mix', 'value' => $this->formatPercent($this->percent($products->where('is_featured', true)->count(), max($products->count(), 1))), 'note' => 'Porsi produk prioritas yang sedang didorong untuk visibility atau promo'],
                ['label' => 'Margin rendah', 'value' => number_format($products->filter(fn (Product $product): float => $this->marginPercent($product) < 25)->count(), 0, ',', '.'), 'note' => 'Produk yang perlu review harga atau negosiasi cost dengan supplier'],
                ['label' => 'Supplier mapped', 'value' => $this->formatPercent($this->percent($products->filter(fn (Product $product): bool => $product->primarySupplier !== null)->count(), max($products->count(), 1))), 'note' => 'SKU dengan supplier utama yang sudah terhubung ke master produk'],
                ['label' => 'Rata-rata cover', 'value' => $this->formatNullableDays($products->avg(fn (Product $product): ?float => $this->daysCover($product))), 'note' => 'Estimasi hari cover dari stok saat ini terhadap run rate harian produk'],
            ],
            'categoryMix' => $categoryMix,
            'products' => $products->map(function (Product $product): array {
                return [
                    'id' => $product->id,
                    'sku' => $product->sku,
                    'barcode' => $product->barcode,
                    'name' => $product->name,
                    'category' => $product->category?->name ?? '-',
                    'supplier' => $product->primarySupplier?->name ?? '-',
                    'price' => (float) $product->selling_price,
                    'current_stock' => (float) $product->current_stock,
                    'stock_days' => $this->daysCover($product),
                    'margin' => $this->marginPercent($product),
                    'status' => $this->productStatus($product),
                    'edit_url' => route('products.edit', $product),
                    'delete_blocked' => $product->inventory_ledgers_count > 0 || $product->purchase_order_items_count > 0,
                ];
            })->all(),
            'governanceCards' => [
                ['title' => 'Assortment health', 'value' => $this->formatPercent($this->percent($activeProducts, max($products->count(), 1))), 'note' => 'Porsi produk yang benar-benar aktif dan siap digunakan lintas modul'],
                ['title' => 'Daily run rate ready', 'value' => $this->formatPercent($this->percent($products->filter(fn (Product $product): float => (float) $product->daily_run_rate > 0)->count(), max($products->count(), 1))), 'note' => 'SKU yang sudah bisa dihitung cover stock dan kebutuhan replenishment-nya'],
                ['title' => 'Reorder policy ready', 'value' => $this->formatPercent($this->percent($products->filter(fn (Product $product): float => (float) $product->reorder_level > 0 && (float) $product->reorder_quantity > 0)->count(), max($products->count(), 1))), 'note' => 'Produk yang sudah punya parameter reorder untuk memicu keputusan pembelian'],
                ['title' => 'Featured SKU', 'value' => number_format($products->where('is_featured', true)->count(), 0, ',', '.'), 'note' => 'Produk andalan yang bisa ditarik ke dashboard dan prioritas stok'],
            ],
            'watchlist' => $this->productWatchlist($products),
            'createUrl' => route('products.create'),
        ]);
    }

    public function productFormData(?Product $product = null): array
    {
        return [
            'title' => $product?->exists ? 'Edit Produk' : 'Tambah Produk',
            'pageTitle' => $product?->exists ? 'Edit Produk' : 'Tambah Produk',
            'pageEyebrow' => 'Master Produk',
            'pageDescription' => $product?->exists
                ? 'Perbarui master produk agar pricing, stock policy, dan supplier mapping tetap sinkron.'
                : 'Tambahkan SKU baru lengkap dengan kategori, supplier utama, pricing, dan parameter reorder.',
            'product' => $product,
            'categories' => Category::query()->where('is_active', true)->orderBy('sort_order')->orderBy('name')->get(),
            'suppliers' => Supplier::query()->where('is_active', true)->orderBy('name')->get(),
            'statusOptions' => Product::statusOptions(),
            'unitOptions' => ['pcs', 'box', 'btl', 'cup', 'pack'],
            'submitUrl' => $product?->exists ? route('products.update', $product) : route('products.store'),
            'submitMethod' => $product?->exists ? 'PUT' : 'POST',
            'backUrl' => route('produk'),
            'createCategoryUrl' => route('categories.create'),
            'createSupplierUrl' => route('suppliers.create'),
            'generatedAt' => $this->timestamp(),
        ];
    }

    public function categoryFormData(?Category $category = null): array
    {
        return [
            'title' => $category?->exists ? 'Edit Kategori' : 'Tambah Kategori',
            'pageTitle' => $category?->exists ? 'Edit Kategori' : 'Tambah Kategori',
            'pageEyebrow' => 'Master Kategori',
            'pageDescription' => $category?->exists
                ? 'Perbarui struktur kategori agar assortment, dashboard, dan procurement mix tetap konsisten.'
                : 'Tambahkan kategori baru untuk memastikan pemetaan produk, analitik, dan perencanaan belanja tetap rapi.',
            'category' => $category,
            'submitUrl' => $category?->exists ? route('categories.update', $category) : route('categories.store'),
            'submitMethod' => $category?->exists ? 'PUT' : 'POST',
            'backUrl' => route('kategori'),
            'generatedAt' => $this->timestamp(),
        ];
    }

    public function supplierFormData(?Supplier $supplier = null): array
    {
        return [
            'title' => $supplier?->exists ? 'Edit Supplier' : 'Tambah Supplier',
            'pageTitle' => $supplier?->exists ? 'Edit Supplier' : 'Tambah Supplier',
            'pageEyebrow' => 'Master Supplier',
            'pageDescription' => $supplier?->exists
                ? 'Perbarui profil supplier agar SLA, term pembayaran, dan risiko supply tetap terkontrol.'
                : 'Tambahkan supplier baru lengkap dengan SLA, termin pembayaran, dan kontak operasional procurement.',
            'supplier' => $supplier,
            'submitUrl' => $supplier?->exists ? route('suppliers.update', $supplier) : route('suppliers.store'),
            'submitMethod' => $supplier?->exists ? 'PUT' : 'POST',
            'backUrl' => route('supplier'),
            'generatedAt' => $this->timestamp(),
        ];
    }

    public function customerDirectoryData(): array
    {
        $page = $this->basePage('customer-directory');
        $customers = Customer::query()
            ->withCount('salesTransactions')
            ->withSum(['salesTransactions as receivable_open' => fn ($query) => $query->where('balance_due', '>', 0)], 'balance_due')
            ->orderBy('name')
            ->get();
        $activeCustomers = $customers->where('status', Customer::STATUS_ACTIVE);
        $totalCreditLimit = (float) $activeCustomers->sum('credit_limit');
        $openReceivable = (float) $activeCustomers->sum('receivable_open');

        return array_merge($page, [
            'generatedAt' => $this->timestamp(),
            'heroStats' => [
                ['label' => 'Active customers', 'value' => number_format($activeCustomers->count(), 0, ',', '.'), 'caption' => 'Customer aktif yang bisa dipakai untuk invoice dan monitoring piutang retail'],
                ['label' => 'Open receivable', 'value' => $this->formatCompactCurrency($openReceivable), 'caption' => 'Saldo piutang customer yang belum selesai ditagih'],
                ['label' => 'Invoice customers', 'value' => number_format($customers->where('receivable_open', '>', 0)->count(), 0, ',', '.'), 'caption' => 'Customer yang saat ini punya saldo invoice berjalan'],
                ['label' => 'Credit limit', 'value' => $this->formatCompactCurrency($totalCreditLimit), 'caption' => 'Batas kredit agregat yang disiapkan untuk relasi customer aktif'],
            ],
            'metrics' => [
                ['label' => 'Average term', 'value' => $this->formatDecimal($activeCustomers->avg('payment_term_days')) . ' hari', 'note' => 'Rata-rata tempo pembayaran customer aktif.'],
                ['label' => 'Credit utilization', 'value' => $this->formatPercent($this->percent($openReceivable, max($totalCreditLimit, 1))), 'note' => 'Porsi limit kredit yang saat ini sedang dipakai customer.'],
                ['label' => 'Prospect to active', 'value' => $this->formatPercent($this->percent($customers->where('status', Customer::STATUS_ACTIVE)->count(), max($customers->count(), 1))), 'note' => 'Kualitas aktivasi customer dari master data yang tersedia.'],
                ['label' => 'Invoices per customer', 'value' => $this->formatDecimal($customers->avg('sales_transactions_count')), 'note' => 'Rata-rata jumlah transaksi per customer untuk membaca engagement akun.'],
            ],
            'customers' => $customers->map(function (Customer $customer): array {
                return [
                    'id' => $customer->id,
                    'code' => $customer->code,
                    'name' => $customer->name,
                    'segment' => $customer->segment,
                    'contact' => trim(collect([$customer->phone, $customer->email])->filter()->join(' / ')) ?: '-',
                    'city' => $customer->city ?: '-',
                    'credit_limit' => $this->formatCompactCurrency((float) $customer->credit_limit),
                    'payment_term' => number_format((int) $customer->payment_term_days, 0, ',', '.') . ' hari',
                    'open_receivable' => $this->formatCompactCurrency((float) $customer->receivable_open),
                    'status' => Customer::statusOptions()[$customer->status] ?? ucfirst($customer->status),
                    'edit_url' => route('customers.edit', $customer),
                ];
            })->all(),
            'segments' => $customers->groupBy('segment')->map(function (Collection $group, string $segment): array {
                return [
                    'title' => $segment,
                    'value' => number_format($group->count(), 0, ',', '.'),
                    'note' => 'Open receivable ' . $this->formatCompactCurrency((float) $group->sum('receivable_open')),
                ];
            })->values()->all(),
            'createUrl' => route('customers.create'),
        ]);
    }

    public function customerFormData(?Customer $customer = null): array
    {
        return [
            'title' => $customer?->exists ? 'Edit Customer' : 'Tambah Customer',
            'pageTitle' => $customer?->exists ? 'Edit Customer' : 'Tambah Customer',
            'pageEyebrow' => 'Sales & POS',
            'pageDescription' => $customer?->exists
                ? 'Perbarui profil customer agar invoice, limit kredit, dan histori penagihan tetap akurat.'
                : 'Tambahkan customer baru untuk mendukung invoice retail, pembayaran termin, dan analitik penjualan berbasis akun.',
            'customer' => $customer,
            'segmentOptions' => ['Retail', 'Corporate', 'Community', 'Wholesale', 'Membership'],
            'statusOptions' => Customer::statusOptions(),
            'submitUrl' => $customer?->exists ? route('customers.update', $customer) : route('customers.store'),
            'submitMethod' => $customer?->exists ? 'PUT' : 'POST',
            'backUrl' => route('customer-directory'),
            'generatedAt' => $this->timestamp(),
        ];
    }

    private function productsWithStock(): Collection
    {
        return Product::query()
            ->with(['category', 'primarySupplier'])
            ->withCount(['inventoryLedgers', 'purchaseOrderItems'])
            ->leftJoinSub($this->productStockSubquery(), 'stock_positions', function ($join): void {
                $join->on('stock_positions.product_id', '=', 'products.id');
            })
            ->select('products.*')
            ->selectRaw('COALESCE(stock_positions.current_stock, 0) as current_stock')
            ->selectRaw('stock_positions.last_movement_at as last_movement_at')
            ->orderBy('products.name')
            ->get()
            ->map(function (Product $product): Product {
                if ($product->last_movement_at !== null) {
                    $product->setAttribute('last_movement_at', CarbonImmutable::parse($product->last_movement_at));
                }

                return $product;
            });
    }

    private function productStockSubquery(): Builder
    {
        return InventoryLedger::query()
            ->select('product_id')
            ->selectRaw('SUM(quantity) as current_stock')
            ->selectRaw('MAX(transaction_at) as last_movement_at')
            ->groupBy('product_id');
    }

    private function categoryMix(Collection $products): array
    {
        $totalValue = max($products->sum(fn (Product $product): float => $this->stockValue($product)), 1);

        return $products
            ->groupBy(fn (Product $product): string => $product->category?->name ?? 'Tanpa Kategori')
            ->map(function (Collection $group, string $name) use ($totalValue): array {
                $stockValue = $group->sum(fn (Product $product): float => $this->stockValue($product));

                return [
                    'name' => $name,
                    'share' => $this->round($this->percent($stockValue, $totalValue)),
                    'margin' => $this->round($group->avg(fn (Product $product): float => $this->marginPercent($product))),
                    'note' => 'Nilai stok ' . $this->formatCompactCurrency($stockValue) . ' dari kategori aktif ini',
                ];
            })
            ->sortByDesc('share')
            ->take(5)
            ->values()
            ->all();
    }

    private function productWatchlist(Collection $products): array
    {
        $alerts = [];

        $lowCover = $products
            ->filter(fn (Product $product): bool => $this->daysCover($product) !== null && $this->daysCover($product) <= 3)
            ->sortBy(fn (Product $product): float => $this->daysCover($product) ?? 9999)
            ->take(2);

        foreach ($lowCover as $product) {
            $alerts[] = [
                'title' => $product->name . ' cover ' . $this->formatNullableDays($this->daysCover($product)),
                'detail' => 'SKU ' . $product->sku . ' perlu replenishment karena stok saat ini hanya ' . number_format($product->current_stock, 0, ',', '.') . ' ' . $product->unit_of_measure . '.',
            ];
        }

        $missingBarcode = $products->whereNull('barcode')->count();
        if ($missingBarcode > 0) {
            $alerts[] = [
                'title' => number_format($missingBarcode, 0, ',', '.') . ' SKU belum punya barcode',
                'detail' => 'Lengkapi barcode agar proses scan POS, transfer, dan receiving tidak tersendat.',
            ];
        }

        $noSupplier = $products->filter(fn (Product $product): bool => $product->primarySupplier === null)->count();
        if ($noSupplier > 0) {
            $alerts[] = [
                'title' => number_format($noSupplier, 0, ',', '.') . ' SKU belum punya supplier utama',
                'detail' => 'Mapping supplier yang lengkap akan mempercepat pembuatan purchase order dan evaluasi spend.',
            ];
        }

        return array_slice($alerts, 0, 3);
    }

    public function inventorySummaryData(): array
    {
        $page = $this->basePage('stock-summary');
        $warehouseStockRows = $this->warehouseStockRows();
        $positiveRows = $warehouseStockRows->where('on_hand', '>', 0);
        $inventoryValue = $positiveRows->sum(fn (object $row): float => $row->on_hand * $row->cost_price);
        $totalUnits = $positiveRows->sum('on_hand');
        $dailyDemand = $positiveRows->sum('daily_run_rate');
        $averageDaysCover = $dailyDemand > 0 ? $totalUnits / $dailyDemand : null;
        $lowStockRows = $positiveRows->filter(fn (object $row): bool => $row->on_hand <= $row->reorder_level);
        $agingBuckets = $this->agingBuckets($positiveRows);

        return array_merge($page, [
            'generatedAt' => $this->timestamp(),
            'heroStats' => [
                ['label' => 'Nilai stok', 'value' => $this->formatCompactCurrency($inventoryValue), 'caption' => 'Valuasi stok aktif dari seluruh ledger yang sudah tercatat'],
                ['label' => 'Unit on hand', 'value' => number_format((float) $totalUnits, 0, ',', '.'), 'caption' => 'Jumlah unit netto yang masih tersedia di gudang dan outlet'],
                ['label' => 'SKU kritikal', 'value' => number_format($lowStockRows->count(), 0, ',', '.'), 'caption' => 'Produk dengan stok di bawah atau sama dengan reorder level'],
                ['label' => 'Gudang aktif', 'value' => number_format(Warehouse::query()->where('is_active', true)->count(), 0, ',', '.'), 'caption' => 'Lokasi stok yang saat ini aktif untuk distribusi retail'],
            ],
            'metrics' => [
                ['label' => 'Rata-rata cover', 'value' => $this->formatNullableDays($averageDaysCover), 'note' => 'Estimasi berapa hari stok saat ini bisa menopang run rate harian produk'],
                ['label' => 'Low stock ratio', 'value' => $this->formatPercent($this->percent($lowStockRows->count(), max($positiveRows->count(), 1))), 'note' => 'Persentase product-location yang butuh replenishment cepat'],
                ['label' => 'Inactive stock > 30 hari', 'value' => $this->formatCompactCurrency($agingBuckets[2]['numeric_value'] + $agingBuckets[3]['numeric_value']), 'note' => 'Nilai stok yang terakhir bergerak lebih dari 30 hari lalu'],
                ['label' => 'Fast movers', 'value' => number_format($positiveRows->filter(fn (object $row): float => $row->daily_run_rate >= 20)->count(), 0, ',', '.'), 'note' => 'Product-location dengan run rate tinggi yang perlu stok disiplin'],
                ['label' => 'Inbound approved', 'value' => number_format(PurchaseOrder::query()->where('status', PurchaseOrder::STATUS_APPROVED)->count(), 0, ',', '.'), 'note' => 'PO approved yang siap menjadi suplai stok berikutnya'],
                ['label' => 'Ledger bulan ini', 'value' => number_format(InventoryLedger::query()->whereMonth('transaction_at', now()->month)->count(), 0, ',', '.'), 'note' => 'Jumlah pergerakan stok yang tercatat pada bulan berjalan'],
            ],
            'locations' => $this->warehouseCards($positiveRows),
            'agingBuckets' => array_map(function (array $bucket): array {
                return [
                    'label' => $bucket['label'],
                    'share' => $bucket['share'],
                    'value' => $this->formatCompactCurrency($bucket['numeric_value']),
                ];
            }, $agingBuckets),
            'criticalItems' => $lowStockRows
                ->sortBy(fn (object $row): float => $this->rowDaysCover($row) ?? 9999)
                ->take(8)
                ->map(function (object $row): array {
                    return [
                        'sku' => $row->sku,
                        'name' => $row->product_name,
                        'location' => $row->warehouse_name,
                        'on_hand' => number_format($row->on_hand, 0, ',', '.') . ' ' . $row->unit_of_measure,
                        'days_cover' => $this->formatNullableDays($this->rowDaysCover($row)),
                        'risk' => $this->formatCompactCurrency($row->on_hand * $row->cost_price),
                        'action' => 'Top-up minimal ' . number_format(max($row->reorder_quantity, 1), 0, ',', '.') . ' ' . $row->unit_of_measure,
                    ];
                })->values()->all(),
            'actionCards' => [
                ['title' => 'PO pending approval', 'value' => number_format(PurchaseOrder::query()->where('status', PurchaseOrder::STATUS_PENDING_APPROVAL)->count(), 0, ',', '.'), 'note' => 'Approval yang cepat akan langsung memengaruhi replenishment stok kritikal'],
                ['title' => 'Inbound due 3 hari', 'value' => number_format(PurchaseOrder::query()->whereIn('status', [PurchaseOrder::STATUS_APPROVED, PurchaseOrder::STATUS_PENDING_APPROVAL])->whereDate('expected_date', '<=', now()->addDays(3))->count(), 0, ',', '.'), 'note' => 'Receiving plan perlu disiapkan agar inbound tidak tertahan'],
                ['title' => 'Transfer-like demand', 'value' => number_format($lowStockRows->count(), 0, ',', '.'), 'note' => 'Jumlah product-location yang saat ini membutuhkan perpindahan stok atau PO baru'],
                ['title' => 'Last movement today', 'value' => number_format(InventoryLedger::query()->whereDate('transaction_at', now())->count(), 0, ',', '.'), 'note' => 'Aktivitas ledger di hari ini yang bisa langsung ditelusuri ke referensi dokumen'],
            ],
        ]);
    }

    public function supplierDirectoryData(): array
    {
        $page = $this->basePage('supplier');
        $suppliers = Supplier::query()
            ->with(['products.category'])
            ->withCount(['products', 'purchaseOrders'])
            ->withSum(['purchaseOrders as spend_total'], 'total_amount')
            ->withSum(['purchaseOrders as open_po_total' => fn ($query) => $query->whereIn('status', [PurchaseOrder::STATUS_PENDING_APPROVAL, PurchaseOrder::STATUS_APPROVED, PurchaseOrder::STATUS_PARTIALLY_RECEIVED])], 'total_amount')
            ->orderByDesc('fill_rate')
            ->get();

        $activeSuppliers = $suppliers->where('is_active', true);
        $suppliersAtRisk = $activeSuppliers->filter(fn (Supplier $supplier): bool => (float) $supplier->fill_rate < 93 || (float) $supplier->reject_rate > 1.5);

        return array_merge($page, [
            'generatedAt' => $this->timestamp(),
            'heroStats' => [
                ['label' => 'Supplier aktif', 'value' => number_format($activeSuppliers->count(), 0, ',', '.'), 'caption' => 'Vendor yang saat ini masih digunakan untuk procurement retail'],
                ['label' => 'Rata-rata fill rate', 'value' => $this->formatPercent($activeSuppliers->avg('fill_rate')), 'caption' => 'Kemampuan supplier memenuhi qty pesanan yang diajukan'],
                ['label' => 'Open PO value', 'value' => $this->formatCompactCurrency(PurchaseOrder::query()->whereIn('status', [PurchaseOrder::STATUS_PENDING_APPROVAL, PurchaseOrder::STATUS_APPROVED])->sum('total_amount')), 'caption' => 'Nilai PO yang masih berjalan atau menunggu approval'],
                ['label' => 'Supplier at risk', 'value' => number_format($suppliersAtRisk->count(), 0, ',', '.'), 'caption' => 'Vendor yang perlu follow-up karena SLA atau kualitasnya melemah'],
            ],
            'metrics' => [
                ['label' => 'Average lead time', 'value' => $this->formatDecimal($activeSuppliers->avg('lead_time_days')) . ' hari', 'note' => 'Semakin pendek lead time, semakin kecil tekanan ke safety stock'],
                ['label' => 'Reject rate', 'value' => $this->formatPercent($activeSuppliers->avg('reject_rate')), 'note' => 'Kualitas barang incoming yang perlu terus dikendalikan'],
                ['label' => 'Average payment term', 'value' => $this->formatDecimal($activeSuppliers->avg('payment_term_days')) . ' hari', 'note' => 'Pengaruh langsung ke tekanan cashflow procurement'],
                ['label' => 'Preferred vendors', 'value' => number_format($activeSuppliers->filter(fn (Supplier $supplier): float => (float) $supplier->rating >= 4.4)->count(), 0, ',', '.'), 'note' => 'Vendor dengan rating tinggi dan performa supply paling stabil'],
                ['label' => 'Catalog coverage', 'value' => $this->formatPercent($this->percent($activeSuppliers->filter(fn (Supplier $supplier): int => $supplier->products_count > 0)->count(), max($activeSuppliers->count(), 1))), 'note' => 'Persentase supplier yang benar-benar sudah terhubung ke master produk'],
                ['label' => 'Total supplier spend', 'value' => $this->formatCompactCurrency($suppliers->sum('spend_total')), 'note' => 'Akumulasi nilai purchase order yang sudah tercatat per supplier'],
            ],
            'suppliers' => $suppliers->map(function (Supplier $supplier): array {
                return [
                    'code' => $supplier->code,
                    'name' => $supplier->name,
                    'focus' => $supplier->products->first()?->category?->name ?? 'General Merchandise',
                    'contact' => $supplier->contact_person ?: '-',
                    'city' => $supplier->city ?: '-',
                    'fill_rate' => $this->formatPercent($supplier->fill_rate),
                    'lead_time' => $this->formatDecimal($supplier->lead_time_days) . ' hari',
                    'term' => number_format($supplier->payment_term_days, 0, ',', '.') . ' hari',
                    'spend' => $this->formatCompactCurrency((float) $supplier->spend_total),
                    'open_po' => $this->formatCompactCurrency((float) $supplier->open_po_total),
                    'products_count' => number_format((int) $supplier->products_count, 0, ',', '.'),
                    'status' => $this->supplierStatus($supplier),
                    'edit_url' => route('suppliers.edit', $supplier),
                    'delete_url' => route('suppliers.destroy', $supplier),
                    'can_delete' => (int) $supplier->products_count === 0 && (int) $supplier->purchase_orders_count === 0,
                ];
            })->all(),
            'contractCards' => [
                ['title' => 'Vendor perlu perhatian', 'value' => number_format($suppliersAtRisk->count(), 0, ',', '.'), 'note' => 'Dipicu oleh fill rate rendah atau reject rate tinggi pada supplier aktif'],
                ['title' => 'PO menunggu approval', 'value' => number_format(PurchaseOrder::query()->where('status', PurchaseOrder::STATUS_PENDING_APPROVAL)->count(), 0, ',', '.'), 'note' => 'Approval yang lambat bisa membuat supplier kehilangan slot pengiriman'],
                ['title' => 'Approved PO supplier aktif', 'value' => number_format(PurchaseOrder::query()->where('status', PurchaseOrder::STATUS_APPROVED)->count(), 0, ',', '.'), 'note' => 'Jumlah PO supplier yang sudah siap diproses ke receiving'],
            ],
            'riskAlerts' => $suppliersAtRisk->take(3)->map(function (Supplier $supplier): array {
                return [
                    'title' => $supplier->name,
                    'detail' => 'Fill rate ' . $this->formatPercent($supplier->fill_rate) . ', reject rate ' . $this->formatPercent($supplier->reject_rate) . ', lead time ' . $this->formatDecimal($supplier->lead_time_days) . ' hari.',
                ];
            })->values()->all(),
            'createUrl' => route('suppliers.create'),
        ]);
    }

    private function warehouseStockRows(): Collection
    {
        $query = DB::table('inventory_ledgers')
            ->join('products', 'products.id', '=', 'inventory_ledgers.product_id')
            ->join('warehouses', 'warehouses.id', '=', 'inventory_ledgers.warehouse_id')
            ->select(
                'products.id as product_id',
                'products.sku',
                'products.name as product_name',
                'products.unit_of_measure',
                'products.cost_price',
                'products.daily_run_rate',
                'products.reorder_level',
                'products.reorder_quantity',
                'warehouses.id as warehouse_id',
                'warehouses.name as warehouse_name',
                'warehouses.code as warehouse_code'
            )
            ->selectRaw('SUM(inventory_ledgers.quantity) as on_hand')
            ->selectRaw('MAX(inventory_ledgers.transaction_at) as last_movement_at');

        $user = auth()->user();
        if ($user !== null && Schema::hasColumn('inventory_ledgers', 'tenant_id') && filled($user->tenant_id)) {
            $query->where('inventory_ledgers.tenant_id', (int) $user->tenant_id);
        }
        if ($user instanceof User && Schema::hasColumn('inventory_ledgers', 'location_id') && $user->shouldConstrainLocation()) {
            $locationIds = $user->scopedLocationIds();

            if ($locationIds === []) {
                $query->whereRaw('1 = 0');
            } else {
                $query->whereIn('inventory_ledgers.location_id', $locationIds);
            }
        }

        return $query
            ->groupBy(
                'products.id',
                'products.sku',
                'products.name',
                'products.unit_of_measure',
                'products.cost_price',
                'products.daily_run_rate',
                'products.reorder_level',
                'products.reorder_quantity',
                'warehouses.id',
                'warehouses.name',
                'warehouses.code'
            )
            ->get()
            ->map(function (object $row): object {
                $row->on_hand = (float) $row->on_hand;
                $row->cost_price = (float) $row->cost_price;
                $row->daily_run_rate = (float) $row->daily_run_rate;
                $row->reorder_level = (float) $row->reorder_level;
                $row->reorder_quantity = (float) $row->reorder_quantity;
                $row->last_movement_at = $row->last_movement_at ? CarbonImmutable::parse($row->last_movement_at) : null;

                return $row;
            });
    }

    private function warehouseCards(Collection $rows): array
    {
        return $rows
            ->groupBy('warehouse_id')
            ->map(function (Collection $warehouseRows): array {
                $first = $warehouseRows->first();
                $stockValue = $warehouseRows->sum(fn (object $row): float => $row->on_hand * $row->cost_price);
                $criticalCount = $warehouseRows->filter(fn (object $row): bool => $row->on_hand <= $row->reorder_level)->count();

                return [
                    'name' => $first->warehouse_name,
                    'stock_value' => $this->formatCompactCurrency($stockValue),
                    'availability' => $this->formatPercent($this->percent($warehouseRows->where('on_hand', '>', 0)->count(), max($warehouseRows->count(), 1))),
                    'critical' => number_format($criticalCount, 0, ',', '.') . ' SKU',
                    'note' => 'Stok aktif ' . number_format($warehouseRows->where('on_hand', '>', 0)->count(), 0, ',', '.') . ' SKU dengan valuasi terkini dari ledger',
                ];
            })
            ->values()
            ->all();
    }

    private function agingBuckets(Collection $rows): array
    {
        $buckets = [
            ['label' => '0-7 hari', 'numeric_value' => 0],
            ['label' => '8-14 hari', 'numeric_value' => 0],
            ['label' => '15-30 hari', 'numeric_value' => 0],
            ['label' => '> 30 hari', 'numeric_value' => 0],
        ];

        foreach ($rows as $row) {
            $days = $row->last_movement_at?->diffInDays(now()) ?? 0;
            $value = $row->on_hand * $row->cost_price;

            if ($days <= 7) {
                $buckets[0]['numeric_value'] += $value;
            } elseif ($days <= 14) {
                $buckets[1]['numeric_value'] += $value;
            } elseif ($days <= 30) {
                $buckets[2]['numeric_value'] += $value;
            } else {
                $buckets[3]['numeric_value'] += $value;
            }
        }

        $total = max(array_sum(array_column($buckets, 'numeric_value')), 1);

        return array_map(function (array $bucket) use ($total): array {
            $bucket['share'] = (int) round(($bucket['numeric_value'] / $total) * 100);

            return $bucket;
        }, $buckets);
    }

    public function purchaseOrderIndexData(): array
    {
        $page = $this->basePage('purchase-orders');
        $purchaseOrders = PurchaseOrder::query()
            ->with(['supplier', 'warehouse', 'creator', 'items.product', 'payments.paymentMethod'])
            ->withCount('items')
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

        $openOrders = $purchaseOrders->whereIn('status', [
            PurchaseOrder::STATUS_DRAFT,
            PurchaseOrder::STATUS_PENDING_APPROVAL,
            PurchaseOrder::STATUS_APPROVED,
            PurchaseOrder::STATUS_PARTIALLY_RECEIVED,
        ]);

        $pendingApproval = $purchaseOrders->where('status', PurchaseOrder::STATUS_PENDING_APPROVAL);
        $approvedOrders = $purchaseOrders->where('status', PurchaseOrder::STATUS_APPROVED);
        $averageApprovalHours = $purchaseOrders
            ->filter(fn (PurchaseOrder $purchaseOrder): bool => $purchaseOrder->submitted_at !== null && $purchaseOrder->approved_at !== null)
            ->avg(fn (PurchaseOrder $purchaseOrder): float => $purchaseOrder->submitted_at->diffInSeconds($purchaseOrder->approved_at) / 3600);

        return array_merge($page, [
            'generatedAt' => $this->timestamp(),
            'heroStats' => [
                ['label' => 'Open PO', 'value' => number_format($openOrders->count(), 0, ',', '.'), 'caption' => 'PO yang masih berjalan dan memerlukan tindak lanjut operasional'],
                ['label' => 'Awaiting approval', 'value' => number_format($pendingApproval->count(), 0, ',', '.'), 'caption' => 'PO yang belum bisa diproses supplier karena approval belum selesai'],
                ['label' => 'Approved PO', 'value' => number_format($approvedOrders->count(), 0, ',', '.'), 'caption' => 'PO yang siap menjadi inbound ke gudang atau outlet'],
                ['label' => 'Overdue PO', 'value' => number_format($purchaseOrders->filter(fn (PurchaseOrder $purchaseOrder): bool => $purchaseOrder->expected_date !== null && $purchaseOrder->expected_date->isPast() && in_array($purchaseOrder->status, [PurchaseOrder::STATUS_PENDING_APPROVAL, PurchaseOrder::STATUS_APPROVED], true))->count(), 0, ',', '.'), 'caption' => 'PO yang ETA-nya sudah lewat namun belum selesai'],
            ],
            'metrics' => [
                ['label' => 'Committed spend', 'value' => $this->formatCompactCurrency($openOrders->sum('total_amount')), 'note' => 'Nilai PO aktif yang sudah tercatat dalam pipeline pembelian'],
                ['label' => 'Pending approval value', 'value' => $this->formatCompactCurrency($pendingApproval->sum('total_amount')), 'note' => 'Nilai pembelian yang tertahan di meja approval'],
                ['label' => 'Average approval time', 'value' => $this->formatDecimal($averageApprovalHours) . ' jam', 'note' => 'Selisih waktu dari submit sampai approved untuk PO yang sudah lolos workflow'],
                ['label' => 'Average line per PO', 'value' => $this->formatDecimal($purchaseOrders->avg('items_count')), 'note' => 'Kepadatan item di setiap dokumen purchase order'],
                ['label' => 'Due in 3 days', 'value' => number_format($purchaseOrders->filter(fn (PurchaseOrder $purchaseOrder): bool => $purchaseOrder->expected_date !== null && $purchaseOrder->expected_date->between(now(), now()->addDays(3)) && in_array($purchaseOrder->status, [PurchaseOrder::STATUS_PENDING_APPROVAL, PurchaseOrder::STATUS_APPROVED], true))->count(), 0, ',', '.'), 'note' => 'Receiving plan perlu disiapkan untuk inbound yang segera datang'],
                ['label' => 'Closed PO value', 'value' => $this->formatCompactCurrency($purchaseOrders->whereIn('status', [PurchaseOrder::STATUS_RECEIVED, PurchaseOrder::STATUS_CANCELLED, PurchaseOrder::STATUS_REJECTED])->sum('total_amount')), 'note' => 'Nilai PO yang sudah selesai atau ditutup dari workflow'],
            ],
            'purchaseOrders' => $purchaseOrders->map(function (PurchaseOrder $purchaseOrder): array {
                return [
                    'id' => $purchaseOrder->id,
                    'po_number' => $purchaseOrder->po_number,
                    'supplier' => $purchaseOrder->supplier?->name ?? '-',
                    'warehouse' => $purchaseOrder->warehouse?->name ?? '-',
                    'buyer' => $purchaseOrder->creator?->name ?? 'System Buyer',
                    'order_date' => $purchaseOrder->order_date?->format('d M Y') ?? '-',
                    'expected_date' => $purchaseOrder->expected_date?->format('d M Y') ?? '-',
                    'due_date' => $purchaseOrder->due_date?->format('d M Y') ?? '-',
                    'total_amount' => $this->formatCompactCurrency((float) $purchaseOrder->total_amount),
                    'paid_amount' => $this->formatCompactCurrency((float) $purchaseOrder->paid_amount),
                    'balance_due' => $this->formatCompactCurrency((float) $purchaseOrder->balance_due),
                    'status' => $purchaseOrder->status,
                    'status_label' => PurchaseOrder::statusOptions()[$purchaseOrder->status] ?? ucfirst($purchaseOrder->status),
                    'payment_status' => PurchaseOrder::paymentStatusOptions()[$purchaseOrder->payment_status] ?? ucfirst($purchaseOrder->payment_status),
                    'items_count' => $purchaseOrder->items_count,
                    'edit_url' => route('purchase-orders.edit', $purchaseOrder),
                    'can_edit' => $purchaseOrder->canBeEdited(),
                    'can_submit' => in_array($purchaseOrder->status, [PurchaseOrder::STATUS_DRAFT, PurchaseOrder::STATUS_REJECTED], true),
                    'can_approve' => $purchaseOrder->canBeApproved(),
                    'can_reject' => $purchaseOrder->status === PurchaseOrder::STATUS_PENDING_APPROVAL,
                    'can_cancel' => $purchaseOrder->canBeCancelled(),
                    'can_receive' => $purchaseOrder->canBeReceived(),
                    'can_pay' => in_array($purchaseOrder->status, [PurchaseOrder::STATUS_APPROVED, PurchaseOrder::STATUS_PARTIALLY_RECEIVED, PurchaseOrder::STATUS_RECEIVED], true) && (float) $purchaseOrder->balance_due > 0,
                    'submit_url' => route('purchase-orders.submit', $purchaseOrder),
                    'approve_url' => route('purchase-orders.approve', $purchaseOrder),
                    'reject_url' => route('purchase-orders.reject', $purchaseOrder),
                    'cancel_url' => route('purchase-orders.cancel', $purchaseOrder),
                    'receive_url' => route('goods-receipts.create', $purchaseOrder),
                    'payment_url' => route('purchase-orders.payment-form', $purchaseOrder),
                ];
            })->all(),
            'pipelineCards' => [
                ['title' => 'Draft PO', 'value' => number_format($purchaseOrders->where('status', PurchaseOrder::STATUS_DRAFT)->count(), 0, ',', '.'), 'note' => 'Dokumen pembelian yang masih bisa diubah bebas oleh tim buyer'],
                ['title' => 'Pending approval', 'value' => number_format($pendingApproval->count(), 0, ',', '.'), 'note' => 'Tahap paling krusial untuk mempercepat suplai ke warehouse'],
                ['title' => 'Approved & inbound', 'value' => number_format($approvedOrders->count(), 0, ',', '.'), 'note' => 'PO approved yang harus dikawal ke receiving dan inventory ledger'],
                ['title' => 'Rejected/cancelled', 'value' => number_format($purchaseOrders->whereIn('status', [PurchaseOrder::STATUS_REJECTED, PurchaseOrder::STATUS_CANCELLED])->count(), 0, ',', '.'), 'note' => 'Dokumen yang ditutup dan perlu evaluasi alasan bisnisnya'],
            ],
            'spendMix' => $this->purchaseOrderSpendMix($purchaseOrders),
            'actionQueue' => $this->purchaseOrderActionQueue($purchaseOrders),
            'createUrl' => route('purchase-orders.create'),
        ]);
    }

    public function purchaseOrderFormData(?PurchaseOrder $purchaseOrder = null): array
    {
        $purchaseOrder?->loadMissing(['items.product', 'supplier', 'warehouse']);
        $products = Product::query()->where('status', Product::STATUS_ACTIVE)->orderBy('name')->get();

        return [
            'title' => $purchaseOrder?->exists ? 'Edit Purchase Order' : 'Buat Purchase Order',
            'pageTitle' => $purchaseOrder?->exists ? 'Edit Purchase Order' : 'Buat Purchase Order',
            'pageEyebrow' => 'Procurement',
            'pageDescription' => $purchaseOrder?->exists
                ? 'Perbarui header dan line item purchase order sebelum dokumen ditutup atau di-approve.'
                : 'Buat purchase order baru lengkap dengan supplier, lokasi receiving, dan line item pembelian.',
            'purchaseOrder' => $purchaseOrder,
            'suppliers' => Supplier::query()->where('is_active', true)->orderBy('name')->get(),
            'warehouses' => Warehouse::query()->where('is_active', true)->orderBy('name')->get(),
            'products' => $products,
            'productsForPicker' => $products->map(fn (Product $product): array => [
                'id' => $product->id,
                'sku' => $product->sku,
                'name' => $product->name,
                'unit_of_measure' => $product->unit_of_measure,
                'cost_price' => (float) $product->cost_price,
            ])->values()->all(),
            'submitUrl' => $purchaseOrder?->exists ? route('purchase-orders.update', $purchaseOrder) : route('purchase-orders.store'),
            'submitMethod' => $purchaseOrder?->exists ? 'PUT' : 'POST',
            'backUrl' => route('purchase-orders'),
            'generatedAt' => $this->timestamp(),
        ];
    }

    public function purchaseOrderPaymentFormData(PurchaseOrder $purchaseOrder): array
    {
        $purchaseOrder->loadMissing(['supplier', 'warehouse', 'payments.paymentMethod']);

        return [
            'title' => 'Bayar Purchase Order',
            'pageTitle' => 'Bayar Purchase Order',
            'pageEyebrow' => 'Keuangan',
            'pageDescription' => 'Catat pembayaran supplier agar hutang, due date, dan cash planning procurement tetap terkendali.',
            'purchaseOrder' => $purchaseOrder,
            'paymentMethods' => PaymentMethod::query()->where('is_active', true)->orderBy('name')->get(),
            'submitUrl' => route('purchase-orders.pay', $purchaseOrder),
            'backUrl' => route('receivables-payables'),
            'generatedAt' => $this->timestamp(),
        ];
    }

    public function posTransactionIndexData(): array
    {
        $page = $this->basePage('pos-transactions');
        $transactions = SalesTransaction::query()
            ->with(['outlet', 'cashier', 'customer', 'items.product', 'payments.paymentMethod'])
            ->orderByDesc('sold_at')
            ->get();
        $payments = $transactions->flatMap(fn (SalesTransaction $transaction): Collection => $transaction->payments);

        return array_merge($page, [
            'generatedAt' => $this->timestamp(),
            'heroStats' => [
                ['label' => 'Posted transactions', 'value' => number_format($transactions->count(), 0, ',', '.'), 'caption' => 'Checkout outlet yang sudah diposting ke penjualan dan pergerakan stok'],
                ['label' => 'Net sales', 'value' => $this->formatCompactCurrency($transactions->sum('net_amount')), 'caption' => 'Omzet retail dari transaksi POS dan invoice outlet'],
                ['label' => 'Units sold', 'value' => number_format((float) $transactions->sum('items_count'), 0, ',', '.'), 'caption' => 'Pergerakan unit yang keluar dari outlet melalui POS'],
                ['label' => 'Split payment', 'value' => $this->formatPercent($this->percent($transactions->where('split_payment_count', '>', 1)->count(), max($transactions->count(), 1))), 'caption' => 'Persentase transaksi yang memakai lebih dari satu metode pembayaran'],
            ],
            'metrics' => [
                ['label' => 'Average basket', 'value' => $this->formatCompactCurrency($transactions->avg('net_amount')), 'note' => 'Nilai transaksi rata-rata untuk membaca kualitas penjualan outlet.'],
                ['label' => 'Digital payment share', 'value' => $this->formatPercent($this->percent($payments->filter(fn ($payment): bool => in_array($payment->paymentMethod?->category, ['qris', 'card', 'ewallet', 'bank_transfer'], true))->sum('amount'), max($payments->sum('amount'), 1))), 'note' => 'Porsi kanal digital terhadap total pembayaran POS.'],
                ['label' => 'Active outlets', 'value' => number_format($transactions->whereNotNull('outlet_id')->groupBy('outlet_id')->count(), 0, ',', '.'), 'note' => 'Outlet yang benar-benar menghasilkan transaksi pada dataset saat ini.'],
                ['label' => 'Open invoice balance', 'value' => $this->formatCompactCurrency($transactions->sum('balance_due')), 'note' => 'Sisa tagihan yang masih terbuka dari invoice customer.'],
            ],
            'transactions' => $transactions->map(function (SalesTransaction $transaction): array {
                return [
                    'transaction_number' => $transaction->transaction_number,
                    'invoice_number' => $transaction->invoice_number ?? $transaction->transaction_number,
                    'outlet' => $transaction->outlet?->name ?? '-',
                    'cashier' => $transaction->cashier?->full_name ?? 'System POS',
                    'customer' => $transaction->customer?->name ?? ($transaction->customer_name ?: 'Walk-in Customer'),
                    'sold_at' => $transaction->sold_at?->format('d M Y H:i') ?? '-',
                    'items_count' => number_format((int) $transaction->items_count, 0, ',', '.'),
                    'net_amount' => $this->formatCompactCurrency((float) $transaction->net_amount),
                    'paid_amount' => $this->formatCompactCurrency((float) $transaction->paid_amount),
                    'balance_due' => $this->formatCompactCurrency((float) $transaction->balance_due),
                    'payment_status' => SalesTransaction::paymentStatusOptions()[$transaction->payment_status] ?? ucfirst($transaction->payment_status),
                    'payments' => $transaction->payments->map(fn ($payment): string => ($payment->paymentMethod?->name ?? '-') . ' ' . $this->formatCompactCurrency((float) $payment->amount))->join(' / '),
                    'lines' => $transaction->items->map(fn ($item): string => ($item->product?->name ?? '-') . ' x' . $this->formatDecimal((float) $item->quantity))->join(', '),
                ];
            })->all(),
            'paymentCards' => PaymentMethod::query()->where('is_active', true)->orderBy('name')->get()->map(function (PaymentMethod $method) use ($payments): array {
                $methodPayments = $payments->where('payment_method_id', $method->id);

                return [
                    'title' => $method->name,
                    'value' => $this->formatCompactCurrency($methodPayments->sum('amount')),
                    'note' => number_format($methodPayments->count(), 0, ',', '.') . ' pembayaran / fee ' . $this->formatPercent((float) $method->transaction_fee_rate),
                ];
            })->all(),
            'createUrl' => route('sales-transactions.create'),
        ]);
    }

    public function posTransactionFormData(): array
    {
        $outlets = Outlet::query()
            ->where('status', Outlet::STATUS_ACTIVE)
            ->whereNotNull('warehouse_id')
            ->orderBy('name')
            ->get();
        $products = Product::query()->where('status', Product::STATUS_ACTIVE)->orderBy('name')->get();
        $stockMap = $this->stockMapByWarehouse();

        return [
            'title' => 'Buat Transaksi POS',
            'pageTitle' => 'Buat Transaksi POS',
            'pageEyebrow' => 'Sales & POS',
            'pageDescription' => 'Checkout retail barcode-centric dengan split payment real-time, posting stok otomatis, dan jurnal akuntansi atomik dalam satu transaksi.',
            'outlets' => $outlets,
            'cashiers' => Employee::query()->where('status', Employee::STATUS_ACTIVE)->where('department', 'Retail Operations')->orderBy('full_name')->get(),
            'customers' => Customer::query()->where('status', Customer::STATUS_ACTIVE)->orderBy('name')->get(),
            'paymentMethods' => PaymentMethod::query()->where('is_active', true)->orderBy('name')->get(),
            'productsForPicker' => $products->map(function (Product $product) use ($outlets, $stockMap): array {
                $stockByOutlet = [];

                foreach ($outlets as $outlet) {
                    $stockByOutlet[$outlet->id] = (float) ($stockMap[$outlet->warehouse_id][$product->id] ?? 0);
                }

                return [
                    'id' => $product->id,
                    'sku' => $product->sku,
                    'barcode' => $product->barcode,
                    'name' => $product->name,
                    'selling_price' => (float) $product->selling_price,
                    'unit_cost' => (float) $product->cost_price,
                    'unit_of_measure' => $product->unit_of_measure,
                    'stock_by_outlet' => $stockByOutlet,
                ];
            })->values()->all(),
            'submitUrl' => route('sales-transactions.store'),
            'backUrl' => route('pos-transactions'),
            'generatedAt' => $this->timestamp(),
        ];
    }

    public function salesInvoiceIndexData(): array
    {
        $page = $this->basePage('sales-invoices');
        $transactions = SalesTransaction::query()
            ->with(['customer', 'outlet', 'payments.paymentMethod'])
            ->orderByDesc('invoice_date')
            ->orderByDesc('id')
            ->get();
        $openInvoices = $transactions->where('balance_due', '>', 0);

        return array_merge($page, [
            'generatedAt' => $this->timestamp(),
            'heroStats' => [
                ['label' => 'Invoices', 'value' => number_format($transactions->count(), 0, ',', '.'), 'caption' => 'Dokumen invoice retail yang tercatat dari transaksi outlet'],
                ['label' => 'Open receivable', 'value' => $this->formatCompactCurrency($openInvoices->sum('balance_due')), 'caption' => 'Saldo piutang yang masih harus ditagih dari customer'],
                ['label' => 'Overdue invoices', 'value' => number_format($openInvoices->filter(fn (SalesTransaction $transaction): bool => $transaction->due_date !== null && $transaction->due_date->isPast())->count(), 0, ',', '.'), 'caption' => 'Invoice yang sudah melewati due date dan perlu follow-up collection'],
                ['label' => 'Collected', 'value' => $this->formatCompactCurrency($transactions->sum('paid_amount')), 'caption' => 'Nilai pembayaran customer yang sudah masuk ke invoice'],
            ],
            'metrics' => [
                ['label' => 'Average DSO proxy', 'value' => $this->formatDecimal($openInvoices->filter(fn (SalesTransaction $transaction): bool => $transaction->invoice_date !== null)->avg(fn (SalesTransaction $transaction): float => max((int) $transaction->invoice_date?->diffInDays(now()) ?? 0, 0))) . ' hari', 'note' => 'Pendekatan hari outstanding dari invoice yang masih terbuka.'],
                ['label' => 'Paid ratio', 'value' => $this->formatPercent($this->percent($transactions->where('payment_status', 'paid')->count(), max($transactions->count(), 1))), 'note' => 'Porsi invoice yang sudah lunas sepenuhnya.'],
                ['label' => 'Partial ratio', 'value' => $this->formatPercent($this->percent($transactions->where('payment_status', 'partial')->count(), max($transactions->count(), 1))), 'note' => 'Porsi invoice yang sudah dibayar sebagian dan masih butuh settlement lanjutan.'],
                ['label' => 'Customer billed', 'value' => number_format($transactions->whereNotNull('customer_id')->groupBy('customer_id')->count(), 0, ',', '.'), 'note' => 'Customer unik yang sudah masuk histori billing retail.'],
            ],
            'invoices' => $transactions->map(function (SalesTransaction $transaction): array {
                return [
                    'id' => $transaction->id,
                    'invoice_number' => $transaction->invoice_number ?? $transaction->transaction_number,
                    'invoice_date' => $transaction->invoice_date?->format('d M Y') ?? '-',
                    'due_date' => $transaction->due_date?->format('d M Y') ?? '-',
                    'customer' => $transaction->customer?->name ?? ($transaction->customer_name ?: 'Walk-in Customer'),
                    'outlet' => $transaction->outlet?->name ?? '-',
                    'net_amount' => $this->formatCompactCurrency((float) $transaction->net_amount),
                    'paid_amount' => $this->formatCompactCurrency((float) $transaction->paid_amount),
                    'balance_due' => $this->formatCompactCurrency((float) $transaction->balance_due),
                    'payment_status' => SalesTransaction::paymentStatusOptions()[$transaction->payment_status] ?? ucfirst($transaction->payment_status),
                    'payments' => $transaction->payments->map(fn ($payment): string => ($payment->paymentMethod?->name ?? '-') . ' ' . $this->formatCompactCurrency((float) $payment->amount))->join(' / '),
                    'can_pay' => (float) $transaction->balance_due > 0,
                    'payment_url' => route('sales-invoices.payment-form', $transaction),
                ];
            })->all(),
            'collectionCards' => [
                ['title' => 'Current', 'value' => $this->formatCompactCurrency($openInvoices->filter(fn (SalesTransaction $transaction): bool => $transaction->due_date !== null && $transaction->due_date->isFuture())->sum('balance_due')), 'note' => 'Tagihan yang masih berada dalam termin pembayaran.'],
                ['title' => 'Overdue', 'value' => $this->formatCompactCurrency($openInvoices->filter(fn (SalesTransaction $transaction): bool => $transaction->due_date !== null && $transaction->due_date->isPast())->sum('balance_due')), 'note' => 'Invoice yang perlu ditindaklanjuti oleh tim collection atau sales admin.'],
                ['title' => 'Top customer exposure', 'value' => $openInvoices->groupBy(fn (SalesTransaction $transaction): string => $transaction->customer?->name ?? ($transaction->customer_name ?: 'Walk-in Customer'))->map(fn (Collection $group): float => $group->sum('balance_due'))->sortDesc()->keys()->first() ?? '-', 'note' => 'Customer dengan eksposur invoice berjalan terbesar saat ini.'],
            ],
        ]);
    }

    public function salesInvoicePaymentFormData(SalesTransaction $salesTransaction): array
    {
        $salesTransaction->loadMissing(['customer', 'outlet', 'payments.paymentMethod']);

        return [
            'title' => 'Terima Pembayaran Invoice',
            'pageTitle' => 'Terima Pembayaran Invoice',
            'pageEyebrow' => 'Sales & POS',
            'pageDescription' => 'Catat settlement invoice customer agar saldo piutang, cashflow, dan histori pembayaran tetap sinkron.',
            'salesTransaction' => $salesTransaction,
            'paymentMethods' => PaymentMethod::query()->where('is_active', true)->orderBy('name')->get(),
            'submitUrl' => route('sales-invoices.pay', $salesTransaction),
            'backUrl' => route('sales-invoices'),
            'generatedAt' => $this->timestamp(),
        ];
    }

    public function stockTransferIndexData(): array
    {
        $page = $this->basePage('stock-mutation');
        $transfers = StockTransfer::query()
            ->with(['sourceWarehouse', 'destinationWarehouse', 'requester', 'items.product'])
            ->withCount('items')
            ->orderByRaw(
                "case status
                    when ? then 1
                    when ? then 2
                    when ? then 3
                    when ? then 4
                    when ? then 5
                    when ? then 6
                    else 99
                end",
                [
                    StockTransfer::STATUS_PENDING_APPROVAL,
                    StockTransfer::STATUS_DRAFT,
                    StockTransfer::STATUS_APPROVED,
                    StockTransfer::STATUS_RECEIVED,
                    StockTransfer::STATUS_REJECTED,
                    StockTransfer::STATUS_CANCELLED,
                ]
            )
            ->orderByDesc('request_date')
            ->get();

        return array_merge($page, [
            'generatedAt' => $this->timestamp(),
            'heroStats' => [
                ['label' => 'Open transfers', 'value' => number_format($transfers->whereIn('status', [StockTransfer::STATUS_DRAFT, StockTransfer::STATUS_PENDING_APPROVAL, StockTransfer::STATUS_APPROVED])->count(), 0, ',', '.'), 'caption' => 'Mutasi stok yang masih berjalan antar gudang atau outlet'],
                ['label' => 'Pending approval', 'value' => number_format($transfers->where('status', StockTransfer::STATUS_PENDING_APPROVAL)->count(), 0, ',', '.'), 'caption' => 'Dokumen transfer yang menunggu persetujuan sebelum barang bergerak'],
                ['label' => 'Received transfers', 'value' => number_format($transfers->where('status', StockTransfer::STATUS_RECEIVED)->count(), 0, ',', '.'), 'caption' => 'Mutasi yang sudah diterima dan diposting ke dua sisi warehouse'],
                ['label' => 'Transfer value', 'value' => $this->formatCompactCurrency($transfers->whereIn('status', [StockTransfer::STATUS_APPROVED, StockTransfer::STATUS_RECEIVED])->sum('total_cost')), 'caption' => 'Nilai barang yang sedang atau sudah dipindahkan antar lokasi'],
            ],
            'metrics' => [
                ['label' => 'Average line per transfer', 'value' => $this->formatDecimal($transfers->avg('items_count')), 'note' => 'Kepadatan SKU per dokumen transfer untuk membaca kompleksitas operasional.'],
                ['label' => 'Pending quantity', 'value' => number_format((float) $transfers->where('status', StockTransfer::STATUS_APPROVED)->sum('total_quantity'), 0, ',', '.'), 'note' => 'Jumlah unit yang sudah approved tetapi belum diterima oleh lokasi tujuan.'],
                ['label' => 'Warehouse lanes', 'value' => number_format($transfers->map(fn (StockTransfer $transfer): string => $transfer->source_warehouse_id . '-' . $transfer->destination_warehouse_id)->unique()->count(), 0, ',', '.'), 'note' => 'Jalur mutasi aktif yang saat ini dipakai jaringan retail.'],
                ['label' => 'Approval queue', 'value' => number_format($transfers->where('status', StockTransfer::STATUS_PENDING_APPROVAL)->count(), 0, ',', '.'), 'note' => 'Dokumen yang menunggu keputusan inventory control atau supervisor area.'],
            ],
            'transfers' => $transfers->map(function (StockTransfer $transfer): array {
                $receivedQuantity = $transfer->items->sum('received_quantity');

                return [
                    'id' => $transfer->id,
                    'transfer_number' => $transfer->transfer_number,
                    'lane' => ($transfer->sourceWarehouse?->name ?? '-') . ' -> ' . ($transfer->destinationWarehouse?->name ?? '-'),
                    'request_date' => $transfer->request_date?->format('d M Y') ?? '-',
                    'expected_receipt_date' => $transfer->expected_receipt_date?->format('d M Y') ?? '-',
                    'status' => $transfer->status,
                    'status_label' => StockTransfer::statusOptions()[$transfer->status] ?? ucfirst($transfer->status),
                    'total_quantity' => number_format((float) $transfer->total_quantity, 0, ',', '.'),
                    'progress' => number_format((float) $receivedQuantity, 0, ',', '.') . ' / ' . number_format((float) $transfer->total_quantity, 0, ',', '.'),
                    'total_cost' => $this->formatCompactCurrency((float) $transfer->total_cost),
                    'edit_url' => route('stock-transfers.edit', $transfer),
                    'submit_url' => route('stock-transfers.submit', $transfer),
                    'approve_url' => route('stock-transfers.approve', $transfer),
                    'reject_url' => route('stock-transfers.reject', $transfer),
                    'cancel_url' => route('stock-transfers.cancel', $transfer),
                    'receive_url' => route('stock-transfers.receive-form', $transfer),
                    'can_edit' => $transfer->canBeEdited(),
                    'can_submit' => in_array($transfer->status, [StockTransfer::STATUS_DRAFT, StockTransfer::STATUS_REJECTED], true),
                    'can_approve' => $transfer->canBeApproved(),
                    'can_reject' => $transfer->status === StockTransfer::STATUS_PENDING_APPROVAL,
                    'can_cancel' => $transfer->canBeCancelled(),
                    'can_receive' => $transfer->canBeReceived(),
                ];
            })->all(),
            'pipelineCards' => [
                ['title' => 'Draft', 'value' => number_format($transfers->where('status', StockTransfer::STATUS_DRAFT)->count(), 0, ',', '.'), 'note' => 'Transfer yang masih disusun oleh planner atau supervisor outlet.'],
                ['title' => 'Approved lanes', 'value' => number_format($transfers->where('status', StockTransfer::STATUS_APPROVED)->count(), 0, ',', '.'), 'note' => 'Barang siap diterima tujuan dan akan memengaruhi stok outlet saat receiving diposting.'],
                ['title' => 'Received this month', 'value' => number_format($transfers->where('status', StockTransfer::STATUS_RECEIVED)->filter(fn (StockTransfer $transfer): bool => $transfer->received_at?->isCurrentMonth() ?? false)->count(), 0, ',', '.'), 'note' => 'Mutasi yang sudah selesai pada bulan berjalan.'],
            ],
            'createUrl' => route('stock-transfers.create'),
        ]);
    }

    public function stockTransferFormData(?StockTransfer $stockTransfer = null): array
    {
        $stockTransfer?->loadMissing('items.product');
        $warehouses = Warehouse::query()->where('is_active', true)->orderBy('name')->get();
        $products = Product::query()->where('status', Product::STATUS_ACTIVE)->orderBy('name')->get();
        $stockMap = $this->stockMapByWarehouse();

        return [
            'title' => $stockTransfer?->exists ? 'Edit Transfer Stok' : 'Buat Transfer Stok',
            'pageTitle' => $stockTransfer?->exists ? 'Edit Transfer Stok' : 'Buat Transfer Stok',
            'pageEyebrow' => 'Mutasi Stok',
            'pageDescription' => $stockTransfer?->exists
                ? 'Perbarui jalur mutasi dan item transfer sebelum dokumen disetujui atau diterima.'
                : 'Buat transfer stok antar warehouse dan outlet agar balancing persediaan lebih disiplin.',
            'stockTransfer' => $stockTransfer,
            'warehouses' => $warehouses,
            'productsForPicker' => $products->map(function (Product $product) use ($warehouses, $stockMap): array {
                $stockByWarehouse = [];

                foreach ($warehouses as $warehouse) {
                    $stockByWarehouse[$warehouse->id] = (float) ($stockMap[$warehouse->id][$product->id] ?? 0);
                }

                return [
                    'id' => $product->id,
                    'sku' => $product->sku,
                    'name' => $product->name,
                    'unit_of_measure' => $product->unit_of_measure,
                    'stock_by_warehouse' => $stockByWarehouse,
                ];
            })->values()->all(),
            'submitUrl' => $stockTransfer?->exists ? route('stock-transfers.update', $stockTransfer) : route('stock-transfers.store'),
            'submitMethod' => $stockTransfer?->exists ? 'PUT' : 'POST',
            'backUrl' => route('stock-mutation'),
            'generatedAt' => $this->timestamp(),
        ];
    }

    public function stockTransferReceiveFormData(StockTransfer $stockTransfer): array
    {
        $stockTransfer->loadMissing(['sourceWarehouse', 'destinationWarehouse', 'items.product']);

        return [
            'title' => 'Terima Transfer Stok',
            'pageTitle' => 'Terima Transfer Stok',
            'pageEyebrow' => 'Mutasi Stok',
            'pageDescription' => 'Konfirmasi qty yang benar-benar diterima di lokasi tujuan untuk mem-post transfer ke inventory ledger.',
            'stockTransfer' => $stockTransfer,
            'submitUrl' => route('stock-transfers.receive', $stockTransfer),
            'backUrl' => route('stock-mutation'),
            'generatedAt' => $this->timestamp(),
        ];
    }

    public function goodsReceiptIndexData(): array
    {
        $page = $this->basePage('goods-receipts');
        $receipts = GoodsReceipt::query()
            ->with(['purchaseOrder.supplier', 'warehouse', 'receiver', 'items.product'])
            ->orderByDesc('received_at')
            ->get();

        return array_merge($page, [
            'generatedAt' => $this->timestamp(),
            'heroStats' => [
                ['label' => 'Receipts posted', 'value' => number_format($receipts->count(), 0, ',', '.'), 'caption' => 'Dokumen penerimaan yang sudah mem-post stok dari purchase order'],
                ['label' => 'Total qty received', 'value' => number_format((float) $receipts->sum('total_quantity'), 0, ',', '.'), 'caption' => 'Jumlah unit yang sudah diterima ke warehouse'],
                ['label' => 'Receipt value', 'value' => $this->formatCompactCurrency($receipts->sum('total_cost')), 'caption' => 'Nilai inventory masuk yang sudah dicatat lewat receiving'],
                ['label' => 'Ready to receive', 'value' => number_format(PurchaseOrder::query()->whereIn('status', [PurchaseOrder::STATUS_APPROVED, PurchaseOrder::STATUS_PARTIALLY_RECEIVED])->count(), 0, ',', '.'), 'caption' => 'PO approved yang siap diproses ke inbound berikutnya'],
            ],
            'metrics' => [
                ['label' => 'Average lines', 'value' => $this->formatDecimal($receipts->avg(fn (GoodsReceipt $receipt): int => $receipt->items->count())), 'note' => 'Rata-rata item per receiving document.'],
                ['label' => 'Received this month', 'value' => number_format($receipts->filter(fn (GoodsReceipt $receipt): bool => $receipt->received_at?->isCurrentMonth() ?? false)->count(), 0, ',', '.'), 'note' => 'Inbound yang berhasil diposting pada bulan berjalan.'],
                ['label' => 'PO partially received', 'value' => number_format(PurchaseOrder::query()->where('status', PurchaseOrder::STATUS_PARTIALLY_RECEIVED)->count(), 0, ',', '.'), 'note' => 'Purchase order yang masih punya outstanding inbound.'],
                ['label' => 'Pending inbound value', 'value' => $this->formatCompactCurrency(PurchaseOrder::query()->whereIn('status', [PurchaseOrder::STATUS_APPROVED, PurchaseOrder::STATUS_PARTIALLY_RECEIVED])->sum('total_amount')), 'note' => 'Nilai PO yang masih menunggu kedatangan fisik barang.'],
            ],
            'receipts' => $receipts->map(function (GoodsReceipt $receipt): array {
                return [
                    'receipt_number' => $receipt->receipt_number,
                    'purchase_order' => $receipt->purchaseOrder?->po_number ?? '-',
                    'supplier' => $receipt->purchaseOrder?->supplier?->name ?? '-',
                    'warehouse' => $receipt->warehouse?->name ?? '-',
                    'received_at' => $receipt->received_at?->format('d M Y H:i') ?? '-',
                    'quantity' => number_format((float) $receipt->total_quantity, 0, ',', '.'),
                    'value' => $this->formatCompactCurrency((float) $receipt->total_cost),
                    'receiver' => $receipt->receiver?->name ?? 'System Receiving',
                ];
            })->all(),
            'receiptCards' => $receipts->take(4)->map(function (GoodsReceipt $receipt): array {
                return [
                    'title' => $receipt->receipt_number,
                    'value' => $this->formatCompactCurrency((float) $receipt->total_cost),
                    'note' => ($receipt->warehouse?->name ?? '-') . ' / ' . number_format($receipt->items->count(), 0, ',', '.') . ' item',
                ];
            })->values()->all(),
        ]);
    }

    public function goodsReceiptFormData(PurchaseOrder $purchaseOrder): array
    {
        $purchaseOrder->loadMissing(['supplier', 'warehouse', 'items.product']);

        return [
            'title' => 'Terima Barang dari PO',
            'pageTitle' => 'Terima Barang dari PO',
            'pageEyebrow' => 'Penerimaan Barang',
            'pageDescription' => 'Catat qty yang benar-benar datang dari supplier agar stok, PO, dan warehouse tetap sinkron.',
            'purchaseOrder' => $purchaseOrder,
            'submitUrl' => route('goods-receipts.store', $purchaseOrder),
            'backUrl' => route('purchase-orders'),
            'generatedAt' => $this->timestamp(),
        ];
    }

    public function warehouseOverviewData(): array
    {
        $page = $this->basePage('warehouse');
        $warehouseRows = $this->warehouseStockRows();
        $warehouses = Warehouse::query()
            ->withCount(['goodsReceipts', 'sourceStockTransfers', 'destinationStockTransfers'])
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return array_merge($page, $this->connectedBoardData([
            'heroStats' => [
                ['label' => 'Gudang aktif', 'value' => number_format($warehouses->count(), 0, ',', '.'), 'caption' => 'DC dan gudang toko yang saat ini menjadi titik persediaan retail'],
                ['label' => 'Nilai stok', 'value' => $this->formatCompactCurrency($warehouseRows->sum(fn (object $row): float => $row->on_hand * $row->cost_price)), 'caption' => 'Valuasi stok seluruh jaringan warehouse dari inventory ledger'],
                ['label' => 'Transfer posted', 'value' => number_format(StockTransfer::query()->where('status', StockTransfer::STATUS_RECEIVED)->count(), 0, ',', '.'), 'caption' => 'Dokumen mutasi antar gudang dan outlet yang sudah selesai diterima'],
                ['label' => 'Inbound receipts', 'value' => number_format(GoodsReceipt::query()->count(), 0, ',', '.'), 'caption' => 'History penerimaan barang dari purchase order ke warehouse'],
            ],
            'metrics' => [
                ['label' => 'Warehouse coverage', 'value' => number_format($warehouseRows->groupBy('warehouse_id')->count(), 0, ',', '.'), 'note' => 'Jumlah lokasi stok yang benar-benar punya posisi persediaan aktif'],
                ['label' => 'Average stock value', 'value' => $this->formatCompactCurrency($warehouseRows->groupBy('warehouse_id')->map(fn (Collection $group): float => $group->sum(fn (object $row): float => $row->on_hand * $row->cost_price))->avg()), 'note' => 'Rata-rata nilai stok per warehouse untuk membaca kapasitas modal kerja'],
                ['label' => 'Critical locations', 'value' => number_format($warehouseRows->groupBy('warehouse_id')->filter(fn (Collection $group): bool => $group->contains(fn (object $row): bool => $row->on_hand <= $row->reorder_level))->count(), 0, ',', '.'), 'note' => 'Gudang yang menampung minimal satu SKU kritikal dan perlu perhatian replenishment'],
                ['label' => 'Receiving throughput', 'value' => number_format(GoodsReceipt::query()->whereMonth('received_at', now()->month)->count(), 0, ',', '.'), 'note' => 'Dokumen receiving pada bulan berjalan sebagai indikator arus barang masuk'],
            ],
            'mainTitle' => 'Warehouse Network',
            'mainDescription' => 'Setiap gudang sekarang terbaca bersama valuasi stok, receiving PO, dan mutasi antar outlet.',
            'tableColumns' => ['Gudang', 'Tipe', 'Kota', 'Stock Value', 'Coverage', 'Aktivitas'],
            'tableRows' => $warehouses->map(function (Warehouse $warehouse) use ($warehouseRows): array {
                $rows = $warehouseRows->where('warehouse_id', $warehouse->id);
                $stockValue = $rows->sum(fn (object $row): float => $row->on_hand * $row->cost_price);

                return [
                    'Gudang' => $warehouse->name,
                    'Tipe' => Warehouse::typeOptions()[$warehouse->type] ?? ucfirst(str_replace('_', ' ', $warehouse->type)),
                    'Kota' => $warehouse->city,
                    'Stock Value' => $this->formatCompactCurrency($stockValue),
                    'Coverage' => number_format($rows->where('on_hand', '>', 0)->count(), 0, ',', '.') . ' SKU aktif',
                    'Aktivitas' => number_format((int) $warehouse->goods_receipts_count, 0, ',', '.') . ' receipts / ' . number_format((int) $warehouse->destination_stock_transfers_count, 0, ',', '.') . ' inbound',
                ];
            })->all(),
            'sideTitle' => 'Koneksi Modul',
            'sideCards' => [
                ['title' => 'Receiving dari PO', 'value' => number_format(PurchaseOrder::query()->whereIn('status', [PurchaseOrder::STATUS_APPROVED, PurchaseOrder::STATUS_PARTIALLY_RECEIVED])->count(), 0, ',', '.'), 'note' => 'Warehouse menjadi titik posting stok utama untuk purchase order yang sudah approved.'],
                ['title' => 'Transfer antar lokasi', 'value' => number_format(StockTransfer::query()->whereIn('status', [StockTransfer::STATUS_PENDING_APPROVAL, StockTransfer::STATUS_APPROVED])->count(), 0, ',', '.'), 'note' => 'Mutasi aktif akan memengaruhi cover stok outlet dan balancing antar gudang.'],
                ['title' => 'POS linked outlet', 'value' => number_format(Outlet::query()->whereNotNull('warehouse_id')->count(), 0, ',', '.'), 'note' => 'Outlet yang sudah terhubung ke gudang toko bisa langsung mem-post penjualan ke inventory ledger.'],
            ],
        ]));
    }

    public function categoryDirectoryData(): array
    {
        $page = $this->basePage('kategori');
        $categories = Category::query()->withCount('products')->with(['products' => fn ($query) => $query->where('status', Product::STATUS_ACTIVE)])->orderBy('sort_order')->orderBy('name')->get();
        $activeCategories = $categories->where('is_active', true);
        $largestCategory = $categories->sortByDesc('products_count')->first();

        return array_merge($page, [
            'generatedAt' => $this->timestamp(),
            'heroStats' => [
                ['label' => 'Kategori aktif', 'value' => number_format($activeCategories->count(), 0, ',', '.'), 'caption' => 'Kategori yang saat ini menopang katalog retail'],
                ['label' => 'SKU mapped', 'value' => number_format($categories->sum('products_count'), 0, ',', '.'), 'caption' => 'Jumlah master produk yang sudah masuk struktur kategori'],
                ['label' => 'Featured categories', 'value' => number_format($categories->filter(fn (Category $category): bool => $category->products->where('is_featured', true)->isNotEmpty())->count(), 0, ',', '.'), 'caption' => 'Kategori yang punya SKU prioritas untuk promo atau display'],
                ['label' => 'Largest assortment', 'value' => $largestCategory?->name ?? '-', 'caption' => 'Kategori dengan jumlah SKU paling besar saat ini'],
            ],
            'metrics' => [
                ['label' => 'Average SKU per category', 'value' => $this->formatDecimal($categories->avg('products_count')), 'note' => 'Membaca kepadatan assortment agar kategori tidak terlalu tipis atau terlalu berat.'],
                ['label' => 'Active category share', 'value' => $this->formatPercent($this->percent($activeCategories->count(), max($categories->count(), 1))), 'note' => 'Proporsi kategori yang masih aktif secara komersial.'],
                ['label' => 'Featured SKU share', 'value' => $this->formatPercent($this->percent($categories->sum(fn (Category $category): int => $category->products->where('is_featured', true)->count()), max(Product::query()->count(), 1))), 'note' => 'Porsi SKU unggulan terhadap keseluruhan katalog.'],
                ['label' => 'Unmapped products', 'value' => number_format(Product::query()->whereNull('category_id')->count(), 0, ',', '.'), 'note' => 'Produk yang belum masuk kategori akan menyulitkan assortment planning dan reporting.'],
            ],
            'categories' => $categories->map(function (Category $category): array {
                return [
                    'id' => $category->id,
                    'code' => $category->code,
                    'name' => $category->name,
                    'sort_order' => number_format((int) $category->sort_order, 0, ',', '.'),
                    'products_count' => number_format((int) $category->products_count, 0, ',', '.'),
                    'featured_count' => number_format($category->products->where('is_featured', true)->count(), 0, ',', '.'),
                    'status' => $category->is_active ? 'Active' : 'Inactive',
                    'description' => $category->description ?: 'Kategori siap dipakai untuk segmentasi retail.',
                    'edit_url' => route('categories.edit', $category),
                    'delete_url' => route('categories.destroy', $category),
                    'can_delete' => (int) $category->products_count === 0,
                ];
            })->all(),
            'insightCards' => [
                ['title' => 'Dashboard mix', 'value' => number_format(count($this->categoryMix($this->productsWithStock())), 0, ',', '.'), 'note' => 'Dashboard utama membaca nilai stok dan margin berdasarkan struktur kategori ini.'],
                ['title' => 'Open spend by category', 'value' => number_format(count($this->purchaseOrderSpendMix(PurchaseOrder::query()->with(['items.product.category'])->get())), 0, ',', '.'), 'note' => 'Belanja procurement bisa dibaca per kategori bila mapping produk tetap disiplin.'],
            ],
            'createUrl' => route('categories.create'),
        ]);
    }

    public function outletDirectoryData(): array
    {
        $page = $this->basePage('outlet');
        $salesDate = $this->latestSalesDate();
        $attendanceDate = $this->latestAttendanceDate();
        $monthStart = $salesDate->startOfMonth();

        $outlets = Outlet::query()
            ->with(['warehouse', 'employees', 'salesTransactions', 'attendanceLogs' => fn ($query) => $query->whereDate('shift_date', $attendanceDate->toDateString())])
            ->orderBy('name')
            ->get();

        $salesTransactions = SalesTransaction::query()
            ->where('status', 'paid')
            ->whereBetween('sold_at', [$monthStart->startOfDay(), $salesDate->endOfDay()])
            ->get();

        $splitPayments = $salesTransactions->where('split_payment_count', '>', 1)->count();
        $activeOutlets = $outlets->where('status', Outlet::STATUS_ACTIVE);
        $activeEmployees = Employee::query()->where('status', Employee::STATUS_ACTIVE)->count();

        return array_merge($page, [
            'generatedAt' => $this->timestamp(),
            'heroStats' => [
                ['label' => 'Outlet aktif', 'value' => number_format($activeOutlets->count(), 0, ',', '.'), 'caption' => 'Cabang retail yang saat ini masih melayani transaksi'],
                ['label' => 'Sales MTD', 'value' => $this->formatCompactCurrency($salesTransactions->sum('net_amount')), 'caption' => 'Omzet dari seluruh transaksi outlet pada periode berjalan'],
                ['label' => 'Split payment ratio', 'value' => $this->formatPercent($this->percent($splitPayments, max($salesTransactions->count(), 1))), 'caption' => 'Porsi transaksi yang memakai lebih dari satu metode pembayaran'],
                ['label' => 'Headcount aktif', 'value' => number_format($activeEmployees, 0, ',', '.'), 'caption' => 'Karyawan aktif yang sedang menopang operasi multi outlet'],
            ],
            'metrics' => [
                ['label' => 'Average service level', 'value' => $this->formatPercent($activeOutlets->avg('service_level')), 'note' => 'Mengukur konsistensi layanan outlet terhadap standar operasi retail'],
                ['label' => 'Inventory accuracy', 'value' => $this->formatPercent($activeOutlets->avg('inventory_accuracy')), 'note' => 'Membaca seberapa disiplin stok outlet terhadap data sistem dan hasil stock opname'],
                ['label' => 'Average basket', 'value' => $this->formatCompactCurrency($salesTransactions->avg('net_amount')), 'note' => 'Nilai transaksi rata-rata sebagai indikator kualitas selling dan product mix di outlet'],
                ['label' => 'Fulfillment hub', 'value' => number_format($outlets->where('is_fulfillment_hub', true)->count(), 0, ',', '.'), 'note' => 'Outlet yang juga berperan menangani fulfillment atau pickup order omnichannel'],
                ['label' => 'Outlet renovation', 'value' => number_format($outlets->where('status', Outlet::STATUS_RENOVATION)->count(), 0, ',', '.'), 'note' => 'Cabang yang kapasitas layanannya sedang turun karena renovasi atau pembatasan operasi'],
                ['label' => 'Attendance today', 'value' => $this->formatPercent($this->percent(AttendanceLog::query()->whereDate('shift_date', $attendanceDate->toDateString())->whereIn('attendance_status', ['present', 'late'])->count(), max(AttendanceLog::query()->whereDate('shift_date', $attendanceDate->toDateString())->count(), 1))), 'note' => 'Tingkat kehadiran tim outlet dan support pada tanggal operasi terakhir'],
            ],
            'outlets' => $outlets->map(function (Outlet $outlet) use ($monthStart, $salesDate): array {
                $transactions = $outlet->salesTransactions
                    ->where('status', 'paid')
                    ->filter(fn (SalesTransaction $transaction): bool => $transaction->sold_at !== null && $transaction->sold_at->between($monthStart, $salesDate->endOfDay()));
                $splitRatio = $this->percent($transactions->where('split_payment_count', '>', 1)->count(), max($transactions->count(), 1));

                return [
                    'code' => $outlet->code,
                    'name' => $outlet->name,
                    'region' => $outlet->region ?? '-',
                    'manager' => $outlet->manager_name ?? '-',
                    'city' => $outlet->city,
                    'sales_target' => $this->formatCompactCurrency((float) $outlet->daily_sales_target),
                    'sales_mtd' => $this->formatCompactCurrency($transactions->sum('net_amount')),
                    'split_ratio' => $this->formatPercent($splitRatio),
                    'headcount' => number_format($outlet->employees->where('status', Employee::STATUS_ACTIVE)->count(), 0, ',', '.') . ' aktif',
                    'service_level' => $this->formatPercent($outlet->service_level),
                    'status' => $this->outletStatus($outlet),
                    'edit_url' => route('outlets.edit', $outlet),
                ];
            })->all(),
            'performanceCards' => [
                ['title' => 'Top outlet sales', 'value' => $outlets->mapWithKeys(fn (Outlet $outlet): array => [$outlet->name => $outlet->salesTransactions->where('status', 'paid')->sum('net_amount')])->sortDesc()->keys()->first() ?? '-', 'note' => 'Outlet dengan kontribusi omzet tertinggi pada data transaksi berjalan'],
                ['title' => 'Sales vs target', 'value' => $this->formatPercent($this->percent($salesTransactions->sum('net_amount'), max($activeOutlets->sum('daily_sales_target') * max($salesDate->day, 1), 1))), 'note' => 'Perbandingan penjualan aktual terhadap target harian outlet secara akumulatif'],
                ['title' => 'Outlet linked warehouse', 'value' => number_format($outlets->whereNotNull('warehouse_id')->count(), 0, ',', '.'), 'note' => 'Cabang yang sudah terhubung ke lokasi stok fisik untuk sinkronisasi replenishment'],
            ],
            'createUrl' => route('outlets.create'),
        ]);
    }

    public function outletFormData(?Outlet $outlet = null): array
    {
        return [
            'title' => $outlet?->exists ? 'Edit Outlet' : 'Tambah Outlet',
            'pageTitle' => $outlet?->exists ? 'Edit Outlet' : 'Tambah Outlet',
            'pageEyebrow' => 'Multi Outlet',
            'pageDescription' => $outlet?->exists
                ? 'Perbarui target, service level, dan data operasional outlet agar kontrol cabang tetap presisi.'
                : 'Tambahkan outlet baru lengkap dengan target penjualan, mapping gudang, dan profil operasional cabang.',
            'outlet' => $outlet,
            'warehouses' => Warehouse::query()->where('is_active', true)->orderBy('name')->get(),
            'statusOptions' => Outlet::statusOptions(),
            'regionOptions' => ['West', 'Central', 'East'],
            'submitUrl' => $outlet?->exists ? route('outlets.update', $outlet) : route('outlets.store'),
            'submitMethod' => $outlet?->exists ? 'PUT' : 'POST',
            'backUrl' => route('outlet'),
            'generatedAt' => $this->timestamp(),
        ];
    }

    public function employeeManagementData(): array
    {
        $page = $this->basePage('employee-management');
        $attendanceDate = $this->latestAttendanceDate();
        $employees = Employee::query()->with(['outlet', 'location'])->orderBy('full_name')->get();
        $attendanceLogs = AttendanceLog::query()->whereDate('shift_date', $attendanceDate->toDateString())->get();
        $latestPayroll = PayrollRun::query()->with('items')->orderByDesc('period_end')->first();
        $activeEmployees = $employees->where('status', Employee::STATUS_ACTIVE);
        $warehouseMapped = $activeEmployees->filter(fn (Employee $employee): bool => $employee->location?->type === Location::TYPE_WAREHOUSE);
        $outletMapped = $activeEmployees->filter(fn (Employee $employee): bool => $employee->location?->type === Location::TYPE_OUTLET);

        return array_merge($page, [
            'generatedAt' => $this->timestamp(),
            'heroStats' => [
                ['label' => 'Karyawan aktif', 'value' => number_format($activeEmployees->count(), 0, ',', '.'), 'caption' => 'Headcount aktif lintas outlet, gudang, dan support function'],
                ['label' => 'Payroll exposure', 'value' => $this->formatCompactCurrency($latestPayroll?->total_net), 'caption' => 'Nilai take home pay dari payroll run terbaru'],
                ['label' => 'Attendance rate', 'value' => $this->formatPercent($this->percent($attendanceLogs->whereIn('attendance_status', ['present', 'late'])->count(), max($attendanceLogs->count(), 1))), 'caption' => 'Kehadiran pada hari operasional terakhir'],
                ['label' => 'Location staffed', 'value' => number_format($activeEmployees->whereNotNull('location_id')->groupBy('location_id')->count(), 0, ',', '.'), 'caption' => 'Jumlah outlet/gudang yang sudah memiliki tim aktif terdistribusi'],
            ],
            'metrics' => [
                ['label' => 'Permanent mix', 'value' => $this->formatPercent($this->percent($employees->where('employment_type', 'permanent')->count(), max($employees->count(), 1))), 'note' => 'Komposisi tenaga kerja tetap sebagai penopang stabilitas operasional'],
                ['label' => 'Average base salary', 'value' => $this->formatCompactCurrency($activeEmployees->avg('base_salary')), 'note' => 'Rata-rata gaji pokok karyawan aktif untuk membaca struktur biaya tenaga kerja'],
                ['label' => 'On leave', 'value' => number_format($employees->where('status', Employee::STATUS_LEAVE)->count(), 0, ',', '.'), 'note' => 'Karyawan yang sedang cuti atau tidak tersedia secara operasional'],
                ['label' => 'Overtime today', 'value' => number_format((float) $attendanceLogs->sum('overtime_minutes'), 0, ',', '.') . ' menit', 'note' => 'Lembur pada hari operasional terakhir yang berpengaruh ke payroll dan service capacity'],
                ['label' => 'Outlet mapped', 'value' => number_format($outletMapped->count(), 0, ',', '.'), 'note' => 'Karyawan aktif yang dipetakan ke lokasi outlet untuk operasional POS.'],
                ['label' => 'Warehouse mapped', 'value' => number_format($warehouseMapped->count(), 0, ',', '.'), 'note' => 'Karyawan aktif yang dipetakan ke lokasi gudang untuk inbound, stok, dan transfer.'],
            ],
            'employees' => $employees->map(function (Employee $employee): array {
                return [
                    'employee_code' => $employee->employee_code,
                    'full_name' => $employee->full_name,
                    'department' => $employee->department,
                    'position' => $employee->position_title,
                    'outlet' => $employee->outlet?->name ?? 'Head Office',
                    'location' => $employee->location?->name ?? 'Belum dipetakan',
                    'location_type' => $employee->location?->type === Location::TYPE_WAREHOUSE ? 'Gudang' : ($employee->location?->type === Location::TYPE_OUTLET ? 'Outlet' : 'Head Office'),
                    'employment_type' => Employee::employmentTypeOptions()[$employee->employment_type] ?? ucfirst(str_replace('_', ' ', $employee->employment_type)),
                    'base_salary' => $this->formatCompactCurrency((float) $employee->base_salary),
                    'sales_bonus_rate' => $this->formatPercent((float) $employee->sales_bonus_rate),
                    'status' => $this->employeeStatus($employee),
                    'edit_url' => route('employees.edit', $employee),
                ];
            })->all(),
            'departmentCards' => $employees
                ->groupBy('department')
                ->map(function (Collection $group, string $department): array {
                    return [
                        'title' => $department,
                        'value' => number_format($group->where('status', Employee::STATUS_ACTIVE)->count(), 0, ',', '.') . ' aktif',
                        'note' => 'Base salary ' . $this->formatCompactCurrency($group->where('status', Employee::STATUS_ACTIVE)->sum('base_salary')),
                    ];
                })
                ->values()
                ->all(),
            'createUrl' => route('employees.create'),
        ]);
    }

    public function employeeFormData(?Employee $employee = null): array
    {
        return [
            'title' => $employee?->exists ? 'Edit Karyawan' : 'Tambah Karyawan',
            'pageTitle' => $employee?->exists ? 'Edit Karyawan' : 'Tambah Karyawan',
            'pageEyebrow' => 'HR Module',
            'pageDescription' => $employee?->exists
                ? 'Perbarui profil kerja, assignment location outlet/gudang, dan policy payroll karyawan.'
                : 'Tambahkan karyawan baru lengkap dengan mapping location, department, jabatan, dan policy payroll.',
            'employee' => $employee,
            'locations' => Location::query()
                ->where('status', 'active')
                ->orderBy('type')
                ->orderBy('name')
                ->get(),
            'outlets' => Outlet::query()->orderBy('name')->get(),
            'statusOptions' => Employee::statusOptions(),
            'employmentTypeOptions' => Employee::employmentTypeOptions(),
            'departmentOptions' => ['Retail Operations', 'People Operations', 'Finance', 'Warehouse Ops'],
            'submitUrl' => $employee?->exists ? route('employees.update', $employee) : route('employees.store'),
            'submitMethod' => $employee?->exists ? 'PUT' : 'POST',
            'backUrl' => route('employee-management'),
            'generatedAt' => $this->timestamp(),
        ];
    }

    public function attendanceLogData(): array
    {
        $page = $this->basePage('attendance-log');
        $attendanceDate = $this->latestAttendanceDate();
        $logs = AttendanceLog::query()
            ->with(['employee.outlet', 'employee.location', 'outlet', 'location'])
            ->whereDate('shift_date', $attendanceDate->toDateString())
            ->orderBy('attendance_status')
            ->orderBy('scheduled_start')
            ->get();

        return array_merge($page, [
            'generatedAt' => $this->timestamp(),
            'heroStats' => [
                ['label' => 'Scheduled staff', 'value' => number_format($logs->count(), 0, ',', '.'), 'caption' => 'Roster kerja pada tanggal operasi terakhir'],
                ['label' => 'Present', 'value' => number_format($logs->where('attendance_status', 'present')->count(), 0, ',', '.'), 'caption' => 'Karyawan hadir tepat waktu'],
                ['label' => 'Late', 'value' => number_format($logs->where('attendance_status', 'late')->count(), 0, ',', '.'), 'caption' => 'Tim yang hadir tetapi melampaui toleransi keterlambatan'],
                ['label' => 'Overtime', 'value' => number_format((float) $logs->sum('overtime_minutes'), 0, ',', '.') . ' menit', 'caption' => 'Akumulasi lembur yang perlu masuk ke payroll'],
            ],
            'metrics' => [
                ['label' => 'Attendance rate', 'value' => $this->formatPercent($this->percent($logs->whereIn('attendance_status', ['present', 'late'])->count(), max($logs->count(), 1))), 'note' => 'Indikator kesiapan manpower terhadap kebutuhan layanan dan shift outlet'],
                ['label' => 'Late minutes', 'value' => number_format((float) $logs->sum('late_minutes'), 0, ',', '.') . ' menit', 'note' => 'Total keterlambatan sebagai sinyal disiplin tim dan risiko service gap'],
                ['label' => 'Leave ratio', 'value' => $this->formatPercent($this->percent($logs->where('attendance_status', 'leave')->count(), max($logs->count(), 1))), 'note' => 'Porsi roster yang tidak aktif karena cuti atau izin terencana'],
                ['label' => 'Location impacted', 'value' => number_format($logs->whereIn('attendance_status', ['late', 'leave', 'absent'])->groupBy('location_id')->count(), 0, ',', '.'), 'note' => 'Outlet atau gudang yang mengalami gangguan karena disiplin atau availability tim'],
            ],
            'logs' => $logs->map(function (AttendanceLog $log): array {
                return [
                    'employee' => $log->employee?->full_name ?? '-',
                    'outlet' => $log->location?->name ?? $log->outlet?->name ?? $log->employee?->location?->name ?? 'Head Office',
                    'shift' => $log->shift_name,
                    'schedule' => ($log->scheduled_start?->format('H:i') ?? '-') . ' - ' . ($log->scheduled_end?->format('H:i') ?? '-'),
                    'clock' => ($log->clock_in_at?->format('H:i') ?? '-') . ' / ' . ($log->clock_out_at?->format('H:i') ?? '-'),
                    'late_minutes' => number_format((float) $log->late_minutes, 0, ',', '.') . ' menit',
                    'overtime_minutes' => number_format((float) $log->overtime_minutes, 0, ',', '.') . ' menit',
                    'status' => ucfirst($log->attendance_status),
                ];
            })->all(),
            'actionCards' => [
                ['title' => 'Tindak lanjuti keterlambatan', 'value' => number_format($logs->where('attendance_status', 'late')->count(), 0, ',', '.'), 'note' => 'Supervisor outlet perlu follow-up kehadiran yang memengaruhi service floor'],
                ['title' => 'Roster kosong', 'value' => number_format($logs->whereIn('attendance_status', ['leave', 'absent'])->count(), 0, ',', '.'), 'note' => 'Perlu redistribusi shift agar beban frontliner tidak melonjak'],
                ['title' => 'Lembur payroll', 'value' => $this->formatCompactCurrency($logs->sum(fn (AttendanceLog $log): float => ($log->employee?->overtime_rate ?? 0) * ((float) $log->overtime_minutes / 60))), 'note' => 'Estimasi eksposur lembur yang akan masuk ke payroll run berikutnya'],
            ],
        ]);
    }

    public function shiftAttendanceData(): array
    {
        $page = $this->basePage('shift-attendance');
        $focusDate = EmployeeShiftAssignment::query()->max('shift_date');
        $focusDate = $focusDate
            ? CarbonImmutable::parse($focusDate)
            : CarbonImmutable::now('Asia/Jakarta');

        $assignments = EmployeeShiftAssignment::query()
            ->with(['employee.location', 'employee.outlet', 'shift', 'location'])
            ->whereDate('shift_date', $focusDate->toDateString())
            ->orderBy('scheduled_start')
            ->get();
        $locations = Location::query()
            ->where('status', 'active')
            ->orderBy('type')
            ->orderBy('name')
            ->get();
        $employees = Employee::query()
            ->with('location')
            ->where('status', Employee::STATUS_ACTIVE)
            ->orderBy('full_name')
            ->get();
        $shifts = Shift::query()
            ->with('location')
            ->where('is_active', true)
            ->orderBy('start_time')
            ->orderBy('name')
            ->get();

        return array_merge($page, [
            'generatedAt' => $this->timestamp(),
            'focusDate' => $focusDate->format('Y-m-d'),
            'heroStats' => [
                ['label' => 'Scheduled', 'value' => number_format($assignments->count(), 0, ',', '.'), 'caption' => 'Total roster shift pada tanggal fokus'],
                ['label' => 'Checked In', 'value' => number_format($assignments->where('workflow_status', EmployeeShiftAssignment::WORKFLOW_CHECKED_IN)->count(), 0, ',', '.'), 'caption' => 'Karyawan yang sudah clock in namun belum clock out'],
                ['label' => 'Checked Out', 'value' => number_format($assignments->where('workflow_status', EmployeeShiftAssignment::WORKFLOW_CHECKED_OUT)->count(), 0, ',', '.'), 'caption' => 'Shift yang sudah selesai clock out'],
                ['label' => 'Absent', 'value' => number_format($assignments->where('attendance_status', EmployeeShiftAssignment::ATTENDANCE_ABSENT)->count(), 0, ',', '.'), 'caption' => 'Shift yang ditandai absen dan memengaruhi payroll'],
            ],
            'metrics' => [
                ['label' => 'Late minutes', 'value' => number_format((float) $assignments->sum('late_minutes'), 0, ',', '.') . ' menit', 'note' => 'Akumulasi keterlambatan roster retail pada tanggal operasi ini.'],
                ['label' => 'Overtime minutes', 'value' => number_format((float) $assignments->sum('overtime_minutes'), 0, ',', '.') . ' menit', 'note' => 'Lembur aktual yang akan masuk ke perhitungan payroll otomatis.'],
                ['label' => 'Coverage rate', 'value' => $this->formatPercent($this->percent($assignments->whereIn('attendance_status', [EmployeeShiftAssignment::ATTENDANCE_PRESENT, EmployeeShiftAssignment::ATTENDANCE_LATE])->count(), max($assignments->count(), 1))), 'note' => 'Proporsi shift yang ter-cover oleh kehadiran tim.'],
                ['label' => 'Warehouse shifts', 'value' => number_format($assignments->filter(fn (EmployeeShiftAssignment $assignment): bool => $assignment->location?->type === Location::TYPE_WAREHOUSE)->count(), 0, ',', '.'), 'note' => 'Jumlah assignment shift yang dipetakan ke lokasi gudang.'],
            ],
            'assignments' => $assignments->map(function (EmployeeShiftAssignment $assignment): array {
                $canClockIn = in_array($assignment->workflow_status, [EmployeeShiftAssignment::WORKFLOW_SCHEDULED, EmployeeShiftAssignment::WORKFLOW_CHECKED_IN], true);
                $canClockOut = $assignment->clock_in_at !== null && ! in_array($assignment->workflow_status, [EmployeeShiftAssignment::WORKFLOW_CHECKED_OUT, EmployeeShiftAssignment::WORKFLOW_CANCELLED, EmployeeShiftAssignment::WORKFLOW_CLOSED], true);
                $canMarkAbsent = in_array($assignment->workflow_status, [EmployeeShiftAssignment::WORKFLOW_SCHEDULED, EmployeeShiftAssignment::WORKFLOW_CHECKED_IN], true);

                return [
                    'id' => $assignment->id,
                    'employee' => $assignment->employee?->full_name ?? '-',
                    'location' => $assignment->location?->name ?? $assignment->employee?->location?->name ?? $assignment->employee?->outlet?->name ?? 'Head Office',
                    'location_type' => $assignment->location?->type === Location::TYPE_WAREHOUSE ? 'Gudang' : ($assignment->location?->type === Location::TYPE_OUTLET ? 'Outlet' : '-'),
                    'shift' => $assignment->shift?->name ?? '-',
                    'schedule' => ($assignment->scheduled_start?->format('H:i') ?? '-') . ' - ' . ($assignment->scheduled_end?->format('H:i') ?? '-'),
                    'clock' => ($assignment->clock_in_at?->format('H:i') ?? '-') . ' / ' . ($assignment->clock_out_at?->format('H:i') ?? '-'),
                    'workflow_status' => str_replace('_', ' ', ucfirst($assignment->workflow_status)),
                    'attendance_status' => ucfirst($assignment->attendance_status),
                    'late_minutes' => number_format((float) $assignment->late_minutes, 0, ',', '.') . ' menit',
                    'overtime_minutes' => number_format((float) $assignment->overtime_minutes, 0, ',', '.') . ' menit',
                    'can_clock_in' => $canClockIn,
                    'can_clock_out' => $canClockOut,
                    'can_mark_absent' => $canMarkAbsent,
                    'clock_in_url' => route('shift-attendance.clock-in', $assignment),
                    'clock_out_url' => route('shift-attendance.clock-out', $assignment),
                    'mark_absent_url' => route('shift-attendance.mark-absent', $assignment),
                ];
            })->values()->all(),
            'employees' => $employees->map(fn (Employee $employee): array => [
                'id' => $employee->id,
                'full_name' => $employee->full_name,
                'employee_code' => $employee->employee_code,
                'location_name' => $employee->location?->name ?? $employee->outlet?->name ?? 'Head Office',
            ])->all(),
            'shifts' => $shifts->map(fn (Shift $shift): array => [
                'id' => $shift->id,
                'name' => $shift->name,
                'code' => $shift->code,
                'window' => substr((string) $shift->start_time, 0, 5) . ' - ' . substr((string) $shift->end_time, 0, 5),
                'location_name' => $shift->location?->name,
            ])->all(),
            'locations' => $locations->map(fn (Location $location): array => [
                'id' => $location->id,
                'name' => $location->name,
                'type' => $location->type === Location::TYPE_WAREHOUSE ? 'Gudang' : 'Outlet',
            ])->all(),
            'assignUrl' => route('shift-attendance.assign'),
        ]);
    }

    public function payrollListData(): array
    {
        $page = $this->basePage('payroll-list');
        $payrollRuns = PayrollRun::query()
            ->with(['items.employee.outlet', 'items.employee.location'])
            ->orderByDesc('period_end')
            ->get();

        $currentRun = $payrollRuns->first();
        $currentItems = $currentRun?->items ?? collect();

        return array_merge($page, [
            'generatedAt' => $this->timestamp(),
            'heroStats' => [
                ['label' => 'Payroll run terbaru', 'value' => $currentRun?->code ?? '-', 'caption' => 'Dokumen payroll yang saat ini menjadi fokus finance dan HR'],
                ['label' => 'Total net salary', 'value' => $this->formatCompactCurrency($currentRun?->total_net), 'caption' => 'Nilai take home pay dari payroll terbaru'],
                ['label' => 'Employee covered', 'value' => number_format((int) ($currentRun?->employee_count ?? 0), 0, ',', '.'), 'caption' => 'Jumlah karyawan yang sudah masuk proses penggajian'],
                ['label' => 'Pending payment', 'value' => number_format($currentItems->where('payment_status', '!=', 'paid')->count(), 0, ',', '.'), 'caption' => 'Karyawan yang gajinya belum ditandai paid'],
            ],
            'metrics' => [
                ['label' => 'Gross salary', 'value' => $this->formatCompactCurrency($currentRun?->total_gross), 'note' => 'Gaji pokok, bonus sales POS, allowance, dan overtime sebelum deduction'],
                ['label' => 'Total deductions', 'value' => $this->formatCompactCurrency($currentRun?->total_deductions), 'note' => 'Potongan payroll yang perlu tercermin juga di laporan keuangan'],
                ['label' => 'Average take home', 'value' => $this->formatCompactCurrency($currentItems->avg('net_salary')), 'note' => 'Rata-rata take home pay untuk membaca struktur biaya tenaga kerja saat ini'],
                ['label' => 'Payroll readiness', 'value' => $this->formatPercent($this->percent($payrollRuns->whereIn('status', [PayrollRun::STATUS_APPROVED, PayrollRun::STATUS_PAID])->count(), max($payrollRuns->count(), 1))), 'note' => 'Porsi payroll run yang sudah lolos approval atau selesai dibayar'],
            ],
            'payrollRuns' => $payrollRuns->map(function (PayrollRun $payrollRun): array {
                return [
                    'id' => $payrollRun->id,
                    'code' => $payrollRun->code,
                    'period' => $payrollRun->period_start?->format('d M') . ' - ' . $payrollRun->period_end?->format('d M Y'),
                    'status' => PayrollRun::statusOptions()[$payrollRun->status] ?? ucfirst($payrollRun->status),
                    'employee_count' => number_format((int) $payrollRun->employee_count, 0, ',', '.'),
                    'gross' => $this->formatCompactCurrency((float) $payrollRun->total_gross),
                    'deductions' => $this->formatCompactCurrency((float) $payrollRun->total_deductions),
                    'net' => $this->formatCompactCurrency((float) $payrollRun->total_net),
                    'can_submit' => $payrollRun->status === PayrollRun::STATUS_DRAFT,
                    'can_approve' => in_array($payrollRun->status, [PayrollRun::STATUS_DRAFT, PayrollRun::STATUS_PROCESSING], true),
                    'can_pay' => $payrollRun->status === PayrollRun::STATUS_APPROVED,
                    'submit_url' => route('payroll-runs.submit', $payrollRun),
                    'approve_url' => route('payroll-runs.approve', $payrollRun),
                    'pay_url' => route('payroll-runs.pay', $payrollRun),
                ];
            })->all(),
            'locations' => Location::query()
                ->where('status', 'active')
                ->orderBy('type')
                ->orderBy('name')
                ->get()
                ->map(fn (Location $location): array => [
                    'id' => $location->id,
                    'name' => $location->name,
                    'type' => $location->type === Location::TYPE_WAREHOUSE ? 'Gudang' : 'Outlet',
                ])
                ->all(),
            'defaultPeriodStart' => CarbonImmutable::now('Asia/Jakarta')->startOfMonth()->format('Y-m-d'),
            'defaultPeriodEnd' => CarbonImmutable::now('Asia/Jakarta')->endOfMonth()->format('Y-m-d'),
            'generateUrl' => route('payroll-runs.generate'),
            'topEarners' => $currentItems
                ->sortByDesc('net_salary')
                ->take(5)
                ->map(function ($item): array {
                    return [
                        'employee' => $item->employee?->full_name ?? '-',
                        'outlet' => $item->employee?->location?->name ?? $item->employee?->outlet?->name ?? 'Head Office',
                        'position' => $item->employee?->position_title ?? '-',
                        'net_salary' => $this->formatCompactCurrency((float) $item->net_salary),
                        'sales_bonus' => $this->formatCompactCurrency((float) $item->sales_bonus_amount),
                        'attendance_deduction' => $this->formatCompactCurrency((float) $item->attendance_deduction_amount),
                        'status' => ucfirst($item->payment_status),
                    ];
                })->values()->all(),
        ]);
    }

    public function splitPaymentData(): array
    {
        return $this->analyticsCacheService->rememberSplitPayment(function (): array {
            $page = $this->basePage('split-payment');
            $transactions = SalesTransaction::query()
                ->with(['outlet', 'payments.paymentMethod'])
                ->where('status', 'paid')
                ->orderByDesc('sold_at')
                ->get();

            $payments = $transactions->flatMap(fn (SalesTransaction $transaction) => $transaction->payments);
            $splitTransactions = $transactions->where('split_payment_count', '>', 1);
            $gatewayFee = $payments->sum(function ($payment): float {
                $feeRate = (float) ($payment->paymentMethod?->transaction_fee_rate ?? 0);

                return ((float) $payment->amount * $feeRate) / 100;
            });

            $paymentMethods = PaymentMethod::query()
                ->with(['salesPayments.salesTransaction'])
                ->where('is_active', true)
                ->orderBy('name')
                ->get();

            return array_merge($page, [
                'generatedAt' => $this->timestamp(),
                'heroStats' => [
                    ['label' => 'Paid transactions', 'value' => number_format($transactions->count(), 0, ',', '.'), 'caption' => 'Transaksi POS yang sudah dibayar pada dataset saat ini'],
                    ['label' => 'Split payment', 'value' => $this->formatPercent($this->percent($splitTransactions->count(), max($transactions->count(), 1))), 'caption' => 'Transaksi yang dibayar dengan dua metode atau lebih'],
                    ['label' => 'Net sales', 'value' => $this->formatCompactCurrency($transactions->sum('net_amount')), 'caption' => 'Omzet dari transaksi yang sudah dibayar'],
                    ['label' => 'Gateway fee', 'value' => $this->formatCompactCurrency($gatewayFee), 'caption' => 'Estimasi biaya payment gateway dan MDR dari komposisi pembayaran'],
                ],
                'metrics' => [
                    ['label' => 'Average ticket', 'value' => $this->formatCompactCurrency($transactions->avg('net_amount')), 'note' => 'Nilai transaksi rata-rata untuk memotret basket quality dan payment behavior'],
                    ['label' => 'Active methods', 'value' => number_format($paymentMethods->count(), 0, ',', '.'), 'note' => 'Metode pembayaran yang siap dipakai lintas outlet dan skenario checkout'],
                    ['label' => 'Cash share', 'value' => $this->formatPercent($this->percent($payments->filter(fn ($payment): bool => $payment->paymentMethod?->category === 'cash')->sum('amount'), max($payments->sum('amount'), 1))), 'note' => 'Porsi kas sebagai indikator settlement instan tetapi berisiko lebih tinggi di cash handling'],
                    ['label' => 'Digital share', 'value' => $this->formatPercent($this->percent($payments->filter(fn ($payment): bool => in_array($payment->paymentMethod?->category, ['qris', 'card', 'ewallet', 'bank_transfer'], true))->sum('amount'), max($payments->sum('amount'), 1))), 'note' => 'Porsi kanal digital yang relevan untuk fee, settlement, dan rekonsiliasi keuangan'],
                ],
                'paymentMethods' => $paymentMethods->map(function (PaymentMethod $method) use ($payments): array {
                    $methodPayments = $payments->filter(fn ($payment): bool => $payment->payment_method_id === $method->id);
                    $totalAmount = $methodPayments->sum('amount');

                    return [
                        'name' => $method->name,
                        'provider' => $method->provider ?? 'Internal',
                        'category' => ucfirst(str_replace('_', ' ', $method->category)),
                        'transactions' => number_format($methodPayments->count(), 0, ',', '.'),
                        'share' => $this->formatPercent($this->percent($totalAmount, max($payments->sum('amount'), 1))),
                        'amount' => $this->formatCompactCurrency($totalAmount),
                        'fee' => $this->formatPercent($method->transaction_fee_rate),
                    ];
                })->all(),
                'transactions' => $transactions->take(8)->map(function (SalesTransaction $transaction): array {
                    return [
                        'transaction_number' => $transaction->transaction_number,
                        'outlet' => $transaction->outlet?->name ?? '-',
                        'sold_at' => $transaction->sold_at?->format('d M Y H:i') ?? '-',
                        'amount' => $this->formatCompactCurrency((float) $transaction->net_amount),
                        'split_count' => $transaction->split_payment_count,
                        'payments' => $transaction->payments->map(fn ($payment): string => ($payment->paymentMethod?->name ?? '-') . ' ' . $this->formatCompactCurrency((float) $payment->amount))->join(' + '),
                    ];
                })->all(),
            ]);
        });
    }

    public function financialReportData(): array
    {
        return $this->analyticsCacheService->rememberFinancialReport(function (): array {
            $page = $this->basePage('financial-report');
            $periodStart = CarbonImmutable::now('Asia/Jakarta')->startOfMonth();
            $periodEnd = CarbonImmutable::now('Asia/Jakarta')->endOfDay();
            $profitLoss = $this->financialStatementAggregateService->profitLossSummary($periodStart, $periodEnd);
            $balanceSheet = $this->financialStatementAggregateService->balanceSheetSummary($periodEnd);
            $latestPayroll = PayrollRun::query()->orderByDesc('period_end')->first();
            $journalEntriesCount = AccountingJournalEntry::query()
                ->whereBetween('entry_date', [$periodStart->toDateString(), $periodEnd->toDateString()])
                ->count();
            $inventoryFromOps = $this->productsWithStock()->sum(fn (Product $product): float => $this->stockValue($product));
            $inventoryBalance = abs((float) $balanceSheet['inventory_balance']) > 0.01
                ? (float) $balanceSheet['inventory_balance']
                : $inventoryFromOps;
            $workingCapitalProxy = (float) $balanceSheet['cash_balance']
                + (float) $balanceSheet['accounts_receivable_balance']
                + $inventoryBalance
                - ((float) $balanceSheet['accounts_payable_balance'] + (float) $balanceSheet['payroll_payable_balance']);

            return array_merge($page, $this->connectedBoardData([
                'heroStats' => [
                    ['label' => 'Revenue MTD', 'value' => $this->formatCompactCurrency($profitLoss['revenue_amount']), 'caption' => 'Pendapatan bulan berjalan dari aggregate table laba rugi.'],
                    ['label' => 'Net profit MTD', 'value' => $this->formatCompactCurrency($profitLoss['net_profit_amount']), 'caption' => 'Laba bersih periode berjalan dari jurnal otomatis POS dan payroll.'],
                    ['label' => 'Total assets', 'value' => $this->formatCompactCurrency((float) $balanceSheet['total_assets']), 'caption' => 'Aset dari aggregate table neraca (kas, piutang, persediaan).'],
                    ['label' => 'Total liabilities', 'value' => $this->formatCompactCurrency((float) $balanceSheet['total_liabilities']), 'caption' => 'Liabilitas dari aggregate table neraca (hutang usaha dan hutang gaji).'],
                ],
                'metrics' => [
                    ['label' => 'Gross profit MTD', 'value' => $this->formatCompactCurrency((float) $profitLoss['gross_profit_amount']), 'note' => 'Laba kotor hasil agregasi jurnal pendapatan dan HPP.'],
                    ['label' => 'Payroll expense MTD', 'value' => $this->formatCompactCurrency((float) $profitLoss['payroll_expense_amount']), 'note' => 'Beban payroll dari jurnal akrual payroll run yang telah approved.'],
                    ['label' => 'Working capital proxy', 'value' => $this->formatCompactCurrency($workingCapitalProxy), 'note' => 'Kas + piutang + persediaan - (hutang usaha + hutang gaji).'],
                    ['label' => 'Journal entries MTD', 'value' => number_format($journalEntriesCount, 0, ',', '.'), 'note' => 'Jumlah jurnal otomatis yang diproses untuk laporan akuntansi bulan ini.'],
                ],
                'actions' => [
                    ['label' => 'Export Excel (Chunk)', 'url' => route('financial-report.export', ['format' => 'excel', 'start_date' => $periodStart->toDateString(), 'end_date' => $periodEnd->toDateString()])],
                    ['label' => 'Export PDF (Chunk)', 'url' => route('financial-report.export', ['format' => 'pdf', 'start_date' => $periodStart->toDateString(), 'end_date' => $periodEnd->toDateString()]), 'variant' => 'secondary'],
                ],
                'mainTitle' => 'Laba Rugi & Neraca Real-Time',
                'mainDescription' => 'Panel ini membaca aggregate tables laba rugi dan neraca yang diupdate otomatis saat jurnal POS/payroll diposting. Query tetap cepat walau volume transaksi besar.',
                'tableColumns' => ['Akun', 'Nilai', 'Konteks'],
                'tableRows' => [
                    ['Akun' => 'Pendapatan Penjualan', 'Nilai' => $this->formatCompactCurrency((float) $profitLoss['revenue_amount']), 'Konteks' => 'Aggregate akun 4xxx dari jurnal otomatis POS.'],
                    ['Akun' => 'Harga Pokok Penjualan', 'Nilai' => $this->formatCompactCurrency((float) $profitLoss['cogs_amount']), 'Konteks' => 'Aggregate akun 5101 dari jurnal COGS POS.'],
                    ['Akun' => 'Beban Payroll', 'Nilai' => $this->formatCompactCurrency((float) $profitLoss['payroll_expense_amount']), 'Konteks' => 'Aggregate akun 5201 dari jurnal payroll accrual.'],
                    ['Akun' => 'Laba Bersih', 'Nilai' => $this->formatCompactCurrency((float) $profitLoss['net_profit_amount']), 'Konteks' => 'Akumulasi net profit periode laporan dari aggregate P/L.'],
                    ['Akun' => 'Kas & Bank', 'Nilai' => $this->formatCompactCurrency((float) $balanceSheet['cash_balance']), 'Konteks' => 'Saldo kas berbasis aggregate pergerakan akun 1101/1102.'],
                    ['Akun' => 'Piutang Usaha', 'Nilai' => $this->formatCompactCurrency((float) $balanceSheet['accounts_receivable_balance']), 'Konteks' => 'Saldo piutang dari aggregate akun 1103/1104.'],
                    ['Akun' => 'Persediaan', 'Nilai' => $this->formatCompactCurrency($inventoryBalance), 'Konteks' => 'Neraca persediaan dari aggregate akun 12xx (fallback ke valuasi operasional jika kosong).'],
                    ['Akun' => 'Hutang Usaha', 'Nilai' => $this->formatCompactCurrency((float) $balanceSheet['accounts_payable_balance']), 'Konteks' => 'Saldo liabilitas vendor dari aggregate akun 2101.'],
                    ['Akun' => 'Hutang Gaji', 'Nilai' => $this->formatCompactCurrency((float) $balanceSheet['payroll_payable_balance']), 'Konteks' => 'Saldo kewajiban payroll dari aggregate akun 2102.'],
                    ['Akun' => 'Total Aset', 'Nilai' => $this->formatCompactCurrency((float) $balanceSheet['total_assets']), 'Konteks' => 'Kas + Piutang + Persediaan.'],
                    ['Akun' => 'Total Liabilitas + Ekuitas', 'Nilai' => $this->formatCompactCurrency((float) $balanceSheet['total_liabilities'] + (float) $balanceSheet['total_equity']), 'Konteks' => 'Konsolidasi neraca dari aggregate table balance sheet.'],
                ],
                'sideTitle' => 'Finance Guardrail',
                'sideCards' => [
                    ['title' => 'Balance Delta', 'value' => $this->formatCompactCurrency((float) $balanceSheet['balance_delta']), 'note' => 'Selisih Aset terhadap (Liabilitas + Ekuitas). Idealnya mendekati nol.'],
                    ['title' => 'Retained Earnings', 'value' => $this->formatCompactCurrency((float) $balanceSheet['retained_earnings_balance']), 'note' => 'Akumulasi laba ditahan dari agregasi net profit jurnal.'],
                    ['title' => 'Latest Payroll', 'value' => $this->formatCompactCurrency($latestPayroll?->total_net), 'note' => 'Referensi payroll run terbaru: ' . ($latestPayroll?->code ?? '-')],
                ],
            ]));
        });
    }

    public function cashflowData(): array
    {
        return $this->analyticsCacheService->rememberCashflow(function (): array {
            $page = $this->basePage('cashflow');
            $salesPayments = SalesTransaction::query()
                ->with(['payments.paymentMethod', 'payments.salesTransaction'])
                ->get()
                ->flatMap(fn (SalesTransaction $transaction): Collection => $transaction->payments);
            $supplierPayments = PurchaseOrderPayment::query()->with(['paymentMethod', 'purchaseOrder.supplier'])->orderByDesc('payment_date')->get();
            $payrollRuns = PayrollRun::query()->where('status', PayrollRun::STATUS_PAID)->orderByDesc('period_end')->get();

            $cashIn = (float) $salesPayments->sum('amount');
            $cashOut = (float) $supplierPayments->sum('amount') + (float) $payrollRuns->sum('total_net');

            $flowRows = collect();

            foreach ($salesPayments as $payment) {
                $flowRows->push([
                    'tanggal' => $payment->settled_at?->format('Y-m-d') ?? now()->format('Y-m-d'),
                    'arus' => 'Cash In',
                    'nilai' => (float) $payment->amount,
                    'context' => 'Sales collection ' . ($payment->salesTransaction?->invoice_number ?? $payment->salesTransaction?->transaction_number ?? '-'),
                ]);
            }

            foreach ($supplierPayments as $payment) {
                $flowRows->push([
                    'tanggal' => $payment->payment_date?->format('Y-m-d') ?? now()->format('Y-m-d'),
                    'arus' => 'Cash Out',
                    'nilai' => -1 * (float) $payment->amount,
                    'context' => 'Supplier payment ' . ($payment->purchaseOrder?->po_number ?? '-'),
                ]);
            }

            foreach ($payrollRuns as $payrollRun) {
                $flowRows->push([
                    'tanggal' => $payrollRun->period_end?->format('Y-m-d') ?? now()->format('Y-m-d'),
                    'arus' => 'Cash Out',
                    'nilai' => -1 * (float) $payrollRun->total_net,
                    'context' => 'Payroll payout ' . $payrollRun->code,
                ]);
            }

            $dailyRows = $flowRows
                ->groupBy('tanggal')
                ->map(function (Collection $group, string $date): array {
                    $net = $group->sum('nilai');

                    return [
                        'Tanggal' => CarbonImmutable::parse($date)->format('d M Y'),
                        'Inflow' => $this->formatCompactCurrency($group->filter(fn (array $row): bool => $row['nilai'] > 0)->sum('nilai')),
                        'Outflow' => $this->formatCompactCurrency(abs($group->filter(fn (array $row): bool => $row['nilai'] < 0)->sum('nilai'))),
                        'Net' => $this->formatCompactCurrency($net),
                        'Konteks' => $group->pluck('context')->take(2)->join(' / '),
                    ];
                })
                ->sortByDesc('Tanggal')
                ->values()
                ->take(8)
                ->all();

            return array_merge($page, $this->connectedBoardData([
                'heroStats' => [
                    ['label' => 'Cash in', 'value' => $this->formatCompactCurrency($cashIn), 'caption' => 'Collection customer yang sudah masuk lewat payment channel.'],
                    ['label' => 'Cash out', 'value' => $this->formatCompactCurrency($cashOut), 'caption' => 'Pembayaran supplier dan payroll yang sudah tercatat.'],
                    ['label' => 'Net flow', 'value' => $this->formatCompactCurrency($cashIn - $cashOut), 'caption' => 'Selisih arus kas masuk dan keluar pada dataset saat ini.'],
                    ['label' => 'Pending settlement', 'value' => $this->formatCompactCurrency($salesPayments->filter(fn ($payment): bool => $payment->settled_at !== null && $payment->settled_at->isFuture())->sum('amount')), 'caption' => 'Dana digital yang belum jatuh ke settlement date.'],
                ],
                'metrics' => [
                    ['label' => 'Supplier payment count', 'value' => number_format($supplierPayments->count(), 0, ',', '.'), 'note' => 'Jumlah pembayaran ke supplier yang sudah dicatat.'],
                    ['label' => 'Paid payroll runs', 'value' => number_format($payrollRuns->count(), 0, ',', '.'), 'note' => 'Payroll run yang sudah keluar sebagai arus kas aktual.'],
                    ['label' => 'Digital collection', 'value' => $this->formatCompactCurrency($salesPayments->filter(fn ($payment): bool => in_array($payment->paymentMethod?->category, ['qris', 'card', 'ewallet', 'bank_transfer'], true))->sum('amount')), 'note' => 'Kas masuk dari kanal non-tunai yang perlu settlement discipline.'],
                    ['label' => 'Cash collection', 'value' => $this->formatCompactCurrency($salesPayments->filter(fn ($payment): bool => $payment->paymentMethod?->category === 'cash')->sum('amount')), 'note' => 'Kas masuk instan dari channel tunai outlet.'],
                ],
                'mainTitle' => 'Operational Cashflow',
                'mainDescription' => 'Cashflow ini membaca collection penjualan, pembayaran supplier, dan payroll payout sebagai sumber arus kas retail harian.',
                'tableColumns' => ['Tanggal', 'Inflow', 'Outflow', 'Net', 'Konteks'],
                'tableRows' => $dailyRows,
                'sideTitle' => 'Cash Driver',
                'sideCards' => [
                    ['title' => 'Sales collection', 'value' => $this->formatCompactCurrency($cashIn), 'note' => 'Motor cash in utama dari outlet dan invoice customer.'],
                    ['title' => 'Supplier payout', 'value' => $this->formatCompactCurrency($supplierPayments->sum('amount')), 'note' => 'Outflow yang paling dekat dengan procurement obligation.'],
                    ['title' => 'Payroll payout', 'value' => $this->formatCompactCurrency($payrollRuns->sum('total_net')), 'note' => 'Arus kas tenaga kerja yang harus dibaca bersama sales productivity.'],
                ],
            ]));
        });
    }

    public function receivablesPayablesData(): array
    {
        return $this->analyticsCacheService->rememberReceivablesPayables(function (): array {
            $page = $this->basePage('receivables-payables');
            $receivables = SalesTransaction::query()
                ->with(['customer', 'outlet', 'payments.paymentMethod'])
                ->where('balance_due', '>', 0)
                ->orderBy('due_date')
                ->get();
            $payables = PurchaseOrder::query()
                ->with(['supplier', 'warehouse', 'payments.paymentMethod'])
                ->where('balance_due', '>', 0)
                ->whereIn('status', [PurchaseOrder::STATUS_APPROVED, PurchaseOrder::STATUS_PARTIALLY_RECEIVED, PurchaseOrder::STATUS_RECEIVED])
                ->orderBy('due_date')
                ->get();

            return array_merge($page, [
                'generatedAt' => $this->timestamp(),
                'heroStats' => [
                    ['label' => 'AR open', 'value' => $this->formatCompactCurrency($receivables->sum('balance_due')), 'caption' => 'Piutang customer yang masih harus dikoleksi.'],
                    ['label' => 'AR overdue', 'value' => number_format($receivables->filter(fn (SalesTransaction $transaction): bool => $transaction->due_date !== null && $transaction->due_date->isPast())->count(), 0, ',', '.'), 'caption' => 'Invoice customer yang sudah lewat jatuh tempo.'],
                    ['label' => 'AP open', 'value' => $this->formatCompactCurrency($payables->sum('balance_due')), 'caption' => 'Kewajiban supplier yang belum dibayarkan.'],
                    ['label' => 'AP due soon', 'value' => number_format($payables->filter(fn (PurchaseOrder $purchaseOrder): bool => $purchaseOrder->due_date !== null && $purchaseOrder->due_date->between(now()->subDay(), now()->addDays(7)))->count(), 0, ',', '.'), 'caption' => 'PO yang due date-nya jatuh dalam tujuh hari ke depan.'],
                ],
                'metrics' => [
                    ['label' => 'Net exposure', 'value' => $this->formatCompactCurrency($receivables->sum('balance_due') - $payables->sum('balance_due')), 'note' => 'Selisih piutang dan hutang sebagai pandangan cepat exposure modal kerja.'],
                    ['label' => 'Receivable customers', 'value' => number_format($receivables->groupBy('customer_id')->count(), 0, ',', '.'), 'note' => 'Jumlah akun customer yang masih punya saldo invoice terbuka.'],
                    ['label' => 'Payable suppliers', 'value' => number_format($payables->groupBy('supplier_id')->count(), 0, ',', '.'), 'note' => 'Jumlah supplier yang saat ini menunggu pembayaran.'],
                    ['label' => 'Collection coverage', 'value' => $this->formatPercent($this->percent($receivables->sum('paid_amount'), max($receivables->sum('net_amount'), 1))), 'note' => 'Porsi invoice receivable yang sudah sempat dibayar customer.'],
                ],
                'receivables' => $receivables->map(function (SalesTransaction $transaction): array {
                    return [
                        'invoice_number' => $transaction->invoice_number ?? $transaction->transaction_number,
                        'customer' => $transaction->customer?->name ?? ($transaction->customer_name ?: 'Walk-in Customer'),
                        'outlet' => $transaction->outlet?->name ?? '-',
                        'invoice_date' => $transaction->invoice_date?->format('d M Y') ?? '-',
                        'due_date' => $transaction->due_date?->format('d M Y') ?? '-',
                        'net_amount' => $this->formatCompactCurrency((float) $transaction->net_amount),
                        'paid_amount' => $this->formatCompactCurrency((float) $transaction->paid_amount),
                        'balance_due' => $this->formatCompactCurrency((float) $transaction->balance_due),
                        'status' => SalesTransaction::paymentStatusOptions()[$transaction->payment_status] ?? ucfirst($transaction->payment_status),
                        'payments' => $transaction->payments->map(fn ($payment): string => ($payment->paymentMethod?->name ?? '-') . ' ' . $this->formatCompactCurrency((float) $payment->amount))->join(' / '),
                        'payment_url' => route('sales-invoices.payment-form', $transaction),
                    ];
                })->all(),
                'payables' => $payables->map(function (PurchaseOrder $purchaseOrder): array {
                    return [
                        'po_number' => $purchaseOrder->po_number,
                        'supplier' => $purchaseOrder->supplier?->name ?? '-',
                        'warehouse' => $purchaseOrder->warehouse?->name ?? '-',
                        'due_date' => $purchaseOrder->due_date?->format('d M Y') ?? '-',
                        'total_amount' => $this->formatCompactCurrency((float) $purchaseOrder->total_amount),
                        'paid_amount' => $this->formatCompactCurrency((float) $purchaseOrder->paid_amount),
                        'balance_due' => $this->formatCompactCurrency((float) $purchaseOrder->balance_due),
                        'status' => PurchaseOrder::paymentStatusOptions()[$purchaseOrder->payment_status] ?? ucfirst($purchaseOrder->payment_status),
                        'payments' => $purchaseOrder->payments->map(fn ($payment): string => ($payment->paymentMethod?->name ?? '-') . ' ' . $this->formatCompactCurrency((float) $payment->amount))->join(' / '),
                        'payment_url' => route('purchase-orders.payment-form', $purchaseOrder),
                    ];
                })->all(),
                'actionCards' => [
                    ['title' => 'Top overdue customer', 'value' => $receivables->filter(fn (SalesTransaction $transaction): bool => $transaction->due_date !== null && $transaction->due_date->isPast())->sortByDesc('balance_due')->first()?->customer?->name ?? '-', 'note' => 'Prioritas follow-up collection untuk saldo invoice terbesar yang sudah overdue.'],
                    ['title' => 'Top supplier payable', 'value' => $payables->sortByDesc('balance_due')->first()?->supplier?->name ?? '-', 'note' => 'Supplier dengan exposure hutang terbesar yang perlu dijaga komunikasinya.'],
                    ['title' => 'Near-term due', 'value' => number_format($payables->filter(fn (PurchaseOrder $purchaseOrder): bool => $purchaseOrder->due_date !== null && $purchaseOrder->due_date->between(now()->subDay(), now()->addDays(7)))->count(), 0, ',', '.'), 'note' => 'Dokumen hutang yang perlu masuk cash planning pekan ini.'],
                ],
            ]);
        });
    }

    public function connectedWorkspaceData(string $pageKey): array
    {
        $page = $this->basePage($pageKey);
        $sampleEmployee = $this->samplePortalEmployee();
        $portalPage = in_array($pageKey, ['my-home', 'my-leave', 'my-schedule', 'salary-slip', 'resign-request'], true);
        $financePage = $pageKey === 'financial-report';
        $hrPage = in_array($pageKey, ['shift-attendance', 'schedule-request', 'leave-request', 'resign-data'], true);

        $heroStats = $portalPage
            ? [
                ['label' => 'Employee', 'value' => $sampleEmployee?->full_name ?? '-', 'caption' => 'Sample employee dari master HR.'],
                ['label' => 'Outlet', 'value' => $sampleEmployee?->outlet?->name ?? 'Head Office', 'caption' => 'Lokasi kerja utama.'],
                ['label' => 'Attendance', 'value' => number_format($sampleEmployee?->attendanceLogs()->count() ?? 0, 0, ',', '.'), 'caption' => 'Riwayat kehadiran personal.'],
                ['label' => 'Payroll', 'value' => number_format($sampleEmployee?->payrollRunItems()->count() ?? 0, 0, ',', '.'), 'caption' => 'Histori payroll personal.'],
            ]
            : ($financePage
                ? [
                    ['label' => 'Retail sales', 'value' => $this->formatCompactCurrency(SalesTransaction::query()->where('status', 'paid')->sum('net_amount')), 'caption' => 'Omzet dari POS paid.'],
                    ['label' => 'Open PO', 'value' => $this->formatCompactCurrency(PurchaseOrder::query()->whereIn('status', [PurchaseOrder::STATUS_PENDING_APPROVAL, PurchaseOrder::STATUS_APPROVED, PurchaseOrder::STATUS_PARTIALLY_RECEIVED])->sum('total_amount')), 'caption' => 'Komitmen belanja aktif.'],
                    ['label' => 'Payroll', 'value' => $this->formatCompactCurrency(PayrollRun::query()->latest('period_end')->value('total_net')), 'caption' => 'Net payroll terbaru.'],
                    ['label' => 'Inventory', 'value' => $this->formatCompactCurrency($this->productsWithStock()->sum(fn (Product $product): float => $this->stockValue($product))), 'caption' => 'Valuasi stok aktif.'],
                ]
                : [
                    ['label' => 'Outlet aktif', 'value' => number_format(Outlet::query()->where('status', Outlet::STATUS_ACTIVE)->count(), 0, ',', '.'), 'caption' => 'Cabang aktif di jaringan retail.'],
                    ['label' => 'Produk aktif', 'value' => number_format(Product::query()->where('status', Product::STATUS_ACTIVE)->count(), 0, ',', '.'), 'caption' => 'SKU aktif di katalog.'],
                    ['label' => 'Goods receipts', 'value' => number_format(GoodsReceipt::query()->count(), 0, ',', '.'), 'caption' => 'Receiving PO yang sudah diposting.'],
                    ['label' => 'Transfers', 'value' => number_format(StockTransfer::query()->count(), 0, ',', '.'), 'caption' => 'Dokumen mutasi antar lokasi.'],
                ]);

        $metrics = $portalPage
            ? [
                ['label' => 'Status', 'value' => $sampleEmployee ? $this->employeeStatus($sampleEmployee) : '-', 'note' => 'Status kerja personal.'],
                ['label' => 'Department', 'value' => $sampleEmployee?->department ?? '-', 'note' => 'Departemen employee sample.'],
                ['label' => 'Position', 'value' => $sampleEmployee?->position_title ?? '-', 'note' => 'Jabatan employee sample.'],
                ['label' => 'Last payroll', 'value' => $this->formatCompactCurrency($sampleEmployee?->payrollRunItems()->latest('id')->value('net_salary')), 'note' => 'Net salary terbaru.'],
            ]
            : ($hrPage
                ? [
                    ['label' => 'Attendance rate', 'value' => $this->formatPercent($this->percent(AttendanceLog::query()->whereIn('attendance_status', ['present', 'late'])->count(), max(AttendanceLog::query()->count(), 1))), 'note' => 'Kesiapan tim dari attendance.'],
                    ['label' => 'Leave', 'value' => number_format(Employee::query()->where('status', Employee::STATUS_LEAVE)->count(), 0, ',', '.'), 'note' => 'Employee status leave.'],
                    ['label' => 'Resign', 'value' => number_format(Employee::query()->where('status', Employee::STATUS_RESIGNED)->count(), 0, ',', '.'), 'note' => 'Arsip resign perusahaan.'],
                    ['label' => 'Payroll queue', 'value' => number_format(PayrollRun::query()->whereIn('status', [PayrollRun::STATUS_DRAFT, PayrollRun::STATUS_PROCESSING, PayrollRun::STATUS_APPROVED])->count(), 0, ',', '.'), 'note' => 'Workflow payroll berjalan.'],
                ]
                : [
                    ['label' => 'Split payment', 'value' => number_format(SalesTransaction::query()->where('status', 'paid')->where('split_payment_count', '>', 1)->count(), 0, ',', '.'), 'note' => 'Checkout multi metode pembayaran.'],
                    ['label' => 'Open PO', 'value' => number_format(PurchaseOrder::query()->whereIn('status', [PurchaseOrder::STATUS_PENDING_APPROVAL, PurchaseOrder::STATUS_APPROVED, PurchaseOrder::STATUS_PARTIALLY_RECEIVED])->count(), 0, ',', '.'), 'note' => 'Belanja yang masih berjalan.'],
                    ['label' => 'Receipts', 'value' => number_format(GoodsReceipt::query()->count(), 0, ',', '.'), 'note' => 'Penerimaan yang sudah posted.'],
                    ['label' => 'POS outlets', 'value' => number_format(Outlet::query()->whereNotNull('warehouse_id')->count(), 0, ',', '.'), 'note' => 'Outlet yang siap posting sales ke stok.'],
                ]);

        $tableRows = match ($pageKey) {
            'warehouse', 'store-warehouse' => Warehouse::query()->where('is_active', true)->orderBy('name')->get()->map(fn (Warehouse $warehouse): array => [
                'Area' => $warehouse->name,
                'Value' => Warehouse::typeOptions()[$warehouse->type] ?? $warehouse->type,
                'Context' => $warehouse->city,
            ])->all(),
            'kategori' => Category::query()->withCount('products')->orderBy('sort_order')->orderBy('name')->get()->map(fn (Category $category): array => [
                'Area' => $category->name,
                'Value' => number_format((int) $category->products_count, 0, ',', '.') . ' SKU',
                'Context' => $category->is_active ? 'Active' : 'Inactive',
            ])->all(),
            'purchase-return' => Supplier::query()->where('is_active', true)->orderByDesc('reject_rate')->take(8)->get()->map(fn (Supplier $supplier): array => [
                'Area' => $supplier->name,
                'Value' => $this->formatPercent((float) $supplier->reject_rate),
                'Context' => 'Fill ' . $this->formatPercent((float) $supplier->fill_rate),
            ])->all(),
            'shift-attendance' => AttendanceLog::query()->with('outlet')->get()->groupBy(fn (AttendanceLog $log): string => ($log->shift_name ?: '-') . ' / ' . ($log->outlet?->name ?? 'Head Office'))->map(fn (Collection $group, string $name): array => [
                'Area' => $name,
                'Value' => number_format($group->whereIn('attendance_status', ['present', 'late'])->count(), 0, ',', '.') . ' hadir',
                'Context' => number_format($group->whereIn('attendance_status', ['leave', 'absent'])->count(), 0, ',', '.') . ' issue',
            ])->values()->all(),
            'schedule-request' => Outlet::query()->where('status', Outlet::STATUS_ACTIVE)->withCount(['employees as active_staff' => fn ($query) => $query->where('status', Employee::STATUS_ACTIVE)])->orderByDesc('daily_sales_target')->get()->map(fn (Outlet $outlet): array => [
                'Area' => $outlet->name,
                'Value' => number_format((int) $outlet->active_staff, 0, ',', '.') . ' staff',
                'Context' => $this->formatCompactCurrency((float) $outlet->daily_sales_target),
            ])->all(),
            'leave-request' => Employee::query()->with('outlet')->where('status', Employee::STATUS_LEAVE)->orderBy('full_name')->get()->map(fn (Employee $employee): array => [
                'Area' => $employee->full_name,
                'Value' => $employee->outlet?->name ?? 'Head Office',
                'Context' => $employee->department,
            ])->all(),
            'resign-data' => Employee::query()->with('outlet')->where('status', Employee::STATUS_RESIGNED)->orderByDesc('join_date')->get()->map(fn (Employee $employee): array => [
                'Area' => $employee->full_name,
                'Value' => $employee->outlet?->name ?? 'Head Office',
                'Context' => $employee->position_title,
            ])->all(),
            'financial-report' => [
                ['Area' => 'Retail Sales', 'Value' => $this->formatCompactCurrency(SalesTransaction::query()->where('status', 'paid')->sum('net_amount')), 'Context' => 'POS paid transactions'],
                ['Area' => 'Payroll Net', 'Value' => $this->formatCompactCurrency(PayrollRun::query()->latest('period_end')->value('total_net')), 'Context' => 'Latest payroll run'],
                ['Area' => 'Open PO', 'Value' => $this->formatCompactCurrency(PurchaseOrder::query()->whereIn('status', [PurchaseOrder::STATUS_PENDING_APPROVAL, PurchaseOrder::STATUS_APPROVED, PurchaseOrder::STATUS_PARTIALLY_RECEIVED])->sum('total_amount')), 'Context' => 'Procurement exposure'],
                ['Area' => 'Inventory', 'Value' => $this->formatCompactCurrency($this->productsWithStock()->sum(fn (Product $product): float => $this->stockValue($product))), 'Context' => 'Active stock value'],
            ],
            default => [
                ['Area' => 'Employee Code', 'Value' => $sampleEmployee?->employee_code ?? '-', 'Context' => $sampleEmployee?->department ?? '-'],
                ['Area' => 'Employment Type', 'Value' => $sampleEmployee ? (Employee::employmentTypeOptions()[$sampleEmployee->employment_type] ?? '-') : '-', 'Context' => $sampleEmployee?->position_title ?? '-'],
                ['Area' => 'Last Shift', 'Value' => $sampleEmployee?->attendanceLogs()->latest('shift_date')->value('shift_name') ?? '-', 'Context' => $sampleEmployee?->outlet?->name ?? 'Head Office'],
            ],
        };

        return array_merge($page, $this->connectedBoardData([
            'heroStats' => $heroStats,
            'metrics' => $metrics,
            'mainTitle' => $page['title'],
            'mainDescription' => $page['description'] ?? $page['pageDescription'],
            'tableColumns' => ['Area', 'Value', 'Context'],
            'tableRows' => $tableRows,
        ]));
    }

    public function stockOpnameIndexData(): array
    {
        $page = $this->basePage('stock-opname');
        $opnames = StockOpname::query()
            ->with(['warehouse', 'approver'])
            ->withCount('items')
            ->orderByDesc('opname_date')
            ->orderByDesc('id')
            ->take(30)
            ->get();

        return array_merge($page, [
            'generatedAt' => $this->timestamp(),
            'createUrl' => route('stock-opnames.create'),
            'heroStats' => [
                ['label' => 'Dokumen opname', 'value' => number_format($opnames->count(), 0, ',', '.'), 'caption' => 'Stock opname terbaru lintas gudang.'],
                ['label' => 'Pending approval', 'value' => number_format($opnames->where('status', StockOpname::STATUS_PENDING_APPROVAL)->count(), 0, ',', '.'), 'caption' => 'Dokumen menunggu verifikasi manager.'],
                ['label' => 'Total variance', 'value' => number_format((float) $opnames->sum('total_variance_qty'), 1, ',', '.'), 'caption' => 'Akumulasi selisih qty dari seluruh dokumen.'],
                ['label' => 'Adjusted value', 'value' => $this->formatCompactCurrency((float) $opnames->sum('total_variance_value')), 'caption' => 'Nilai adjustment potensial dari hasil count fisik.'],
            ],
            'opnames' => $opnames->map(function (StockOpname $opname): array {
                return [
                    'id' => $opname->id,
                    'opname_number' => $opname->opname_number,
                    'opname_date' => $opname->opname_date?->format('d M Y') ?? '-',
                    'warehouse' => $opname->warehouse?->name ?? '-',
                    'status' => $opname->status,
                    'status_label' => match ($opname->status) {
                        StockOpname::STATUS_DRAFT => 'Draft',
                        StockOpname::STATUS_PENDING_APPROVAL => 'Pending Approval',
                        StockOpname::STATUS_APPROVED => 'Approved',
                        StockOpname::STATUS_REJECTED => 'Rejected',
                        default => ucfirst($opname->status),
                    },
                    'items_count' => (int) $opname->items_count,
                    'total_variance_qty' => number_format((float) $opname->total_variance_qty, 1, ',', '.'),
                    'total_variance_value' => $this->formatCompactCurrency((float) $opname->total_variance_value),
                    'approver' => $opname->approver?->name ?? '-',
                    'can_edit' => $opname->canBeEdited(),
                    'can_submit' => in_array($opname->status, [StockOpname::STATUS_DRAFT, StockOpname::STATUS_REJECTED], true),
                    'can_approve' => $opname->canBeApproved(),
                    'can_reject' => $opname->canBeApproved(),
                    'edit_url' => route('stock-opnames.edit', $opname),
                    'submit_url' => route('stock-opnames.submit', $opname),
                    'approve_url' => route('stock-opnames.approve', $opname),
                    'reject_url' => route('stock-opnames.reject', $opname),
                ];
            })->all(),
        ]);
    }

    public function stockOpnameFormData(?StockOpname $stockOpname = null): array
    {
        $stockOpname?->loadMissing('items.product');

        return [
            'title' => $stockOpname?->exists ? 'Edit Stock Opname' : 'Buat Stock Opname',
            'pageTitle' => $stockOpname?->exists ? 'Edit Stock Opname' : 'Buat Stock Opname',
            'pageEyebrow' => 'Inventory Control',
            'pageDescription' => 'Catat hasil count fisik lalu submit untuk approval adjustment agar ledger stok tetap akurat.',
            'stockOpname' => $stockOpname,
            'warehouses' => Warehouse::query()->where('is_active', true)->orderBy('name')->get(),
            'products' => Product::query()->where('status', Product::STATUS_ACTIVE)->orderBy('name')->get(),
            'submitUrl' => $stockOpname?->exists ? route('stock-opnames.update', $stockOpname) : route('stock-opnames.store'),
            'submitMethod' => $stockOpname?->exists ? 'PUT' : 'POST',
            'backUrl' => route('stock-opname'),
            'generatedAt' => $this->timestamp(),
        ];
    }

    public function salesReturnIndexData(): array
    {
        $page = $this->basePage('sales-return');
        $returns = SalesReturn::query()
            ->with(['salesTransaction.outlet', 'approver'])
            ->withCount('items')
            ->orderByDesc('return_date')
            ->orderByDesc('id')
            ->take(30)
            ->get();
        $candidates = SalesTransaction::query()
            ->with('outlet')
            ->whereIn('status', ['paid', 'refunded'])
            ->orderByDesc('sold_at')
            ->take(12)
            ->get();

        return array_merge($page, [
            'generatedAt' => $this->timestamp(),
            'heroStats' => [
                ['label' => 'Retur dibuat', 'value' => number_format($returns->count(), 0, ',', '.'), 'caption' => 'Dokumen retur penjualan terbaru.'],
                ['label' => 'Pending approval', 'value' => number_format($returns->where('status', SalesReturn::STATUS_PENDING_APPROVAL)->count(), 0, ',', '.'), 'caption' => 'Retur menunggu persetujuan supervisor.'],
                ['label' => 'Refund approved', 'value' => $this->formatCompactCurrency((float) $returns->where('status', SalesReturn::STATUS_APPROVED)->sum('refund_amount')), 'caption' => 'Total nominal refund yang sudah diposting.'],
                ['label' => 'Invoice candidate', 'value' => number_format($candidates->count(), 0, ',', '.'), 'caption' => 'Transaksi yang bisa dibuatkan retur.'],
            ],
            'returns' => $returns->map(function (SalesReturn $return): array {
                $transaction = $return->salesTransaction;
                return [
                    'id' => $return->id,
                    'return_number' => $return->return_number,
                    'return_date' => $return->return_date?->format('d M Y') ?? '-',
                    'transaction_number' => $transaction?->transaction_number ?? '-',
                    'invoice_number' => $transaction?->invoice_number ?? '-',
                    'outlet' => $transaction?->outlet?->name ?? '-',
                    'status' => $return->status,
                    'status_label' => match ($return->status) {
                        SalesReturn::STATUS_DRAFT => 'Draft',
                        SalesReturn::STATUS_PENDING_APPROVAL => 'Pending Approval',
                        SalesReturn::STATUS_APPROVED => 'Approved',
                        SalesReturn::STATUS_REJECTED => 'Rejected',
                        default => ucfirst($return->status),
                    },
                    'items_count' => (int) $return->items_count,
                    'refund_amount' => $this->formatCompactCurrency((float) $return->refund_amount),
                    'approver' => $return->approver?->name ?? '-',
                    'can_submit' => in_array($return->status, [SalesReturn::STATUS_DRAFT, SalesReturn::STATUS_REJECTED], true),
                    'can_approve' => $return->canBeApproved(),
                    'can_reject' => $return->canBeApproved(),
                    'submit_url' => route('sales-returns.submit', $return),
                    'approve_url' => route('sales-returns.approve', $return),
                    'reject_url' => route('sales-returns.reject', $return),
                ];
            })->all(),
            'createCandidates' => $candidates->map(fn (SalesTransaction $transaction): array => [
                'label' => ($transaction->invoice_number ?? $transaction->transaction_number) . ' • ' . ($transaction->outlet?->name ?? '-'),
                'net_amount' => $this->formatCompactCurrency((float) $transaction->net_amount),
                'sold_at' => $transaction->sold_at?->format('d M Y H:i') ?? '-',
                'create_url' => route('sales-returns.create', $transaction),
            ])->all(),
        ]);
    }

    public function salesReturnFormData(SalesTransaction $salesTransaction): array
    {
        $salesTransaction->loadMissing(['outlet', 'items.product']);

        return [
            'title' => 'Buat Retur Penjualan',
            'pageTitle' => 'Buat Retur Penjualan',
            'pageEyebrow' => 'Sales & POS',
            'pageDescription' => 'Pilih item yang diretur customer, lalu submit untuk approval refund dan reverse jurnal.',
            'salesTransaction' => $salesTransaction,
            'items' => $salesTransaction->items,
            'submitUrl' => route('sales-returns.store', $salesTransaction),
            'backUrl' => route('sales-return'),
            'generatedAt' => $this->timestamp(),
        ];
    }

    public function purchaseReturnIndexData(): array
    {
        $page = $this->basePage('purchase-return');
        $returns = PurchaseReturn::query()
            ->with(['supplier', 'warehouse', 'purchaseOrder', 'approver'])
            ->withCount('items')
            ->orderByDesc('return_date')
            ->orderByDesc('id')
            ->take(30)
            ->get();
        $candidates = PurchaseOrder::query()
            ->with(['supplier', 'warehouse'])
            ->whereIn('status', [PurchaseOrder::STATUS_PARTIALLY_RECEIVED, PurchaseOrder::STATUS_RECEIVED])
            ->orderByDesc('received_at')
            ->take(12)
            ->get();

        return array_merge($page, [
            'generatedAt' => $this->timestamp(),
            'createUrl' => route('purchase-returns.create'),
            'heroStats' => [
                ['label' => 'Retur pembelian', 'value' => number_format($returns->count(), 0, ',', '.'), 'caption' => 'Dokumen retur supplier terbaru.'],
                ['label' => 'Pending approval', 'value' => number_format($returns->where('status', PurchaseReturn::STATUS_PENDING_APPROVAL)->count(), 0, ',', '.'), 'caption' => 'Retur menunggu persetujuan manager.'],
                ['label' => 'Approved value', 'value' => $this->formatCompactCurrency((float) $returns->where('status', PurchaseReturn::STATUS_APPROVED)->sum('total_amount')), 'caption' => 'Nilai retur pembelian yang sudah diposting.'],
                ['label' => 'PO candidates', 'value' => number_format($candidates->count(), 0, ',', '.'), 'caption' => 'PO yang sudah receiving dan bisa diretur.'],
            ],
            'returns' => $returns->map(function (PurchaseReturn $return): array {
                return [
                    'id' => $return->id,
                    'return_number' => $return->return_number,
                    'return_date' => $return->return_date?->format('d M Y') ?? '-',
                    'supplier' => $return->supplier?->name ?? '-',
                    'warehouse' => $return->warehouse?->name ?? '-',
                    'po_number' => $return->purchaseOrder?->po_number ?? '-',
                    'status' => $return->status,
                    'status_label' => match ($return->status) {
                        PurchaseReturn::STATUS_DRAFT => 'Draft',
                        PurchaseReturn::STATUS_PENDING_APPROVAL => 'Pending Approval',
                        PurchaseReturn::STATUS_APPROVED => 'Approved',
                        PurchaseReturn::STATUS_REJECTED => 'Rejected',
                        default => ucfirst($return->status),
                    },
                    'items_count' => (int) $return->items_count,
                    'total_amount' => $this->formatCompactCurrency((float) $return->total_amount),
                    'approver' => $return->approver?->name ?? '-',
                    'can_submit' => in_array($return->status, [PurchaseReturn::STATUS_DRAFT, PurchaseReturn::STATUS_REJECTED], true),
                    'can_approve' => $return->canBeApproved(),
                    'can_reject' => $return->canBeApproved(),
                    'submit_url' => route('purchase-returns.submit', $return),
                    'approve_url' => route('purchase-returns.approve', $return),
                    'reject_url' => route('purchase-returns.reject', $return),
                ];
            })->all(),
            'poCandidates' => $candidates->map(fn (PurchaseOrder $purchaseOrder): array => [
                'label' => $purchaseOrder->po_number . ' • ' . ($purchaseOrder->supplier?->name ?? '-'),
                'warehouse' => $purchaseOrder->warehouse?->name ?? '-',
                'total_amount' => $this->formatCompactCurrency((float) $purchaseOrder->total_amount),
            ])->all(),
        ]);
    }

    public function purchaseReturnFormData(): array
    {
        return [
            'title' => 'Buat Retur Pembelian',
            'pageTitle' => 'Buat Retur Pembelian',
            'pageEyebrow' => 'Procurement',
            'pageDescription' => 'Catat barang yang dikembalikan ke supplier lalu submit untuk approval reverse stok dan jurnal.',
            'purchaseOrders' => PurchaseOrder::query()
                ->with(['supplier', 'warehouse', 'items.product'])
                ->whereIn('status', [PurchaseOrder::STATUS_PARTIALLY_RECEIVED, PurchaseOrder::STATUS_RECEIVED])
                ->orderByDesc('received_at')
                ->orderByDesc('id')
                ->get(),
            'suppliers' => Supplier::query()->where('is_active', true)->orderBy('name')->get(),
            'warehouses' => Warehouse::query()->where('is_active', true)->orderBy('name')->get(),
            'products' => Product::query()->where('status', Product::STATUS_ACTIVE)->orderBy('name')->get(),
            'submitUrl' => route('purchase-returns.store'),
            'backUrl' => route('purchase-return'),
            'generatedAt' => $this->timestamp(),
        ];
    }

    public function periodClosingData(): array
    {
        $page = $this->basePage('period-closing');
        $periods = AccountingPeriod::query()
            ->withoutTenantLocation()
            ->with('closer')
            ->orderByDesc('start_date')
            ->orderByDesc('id')
            ->take(36)
            ->get();

        return array_merge($page, [
            'generatedAt' => $this->timestamp(),
            'periods' => $periods->map(function (AccountingPeriod $period): array {
                return [
                    'id' => $period->id,
                    'period_code' => $period->period_code,
                    'range' => ($period->start_date?->format('d M Y') ?? '-') . ' - ' . ($period->end_date?->format('d M Y') ?? '-'),
                    'status' => $period->status,
                    'status_label' => $period->status === AccountingPeriod::STATUS_CLOSED ? 'Closed' : 'Open',
                    'closed_at' => $period->closed_at?->format('d M Y H:i'),
                    'closed_by' => $period->closer?->name ?? '-',
                    'can_reopen' => $period->status === AccountingPeriod::STATUS_CLOSED,
                    'reopen_url' => route('period-closing.reopen', $period),
                ];
            })->all(),
            'closeUrl' => route('period-closing.close'),
            'defaultStartDate' => CarbonImmutable::now('Asia/Jakarta')->startOfMonth()->toDateString(),
            'defaultEndDate' => CarbonImmutable::now('Asia/Jakarta')->endOfMonth()->toDateString(),
            'defaultPeriodCode' => CarbonImmutable::now('Asia/Jakarta')->format('Ym'),
        ]);
    }

    public function cashReconciliationData(): array
    {
        $page = $this->basePage('cash-reconciliation');
        $records = CashReconciliation::query()
            ->with('approver')
            ->orderByDesc('reconciliation_date')
            ->orderByDesc('id')
            ->take(30)
            ->get();

        return array_merge($page, [
            'generatedAt' => $this->timestamp(),
            'locations' => Location::query()->orderBy('name')->get(),
            'rows' => $records->map(function (CashReconciliation $record): array {
                return [
                    'id' => $record->id,
                    'date' => $record->reconciliation_date?->format('d M Y') ?? '-',
                    'opening_balance' => $this->formatCompactCurrency((float) $record->opening_balance),
                    'expected_inflows' => $this->formatCompactCurrency((float) $record->expected_inflows),
                    'expected_outflows' => $this->formatCompactCurrency((float) $record->expected_outflows),
                    'expected_ending' => $this->formatCompactCurrency((float) $record->expected_ending_balance),
                    'counted_ending' => $this->formatCompactCurrency((float) $record->counted_ending_balance),
                    'difference_amount' => $this->formatCompactCurrency((float) $record->difference_amount),
                    'status' => $record->status,
                    'status_label' => ucfirst($record->status),
                    'approver' => $record->approver?->name ?? '-',
                    'can_submit' => $record->status === CashReconciliation::STATUS_DRAFT,
                    'can_approve' => in_array($record->status, [CashReconciliation::STATUS_DRAFT, CashReconciliation::STATUS_SUBMITTED], true),
                    'can_reject' => in_array($record->status, [CashReconciliation::STATUS_DRAFT, CashReconciliation::STATUS_SUBMITTED], true),
                    'submit_url' => route('cash-reconciliations.submit', $record),
                    'approve_url' => route('cash-reconciliations.approve', $record),
                    'reject_url' => route('cash-reconciliations.reject', $record),
                ];
            })->all(),
            'storeUrl' => route('cash-reconciliations.store'),
            'defaultDate' => CarbonImmutable::now('Asia/Jakarta')->toDateString(),
            'reportExports' => ReportExport::query()
                ->withoutTenantLocation()
                ->orderByDesc('id')
                ->take(10)
                ->get()
                ->map(fn (ReportExport $report): array => [
                    'id' => $report->id,
                    'status' => $report->status,
                    'format' => strtoupper($report->format),
                    'file_name' => $report->file_name ?? '-',
                    'error_message' => $report->error_message,
                    'download_url' => $report->status === ReportExport::STATUS_COMPLETED ? route('financial-report.export.download', $report) : null,
                ])->all(),
            'queueExportUrl' => route('financial-report.export.queue'),
        ]);
    }

    public function auditTrailPageData(): array
    {
        $page = $this->basePage('audit-trail');
        $latestLog = AuditLog::query()->orderByDesc('id')->value('created_at');

        return array_merge($page, [
            'generatedAt' => $this->timestamp(),
            'latestAuditAt' => $latestLog !== null
                ? CarbonImmutable::parse((string) $latestLog)->format('d M Y H:i')
                : '-',
        ]);
    }

    private function purchaseOrderSpendMix(Collection $purchaseOrders): array
    {
        $openOrders = $purchaseOrders->whereIn('status', [
            PurchaseOrder::STATUS_DRAFT,
            PurchaseOrder::STATUS_PENDING_APPROVAL,
            PurchaseOrder::STATUS_APPROVED,
            PurchaseOrder::STATUS_PARTIALLY_RECEIVED,
        ]);

        $categoryTotals = [];

        foreach ($openOrders as $purchaseOrder) {
            foreach ($purchaseOrder->items as $item) {
                $category = $item->product?->category?->name ?? 'Tanpa Kategori';
                $categoryTotals[$category] = ($categoryTotals[$category] ?? 0) + (float) $item->line_total;
            }
        }

        $total = max(array_sum($categoryTotals), 1);

        return collect($categoryTotals)
            ->sortDesc()
            ->map(fn (float $amount, string $name): array => [
                'name' => $name,
                'share' => (int) round(($amount / $total) * 100),
                'value' => $this->formatCompactCurrency($amount),
            ])
            ->values()
            ->all();
    }

    private function purchaseOrderActionQueue(Collection $purchaseOrders): array
    {
        $actions = [];

        $pending = $purchaseOrders->where('status', PurchaseOrder::STATUS_PENDING_APPROVAL)->take(2);
        foreach ($pending as $purchaseOrder) {
            $actions[] = [
                'title' => 'Approve ' . $purchaseOrder->po_number,
                'detail' => 'PO untuk ' . ($purchaseOrder->supplier?->name ?? 'supplier') . ' masih menunggu approval dengan nilai ' . $this->formatCompactCurrency((float) $purchaseOrder->total_amount) . '.',
            ];
        }

        $overdue = $purchaseOrders->first(fn (PurchaseOrder $purchaseOrder): bool => $purchaseOrder->expected_date !== null && $purchaseOrder->expected_date->isPast() && in_array($purchaseOrder->status, [PurchaseOrder::STATUS_PENDING_APPROVAL, PurchaseOrder::STATUS_APPROVED], true));
        if ($overdue) {
            $actions[] = [
                'title' => $overdue->po_number . ' melewati ETA',
                'detail' => 'Follow-up supplier ' . ($overdue->supplier?->name ?? '-') . ' karena expected date ' . $overdue->expected_date?->format('d M Y') . ' sudah lewat.',
            ];
        }

        return array_slice($actions, 0, 3);
    }

    private function productStatus(Product $product): string
    {
        if ($product->status === Product::STATUS_DISCONTINUED) {
            return 'Discontinued';
        }

        if ($product->status === Product::STATUS_INACTIVE) {
            return 'Inactive';
        }

        $daysCover = $this->daysCover($product);

        if ($daysCover !== null && $daysCover <= 2) {
            return 'Critical';
        }

        if ((float) $product->current_stock <= (float) $product->reorder_level) {
            return 'Replenish';
        }

        if ($product->is_featured) {
            return 'Hero';
        }

        if ($daysCover !== null && $daysCover <= 5) {
            return 'Watch';
        }

        return 'Stable';
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

        if ((float) $supplier->rating >= 4.4) {
            return 'Preferred';
        }

        return 'Stable';
    }

    private function outletStatus(Outlet $outlet): string
    {
        return match ($outlet->status) {
            Outlet::STATUS_ACTIVE => $outlet->is_fulfillment_hub ? 'Hub' : 'Active',
            Outlet::STATUS_RENOVATION => 'Renovation',
            default => 'Inactive',
        };
    }

    private function employeeStatus(Employee $employee): string
    {
        return match ($employee->status) {
            Employee::STATUS_ACTIVE => 'Active',
            Employee::STATUS_LEAVE => 'Leave',
            default => 'Resigned',
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

    private function marginPercent(Product $product): float
    {
        $sellingPrice = (float) $product->selling_price;

        if ($sellingPrice <= 0) {
            return 0.0;
        }

        return (($sellingPrice - (float) $product->cost_price) / $sellingPrice) * 100;
    }

    private function daysCover(Product $product): ?float
    {
        $dailyRunRate = (float) $product->daily_run_rate;

        if ($dailyRunRate <= 0) {
            return null;
        }

        return (float) $product->current_stock / $dailyRunRate;
    }

    private function rowDaysCover(object $row): ?float
    {
        if ((float) $row->daily_run_rate <= 0) {
            return null;
        }

        return (float) $row->on_hand / (float) $row->daily_run_rate;
    }

    private function basePage(string $key): array
    {
        $page = MenuHelper::findPage($key);

        return [
            'key' => $page['key'] ?? $key,
            'path' => $page['path'] ?? '#',
            'title' => $page['title'] ?? 'WebStellar ERP',
            'pageTitle' => $page['title'] ?? 'WebStellar ERP',
            'pageEyebrow' => $page['eyebrow'] ?? 'Workspace',
            'description' => $page['description'] ?? 'Halaman ini disiapkan sebagai workspace ERP retail.',
            'pageDescription' => $page['description'] ?? 'Halaman ini disiapkan sebagai workspace ERP retail.',
        ];
    }

    private function connectedBoardData(array $overrides = []): array
    {
        return array_merge([
            'generatedAt' => $this->timestamp(),
            'heroStats' => [],
            'metrics' => [],
            'mainTitle' => 'Connected Workspace',
            'mainDescription' => 'Halaman ini membaca data nyata dari modul ERP yang sudah aktif.',
            'tableColumns' => ['Area', 'Value', 'Context'],
            'tableRows' => [],
            'sideTitle' => null,
            'sideCards' => [],
            'actions' => [],
        ], $overrides);
    }

    private function stockMapByWarehouse(): array
    {
        return $this->warehouseStockRows()
            ->groupBy('warehouse_id')
            ->map(fn (Collection $rows): array => $rows->mapWithKeys(fn (object $row): array => [$row->product_id => (float) $row->on_hand])->all())
            ->all();
    }

    private function samplePortalEmployee(): ?Employee
    {
        return Employee::query()
            ->with(['outlet', 'attendanceLogs', 'payrollRunItems.payrollRun'])
            ->where('status', Employee::STATUS_ACTIVE)
            ->orderBy('full_name')
            ->first();
    }

    private function percent(float|int|null $value, float|int|null $total): float
    {
        if ((float) $total === 0.0) {
            return 0.0;
        }

        return ((float) $value / (float) $total) * 100;
    }

    private function round(float|int|null $value, int $precision = 1): float
    {
        return round((float) $value, $precision);
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
        if ($value === null) {
            return '-';
        }

        return $this->formatDecimal((float) $value) . ' hari';
    }

    private function formatDecimal(float|int|string|null $value, int $precision = 1): string
    {
        $formatted = number_format((float) $value, $precision, ',', '.');
        $formatted = preg_replace('/,0+$/', '', $formatted);

        return preg_replace('/(,\d*[1-9])0+$/', '$1', $formatted) ?? $formatted;
    }
}
