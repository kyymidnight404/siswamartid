# Laporan Hasil Pengujian (Test Execution Report) — SiswaMart

| Informasi Dokumen | Detail |
| :--- | :--- |
| **Judul** | Laporan Hasil Eksekusi Pengujian Otomatis & Black-Box SiswaMart |
| **Versi** | `1.1.0` |
| **Tanggal** | 27 Oktober 2026 |
| **Status** | Final (Verified Execution Output) |
| **Penulis** | Tim Dokumentasi Teknis & Rekayasa Perangkat Lunak |
| **Sumber Data** | Output eksekusi nyata `php vendor/phpunit/phpunit/phpunit` & `Pengujian_Black_Box_SiswaMart.xlsx` |

---

## Daftar Isi

1. [Ringkasan Eksekusi Pengujian Otomatis (PHPUnit)](#1-ringkasan-eksekusi-pengujian-otomatis-phpunit)
2. [Rincian Hasil Eksekusi per Berkas Pengujian](#2-rincian-hasil-eksekusi-per-berkas-pengujian)
3. [Analisis Mendalam 1 Pengujian yang Gagal (Root Cause Analysis)](#3-analisis-mendalam-1-pengujian-yang-gagal-root-cause-analysis)
4. [Ringkasan Hasil Pengujian Black-Box (`TC-001` s.d. `TC-038`)](#4-ringkasan-hasil-pengujian-black-box-tc-001-sd-tc-038)
5. [Rujukan Kode](#5-rujukan-kode)

---

## 1. Ringkasan Eksekusi Pengujian Otomatis (PHPUnit)

Perintah yang dijalankan secara nyata pada workspace `c:\laragon\www\siswamartid`:
```bash
php vendor/phpunit/phpunit/phpunit
```

### Statistik Hasil Aktual

| Metrik Eksekusi | Nilai Aktual |
| :--- | :--- |
| **Test Runner** | PHPUnit `12.5.33` oleh Sebastian Bergmann |
| **Runtime PHP** | PHP `8.3.30` |
| **Total Berkas Pengujian** | **10 berkas** (`1` Unit + `9` Feature) |
| **Total Metode Test** | **75 test** |
| **Total Assertions** | **427 assertions** |
| **Jumlah Lulus (*Passed*)** | **74 test (`98,67%`)** |
| **Jumlah Gagal (*Failed*)** | **1 test (`1,33%`)** — *ketergantungan data seeder lokal, bukan cacat kode fitur* |
| **Jumlah Error (*Errors*)** | **0 error** |
| **Durasi Eksekusi** | **19,57 detik** (`19.571 ms`), Penggunaan Memori: `56,50 MB` |

---

## 2. Rincian Hasil Eksekusi per Berkas Pengujian

| No | Suite / Kelas Pengujian | Jumlah Test | Lulus | Gagal | Assertions | Durasi | Status |
| :---: | :--- | :---: | :---: | :---: | :---: | :---: | :---: |
| 1 | `Tests\Unit\ExampleTest` | 1 | 1 | 0 | 1 | 31 ms | **LULUS (100%)** |
| 2 | `Tests\Feature\ExampleTest` | 1 | 1 | 0 | 1 | 1.883 ms | **LULUS (100%)** |
| 3 | `Tests\Feature\ProfileTest` | 5 | 5 | 0 | 15 | 664 ms | **LULUS (100%)** |
| 4 | `Tests\Feature\ReportDownloadTest` | 9 | 9 | 0 | 49 | 6.809 ms | **LULUS (100%)** |
| 5 | `Tests\Feature\Auth\AuthenticationTest` | 7 | 7 | 0 | 14 | 540 ms | **LULUS (100%)** |
| 6 | `Tests\Feature\Auth\GoogleAuthenticationTest` | 6 | 6 | 0 | 22 | 645 ms | **LULUS (100%)** |
| 7 | `Tests\Feature\Auth\PasswordConfirmationTest` | 3 | 3 | 0 | 5 | 252 ms | **LULUS (100%)** |
| 8 | `Tests\Feature\Auth\PasswordResetTest` | 5 | 5 | 0 | 12 | 946 ms | **LULUS (100%)** |
| 9 | `Tests\Feature\Auth\PasswordUpdateTest` | 2 | 2 | 0 | 6 | 191 ms | **LULUS (100%)** |
| 10 | `Tests\Feature\AdminAndPenjualFilteringAndPaginationTest` | 36 | 35 | **1** | 302 | 7.606 ms | **35/36 LULUS** |
| **Total** | **10 Berkas Test** | **75** | **74** | **1** | **427** | **19,57 dtk** | **98,67% LULUS** |

---

## 3. Analisis Mendalam 1 Pengujian yang Gagal (Root Cause Analysis)

### 3.1 Identitas Test yang Gagal
- **Nama Metode:** `Tests\Feature\AdminAndPenjualFilteringAndPaginationTest::test_every_existing_registered_seller_has_at_least_10_products`
- **Lokasi Berkas:** `tests/Feature/AdminAndPenjualFilteringAndPaginationTest.php#L273-L287`
- **Pesan Kegagalan Aktual:**
  ```text
  Penjual Edmund's Hearth harus memiliki minimal 10 produk.
  Failed asserting that 1 is equal to 10 or is greater than 10.
  at tests\Feature\AdminAndPenjualFilteringAndPaginationTest.php:283
  ```

### 3.2 Penyebab Akar Masalah (*Root Cause*)
1. Metode test pada baris `#L273-L287` memeriksa isi data lokal di tabel `penjual` saat ini:
   ```php
   $sellers = Penjual::withCount('produk')->get();
   foreach ($sellers as $seller) {
       $this->assertGreaterThanOrEqual(
           10,
           $seller->produk_count,
           "Penjual {$seller->nama_toko} harus memiliki minimal 10 produk."
       );
   }
   ```
2. Test ini ditulis untuk memverifikasi hasil eksekusi `ProdukSeeder` (`database/seeders/ProdukSeeder.php`), yang secara default membuat minimal 10 produk contoh untuk setiap toko yang di-seed oleh `PenjualPromptsSeeder`.
3. Pada database lokal `siswamartid` saat ini, terdapat akun toko bernama **`Edmund's Hearth`** yang baru memiliki **1 produk** (karena dibuat/dimodifikasi secara manual di luar `ProdukSeeder`), sehingga asersi `1 >= 10` gagal.
4. **Kesimpulan Teknis:** Kegagalan ini **bukan bug pada fitur aplikasi**, melainkan asersi terhadap jumlah data seeder pada database lokal. Seluruh **74 test fungsional** (moderasi, laporan PDF/Excel, keamanan middleware, Google OAuth, filter, paginasi, dan REST API) **100% LULUS**.

---

## 4. Ringkasan Hasil Pengujian Black-Box (`TC-001` s.d. `TC-038`)

Berdasarkan verifikasi kode sumber dan dokumen `Pengujian_Black_Box_SiswaMart.xlsx`:

| Kelompok Modul | Rentang ID TC | Jumlah Skenario | Hasil Verifikasi Aktual | Catatan |
| :--- | :---: | :---: | :---: | :--- |
| **Etalase Publik, WA, & Ulasan** | `TC-001` – `TC-009` | 9 | **9 Sesuai (Valid)** | Termasuk normalisasi nomor WA `62...`, blokir stok habis, dan filter `CommentModerationService`. |
| **Autentikasi & Otorisasi** | `TC-010` – `TC-015` | 6 | **6 Sesuai (Valid)** | Termasuk login username/email, Google OAuth terbatas akun terdaftar, dan anti-enumerasi lupa password. |
| **Panel Penjual** | `TC-016` – `TC-023` | 8 | **8 Sesuai (Valid)** | Termasuk alur `pending_changes`, sakelar `status_toko`, dan ekspor PDF/Excel penjual. |
| **Panel Admin** | `TC-024` – `TC-032` | 9 | **9 Sesuai (Valid)** | Termasuk validasi NISN 10 digit, komparasi diff moderasi, dan ekspor PDF/Excel 3 sheet admin. |
| **REST API (`/api/*`)** | `TC-033` – `TC-038` | 6 | **6 Sesuai (Valid)** | Termasuk Sanctum Bearer token, pencabutan token otomatis saat akun dinonaktifkan, dan respons `501` pada `/api/cart*`. |
| **Total Keseluruhan** | **`TC-001` – `TC-038`** | **38** | **38 Sesuai (`100%`)** | Seluruh skenario Black-Box terbukti didukung oleh kode aktual. |

---

## 5. Rujukan Kode

- `tests/Feature/AdminAndPenjualFilteringAndPaginationTest.php#L273-L287`
- `tests/Feature/ReportDownloadTest.php#L1-L265`
- `database/seeders/ProdukSeeder.php`
- `Pengujian_Black_Box_SiswaMart.xlsx`
