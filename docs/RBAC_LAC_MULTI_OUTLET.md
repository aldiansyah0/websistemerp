# RBAC + LAC Multi-Outlet

Dokumen ini menjelaskan implementasi akses hybrid:

- **RBAC (Role-Based Access Control):** menentukan fitur/modul yang boleh diakses.
- **LAC (Location Access Control):** menentukan data outlet/gudang mana yang boleh dilihat.

## Scope User

Kolom baru pada `users`:

- `access_scope`: `all_locations`, `assigned_locations`, `single_location`
- `active_location_id`: konteks outlet/gudang aktif

Pivot baru:

- `user_locations` untuk assignment multi lokasi per user.

## Pola Akses

- `all_locations`
  - Tidak dibatasi scope lokasi (tetap tenant-scoped).
  - Cocok untuk `owner`, `super_admin`.
- `assigned_locations`
  - Jika `active_location_id` terisi: hanya lokasi aktif.
  - Jika `active_location_id` kosong: semua lokasi yang di-assign di `user_locations`.
  - Cocok untuk `manager`, `hrd`, `finance` multi outlet.
- `single_location`
  - Terkunci pada satu lokasi aktif.
  - Cocok untuk `admin cabang`, `staff admin`, `cashier`, `staff outlet`.

## Role Hierarchy

Role sistem yang aktif:

- `owner`
- `manager`
- `hrd`
- `super_admin`
- `admin`
- `staff_admin`
- `cashier`
- `staff_outlet`

Role kompatibilitas lama tetap tersedia:

- `warehouse_manager`
- `finance`

## Outlet Switcher

Endpoint:

- `POST /session/active-location` (`active-location.switch`)

Komponen:

- Header menampilkan dropdown lokasi aktif untuk user yang bisa switch.
- Validasi akses ditangani oleh `LocationAccessService`.

## File Inti

- Scope model: `app/Models/Scopes/TenantLocationScope.php`
- Model user: `app/Models/User.php`
- Service switching: `app/Services/LocationAccessService.php`
- Controller switch: `app/Http/Controllers/LocationAccessController.php`
- Seeder role & mapping: `database/seeders/RolePermissionSeeder.php`
