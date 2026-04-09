# ERP Variant-Centric Architecture

## Tujuan
Mengarahkan transaksi operasional retail ke level SKU (`product_variants`) agar stok, costing, dan laporan lebih akurat untuk ukuran/warna/kemasan berbeda.

## Implementasi yang Sudah Aktif
- Tabel operasional sudah memiliki `product_variant_id`:
  - `inventory_ledgers`
  - `purchase_order_items`
  - `sales_transaction_items`
  - `stock_transfer_items`
  - `goods_receipt_items`
  - `stock_opname_items`
  - `sales_return_items`
  - `purchase_return_items`
- Backfill otomatis dari default variant setiap produk.
- Service transaksi (`POS`, `PO`, `Receiving`, `Transfer`, `Opname`, `Sales Return`, `Purchase Return`) sudah menulis `product_variant_id`.
- `StockService` otomatis resolve default variant jika request masih mengirim `product_id` saja (backward compatible).

## Dampak Bisnis
- Akurasi stok per SKU naik.
- Dasar analitik margin per variant menjadi siap.
- Migrasi dari alur lama (`product_id only`) tetap aman tanpa memutus UI existing.

## Rekomendasi Lanjutan
- UI form operasional menampilkan pilihan variant langsung (bukan hanya produk induk).
- Dashboard menambah KPI `Top Variant`, `Stock Cover per Variant`, dan `Variant Sell-through`.

