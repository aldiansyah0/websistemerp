# ERP Backup & Restore SOP

## Tujuan
Menjamin data ERP retail dapat dipulihkan cepat saat terjadi insiden aplikasi, human error, atau kerusakan infrastruktur.

## Backup Harian
Jalankan perintah:

```bash
php artisan erp:backup --file=backups/daily-20260408-233000.json
```

Output menyertakan:
- lokasi file backup
- jumlah tabel
- jumlah baris data

## Verifikasi Integritas Backup
Setelah backup terbentuk, validasi checksum:

```bash
php artisan erp:backup:verify backups/daily-20260408-233000.json
```

Jika checksum tidak valid, file dianggap korup/berubah dan wajib buat backup baru.

## Restore
Untuk restore dari file tertentu:

```bash
php artisan erp:restore backups/daily-20260408-233000.json --force
```

Catatan:
- di environment production, restore wajib `--force`
- restore akan memprioritaskan replace snapshot per tabel; jika terkunci constraint relasi, sistem fallback ke mode upsert agar proses tetap aman dan tidak gagal total

## Checklist Operasional
1. Validasi backup selesai tanpa error.
2. Jalankan verifikasi checksum (`erp:backup:verify`).
3. Uji restore berkala di environment staging.
4. Simpan file backup ke storage sekunder/offsite.
5. Jalankan pruning retensi sesuai kebijakan:

```bash
php artisan erp:backup:prune --keep-days=14
```

6. Catat hasil uji restore (waktu proses, row count, hasil sampling data).
7. Aktifkan alert bila backup atau restore gagal.

## Scheduling Rekomendasi (Cron)
```bash
# backup tiap hari jam 23:30
30 23 * * * php artisan erp:backup --file=backups/daily-$(date +\%Y\%m\%d-\%H\%M\%S).json

# verify backup terbaru jam 23:35
35 23 * * * php artisan erp:backup:verify backups/daily-$(date +\%Y\%m\%d)-233000.json

# pruning backup lama jam 00:10
10 0 * * * php artisan erp:backup:prune --keep-days=14
```

## Queue & Alert Operasional
```bash
# worker untuk report/export
php artisan erp:work-reports

# monitoring queue report (non-zero exit code jika threshold terlampaui)
php artisan erp:queue:monitor
```

Alert dikirim lewat channel `alerts` dan dapat dihubungkan ke Slack/Sentry.

## Validasi Teknis
SOP ini sudah divalidasi melalui automated test:
- `tests/Feature/BackupRestoreCommandTest.php`
- `tests/Feature/QueueOpsCommandTest.php`
- `tests/Feature/AuditTrailAccessTest.php`
2. Simpan file backup ke storage sekunder/offsite.
3. Uji restore berkala di environment staging.
4. Catat hasil uji restore (waktu proses, row count, hasil sampling data).
5. Aktifkan alert bila backup job gagal.

## Validasi Teknis
SOP ini sudah divalidasi dengan automated test:
- `tests/Feature/BackupRestoreCommandTest.php`
