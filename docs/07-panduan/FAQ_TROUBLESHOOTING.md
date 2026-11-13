# FAQ & Troubleshooting (Penyelesaian Masalah Umum) — SiswaMart

| Informasi Dokumen | Detail |
| :--- | :--- |
| **Judul** | FAQ dan Panduan Troubleshooting Masalah Umum SiswaMart |
| **Versi** | `1.1.0` |
| **Tanggal** | 27 Oktober 2026 |
| **Status** | Final (Source-Code Backed) |
| **Penulis** | Tim Dokumentasi Teknis & Rekayasa Perangkat Lunak |
| **Sumber Data** | `config/filesystems.php`, `config/services.php`, `app/Http/Controllers/Auth/GoogleAuthController.php`, `app/Http/Controllers/Penjual/ProdukController.php`, `public/deploy-helper.php` |

---

## Daftar Isi

1. [Masalah Gambar Produk / Foto Toko Tidak Muncul (404 Not Found)](#1-masalah-gambar-produk--foto-toko-tidak-muncul-404-not-found)
2. [Masalah Login Google OAuth (`redirect_uri_mismatch`, SSL `cacert.pem`, atau Akun Belum Terdaftar)](#2-masalah-login-google-oauth-redirect_uri_mismatch-ssl-cacertpem-atau-akun-belum-terdaftar)
3. [Masalah Perubahan Edit Produk Penjual Belum Tampil di Halaman Publik](#3-masalah-perubahan-edit-produk-penjual-belum-tampil-di-halaman-publik)
4. [Masalah Gagal Menghapus Penjual atau Kategori di Panel Admin](#4-masalah-gagal-menghapus-penjual-atau-kategori-di-panel-admin)
5. [Masalah Error Kolom Database Tidak Ditemukan (`Unknown column 'username' / 'pending_action'`)](#5-masalah-error-kolom-database-tidak-ditemukan-unknown-column-username--pending_action)
6. [Masalah Pengiriman Ulasan Ditolak atau Terkena Batas Percobaan (HTTP 429)](#6-masalah-pengiriman-ulasan-ditolak-atau-terkena-batas-percobaan-http-429)
7. [Masalah Koneksi Database Ditolak (`SQLSTATE[HY000] [2002]`)](#7-masalah-koneksi-database-ditolak-sqlstatehy000-2002)
8. [Rujukan Kode](#8-rujukan-kode)

---

## 1. Masalah Gambar Produk / Foto Toko Tidak Muncul (404 Not Found)

### Gejala
Produk berhasil ditambahkan dan tampil di katalog, tetapi gambar produk pecah (menampilkan placeholder atau error `404` pada URL `/storage/produk/...`).

### Penyebab
Berkas yang diunggah disimpan di `storage/app/public/produk/`, sedangkan web server melayani berkas dari `public/storage/`. Tautan simbolik (*symbolic link*) antara kedua direktori tersebut belum dibuat atau rusak setelah memindahkan folder proyek.

### Solusi
1. Pada lingkungan lokal (Laragon / Terminal):
   ```bash
   php artisan storage:link
   ```
2. Jika symlink lama mengarah ke path komputer lain, hapus folder/link `public/storage` terlebih dahulu lalu jalankan kembali `php artisan storage:link`.
3. Pastikan variabel `APP_URL` di `.env` sesuai dengan domain atau URL lokal yang sedang diakses (`config/filesystems.php#L39-L45`).

---

## 2. Masalah Login Google OAuth (`redirect_uri_mismatch`, SSL `cacert.pem`, atau Akun Belum Terdaftar)

### Kasus 2A: Pesan *"Akun dengan email ini belum terdaftar"*
- **Penyebab:** Sesuai aturan keamanan `BR-AUTH-02` (`GoogleAuthController.php#L75-L79`), login Google tidak membuat akun baru secara otomatis.
- **Solusi:** Pastikan Admin Sekolah telah mendaftarkan akun penjual dengan alamat email yang sama persis dengan alamat email Google yang Anda pilih.

### Kasus 2B: Pesan *"Login Google gagal. Silakan coba lagi atau masuk dengan email dan password"* di Laragon Windows
- **Penyebab:** cURL PHP di Windows gagal memverifikasi sertifikat SSL saat menghubungi server `https://oauth2.googleapis.com/token` karena berkas `cacert.pem` belum dikonfigurasi.
- **Solusi:**
  1. Periksa lokasi `cacert.pem` di Laragon (misal: `C:\laragon\etc\ssl\cacert.pem`).
  2. Isi variabel `GOOGLE_HTTP_VERIFY` di berkas `.env` dengan path lengkap ke berkas `cacert.pem` tersebut (`config/services.php#L42`).
  3. Jalankan `php artisan config:clear`.

### Kasus 2C: Error `Error 400: redirect_uri_mismatch` dari Google
- **Penyebab:** Nilai `GOOGLE_REDIRECT_URI` di `.env` tidak sama persis dengan *Authorized redirect URIs* di Google Cloud Console.
- **Solusi:** Pastikan URI callback di Google Cloud Console dan `.env` bernilai identik, misalnya `http://localhost/auth/google/callback` atau `https://domain-sekolah.sch.id/auth/google/callback` (`config/services.php#L39`).

---

## 3. Masalah Perubahan Edit Produk Penjual Belum Tampil di Halaman Publik

### Gejala
Penjual sudah mengedit harga, nama, atau foto produk yang berstatus **Aktif** dan klik Simpan, tetapi saat dilihat di halaman publik data yang tampil masih versi lama.

### Penyebab
Ini adalah perilaku desain yang disengaja (**`ADR-002` / `BR-PROD-03`**). Saat produk yang sudah `aktif` diedit, perubahan disimpan ke dalam kolom `pending_changes` dengan status **`pending_action = 'update'`** agar produk lama tetap tayang selama menunggu pemeriksaan Admin.

### Solusi
1. Buka panel Admin $\rightarrow$ **Moderasi Produk** (`/admin/produk`).
2. Pilih produk yang memiliki label **Menunggu Persetujuan Edit (`update`)**.
3. Klik **Setujui (`Approve`)**. Data baru di `pending_changes` akan langsung diterapkan ke halaman publik.

---

## 4. Masalah Gagal Menghapus Penjual atau Kategori di Panel Admin

### Kasus 4A: Tidak Bisa Menghapus Penjual
- **Pesan Sistem:** *"Penjual tidak dapat dihapus karena masih memiliki X produk terdaftar. Nonaktifkan akun penjual jika ingin menyembunyikan tokonya."*
- **Penyebab & Solusi (`BR-SELLER-01`):** Untuk melindungi riwayat produk dan ulasan, penjual yang sudah memiliki produk tidak boleh dihapus. Gunakan tombol **Nonaktifkan (`toggle`)** pada baris penjual tersebut untuk menyembunyikan seluruh produknya dari publik.

### Kasus 4B: Tidak Bisa Menghapus Kategori
- **Pesan Sistem:** *"Kategori tidak dapat dihapus karena masih memiliki produk."*
- **Penyebab & Solusi (`BR-KAT-01`):** Masih ada produk yang memakai kategori tersebut. Ubah kategori pada produk-produk terkait terlebih dahulu sebelum menghapus kategori.

---

## 5. Masalah Error Kolom Database Tidak Ditemukan (`Unknown column 'username' / 'pending_action'`)

### Gejala
Setelah menarik pembaruan kode terbaru (*git pull*), muncul pesan error SQL seperti `Unknown column 'username' in 'field list'`, `Unknown column 'pending_action'`, atau `Unknown column 'google_id'`.

### Penyebab
Lima berkas migrasi penyelarasan skema (`2026_10_15_000001` s.d. `2026_10_15_000005` — Batch 9) belum dijalankan pada database di komputer/server tersebut.

### Solusi
Jalankan perintah migrasi dan bersihkan cache:
```bash
php artisan migrate
php artisan optimize:clear
```

---

## 6. Masalah Pengiriman Ulasan Ditolak atau Terkena Batas Percobaan (HTTP 429)

### Kasus 6A: Pesan *"Ulasan/Nama mengandung kata yang tidak pantas"*
- **Penyebab:** Teks ulasan atau nama mengandung kata yang masuk dalam daftar terlarang (`badwords-list.json` atau tabel `bad_words`).
- **Solusi untuk Admin (Jika Terjadi False Positive):** Jika kata yang terdeteksi sebenarnya adalah kata sopan/istilah produk yang wajar, Admin dapat menambahkan frasa tersebut ke tabel `whitelists` lalu membersihkan cache kamus moderasi (`Cache::forget('comment_moderation.whitelist')` atau `php artisan cache:clear`).

### Kasus 6B: Error `429 Too Many Requests`
- **Penyebab:** Rute pengiriman ulasan dibatasi maksimal **3 kali per menit** di Web (`throttle:3,1`) dan klik WhatsApp dibatasi **30 kali per menit** (`throttle:30,1`).
- **Solusi:** Tunggu 60 detik sebelum mencoba mengirim ulang.

---

## 7. Masalah Koneksi Database Ditolak (`SQLSTATE[HY000] [2002]`)

### Gejala
Muncul error `SQLSTATE[HY000] [2002] No connection could be made` atau `forbidden by access permissions`.

### Penyebab & Solusi
1. Pastikan layanan **MySQL** di Laragon sudah berstatus **Running** pada port `3306`.
2. Periksa variabel `DB_HOST=127.0.0.1`, `DB_PORT=3306`, `DB_DATABASE=siswamartid`, dan `DB_USERNAME` pada berkas `.env`.
3. Jika konfigurasi `.env` pernah di-cache, jalankan `php artisan config:clear`.

---

## 8. Rujukan Kode

- `config/filesystems.php#L32-L60`
- `config/services.php#L35-L44`
- `app/Http/Controllers/Auth/GoogleAuthController.php#L23-L133`
- `app/Http/Controllers/Penjual/ProdukController.php#L140-L219`
- `app/Http/Controllers/Admin/PenjualController.php#L202-L215`
- `app/Http/Controllers/Admin/KategoriController.php#L66-L78`
- `app/Services/CommentModerationService.php#L18-L361`
