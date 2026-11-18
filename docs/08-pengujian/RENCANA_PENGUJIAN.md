# Rencana Pengujian (Test Plan) — SiswaMart

| Informasi Dokumen | Detail |
| :--- | :--- |
| **Judul** | Rencana Pengujian (Test Plan) Perangkat Lunak SiswaMart |
| **Versi** | `1.2.0` |
| **Tanggal** | 18 November 2026 |
| **Status** | Final (Source-Code Backed) |
| **Penulis** | Tim Dokumentasi Teknis & Rekayasa Perangkat Lunak |
| **Sumber Data** | `phpunit.xml`, `tests/Feature/*.php`, `docs/08-pengujian/TEST_CASE.md` |

---

## Daftar Isi

1. [Tujuan dan Ruang Lingkup Pengujian](#1-tujuan-dan-ruang-lingkup-pengujian)
2. [Strategi dan Jenis Pengujian](#2-strategi-dan-jenis-pengujian)
3. [Lingkungan Pengujian Aktual](#3-lingkungan-pengujian-aktual)
4. [Keamanan Database Selama Pengujian (`DatabaseTransactions`)](#4-keamanan-database-selama-pengujian-databasetransactions)
5. [Kriteria Lulus / Gagal (Pass / Fail Criteria)](#5-kriteria-lulus--gagal-pass--fail-criteria)
6. [Daftar Modul yang Diuji](#6-daftar-modul-yang-diuji)
7. [Rujukan Kode](#7-rujukan-kode)

---

## 1. Tujuan dan Ruang Lingkup Pengujian

Rencana pengujian ini disusun untuk memverifikasi bahwa seluruh kebutuhan fungsional (`FR-*`), kebutuhan non-fungsional (`NFR-*`), dan aturan bisnis (`BR-*`) pada aplikasi **SiswaMart** berjalan sesuai spesifikasi kode aktual tanpa merusak integritas data yang sudah ada.

### 1.1 Ruang Lingkup yang Diuji (*In-Scope*)
1. **Etalase Publik:** Beranda, filter katalog kategori & harga, detail produk & penghitung `views`, pelacakan klik WhatsApp (`status_toko` buka/tutup & blokir stok habis), serta pengiriman ulasan tamu.
2. **Autentikasi & Otorisasi:** Login email/username, Google OAuth (`Socialite`), pembatasan akun nonaktif (`EnsureAdmin`, `EnsurePenjual`, `EnsureUserIsActive`), reset password anti-enumerasi, dan manajemen profil.
3. **Panel Penjual:** Dasbor penjual, profil toko, sakelar buka/tutup toko, pengajuan produk (`create`, `update` via `pending_changes`, `delete`), dan ekspor laporan toko (PDF & Excel `.xlsx`).
4. **Panel Admin:** Dasbor analitik, CRUD penjual dengan validasi NISN 10 digit & proteksi hapus, moderasi produk (`approve`/`reject`), CRUD kategori, moderasi komentar (`CommentModerationService`), notifikasi admin, dan ekspor laporan global (PDF & Excel `.xlsx`).
5. **REST API (`/api/*`):** Seluruh **37 rute API** (termasuk 11 alias `/api/v1/*`, token Sanctum, validasi JSON `422`, dan respons `501 Not Implemented` pada `/api/cart*`).

### 1.2 Di Luar Ruang Lingkup (*Out-of-Scope*)
- Pengujian beban terdistribusi (*distributed load testing*) di server produksi.
- Pengiriman pesan aktual di dalam aplikasi WhatsApp pihak ketiga setelah redirect `wa.me` berhasil.

---

## 2. Strategi dan Jenis Pengujian

Pengujian SiswaMart menggabungkan dua pendekatan yang saling melengkapi:

| Jenis Pengujian | Metode Eksekusi | Cakupan Berkas / Dokumen | Jumlah Skenario |
| :--- | :--- | :--- | :---: |
| **1. Pengujian Otomatis (*Automated Feature Testing*)** | Dieksekusi menggunakan **PHPUnit 12.5.33** (`php vendor/phpunit/phpunit/phpunit` atau `php artisan test`). | **14 berkas test** di `tests/Feature/`. | **129 metode test** (**776 assertions**) |
| **2. Pengujian Fungsional *Black-Box*** | Skenario uji berbasis input-output antarmuka pengguna dan API sesuai matriks uji standar SMK RPL. | Diselaraskan dengan berkas `Pengujian_Black_Box_SiswaMart.xlsx` dan `docs/08-pengujian/TEST_CASE.md`. | **38 butir Test Case** (`TC-001` s.d. `TC-038`) |

---

## 3. Lingkungan Pengujian Aktual

Spesifikasi perangkat lunak dan konfigurasi pada lingkungan pengujian tempat seluruh test dieksekusi:

| Komponen Lingkungan | Nilai Aktual Terverifikasi | Rujukan Konfigurasi |
| :--- | :--- | :--- |
| **Sistem Operasi** | Windows (Lingkungan Pengembangan Laragon) | — |
| **Versi PHP** | `PHP 8.3.33` lokal (`^8.3` di `composer.json`), `PHP 8.5` hosting | `composer.json#L9`, `#L85` |
| **Framework Aplikasi** | `Laravel Framework v13.38.0` | `composer.lock` |
| **Test Runner** | `PHPUnit 12.5.33` | `composer.json#L24` |
| **Database Pengujian** | MySQL (`siswamartid`, 19 tabel aktif, 1 SQL View, 2 Stored Routines, 1 Trigger, 32 migrasi `Ran`) | `.env`, `phpunit.xml` |
| **Konfigurasi `phpunit.xml`** | `APP_ENV=testing`, `CACHE_STORE=array`, `MAIL_MAILER=array`, `QUEUE_CONNECTION=sync`, `SESSION_DRIVER=array`, `BCRYPT_ROUNDS=4` | `phpunit.xml#L21-L29` |

---

## 4. Keamanan Database Selama Pengujian (`DatabaseTransactions`)

> [!IMPORTANT]
> **Perlindungan Data Aktual:**
> Seluruh kelas pengujian di `tests/Feature/` menggunakan trait `Illuminate\Foundation\Testing\DatabaseTransactions` (**bukan** `RefreshDatabase` atau `DatabaseMigrations`).
> Artinya:
> 1. Setiap metode test dimulai dengan `BEGIN TRANSACTION`.
> 2. Seluruh data uji yang dibuat selama test berlangsung langsung dibatalkan (`ROLLBACK`) begitu metode test selesai.
> 3. Skema tabel dan data yang ada di database `siswamartid` **tidak pernah di-drop atau dirusak** oleh eksekusi PHPUnit.

*(Catatan konfigurasi: Pada `phpunit.xml#L24-L25`, baris `DB_CONNECTION=sqlite` dan `DB_DATABASE=:memory:` sengaja dinonaktifkan dalam komentar XML sehingga PHPUnit menggunakan koneksi MySQL dengan `DatabaseTransactions`).*

---

## 5. Kriteria Lulus / Gagal (Pass / Fail Criteria)

1. **Kriteria Lulus (*Pass*):**
   - Kode status HTTP aktual cocok dengan spesifikasi (`200`, `201`, `302`, `401`, `403`, `404`, `422`, `501`).
   - Perubahan state database (seperti `produk.status`, `pending_action`, `pending_changes`, `produk_views_logs`, `ulasan`, dan `notifications`) sesuai dengan aturan bisnis `BR-01` s.d. `BR-16`.
   - Berkas ekspor laporan memiliki header MIME dan struktur biner yang valid (`%PDF` untuk PDF dan struktur XML ZIP untuk `.xlsx`).
2. **Kriteria Gagal (*Fail*):**
   - Terjadi *unhandled exception* (`HTTP 500`), kebocoran hak akses antar peran, atau ketidaksesuaian asersi pada PHPUnit.

---

## 6. Daftar Modul yang Diuji

| Kode Suite Otomatis | Berkas Test (`tests/...`) | Modul yang Diuji | Jumlah Metode Test |
| :--- | :--- | :--- | :---: |
| **TC-AUTO-001** | `tests/Feature/AdminAndPenjualFilteringAndPaginationTest.php` | Filter & paginasi admin/penjual | 6 |
| **TC-AUTO-002** | `tests/Feature/AdminRekapPenjualTest.php` | Rekap penjual Admin & metrik toko | 5 |
| **TC-AUTO-003** | `tests/Feature/AdminSellerReportTest.php` | Laporan performa penjual, `approved_at`, dan endpoint rating produk | 19 |
| **TC-AUTO-004** | `tests/Feature/ApiMobileSiswaMartTest.php` | REST API katalog, ulasan, moderasi, dan `/api/v1/*` | 8 |
| **TC-AUTO-005** | `tests/Feature/Auth/GoogleLoginTest.php` | Login Google OAuth (akun terdaftar, ditolak jika nonaktif/belum terdaftar) | 15 |
| **TC-AUTO-006** | `tests/Feature/Auth/PasswordResetTest.php` | Lupa password, anti-enumerasi email, reset password dengan token valid | 15 |
| **TC-AUTO-007** | `tests/Feature/LaporanPdfTest.php` | Unduhan dan validitas PDF laporan Admin & Penjual | 19 |
| **TC-AUTO-008** | `tests/Feature/LaporanRekapitulasiUnifiedExportTest.php` | Ekspor rekapitulasi terpadu Excel & PDF | 7 |
| **TC-AUTO-009** | `tests/Feature/PdfFilenameStandardizationTest.php` | Standardisasi penamaan berkas PDF (`PdfFilenameService`) | 10 |
| **TC-AUTO-010** | `tests/Feature/PublicCatalogRedirectTest.php` | Redirect `GET /produk` ke `/kategori` (`301`) | 1 |
| **TC-AUTO-011** | `tests/Feature/SecurityHardeningTest.php` | Rate limiting, sanitasi, otorisasi, dan keamanan endpoint | 5 |
| **TC-AUTO-012** | `tests/Feature/SellerExportAuthorizationTest.php` | Otorisasi akses ekspor laporan penjual | 1 |
| **TC-AUTO-013** | `tests/Feature/SiswaMartRefinementRegressionTest.php` | Regresi UI, rute `/{penjual:slug}/{produk:slug}`, notifikasi Admin, dan objek SQL | 16 |
| **TC-AUTO-014** | `tests/Feature/XlsxWriterTest.php` | Generator OpenXML `.xlsx` mandiri (`App\Support\XlsxWriter`) | 2 |
| **Total** | **14 Berkas Test** | **Seluruh Modul Aplikasi & DB** | **129 Metode Test** |

---

## 7. Rujukan Kode

- `phpunit.xml#L1-L26`
- `tests/Feature/SiswaMartRefinementRegressionTest.php`
- `tests/Feature/AdminAndPenjualFilteringAndPaginationTest.php`
- `tests/Feature/AdminSellerReportTest.php`
- `tests/Feature/LaporanPdfTest.php`
- `tests/Feature/Auth/GoogleLoginTest.php`
- `tests/Feature/Auth/PasswordResetTest.php`
- `tests/Feature/ApiMobileSiswaMartTest.php`
- `tests/Feature/SecurityHardeningTest.php`
