# Spesifikasi Kasus Uji (Test Cases) — SiswaMart

| Informasi Dokumen | Detail |
| :--- | :--- |
| **Judul** | Spesifikasi Kasus Uji Black-Box (`TC-001`–`TC-038`) & Test Otomatis (`TC-AUTO-001`–`TC-AUTO-010`) |
| **Versi** | `1.1.0` |
| **Tanggal** | 27 Oktober 2026 |
| **Status** | Final (Source-Code & `Pengujian_Black_Box_SiswaMart.xlsx` Aligned) |
| **Penulis** | Tim Dokumentasi Teknis & Rekayasa Perangkat Lunak |
| **Sumber Data** | `Pengujian_Black_Box_SiswaMart.xlsx`, `tests/Feature/*.php`, `docs/02-srs/SRS.md` |

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
| **TC-004** | Detail Produk | Melihat detail produk aktif & pencatatan `views` | Slug produk aktif | Buka `GET /produk/{slug}` | Halaman detail tampil (`200 OK`), kolom `views` bertambah `+1`, dan log `type='view'` tercatat di `produk_views_logs`. | `FR-PUB-003`, `TC-AUTO-010` |
| **TC-005** | Detail Produk | Mengakses URL produk yang berstatus `pending`/`ditolak` atau penjual nonaktif | Slug produk `pending` atau penjual `is_active=0` | Buka `GET /produk/{slug}` | Sistem menolak akses dengan halaman `404 Not Found`. | `FR-PUB-003`, `BR-PROD-01`, `TC-AUTO-010` |
| **TC-006** | Pemesanan WA | Klik Pesan via WhatsApp saat stok `tersedia`/`po` dan toko `buka` | `stok='tersedia'`, `status_toko='buka'`, `nomor_whatsapp='08123...'` | Klik tombol WA (`GET /produk/{slug}/whatsapp`) | Mencatat log `type='whatsapp'` dan redirect `302` ke `https://wa.me/628123...` dengan pesan pemesanan standar. | `FR-PUB-004`, `BR-STORE-01`, `TC-AUTO-010` |
| **TC-007** | Pemesanan WA | Klik Pesan via WhatsApp saat toko `tutup` vs saat `stok='habis'` | Kondisi A: `status_toko='tutup'`; Kondisi B: `stok='habis'` | Akses `GET /produk/{slug}/whatsapp` | Kondisi A: Redirect ke WA dengan pesan konfirmasi jam buka toko. Kondisi B: Diblokir & redirect kembali ke produk dengan pesan stok habis. | `FR-PUB-004`, `BR-STORE-01`, `TC-AUTO-010` |
| **TC-008** | Ulasan Tamu | Mengirim ulasan produk yang sopan & valid | `guest_username='Rina'`, `rating=5`, `komentar='Rasanya enak sekali!'` | Kirim `POST /produk/{slug}/ulasan` | Ulasan tersimpan (`status='aktif'`), tampil di halaman produk, dan mengirim notifikasi ke Admin. | `FR-PUB-005`, `BR-REV-01` |
| **TC-009** | Ulasan Tamu | Mengirim ulasan yang mengandung kata kasar / variasi *leetspeak* | `guest_username='User'`, `rating=1`, `komentar` mengandung kata terlarang / `b0d0h` | Kirim `POST /produk/{slug}/ulasan` | `CommentModerationService` mendeteksi kata terlarang, menolak penyimpanan ke DB, dan menampilkan pesan error validasi. | `FR-PUB-005`, `BR-REV-01` |

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
| **TC-022** | Profil Toko | Mengubah status operasional toko (`buka` $\leftrightarrow$ `tutup`) | `status_toko='tutup'` | Kirim `PATCH /penjual/profil/status-toko` | Kolom `penjual.status_toko` berubah menjadi `'tutup'` dan lencana Toko Tutup tampil di halaman publik. | `FR-SEL-005`, `BR-STORE-01`, `TC-AUTO-010` |
| **TC-023** | Laporan Penjual | Mengunduh laporan toko dalam format PDF dan Excel (`.xlsx`) | Filter opsional (`status`, `kategori_id`, `stok`) | Akses `/penjual/laporan/export` & `/penjual/laporan/export-excel` | Mengembalikan file `.pdf` (`application/pdf`) dan `.xlsx` 2 sheet (`application/vnd.openxmlformats-officedocument.spreadsheetml.sheet`). | `FR-SEL-006`, `BR-REP-01`, `TC-AUTO-009` |

---

### 1.4 Modul Panel Admin (`TC-024` s.d. `TC-032`)

| ID TC | Modul | Skenario Pengujian | Data Masukan / Kondisi | Langkah Pengujian | Hasil yang Diharapkan | Rujukan FR / Test Otomatis |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- |
| **TC-024** | Kelola Penjual | Mendaftarkan akun penjual baru dengan NISN 10 digit valid vs tidak valid | Kondisi A: NISN `0012345678`; Kondisi B: NISN `12345` / huruf | Kirim `POST /admin/penjual` | Kondisi A: Akun `users` & `penjual` berhasil dibuat. Kondisi B: Ditolak validasi karena NISN wajib tepat 10 digit angka unik. | `FR-ADM-002`, `BR-SELLER-02`, `TC-AUTO-010` |
| **TC-025** | Kelola Penjual | Menonaktifkan akun penjual (`toggle`) dan memverifikasi efek cabut akses | Penjual aktif yang memiliki produk aktif | Kirim `PATCH /admin/penjual/{penjual}/toggle` | `users.is_active` menjadi `false`, seluruh produk penjual hilang dari etalase publik, dan sesi/token penjual dicabut. | `FR-ADM-002`, `BR-PROD-01`, `BR-AUTH-01`, `TC-AUTO-010` |
| **TC-026** | Kelola Penjual | Mencoba menghapus penjual yang masih memiliki produk | Penjual dengan `produk_count > 0` | Kirim `DELETE /admin/penjual/{penjual}` | Penghapusan ditolak dengan pesan error bahwa penjual masih memiliki produk terdaftar. | `FR-ADM-002`, `BR-SELLER-01`, `TC-AUTO-010` |
| **TC-027** | Moderasi Produk | Menyetujui (`Approve`) ajuan `create`, `update`, dan `delete` | `status='aktif'` pada `PATCH /admin/produk/{produk}/status` | Eksekusi `Approve` untuk ketiga jenis `pending_action` | `create`: status jadi `'aktif'`. `update`: `pending_changes` disalin ke kolom utama. `delete`: baris produk & foto dihapus permanen dari DB/storage. | `FR-ADM-003`, `BR-PROD-02..04`, `TC-AUTO-010` |
| **TC-028** | Moderasi Produk | Menolak (`Reject`) ajuan moderasi tanpa/dengan alasan penolakan | `status='ditolak'`, `alasan_ditolak` kosong vs terisi | Kirim `PATCH /admin/produk/{produk}/status` | Jika alasan kosong: ditolak validasi. Jika terisi: `alasan_ditolak` tersimpan; untuk ajuan `update`/`delete` pada produk aktif, versi lama tetap tayang (`status='aktif'`). | `FR-ADM-003`, `BR-PROD-05`, `TC-AUTO-010` |
| **TC-029** | Kategori | Menambah, mengedit, dan mencoba menghapus kategori berproduk | Kategori kosong vs kategori berisi produk | Kirim `POST`, `PUT`, `DELETE /admin/kategori` | Tambah/edit berhasil; hapus kategori berproduk ditolak oleh sistem (`BR-KAT-01`). | `FR-ADM-004`, `BR-KAT-01` |
| **TC-030** | Komentar | Menyaring dan menghapus ulasan tamu di panel Admin | ID ulasan di `/admin/komentar` | Kirim `DELETE /admin/komentar/{ulasan}` | Ulasan terhapus dari database dan hilang dari halaman detail produk. | `FR-ADM-005` |
| **TC-031** | Notifikasi Admin | Membaca satu notifikasi, tandai semua dibaca, dan hapus semua | Notifikasi aktivitas di tabel `notifications` | Akses `/admin/notifikasi/{id}/baca`, `baca-semua`, `hapus-semua` | Kolom `read_at` terisi / notifikasi terhapus dan penghitung `unread_count` berkurang menjadi `0`. | `FR-ADM-006`, `TC-AUTO-010` |
| **TC-032** | Laporan Admin | Mengunduh laporan analitik global dalam format PDF dan Excel (`.xlsx`) | Filter periode tanggal, penjual, kategori, status | Akses `/admin/laporan/export` & `/admin/laporan/export-excel` | Mengembalikan file `.pdf` A4 Landscape dan `.xlsx` 3 sheet (*Ringkasan*, *Performa Penjual*, *Daftar Produk*) yang telah disanitasi. | `FR-ADM-007`, `BR-REP-01`, `TC-AUTO-009` |

---

### 1.5 Modul REST API (`/api/*`) (`TC-033` s.d. `TC-038`)

| ID TC | Modul | Skenario Pengujian | Data Masukan / Kondisi | Langkah Pengujian | Hasil yang Diharapkan | Rujukan FR / Test Otomatis |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- |
| **TC-033** | API Auth | Login API (`POST /api/login`), cek profil (`GET /api/me`), & `POST /api/register` | Kredensial valid & akses `/api/register` | Panggil `POST /api/login`, `GET /api/me`, `POST /api/register` | Login mengembalikan Bearer token (`200`), `/api/me` mengembalikan data user (`200`), dan `/api/register` ditolak (`403`). | `FR-API-001`, `API-001..004`, `TC-AUTO-010` |
| **TC-034** | API Katalog | Mengambil daftar produk, kategori, penjual, dan detail produk via API | Produk aktif vs produk `pending` | Panggil `GET /api/products`, `/api/categories`, `/api/sellers`, `/api/products/{slug}` | Hanya mengembalikan entitas aktif (`200 OK`); produk `pending` mengembalikan `404`. | `FR-API-002`, `API-005..012`, `TC-AUTO-010` |
| **TC-035** | API Ulasan | Mengirim ulasan bersih vs ulasan berkata kasar via `POST /api/products/{slug}/reviews` | JSON ulasan bersih & JSON ulasan kasar | Panggil `POST /api/products/{slug}/reviews` | Ulasan bersih mengembalikan `201 Created`; ulasan kasar ditolak dengan `422 Unprocessable Entity`. | `FR-API-003`, `API-013..015`, `TC-AUTO-010` |
| **TC-036** | API Penjual | Siklus pengajuan produk penjual via API (`/api/seller/products`) | Token Bearer Penjual | Panggil `POST`, `PUT`, `DELETE /api/seller/products` | Mengikuti mekanisme `pending_action` (`create`, `update` via `pending_changes`, `delete`) persis seperti Web. | `FR-API-004`, `API-018..021`, `TC-AUTO-010` |
| **TC-037** | API Admin | Moderasi produk via API (`/api/admin/moderation/products/{produk}/status`) | Token Bearer Admin | Panggil `PATCH /api/admin/moderation/products/{id}/status` | Menyetujui atau menolak ajuan moderasi produk dengan respons JSON `200 OK`. | `FR-API-004`, `API-022..023`, `TC-AUTO-010` |
| **TC-038** | API Keranjang | Mengakses endpoint keranjang belanja `/api/cart*` | Token Bearer Valid | Panggil `GET`, `POST`, `PATCH`, `DELETE /api/cart` | Seluruh endpoint mengembalikan kode `501 Not Implemented` dengan `"code": "CART_NOT_IMPLEMENTED"`. | `FR-API-005`, `API-026`, `TC-AUTO-010` |

---

## 2. Matriks Kasus Uji Otomatis PHPUnit (`TC-AUTO-001` s.d. `TC-AUTO-010`)

Berikut pemetaan seluruh **75 metode pengujian otomatis** di dalam folder `tests/`:

| ID Suite | Berkas Test | Daftar Metode Pengujian Utama | Jumlah Test |
| :--- | :--- | :--- | :---: |
| **TC-AUTO-001** | `tests/Unit/ExampleTest.php` | `test_that_true_is_true` | 1 |
| **TC-AUTO-002** | `tests/Feature/ExampleTest.php` | `test_the_application_returns_a_successful_response` | 1 |
| **TC-AUTO-003** | `tests/Feature/Auth/AuthenticationTest.php` | `test_login_screen_can_be_rendered`, `test_users_can_authenticate_using_the_login_screen`, `test_users_can_authenticate_using_username`, `test_users_can_not_authenticate_with_invalid_password`, `test_inactive_users_can_not_authenticate`, `test_seller_flagged_for_password_change_can_login_and_access_dashboard`, `test_users_can_logout` | 7 |
| **TC-AUTO-004** | `tests/Feature/Auth/GoogleAuthenticationTest.php` | `test_user_can_redirect_to_google_oauth`, `test_existing_penjual_can_login_with_google_and_link_google_id`, `test_existing_admin_can_login_with_google`, `test_unregistered_google_email_is_rejected`, `test_inactive_user_cannot_login_with_google`, `test_unverified_google_email_is_rejected` | 6 |
| **TC-AUTO-005** | `tests/Feature/Auth/PasswordResetTest.php` | `test_reset_password_link_screen_can_be_rendered`, `test_reset_password_link_can_be_requested`, `test_reset_password_request_for_unregistered_email_returns_generic_status_without_sending_mail`, `test_reset_password_screen_can_be_rendered`, `test_password_can_be_reset_with_valid_token` | 5 |
| **TC-AUTO-006** | `tests/Feature/Auth/PasswordConfirmationTest.php` | `test_confirm_password_screen_can_be_rendered`, `test_password_can_be_confirmed`, `test_password_is_not_confirmed_with_invalid_password` | 3 |
| **TC-AUTO-007** | `tests/Feature/Auth/PasswordUpdateTest.php` | `test_password_can_be_updated`, `test_correct_password_must_be_provided_to_update_password` | 2 |
| **TC-AUTO-008** | `tests/Feature/ProfileTest.php` | `test_profile_page_is_displayed`, `test_profile_information_can_be_updated`, `test_email_verification_status_is_unchanged_when_the_email_address_is_unchanged`, `test_user_can_delete_their_account`, `test_correct_password_must_be_provided_to_delete_account` | 5 |
| **TC-AUTO-009** | `tests/Feature/ReportDownloadTest.php` | `test_admin_can_download_report_pdf`, `test_admin_can_download_report_excel`, `test_admin_report_handles_invalid_filters_gracefully`, `test_admin_report_supports_custom_date_range_filter`, `test_penjual_can_download_report_pdf`, `test_penjual_can_download_report_excel`, `test_penjual_report_only_contains_own_products`, `test_guest_cannot_download_reports`, `test_penjual_cannot_download_admin_reports` | 9 |
| **TC-AUTO-010** | `tests/Feature/AdminAndPenjualFilteringAndPaginationTest.php` | 36 metode pengujian mencakup: filter & paginasi admin/penjual, pembuatan penjual dengan NISN 10 digit, blokir hapus penjual berproduk, alur moderasi `create`/`update`/`delete`, sakelar `status_toko`, pelacakan WhatsApp, notifikasi admin, serta pengujian endpoint `/api/*` (login, katalog, ulasan, moderasi, dan `/api/cart` 501). | 36 |

---

## 3. Rujukan Kode

- `Pengujian_Black_Box_SiswaMart.xlsx`
- `tests/Feature/AdminAndPenjualFilteringAndPaginationTest.php#L1-L1480`
- `tests/Feature/ReportDownloadTest.php#L1-L265`
- `tests/Feature/Auth/AuthenticationTest.php#L1-L125`
- `tests/Feature/Auth/GoogleAuthenticationTest.php#L1-L195`
- `tests/Feature/Auth/PasswordResetTest.php#L1-L115`
