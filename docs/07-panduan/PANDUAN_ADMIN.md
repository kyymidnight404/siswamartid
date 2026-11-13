# Panduan Pengguna Admin Sekolah — SiswaMart

| Informasi Dokumen | Detail |
| :--- | :--- |
| **Judul** | Panduan Operasional Panel Admin Sekolah SiswaMart |
| **Versi** | `1.1.0` |
| **Tanggal** | 27 Oktober 2026 |
| **Status** | Final (Source-Code Backed) |
| **Penulis** | Tim Dokumentasi Teknis & Rekayasa Perangkat Lunak |
| **Sumber Data** | `routes/web.php#L31-L69`, `app/Http/Controllers/Admin/`, `resources/views/admin/` |

---

## Daftar Isi

1. [Peran dan Tanggung Jawab Admin Sekolah](#1-peran-dan-tanggung-jawab-admin-sekolah)
2. [Memantau Dasbor Analitik Admin](#2-memantau-dasbor-analitik-admin)
3. [Mengelola Akun Penjual Siswa (Tambah, Edit, Nonaktifkan, Hapus)](#3-mengelola-akun-penjual-siswa-tambah-edit-nonaktifkan-hapus)
4. [Moderasi Produk (`Create`, `Update` Diff, dan `Delete`)](#4-moderasi-produk-create-update-diff-dan-delete)
5. [Mengelola Master Kategori Produk](#5-mengelola-master-kategori-produk)
6. [Memoderasi Komentar & Ulasan Pengunjung](#6-memoderasi-komentar--ulasan-pengunjung)
7. [Mengelola Lonceng Notifikasi Sistem](#7-mengelola-lonceng-notifikasi-sistem)
8. [Mengunduh Laporan Analitik Global (PDF & Excel `.xlsx`)](#8-mengunduh-laporan-analitik-global-pdf--excel-xlsx)
9. [Rujukan Kode](#9-rujukan-kode)

---

## 1. Peran dan Tanggung Jawab Admin Sekolah

Admin Sekolah memegang akses penuh ke panel `/admin/*` (dilindungi middleware `EnsureAdmin`) dengan wewenang utama:
1. Mendaftarkan dan memverifikasi akun penjual siswa menggunakan NISN 10 digit.
2. Menjaga kualitas dan kepatutan katalog melalui moderasi produk (`Approve` / `Reject`).
3. Mengelola master kategori dan mengawasi ulasan publik.
4. Mengekspor laporan analitik berkala dalam format PDF dan Excel (`.xlsx`).

---

## 2. Memantau Dasbor Analitik Admin

Buka `GET /admin/dashboard` (`Admin\DashboardController@index`) untuk memantau:
- **Kartu Ringkasan Utama:** Total Penjual, Total Produk (`Aktif`, `Pending`, `Ditolak`), Total Tayangan (`Views`), dan Total Klik WhatsApp.
- **Daftar Antrean Moderasi Terbaru:** Pintasan langsung ke produk yang baru diajukan penjual.
- **Produk Terpopuler & Aktivitas Penjual:** Pemantauan toko yang aktif maupun yang lama tidak aktif (`last_activity_at`).

---

## 3. Mengelola Akun Penjual Siswa (Tambah, Edit, Nonaktifkan, Hapus)

Buka menu **Kelola Penjual** (`GET /admin/penjual` — `Admin\PenjualController`).

### 3.1 Mendaftarkan Penjual Baru (`POST /admin/penjual`)
1. Klik **Tambah Penjual** (`/admin/penjual/create`).
2. Isi data wajib berikut (`Admin\PenjualController@store#L90-L103`):
   - **Nama Lengkap Siswa (`name`)**
   - **NISN (`nisn`):** Wajib tepat **10 digit angka unik** (`0012345678`).
   - **Email (`email`)** dan **Password Awal (`password`)** (minimal 8 karakter).
   - **Nama Toko (`nama_toko`)**, **Nomor WhatsApp (`nomor_whatsapp`)**, **Lokasi Kelas (`lokasi_kelas`)**, dan **Alamat (`alamat`)**.
3. Klik **Simpan**. Sistem akan otomatis membuatkan `username` unik dan mengaktifkan toko penjual.

### 3.2 Menonaktifkan vs Menghapus Penjual
- **Nonaktifkan / Aktifkan Akun (`PATCH /admin/penjual/{penjual}/toggle`):**
  - Gunakan tombol sakelar status untuk menonaktifkan sementara penjual yang lulus atau melanggar aturan.
  - Begitu dinonaktifkan (`is_active = false`), sesi penjual langsung diputus dan seluruh produknya otomatis disembunyikan dari publik (`BR-PROD-01`, `BR-AUTH-01`).
- **Hapus Akun Penjual (`DELETE /admin/penjual/{penjual}`):**
  - Penghapusan permanen **hanya diizinkan jika penjual belum memiliki produk sama sekali** (`produk_count == 0`, `BR-SELLER-01`). Jika penjual sudah memiliki produk, sistem akan menolak penghapusan demi menjaga integritas data historis.

---

## 4. Moderasi Produk (`Create`, `Update` Diff, dan `Delete`)

Buka menu **Moderasi Produk** (`GET /admin/produk` — `Admin\ProdukController@index`).

### 4.1 Memeriksa Detail Ajuan (`GET /admin/produk/{produk}`)
Pada halaman detail moderasi (`resources/views/admin/produk/show.blade.php`), perhatikan jenis ajuan (`pending_action`):
1. **`create` (Produk Baru):** Tinjau foto, nama, deskripsi, kategori, dan kewajaran harga.
2. **`update` (Perubahan Produk Aktif):** Sistem menampilkan **Tabel Perbandingan Perubahan (*Diff*)** yang menyandingkan **Nilai Saat Ini (Tayang di Publik)** vs **Nilai Baru yang Diajukan Penjual** (termasuk pratinjau foto lama vs foto baru).
3. **`delete` (Permintaan Hapus Produk):** Penjual meminta agar produk dihapus dari sistem.

### 4.2 Menyetujui (`Approve`) atau Menolak (`Reject`) (`PATCH /admin/produk/{produk}/status`)
- **Klik Setujui (`status = 'aktif'`):**
  - Untuk `create`: Produk langsung tayang di publik.
  - Untuk `update`: Data di `pending_changes` disalin ke kolom utama produk dan foto lama digantikan foto baru.
  - Untuk `delete`: Produk beserta file fotonya dihapus permanen dari sistem.
- **Klik Tolak (`status = 'ditolak'`):**
  - Anda **wajib mengisi kolom `alasan_ditolak`** (maksimal 500 karakter) agar siswa mengetahui bagian mana yang harus diperbaiki.
  - Jika yang ditolak adalah ajuan `update` atau `delete` pada produk yang sebelumnya sudah `aktif`, produk versi lama tetap tayang di publik dan hanya ajuan perubahannya yang dibatalkan (`BR-PROD-05`).

---

## 5. Mengelola Master Kategori Produk

Buka menu **Kategori** (`GET /admin/kategori` — `Admin\KategoriController`):
- **Tambah / Edit Kategori:** Masukkan `nama` kategori (slug dibuat otomatis) dan `icon` opsional.
- **Hapus Kategori (`DELETE /admin/kategori/{kategori}`):** Kategori **tidak dapat dihapus** apabila masih ada produk yang terhubung ke kategori tersebut (`BR-KAT-01`). Pindahkan atau hapus produk terkait terlebih dahulu.

---

## 6. Memoderasi Komentar & Ulasan Pengunjung

Buka menu **Moderasi Komentar** (`GET /admin/komentar` — `Admin\KomentarController`):
1. Meskipun ulasan tamu telah disaring otomatis oleh `CommentModerationService` saat dikirim, Admin dapat meninjau seluruh ulasan berdasarkan kata kunci, rating (1–5), atau nama produk.
2. Klik **Detail** (`GET /admin/komentar/{ulasan}`) untuk melihat isi lengkap komentar dan metadata moderasinya.
3. Klik **Hapus** (`DELETE /admin/komentar/{ulasan}`) jika ditemukan ulasan spam atau tidak relevan.
4. Untuk pembersihan massal melalui terminal server, Admin teknis juga dapat menjalankan perintah `php artisan comments:audit-clean` (`AuditCleanCommentsCommand`).

---

## 7. Mengelola Lonceng Notifikasi Sistem

Ikon **Lonceng Notifikasi** di bilah atas panel Admin (`Admin\NotificationController`) menampilkan pemberitahuan real-time setiap kali:
- Penjual mengajukan produk baru (`create`), perubahan produk (`update`), atau penghapusan produk (`delete`).
- Pengunjung mengirimkan ulasan baru pada produk.

Aksi yang tersedia:
- Klik item notifikasi (`GET /admin/notifikasi/{id}/baca`) untuk menandai telah dibaca sekaligus langsung menuju halaman produk/ulasan terkait.
- Klik **Tandai Semua Dibaca** (`PATCH /admin/notifikasi/baca-semua`) atau buka halaman penuh `/admin/notifikasi` untuk menghapus riwayat notifikasi (`DELETE /admin/notifikasi/hapus-semua`).

---

## 8. Mengunduh Laporan Analitik Global (PDF & Excel `.xlsx`)

1. Buka menu **Laporan Analitik** (`GET /admin/laporan` — `Admin\LaporanController@index`).
2. Pilih filter laporan sesuai kebutuhan:
   - **Periode Waktu (`period`):** Harian (`daily`), Mingguan (`weekly`), Bulanan (`monthly`), Tahunan (`yearly`), atau Rentang Tanggal Kustom (`custom`: `start_date` s.d. `end_date`).
   - **Filter Tambahan:** Toko Penjual (`penjual_id`), Kategori (`kategori_id`), Status Produk (`status`), dan Kata Kunci (`search`).
3. Klik tombol **Download Laporan**:
   - **Download PDF (`GET /admin/laporan/export`):** Menghasilkan dokumen PDF resmi A4 Landscape.
   - **Download Excel (`GET /admin/laporan/export-excel`):** Menghasilkan buku kerja `.xlsx` berisi **3 Lembar Kerja (Sheets)**:
     1. *Ringkasan & Statistik* (KPI global & distribusi kategori)
     2. *Performa Penjual* (peringkat toko, jumlah produk, tayangan, klik WA, rating)
     3. *Daftar Produk* (rincian lengkap per item produk)

---

## 9. Rujukan Kode

- `routes/web.php#L31-L69`
- `app/Http/Controllers/Admin/DashboardController.php#L14-L98`
- `app/Http/Controllers/Admin/PenjualController.php#L18-L246`
- `app/Http/Controllers/Admin/ProdukController.php#L15-L163`
- `app/Http/Controllers/Admin/KategoriController.php#L14-L79`
- `app/Http/Controllers/Admin/KomentarController.php#L14-L66`
- `app/Http/Controllers/Admin/NotificationController.php#L13-L108`
- `app/Http/Controllers/Admin/LaporanController.php#L14-L53`
- `app/Services/LaporanAdminService.php#L15-L296`
- `app/Exports/LaporanAdminExport.php#L15-L261`
