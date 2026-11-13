# Panduan Deployment ke Hosting (cPanel / VPS) — SiswaMart

| Informasi Dokumen | Detail |
| :--- | :--- |
| **Judul** | Panduan Deployment Produksi SiswaMart (Shared Hosting cPanel & VPS) |
| **Versi** | `1.1.0` |
| **Tanggal** | 27 Oktober 2026 |
| **Status** | Final (Source-Code Backed) |
| **Penulis** | Tim Dokumentasi Teknis & Rekayasa Perangkat Lunak |
| **Sumber Data** | `public/index.php`, `config/dompdf.php`, `public/deploy-helper.php`, `bootstrap/app.php` |

---

## Daftar Isi

1. [Arsitektur Deployment Multi-Path (`public/index.php`)](#1-arsitektur-deployment-multi-path-publicindexphp)
2. [Opsi A: Deployment pada Shared Hosting (cPanel / DirectAdmin)](#2-opsi-a-deployment-pada-shared-hosting-cpanel--directadmin)
3. [Opsi B: Deployment pada VPS (Nginx / Apache)](#3-opsi-b-deployment-pada-vps-nginx--apache)
4. [Keamanan Kritis pasca-Deployment (`public/deploy-helper.php`)](#4-keamanan-kritis-pasca-deployment-publicdeploy-helperphp)
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

### 2.1 Struktur Folder di cPanel
Tempatkan kode inti aplikasi di luar `public_html` agar berkas `.env` dan `database/` tidak pernah bisa diakses dari luar:

```text
/home/username_cpanel/
├── core/                       # Seluruh isi proyek (app, bootstrap, config, database, resources, routes, storage, vendor, .env)
└── public_html/                # Seluruh isi dari folder public/ (index.php, .htaccess, build/, css/, images/)
```

### 2.2 Langkah-Langkah Setup di cPanel
1. **Build Aset di Lokal Sebelum Upload:**
   ```bash
   npm run build
   composer install --no-dev --optimize-autoloader
   ```
2. **Unggah Berkas:**
   - Unggah isi folder `public/` ke `/home/username/public_html/`.
   - Unggah seluruh folder proyek lainnya ke `/home/username/core/`.
3. **Konfigurasi `.env` Produksi di `/home/username/core/.env`:**
   - Setel `APP_ENV=production` dan **`APP_DEBUG=false`**.
   - Sesuaikan `APP_URL=https://domain-sekolah.sch.id`.
   - Sesuaikan kredensial database MySQL cPanel (`DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`).
4. **Jalankan Migrasi & Buat Symlink Storage:**
   Melalui fitur **Terminal** di cPanel (atau SSH):
   ```bash
   cd ~/core
   php artisan migrate --force
   ln -s ~/core/storage/app/public ~/public_html/storage
   php artisan optimize
   ```

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

## 4. Keamanan Kritis pasca-Deployment (`public/deploy-helper.php`)

> [!CAUTION]
> **WAJIB DIHAPUS DI PRODUKSI (`RSK-01`):**
> Di dalam repositori terdapat berkas utilitas `public/deploy-helper.php` (`#L1-L163`) yang dibuat untuk membantu eksekusi `storage:link`, `migrate`, dan `optimize:clear` di shared hosting yang tidak memiliki akses SSH.
> - **Jangan pernah membiarkan `public/deploy-helper.php` tetap berada di `public_html/` pada server produksi.**
> - Segera **hapus berkas `deploy-helper.php`** begitu proses instalasi selesai agar pihak luar tidak dapat memicu aksi Artisan melalui URL publik.

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

- [ ] `APP_ENV=production` dan `APP_DEBUG=false` di berkas `.env`.
- [ ] Berkas **`public/deploy-helper.php` telah dihapus** dari server publik.
- [ ] Seluruh 27 migrasi berstatus `Ran` (`php artisan migrate:status`).
- [ ] Symlink `public/storage` berfungsi dan gambar produk dapat dimuat (`HTTP 200`).
- [ ] `npm run build` telah dijalankan dan `public/build/manifest.json` tersedia.
- [ ] Sertifikat HTTPS (SSL/TLS) aktif pada domain dan `GOOGLE_REDIRECT_URI` menggunakan skema `https://`.
- [ ] Password akun Admin awal telah diganti dengan kata sandi kuat.

---

## 8. Rujukan Kode

- `public/index.php#L1-L48`
- `config/dompdf.php#L42`
- `public/deploy-helper.php#L1-L163`
- `config/filesystems.php#L32-L65`
