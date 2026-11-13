# Software Requirements Specification (SRS) — SiswaMart

## Informasi Dokumen

| Atribut | Detail |
|---|---|
| **Judul Dokumen** | Software Requirements Specification (SRS) — SiswaMart |
| **Versi Dokumen** | 1.1.0 |
| **Tanggal Pembaruan** | 27 Oktober 2026 |
| **Status** | Final (*Source-Code Backed*) |
| **Penulis** | Tim Rekayasa Perangkat Lunak (SMK RPL) |
| **Sumber Data** | `routes/web.php`, `routes/auth.php`, `routes/api.php`, `app/Http/Controllers/`, `app/Services/`, `app/Models/`, `tests/Feature/` |
| **Dokumen Terkait** | [PRD.md](../01-prd/PRD.md) • [USE_CASE.md](../05-desain/USE_CASE.md) • [ATURAN_BISNIS.md](../05-desain/ATURAN_BISNIS.md) • [API.md](../06-api/API.md) • [TEST_CASE.md](../08-pengujian/TEST_CASE.md) |

---

## Daftar Isi

1. [Pendahuluan](#1-pendahuluan)
2. [Deskripsi Umum Sistem](#2-deskripsi-umum-sistem)
3. [Spesifikasi Kebutuhan Fungsional (FR)](#3-spesifikasi-kebutuhan-fungsional-fr)
4. [Spesifikasi Kebutuhan Non-Fungsional (NFR)](#4-spesifikasi-kebutuhan-non-fungsional-nfr)
5. [Spesifikasi Antarmuka Eksternal](#5-spesifikasi-antarmuka-eksternal)
6. [Matriks Keterlacakan Kebutuhan (Requirements Traceability Matrix / RTM)](#6-matriks-keterlacakan-kebutuhan-requirements-traceability-matrix--rtm)
7. [Rujukan Kode](#7-rujukan-kode)

---

## 1. Pendahuluan

### 1.1 Tujuan Dokumen
Dokumen *Software Requirements Specification* (SRS) ini menjabarkan spesifikasi teknis kebutuhan fungsional dan non-fungsional aplikasi **SiswaMart** secara terukur dan dapat diverifikasi. Setiap kebutuhan dihubungkan secara langsung ke rute, controller, service, aturan bisnis, serta kasus pengujian di dalam repositori.

### 1.2 Ruang Lingkup Produk
SiswaMart adalah aplikasi web dan REST API berbasis Laravel 13 (`v13.38.0`) yang menyediakan katalog promosi produk kantin sekolah dan siswa wirausaha dengan mekanisme pemesanan langsung ke WhatsApp penjual (*Direct-to-WhatsApp Ordering*), sistem moderasi produk berjenjang (*staging moderation*), filter kata kasar otomatis pada ulasan tamu, serta pelaporan analitik terpadu berformat PDF dan Excel (`.xlsx`).

---

## 2. Deskripsi Umum Sistem

### 2.1 Kelas dan Karakteristik Pengguna

| Kelas Pengguna | Peran di Database (`users.role`) | Middleware Proteksi | Hak Akses Utama |
|---|---|---|---|
| **Pengunjung / Pembeli (`Guest`)** | Tidak memiliki akun (Anonim) | `web` (+ `throttle` pada aksi sensitif) | Menelusuri beranda, kategori, detail produk, klik tombol pesan WhatsApp, dan mengirim ulasan tamu. |
| **Penjual (`Seller`)** | `penjual` | `web`, `auth`, `penjual` (`EnsurePenjual`) / API: `auth:sanctum`, `active` | Mengelola status buka/tutup toko, mengajukan tambah/edit/hapus produk miliknya, memantau metrik toko, dan mengunduh laporan PDF/Excel mingguan. |
| **Administrator (`Admin`)** | `admin` | `web`, `auth`, `admin` (`EnsureAdmin`) / API: `auth:sanctum`, `active` | Mengelola akun penjual, memoderasi pengajuan produk, mengelola kategori, menghapus komentar ulasan, memantau notifikasi sistem, dan mengunduh laporan agregat PDF/Excel. |

### 2.2 Lingkungan Operasi & Batasan Desain
1. **Lingkungan Server:** PHP `^8.3` (`8.3.30`), MySQL/MariaDB (InnoDB), Web Server Apache/Nginx (Laragon untuk lokal; cPanel/DirectAdmin dengan pemisahan `public_html` dan folder `core` untuk produksi).
2. **Zona Waktu:** Konfigurasi dasar framework `UTC` (`config/app.php#L68`), sementara seluruh perhitungan periode laporan menggunakan zona waktu eksplisit `Asia/Jakarta` (WIB) (`app/Services/LaporanAdminService.php#L24`, `app/Services/LaporanPenjualService.php#L24`).
3. **Ketiadaan Transaksi Pembayaran Internal:** Sistem tidak memproses pembayaran atau keranjang belanja; endpoint `/api/cart*` secara eksplisit dikunci pada status `HTTP 501 Not Implemented` (`app/Http/Controllers/Api/CartController.php#L9-L15`).

---

## 3. Spesifikasi Kebutuhan Fungsional (FR)

### 3.1 Modul Autentikasi & Manajemen Sesi (`FR-AUTH`)

| ID Kebutuhan | Nama Kebutuhan | Spesifikasi Teknis Input, Proses & Output | Status |
|---|---|---|---|
| **FR-AUTH-001** *(FR-001)* | Login Multi-Identitas (Email/Username) & Rate Limit | **Input:** `email` (string: email atau username), `password` (string), `remember` (boolean opsional) pada `POST /login`.<br>**Proses:** `LoginRequest::authenticate()` mendeteksi format email via `filter_var(..., FILTER_VALIDATE_EMAIL)` (`L45-L46`), memeriksa batas maksimal 5 percobaan gagal per `lower(email)\|ip` (`L73-L97`), memverifikasi kredensial dan `is_active == true` (`L48-L63`), meregenerasi sesi, serta mencatat `last_login_at` pada profil `penjual` (`AuthenticatedSessionController.php#L31-L40`).<br>**Output:** Redirect ke `route('admin.dashboard')` untuk `admin` atau `route('penjual.dashboard')` untuk `penjual`. | `[Implemented]` |
| **FR-AUTH-002** *(FR-002)* | Google OAuth 2.0 Single Sign-On (SSO) | **Input:** Alur OAuth `GET /auth/google` dan callback `GET /auth/google/callback`.<br>**Proses:** `GoogleAuthController` memverifikasi konfigurasi `services.google`, menyinkronkan URL redirect lokal/produksi (`L165-L183`), mengambil profil Google (dengan fallback `stateless()` saat `InvalidStateException`, `L42-L55`), memvalidasi email terverifikasi (`L69-L75`), mencari user berdasarkan `google_id` atau `LOWER(email)` (`L78-L84`), mencegah pembajakan akun jika sudah tertaut ke `google_id` berbeda (`L86-L90`), dan menolak email yang belum terdaftar di DB (`L102-L106`).<br>**Output:** Sesi login terbentuk dan diarahkan sesuai role. | `[Implemented]` |
| **FR-AUTH-003** *(FR-003)* | Penegakan Status Akun Aktif (`is_active`) | **Input:** Setiap request terautentikasi pada rute Admin, Penjual, atau API Sanctum.<br>**Proses:** Middleware `EnsureAdmin` (`L26-L34`), `EnsurePenjual` (`L26-L34`), dan `EnsureUserIsActive` (`L19-L41`) memeriksa `user->is_active`. Jika `false`, seluruh token Sanctum dicabut (`$user->tokens()->delete()`) dan sesi web di-logout paksa.<br>**Output:** Redirect ke `/login` dengan pesan error (web) atau JSON `403 Forbidden` (API). | `[Implemented]` |
| **FR-AUTH-004** *(FR-004)* | Penutupan Registrasi Mandiri Publik | **Input:** Akses ke rute registrasi web atau `POST /api/register`.<br>**Proses:** Rute `register` di `routes/auth.php#L19-L22` dinonaktifkan; `Api\AuthController::register` (`L29-L36`) langsung menolak permintaan.<br>**Output:** HTTP `404` pada web `/register` dan HTTP `403 Forbidden` pada `POST /api/register`. | `[Implemented]` |
| **FR-AUTH-005** *(FR-005)* | Lupa & Reset Kata Sandi dengan Anti-Enumerasi | **Input:** `POST /forgot-password` (`email`, `throttle:5,1`) dan `POST /reset-password` (`token`, `email`, `password` min 8 karakter + `confirmed`, `throttle:5,1`).<br>**Proses:** `PasswordResetLinkController::store` memeriksa apakah user aktif dan ber-role `admin`/`penjual`. Jika tidak ditemukan/nonaktif, sistem tetap mengembalikan pesan sukses generik `__('passwords.sent')` tanpa mengirim email (`L37-L47`). Jika valid, mengirim `ResetPasswordNotification` (`emails.reset-password`). Saat reset (`NewPasswordController::store`), password di-hash, `remember_token` dirotasi, dan `must_change_password` diset `false` (`L55-L71`).<br>**Output:** Redirect ke `/login` dengan status sukses. | `[Implemented]` |
| **FR-AUTH-006** *(FR-006)* | Flag Wajib Ganti Password (`must_change_password`) | **Input:** Kolom boolean `users.must_change_password` (default `false`).<br>**Proses:** Tersedia pada model `User` (`L16, L59`) dan di-reset menjadi `false` saat reset password (`NewPasswordController.php#L63`), namun belum ada middleware pengalihan paksa saat login.<br>**Output:** Kolom tersimpan di DB. | `[Partial]` |

### 3.2 Modul Katalog Publik, Pencarian & Pemesanan WhatsApp (`FR-PROD` & `FR-WA`)

| ID Kebutuhan | Nama Kebutuhan | Spesifikasi Teknis Input, Proses & Output | Status |
|---|---|---|---|
| **FR-PROD-001** *(FR-007)* | Beranda Katalog Publik | **Input:** `GET /` dengan query opsional `q` dan `kategori`.<br>**Proses:** `Public\HomeController::index` (`L18-L102`) memuat 3 produk hero terbaru, harga minimum aktif, daftar kategori + `produk_aktif_count`, katalog mobile terpaginasi (12 item), 5 produk rekomendasi (rating tertinggi), 3 produk populer (views tertinggi), 3 ulasan pilihan terbaru, dan statistik total.<br>**Output:** Render view `public.home.index`. | `[Implemented]` |
| **FR-PROD-002** *(FR-008)* | Filter Visibilitas Publik & Redirect `/produk` | **Input:** `GET /produk` atau query katalog publik.<br>**Proses:** `GET /produk` melakukan redirect `301` ke `/kategori` (`routes/web.php#L14`). Seluruh query produk publik wajib memakai `Produk::publicVisible()` (`status = 'aktif'` dan `penjual.user.is_active = true`, `Produk.php#L108-L114`).<br>**Output:** Produk nonaktif/pending/ditolak atau milik penjual nonaktif tidak pernah muncul di publik. | `[Implemented]` |
| **FR-PROD-003** *(FR-009)* | Eksplorasi Kategori, Pencarian, Filter Harga & Urutan | **Input:** `GET /kategori` atau `GET /kategori/{kategori:slug}` dengan query `q`, `min_price`, `max_price`, `sort` (`terbaru\|termurah\|termahal\|terpopuler`).<br>**Proses:** `Public\KategoriController::index` (`L16-L62`) dan `show` (`L67-L115`) menyaring produk publik berdasarkan kata kunci nama/deskripsi, rentang harga numerik, dan urutan.<br>**Output:** Render view `public.kategori.index` atau `public.kategori.show`. | `[Implemented]` |
| **FR-PROD-004** *(FR-010)* | Paginasi Katalog Publik | **Input:** Parameter `page` pada katalog kategori/beranda.<br>**Proses:** Menggunakan `paginate(12)->withQueryString()`.<br>**Output:** Navigasi halaman mempertahankan filter aktif. | `[Implemented]` |
| **FR-PROD-005** *(FR-011)* | Halaman Detail Produk & Produk Terkait | **Input:** `GET /produk/{produk:slug}` (`throttle:60,1`).<br>**Proses:** `Public\ProdukController::show` (`L53-L86`) memverifikasi produk aktif & penjual aktif (jika tidak, `404`), memuat relasi `kategori`, `penjual.user`, daftar ulasan publik (`publicVisible()->latest()`), dan 4 produk terkait dari kategori yang sama.<br>**Output:** Render view `public.produk.show`. | `[Implemented]` |
| **FR-PROD-006** *(FR-012)* | Pencatatan Analitik Kunjungan Produk (`view`) | **Input:** Kunjungan ke `GET /produk/{produk:slug}` atau `POST /api/products/{product}/view`.<br>**Proses:** Menambah counter `$produk->increment('views')` dan menyimpan baris baru di `produk_views_logs` dengan `type = 'view'` di dalam blok `try-catch` (`Public\ProdukController.php#L57-L65`).<br>**Output:** Counter `views` dan log harian bertambah 1. | `[Implemented]` |
| **FR-WA-001** *(FR-013)* | Pengalihan Pemesanan WhatsApp & Normalisasi Nomor | **Input:** `GET /produk/{produk:slug}/whatsapp` (`throttle:30,1`).<br>**Proses:** `Public\ProdukController::trackWhatsapp` (`L91-L121`) menghapus karakter non-digit dari `nomor_whatsapp`, mengubah awalan `0` menjadi `62` (`L95-L102`), atau mengembalikan flash error jika nomor kosong.<br>**Output:** Redirect HTTP 302 ke `https://wa.me/{waNumber}?text=...`. | `[Implemented]` |
| **FR-WA-002** *(FR-014)* | Penyesuaian Pesan WhatsApp Saat Toko Tutup | **Input:** Status `penjual.status_toko === 'tutup'` saat klik WhatsApp.<br>**Proses:** Menambahkan prefiks `[Pemberitahuan: Toko sedang tutup, balasan mungkin tertunda]\n\n` pada pesan template (`Public\ProdukController.php#L113-L115`).<br>**Output:** Parameter `text` URL WhatsApp menyertakan pemberitahuan toko tutup. | `[Implemented]` |
| **FR-WA-003** *(FR-015)* | Pencatatan Log Klik WhatsApp (`whatsapp`) | **Input:** Klik pada rute WhatsApp web atau `POST /api/products/{product}/whatsapp`.<br>**Proses:** Menyimpan baris baru di `produk_views_logs` dengan `type = 'whatsapp'` (`Public\ProdukController.php#L105-L111`).<br>**Output:** Interaksi klik WA tercatat di basis data. | `[Implemented]` |

### 3.3 Modul Ulasan Tamu & Moderasi Kata Kasar (`FR-REV`)

| ID Kebutuhan | Nama Kebutuhan | Spesifikasi Teknis Input, Proses & Output | Status |
|---|---|---|---|
| **FR-REV-001** *(FR-016)* | Pengiriman Ulasan Tamu & Rate Limit | **Input:** `POST /produk/{produk:slug}/ulasan` (`throttle:3,1`) dengan `guest_username` (`required\|string\|max:100`), `rating` (`required\|integer\|min:1\|max:5`), `komentar` (`nullable\|string\|max:1000`).<br>**Proses:** `Public\UlasanController::store` (`L18-L69`) memvalidasi input dan memastikan produk serta penjual aktif.<br>**Output:** Ulasan disimpan atau ditolak dengan pesan validasi. | `[Implemented]` |
| **FR-REV-002** *(FR-017)* | Pemindaian Kata Kasar Otomatis (*Leetspeak-Aware*) | **Input:** Nilai `guest_username` dan `komentar` dari form ulasan web maupun API.<br>**Proses:** `CommentModerationService::moderate()` (`L28-L133`) menormalisasi karakter *leetspeak* (`@`->`a`, `4`->`a`, `0`->`o`, `1`->`i`, `!`->`i`, `3`->`e`, `$`->`s`, `5`->`s`, `7`->`t`) dan mencocokkan regex batas kata terhadap kamus gabungan `resources/dictionaries/badwords-list.json` + tabel `bad_words` (`is_active = true`). Jika terdeteksi, penyimpanan dibatalkan.<br>**Output:** Redirect kembali ke `#ulasan` dengan `review_warning` (web) atau HTTP `422` (API). | `[Implemented]` |
| **FR-REV-003** *(FR-018)* | Visibilitas Ulasan Publik & Notifikasi Admin | **Input:** Ulasan yang lolos moderasi.<br>**Proses:** Disimpan ke tabel `ulasan` dengan `moderation_status = 'approved'`, `status = 'approved'`, `is_censored = false`, lalu memicu `AdminNotificationService::notifyAdmins` (`activity: 'ulasan.ditambahkan'`).<br>**Output:** Ulasan langsung tampil pada `Ulasan::publicVisible()` dan lonceng notifikasi Admin bertambah. | `[Implemented]` |

### 3.4 Modul Dasbor & Operasional Penjual (`FR-SELLER`)

| ID Kebutuhan | Nama Kebutuhan | Spesifikasi Teknis Input, Proses & Output | Status |
|---|---|---|---|
| **FR-SELLER-001** *(FR-019)* | Dasbor Ringkasan Performa Penjual | **Input:** `GET /penjual/dashboard`.<br>**Proses:** `Penjual\DashboardController::index` (`L17-L47`) menghitung total produk, produk aktif, produk pending, total views, total ulasan publik, 3 produk terpopuler, dan 5 produk terbaru milik penjual yang login.<br>**Output:** Render view `penjual.dashboard`. | `[Implemented]` |
| **FR-SELLER-002** *(FR-020)* | Toggle Status Toko (`buka` / `tutup`) | **Input:** `PATCH /penjual/status-toko`.<br>**Proses:** `Penjual\DashboardController::toggleStatusToko` (`L52-L61`) membalik nilai `penjual.status_toko` antara `'buka'` dan `'tutup'`.<br>**Output:** Redirect kembali dengan flash `status`. | `[Implemented]` |
| **FR-SELLER-003** *(FR-021)* | Pengajuan Produk Baru (`pending_action = 'create'`) | **Input:** `POST /penjual/produk` dengan `nama`, `kategori_id`, `deskripsi`, `harga` (`numeric\|min:0`), `stok` (`in:tersedia,po,habis`), `label_promosi` (`nullable\|max:100`), `foto_utama` (`nullable\|image\|mimes:jpg,jpeg,png,webp\|max:2048`).<br>**Proses:** `Penjual\ProdukController::store` (`L136-L181`) membuat slug unik, menyimpan foto ke disk `public/produk`, menyetel `status = 'pending'` & `pending_action = 'create'`, mencatat aktivitas penjual, dan mengirim notifikasi ke Admin.<br>**Output:** Redirect ke `penjual.produk.index`. | `[Implemented]` |
| **FR-SELLER-004** *(FR-022)* | Pengajuan Edit Produk ke Staging (`pending_changes`) | **Input:** `PUT /penjual/produk/{produk}`.<br>**Proses:** `Penjual\ProdukController::update` (`L200-L250`) memverifikasi kepemilikan (`403` jika bukan miliknya), menolak jika `pending_action` belum kosong, menyimpan data baru ke kolom JSON `pending_changes` dengan `pending_action = 'update'` tanpa mengubah kolom utama produk.<br>**Output:** Data tayang di katalog publik tetap memakai versi lama sampai disetujui Admin. | `[Implemented]` |
| **FR-SELLER-005** *(FR-023)* | Pengajuan Hapus Produk (`pending_action = 'delete'`) | **Input:** `DELETE /penjual/produk/{produk}`.<br>**Proses:** `Penjual\ProdukController::destroy` (`L255-L288`) memverifikasi kepemilikan, menolak jika ada pengajuan aktif, dan menyetel `pending_action = 'delete'`.<br>**Output:** Produk tetap ada di DB hingga Admin menyetujui penghapusan. | `[Implemented]` |
| **FR-SELLER-006** *(FR-024)* | Filter, Pencarian & Paginasi Inventaris Penjual | **Input:** `GET /penjual/produk` dengan `status`, `search`, `kategori_id`, `stok`, `per_page` (`10, 25, 50, all`).<br>**Proses:** `Penjual\ProdukController::index` (`L38-L101`) menyaring produk milik penjual.<br>**Output:** Render view `penjual.produk.index`. | `[Implemented]` |
| **FR-SELLER-007** *(FR-025)* | Laporan Mingguan Penjual & Ekspor PDF (A4 Portrait) | **Input:** `GET /penjual/laporan` dan `GET /penjual/laporan/pdf` dengan `week_offset` (`<= 0`).<br>**Proses:** `LaporanPenjualService::getSummary` (`L19-L189`) menghitung metrik 7 hari terakhir vs 7 hari sebelumnya; `Penjual\LaporanController::pdf` (`L269-L286`) me-render PDF via DomPDF.<br>**Output:** Tampilan laporan web atau unduhan `rekap-penjual-{slug}-{Ymd}.pdf`. | `[Implemented]` |
| **FR-SELLER-008** *(FR-026)* | Ekspor Laporan Mingguan Penjual Format Excel (`.xlsx`) | **Input:** `GET /penjual/laporan/sheet` dengan `week_offset` (`<= 0`).<br>**Proses:** `Penjual\LaporanController::sheet` (`L46-L264`) membangun workbook 5 sheet (`Ringkasan`, `Top 5 Produk`, `Views Harian` + grafik batang, `Produk Diunggah`, `Ulasan`) menggunakan `XlsxWriter`.<br>**Output:** Unduhan berkas `rekap-penjual-{slug}-{Ymd}.xlsx`. | `[Implemented]` |

### 3.5 Modul Administrasi, Moderasi & Pelaporan Sekolah (`FR-ADMIN`, `FR-NOTIF`, `FR-API`)

| ID Kebutuhan | Nama Kebutuhan | Spesifikasi Teknis Input, Proses & Output | Status |
|---|---|---|---|
| **FR-ADMIN-001** *(FR-027)* | Dasbor Statistik Global Admin | **Input:** `GET /admin/dashboard`.<br>**Proses:** `Admin\DashboardController::index` (`L21-L38`) menghitung total penjual, total produk aktif, dan total produk yang memerlukan moderasi.<br>**Output:** Render view `admin.dashboard`. | `[Implemented]` |
| **FR-ADMIN-002** *(FR-028)* | Manajemen Akun & Profil Penjual (CRUD) | **Input:** `Resource /admin/penjual` (`index`, `create`, `store`, `show`, `edit`, `update`).<br>**Proses:** `Admin\PenjualController` memvalidasi `nisn` (`digits:10\|unique`), `email`, `password` (`min:8`), `nama_toko`, `nomor_whatsapp`, `lokasi_kelas`, `alamat`, dan `foto_toko` di dalam `DB::transaction()`.<br>**Output:** Data `users` dan `penjual` tersimpan secara atomik. | `[Implemented]` |
| **FR-ADMIN-003** *(FR-029)* | Toggle Status Aktif Akun Penjual | **Input:** `PATCH /admin/penjual/{penjual}/toggle-status`.<br>**Proses:** `Admin\PenjualController::toggleStatus` (`L220-L236`) membalik `users.is_active`; jika menjadi `false`, menghapus seluruh token Sanctum penjual.<br>**Output:** Status akun berubah dan produk penjual otomatis tersembunyi dari publik bila nonaktif. | `[Implemented]` |
| **FR-ADMIN-004** *(FR-030)* | Proteksi Penghapusan Akun Penjual yang Memiliki Produk | **Input:** `DELETE /admin/penjual/{penjual}` atau `DELETE /profile`.<br>**Proses:** Memeriksa `$penjual->produk()->exists()`; jika `true`, membatalkan penghapusan.<br>**Output:** Pesan error yang mengarahkan untuk menonaktifkan akun saja. | `[Implemented]` |
| **FR-ADMIN-005** *(FR-031)* | Moderasi Pengajuan Produk (*Approve / Reject Diff*) & Hapus Langsung | **Input:** `GET /admin/produk/{produk}`, `PATCH /admin/produk/{produk}/update-status`, `DELETE /admin/produk/{produk}`.<br>**Proses:** `Admin\ProdukController::updateStatus` (`L100-L222`) memproses keputusan `approve` atau `reject` atas `pending_action` (`create`, `update`, `delete`) maupun perubahan status langsung, termasuk manajemen file foto di disk `public`.<br>**Output:** Status produk diperbarui dan Admin lain diberi notifikasi. | `[Implemented]` |
| **FR-ADMIN-006** *(FR-032)* | Manajemen Kategori Produk & Proteksi FK | **Input:** `GET /admin/categories`, `POST /admin/categories`, `PUT/PATCH /admin/categories/{category}`, `DELETE /admin/categories/{category}`.<br>**Proses:** `Admin\KategoriController` memvalidasi nama unik dan ikon dari 4 konstanta `ICON_OPTIONS`, membuat slug otomatis, serta menolak penghapusan bila `$category->produk()->count() > 0` (`L77-L79`).<br>**Output:** Kategori tersimpan/terhapus dengan aman. | `[Implemented]` |
| **FR-ADMIN-007** *(FR-033)* | Manajemen & Penghapusan Komentar Ulasan | **Input:** `GET /admin/komentar` (alias `GET /admin/ulasan`) dan `DELETE /admin/komentar/{ulasan}`.<br>**Proses:** `Admin\KomentarController` menampilkan daftar ulasan dengan filter pencarian, rating, dan kategori, serta menghapus ulasan yang dipilih.<br>**Output:** Ulasan terhapus dari database. | `[Implemented]` |
| **FR-ADMIN-008** *(FR-034)* | Laporan Agregat Sekolah & Ekspor PDF (A4 Landscape) | **Input:** `GET /admin/laporan`, `GET /admin/laporan/penjual/pdf`, `GET /admin/laporan/ulasan/pdf`, `GET /admin/laporan/pdf` dengan `period` (`weekly\|monthly`).<br>**Proses:** `LaporanAdminService` menghitung metrik kinerja seluruh penjual dan distribusi ulasan; `Admin\LaporanController` mencetak PDF A4 Landscape.<br>**Output:** Tampilan web atau unduhan PDF. | `[Implemented]` |
| **FR-ADMIN-009** *(FR-035)* | Ekspor Laporan Agregat & Master Penjual Format Excel (`.xlsx`) | **Input:** `GET /admin/laporan/penjual/sheet`, `GET /admin/laporan/ulasan/sheet`, dan `GET /admin/penjual/export`.<br>**Proses:** `Admin\LaporanController` menghasilkan berkas `.xlsx` multi-sheet terformat melalui `XlsxWriter`.<br>**Output:** Unduhan berkas `.xlsx`. | `[Implemented]` |
| **FR-NOTIF-001** *(FR-036)* | Pencatatan Notifikasi Aktivitas Admin di Database | **Input:** Event tambah/edit/hapus produk atau ulasan baru.<br>**Proses:** `AdminNotificationService::notifyAdmins` mengirim `AdminActivityNotification` (`via: ['database']`) ke seluruh user ber-role `admin`.<br>**Output:** Baris baru pada tabel `notifications`. | `[Implemented]` |
| **FR-NOTIF-002** *(FR-037)* | Polling AJAX Notifikasi & Tandai Terbaca | **Input:** `GET /admin/notifikasi/poll`, `POST /admin/notifikasi/{notification}/baca`, `POST /admin/notifikasi/baca-semua`.<br>**Proses:** `Admin\NotificationController` mengembalikan JSON 8 notifikasi terbaru + `unread_count` atau menandai `read_at = now()` dengan validasi kepemilikan `notifiable_id`.<br>**Output:** JSON notifikasi atau redirect ke URL tujuan notifikasi. | `[Implemented]` |
| **FR-API-001** *(FR-038)* | Penyediaan Endpoint REST API (`/api/*`) | **Input:** Request HTTP ke 26 rute di `routes/api.php`.<br>**Proses:** Ditangani oleh 8 controller di `app/Http/Controllers/Api/` dengan format JSON standar `ApiController` (`success`, `message`, `data`/`errors`) dan autentikasi Bearer Token Sanctum.<br>**Output:** Respons JSON (`200`, `201`, `401`, `403`, `404`, `422`, `429`, atau `501` pada `/api/cart*`). | `[Implemented]` |

---

## 4. Spesifikasi Kebutuhan Non-Fungsional (NFR)

Rincian ke-12 kebutuhan non-fungsional (`NFR-001` s.d. `NFR-012`) selaras dengan yang ditetapkan pada [01-prd/PRD.md — Bagian M](../01-prd/PRD.md#m-kebutuhan-non-fungsional-non-functional-requirements), mencakup:
- **Kinerja (`NFR-001`, `NFR-009`):** Paginasi wajib di seluruh daftar katalog/tabel (`12` di publik, `10/25/50` di dasbor, `1–50` di API) dan indeks komposit pada `produk_views_logs`.
- **Keamanan (`NFR-002`, `NFR-003`, `NFR-004`, `NFR-011`):** CSRF, Blade XSS auto-escaping, Bcrypt hashing (`cost = 12`), isolasi `.env`, dan sanitasi *formula injection* pada ekspor `.xlsx`.
- **Keandalan & Portabilitas (`NFR-005`, `NFR-008`, `NFR-012`):** Transaksi atomik `DB::transaction()`, pembungkus `XlsxWriter` tanpa ketergantungan `ext-zip`, serta resolusi multi-path `public/index.php` untuk hosting cPanel/DirectAdmin.
- **Kepatuhan & Aksesibilitas (`NFR-006`, `NFR-007`, `NFR-010`):** Responsivitas `360px–1920px`, arsitektur berbasis Service, dan filter kata kasar ber-cache 24 jam.

---

## 5. Spesifikasi Antarmuka Eksternal

### 5.1 Antarmuka Pengguna (User Interfaces)
- **Layout Publik (`resources/views/layouts/public.blade.php`):** Navbar mengambang berbentuk *pill* (`.home-navbar`), banner lokasi sekolah (`SMKN 11 Bandung`), kontainer responsif (`1200px` / `1280px` di beranda), dan footer 4 kolom.
- **Layout Dasbor Admin & Penjual (`layouts/admin.blade.php`, `layouts/penjual.blade.php`):** Sidebar tetap selebar `264px` pada layar `>= 900px` dan *off-canvas drawer* pada layar `< 900px`, dilengkapi topbar dan menu unduh laporan (`<x-ui.download-menu>`).
- **Layout Autentikasi (`layouts/guest.blade.php`):** Tata letak dua kolom (*split-screen hero + form*) pada `/login` dan kartu terpusat pada `/forgot-password` serta `/reset-password`.

### 5.2 Antarmuka Perangkat Lunak & Protokol Eksternal
1. **WhatsApp Web / Mobile Deep-Link:** Menggunakan skema `https://wa.me/{phone}?text={urlencoded_message}` melalui HTTP Redirect `302` (`Public\ProdukController::trackWhatsapp`) atau payload JSON `whatsapp_url` (`Api\ProductController::trackWhatsapp`).
2. **Google OAuth 2.0 (`laravel/socialite`):** Berkomunikasi dengan endpoint OAuth Google menggunakan scope `openid`, `profile`, `email`. Pada lingkungan Windows Laragon lokal, driver mengonfigurasi bundle sertifikat `C:/laragon/etc/ssl/cacert.pem` secara otomatis jika tersedia (`GoogleAuthController.php#L147-L159`).
3. **SMTP / Mail Transport:** Digunakan oleh `ResetPasswordNotification` untuk mengirimkan email HTML (`resources/views/emails/reset-password.blade.php`) dan plain-text (`reset-password-text.blade.php`).

---

## 6. Matriks Keterlacakan Kebutuhan (Requirements Traceability Matrix / RTM)

Matriks ini menghubungkan setiap kebutuhan fungsional (`FR`) ke Use Case (`UC`), Aturan Bisnis (`BR`), Rute & Controller, serta Kasus Uji (`TC` Black-Box dan Suite Uji Otomatis PHPUnit):

| ID Kebutuhan | ID Use Case | ID Aturan Bisnis | Rute Utama (`web` / `auth` / `api`) | Controller & Method | Test Case Black-Box / Otomatis |
|---|---|---|---|---|---|
| **FR-AUTH-001** *(FR-001)* | UC-005 | BR-AUTH-02 | `GET\|POST /login`, `POST /api/login` | `Auth\AuthenticatedSessionController@store`, `Api\AuthController@login` | `TC-008`, `TC-009`, `TC-032`, `TC-033`, `GoogleLoginTest` |
| **FR-AUTH-002** *(FR-002)* | UC-006 | BR-AUTH-01 | `GET /auth/google`, `GET /auth/google/callback` | `Auth\GoogleAuthController@redirect`, `callback` | `GoogleLoginTest` (15 test) |
| **FR-AUTH-003** *(FR-003)* | UC-005, UC-014 | BR-AUTH-02 | Seluruh rute `/admin/*`, `/penjual/*`, `/api/*` (auth) | `EnsureAdmin`, `EnsurePenjual`, `EnsureUserIsActive` | `TC-009`, `TC-025`, `TC-033`, `SecurityHardeningTest` |
| **FR-AUTH-004** *(FR-004)* | UC-014 | BR-SELLER-01 | `POST /api/register` (`/register` web ditutup) | `Api\AuthController@register` | `TC-010` |
| **FR-AUTH-005** *(FR-005)* | UC-007 | BR-AUTH-03 | `GET\|POST /forgot-password`, `GET /reset-password/{token}`, `POST /reset-password` | `Auth\PasswordResetLinkController@store`, `NewPasswordController@store` | `PasswordResetTest` (15 test) |
| **FR-AUTH-006** *(FR-006)* | UC-007 | BR-AUTH-03 | `POST /reset-password` | `Auth\NewPasswordController@store` (`L63`) | `PasswordResetTest` |
| **FR-PROD-001** *(FR-007)* | UC-001 | BR-PROD-01 | `GET /` | `Public\HomeController@index` | `TC-001` |
| **FR-PROD-002** *(FR-008)* | UC-001 | BR-PROD-01 | `GET /produk`, `GET /kategori` | `routes/web.php#L14`, `Produk::scopePublicVisible` | `TC-001`, `TC-002`, `PublicCatalogRedirectTest` |
| **FR-PROD-003** *(FR-009)* | UC-001 | BR-PROD-01 | `GET /kategori`, `GET /kategori/{kategori:slug}`, `GET /api/products` | `Public\KategoriController@index`, `show`, `Api\ProductController@index` | `TC-002`, `TC-003`, `TC-034` |
| **FR-PROD-004** *(FR-010)* | UC-001 | BR-PROD-01 | `GET /kategori`, `GET /kategori/{kategori:slug}` | `Public\KategoriController@index`, `show` | `TC-003`, `TC-034` |
| **FR-PROD-005** *(FR-011)* | UC-002 | BR-PROD-01 | `GET /produk/{produk:slug}`, `GET /api/products/{product}` | `Public\ProdukController@show`, `Api\ProductController@show` | `TC-004` |
| **FR-PROD-006** *(FR-012)* | UC-002 | BR-PROD-01 | `GET /produk/{produk:slug}`, `POST /api/products/{product}/view` | `Public\ProdukController@show`, `Api\ProductController@trackView` | `TC-004`, `LaporanPdfTest` |
| **FR-WA-001** *(FR-013)* | UC-003 | BR-STORE-01 | `GET /produk/{produk:slug}/whatsapp`, `POST /api/products/{product}/whatsapp` | `Public\ProdukController@trackWhatsapp`, `Api\ProductController@trackWhatsapp` | `TC-005` |
| **FR-WA-002** *(FR-014)* | UC-003 | BR-STORE-01 | `GET /produk/{produk:slug}/whatsapp`, `POST /api/products/{product}/whatsapp` | `Public\ProdukController@trackWhatsapp`, `Api\ProductController@trackWhatsapp` | `TC-005` |
| **FR-WA-003** *(FR-015)* | UC-003 | BR-STORE-01 | `GET /produk/{produk:slug}/whatsapp`, `POST /api/products/{product}/whatsapp` | `Public\ProdukController@trackWhatsapp`, `Api\ProductController@trackWhatsapp` | `TC-005` |
| **FR-REV-001** *(FR-016)* | UC-004 | BR-REV-03 | `POST /produk/{produk:slug}/ulasan`, `POST /api/products/{product}/reviews` | `Public\UlasanController@store`, `Api\ReviewController@store` | `TC-006`, `TC-007`, `TC-035`, `SecurityHardeningTest` |
| **FR-REV-002** *(FR-017)* | UC-004 | BR-REV-01 | `POST /produk/{produk:slug}/ulasan`, `POST /api/products/{product}/reviews` | `CommentModerationService@moderate`, `Public\UlasanController@store` | `TC-006`, `TC-030`, `SecurityHardeningTest` |
| **FR-REV-003** *(FR-018)* | UC-004 | BR-REV-02 | `GET /produk/{produk:slug}`, `GET /api/products/{product}/reviews` | `Ulasan::scopePublicVisible`, `Api\ReviewController@index` | `TC-006`, `TC-035` |
| **FR-SELLER-001** *(FR-019)* | UC-008 | — | `GET /penjual/dashboard`, `GET /api/seller/dashboard` | `Penjual\DashboardController@index`, `Api\SellerController@dashboard` | `TC-014` |
| **FR-SELLER-002** *(FR-020)* | UC-009 | BR-STORE-01 | `PATCH /penjual/status-toko`, `PATCH /api/seller/store-status` | `Penjual\DashboardController@toggleStatusToko`, `Api\SellerController@toggleStoreStatus` | `TC-005`, `TC-014` |
| **FR-SELLER-003** *(FR-021)* | UC-010 | BR-PROD-02, BR-PROD-05 | `GET /penjual/produk/create`, `POST /penjual/produk` | `Penjual\ProdukController@create`, `store` | `TC-016`, `TC-017` |
| **FR-SELLER-004** *(FR-022)* | UC-011 | BR-PROD-03 | `GET /penjual/produk/{produk}/edit`, `PUT\|PATCH /penjual/produk/{produk}` | `Penjual\ProdukController@edit`, `update` | `TC-018`, `TC-019` |
| **FR-SELLER-005** *(FR-023)* | UC-012 | BR-PROD-04 | `DELETE /penjual/produk/{produk}` | `Penjual\ProdukController@destroy` | `TC-019`, `TC-020` |
| **FR-SELLER-006** *(FR-024)* | UC-010 | — | `GET /penjual/produk` | `Penjual\ProdukController@index` | `TC-015`, `AdminAndPenjualFilteringAndPaginationTest` |
| **FR-SELLER-007** *(FR-025)* | UC-013 | — | `GET /penjual/laporan`, `GET /penjual/laporan/pdf` | `Penjual\LaporanController@index`, `pdf` | `TC-021`, `LaporanPdfTest`, `LaporanRekapitulasiUnifiedExportTest` |
| **FR-SELLER-008** *(FR-026)* | UC-013 | BR-REP-01 | `GET /penjual/laporan/sheet` | `Penjual\LaporanController@sheet` | `LaporanPdfTest`, `LaporanRekapitulasiUnifiedExportTest`, `XlsxWriterTest` |
| **FR-ADMIN-001** *(FR-027)* | UC-014 | — | `GET /admin/dashboard` | `Admin\DashboardController@index` | `TC-022`, `AdminRekapPenjualTest` |
| **FR-ADMIN-002** *(FR-028)* | UC-014 | BR-SELLER-01 | `Resource /admin/penjual` | `Admin\PenjualController@index`, `create`, `store`, `show`, `edit`, `update` | `TC-023`, `TC-024`, `AdminAndPenjualFilteringAndPaginationTest` |
| **FR-ADMIN-003** *(FR-029)* | UC-015 | BR-AUTH-02 | `PATCH /admin/penjual/{penjual}/toggle-status` | `Admin\PenjualController@toggleStatus` | `TC-025`, `SecurityHardeningTest` |
| **FR-ADMIN-004** *(FR-030)* | UC-015 | BR-SELLER-02 | `DELETE /admin/penjual/{penjual}`, `DELETE /profile` | `Admin\PenjualController@destroy`, `ProfileController@destroy` | `TC-013` |
| **FR-ADMIN-005** *(FR-031)* | UC-016 | BR-PROD-02..05 | `GET /admin/produk`, `GET /admin/produk/{produk}`, `PATCH /admin/produk/{produk}/update-status`, `DELETE /admin/produk/{produk}` | `Admin\ProdukController@index`, `show`, `updateStatus`, `destroy` | `TC-028`, `AdminAndPenjualFilteringAndPaginationTest` |
| **FR-ADMIN-006** *(FR-032)* | UC-017 | BR-KAT-01 | `Resource /admin/categories` | `Admin\KategoriController@index`, `store`, `update`, `destroy` | `TC-026`, `TC-027`, `AdminAndPenjualFilteringAndPaginationTest` |
| **FR-ADMIN-007** *(FR-033)* | UC-018 | BR-REV-02 | `GET /admin/komentar`, `DELETE /admin/komentar/{ulasan}` | `Admin\KomentarController@index`, `destroy` | `TC-029`, `TC-038`, `AdminAndPenjualFilteringAndPaginationTest` |
| **FR-ADMIN-008** *(FR-034)* | UC-019 | — | `GET /admin/laporan`, `GET /admin/laporan/penjual/pdf`, `GET /admin/laporan/ulasan/pdf`, `GET /admin/laporan/pdf` | `Admin\LaporanController@index`, `penjualPdf`, `ulasanPdf`, `pdf` | `LaporanPdfTest`, `AdminRekapPenjualTest`, `LaporanRekapitulasiUnifiedExportTest` |
| **FR-ADMIN-009** *(FR-035)* | UC-019 | BR-REP-01 | `GET /admin/laporan/penjual/sheet`, `GET /admin/laporan/ulasan/sheet`, `GET /admin/penjual/export` | `Admin\LaporanController@penjualSheet`, `ulasanSheet`, `penjualExport` | `LaporanPdfTest`, `SellerExportAuthorizationTest`, `LaporanRekapitulasiUnifiedExportTest` |
| **FR-NOTIF-001** *(FR-036)* | UC-020 | — | Internal event trigger | `AdminNotificationService@notifyAdmins`, `AdminActivityNotification` | `TC-031` |
| **FR-NOTIF-002** *(FR-037)* | UC-020 | — | `GET /admin/notifikasi/poll`, `POST /admin/notifikasi/{notification}/baca`, `POST /admin/notifikasi/baca-semua`, `GET /api/notifications*` | `Admin\NotificationController@poll`, `markRead`, `markAllRead`, `Api\NotificationController` | `TC-031`, `TC-037` |
| **FR-API-001** *(FR-038)* | UC-001..005, UC-008..009 | BR-PROD-01, BR-REV-01 | `routes/api.php` (26 rute) | `App\Http\Controllers\Api\*` | `TC-032` s.d. `TC-037`, `SecurityHardeningTest` |

---

## 7. Rujukan Kode

- [`routes/web.php`](../../routes/web.php) (`L1-L107`)
- [`routes/auth.php`](../../routes/auth.php) (`L1-L65`)
- [`routes/api.php`](../../routes/api.php) (`L1-L76`)
- [`bootstrap/app.php`](../../bootstrap/app.php) (`L1-L57`)
- [`app/Http/Requests/Auth/LoginRequest.php`](../../app/Http/Requests/Auth/LoginRequest.php) (`L1-L99`)
- [`app/Http/Middleware/EnsureAdmin.php`](../../app/Http/Middleware/EnsureAdmin.php), [`EnsurePenjual.php`](../../app/Http/Middleware/EnsurePenjual.php), [`EnsureUserIsActive.php`](../../app/Http/Middleware/EnsureUserIsActive.php)
- [`app/Services/CommentModerationService.php`](../../app/Services/CommentModerationService.php), [`AdminNotificationService.php`](../../app/Services/AdminNotificationService.php), [`LaporanAdminService.php`](../../app/Services/LaporanAdminService.php), [`LaporanPenjualService.php`](../../app/Services/LaporanPenjualService.php)
- [`app/Support/XlsxWriter.php`](../../app/Support/XlsxWriter.php), [`app/Exports/Concerns/StylesSheet.php`](../../app/Exports/Concerns/StylesSheet.php)
