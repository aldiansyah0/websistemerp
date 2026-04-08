<?php

use App\Models\Category;
use App\Models\Supplier;
use Illuminate\Support\Facades\Storage;

beforeEach(function (): void {
    $this->seed();
});

test('erp backup and restore commands recover master data counts', function () {
    $backupFile = 'backups/test-backup.json';
    $categoryCountBefore = Category::query()->count();
    $supplierCountBefore = Supplier::query()->count();
    $category = Category::query()->orderBy('id')->firstOrFail();
    $supplier = Supplier::query()->orderBy('id')->firstOrFail();
    $originalCategoryName = $category->name;
    $originalSupplierName = $supplier->name;

    $this->artisan('erp:backup', [
        '--file' => $backupFile,
        '--disk' => 'local',
    ])->assertExitCode(0);

    expect(Storage::disk('local')->exists($backupFile))->toBeTrue();

    $category->update(['name' => 'BROKEN CATEGORY']);
    $supplier->update(['name' => 'BROKEN SUPPLIER']);

    expect(Category::query()->count())->toBe($categoryCountBefore)
        ->and(Supplier::query()->count())->toBe($supplierCountBefore);

    $this->artisan('erp:restore', [
        'file' => $backupFile,
        '--disk' => 'local',
        '--force' => true,
    ])->assertExitCode(0);

    expect(Category::query()->count())->toBe($categoryCountBefore)
        ->and(Supplier::query()->count())->toBe($supplierCountBefore)
        ->and(Category::query()->findOrFail($category->id)->name)->toBe($originalCategoryName)
        ->and(Supplier::query()->findOrFail($supplier->id)->name)->toBe($originalSupplierName);
});
