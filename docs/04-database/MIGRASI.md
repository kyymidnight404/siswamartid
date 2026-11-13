# Dokumentasi Migrasi Database — SiswaMart

| Informasi Dokumen | Detail |
| :--- | :--- |
| **Judul** | Daftar, Status, dan Urutan Migrasi Database SiswaMart |
| **Versi** | `1.1.0` |
| **Tanggal** | 27 Oktober 2026 |
| **Status** | Final (Source-Code & `php artisan migrate:status` Verified) |
| **Penulis** | Tim Dokumentasi Teknis & Rekayasa Perangkat Lunak |
| **Sumber Data** | Output aktual `php artisan migrate:status` dan 27 berkas di `database/migrations/` |

---

## Daftar Isi

1. [Ringkasan Eksekusi Migrasi](#1-ringkasan-eksekusi-migrasi)
2. [Tabel Daftar Lengkap 27 Migrasi](#2-tabel-daftar-lengkap-27-migrasi)
3. [Detail Migrasi Penyelarasan Skema (Batch 9 / `2026_10_15_*`)](#3-detail-migrasi-penyelarasan-skema-batch-9--2026_10_15_)
4. [Pemeriksaan Pra-Eksekusi (Pre-Flight Validation) pada Migrasi Batch 9](#4-pemeriksaan-pra-eksekusi-pre-flight-validation-pada-migrasi-batch-9)
5. [Urutan Aman Menjalankan Migrasi pada Lingkungan Baru / Produksi](#5-urutan-aman-menjalankan-migrasi-pada-lingkungan-baru--produksi)
6. [Rujukan Kode](#6-rujukan-kode)

---

## 1. Ringkasan Eksekusi Migrasi

Berdasarkan pemeriksaan langsung perintah `php artisan migrate:status` pada database `siswamartid`:
- **Total Berkas Migrasi:** **27 berkas** (`database/migrations/`).
- **Status di Database Lokal (`siswamartid`):** **27 `Ran` (100% telah dijalankan)** dalam **9 Batch** (`Batch 1` s.d. `Batch 9`), **0 `Pending`**.
- **Catatan Penting tentang Batch 9:** Lima migrasi bertanggal `2026_10_15_000001` s.d. `2026_10_15_000005` yang sebelumnya tercatat *Pending* pada dokumen `ERD.md` lama kini sudah berstatus **`Ran` pada Batch 9** di database lokal. Namun, bagi server staging/produksi yang belum diperbarui sejak 14 Oktober 2026, kelima migrasi ini wajib dijalankan secara berurutan.

---

## 2. Tabel Daftar Lengkap 27 Migrasi

| No | Nama Migrasi (`database/migrations/*.php`) | Batch Lokal | Status Lokal | Tabel yang Dibuat / Diubah | Tujuan Perubahan Skema |
| :---: | :--- | :---: | :---: | :--- | :--- |
| 1 | `0001_01_01_000000_create_users_table` | `1` | `Ran` | `users`, `password_reset_tokens`, `sessions` | Membuat tabel autentikasi pengguna dasar, token reset password, dan sesi HTTP. |
| 2 | `0001_01_01_000001_create_cache_table` | `1` | `Ran` | `cache`, `cache_locks` | Membuat tabel penyimpanan cache aplikasi dan atomic lock. |
| 3 | `0001_01_01_000002_create_jobs_table` | `1` | `Ran` | `jobs`, `job_batches`, `failed_jobs` | Membuat tabel antrean job latar belakang (*queue*). |
| 4 | `2026_08_21_025042_add_role_and_is_active_to_users_table` | `1` | `Ran` | `users` | Menambah kolom `role` (`enum('admin','penjual')` default `'penjual'`) dan `is_active` (`boolean` default `true`). |
| 5 | `2026_08_21_025055_create_penjual_table` | `1` | `Ran` | `penjual` | Membuat tabel profil toko penjual (`user_id`, `nama_toko`, `deskripsi_toko`, `nomor_whatsapp`, `lokasi_kelas`, `foto_toko`). |
| 6 | `2026_08_21_025056_create_kategori_table` | `1` | `Ran` | `kategori` | Membuat tabel master kategori (`nama` UK, `slug` UK, `icon`). |
| 7 | `2026_08_21_025057_create_produk_table` | `1` | `Ran` | `produk` | Membuat tabel katalog produk (`penjual_id` FK cascade, `kategori_id` FK restrict, `nama`, `slug` UK, `deskripsi`, `harga`, `stok`, `label_promosi`, `foto_utama`, `status`). |
| 8 | `2026_08_21_025058_create_ulasan_table` | `1` | `Ran` | `ulasan` | Membuat tabel ulasan pengunjung (`produk_id` FK cascade, `guest_username`, `rating`, `komentar`). |
| 9 | `2026_08_24_064657_add_views_to_produk_table` | `1` | `Ran` | `produk` | Menambahkan penghitung tayangan `views` (`bigInteger` default `0`) pada produk. |
| 10 | `2026_09_16_000001_create_bad_words_table` | `1` | `Ran` | `bad_words` | Membuat tabel awal kamus kata kasar (`word` UK, `is_active`). |
| 11 | `2026_09_16_000002_update_ulasan_table_for_moderation` | `1` | `Ran` | `ulasan` | Menambahkan kolom `original_komentar`, `is_censored`, dan `status` (default `'aktif'`). |
| 12 | `2026_09_16_000003_create_produk_views_logs_table` | `1` | `Ran` | `produk_views_logs` | Membuat tabel log trafik produk (`produk_id` FK, `type` `'view'`/`'whatsapp'`, indeks komposit waktu). |
| 13 | `2026_09_21_000001_create_whitelists_table` | `2` | `Ran` | `whitelists` | Membuat tabel daftar kata/frasa aman (`phrase` UK, `reason`, `is_active`). |
| 14 | `2026_09_21_000002_update_ulasan_table_for_full_moderation` | `2` | `Ran` | `ulasan` | Menambah kolom `moderation_status`, `display_content`, `detected_words`, `moderation_reason`, `moderated_at`, dan `moderated_by` (FK ke `users.id` `SET NULL`). |
| 15 | `2026_09_21_000003_create_comment_moderation_logs_table` | `2` | `Ran` | `comment_moderation_logs` | Membuat tabel jejak audit moderasi komentar oleh Admin. |
| 16 | `2026_09_21_000004_update_bad_words_table_schema` | `2` | `Ran` | `bad_words` | Menambah atribut klasifikasi kata kasar (`language`, `severity`, `category`, `type`). |
| 17 | `2026_09_25_000001_create_notifications_table` | `3` | `Ran` | `notifications` | Membuat tabel notifikasi polimorfik standar Laravel (UUID PK). |
| 18 | `2026_09_25_064535_create_personal_access_tokens_table` | `4` | `Ran` | `personal_access_tokens` | Membuat tabel token Bearer API Laravel Sanctum. |
| 19 | `2026_09_28_000001_add_rejection_reason_to_produk_table` | `5` | `Ran` | `produk` | Menambah kolom `alasan_ditolak` (`varchar(500) nullable`) dan memperluas enum `status` produk dengan nilai `'ditolak'`. |
| 20 | `2026_09_28_000002_add_seller_profile_activity_fields` | `6` | `Ran` | `penjual` | Menambah kolom pelacakan keaktifan penjual (`last_login_at`, `last_activity_at`, `last_activity_type`). |
| 21 | `2026_10_14_000001_add_status_toko_to_penjual_table` | `7` | `Ran` | `penjual` | Menambah kolom `status_toko` (awalnya `string(20)` default `'buka'`) beserta backfill dari tabel `settings` lama jika ada. |
| 22 | `2026_10_14_000002_add_must_change_password_to_users_table` | `8` | `Ran` | `users` | Menambah kolom boolean `must_change_password` (default `false`). |
| 23 | `2026_10_15_000001_align_local_schema_with_erd` | `9` | `Ran` *(Ex-Pending)* | `users`, `penjual` | Menambah `users.username` (UK, backfill otomatis dari email/nama), `penjual.nisn` (`varchar(10)` UK), `penjual.alamat`, dan constraint unik `penjual_user_id_unique`. |
| 24 | `2026_10_15_000002_normalize_erd_status_values` | `9` | `Ran` *(Ex-Pending)* | `penjual`, `produk`, `ulasan` | Menormalisasi tipe kolom `penjual.status_toko` (`enum('buka','tutup')`), `produk.stok` (`enum('tersedia','po','habis')`), `produk.status` (`enum('draft','pending','aktif','ditolak')`), dan `ulasan.rating` (`tinyint unsigned`). |
| 25 | `2026_10_15_000003_remove_admin_creator_from_penjual` | `9` | `Ran` *(Ex-Pending)* | `penjual` | Menghapus kolom sementara `created_by_admin_id` dari tabel `penjual`. |
| 26 | `2026_10_15_000004_add_product_moderation_requests` | `9` | `Ran` *(Ex-Pending)* | `produk` | Menambah kolom `pending_action` (`varchar(20)`) dan `pending_changes` (`json`) untuk alur moderasi dua tahap. |
| 27 | `2026_10_15_000005_add_google_id_to_users_table` | `9` | `Ran` *(Ex-Pending)* | `users` | Menambah kolom `google_id` (`varchar(255) nullable` UK) untuk integrasi login Google OAuth. |

---

## 3. Detail Migrasi Penyelarasan Skema (Batch 9 / `2026_10_15_*`)

Kelima migrasi pada Batch 9 dirancang dengan mekanisme **backfill data otomatis** agar data lama tidak rusak saat migrasi dijalankan:

1. **`2026_10_15_000001_align_local_schema_with_erd.php` (`#L21-L157`)**:
   - Membuat kolom `username` pada `users` terlebih dahulu sebagai `nullable`, lalu mengisi otomatis seluruh baris `users` yang belum memiliki `username` menggunakan bagian lokal dari `email` atau `Str::slug($user->name)` dengan akhiran angka jika terjadi duplikasi (`resolveUniqueUsername`, `#L134-L157`), sebelum mengubah kolom menjadi `NOT NULL UNIQUE`.
2. **`2026_10_15_000002_normalize_erd_status_values.php` (`#L15-L75`)**:
   - Mengonversi produk lama yang masih memiliki `status = 'nonaktif'` menjadi `'pending'` sebelum mempersempit definisi `ENUM('draft', 'pending', 'aktif', 'ditolak')`.
3. **`2026_10_15_000004_add_product_moderation_requests.php` (`#L18-L25`)**:
   - Mengisi nilai `pending_action = 'create'` secara otomatis untuk seluruh produk yang saat itu sedang berstatus `'pending'` dan belum memiliki `pending_action`.

---

## 4. Pemeriksaan Pra-Eksekusi (Pre-Flight Validation) pada Migrasi Batch 9

Migrasi `2026_10_15_000001` dan `2026_10_15_000002` memiliki pemeriksaan integritas data ketat yang akan melempar `RuntimeException` jika data di server tujuan tidak bersih. Sebelum menjalankan `php artisan migrate` pada database lama di server produksi, pastikan kondisi berikut terpenuhi:

```sql
-- 1. Pastikan tidak ada user_id ganda di tabel penjual (syarat 2026_10_15_000001#L86-L96)
SELECT user_id, COUNT(*) AS total FROM penjual GROUP BY user_id HAVING total > 1;

-- 2. Pastikan tidak ada rating ulasan di luar rentang 1..5 (syarat 2026_10_15_000002#L63-L70)
SELECT id, rating FROM ulasan WHERE rating IS NULL OR rating < 1 OR rating > 5;
```

Jika kedua kueri di atas mengembalikan `0 rows`, maka migrasi Batch 9 dijamin berjalan mulus tanpa `RuntimeException`.

---

## 5. Urutan Aman Menjalankan Migrasi pada Lingkungan Baru / Produksi

### 5.1 Instalasi Baru (Database Kosong)
Pada instalasi lokal baru atau server baru:
```bash
php artisan migrate
php artisan db:seed
```
Seluruh 27 migrasi akan dieksekusi secara berurutan dari nomor 1 hingga 27 dalam satu batch.

### 5.2 Pembaruan Server Produksi (Upgrade dari Batch 8 ke Batch 9)
1. **Cadangkan Database (*Backup*):**
   ```bash
   mysqldump -u <user> -p <nama_db> > backup_pre_batch9.sql
   ```
2. **Periksa Status Migrasi:**
   ```bash
   php artisan migrate:status
   ```
3. **Jalankan Migrasi:**
   ```bash
   php artisan migrate --force
   ```
4. **Bersihkan Cache Konfigurasi & Rute:**
   ```bash
   php artisan optimize:clear
   ```

> [!CAUTION]
> **Dilarang** menjalankan `php artisan migrate:fresh` atau `php artisan migrate:reset` pada database yang sudah berisi data pengguna karena perintah tersebut akan menghapus seluruh tabel (`DROP TABLE`).

---

## 6. Rujukan Kode

- `database/migrations/2026_10_15_000001_align_local_schema_with_erd.php#L1-L250`
- `database/migrations/2026_10_15_000002_normalize_erd_status_values.php#L1-L131`
- `database/migrations/2026_10_15_000003_remove_admin_creator_from_penjual.php#L1-L55`
- `database/migrations/2026_10_15_000004_add_product_moderation_requests.php#L1-L40`
- `database/migrations/2026_10_15_000005_add_google_id_to_users_table.php#L1-L28`
