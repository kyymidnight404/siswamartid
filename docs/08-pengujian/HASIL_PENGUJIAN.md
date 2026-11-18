# Laporan Hasil Pengujian (Test Execution Report) — SiswaMart

| Informasi Dokumen | Detail |
| :--- | :--- |
| **Judul** | Laporan Hasil Eksekusi Pengujian Otomatis & Black-Box SiswaMart |
| **Versi** | `1.2.0` |
| **Tanggal** | 18 November 2026 |
| **Status** | Final (Verified Execution Output) |
| **Penulis** | Tim Dokumentasi Teknis & Rekayasa Perangkat Lunak |
| **Sumber Data** | Output eksekusi nyata `php vendor/phpunit/phpunit/phpunit` / `php artisan test` & spesifikasi `TEST_CASE.md` |

---

## Daftar Isi

1. [Ringkasan Eksekusi Pengujian Otomatis (PHPUnit)](#1-ringkasan-eksekusi-pengujian-otomatis-phpunit)
2. [Rincian Hasil Eksekusi per Berkas Pengujian](#2-rincian-hasil-eksekusi-per-berkas-pengujian)
3. [Ringkasan Hasil Pengujian Black-Box (`TC-001` s.d. `TC-038`)](#3-ringkasan-hasil-pengujian-black-box-tc-001-sd-tc-038)
4. [Rujukan Kode](#4-rujukan-kode)

---

## 1. Ringkasan Eksekusi Pengujian Otomatis (PHPUnit)

Perintah yang dijalankan secara nyata pada workspace `c:\laragon\www\siswamartid`:
```bash
php vendor/phpunit/phpunit/phpunit --log-junit "$env:TEMP\junit.xml"
```

### Statistik Hasil Aktual

| Metrik Eksekusi | Nilai Aktual |
| :--- | :--- |
| **Test Runner** | PHPUnit `12.5.33` |
| **Runtime PHP** | PHP `8.3.33` |
| **Total Berkas Pengujian** | **14 berkas** (`tests/Feature/`) |
| **Total Metode Test** | **129 test** |
| **Total Assertions** | **776 assertions** |
| **Jumlah Lulus (*Passed*)** | **129 test (`100%`)** |
| **Jumlah Gagal (*Failed*)** | **0 test** |
| **Jumlah Error (*Errors*)** | **0 error** |
| **Durasi Eksekusi** | **23,23 detik** (`23.226664s`) |

---

## 2. Rincian Hasil Eksekusi per Berkas Pengujian

| No | Suite / Kelas Pengujian | Jumlah Test | Lulus | Gagal | Assertions | Durasi | Status |
| :---: | :--- | :---: | :---: | :---: | :---: | :---: | :---: |
| 1 | `Tests\Feature\AdminAndPenjualFilteringAndPaginationTest` | 6 | 6 | 0 | 29 | 3,27 dtk | **LULUS (100%)** |
| 2 | `Tests\Feature\AdminRekapPenjualTest` | 5 | 5 | 0 | 18 | 1,34 dtk | **LULUS (100%)** |
| 3 | `Tests\Feature\AdminSellerReportTest` | 19 | 19 | 0 | 71 | 4,20 dtk | **LULUS (100%)** |
| 4 | `Tests\Feature\ApiMobileSiswaMartTest` | 8 | 8 | 0 | 47 | 1,68 dtk | **LULUS (100%)** |
| 5 | `Tests\Feature\Auth\GoogleLoginTest` | 15 | 15 | 0 | 48 | 1,56 dtk | **LULUS (100%)** |
| 6 | `Tests\Feature\Auth\PasswordResetTest` | 15 | 15 | 0 | 64 | 1,74 dtk | **LULUS (100%)** |
| 7 | `Tests\Feature\LaporanPdfTest` | 19 | 19 | 0 | 73 | 2,93 dtk | **LULUS (100%)** |
| 8 | `Tests\Feature\LaporanRekapitulasiUnifiedExportTest` | 7 | 7 | 0 | 32 | 1,18 dtk | **LULUS (100%)** |
| 9 | `Tests\Feature\PdfFilenameStandardizationTest` | 10 | 10 | 0 | 185 | 1,13 dtk | **LULUS (100%)** |
| 10 | `Tests\Feature\PublicCatalogRedirectTest` | 1 | 1 | 0 | 4 | 0,10 dtk | **LULUS (100%)** |
| 11 | `Tests\Feature\SecurityHardeningTest` | 5 | 5 | 0 | 26 | 0,39 dtk | **LULUS (100%)** |
| 12 | `Tests\Feature\SellerExportAuthorizationTest` | 1 | 1 | 0 | 2 | 0,23 dtk | **LULUS (100%)** |
| 13 | `Tests\Feature\SiswaMartRefinementRegressionTest` | 16 | 16 | 0 | 165 | 3,36 dtk | **LULUS (100%)** |
| 14 | `Tests\Feature\XlsxWriterTest` | 2 | 2 | 0 | 12 | 0,12 dtk | **LULUS (100%)** |
| **Total** | **14 Berkas Test** | **129** | **129** | **0** | **776** | **23,23 dtk** | **100% LULUS** |

### Cakupan Regresi Terbaru (`SiswaMartRefinementRegressionTest`)
- Konsistensi navbar publik dan tombol `Dashboard` untuk Admin/Penjual.
- Rute produk terlingkup toko `/{penjual:slug}/{produk:slug}` termasuk slug produk identik di toko berbeda, redirect `301` legacy `/produk/{slug}`, serta proteksi *reserved slug*.
- Panel notifikasi Admin (`Semua`/`Produk`/`Ulasan` tanpa tab setting, halaman penuh `/admin/notifikasi`, serta normalisasi redirect internal agar tetap berada di host aktif).
- Verifikasi objek SQL database (`v_katalog_produk_aktif`, `fn_hitung_rating_produk`, `sp_rekap_toko`, dan `trg_produk_set_approved_at`).

---

## 3. Ringkasan Hasil Pengujian Black-Box (`TC-001` s.d. `TC-038`)

| Kelompok Modul | Rentang ID TC | Jumlah Skenario | Hasil Verifikasi Aktual | Catatan |
| :--- | :---: | :---: | :---: | :--- |
| **Etalase Publik, WA, & Ulasan** | `TC-001` – `TC-009` | 9 | **9 Sesuai (Valid)** | Rute `/{penjual:slug}/{produk:slug}` + redirect `301` legacy `/produk/{slug}`, normalisasi nomor WA `62...`, dan filter `CommentModerationService`. |
| **Autentikasi & Otorisasi** | `TC-010` – `TC-015` | 6 | **6 Sesuai (Valid)** | Login username/email, Google OAuth terbatas akun terdaftar, dan anti-enumerasi lupa password. |
| **Panel Penjual** | `TC-016` – `TC-023` | 8 | **8 Sesuai (Valid)** | Alur `pending_changes`, sakelar `status_toko`, dan ekspor PDF/Excel penjual. |
| **Panel Admin** | `TC-024` – `TC-032` | 9 | **9 Sesuai (Valid)** | Validasi NISN 10 digit, komparasi diff moderasi, notifikasi kategori (`Semua`/`Produk`/`Ulasan`), dan ekspor PDF/Excel admin. |
| **REST API (`/api/*`)** | `TC-033` – `TC-038` | 6 | **6 Sesuai (Valid)** | Sanctum Bearer token, pencabutan token otomatis saat akun dinonaktifkan, endpoint `/api/v1/*`, dan `501` pada `/api/cart*`. |
| **Total Keseluruhan** | **`TC-001` – `TC-038`** | **38** | **38 Sesuai (`100%`)** | Seluruh skenario Black-Box terverifikasi oleh kode dan pengujian otomatis aktual. |

---

## 4. Rujukan Kode

- `tests/Feature/SiswaMartRefinementRegressionTest.php`
- `tests/Feature/AdminAndPenjualFilteringAndPaginationTest.php`
- `tests/Feature/AdminSellerReportTest.php`
- `tests/Feature/LaporanPdfTest.php`
- `tests/Feature/Auth/GoogleLoginTest.php`
- `tests/Feature/Auth/PasswordResetTest.php`
- `tests/Feature/ApiMobileSiswaMartTest.php`
- `tests/Feature/SecurityHardeningTest.php`
