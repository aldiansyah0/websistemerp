# ERP Service Pattern Guide

## 1. Prinsip Arsitektur
- Controller/Livewire hanya untuk request-response.
- Gunakan alur berlapis: `Controller/Livewire -> Workflow -> Service -> Model`.
- Semua logika bisnis berat ditempatkan di `app/Services`.
- Gunakan transaksi database (`DB::transaction`) untuk workflow lintas tabel.

## 2. StockService
- File: `app/Services/StockService.php`
- Tanggung jawab:
  - hitung saldo stok realtime (`currentBalance`, `currentLocationBalance`)
  - validasi minimum stock (`validateMinimumStock`)
  - posting mutasi stok ke `inventory_ledgers` + mirror ke `stock_mutations`
- Cara pakai:
  - injeksikan `StockService` ke service domain (`SalesTransactionService`, `GoodsReceiptService`, `StockTransferService`)
  - jangan panggil query ledger langsung dari controller.

## 2A. POS Barcode-Centric + Atomic
- UI POS berada di `resources/views/pages/operations/pos-transaction-form.blade.php` dan menggunakan Alpine.js untuk keranjang sisi klien.
- Scan barcode/SKU langsung menambah item ke cart tanpa request server.
- Split payment disimpan pada tabel `transaction_payments` (satu transaksi bisa multi metode pembayaran).
- `SalesTransactionService` menggunakan `DB::beginTransaction()` sehingga posting stok, payment, dan jurnal akuntansi terjadi atomik.
- Jika salah satu langkah gagal, seluruh transaksi di-rollback.

## 3. Eager Loading Global
- Gunakan eager loading default di model (`protected $with = [...]`) untuk relasi yang sering dipakai.
- `AppServiceProvider` mengaktifkan `Model::preventLazyLoading()` (non-production) untuk mendeteksi N+1 lebih awal.

## 4. Global Scope Tenant/Lokasi
- Trait: `app/Models/Concerns/BelongsToTenantLocation.php`
- Scope: `app/Models/Scopes/TenantLocationScope.php`
- Terapkan trait di model bisnis agar query otomatis tersaring berdasarkan:
  - `tenant_id`
  - `location_id` (jika model location-scoped)
- Konfigurasi model:
  - `protected bool $locationScoped = true|false;`

## 5. Caching Strategy
- Service: `app/Services/AnalyticsCacheService.php`
- Data yang di-cache:
  - dashboard (`RetailDashboardService`)
  - laporan keuangan/cashflow/AR-AP/split payment (`RetailOperationsService`)
- Store cache:
  - `ERP_CACHE_STORE=redis` (recommended) atau `file`
- Invalidasi:
  - panggil `$analyticsCacheService->invalidate()` setelah operasi write (POS, PO, GR, payroll, master data CRUD).

## 6. Checklist Fitur Baru
- Tambah service baru untuk logika berat.
- Tambah/cek eager loading default model terkait.
- Pastikan model sudah tenant-scoped/location-scoped sesuai kebutuhan.
- Gunakan cache untuk agregasi besar.
- Tambahkan invalidasi cache saat write.
