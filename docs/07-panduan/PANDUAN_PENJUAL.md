# Panduan Pengguna Penjual (Siswa) — SiswaMart

| Informasi Dokumen | Detail |
| :--- | :--- |
| **Judul** | Panduan Penggunaan Panel Penjual (Siswa Wirausaha) |
| **Versi** | `1.1.0` |
| **Tanggal** | 27 Oktober 2026 |
| **Status** | Final (Source-Code Backed) |
| **Penulis** | Tim Dokumentasi Teknis & Rekayasa Perangkat Lunak |
| **Sumber Data** | `routes/web.php#L72-L82`, `app/Http/Controllers/Penjual/`, `resources/views/penjual/` |

---

## Daftar Isi

1. [Login, Lupa Kata Sandi, dan Keamanan Akun Penjual](#1-login-lupa-kata-sandi-dan-keamanan-akun-penjual)
2. [Memahami Dasbor Penjual](#2-memahami-dasbor-penjual)
3. [Mengelola Profil Toko dan Sakelar Buka/Tutup Toko](#3-mengelola-profil-toko-dan-sakelar-bukatutup-toko)
4. [Mengelola Produk: Tambah, Edit, dan Hapus](#4-mengelola-produk-tambah-edit-dan-hapus)
5. [Tabel Arti Status Produk, Status Moderasi (`pending_action`), dan Status Stok](#5-tabel-arti-status-produk-status-moderasi-pending_action-dan-status-stok)
6. [Mengunduh Laporan Toko (PDF & Excel `.xlsx`)](#6-mengunduh-laporan-toko-pdf--excel-xlsx)
7. [Rujukan Kode](#7-rujukan-kode)

---

## 1. Login, Lupa Kata Sandi, dan Keamanan Akun Penjual

Akun penjual SiswaMart didaftarkan secara resmi oleh Admin Sekolah menggunakan **NISN (10 digit)** dan email Anda (`ADR-004`).

### 1.1 Cara Masuk (Login)
1. Buka halaman `/login`.
2. Anda dapat masuk menggunakan salah satu dari dua metode:
   - **Email / Username & Kata Sandi:** Masukkan alamat email atau `username` Anda beserta kata sandi, lalu klik **Masuk**.
   - **Masuk dengan Google (`GET /auth/google`):** Klik tombol **Masuk dengan Google** jika email akun Google Anda sama dengan email yang didaftarkan oleh Admin Sekolah.
3. Setelah berhasil masuk, Anda akan diarahkan ke **Dasbor Penjual** (`/penjual/dashboard`).

### 1.2 Mengganti Kata Sandi Awal & Lupa Kata Sandi
- Apabila akun Anda baru dibuat oleh Admin, segera perbarui kata sandi melalui menu **Profil Toko** (`/penjual/profil`) atau **Profil Akun** (`/profile`).
- Jika lupa kata sandi, klik **Lupa Kata Sandi?** (`/forgot-password`), masukkan email terdaftar, dan ikuti tautan pengaturan ulang yang dikirim ke kotak masuk email Anda.

---

## 2. Memahami Dasbor Penjual

Halaman **Dasbor Penjual** (`GET /penjual/dashboard` — `Penjual\DashboardController@index`) menyajikan metrik performa toko Anda secara langsung:
- **Total Produk & Rincian Status:** Jumlah produk Anda yang berstatus *Aktif*, *Menunggu Moderasi (Pending)*, dan *Ditolak*.
- **Total Dilihat (`Views`):** Berapa kali halaman detail produk Anda dikunjungi pembeli.
- **Total Klik WhatsApp:** Berapa kali pembeli menekan tombol pemesanan WhatsApp pada produk Anda.
- **Rata-rata Rating Toko:** Nilai rata-rata bintang dari seluruh ulasan pembeli pada produk Anda.

---

## 3. Mengelola Profil Toko dan Sakelar Buka/Tutup Toko

Buka menu **Profil Toko** (`GET /penjual/profil` — `Penjual\ProfilController@edit`) untuk memperbarui identitas toko:

1. **Data Toko yang Dapat Diubah:**
   - **Nama Toko (`nama_toko`)** dan **Deskripsi Toko (`deskripsi_toko`)**.
   - **Nomor WhatsApp (`nomor_whatsapp`):** Pastikan nomor aktif (contoh: `081234567890`). Sistem otomatis mengubah awalan `08` menjadi `628` saat pembeli mengklik tombol pesan.
   - **Lokasi Kelas (`lokasi_kelas`):** Contoh `XII RPL 1` untuk memudahkan COD di sekolah.
   - **Foto Profil Toko (`foto_toko`):** Format `JPG`/`PNG`/`WEBP`, ukuran maksimal **2 MB**.
2. **Sakelar Cepat Buka / Tutup Toko (`PATCH /penjual/profil/status-toko`):**
   - Gunakan tombol **Buka Toko** / **Tutup Toko** di Dasbor atau halaman Profil Toko.
   - Saat diatur ke **`tutup`**, produk Anda tetap tampil di katalog dengan lencana **"Toko Tutup"** dan pesan WhatsApp dari pembeli akan otomatis disesuaikan menjadi pesan konfirmasi ketersediaan.

---

## 4. Mengelola Produk: Tambah, Edit, dan Hapus

Buka menu **Produk Saya** (`GET /penjual/produk` — `Penjual\ProdukController@index`).

### 4.1 Menambah Produk Baru (`POST /penjual/produk`)
1. Klik tombol **Tambah Produk** (`/penjual/produk/create`).
2. Lengkapi formulir:
   - **Nama Produk** (maks. 255 karakter) & **Kategori**.
   - **Harga (Rp)** (angka $\ge 0$) & **Status Stok** (`Stok Tersedia`, `Pre-Order (PO)`, atau `Stok Habis`).
   - **Label Promosi** *(opsional, maks. 50 karakter, misal: "Diskon Jumat" / "Best Seller")*.
   - **Deskripsi Produk** & **Foto Utama Produk** (wajib saat tambah baru, format `jpeg,png,jpg,webp`, maks. **2 MB**).
3. Klik **Simpan**. Produk akan berstatus **`Pending` (`pending_action = 'create'`)** dan menunggu persetujuan Admin sebelum tampil di halaman publik.

### 4.2 Mengedit Produk (`PUT /penjual/produk/{produk}`)
Perilaku sistem saat Anda mengedit produk bergantung pada status produk saat itu (`ADR-002`):
- **Jika produk sedang `Aktif`:**
  - Produk versi lama Anda **tetap tayang di etalase publik** sehingga Anda tidak kehilangan pembeli.
  - Perubahan yang Anda simpan (termasuk foto baru) disimpan di antrean **`pending_changes`** dengan tanda **`Menunggu Persetujuan Edit` (`pending_action = 'update'`)**.
  - Begitu Admin menyetujui, versi baru langsung menggantikan versi lama. Jika Admin menolak, versi lama tetap tayang dan alasan penolakan ditampilkan kepada Anda.
- **Jika produk sedang `Pending` atau `Ditolak`:**
  - Perubahan langsung memperbarui data produk dan mengajukannya kembali ke Admin (`status = 'pending'`, `pending_action = 'create'`).

### 4.3 Mengajukan Hapus Produk (`DELETE /penjual/produk/{produk}`)
1. Klik tombol **Hapus** pada produk yang ingin dihapus.
2. Permintaan hapus akan dikirim ke Admin (**`Menunggu Persetujuan Hapus` / `pending_action = 'delete'`**).
3. *Catatan:* Anda tidak dapat mengajukan hapus apabila produk tersebut sedang memiliki ajuan edit/buat yang belum selesai diperiksa Admin (`Penjual\ProdukController@destroy#L198-L203`).

---

## 5. Tabel Arti Status Produk, Status Moderasi (`pending_action`), dan Status Stok

### 5.1 Status Publikasi Produk (`produk.status`) & `pending_action`

| Status Utama (`status`) | Nilai `pending_action` | Tampil di Publik? | Arti bagi Penjual |
| :--- | :---: | :---: | :--- |
| **`pending`** | `'create'` | Tidak | Produk baru (atau hasil perbaikan produk ditolak) sedang menunggu pemeriksaan Admin. |
| **`aktif`** | `null` | **Ya** | Produk telah disetujui Admin dan tampil normal di etalase publik. |
| **`aktif`** | `'update'` | **Ya** *(versi lama)* | Produk aktif sedang mengajukan revisi data/foto. Versi lama tetap tampil di publik sampai Admin menyetujui revisi. |
| **`aktif`** | `'delete'` | **Ya** | Penjual telah mengajukan penghapusan produk dan sedang menunggu konfirmasi hapus dari Admin. |
| **`ditolak`** | `null` | Tidak | Pengajuan produk baru ditolak oleh Admin. Baca kotak **`alasan_ditolak`**, lalu klik **Edit** untuk memperbaiki dan mengajukan ulang. |

### 5.2 Status Ketersediaan Stok (`produk.stok`)

| Nilai Sistem | Label di Layar | Kapan Digunakan? |
| :---: | :--- | :--- |
| `tersedia` | **Stok Tersedia** | Barang sudah siap dan bisa langsung diserahkan kepada pembeli. |
| `po` | **Pre-Order (PO)** | Barang dibuat sesuai pesanan atau baru tersedia pada hari/jam tertentu. |
| `habis` | **Stok Habis** | Barang sedang kosong; tombol WhatsApp akan dikunci agar pembeli tidak memesan barang yang habis. |

---

## 6. Mengunduh Laporan Toko (PDF & Excel `.xlsx`)

1. Buka menu **Laporan** (`GET /penjual/laporan` — `Penjual\LaporanController@index`).
2. Gunakan filter **Status Produk**, **Kategori**, **Status Stok**, atau **Kata Kunci** bila diperlukan.
3. Klik tombol menu **Download Laporan** (`<x-ui.download-menu>`):
   - **Download PDF (`GET /penjual/laporan/export`):** Menghasilkan dokumen PDF ukuran A4 Landscape siap cetak untuk lampiran laporan kewirausahaan/PKK.
   - **Download Excel (`GET /penjual/laporan/export-excel`):** Menghasilkan berkas `.xlsx` yang terdiri dari 2 lembar kerja (*Sheet 1: Ringkasan Toko* dan *Sheet 2: Daftar Produk*).

---

## 7. Rujukan Kode

- `routes/web.php#L72-L82`
- `app/Http/Controllers/Penjual/DashboardController.php#L13-L61`
- `app/Http/Controllers/Penjual/ProdukController.php#L18-L246`
- `app/Http/Controllers/Penjual/ProfilController.php#L18-L106`
- `app/Http/Controllers/Penjual/LaporanController.php#L14-L53`
- `app/Services/LaporanPenjualService.php#L14-L162`
- `app/Exports/LaporanPenjualExport.php#L15-L163`
