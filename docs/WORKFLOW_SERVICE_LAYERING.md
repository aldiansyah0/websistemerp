# Workflow-Service Layering

Dokumen ini menetapkan pola arsitektur operasional ERP agar konsisten:

`Controller/Livewire -> Workflow -> Service -> Model`

## Tujuan

- Controller dan Livewire tetap tipis (I/O + validasi request).
- Workflow menjadi orkestrator use-case (alur status, urutan proses, transaksi bisnis).
- Service fokus pada domain logic reusable (stok, jurnal, period lock, cache, audit).
- Model hanya untuk persistence dan relasi data.

## Aturan Praktis

- Jangan tulis logika bisnis berat di Controller/Livewire.
- Setiap aksi operasional (store/submit/approve/reject/pay/receive) masuk lewat Workflow.
- Workflow boleh memanggil lebih dari satu Service.
- Service boleh memanggil Model, tapi tidak memanggil Controller/Livewire.
- Semua proses multi-step yang kritikal tetap atomik lewat transaksi database di service domain terkait.

## Workflow yang Aktif

- `App\Workflows\PurchaseOrderWorkflow`
- `App\Workflows\PosTransactionWorkflow`
- `App\Workflows\StockTransferWorkflow`
- `App\Workflows\StockOpnameWorkflow`
- `App\Workflows\GoodsReceiptWorkflow`
- `App\Workflows\SalesReturnWorkflow`
- `App\Workflows\PurchaseReturnWorkflow`
- `App\Workflows\PayrollWorkflow`

## Contoh Alur

### POS

1. Controller/Livewire menerima payload transaksi POS.
2. `PosTransactionWorkflow` menjalankan use-case checkout.
3. Workflow mendelegasikan ke service domain (stok, jurnal, lock periode, audit).
4. Service memproses perubahan model (`SalesTransaction`, `SalesTransactionItem`, `InventoryLedger`, dll).

### Purchase Order

1. Controller/Livewire memanggil `PurchaseOrderWorkflow`.
2. Workflow mengorkestrasi create/update/submit/approve/reject/cancel.
3. Service domain mengelola normalisasi item, invalidasi cache, audit trail, dan posting terkait.

Dokumen ini jadi baseline untuk modul baru agar struktur tetap rapi saat skala fitur bertambah.
