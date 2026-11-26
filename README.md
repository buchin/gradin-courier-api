# Gradin Courier API

REST API berbasis Laravel 13 untuk manajemen master data **Kurir (`couriers`)** tanpa UI (API-only), dibangun untuk technical test Backend Developer Gradin.

## Fitur Utama

- **Master Data Kurir**: `id`, `name`, `phone`, `email`, `vehicle_type`, `license_plate`, `level` (1–5), `is_active`, `registered_at`, `timestamps`.
- **CRUD API**: `index`, `store`, `show`, `update`, `destroy` pada `/api/couriers`.
- **Pagination**: Default 15 item per halaman (`?per_page=15`).
- **Sorting**: Default urut berdasarkan `name` ascending; dapat di-override ke `registered_at` (`?sort=registered_at`, `?sort=-registered_at`, atau `?sort_by=registered_at&sort_dir=desc`).
- **Pencarian Multi-Kata**: `?search=budi+agung` mencocokkan setiap kata terhadap `name` (mis. cocok ke `Budiono Hadi Agung`).
- **Filter Level**: `?level=2,3` mengembalikan kurir dengan level 2 atau 3 saja.
- **Validasi Lengkap**: `StoreCourierRequest` & `UpdateCourierRequest` (validasi `level` integer 1–5 serta aturan `unique` dengan `ignore` saat update) dan format respons terstruktur via `CourierResource`.
- **Seeder & Factory**: Dilengkapi `CourierFactory` dan `CourierSeeder` (termasuk data contoh `Budiono Hadi Agung` dan kurir level 1–5).

## Instalasi

```bash
git clone <repository-url> gradin-courier-api
cd gradin-courier-api
composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate --seed
php artisan serve
```

> **Catatan Database:** Secara default `.env.example` menggunakan SQLite (`DB_CONNECTION=sqlite`) sehingga aplikasi dan test langsung siap dijalankan tanpa setup server database tambahan.

## Menjalankan Test

```bash
php artisan test
```

## Dokumentasi Endpoint

Base URL: `http://127.0.0.1:8000/api`

| Method | Endpoint | Deskripsi |
| --- | --- | --- |
| `GET` | `/api/couriers` | Daftar kurir (paginated, search, filter level, sort) |
| `POST` | `/api/couriers` | Tambah kurir baru |
| `GET` | `/api/couriers/{id}` | Detail kurir berdasarkan ID |
| `PUT` / `PATCH` | `/api/couriers/{id}` | Update data kurir |
| `DELETE` | `/api/couriers/{id}` | Hapus data kurir |

### Query Parameter `GET /api/couriers`

- **Pagination**: `?page=1&per_page=15`
- **Default sort**: berdasarkan `name` ascending (`A-Z`).
- **Override sort tanggal daftar**:
  - `?sort=registered_at` (ascending) atau `?sort=-registered_at` (descending)
  - `?sort_by=registered_at&sort_dir=desc` (atau `asc`)
- **Pencarian multi-kata**:
  - `?search=budi+agung` -> mencocokkan nama seperti `Budiono Hadi Agung`
- **Filter level**:
  - `?level=2,3` -> hanya kurir dengan level 2 atau 3
- **Kombinasi**:
  - `GET /api/couriers?search=budi+agung&level=2,3&sort_by=registered_at&sort_dir=desc&per_page=10`

### Contoh Payload `POST /api/couriers`

```json
{
  "name": "Budiono Hadi Agung",
  "phone": "081234567890",
  "email": "budiono@example.com",
  "vehicle_type": "motorcycle",
  "license_plate": "L 1234 AB",
  "level": 3,
  "is_active": true,
  "registered_at": "2026-11-26T08:00:00Z"
}
```
