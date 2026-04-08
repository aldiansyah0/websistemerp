<?php

use App\Services\RetailDashboardService;

beforeEach(function (): void {
    $this->seed();
});

test('retail dashboard service calculates live kpis from the database', function () {
    $dashboard = app(RetailDashboardService::class)->build();
    $metrics = collect($dashboard['executiveKpis'])->keyBy('label');

    expect($dashboard['heroStats'])->toHaveCount(4)
        ->and($metrics)->toHaveKeys([
            'Inventory Value',
            'On-Hand Availability',
            'Average Service Level',
            'Attendance Readiness',
            'Split Payment Share',
            'Pending Approval Value',
            'Approved Inbound',
            'Payroll Exposure',
            'Supplier Fill Rate',
            'Aging Stock > 30 Hari',
        ])
        ->and($metrics['Inventory Value']['value'])->toContain('Rp')
        ->and($metrics['Supplier Fill Rate']['value'])->toContain('%');
});

test('retail dashboard service exposes live queue alert and module sections', function () {
    $dashboard = app(RetailDashboardService::class)->build();

    expect($dashboard['replenishmentQueue'])->not->toBeEmpty()
        ->and($dashboard['procurementQueue'])->not->toBeEmpty()
        ->and($dashboard['categoryMix'])->toHaveCount(5)
        ->and($dashboard['warehouseCards'])->toHaveCount(4)
        ->and($dashboard['outletCards'])->toHaveCount(4)
        ->and($dashboard['workforceCards'])->toHaveCount(4)
        ->and($dashboard['paymentChannelCards'])->toHaveCount(4)
        ->and($dashboard['supplierCards'])->toHaveCount(4)
        ->and($dashboard['moduleMap'])->toHaveCount(7)
        ->and($dashboard['formulaCards'])->toHaveCount(8)
        ->and($dashboard['alerts'])->not->toBeEmpty();
});
