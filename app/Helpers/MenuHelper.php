<?php

namespace App\Helpers;

class MenuHelper
{
    public static function getMenuGroups(): array
    {
        return [
            [
                'title' => 'Core Menu',
                'items' => [
                    [
                        'key' => 'dashboard',
                        'icon' => 'dashboard',
                        'name' => 'Dashboard',
                        'path' => '/',
                        'description' => 'Dashboard utama kini berfungsi sebagai control tower retail untuk memantau penjualan, stok, pembelian, keuangan, dan kesiapan tim.',
                    ],
                    [
                        'key' => 'warehouse',
                        'icon' => 'warehouse',
                        'name' => 'Warehouse',
                        'path' => '/warehouse',
                        'description' => 'Area kerja warehouse siap diisi modul stok, mutasi barang, penerimaan, dan penyesuaian inventori.',
                    ],
                    [
                        'key' => 'outlet',
                        'icon' => 'outlets',
                        'name' => 'Daftar Outlet',
                        'path' => '/outlet',
                        'description' => 'Daftar outlet kini menjadi fondasi multi outlet untuk target sales, service level, inventory accuracy, dan assignment karyawan per cabang.',
                    ],
                    [
                        'key' => 'produk',
                        'icon' => 'products',
                        'name' => 'Master Produk',
                        'path' => '/produk',
                        'description' => 'Master produk sekarang menjadi sumber data inti untuk SKU, harga, margin, supplier utama, dan kebijakan reorder retail.',
                    ],
                    [
                        'key' => 'kategori',
                        'icon' => 'categories',
                        'name' => 'Manajemen Kategori',
                        'path' => '/kategori',
                        'description' => 'Struktur kategori produk dapat dibangun di halaman ini saat modul katalog mulai dikembangkan.',
                    ],
                    [
                        'key' => 'supplier',
                        'icon' => 'suppliers',
                        'name' => 'Supplier',
                        'path' => '/supplier',
                        'description' => 'Direktori supplier kini menampilkan vendor aktif, performa fill rate, term pembayaran, dan eksposur spend procurement.',
                    ],
                ],
            ],
            [
                'title' => 'Fitur ERP',
                'items' => [
                    [
                        'icon' => 'stock-management',
                        'name' => 'Manajemen Stok',
                        'subItems' => [
                            [
                                'key' => 'stock-summary',
                                'name' => 'Ringkasan Stok',
                                'path' => '/stok/ringkasan',
                                'description' => 'Ringkasan stok kini menarik data langsung dari inventory ledger untuk membaca valuasi, aging, cover, dan SKU kritikal per lokasi.',
                            ],
                            [
                                'key' => 'stock-mutation',
                                'name' => 'Mutasi Stok',
                                'path' => '/stok/mutasi',
                                'description' => 'Halaman mutasi stok dapat dipakai untuk perpindahan barang antar gudang, outlet, atau lokasi operasional.',
                            ],
                            [
                                'key' => 'stock-opname',
                                'name' => 'Stock Opname',
                                'path' => '/stok/stock-opname',
                                'description' => 'Stock opname dipakai untuk cycle count, approval adjustment, dan koreksi saldo stok berbasis bukti fisik.',
                            ],
                            [
                                'key' => 'store-warehouse',
                                'name' => 'Gudang Toko',
                                'path' => '/stok/gudang-toko',
                                'description' => 'Gudang toko disiapkan sebagai area pengelolaan stok yang berada di level outlet atau toko.',
                            ],
                            [
                                'key' => 'purchase-return',
                                'name' => 'Retur Pembelian',
                                'path' => '/stok/retur-pembelian',
                                'description' => 'Menu retur pembelian siap dipakai untuk proses pengembalian barang ke supplier dan koreksi stok terkait.',
                            ],
                        ],
                    ],
                    [
                        'icon' => 'procurement',
                        'name' => 'Procurement',
                        'subItems' => [
                            [
                                'key' => 'purchase-orders',
                                'name' => 'Purchase Order',
                                'path' => '/procurement/purchase-order',
                                'description' => 'Purchase order menjadi pusat kontrol pembelian retail untuk approval, ETA supplier, komitmen budget, dan receiving plan.',
                            ],
                            [
                                'key' => 'goods-receipts',
                                'name' => 'Penerimaan Barang',
                                'path' => '/procurement/penerimaan-barang',
                                'description' => 'Penerimaan barang sekarang menjadi jembatan antara purchase order approved, inbound fisik, dan posting inventory ledger.',
                            ],
                        ],
                    ],
                    [
                        'icon' => 'retail-sales',
                        'name' => 'Sales & POS',
                        'subItems' => [
                            [
                                'key' => 'customer-directory',
                                'name' => 'Customer',
                                'path' => '/penjualan/customer',
                                'description' => 'Master customer dipakai untuk invoice retail, piutang outlet, segmentasi pembelian, dan histori layanan pelanggan.',
                            ],
                            [
                                'key' => 'pos-transactions',
                                'name' => 'POS Transaction',
                                'path' => '/penjualan/pos-transaksi',
                                'description' => 'POS transaction mengelola checkout outlet, sales line items, split payment, dan pengurangan stok toko secara real time.',
                            ],
                            [
                                'key' => 'sales-invoices',
                                'name' => 'Invoice Penjualan',
                                'path' => '/penjualan/invoice',
                                'description' => 'Invoice penjualan menghubungkan transaksi outlet, customer billing, pembayaran parsial, dan monitoring piutang retail.',
                            ],
                            [
                                'key' => 'sales-return',
                                'name' => 'Retur Penjualan',
                                'path' => '/penjualan/retur-penjualan',
                                'description' => 'Retur penjualan menangani refund customer dengan reverse jurnal serta pengembalian stok ke gudang outlet.',
                            ],
                        ],
                    ],
                    [
                        'icon' => 'hrd-module',
                        'name' => 'HRD Module',
                        'subItems' => [
                            [
                                'key' => 'employee-management',
                                'name' => 'Kelola Karyawan',
                                'path' => '/hrd/kelola-karyawan',
                                'description' => 'Kelola data karyawan lintas outlet lengkap dengan department, jabatan, status kerja, dan eksposur gaji.',
                            ],
                            [
                                'key' => 'attendance-log',
                                'name' => 'Log Absensi',
                                'path' => '/hrd/log-absensi',
                                'description' => 'Log absensi kini membaca kehadiran, keterlambatan, dan overtime per shift agar HR dan operasional outlet sinkron.',
                            ],
                            [
                                'key' => 'shift-attendance',
                                'name' => 'Absensi Shift',
                                'path' => '/hrd/absensi-shift',
                                'description' => 'Halaman absensi shift dapat digunakan untuk pemantauan presensi berdasarkan jadwal dan shift kerja.',
                            ],
                            [
                                'key' => 'schedule-request',
                                'name' => 'Pengajuan Jadwal',
                                'path' => '/hrd/pengajuan-jadwal',
                                'description' => 'Pengajuan jadwal disiapkan untuk proses permintaan perubahan atau penyesuaian jadwal kerja.',
                            ],
                            [
                                'key' => 'leave-request',
                                'name' => 'Pengajuan Cuti',
                                'path' => '/hrd/pengajuan-cuti',
                                'description' => 'Menu pengajuan cuti siap dipakai untuk proses approval cuti tahunan, sakit, atau izin khusus.',
                            ],
                            [
                                'key' => 'payroll-list',
                                'name' => 'Daftar Penggajian',
                                'path' => '/hrd/daftar-penggajian',
                                'description' => 'Daftar penggajian kini memantau payroll run, gross to net salary, dan readiness pembayaran karyawan.',
                            ],
                            [
                                'key' => 'resign-data',
                                'name' => 'Data Resign',
                                'path' => '/hrd/data-resign',
                                'description' => 'Data resign siap digunakan untuk pengelolaan pengunduran diri, exit process, dan arsip status karyawan.',
                            ],
                        ],
                    ],
                    [
                        'icon' => 'employee-portal',
                        'name' => 'Portal Karyawan',
                        'subItems' => [
                            [
                                'key' => 'my-home',
                                'name' => 'Beranda Saya',
                                'path' => '/portal-karyawan/beranda',
                                'description' => 'Beranda karyawan menjadi titik masuk personal untuk informasi kerja, notifikasi, dan ringkasan aktivitas.',
                            ],
                            [
                                'key' => 'my-leave',
                                'name' => 'Cuti Saya',
                                'path' => '/portal-karyawan/cuti',
                                'description' => 'Halaman cuti saya dapat menampilkan saldo cuti, histori, dan status permohonan terbaru.',
                            ],
                            [
                                'key' => 'my-schedule',
                                'name' => 'Jadwal Saya',
                                'path' => '/portal-karyawan/jadwal',
                                'description' => 'Jadwal saya siap diisi dengan roster harian, shift mingguan, dan agenda kerja personal.',
                            ],
                            [
                                'key' => 'salary-slip',
                                'name' => 'Slip Gaji',
                                'path' => '/portal-karyawan/slip-gaji',
                                'description' => 'Slip gaji karyawan dapat menampung histori payroll dan detail komponen penghasilan tiap periode.',
                            ],
                            [
                                'key' => 'resign-request',
                                'name' => 'Pengajuan resign',
                                'path' => '/portal-karyawan/pengajuan-resign',
                                'description' => 'Pengajuan resign disiapkan sebagai alur mandiri bagi karyawan untuk mengajukan pengunduran diri.',
                            ],
                        ],
                    ],
                    [
                        'icon' => 'finance',
                        'name' => 'Keuangan',
                        'subItems' => [
                            [
                                'key' => 'financial-report',
                                'name' => 'Laporan Keuangan',
                                'path' => '/keuangan/laporan-keuangan',
                                'description' => 'Laporan keuangan siap dikembangkan untuk neraca, laba rugi, arus kas, dan ringkasan performa bisnis.',
                            ],
                            [
                                'key' => 'cashflow',
                                'name' => 'Cashflow',
                                'path' => '/keuangan/cashflow',
                                'description' => 'Cashflow menghubungkan sales collection, settlement payment, payroll payout, dan kewajiban supplier dalam satu pandangan arus kas.',
                            ],
                            [
                                'key' => 'receivables-payables',
                                'name' => 'Hutang / Piutang',
                                'path' => '/keuangan/hutang-piutang',
                                'description' => 'Hutang dan piutang memantau invoice customer, saldo tertagih, kewajiban supplier, dan due date cash planning.',
                            ],
                            [
                                'key' => 'split-payment',
                                'name' => 'Split Payment',
                                'path' => '/keuangan/split-payment',
                                'description' => 'Split payment console memantau transaksi POS multi metode pembayaran, fee gateway, dan settlement harian per outlet.',
                            ],
                            [
                                'key' => 'period-closing',
                                'name' => 'Period Closing',
                                'path' => '/keuangan/period-closing',
                                'description' => 'Period closing mengunci posting transaksi per periode agar laporan keuangan tetap konsisten dan audit-ready.',
                            ],
                            [
                                'key' => 'cash-reconciliation',
                                'name' => 'Rekonsiliasi Kas/Bank',
                                'path' => '/keuangan/rekonsiliasi-kas-bank',
                                'description' => 'Rekonsiliasi kas bank membandingkan mutasi expected vs kas fisik untuk deteksi selisih harian outlet.',
                            ],
                            [
                                'key' => 'audit-trail',
                                'name' => 'Audit Trail',
                                'path' => '/keuangan/audit-trail',
                                'description' => 'Audit trail menampilkan histori aksi approval, posting, reject, dan workflow kritikal untuk kebutuhan kontrol internal.',
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    public static function getWorkspacePages(): array
    {
        $pages = [];

        foreach (self::getMenuGroups() as $group) {
            foreach ($group['items'] as $item) {
                if (isset($item['path'])) {
                    $pages[$item['key']] = self::buildPageData($item, $group['title']);
                }

                foreach ($item['subItems'] ?? [] as $subItem) {
                    $pages[$subItem['key']] = self::buildPageData($subItem, $item['name']);
                }
            }
        }

        return $pages;
    }

    public static function findPage(string $key): ?array
    {
        return self::getWorkspacePages()[$key] ?? null;
    }

    public static function getPaths(): array
    {
        return array_values(array_map(
            static fn(array $page): string => $page['path'],
            self::getWorkspacePages()
        ));
    }

    public static function isActive(string $path): bool
    {
        return trim(request()->getPathInfo(), '/') === trim($path, '/');
    }

    public static function hasActiveSubItems(array $subItems): bool
    {
        foreach ($subItems as $subItem) {
            if (isset($subItem['path']) && self::isActive($subItem['path'])) {
                return true;
            }
        }

        return false;
    }

    public static function getIconSvg(string $iconName): string
    {
        $icons = [
            'dashboard' => '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M4.75 6.75C4.75 5.64543 5.64543 4.75 6.75 4.75H10.25C11.3546 4.75 12.25 5.64543 12.25 6.75V10.25C12.25 11.3546 11.3546 12.25 10.25 12.25H6.75C5.64543 12.25 4.75 11.3546 4.75 10.25V6.75Z" stroke="currentColor" stroke-width="1.5"/><path d="M11.75 17.25C11.75 15.8693 10.6307 14.75 9.25 14.75H7.75C6.09315 14.75 4.75 16.0931 4.75 17.75V19.25H11.75V17.25Z" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"/><path d="M15.25 5.75H19.25" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/><path d="M15.25 9.25H19.25" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/><path d="M15.25 14.75H19.25" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/><path d="M15.25 18.25H19.25" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>',
            'warehouse' => '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M3.75 9.25L12 4.75L20.25 9.25V18.25C20.25 18.8023 19.8023 19.25 19.25 19.25H4.75C4.19772 19.25 3.75 18.8023 3.75 18.25V9.25Z" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"/><path d="M8.25 19.25V12.75H15.75V19.25" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"/><path d="M3.75 9.25L12 13.25L20.25 9.25" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"/></svg>',
            'outlets' => '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M5.75 10.75H18.25V18.25C18.25 18.8023 17.8023 19.25 17.25 19.25H6.75C6.19772 19.25 5.75 18.8023 5.75 18.25V10.75Z" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"/><path d="M4.75 10.75L6.25 5.75H17.75L19.25 10.75" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"/><path d="M9 14.75H15" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/><path d="M9 19.25V15.75" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/><path d="M15 19.25V15.75" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>',
            'products' => '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M6.75 5.75H17.25C18.3546 5.75 19.25 6.64543 19.25 7.75V16.25C19.25 17.3546 18.3546 18.25 17.25 18.25H6.75C5.64543 18.25 4.75 17.3546 4.75 16.25V7.75C4.75 6.64543 5.64543 5.75 6.75 5.75Z" stroke="currentColor" stroke-width="1.5"/><path d="M8.25 9.25H15.75" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/><path d="M8.25 12.25H15.75" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/><path d="M8.25 15.25H12.75" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>',
            'categories' => '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M5.75 5.75H10.25V10.25H5.75V5.75Z" stroke="currentColor" stroke-width="1.5"/><path d="M13.75 5.75H18.25V10.25H13.75V5.75Z" stroke="currentColor" stroke-width="1.5"/><path d="M5.75 13.75H10.25V18.25H5.75V13.75Z" stroke="currentColor" stroke-width="1.5"/><path d="M13.75 13.75H18.25V18.25H13.75V13.75Z" stroke="currentColor" stroke-width="1.5"/></svg>',
            'suppliers' => '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M7.75 8.25C7.75 6.59315 9.09315 5.25 10.75 5.25H13.25C14.9069 5.25 16.25 6.59315 16.25 8.25C16.25 9.90685 14.9069 11.25 13.25 11.25H10.75C9.09315 11.25 7.75 9.90685 7.75 8.25Z" stroke="currentColor" stroke-width="1.5"/><path d="M4.75 18.75C4.75 15.9886 6.98858 13.75 9.75 13.75H14.25C17.0114 13.75 19.25 15.9886 19.25 18.75V19.25H4.75V18.75Z" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"/></svg>',
            'stock-management' => '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M4.75 7.25L12 4.25L19.25 7.25L12 10.25L4.75 7.25Z" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"/><path d="M4.75 7.25V16.75L12 19.75L19.25 16.75V7.25" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"/><path d="M12 10.25V19.75" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"/></svg>',
            'procurement' => '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M6.75 5.75H17.25C18.3546 5.75 19.25 6.64543 19.25 7.75V16.25C19.25 17.3546 18.3546 18.25 17.25 18.25H6.75C5.64543 18.25 4.75 17.3546 4.75 16.25V7.75C4.75 6.64543 5.64543 5.75 6.75 5.75Z" stroke="currentColor" stroke-width="1.5"/><path d="M8.25 9.25H15.75" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/><path d="M8.25 12.25H13.75" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/><path d="M15.75 14.75L17.25 16.25L20 13.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>',
            'retail-sales' => '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M6.75 6.75H17.25C18.3546 6.75 19.25 7.64543 19.25 8.75V17.25C19.25 18.3546 18.3546 19.25 17.25 19.25H6.75C5.64543 19.25 4.75 18.3546 4.75 17.25V8.75C4.75 7.64543 5.64543 6.75 6.75 6.75Z" stroke="currentColor" stroke-width="1.5"/><path d="M8.25 10.25H15.75" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/><path d="M8.25 13.25H12.25" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/><path d="M14.75 4.75V8.25" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/><path d="M9.25 4.75V8.25" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>',
            'hrd-module' => '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M8 8.25C8 6.73122 9.23122 5.5 10.75 5.5H13.25C14.7688 5.5 16 6.73122 16 8.25C16 9.76878 14.7688 11 13.25 11H10.75C9.23122 11 8 9.76878 8 8.25Z" stroke="currentColor" stroke-width="1.5"/><path d="M4.75 18.75C4.75 15.8505 7.10051 13.5 10 13.5H14C16.8995 13.5 19.25 15.8505 19.25 18.75V19.25H4.75V18.75Z" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"/><path d="M18.25 6.5H20.25" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/><path d="M19.25 5.5V7.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>',
            'employee-portal' => '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M6.75 19.25H17.25C18.3546 19.25 19.25 18.3546 19.25 17.25V6.75C19.25 5.64543 18.3546 4.75 17.25 4.75H6.75C5.64543 4.75 4.75 5.64543 4.75 6.75V17.25C4.75 18.3546 5.64543 19.25 6.75 19.25Z" stroke="currentColor" stroke-width="1.5"/><path d="M8.25 9H15.75" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/><path d="M8.25 12H13.75" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/><path d="M8.25 15H11.75" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>',
            'finance' => '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M5.75 18.25H18.25" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/><path d="M7.75 15.75V10.25" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/><path d="M12 15.75V7.25" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/><path d="M16.25 15.75V12.25" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>',
        ];

        return $icons[$iconName] ?? '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" aria-hidden="true"><circle cx="12" cy="12" r="7.25" stroke="currentColor" stroke-width="1.5"/><path d="M12 8.75V12.25L14.25 14.25" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>';
    }

    private static function buildPageData(array $item, string $eyebrow): array
    {
        return [
            'key' => $item['key'],
            'title' => $item['name'],
            'path' => $item['path'],
            'eyebrow' => $eyebrow,
            'description' => $item['description'] ?? 'Halaman ini siap dikembangkan sebagai modul kerja baru di WebStellar ERP.',
            'route_name' => $item['key'],
        ];
    }
}
