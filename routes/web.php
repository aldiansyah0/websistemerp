<?php

use App\Helpers\MenuHelper;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CashReconciliationController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\FinancialReportExportController;
use App\Http\Controllers\GoodsReceiptController;
use App\Http\Controllers\OutletController;
use App\Http\Controllers\PayrollController;
use App\Http\Controllers\PeriodClosingController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\PurchaseOrderController;
use App\Http\Controllers\PurchaseReturnController;
use App\Http\Controllers\SalesReturnController;
use App\Http\Controllers\SalesTransactionController;
use App\Http\Controllers\ShiftAttendanceController;
use App\Http\Controllers\StockOpnameController;
use App\Http\Controllers\StockTransferController;
use App\Http\Controllers\SupplierController;
use Illuminate\Support\Facades\Route;

Route::get('/', [DashboardController::class, 'dashboard'])->name('dashboard');
Route::get('/kategori/tambah', [CategoryController::class, 'create'])->name('categories.create')->middleware('permission:master-data.manage');
Route::post('/kategori', [CategoryController::class, 'store'])->name('categories.store')->middleware('permission:master-data.manage');
Route::get('/kategori/{category}/edit', [CategoryController::class, 'edit'])->name('categories.edit')->middleware('permission:master-data.manage');
Route::put('/kategori/{category}', [CategoryController::class, 'update'])->name('categories.update')->middleware('permission:master-data.manage');
Route::delete('/kategori/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy')->middleware('permission:master-data.manage');

Route::get('/supplier/tambah', [SupplierController::class, 'create'])->name('suppliers.create')->middleware('permission:master-data.manage');
Route::post('/supplier', [SupplierController::class, 'store'])->name('suppliers.store')->middleware('permission:master-data.manage');
Route::get('/supplier/{supplier}/edit', [SupplierController::class, 'edit'])->name('suppliers.edit')->middleware('permission:master-data.manage');
Route::put('/supplier/{supplier}', [SupplierController::class, 'update'])->name('suppliers.update')->middleware('permission:master-data.manage');
Route::delete('/supplier/{supplier}', [SupplierController::class, 'destroy'])->name('suppliers.destroy')->middleware('permission:master-data.manage');

Route::get('/penjualan/customer/tambah', [CustomerController::class, 'create'])->name('customers.create')->middleware('permission:sales.pos.manage');
Route::post('/penjualan/customer', [CustomerController::class, 'store'])->name('customers.store')->middleware('permission:sales.pos.manage');
Route::get('/penjualan/customer/{customer}/edit', [CustomerController::class, 'edit'])->name('customers.edit')->middleware('permission:sales.pos.manage');
Route::put('/penjualan/customer/{customer}', [CustomerController::class, 'update'])->name('customers.update')->middleware('permission:sales.pos.manage');

Route::get('/outlet/tambah', [OutletController::class, 'create'])->name('outlets.create')->middleware('permission:master-data.manage');
Route::post('/outlet', [OutletController::class, 'store'])->name('outlets.store')->middleware('permission:master-data.manage');
Route::get('/outlet/{outlet}/edit', [OutletController::class, 'edit'])->name('outlets.edit')->middleware('permission:master-data.manage');
Route::put('/outlet/{outlet}', [OutletController::class, 'update'])->name('outlets.update')->middleware('permission:master-data.manage');

Route::get('/produk/tambah', [ProductController::class, 'create'])->name('products.create')->middleware('permission:master-data.manage');
Route::post('/produk', [ProductController::class, 'store'])->name('products.store')->middleware('permission:master-data.manage');
Route::get('/produk/{product}/edit', [ProductController::class, 'edit'])->name('products.edit')->middleware('permission:master-data.manage');
Route::put('/produk/{product}', [ProductController::class, 'update'])->name('products.update')->middleware('permission:master-data.manage');
Route::delete('/produk/{product}', [ProductController::class, 'destroy'])->name('products.destroy')->middleware('permission:master-data.manage');

Route::get('/penjualan/pos-transaksi/buat', [SalesTransactionController::class, 'create'])->name('sales-transactions.create')->middleware('permission:sales.pos.manage');
Route::post('/penjualan/pos-transaksi', [SalesTransactionController::class, 'store'])->name('sales-transactions.store')->middleware('permission:sales.pos.manage');
Route::get('/penjualan/invoice/{salesTransaction}/payment', [SalesTransactionController::class, 'invoicePaymentForm'])->name('sales-invoices.payment-form')->middleware('permission:sales.pos.manage');
Route::post('/penjualan/invoice/{salesTransaction}/payment', [SalesTransactionController::class, 'storeInvoicePayment'])->name('sales-invoices.pay')->middleware('permission:sales.pos.manage');

Route::get('/hrd/kelola-karyawan/tambah', [EmployeeController::class, 'create'])->name('employees.create')->middleware('permission:hr.employee.manage');
Route::post('/hrd/kelola-karyawan', [EmployeeController::class, 'store'])->name('employees.store')->middleware('permission:hr.employee.manage');
Route::get('/hrd/kelola-karyawan/{employee}/edit', [EmployeeController::class, 'edit'])->name('employees.edit')->middleware('permission:hr.employee.manage');
Route::put('/hrd/kelola-karyawan/{employee}', [EmployeeController::class, 'update'])->name('employees.update')->middleware('permission:hr.employee.manage');
Route::post('/hrd/absensi-shift/assign', [ShiftAttendanceController::class, 'assign'])->name('shift-attendance.assign')->middleware('permission:hr.shift.manage');
Route::post('/hrd/absensi-shift/{assignment}/clock-in', [ShiftAttendanceController::class, 'clockIn'])->name('shift-attendance.clock-in')->middleware('permission:hr.shift.manage');
Route::post('/hrd/absensi-shift/{assignment}/clock-out', [ShiftAttendanceController::class, 'clockOut'])->name('shift-attendance.clock-out')->middleware('permission:hr.shift.manage');
Route::post('/hrd/absensi-shift/{assignment}/mark-absent', [ShiftAttendanceController::class, 'markAbsent'])->name('shift-attendance.mark-absent')->middleware('permission:hr.shift.manage');
Route::post('/hrd/daftar-penggajian/generate', [PayrollController::class, 'generate'])->name('payroll-runs.generate')->middleware('permission:hr.payroll.manage');
Route::post('/hrd/daftar-penggajian/{payrollRun}/submit', [PayrollController::class, 'submit'])->name('payroll-runs.submit')->middleware('permission:hr.payroll.manage');
Route::post('/hrd/daftar-penggajian/{payrollRun}/approve', [PayrollController::class, 'approve'])->name('payroll-runs.approve')->middleware('permission:finance.payroll.approve');
Route::post('/hrd/daftar-penggajian/{payrollRun}/pay', [PayrollController::class, 'pay'])->name('payroll-runs.pay')->middleware('permission:finance.payroll.approve');

Route::get('/stok/mutasi/buat', [StockTransferController::class, 'create'])->name('stock-transfers.create')->middleware('permission:inventory.transfer.manage');
Route::post('/stok/mutasi', [StockTransferController::class, 'store'])->name('stock-transfers.store')->middleware('permission:inventory.transfer.manage');
Route::get('/stok/mutasi/{stockTransfer}/edit', [StockTransferController::class, 'edit'])->name('stock-transfers.edit')->middleware('permission:inventory.transfer.manage');
Route::put('/stok/mutasi/{stockTransfer}', [StockTransferController::class, 'update'])->name('stock-transfers.update')->middleware('permission:inventory.transfer.manage');
Route::post('/stok/mutasi/{stockTransfer}/submit', [StockTransferController::class, 'submit'])->name('stock-transfers.submit')->middleware('permission:inventory.transfer.manage');
Route::post('/stok/mutasi/{stockTransfer}/approve', [StockTransferController::class, 'approve'])->name('stock-transfers.approve')->middleware('permission:inventory.transfer.manage');
Route::post('/stok/mutasi/{stockTransfer}/reject', [StockTransferController::class, 'reject'])->name('stock-transfers.reject')->middleware('permission:inventory.transfer.manage');
Route::post('/stok/mutasi/{stockTransfer}/cancel', [StockTransferController::class, 'cancel'])->name('stock-transfers.cancel')->middleware('permission:inventory.transfer.manage');
Route::get('/stok/mutasi/{stockTransfer}/terima', [StockTransferController::class, 'receiveForm'])->name('stock-transfers.receive-form')->middleware('permission:inventory.transfer.manage');
Route::post('/stok/mutasi/{stockTransfer}/terima', [StockTransferController::class, 'receive'])->name('stock-transfers.receive')->middleware('permission:inventory.transfer.manage');

Route::get('/stok/stock-opname/buat', [StockOpnameController::class, 'create'])->name('stock-opnames.create')->middleware('permission:inventory.opname.manage');
Route::post('/stok/stock-opname', [StockOpnameController::class, 'store'])->name('stock-opnames.store')->middleware('permission:inventory.opname.manage');
Route::get('/stok/stock-opname/{stockOpname}/edit', [StockOpnameController::class, 'edit'])->name('stock-opnames.edit')->middleware('permission:inventory.opname.manage');
Route::put('/stok/stock-opname/{stockOpname}', [StockOpnameController::class, 'update'])->name('stock-opnames.update')->middleware('permission:inventory.opname.manage');
Route::post('/stok/stock-opname/{stockOpname}/submit', [StockOpnameController::class, 'submit'])->name('stock-opnames.submit')->middleware('permission:inventory.opname.manage');
Route::post('/stok/stock-opname/{stockOpname}/approve', [StockOpnameController::class, 'approve'])->name('stock-opnames.approve')->middleware('permission:inventory.opname.manage');
Route::post('/stok/stock-opname/{stockOpname}/reject', [StockOpnameController::class, 'reject'])->name('stock-opnames.reject')->middleware('permission:inventory.opname.manage');

Route::get('/procurement/purchase-order/buat', [PurchaseOrderController::class, 'create'])->name('purchase-orders.create')->middleware('permission:procurement.purchase.manage');
Route::post('/procurement/purchase-order', [PurchaseOrderController::class, 'store'])->name('purchase-orders.store')->middleware('permission:procurement.purchase.manage');
Route::get('/procurement/purchase-order/{purchaseOrder}/edit', [PurchaseOrderController::class, 'edit'])->name('purchase-orders.edit')->middleware('permission:procurement.purchase.manage');
Route::put('/procurement/purchase-order/{purchaseOrder}', [PurchaseOrderController::class, 'update'])->name('purchase-orders.update')->middleware('permission:procurement.purchase.manage');
Route::post('/procurement/purchase-order/{purchaseOrder}/submit', [PurchaseOrderController::class, 'submit'])->name('purchase-orders.submit')->middleware('permission:procurement.purchase.manage');
Route::post('/procurement/purchase-order/{purchaseOrder}/approve', [PurchaseOrderController::class, 'approve'])->name('purchase-orders.approve')->middleware('permission:procurement.purchase.manage');
Route::post('/procurement/purchase-order/{purchaseOrder}/reject', [PurchaseOrderController::class, 'reject'])->name('purchase-orders.reject')->middleware('permission:procurement.purchase.manage');
Route::post('/procurement/purchase-order/{purchaseOrder}/cancel', [PurchaseOrderController::class, 'cancel'])->name('purchase-orders.cancel')->middleware('permission:procurement.purchase.manage');
Route::get('/procurement/purchase-order/{purchaseOrder}/receive', [GoodsReceiptController::class, 'create'])->name('goods-receipts.create')->middleware('permission:procurement.purchase.manage');
Route::post('/procurement/purchase-order/{purchaseOrder}/receive', [GoodsReceiptController::class, 'store'])->name('goods-receipts.store')->middleware('permission:procurement.purchase.manage');
Route::get('/procurement/purchase-order/{purchaseOrder}/payment', [PurchaseOrderController::class, 'paymentForm'])->name('purchase-orders.payment-form')->middleware('permission:procurement.purchase.manage');
Route::post('/procurement/purchase-order/{purchaseOrder}/payment', [PurchaseOrderController::class, 'storePayment'])->name('purchase-orders.pay')->middleware('permission:procurement.purchase.manage');
Route::get('/procurement/retur-pembelian/buat', [PurchaseReturnController::class, 'create'])->name('purchase-returns.create')->middleware('permission:procurement.return.manage');
Route::post('/procurement/retur-pembelian', [PurchaseReturnController::class, 'store'])->name('purchase-returns.store')->middleware('permission:procurement.return.manage');
Route::post('/procurement/retur-pembelian/{purchaseReturn}/submit', [PurchaseReturnController::class, 'submit'])->name('purchase-returns.submit')->middleware('permission:procurement.return.manage');
Route::post('/procurement/retur-pembelian/{purchaseReturn}/approve', [PurchaseReturnController::class, 'approve'])->name('purchase-returns.approve')->middleware('permission:procurement.return.manage');
Route::post('/procurement/retur-pembelian/{purchaseReturn}/reject', [PurchaseReturnController::class, 'reject'])->name('purchase-returns.reject')->middleware('permission:procurement.return.manage');

Route::get('/penjualan/retur/{salesTransaction}/buat', [SalesReturnController::class, 'create'])->name('sales-returns.create')->middleware('permission:sales.return.manage');
Route::post('/penjualan/retur/{salesTransaction}', [SalesReturnController::class, 'store'])->name('sales-returns.store')->middleware('permission:sales.return.manage');
Route::post('/penjualan/retur/{salesReturn}/submit', [SalesReturnController::class, 'submit'])->name('sales-returns.submit')->middleware('permission:sales.return.manage');
Route::post('/penjualan/retur/{salesReturn}/approve', [SalesReturnController::class, 'approve'])->name('sales-returns.approve')->middleware('permission:sales.return.manage');
Route::post('/penjualan/retur/{salesReturn}/reject', [SalesReturnController::class, 'reject'])->name('sales-returns.reject')->middleware('permission:sales.return.manage');

Route::get('/keuangan/laporan-keuangan/export', [FinancialReportExportController::class, 'export'])->name('financial-report.export')->middleware('permission:finance.report.export');
Route::post('/keuangan/laporan-keuangan/export/queue', [FinancialReportExportController::class, 'queue'])->name('financial-report.export.queue')->middleware('permission:finance.report.export');
Route::get('/keuangan/laporan-keuangan/export/status/{reportExport}', [FinancialReportExportController::class, 'status'])->name('financial-report.export.status')->middleware('permission:finance.report.export');
Route::get('/keuangan/laporan-keuangan/export/download/{reportExport}', [FinancialReportExportController::class, 'download'])->name('financial-report.export.download')->middleware('permission:finance.report.export');

Route::post('/keuangan/period-closing/close', [PeriodClosingController::class, 'close'])->name('period-closing.close')->middleware('permission:finance.period.close');
Route::post('/keuangan/period-closing/{accountingPeriod}/reopen', [PeriodClosingController::class, 'reopen'])->name('period-closing.reopen')->middleware('permission:finance.period.close');

Route::post('/keuangan/rekonsiliasi-kas-bank', [CashReconciliationController::class, 'store'])->name('cash-reconciliations.store')->middleware('permission:finance.reconciliation.manage');
Route::post('/keuangan/rekonsiliasi-kas-bank/{cashReconciliation}/submit', [CashReconciliationController::class, 'submit'])->name('cash-reconciliations.submit')->middleware('permission:finance.reconciliation.manage');
Route::post('/keuangan/rekonsiliasi-kas-bank/{cashReconciliation}/approve', [CashReconciliationController::class, 'approve'])->name('cash-reconciliations.approve')->middleware('permission:finance.reconciliation.manage');
Route::post('/keuangan/rekonsiliasi-kas-bank/{cashReconciliation}/reject', [CashReconciliationController::class, 'reject'])->name('cash-reconciliations.reject')->middleware('permission:finance.reconciliation.manage');

$workspacePermissionMap = [
    'audit-trail' => 'audit.log.view',
];

foreach (MenuHelper::getWorkspacePages() as $page) {
    if ($page['path'] === '/') {
        continue;
    }

    $route = Route::get($page['path'], [DashboardController::class, 'show'])
        ->defaults('pageKey', $page['key'])
        ->name($page['route_name']);

    if (isset($workspacePermissionMap[$page['key']])) {
        $route->middleware('permission:' . $workspacePermissionMap[$page['key']]);
    }
}
