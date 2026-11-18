# Panduan Deployment ke Hosting (cPanel / VPS) — SiswaMart

| Informasi Dokumen | Detail |
| :--- | :--- |
| **Judul** | Panduan Deployment Produksi SiswaMart (DirectAdmin / cPanel & VPS) |
| **Versi** | `1.2.0` |
| **Tanggal** | 18 November 2026 |
| **Status** | Final (Source-Code & Live Hosting Verified) |
| **Penulis** | Tim Dokumentasi Teknis & Rekayasa Perangkat Lunak |
| **Sumber Data** | `public/index.php`, `public/.htaccess`, `scripts/deploy/create-siswamart-root-zip.ps1`, `scripts/deploy/deploy-helper.php`, `PANDUAN_HOSTING.txt` |

---

## Daftar Isi

1. [Arsitektur Deployment Multi-Path (`public/index.php`)](#1-arsitektur-deployment-multi-path-publicindexphp)
2. [Opsi A: Deployment pada Shared Hosting (cPanel / DirectAdmin)](#2-opsi-a-deployment-pada-shared-hosting-cpanel--directadmin)
3. [Opsi B: Deployment pada VPS (Nginx / Apache)](#3-opsi-b-deployment-pada-vps-nginx--apache)
4. [Keamanan Kritis pasca-Deployment (`deploy-helper.php`)](#4-keamanan-kritis-pasca-deployment-deploy-helperphp)
5. [Hak Akses Direktori (Permissions) dan Optimasi Cache](#5-hak-akses-direktori-permissions-dan-optimasi-cache)
6. [Prosedur Backup Database dan Penyimpanan Berkas](#6-prosedur-backup-database-dan-penyimpanan-berkas)
7. [Checklist Kesiapan Produksi](#7-checklist-kesiapan-produksi)
8. [Rujukan Kode](#8-rujukan-kode)

---

## 1. Arsitektur Deployment Multi-Path (`public/index.php`)

Berkas `public/index.php` (`public/index.php#L14-L46`) telah dirancang secara khusus agar dapat mengenali **3 pola tata letak direktori** tanpa perlu memodifikasi kode sumber saat diunggah ke server:

```php
// public/index.php#L14-L36
$basePathCandidates = [
    realpath(__DIR__.'/..'),
    realpath(__DIR__.'/../core'),
    realpath(__DIR__.'/../siswamart'),
];
```

Selain itu, `public/index.php#L44` memanggil `$app->usePublicPath(__DIR__)` dan `config/dompdf.php#L42` menggunakan `realpath(public_path()) ?: base_path('public')`, sehingga pencetakan laporan PDF (DomPDF) dan pemuatan logo tetap berfungsi meskipun folder `public` dipindahkan ke `public_html`.

---

## 2. Opsi A: Deployment pada Shared Hosting (cPanel / DirectAdmin)

### 2.1 Struktur Folder Aktual di Hosting (DirectAdmin)
Tempatkan kode inti aplikasi di luar `public_html` agar berkas `.env` dan `database/` tidak pernah bisa diakses dari luar:

```text
/domains/siswamartid.my.id/
├── siswamart/                  # Core Laravel (app, bootstrap, config, database, resources, routes, storage, vendor, .env)
├── public_html/                # Web root publik (index.php, .htaccess, build/, css/, fonts/, robots.txt, storage symlink)
└── siswamartid.git/            # Bare Git repository DirectAdmin (sinkron via Fetch ke GitHub main)
```

### 2.2 Langkah-Langkah Packing & Upload Update
1. **Bangun Paket Produksi Otomatis (`dist/hosting.zip`):**
   ```powershell
   powershell -ExecutionPolicy Bypass -File scripts/deploy/create-siswamart-root-zip.ps1
   ```
   *(Script ini otomatis menjalankan `npm run build`, menyertakan `scripts/deploy/deploy-helper.php` ke `public_html/`, mengecualikan `.env` dan `*.tmp`, serta mengompresi ZIP berizin Unix.)*
2. **Unggah & Ekstrak via File Manager DirectAdmin:**
   - Upload `dist/hosting.zip` ke `/domains/siswamartid.my.id/` lalu **Extract** (Merge & overwrite).
3. **Konfigurasi `.env` Produksi di `/domains/siswamartid.my.id/siswamart/.env`:**
   - Setel `APP_ENV=production` dan **`APP_DEBUG=false`**.
   - Setel domain canonical **`APP_URL=https://siswamart.app`** dan **`GOOGLE_REDIRECT_URI=https://siswamart.app/auth/google/callback`** (`public/.htaccess` mengalihkan akses `siswamartid.my.id` dengan `301` ke `https://siswamart.app` sembari menjaga jalur `/.well-known/acme-challenge/`).
   - Sesuaikan kredensial database MySQL/MariaDB hosting (`DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`).
4. **Jalankan Migrasi, Symlink, & Cache Clear:**
   - Akses `https://siswamart.app/deploy-helper.php?key=siswamart2026` (atau via terminal `php artisan migrate --force && php artisan optimize:clear`).
   - Pastikan **seluruh 32 migrasi** (termasuk objek SQL View/Routine/Trigger) sukses tereksekusi.
   - **Segera hapus** `public_html/deploy-helper.php` dan `hosting.zip` setelah selesai.

---

## 3. Opsi B: Deployment pada VPS (Nginx / Apache)

Pada VPS Ubuntu/Debian, Anda dapat mempertahankan struktur satu folder utuh (misal `/var/www/siswamartid`) dan mengarahkan `root` web server langsung ke `/var/www/siswamartid/public`:

```nginx
server {
    listen 80;
    server_name siswamart.smk.sch.id;
    root /var/www/siswamartid/public;

    index index.php;
    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

---

## 4. Keamanan Kritis pasca-Deployment (`deploy-helper.php`)

> [!CAUTION]
> **WAJIB DIHAPUS DI PRODUKSI (`RSK-01`):**
> Skrip bantu deployment `scripts/deploy/deploy-helper.php` (yang ikut disalin ke `public_html/deploy-helper.php` saat packing ZIP) mengizinkan eksekusi `migrate`, `storage:link`, dan pembersihan cache melalui HTTP dengan kunci statis.
> - **Jangan pernah membiarkan `deploy-helper.php` atau arsip `.zip` tertinggal di `public_html/` pada server produksi.**
> - Segera **hapus berkas `public_html/deploy-helper.php`** begitu proses migrasi selesai agar tidak dapat diakses publik (`404 Not Found`).

---

## 5. Hak Akses Direktori (Permissions) dan Optimasi Cache

1. **Permission Direktori Wajib Tulis (`Writable`):**
   Pastikan user web server (`www-data` atau user cPanel) memiliki izin tulis pada:
   ```bash
   chmod -R 775 storage bootstrap/cache
   ```
2. **Optimasi Cache Produksi:**
   Setiap kali selesai memperbarui kode atau `.env` di produksi, jalankan:
   ```bash
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   ```

---

## 6. Prosedur Backup Database dan Penyimpanan Berkas

Untuk menjaga keberlangsungan data wirausaha sekolah:
1. **Backup Database Harian/Mingguan:**
   Cadangkan seluruh 19 tabel MySQL:
   ```bash
   mysqldump -u <db_user> -p <db_name> | gzip > backup_siswamart_$(date +%F).sql.gz
   ```
2. **Backup Berkas Unggahan (`storage/app/public`):**
   Cadangkan folder `storage/app/public/produk` dan `storage/app/public/penjual` yang menyimpan seluruh foto produk dan foto profil toko siswa.

---

## 7. Checklist Kesiapan Produksi

- [ ] `APP_ENV=production`, `APP_DEBUG=false`, dan `APP_URL=https://siswamart.app` di berkas `.env`.
- [ ] Berkas **`public_html/deploy-helper.php` dan arsip `.zip` telah dihapus permanen** dari server publik.
- [ ] Seluruh **32 migrasi** berstatus `Ran` (`php artisan migrate:status`), termasuk objek SQL View, Stored Function/Procedure, dan Trigger.
- [ ] Symlink `public_html/storage` berfungsi dan gambar produk dapat dimuat (`HTTP 200`).
- [ ] `npm run build` telah dijalankan dan `public_html/build/manifest.json` tersedia.
- [ ] Sertifikat HTTPS (SSL/TLS) aktif pada `siswamart.app`, redirect `301` dari `siswamartid.my.id` berjalan, dan `GOOGLE_REDIRECT_URI=https://siswamart.app/auth/google/callback` terdaftar di Google Cloud Console.
- [ ] Password akun Admin awal telah diganti dengan kata sandi kuat.

---

## 8. Rujukan Kode

- `public/index.php`
- `public/.htaccess`
- `config/dompdf.php#L42`
- `scripts/deploy/create-siswamart-root-zip.ps1`
- `scripts/deploy/deploy-helper.php`
- `PANDUAN_HOSTING.txt`
- `config/filesystems.php#L32-L65`
