# ERP Security Baseline

## Header Security
Middleware `SecurityHeaders` menambahkan:
- `X-Frame-Options: SAMEORIGIN`
- `X-Content-Type-Options: nosniff`
- `Referrer-Policy: strict-origin-when-cross-origin`
- `Permissions-Policy: camera=(), microphone=(), geolocation=()`
- `X-XSS-Protection: 0`
- `Strict-Transport-Security` saat HTTPS aktif

## Access Control
- RBAC berbasis permission aktif pada route kritikal (`permission:*`).
- Halaman `Audit Trail` hanya untuk role dengan permission `audit.log.view`.

## Monitoring & Alert
- Queue monitor command: `php artisan erp:queue:monitor`
- Channel alert: `alerts` (siap dihubungkan ke Slack/Sentry)

## Validasi Otomatis
- Test header security: `tests/Feature/SecurityHeadersTest.php`
- Workflow CI: `.github/workflows/ci.yml`

