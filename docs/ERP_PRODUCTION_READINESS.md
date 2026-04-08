# ERP Production Readiness Runbook

## 1) Queue Worker Policy (Report/Export)
Gunakan worker khusus antrean report agar request user tidak menunggu proses export besar.

```bash
php artisan erp:work-reports
```

Konfigurasi utama ada di:
- `config/erp.php` pada `queue.reports`
- `.env` variabel `ERP_REPORT_WORKER_*`

Policy default:
- retries: 3x
- backoff: 10 detik
- timeout: 300 detik
- recycle worker: 100 jobs / 3600 detik

## 2) Queue Monitoring & Alert
Monitoring health antrean:

```bash
php artisan erp:queue:monitor
```

Jika pending/failed melebihi threshold, command return exit code `1` dan log critical ke channel `alerts`.

Threshold diatur via:
- `ERP_QUEUE_PENDING_THRESHOLD`
- `ERP_QUEUE_FAILED_THRESHOLD`
- `ERP_QUEUE_FAILED_WINDOW_MINUTES`

## 3) Error Monitoring
Error unhandled sudah dikirim ke:
- `alerts` log channel
- Sentry (jika `SENTRY_LARAVEL_DSN` terisi)

Lihat:
- `bootstrap/app.php`
- `config/logging.php`
- `config/services.php`

## 4) Auditability
Audit trail tersedia di modul:
- Menu `Keuangan > Audit Trail`
- Route: `/keuangan/audit-trail`

Akses dibatasi permission:
- `audit.log.view`

## 5) Backup & Restore SOP
Detail SOP:
- `docs/ERP_BACKUP_RESTORE_SOP.md`

