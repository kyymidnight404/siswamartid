# Riwayat Perubahan (Changelog) — SiswaMart

| Informasi Dokumen | Detail |
| :--- | :--- |
| **Judul** | Riwayat Perubahan (Changelog) Proyek SiswaMart |
| **Versi** | `1.2.0` |
| **Tanggal** | 18 November 2026 |
| **Status** | Final (Git History Verified) |
| **Penulis** | Tim Dokumentasi Teknis & Rekayasa Perangkat Lunak |
| **Sumber Data** | Output aktual `git log --stat` dan `git status` pada repositori `c:\laragon\www\siswamartid` |

---

## Daftar Isi

1. [Format Pencatatan](#1-format-pencatatan)
2. [[1.2.0] — 17–18 November 2026 (Commit `b38d67e`, `d98ecdd`, `b070e50`, `b3a9d41`)](#2-120--1718-november-2026-commit-b38d67e-d98ecdd-b070e50-b3a9d41)
3. [[1.1.0] — 13 November 2026 (Commit `8358f95`)](#3-110--13-november-2026-commit-8358f95)
4. [[1.0.5] — 26 Oktober 2026 (Commit `108c775`)](#4-105--26-oktober-2026-commit-108c775)
5. [[1.0.4] — 25 Oktober 2026 (Commit `927f7cb` & `f0c0b5a`)](#5-104--25-oktober-2026-commit-927f7cb--f0c0b5a)
6. [[1.0.3] — 21 Oktober 2026 (Commit `da22ca1`)](#6-103--21-oktober-2026-commit-da22ca1)
7. [[1.0.2] — 15 Oktober 2026 (Commit `f379320`)](#7-102--15-oktober-2026-commit-f379320)
8. [[1.0.0] — 2 Oktober 2026 (Commit `1c6bac4`)](#8-100--2-oktober-2026-commit-1c6bac4)
9. [Rujukan Kode & Commit Git](#9-rujukan-kode--commit-git)

---

## 1. Format Pencatatan

Dokumen ini disusun mengikuti standar **[Keep a Changelog](https://keepachangelog.com/id/1.1.0/)** berdasarkan riwayat commit nyata (`git log`) pada repositori proyek SiswaMart.

---

## 2. [1.2.0] — 17–18 November 2026 (Commit `b38d67e`, `d98ecdd`, `b070e50`, `b3a9d41`)

### Added (Ditambahkan)
- **Objek SQL Database (`b3a9d41`):** Migrasi `2026_11_18_000001_add_database_views_routines_and_triggers.php` menambahkan SQL View `v_katalog_produk_aktif`, Stored Function `fn_hitung_rating_produk`, Stored Procedure `sp_rekap_toko`, dan Trigger `trg_produk_set_approved_at` (`SQL SECURITY INVOKER`).
- **Rute Produk Terlingkup Toko (`b38d67e`):** Migrasi `2026_11_17_000001_add_slug_to_penjual_and_scope_produk_slug.php` menambahkan `penjual.slug` dan indeks komposit unik `(penjual_id, slug)`, rute publik `/{penjual:slug}/{produk:slug}` (`show`, `whatsapp`, `ulasan`), serta redirect `301` legacy `/produk/{slug}`.
- **Halaman Penuh & Filter Notifikasi Admin (`d98ecdd`):** Menambahkan rute `GET /admin/notifikasi` (`admin.notifications.index`), tab filter `Semua`, `Produk`, dan `Ulasan` tanpa tab setting, serta normalisasi redirect relatif host di `AdminNotificationService`.

### Changed (Diubah)
- **Domain Canonical & Redirect `301` (`b070e50`):** Menambahkan rewrite `301` di `public/.htaccess` dari `siswamartid.my.id` dan `www` menuju `https://siswamart.app` (melindungi `/.well-known/acme-challenge/`), serta memperbarui `robots.txt`, fallback logo email reset password, `.env.hosting.example`, dan `PANDUAN_HOSTING.txt`.
- **UI Publik & Dashboard (`b38d67e`, `d98ecdd`):** Menyeragamkan tombol navbar `Dashboard` berwarna kuning, memadatkan kartu detail produk agar muat di atas lipatan layar desktop/laptop, mengubah tombol WhatsApp mengambang di HP menjadi popup pill kanan bawah, menghapus tag `Admin` di kiri atas sidebar, dan memusatkan wordmark `SiswaMart` tanpa kotak.
- **Pengujian Otomatis:** Total suite naik menjadi **14 berkas / 129 test (776 assertions)** dengan kelulusan **100% (129/129 Lulus)**.

---

## 3. [1.1.0] — 13 November 2026 (Commit `8358f95`)

**Commit:** `8358f95` — *"feat: refine SiswaMart reporting, security, UI, docs, and hosting setup"*

### Added (Ditambahkan)
- `SecurityHeadersMiddleware`, `SitemapController` (`/sitemap.xml`), `WhatsappUrlService`, dan `PdfFilenameService`.
- Font web lokal (`public/fonts/`), pembaruan desain laporan PDF & Excel Admin/Penjual, skrip packing DirectAdmin (`scripts/deploy/create-siswamart-root-zip.ps1` & `deploy-helper.php`), serta paket dokumentasi `docs/`.
- Migrasi `2026_11_05_000001_add_approved_at_to_produk_table`, `2026_11_09_000001_add_performance_composite_indexes`, dan `2026_11_11_000001_ensure_cache_table_value_is_medium_text`.

---

## 4. [1.0.5] — 26 Oktober 2026 (Commit `108c775`)

**Commit:** `108c775` — *"feat: add Google authentication and comprehensive documentation"*

### Added (Ditambahkan)
- Integrasi **Login Google OAuth 2.0** menggunakan `laravel/socialite` (`^5.31`):
  - `app/Http/Controllers/Auth/GoogleAuthController.php` (`redirect` & `callback`).
  - Migrasi `database/migrations/2026_10_15_000005_add_google_id_to_users_table.php` (kolom `google_id` unik).
  - Pengujian otomatis `tests/Feature/Auth/GoogleAuthenticationTest.php` (6 metode test).
- Rute pratinjau email reset password khusus lingkungan lokal (`GET /preview/reset-password-email` di `routes/web.php#L94-L106`) dan notifikasi email kustom `BrandedResetPassword` & `BrandedVerifyEmail` (`resources/views/emails/auth-action.blade.php`).
- Dokumen analisis awal dan matriks pengujian Black-Box (`Pengujian_Black_Box_SiswaMart.xlsx`).

### Changed (Diubah)
- Menyempurnakan `PasswordResetLinkController` agar menerapkan proteksi anti-enumerasi email (`tests/Feature/Auth/PasswordResetTest.php`).
- Memperbarui tampilan halaman login, lupa kata sandi, dan reset kata sandi (`resources/views/auth/`) dengan tombol Masuk dengan Google.

---

## 4. [1.0.4] — 25 Oktober 2026 (Commit `927f7cb` & `f0c0b5a`)

**Commits:**
- `927f7cb` — *"Update Fitur download laporan admin dan penjual, memperbaiki bug pada halaman produk dan kategori"*
- `f0c0b5a` — *"feat: implement seller and admin report generation with Excel and PDF export"*

### Added (Ditambahkan)
- Layanan agregasi laporan terpusat: `app/Services/LaporanAdminService.php` dan `app/Services/LaporanPenjualService.php`.
- Eksportir buku kerja Excel (`.xlsx`) multi-sheet: `app/Exports/LaporanAdminExport.php` (3 sheet), `app/Exports/LaporanPenjualExport.php` (2 sheet), trait `app/Exports/Concerns/StylesSheet.php` (dengan sanitasi anti-injeksi formula), dan generator OpenXML mandiri `app/Support/XlsxWriter.php`.
- Rute unduh laporan Excel (`GET /admin/laporan/export-excel` & `GET /penjual/laporan/export-excel`) dan komponen UI `<x-ui.download-menu>`.
- Suite pengujian otomatis unduhan laporan `tests/Feature/ReportDownloadTest.php` (9 metode test).
- Resolusi multi-path pada `public/index.php` dan `config/dompdf.php` untuk kompatibilitas deployment *shared hosting* (`core/` vs `public_html/`).

### Fixed (Diperbaiki)
- Perbaikan bug pemfilteran dan tampilan pada halaman katalog produk serta kategori publik (`Public\KategoriController`, `resources/views/public/kategori/`).

---

## 5. [1.0.3] — 21 Oktober 2026 (Commit `da22ca1`)

**Commit:** `da22ca1` — *"feat: add product moderation requests and update seller profile fields"*

### Added (Ditambahkan)
- Mekanisme **Moderasi Produk Dua Tahap Preservatif** (`ADR-002`):
  - Migrasi `2026_10_15_000004_add_product_moderation_requests.php` menambah kolom `pending_action` (`create`, `update`, `delete`) dan `pending_changes` (`json`) pada tabel `produk`.
  - Tabel komparasi *diff* nilai lama vs nilai baru pada halaman detail moderasi Admin (`resources/views/admin/produk/show.blade.php`).
- **REST API Lengkap (`/api/*` — 26 rute)** berbasis Laravel Sanctum (`routes/api.php` dan 11 kontroler di `app/Http/Controllers/Api/`), termasuk middleware `EnsureUserIsActive` (`app/Http/Middleware/EnsureUserIsActive.php`).
- Tiga migrasi penyelarasan ERD (`2026_10_15_000001_align_local_schema_with_erd.php`, `2026_10_15_000002_normalize_erd_status_values.php`, `2026_10_15_000003_remove_admin_creator_from_penjual.php`) untuk menambahkan `users.username`, `penjual.nisn` (10 digit unik), `penjual.alamat`, constraint `1:1` `penjual_user_id_unique`, serta normalisasi enum `stok` (`tersedia`, `po`, `habis`) dan `status_toko` (`buka`, `tutup`).

---

## 6. [1.0.2] — 15 Oktober 2026 (Commit `f379320`)

**Commit:** `f379320` — *"Update Sistem"*

### Added (Ditambahkan)
- Fitur **Status Operasional Toko (`buka` / `tutup`)** pada tabel `penjual` (`2026_10_14_000001_add_status_toko_to_penjual_table.php`) beserta sakelar cepat `PATCH /penjual/profil/status-toko` dan penyesuaian pesan WhatsApp otomatis saat toko tutup.
- Kolom `must_change_password` pada tabel `users` (`2026_10_14_000002_add_must_change_password_to_users_table.php`).
- Komponen UI kustom (`<x-ui.custom-select>`, `<x-ui.status-badge>`, `<x-admin.table-pagination>`) dan suite pengujian komprehensif `tests/Feature/AdminAndPenjualFilteringAndPaginationTest.php`.

---

## 7. [1.0.0] — 2 Oktober 2026 (Commit `1c6bac4`)

**Commit:** `1c6bac4` — *"Initial commit of SiswaMart application"*

### Added (Ditambahkan)
- Rilis fondasi awal aplikasi **SiswaMart** di atas Laravel:
  - Skema dasar `users`, `penjual`, `kategori`, `produk`, `ulasan`, `produk_views_logs`, `bad_words`, `whitelists`, `comment_moderation_logs`, dan `notifications` (Batch 1 s.d. Batch 6).
  - Etalase publik (`HomeController`, `KategoriController`, `ProdukController`, `UlasanController`) dengan pelacakan klik WhatsApp.
  - Layanan penyaringan kata kasar `CommentModerationService` (350 kata dasar `badwords-list.json`) dan `AdminNotificationService`.
  - Panel Admin dan Panel Penjual beserta cetakan laporan PDF awal menggunakan `barryvdh/laravel-dompdf`.

---

## 9. Rujukan Kode & Commit Git

- Commit `b3a9d41` (2026-11-18)
- Commit `b070e50` (2026-11-18)
- Commit `d98ecdd` (2026-11-17)
- Commit `b38d67e` (2026-11-17)
- Commit `8358f95` (2026-11-13)
- Commit `108c775` (2026-10-26)
- Commit `927f7cb` (2026-10-25)
- Commit `f0c0b5a` (2026-10-25)
- Commit `da22ca1` (2026-10-21)
- Commit `f379320` (2026-10-15)
- Commit `1c6bac4` (2026-10-02)
