<?php

use App\Services\RetailOperationsService;

beforeEach(function (): void {
    $this->seed();
});

test('retail operations service resolves the database-backed operational modules', function () {
    $service = app(RetailOperationsService::class);

    $warehousePage = $service->resolve('warehouse');
    $categoryPage = $service->resolve('kategori');
    $outletPage = $service->resolve('outlet');
    $productPage = $service->resolve('produk');
    $inventoryPage = $service->resolve('stock-summary');
    $supplierPage = $service->resolve('supplier');
    $purchaseOrderPage = $service->resolve('purchase-orders');
    $posPage = $service->resolve('pos-transactions');
    $employeePage = $service->resolve('employee-management');
    $attendancePage = $service->resolve('attendance-log');
    $payrollPage = $service->resolve('payroll-list');
    $splitPaymentPage = $service->resolve('split-payment');

    expect($warehousePage['view'])->toBe('pages.operations.connected-board')
        ->and($warehousePage['data']['mainDescription'])->not->toBeEmpty()
        ->and($categoryPage['view'])->toBe('pages.operations.livewire.category-directory')
        ->and($categoryPage['data']['categories'])->not->toBeEmpty()
        ->and($outletPage['view'])->toBe('pages.operations.outlet-directory')
        ->and($productPage['view'])->toBe('pages.operations.livewire.product-catalog')
        ->and($inventoryPage['view'])->toBe('pages.operations.inventory-summary')
        ->and($supplierPage['view'])->toBe('pages.operations.livewire.supplier-directory')
        ->and($purchaseOrderPage['view'])->toBe('pages.operations.livewire.purchase-orders')
        ->and($posPage['view'])->toBe('pages.operations.livewire.pos-transactions')
        ->and($employeePage['view'])->toBe('pages.operations.employee-management')
        ->and($attendancePage['view'])->toBe('pages.operations.attendance-log')
        ->and($payrollPage['view'])->toBe('pages.operations.payroll-list')
        ->and($splitPaymentPage['view'])->toBe('pages.operations.split-payment');
});

test('retail operations service exposes live operations hr and payment datasets', function () {
    $service = app(RetailOperationsService::class);

    $outletPage = $service->outletDirectoryData();
    $productPage = $service->productCatalogData();
    $categoryPage = $service->categoryDirectoryData();
    $inventoryPage = $service->inventorySummaryData();
    $supplierPage = $service->supplierDirectoryData();
    $purchaseOrderPage = $service->purchaseOrderIndexData();
    $employeePage = $service->employeeManagementData();
    $attendancePage = $service->attendanceLogData();
    $payrollPage = $service->payrollListData();
    $splitPaymentPage = $service->splitPaymentData();

    expect($outletPage['outlets'])->toHaveCount(5)
        ->and($outletPage['performanceCards'])->toHaveCount(3)
        ->and($productPage['products'])->toHaveCount(10)
        ->and($productPage['metrics'])->toHaveCount(6)
        ->and($categoryPage['categories'])->toHaveCount(5)
        ->and($inventoryPage['agingBuckets'])->toHaveCount(4)
        ->and($inventoryPage['criticalItems'])->not->toBeEmpty()
        ->and($supplierPage['suppliers'])->toHaveCount(5)
        ->and($purchaseOrderPage['purchaseOrders'])->toHaveCount(6)
        ->and($purchaseOrderPage['spendMix'])->not->toBeEmpty()
        ->and($employeePage['employees'])->toHaveCount(12)
        ->and($attendancePage['logs'])->toHaveCount(11)
        ->and($payrollPage['payrollRuns'])->toHaveCount(1)
        ->and($splitPaymentPage['paymentMethods'])->toHaveCount(7)
        ->and($splitPaymentPage['transactions'])->toHaveCount(8);
});
