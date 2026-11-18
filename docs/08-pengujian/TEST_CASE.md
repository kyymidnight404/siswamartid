# Spesifikasi Kasus Uji (Test Cases) — SiswaMart

| Informasi Dokumen | Detail |
| :--- | :--- |
| **Judul** | Spesifikasi Kasus Uji Black-Box (`TC-001`–`TC-038`) & Test Otomatis (`TC-AUTO-001`–`TC-AUTO-014`) |
| **Versi** | `1.2.0` |
| **Tanggal** | 18 November 2026 |
| **Status** | Final (Source-Code Verified) |
| **Penulis** | Tim Dokumentasi Teknis & Rekayasa Perangkat Lunak |
| **Sumber Data** | `tests/Feature/*.php`, `docs/02-srs/SRS.md` |

---

## Daftar Isi

1. [Matriks Kasus Uji Black-Box (`TC-001` s.d. `TC-038`)](#1-matriks-kasus-uji-black-box-tc-001-sd-tc-038)
   - [1.1 Modul Katalog Publik, WhatsApp, & Ulasan (`TC-001` s.d. `TC-009`)](#11-modul-katalog-publik-whatsapp--ulasan-tc-001-sd-tc-009)
   - [1.2 Modul Autentikasi & Keamanan Akun (`TC-010` s.d. `TC-015`)](#12-modul-autentikasi--keamanan-akun-tc-010-sd-tc-015)
   - [1.3 Modul Panel Penjual (`TC-016` s.d. `TC-023`)](#13-modul-panel-penjual-tc-016-sd-tc-023)
   - [1.4 Modul Panel Admin (`TC-024` s.d. `TC-032`)](#14-modul-panel-admin-tc-024-sd-tc-032)
   - [1.5 Modul REST API (`/api/*`) (`TC-033` s.d. `TC-038`)](#15-modul-rest-api-api-tc-033-sd-tc-038)
2. [Matriks Kasus Uji Otomatis PHPUnit (`TC-AUTO-001` s.d. `TC-AUTO-010`)](#2-matriks-kasus-uji-otomatis-phpunit-tc-auto-001-sd-tc-auto-010)
3. [Rujukan Kode](#3-rujukan-kode)

---

## 1. Matriks Kasus Uji Black-Box (`TC-001` s.d. `TC-038`)

Seluruh 38 skenario di bawah ini diselaraskan langsung dengan lembar kerja `Pengujian_Black_Box_SiswaMart.xlsx` dan dipetakan ke ID Kebutuhan Fungsional (`FR-*`) serta suite pengujian otomatis terkait.

### 1.1 Modul Katalog Publik, WhatsApp, & Ulasan (`TC-001` s.d. `TC-009`)

| ID TC | Modul | Skenario Pengujian | Data Masukan / Kondisi | Langkah Pengujian | Hasil yang Diharapkan | Rujukan FR / Test Otomatis |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- |
| **TC-001** | Beranda Publik | Menampilkan halaman beranda & produk aktif | Produk `status='aktif'` & penjual `is_active=1` | Buka `GET /` | Halaman beranda tampil (`200 OK`) hanya memuat produk aktif dari penjual aktif. | `FR-PUB-001`, `TC-AUTO-002` |
| **TC-002** | Katalog & Filter | Mencari produk berdasarkan kata kunci dan kategori | `q=risoles`, `kategori[]=kuliner` | Buka `GET /kategori` dan kirim filter pencarian | Hanya produk yang cocok dengan kata kunci dan kategori yang ditampilkan (paginasi 12/hal). | `FR-PUB-002`, `TC-AUTO-010` |
| **TC-003** | Katalog & Filter | Filter rentang harga terbalik (`min_harga > max_harga`) | `min_harga=50000`, `max_harga=10000` | Kirim form filter harga di `/kategori` | Sistem otomatis menukar menjadi `10000`–`50000` dan menampilkan produk yang sesuai tanpa error. | `FR-PUB-002` (`KategoriController#L25`) |
| **TC-004** | Detail Produk | Melihat detail produk aktif & pencatatan `views` | Slug toko & produk aktif | Buka `GET /{penjual:slug}/{produk:slug}` (atau URL lama `/produk/{slug}` redirect `301`) | Halaman detail tampil (`200 OK`), kolom `views` bertambah `+1`, dan log `type='view'` tercatat di `produk_views_logs`. | `FR-PUB-003`, `TC-AUTO-013` |
| **TC-005** | Detail Produk | Mengakses URL produk yang berstatus `pending`/`ditolak` atau penjual nonaktif | Slug produk `pending`, tidak cocok toko, atau penjual `is_active=0` | Buka `GET /{penjual:slug}/{produk:slug}` | Sistem menolak akses dengan halaman `404 Not Found`. | `FR-PUB-003`, `BR-PROD-01`, `TC-AUTO-013` |
| **TC-006** | Pemesanan WA | Klik Pesan via WhatsApp saat stok `tersedia`/`po` dan toko `buka` | `stok='tersedia'`, `status_toko='buka'`, `nomor_whatsapp='08123...'` | Klik tombol WA (`GET /{penjual:slug}/{produk:slug}/whatsapp`) | Mencatat log `type='whatsapp'` dan redirect `302` ke `https://wa.me/628123...` dengan pesan pemesanan standar. | `FR-PUB-004`, `BR-STORE-01`, `TC-AUTO-013` |
| **TC-007** | Pemesanan WA | Klik Pesan via WhatsApp saat toko `tutup` vs saat `stok='habis'` | Kondisi A: `status_toko='tutup'`; Kondisi B: `stok='habis'` | Akses `GET /{penjual:slug}/{produk:slug}/whatsapp` | Kondisi A: Redirect ke WA dengan pesan konfirmasi jam buka toko. Kondisi B: Diblokir & redirect kembali ke produk dengan pesan stok habis. | `FR-PUB-004`, `BR-STORE-01`, `TC-AUTO-013` |
| **TC-008** | Ulasan Tamu | Mengirim ulasan produk yang sopan & valid | `guest_username='Rina'`, `rating=5`, `komentar='Rasanya enak sekali!'` | Kirim `POST /{penjual:slug}/{produk:slug}/ulasan` | Ulasan tersimpan (`status='approved'`), tampil di halaman produk, dan mengirim notifikasi ke Admin. | `FR-PUB-005`, `BR-REV-01` |
| **TC-009** | Ulasan Tamu | Mengirim ulasan yang mengandung kata kasar / variasi *leetspeak* | `guest_username='User'`, `rating=1`, `komentar` mengandung kata terlarang / `b0d0h` | Kirim `POST /{penjual:slug}/{produk:slug}/ulasan` | `CommentModerationService` mendeteksi kata terlarang, menolak penyimpanan ke DB, dan menampilkan pesan error validasi. | `FR-PUB-005`, `BR-REV-01` |

---

### 1.2 Modul Autentikasi & Keamanan Akun (`TC-010` s.d. `TC-015`)

| ID TC | Modul | Skenario Pengujian | Data Masukan / Kondisi | Langkah Pengujian | Hasil yang Diharapkan | Rujukan FR / Test Otomatis |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- |
| **TC-010** | Autentikasi Web | Login valid menggunakan Email atau Username | `login=username_atau_email`, `password=valid` | Kirim `POST /login` | Sesi terbuat; Admin diarahkan ke `/admin/dashboard`, Penjual ke `/penjual/dashboard`. | `FR-AUTH-001`, `TC-AUTO-003` |
| **TC-011** | Autentikasi Web | Percobaan login oleh akun yang dinonaktifkan (`is_active=0`) | Akun dengan `is_active=false` | Kirim `POST /login` | Login ditolak dengan pesan `"Akun Anda telah dinonaktifkan oleh admin."` | `FR-AUTH-001`, `BR-AUTH-01`, `TC-AUTO-003` |
| **TC-012** | Google OAuth | Login dengan Google untuk email yang sudah terdaftar dan terverifikasi | Email Google cocok dengan `users.email`, `email_verified=true` | Akses `/auth/google` $\rightarrow$ `/auth/google/callback` | `google_id` ditautkan ke akun dan pengguna berhasil masuk ke dasbor sesuai perannya. | `FR-AUTH-002`, `BR-AUTH-02`, `TC-AUTO-004` |
| **TC-013** | Google OAuth | Login dengan Google menggunakan email asing yang belum terdaftar | Email Google tidak ada di tabel `users` | Akses `/auth/google/callback` | Login ditolak dan diarahkan ke `/login` dengan pesan `"Akun dengan email ini belum terdaftar."` | `FR-AUTH-002`, `BR-AUTH-02`, `TC-AUTO-004` |
| **TC-014** | Lupa Password | Permintaan reset password untuk email terdaftar vs tidak terdaftar | Email terdaftar & email acak | Kirim `POST /forgot-password` | Kedua kondisi menampilkan pesan generik yang sama (anti-enumerasi); email hanya dikirim ke alamat yang benar-benar terdaftar. | `FR-AUTH-003`, `BR-AUTH-03`, `TC-AUTO-005` |
| **TC-015** | Proteksi Peran | Penjual mencoba mengakses URL `/admin/*` (dan sebaliknya) | Sesi login `role='penjual'` | Buka `GET /admin/dashboard` | Middleware `EnsureAdmin` menolak akses dengan kode `403 Forbidden`. | `FR-AUTH-005`, `TC-AUTO-010` |

---

### 1.3 Modul Panel Penjual (`TC-016` s.d. `TC-023`)

| ID TC | Modul | Skenario Pengujian | Data Masukan / Kondisi | Langkah Pengujian | Hasil yang Diharapkan | Rujukan FR / Test Otomatis |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- |
| **TC-016** | Dasbor Penjual | Menampilkan statistik performa toko penjual | Login sebagai `penjual` | Buka `GET /penjual/dashboard` | Menampilkan jumlah produk, tayangan, klik WA, dan rata-rata rating milik toko tersebut saja. | `FR-SEL-001`, `TC-AUTO-010` |
| **TC-017** | Produk Penjual | Menambah produk baru (`Create`) dengan foto valid | Data produk lengkap + `foto_utama` JPG $\le$ 2 MB | Kirim `POST /penjual/produk` | Produk tersimpan dengan `status='pending'`, `pending_action='create'`, dan notifikasi terkirim ke Admin. | `FR-SEL-002`, `BR-PROD-02`, `TC-AUTO-010` |
| **TC-018** | Produk Penjual | Mengedit produk yang sedang `aktif` (`Update` via `pending_changes`) | Produk `status='aktif'`, ubah nama & harga | Kirim `PUT /penjual/produk/{produk}` | Kolom utama produk & `status='aktif'` tidak berubah (tetap tayang di publik); data baru tersimpan di JSON `pending_changes` dengan `pending_action='update'`. | `FR-SEL-003`, `BR-PROD-03`, `TC-AUTO-010` |
| **TC-019** | Produk Penjual | Mengedit produk yang berstatus `ditolak` untuk diajukan ulang | Produk `status='ditolak'`, perbaiki data | Kirim `PUT /penjual/produk/{produk}` | Kolom utama langsung diperbarui, `status` menjadi `'pending'`, `pending_action='create'`, dan `alasan_ditolak` dikosongkan. | `FR-SEL-003`, `BR-PROD-03`, `TC-AUTO-010` |
| **TC-020** | Produk Penjual | Mengajukan penghapusan produk (`Delete`) | Produk milik penjual yang tidak sedang pending aksi lain | Kirim `DELETE /penjual/produk/{produk}` | Baris produk belum terhapus dari DB, melainkan ditandai `pending_action='delete'` dan mengirim notifikasi ke Admin. | `FR-SEL-004`, `BR-PROD-04`, `TC-AUTO-010` |
| **TC-021** | Produk Penjual | Mencoba mengedit/menghapus produk milik penjual lain | ID produk milik toko lain | Kirim `PUT` / `DELETE /penjual/produk/{id}` | Sistem menolak dengan kode `403 Forbidden`. | `FR-SEL-003`, `TC-AUTO-010` |
| **TC-022** | Profil Toko | Mengubah status operasional toko (`buka` $\leftrightarrow$ `tutup`) | `status_toko='tutup'` | Kirim `PATCH /penjual/status-toko` | Kolom `penjual.status_toko` berubah menjadi `'tutup'` dan lencana Toko Tutup tampil di halaman publik. | `FR-SEL-005`, `BR-STORE-01` |
| **TC-023** | Laporan Penjual | Mengunduh laporan toko dalam format PDF dan Excel (`.xlsx`) | Filter `week_offset` mingguan | Akses `GET /penjual/laporan/pdf` & `GET /penjual/laporan/sheet` | Mengembalikan file `.pdf` A4 Portrait (`application/pdf`) dan `.xlsx` 5 sheet (`application/vnd.openxmlformats-officedocument.spreadsheetml.sheet`). | `FR-SEL-006`, `BR-REP-01`, `TC-AUTO-007`, `TC-AUTO-008`, `TC-AUTO-009` |

---

### 1.4 Modul Panel Admin (`TC-024` s.d. `TC-032`)

| ID TC | Modul | Skenario Pengujian | Data Masukan / Kondisi | Langkah Pengujian | Hasil yang Diharapkan | Rujukan FR / Test Otomatis |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- |
| **TC-024** | Kelola Penjual | Mendaftarkan akun penjual baru dengan NISN 10 digit valid vs tidak valid | Kondisi A: NISN `0012345678`; Kondisi B: NISN `12345` / huruf | Kirim `POST /admin/penjual` | Kondisi A: Akun `users` & `penjual` berhasil dibuat. Kondisi B: Ditolak validasi karena NISN wajib tepat 10 digit angka unik. | `FR-ADM-002`, `BR-SELLER-02`, `TC-AUTO-010` |
| **TC-025** | Kelola Penjual | Menonaktifkan akun penjual (`toggle`) dan memverifikasi efek cabut akses | Penjual aktif yang memiliki produk aktif | Kirim `PATCH /admin/penjual/{penjual}/toggle-status` | `users.is_active` menjadi `false`, seluruh produk penjual hilang dari etalase publik, dan sesi/token penjual dicabut. | `FR-ADM-002`, `BR-PROD-01`, `BR-AUTH-01`, `TC-AUTO-010` |
| **TC-026** | Kelola Penjual | Mencoba menghapus penjual yang masih memiliki produk | Penjual dengan `produk_count > 0` | Kirim `DELETE /admin/penjual/{penjual}` | Penghapusan ditolak dengan pesan error bahwa penjual masih memiliki produk terdaftar. | `FR-ADM-002`, `BR-SELLER-01`, `TC-AUTO-010` |
| **TC-027** | Moderasi Produk | Menyetujui (`Approve`) ajuan `create`, `update`, dan `delete` | `status='aktif'` pada `PATCH /admin/produk/{produk}/update-status` | Eksekusi `Approve` untuk ketiga jenis `pending_action` | `create`: status jadi `'aktif'`. `update`: `pending_changes` disalin ke kolom utama. `delete`: baris produk & foto dihapus permanen dari DB/storage. | `FR-ADM-003`, `BR-PROD-02..04`, `TC-AUTO-010` |
| **TC-028** | Moderasi Produk | Menolak (`Reject`) ajuan moderasi tanpa/dengan alasan penolakan | `status='ditolak'`, `alasan_ditolak` kosong vs terisi | Kirim `PATCH /admin/produk/{produk}/update-status` | Jika alasan kosong: ditolak validasi. Jika terisi: `alasan_ditolak` tersimpan; untuk ajuan `update`/`delete` pada produk aktif, versi lama tetap tayang (`status='aktif'`). | `FR-ADM-003`, `BR-PROD-05`, `TC-AUTO-010` |
| **TC-029** | Kategori | Menambah, mengedit, dan mencoba menghapus kategori berproduk | Kategori kosong vs kategori berisi produk | Kirim `POST`, `PUT`, `DELETE /admin/categories` | Tambah/edit berhasil; hapus kategori berproduk ditolak oleh sistem (`BR-KAT-01`). | `FR-ADM-004`, `BR-KAT-01` |
| **TC-030** | Komentar | Menyaring dan menghapus ulasan tamu di panel Admin | ID ulasan di `/admin/komentar` | Kirim `DELETE /admin/komentar/{ulasan}` | Ulasan terhapus dari database dan hilang dari halaman detail produk. | `FR-ADM-005` |
| **TC-031** | Notifikasi Admin | Membuka halaman notifikasi, membaca satu notifikasi, dan tandai semua dibaca | Notifikasi aktivitas di tabel `notifications` | Akses `GET /admin/notifikasi`, `POST /admin/notifikasi/{id}/baca`, `baca-semua` | Halaman notifikasi tampil dengan filter `semua`/`produk`/`ulasan`; kolom `read_at` terisi dan penghitung `unread_count` berkurang menjadi `0`. | `FR-ADM-006`, `TC-AUTO-013` |
| **TC-032** | Laporan Admin | Mengunduh laporan analitik global dalam format PDF dan Excel (`.xlsx`) | Filter periode (`weekly`/`monthly`) & tab Penjual/Ulasan | Akses `/admin/laporan/penjual/pdf` & `/admin/laporan/penjual/sheet` (serta `/ulasan/pdf`, `/ulasan/sheet`) | Mengembalikan file `.pdf` A4 Landscape dan `.xlsx` multi-sheet yang telah disanitasi. | `FR-ADM-007`, `BR-REP-01`, `TC-AUTO-007`, `TC-AUTO-008`, `TC-AUTO-009` |

---

### 1.5 Modul REST API (`/api/*` & `/api/v1/*`) (`TC-033` s.d. `TC-038`)

| ID TC | Modul | Skenario Pengujian | Data Masukan / Kondisi | Langkah Pengujian | Hasil yang Diharapkan | Rujukan FR / Test Otomatis |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- |
| **TC-033** | API Auth | Login API (`POST /api/login`), cek user (`GET /api/user`), & blokir `POST /api/register` | Kredensial valid & akses `/api/register` | Panggil `POST /api/login`, `GET /api/user`, `POST /api/register` | Login mengembalikan Bearer token (`200`), `/api/user` mengembalikan data user (`200`), dan `/api/register` ditolak (`403`). | `FR-API-001`, `API-001..004`, `TC-AUTO-004` |
| **TC-034** | API Katalog | Mengambil daftar produk, kategori, penjual, detail produk, dan tracking via API (termasuk `/api/v1/*`) | Produk aktif vs produk `pending` | Panggil `GET /api/products`, `/api/categories`, `/api/sellers`, `POST /api/products/{product}/view`, `/whatsapp` | Hanya mengembalikan entitas aktif (`200 OK`); produk `pending` mengembalikan `404`. | `FR-API-001`, `API-005..015`, `TC-AUTO-004` |
| **TC-035** | API Ulasan | Mengirim ulasan bersih vs ulasan berkata kasar via `POST /api/products/{product}/reviews` | JSON ulasan bersih & JSON ulasan kasar | Panggil `POST /api/products/{product}/reviews` | Ulasan bersih mengembalikan `201 Created`; ulasan kasar ditolak dengan `422 Unprocessable Entity`. | `FR-API-001`, `API-016..017`, `TC-AUTO-004`, `TC-AUTO-011` |
| **TC-036** | API Penjual | Dasbor penjual & toggle status toko via API (`/api/seller/*`) | Token Bearer Penjual | Panggil `GET /api/seller/dashboard` dan `PATCH /api/seller/store-status` | Mengembalikan statistik/produk penjual (`200 OK`) dan mengubah status `buka`/`tutup`. | `FR-API-001`, `API-020..021`, `TC-AUTO-004` |
| **TC-037** | API Notifikasi & Profil | Mengambil notifikasi/profil dan menandai notifikasi dibaca via API | Token Bearer Valid | Panggil `GET /api/notifications`, `PATCH /api/notifications/{id}/read`, `read-all`, `GET/PATCH /api/profile` | Daftar notifikasi & profil dikembalikan (`200 OK`); status baca diperbarui. | `FR-NOTIF-002`, `API-018..024`, `TC-AUTO-004` |
| **TC-038** | API Keranjang | Mengakses endpoint keranjang belanja `/api/cart*` | Token Bearer Valid | Panggil `GET`, `POST`, `PATCH`, `DELETE /api/cart` | Seluruh endpoint mengembalikan kode `501 Not Implemented` dengan `"code": "CART_NOT_IMPLEMENTED"`. | `FR-API-001`, `API-025`, `TC-AUTO-004` |

---

## 2. Matriks Kasus Uji Otomatis PHPUnit (`TC-AUTO-001` s.d. `TC-AUTO-014`)

Berikut pemetaan seluruh **129 metode pengujian otomatis** di dalam folder `tests/Feature/`:

| ID Suite | Berkas Test | Cakupan Pengujian Utama | Jumlah Test |
| :--- | :--- | :--- | :---: |
| **TC-AUTO-001** | `tests/Feature/AdminAndPenjualFilteringAndPaginationTest.php` | Filter & paginasi daftar admin/penjual dan kontrol jumlah baris | 6 |
| **TC-AUTO-002** | `tests/Feature/AdminRekapPenjualTest.php` | Rekap performa penjual di panel Admin | 5 |
| **TC-AUTO-003** | `tests/Feature/AdminSellerReportTest.php` | Laporan detail penjual, timestamp `approved_at`, dan endpoint rating produk | 19 |
| **TC-AUTO-004** | `tests/Feature/ApiMobileSiswaMartTest.php` | REST API katalog, ulasan, moderasi, token Sanctum, dan rute `/api/v1/*` | 8 |
| **TC-AUTO-005** | `tests/Feature/Auth/GoogleLoginTest.php` | Login Google OAuth (akun terdaftar, blokir nonaktif, validasi `google_id`) | 15 |
| **TC-AUTO-006** | `tests/Feature/Auth/PasswordResetTest.php` | Lupa password, anti-enumerasi email, dan token reset valid | 15 |
| **TC-AUTO-007** | `tests/Feature/LaporanPdfTest.php` | Generasi dan validitas biner laporan PDF Admin & Penjual | 19 |
| **TC-AUTO-008** | `tests/Feature/LaporanRekapitulasiUnifiedExportTest.php` | Ekspor rekapitulasi terpadu Excel & PDF | 7 |
| **TC-AUTO-009** | `tests/Feature/PdfFilenameStandardizationTest.php` | Standardisasi penamaan berkas PDF (`PdfFilenameService`) | 10 |
| **TC-AUTO-010** | `tests/Feature/PublicCatalogRedirectTest.php` | Redirect `301` rute `/produk` ke `/kategori` | 1 |
| **TC-AUTO-011** | `tests/Feature/SecurityHardeningTest.php` | Rate limiter, otorisasi, sanitasi XSS/formula, dan proteksi endpoint | 5 |
| **TC-AUTO-012** | `tests/Feature/SellerExportAuthorizationTest.php` | Pembatasan otorisasi unduhan laporan antar penjual | 1 |
| **TC-AUTO-013** | `tests/Feature/SiswaMartRefinementRegressionTest.php` | Navbar konsisten, rute `/{toko}/{produk}`, notifikasi Admin tanpa setting + host-safe redirect, serta verifikasi View/Function/Procedure/Trigger MySQL | 16 |
| **TC-AUTO-014** | `tests/Feature/XlsxWriterTest.php` | Pembentukan workbook OpenXML `.xlsx` oleh `App\Support\XlsxWriter` | 2 |

---

## 3. Rujukan Kode

- `tests/Feature/SiswaMartRefinementRegressionTest.php`
- `tests/Feature/AdminSellerReportTest.php`
- `tests/Feature/LaporanPdfTest.php`
- `tests/Feature/Auth/GoogleLoginTest.php`
- `tests/Feature/Auth/PasswordResetTest.php`
- `tests/Feature/ApiMobileSiswaMartTest.php`
