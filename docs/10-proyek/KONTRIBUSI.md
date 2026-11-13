# Panduan Kontribusi & Standar Pengembangan — SiswaMart

| Informasi Dokumen | Detail |
| :--- | :--- |
| **Judul** | Panduan Kontribusi, Konvensi Kode, dan Alur Pengembangan SiswaMart |
| **Versi** | `1.1.0` |
| **Tanggal** | 27 Oktober 2026 |
| **Status** | Final (Source-Code Backed) |
| **Penulis** | Tim Dokumentasi Teknis & Rekayasa Perangkat Lunak |
| **Sumber Data** | `composer.json`, `app/`, `database/migrations/`, `tests/` |

---

## Daftar Isi

1. [Standar Gaya Kode (Coding Standards)](#1-standar-gaya-kode-coding-standards)
2. [Konvensi Penamaan (Naming Conventions)](#2-konvensi-penamaan-naming-conventions)
3. [Alur Kerja Git & Branching](#3-alur-kerja-git--branching)
4. [Panduan Menambahkan Fitur Baru](#4-panduan-menambahkan-fitur-baru)
5. [Panduan Membuat Migrasi Database yang Aman](#5-panduan-membuat-migrasi-database-yang-aman)
6. [Panduan Menulis dan Menjalankan Pengujian (`DatabaseTransactions`)](#6-panduan-menulis-dan-menjalankan-pengujian-databasetransactions)
7. [Rujukan Kode](#7-rujukan-kode)

---

## 1. Standar Gaya Kode (Coding Standards)

Proyek SiswaMart mengikuti standar **PSR-12** dan konvensi resmi Laravel 13:
1. **Pemformat Kode Otomatis (`Laravel Pint`):**
   Sebelum melakukan commit, jalankan Laravel Pint (`composer.json#L23`) untuk merapikan format kode PHP:
   ```bash
   ./vendor/bin/pint
   ```
2. **Pengetikan Ketat & Return Types:**
   Gunakan *type-hint* pada parameter metode dan deklarasi tipe kembalian (`JsonResponse`, `RedirectResponse`, `View`, `Response`, `bool`, `array`) seperti yang diterapkan di `app/Http/Controllers/Api/` dan `app/Services/`.
3. **Komentar dan Dokumentasi:**
   Pertahankan docblock PHPDoc yang menjelaskan struktur array/koleksi dan jangan meninggalkan kode mati (*commented-out code*) atau nilai rahasia di dalam kode sumber.

---

## 2. Konvensi Penamaan (Naming Conventions)

Proyek menggunakan perpaduan istilah Bahasa Indonesia untuk entitas domain bisnis dan Bahasa Inggris untuk komponen teknis/API:

| Komponen | Konvensi Penamaan | Contoh Aktual di Kode Sumber |
| :--- | :--- | :--- |
| **Model & Tabel Domain Inti** | Bahasa Indonesia (`PascalCase` untuk Model, `snake_case` tunggal untuk tabel melalui `$table`) | Model `Produk` (`$table = 'produk'`), `Penjual` (`$table = 'penjual'`), `Kategori` (`$table = 'kategori'`), `Ulasan` (`$table = 'ulasan'`). |
| **Kontroler Web** | Dikelompokkan berdasarkan namespace peran (`Admin\`, `Penjual\`, `Public\`, `Auth\`) | `Admin\ProdukController`, `Penjual\ProdukController`, `Public\ProdukController`. |
| **Kontroler & Rute REST API** | Bahasa Inggris di dalam namespace `App\Http\Controllers\Api\` dengan nama rute berawalan `api.` | `Api\ProductController` (`api.products.index`), `Api\SellerProductController` (`api.seller.products.store`). |
| **Nama Rute Web** | Berawalan konteks peran (`public.*`, `penjual.*`, `admin.*`) | `public.produk.show`, `penjual.produk.store`, `admin.produk.update_status`. |
| **Variabel CSS Desain** | Berawalan `--sm-*` di `siswamart-theme.css` | `--sm-primary`, `--sm-ink`, `--sm-accent`, `--sm-surface`. |

---

## 3. Alur Kerja Git & Branching

1. **Branch Utama (`main`):** Selalu dalam kondisi stabil, teruji, dan siap di-deploy.
2. **Penamaan Branch Fitur / Perbaikan:**
   - Fitur baru: `feat/<nama-fitur>` (contoh: `feat/badwords-admin-ui`)
   - Perbaikan bug: `fix/<nama-bug>` (contoh: `fix/kategori-price-filter`)
   - Dokumentasi: `docs/<topik>` (contoh: `docs/update-srs-erd`)
3. **Format Pesan Commit (*Conventional Commits*):**
   - `feat: ...` untuk penambahan fitur baru.
   - `fix: ...` untuk perbaikan bug.
   - `docs: ...` untuk pembaruan dokumentasi.
   - `test: ...` untuk penambahan/perbaikan pengujian otomatis.

---

## 4. Panduan Menambahkan Fitur Baru

Saat menambahkan fitur baru ke SiswaMart, ikuti pola arsitektur berlapis yang sudah ada:
1. **Logika Bisnis Lintas-Kontroler $\rightarrow$ Simpan di `app/Services/`:**
   Jika sebuah logika digunakan baik oleh kontroler Web maupun API (seperti moderasi ulasan atau notifikasi admin), tempatkan logikanya di `app/Services/` dan panggil melalui *dependency injection*.
2. **Perubahan Produk Penjual $\rightarrow$ Hormati Alur Moderasi (`pending_changes`):**
   Setiap fitur yang mengubah data publik produk milik penjual wajib menghormati status moderasi (`BR-PROD-02` s.d. `BR-PROD-05`) dan memicu `AdminNotificationService`.
3. **Respons REST API $\rightarrow$ Gunakan `ApiController`:**
   Warisi `App\Http\Controllers\Api\ApiController` dan gunakan `$this->success(...)` serta `$this->error(...)` agar amplop JSON tetap konsisten.

---

## 5. Panduan Membuat Migrasi Database yang Aman

1. **Jangan Mengubah Migrasi Lama yang Sudah Dijalankan:**
   Selalu buat berkas migrasi baru dengan penamaan kronologis (`YYYY_MM_DD_HHMMSS_deskripsi_perubahan.php`).
2. **Cek Keberadaan Tabel/Kolom (`Schema::hasColumn` / `Schema::hasTable`):**
   Ikuti pola defensif seperti pada `database/migrations/2026_10_15_000001_align_local_schema_with_erd.php` yang memeriksa `Schema::hasColumn(...)` sebelum menambah/menghapus kolom agar migrasi bersifat *idempotent* dan aman dijalankan di berbagai lingkungan.
3. **Sediakan Metode `down()` yang Valid:**
   Pastikan metode `down()` dapat membatalkan perubahan skema tanpa merusak integritas foreign key.

---

## 6. Panduan Menulis dan Menjalankan Pengujian (`DatabaseTransactions`)

> [!WARNING]
> **Aturan Wajib Pengujian Database:**
> Selalu gunakan trait `Illuminate\Foundation\Testing\DatabaseTransactions` pada setiap kelas *Feature Test* baru. **Dilarang menggunakan `RefreshDatabase` atau `DatabaseMigrations`** karena konfigurasi pengujian terhubung ke database lokal pengembangan dan `RefreshDatabase` akan menghapus data yang ada.

Contoh kerangka test yang benar:
```php
<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class FiturBaruTest extends TestCase
{
    use DatabaseTransactions;

    public function test_skenario_fitur_baru(): void
    {
        // Arrange, Act, Assert (otomatis di-rollback setelah selesai)
    }
}
```

Jalankan seluruh pengujian sebelum menyerahkan perubahan:
```bash
php vendor/phpunit/phpunit/phpunit
```

---

## 7. Rujukan Kode

- `composer.json#L1-L97`
- `app/Http/Controllers/Api/ApiController.php#L1-L39`
- `database/migrations/2026_10_15_000001_align_local_schema_with_erd.php#L1-L250`
- `tests/Feature/ReportDownloadTest.php#L1-L30`
- `tests/Feature/AdminAndPenjualFilteringAndPaginationTest.php#L1-L40`
