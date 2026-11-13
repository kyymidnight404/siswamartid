# Panduan Instalasi Lokal (Laragon) — SiswaMart

| Informasi Dokumen | Detail |
| :--- | :--- |
| **Judul** | Panduan Instalasi dan Konfigurasi Lingkungan Lokal SiswaMart |
| **Versi** | `1.1.0` |
| **Tanggal** | 27 Oktober 2026 |
| **Status** | Final (Source-Code Backed) |
| **Penulis** | Tim Dokumentasi Teknis & Rekayasa Perangkat Lunak |
| **Sumber Data** | `composer.json`, `package.json`, `config/*.php`, `database/seeders/*.php` |

---

## Daftar Isi

1. [Prasyarat Sistem (Prerequisites)](#1-prasyarat-sistem-prerequisites)
2. [Langkah-Langkah Instalasi di Laragon](#2-langkah-langkah-instalasi-di-laragon)
3. [Referensi Variabel Konfigurasi `.env` (Nama & Fungsi)](#3-referensi-variabel-konfigurasi-env-nama--fungsi)
4. [Migrasi Database, Seeder, dan Symlink Storage](#4-migrasi-database-seeder-dan-symlink-storage)
5. [Kompilasi Aset Frontend (Vite & Tailwind CSS v4)](#5-kompilasi-aset-frontend-vite--tailwind-css-v4)
6. [Daftar Seeder dan Akun Awal Pengembangan Lokal](#6-daftar-seeder-dan-akun-awal-pengembangan-lokal)
7. [Rujukan Kode](#7-rujukan-kode)

---

## 1. Prasyarat Sistem (Prerequisites)

Pastikan perangkat pengembangan lokal Anda memenuhi spesifikasi berikut (`composer.json`, `package.json`):
- **Lingkungan Server Lokal:** Laragon (direkomendasikan untuk Windows) atau XAMPP/Docker.
- **PHP:** Versi `^8.3` (konfigurasi platform pada `composer.json#L85` adalah `8.3.30`) dengan ekstensi: `pdo_mysql`, `mbstring`, `openssl`, `gd`, `fileinfo`, `xml`, `curl`, dan `zip` (opsional, karena `XlsxWriter` memiliki fallback pure-PHP).
- **Database:** MySQL `8.0+` atau MariaDB `10.6+`.
- **Dependency Manager:** Composer `2.x` dan Node.js `20.x+` (beserta `npm`).

---

## 2. Langkah-Langkah Instalasi di Laragon

1. **Tempatkan Kode Sumber di Direktori Web Laragon:**
   ```bash
   cd C:\laragon\www\siswamartid
   ```
2. **Instal Dependensi PHP dan JavaScript:**
   ```bash
   composer install
   npm install
   ```
3. **Salin Berkas Konfigurasi Environment dan Buat App Key:**
   ```bash
   copy .env.example .env
   php artisan key:generate
   ```
4. **Buat Database MySQL Kosong:**
   Buat database baru bernama `siswamartid` (UTF8mb4 / `utf8mb4_unicode_ci`) melalui Laragon Database (HeidiSQL / phpMyAdmin) atau terminal MySQL.

---

## 3. Referensi Variabel Konfigurasi `.env` (Nama & Fungsi)

> [!IMPORTANT]
> Demi keamanan, dokumen ini **hanya mencantumkan NAMA variabel dan fungsinya**, tanpa menyertakan nilai rahasia, password, atau kunci API aktual.

| Nama Variabel `.env` | File Konfigurasi Pemakai | Fungsi dan Keterangan |
| :--- | :--- | :--- |
| `APP_NAME` | `config/app.php#L16` | Nama aplikasi yang ditampilkan di judul halaman dan email (`SiswaMart`). |
| `APP_ENV` | `config/app.php#L29` | Lingkungan eksekusi (`local`, `testing`, atau `production`). |
| `APP_KEY` | `config/app.php#L105` | Kunci enkripsi aplikasi 32-byte (dihasilkan oleh `php artisan key:generate`). |
| `APP_DEBUG` | `config/app.php#L42` | Mode debug (`true` hanya untuk lokal, **wajib `false`** di produksi). |
| `APP_URL` | `config/app.php#L55` | URL dasar aplikasi untuk pembuatan tautan aset dan storage. |
| `DB_CONNECTION` | `config/database.php#L19` | Driver koneksi database utama (`mysql`). |
| `DB_HOST`, `DB_PORT` | `config/database.php#L49-L50` | Host dan port server MySQL (`127.0.0.1`, `3306`). |
| `DB_DATABASE` | `config/database.php#L51` | Nama skema database (`siswamartid`). |
| `DB_USERNAME`, `DB_PASSWORD` | `config/database.php#L52-L53` | Kredensial pengguna database MySQL. |
| `SESSION_DRIVER` | `config/session.php#L21` | Penyimpanan sesi pengguna (`database`). |
| `CACHE_STORE` | `config/cache.php#L18` | Penyimpanan cache aplikasi (`database` atau `file`). |
| `QUEUE_CONNECTION` | `config/queue.php#L16` | Driver antrean pekerjaan (`database` atau `sync`). |
| `FILESYSTEM_DISK` | `config/filesystems.php#L16` | Disk penyimpanan berkas default (`local` / `public`). |
| `MAIL_MAILER`, `MAIL_HOST`, `MAIL_PORT`, `MAIL_USERNAME`, `MAIL_PASSWORD`, `MAIL_FROM_ADDRESS`, `MAIL_FROM_NAME` | `config/mail.php` | Konfigurasi server SMTP untuk pengiriman email reset password (`BrandedResetPassword`) dan verifikasi email (`BrandedVerifyEmail`). |
| `GOOGLE_CLIENT_ID` | `config/services.php#L37` | Client ID OAuth 2.0 dari Google Cloud Console. |
| `GOOGLE_CLIENT_SECRET` | `config/services.php#L38` | Client Secret OAuth 2.0 dari Google Cloud Console. |
| `GOOGLE_REDIRECT_URI` | `config/services.php#L39` | URL callback OAuth (`/auth/google/callback`). |
| `GOOGLE_HTTP_VERIFY` | `config/services.php#L42` | Opsional: Path ke berkas sertifikat `cacert.pem` pada Windows/Laragon atau boolean verifikasi SSL Guzzle. |
| `SANCTUM_STATEFUL_DOMAINS` | `config/sanctum.php#L18` | Daftar domain stateful untuk autentikasi Sanctum. |

---

## 4. Migrasi Database, Seeder, dan Symlink Storage

1. **Jalankan Seluruh 27 Migrasi Database:**
   ```bash
   php artisan migrate
   ```
2. **Jalankan Seeder Data Awal (Opsional namun Direkomendasikan untuk Lokal):**
   ```bash
   php artisan db:seed
   ```
   *(Atau muat kamus kata kasar secara spesifik dengan `php artisan comments:load-badwords`).*
3. **Buat Symbolic Link untuk Penyimpanan Gambar (`public/storage`):**
   ```bash
   php artisan storage:link
   ```

---

## 5. Kompilasi Aset Frontend (Vite & Tailwind CSS v4)

Berdasarkan `package.json#L5-L7` dan `composer.json#L56-L65`:
- **Untuk mode pengembangan interaktif (*Hot Module Replacement*):**
  ```bash
  npm run dev
  ```
  *(Atau jalankan server aplikasi, queue, dan vite sekaligus menggunakan `composer run dev`).*
- **Untuk membangun aset statis produksi (`public/build/`):**
  ```bash
  npm run build
  ```

---

## 6. Daftar Seeder dan Akun Awal Pengembangan Lokal

Urutan pemanggilan di `database/seeders/DatabaseSeeder.php#L14-L24`:

| No | Kelas Seeder | Tabel yang Diisi | Keterangan Data yang Dibuat |
| :---: | :--- | :--- | :--- |
| 1 | `AdminUserSeeder` | `users` | Membuat akun Admin awal lokal (`admin@siswamart.com`, `role = 'admin'`, `is_active = true`). **Catatan:** Segera ganti password default setelah login pertama. |
| 2 | `KategoriSeeder` | `kategori` | Mengisi 4 kategori utama: `Kuliner`, `Kerajinan`, `Jasa`, dan `Preloved`. |
| 3 | `PenjualPromptsSeeder` | `users`, `penjual` | Membuat akun-akun penjual contoh beserta profil toko, kelas, dan nomor WhatsApp untuk pengujian lokal. |
| 4 | `ProdukSeeder` | `produk`, `ulasan` | Membuat katalog produk contoh (minimal 10 produk per toko hasil seed) beserta sampel ulasan. |
| 5 | `BadWordsSeeder` | `bad_words`, `whitelists` | Memuat kamus kata terlarang dari `resources/data/badwords-list.json` (350 kata) dan daftar frasa aman (`whitelists`) ke database. |

---

## 7. Rujukan Kode

- `composer.json#L1-L97`
- `package.json#L1-L16`
- `config/app.php`, `config/database.php`, `config/filesystems.php`, `config/services.php`, `config/sanctum.php`
- `database/seeders/DatabaseSeeder.php#L1-L26`
- `database/seeders/AdminUserSeeder.php`, `KategoriSeeder.php`, `PenjualPromptsSeeder.php`, `ProdukSeeder.php`, `BadWordsSeeder.php`
