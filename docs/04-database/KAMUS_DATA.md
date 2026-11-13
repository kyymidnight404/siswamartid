# Kamus Data (Data Dictionary) — SiswaMart

| Informasi Dokumen | Detail |
| :--- | :--- |
| **Judul** | Kamus Data (Data Dictionary) Database SiswaMart |
| **Versi** | `1.1.0` |
| **Tanggal** | 27 Oktober 2026 |
| **Status** | Final (Source-Code & Live DB Verified) |
| **Penulis** | Tim Dokumentasi Teknis & Rekayasa Perangkat Lunak |
| **Sumber Data** | `SHOW FULL COLUMNS` & `SHOW INDEX` pada MySQL `siswamartid`, `database/migrations/`, `app/Models/` |

---

## Daftar Isi

1. [Daftar Seluruh Tabel Database (19 Tabel)](#1-daftar-seluruh-tabel-database-19-tabel)
2. [Tabel Domain Bisnis Utama](#2-tabel-domain-bisnis-utama)
   - [2.1 Tabel `users`](#21-tabel-users)
   - [2.2 Tabel `penjual`](#22-tabel-penjual)
   - [2.3 Tabel `kategori`](#23-tabel-kategori)
   - [2.4 Tabel `produk`](#24-tabel-produk)
   - [2.5 Tabel `ulasan`](#25-tabel-ulasan)
   - [2.6 Tabel `produk_views_logs`](#26-tabel-produk_views_logs)
3. [Tabel Moderasi, Notifikasi, & Token API](#3-tabel-moderasi-notifikasi--token-api)
   - [3.1 Tabel `bad_words`](#31-tabel-bad_words)
   - [3.2 Tabel `whitelists`](#32-tabel-whitelists)
   - [3.3 Tabel `comment_moderation_logs`](#33-tabel-comment_moderation_logs)
   - [3.4 Tabel `notifications`](#34-tabel-notifications)
   - [3.5 Tabel `personal_access_tokens`](#35-tabel-personal_access_tokens)
4. [Tabel Infrastruktur Framework Laravel](#4-tabel-infrastruktur-framework-laravel)
5. [Rujukan Kode](#5-rujukan-kode)

---

## 1. Daftar Seluruh Tabel Database (19 Tabel)

| No | Nama Tabel | Model Eloquent | Kategori | Jumlah Kolom | Keterangan Singkat |
| :---: | :--- | :--- | :--- | :---: | :--- |
| 1 | `users` | `App\Models\User` | Domain Inti | 13 | Akun autentikasi Admin dan Penjual. |
| 2 | `penjual` | `App\Models\Penjual` | Domain Inti | 15 | Profil toko siswa, NISN, kontak WhatsApp, dan status toko. |
| 3 | `kategori` | `App\Models\Kategori` | Domain Inti | 6 | Master kategori katalog produk. |
| 4 | `produk` | `App\Models\Produk` | Domain Inti | 17 | Data produk, harga, stok, status moderasi, dan `pending_changes`. |
| 5 | `ulasan` | `App\Models\Ulasan` | Domain Inti | 16 | Ulasan tamu, rating 1–5, dan metadata hasil moderasi kata kasar. |
| 6 | `produk_views_logs` | `App\Models\ProdukViewLog` | Analitik | 5 | Log waktu kunjungan halaman (`view`) dan klik tombol (`whatsapp`). |
| 7 | `bad_words` | `App\Models\BadWord` | Moderasi | 9 | Kamus kata terlarang tambahan di database. |
| 8 | `whitelists` | `App\Models\Whitelist` | Moderasi | 6 | Daftar frasa aman yang dikecualikan dari penyaringan kata kasar. |
| 9 | `comment_moderation_logs` | `App\Models\CommentModerationLog` | Moderasi | 8 | Log audit tindakan moderasi komentar oleh Admin. |
| 10 | `notifications` | `Illuminate\Notifications\DatabaseNotification` | Sistem | 8 | Notifikasi lonceng dasbor Admin. |
| 11 | `personal_access_tokens` | `Laravel\Sanctum\PersonalAccessToken` | Keamanan API | 10 | Token Bearer API Laravel Sanctum. |
| 12 | `password_reset_tokens` | — (Framework) | Autentikasi | 3 | Token pengaturan ulang kata sandi via email. |
| 13 | `sessions` | — (Framework) | Infrastruktur | 6 | Penyimpanan sesi HTTP pengguna aktif. |
| 14 | `cache` | — (Framework) | Infrastruktur | 3 | Penyimpanan cache aplikasi di database. |
| 15 | `cache_locks` | — (Framework) | Infrastruktur | 3 | Kunci atomik (*atomic lock*) untuk cache. |
| 16 | `jobs` | — (Framework) | Infrastruktur | 7 | Antrean pekerjaan latar belakang (*queue*). |
| 17 | `job_batches` | — (Framework) | Infrastruktur | 10 | Metadata batch antrean pekerjaan. |
| 18 | `failed_jobs` | — (Framework) | Infrastruktur | 7 | Log pekerjaan antrean yang gagal dieksekusi. |
| 19 | `migrations` | — (Framework) | Infrastruktur | 3 | Pencatatan versi migrasi database yang telah dijalankan. |

---

## 2. Tabel Domain Bisnis Utama

### 2.1 Tabel `users`
- **Model:** `App\Models\User` (`app/Models/User.php`)
- **Deskripsi:** Menyimpan kredensial login, peran (`admin`/`penjual`), status aktif akun, dan ID Google OAuth.

| Nama Kolom | Tipe Data MySQL | Nullable | Default | Key / Indeks | Relasi / Constraint | Keterangan |
| :--- | :--- | :---: | :--- | :--- | :--- | :--- |
| `id` | `bigint unsigned` | Tidak | `AUTO_INCREMENT` | `PRI` | PK | Identitas unik pengguna. |
| `name` | `varchar(255)` | Tidak | — | — | — | Nama lengkap pengguna (siswa/admin). |
| `username` | `varchar(80)` | Tidak | — | `UNI` (`users_username_unique`) | Unik (`2026_10_15_000001`) | Username unik untuk login selain email. |
| `email` | `varchar(255)` | Tidak | — | `UNI` (`users_email_unique`) | Unik | Alamat email pengguna. |
| `google_id` | `varchar(255)` | Ya | `NULL` | `UNI` (`users_google_id_unique`) | Unik (`2026_10_15_000005`) | Subjek ID akun Google dari Laravel Socialite. |
| `email_verified_at` | `datetime` | Ya | `NULL` | — | — | Waktu verifikasi email. |
| `password` | `varchar(255)` | Tidak | — | — | Cast: `hashed` (Bcrypt) | Hash kata sandi pengguna. |
| `remember_token` | `varchar(100)` | Ya | `NULL` | — | — | Token sesi "Ingat Saya". |
| `created_at` | `datetime` | Ya | `NULL` | — | — | Waktu pembuatan akun. |
| `updated_at` | `datetime` | Ya | `NULL` | — | — | Waktu pembaruan terakhir. |
| `role` | `enum('admin','penjual')` | Tidak | `'penjual'` | — | — | Peran otorisasi pengguna. |
| `is_active` | `tinyint(1)` | Tidak | `1` | — | Cast: `boolean` | Status aktif (`1` = aktif, `0` = dinonaktifkan admin). |
| `must_change_password` | `tinyint(1)` | Tidak | `0` | — | Cast: `boolean` | Penanda kewajiban ganti password awal. |

---

### 2.2 Tabel `penjual`
- **Model:** `App\Models\Penjual` (`app/Models/Penjual.php`)
- **Deskripsi:** Menyimpan profil toko siswa, NISN 10 digit, nomor WhatsApp, kelas/jurusan, dan status operasional toko.

| Nama Kolom | Tipe Data MySQL | Nullable | Default | Key / Indeks | Relasi / Constraint | Keterangan |
| :--- | :--- | :---: | :--- | :--- | :--- | :--- |
| `id` | `bigint unsigned` | Tidak | `AUTO_INCREMENT` | `PRI` | PK | Identitas unik toko penjual. |
| `nisn` | `varchar(10)` | Ya | `NULL` | `UNI` (`penjual_nisn_unique`) | Unik (`2026_10_15_000001`) | Nomor Induk Siswa Nasional (wajib 10 digit angka saat dibuat Admin). |
| `alamat` | `text` | Ya | `NULL` | — | — | Alamat domisili/keterangan lokasi siswa (`2026_10_15_000001`). |
| `user_id` | `bigint unsigned` | Tidak | — | `UNI` (`penjual_user_id_unique`) | FK $\rightarrow$ `users.id` (`ON DELETE CASCADE`) | Relasi `1:1` ke akun pemilik toko. |
| `nama_toko` | `varchar(255)` | Tidak | — | — | — | Nama etalase toko siswa. |
| `deskripsi_toko` | `text` | Ya | `NULL` | — | — | Deskripsi singkat mengenai toko. |
| `nomor_whatsapp` | `varchar(255)` | Tidak | — | — | — | Nomor WhatsApp tujuan pemesanan produk. |
| `lokasi_kelas` | `varchar(255)` | Ya | `NULL` | — | — | Informasi kelas/jurusan siswa (misal `XII RPL 1`). |
| `foto_toko` | `varchar(255)` | Ya | `NULL` | — | — | Path berkas foto profil toko di disk `public`. |
| `status_toko` | `enum('buka','tutup')` | Tidak | `'buka'` | — | Normalisasi `2026_10_15_000002` | Status operasional toko (`buka` atau `tutup`). |
| `created_at` | `datetime` | Ya | `NULL` | — | — | Waktu pembuatan toko. |
| `updated_at` | `datetime` | Ya | `NULL` | — | — | Waktu pembaruan profil toko. |
| `last_login_at` | `timestamp` | Ya | `NULL` | — | Cast: `datetime` | Waktu terakhir penjual berhasil login. |
| `last_activity_at` | `timestamp` | Ya | `NULL` | — | Cast: `datetime` | Waktu terakhir aktivitas penjual (login/kelola produk/update profil). |
| `last_activity_type` | `varchar(50)` | Ya | `NULL` | — | — | Kode jenis aktivitas terakhir (`login`, `product_created`, `profile_updated`, dll.). |

---

### 2.3 Tabel `kategori`
- **Model:** `App\Models\Kategori` (`app/Models/Kategori.php`)
- **Deskripsi:** Menyimpan daftar kategori produk yang dikelola oleh Admin.

| Nama Kolom | Tipe Data MySQL | Nullable | Default | Key / Indeks | Relasi / Constraint | Keterangan |
| :--- | :--- | :---: | :--- | :--- | :--- | :--- |
| `id` | `bigint unsigned` | Tidak | `AUTO_INCREMENT` | `PRI` | PK | Identitas unik kategori. |
| `nama` | `varchar(255)` | Tidak | — | `UNI` (`kategori_nama_unique`) | Unik | Nama kategori (misal: `Kuliner`, `Kerajinan`, `Jasa`, `Preloved`). |
| `slug` | `varchar(255)` | Tidak | — | `UNI` (`kategori_slug_unique`) | Unik | Slug URL ramah SEO (`routeKeyName = 'slug'`). |
| `icon` | `varchar(255)` | Ya | `NULL` | — | — | Nama ikon atau penanda visual kategori. |
| `created_at` | `datetime` | Ya | `NULL` | — | — | Waktu pembuatan kategori. |
| `updated_at` | `datetime` | Ya | `NULL` | — | — | Waktu pembaruan kategori. |

---

### 2.4 Tabel `produk`
- **Model:** `App\Models\Produk` (`app/Models/Produk.php`)
- **Deskripsi:** Menyimpan item produk penjual, status ketersediaan stok, status publikasi, serta payload revisi `pending_changes`.

| Nama Kolom | Tipe Data MySQL | Nullable | Default | Key / Indeks | Relasi / Constraint | Keterangan |
| :--- | :--- | :---: | :--- | :--- | :--- | :--- |
| `id` | `bigint unsigned` | Tidak | `AUTO_INCREMENT` | `PRI` | PK | Identitas unik produk. |
| `penjual_id` | `bigint unsigned` | Tidak | — | `MUL` (`produk_penjual_id_index`) | FK $\rightarrow$ `penjual.id` (`ON DELETE CASCADE`) | Pemilik produk. |
| `kategori_id` | `bigint unsigned` | Tidak | — | `MUL` (`produk_kategori_id_index`) | FK $\rightarrow$ `kategori.id` (`ON DELETE NO ACTION`) | Kategori produk (tidak dapat dihapus jika masih dipakai). |
| `nama` | `varchar(255)` | Tidak | — | — | — | Nama produk yang tampil di katalog. |
| `slug` | `varchar(255)` | Tidak | — | `UNI` (`produk_slug_unique`) | Unik | Slug URL unik (`routeKeyName = 'slug'` di rute publik). |
| `deskripsi` | `text` | Ya | `NULL` | — | — | Deskripsi detail spesifikasi produk. |
| `harga` | `decimal(12,2)` | Tidak | — | — | Cast: `decimal:2` | Harga satuan produk dalam Rupiah. |
| `stok` | `enum('tersedia','po','habis')` | Tidak | `'tersedia'` | — | Normalisasi `2026_10_15_000002` | Status ketersediaan: `tersedia` (Stok Tersedia), `po` (Pre-Order), `habis` (Stok Habis). |
| `label_promosi` | `varchar(255)` | Ya | `NULL` | — | — | Label pita promosi opsional (maks. 50 karakter di validasi). |
| `foto_utama` | `varchar(255)` | Ya | `NULL` | — | — | Path relatif gambar produk pada disk `public` (`produk/...`). |
| `status` | `enum('draft','pending','aktif','ditolak')` | Tidak | `'pending'` | — | Normalisasi `2026_10_15_000002` | Status publikasi produk. Hanya `aktif` yang tampil di publik. |
| `created_at` | `datetime` | Ya | `NULL` | — | — | Waktu pembuatan produk. |
| `updated_at` | `datetime` | Ya | `NULL` | — | — | Waktu pembaruan produk. |
| `views` | `bigint` | Tidak | `0` | — | — | Akumulasi total tayangan halaman detail produk. |
| `alasan_ditolak` | `varchar(500)` | Ya | `NULL` | — | — | Catatan alasan penolakan dari Admin jika ajuan ditolak. |
| `pending_action` | `varchar(20)` | Ya | `NULL` | — | `2026_10_15_000004` | Jenis ajuan moderasi yang sedang aktif (`'create'`, `'update'`, `'delete'`, atau `NULL`). |
| `pending_changes` | `json` | Ya | `NULL` | — | Cast: `array` (`2026_10_15_000004`) | Payload JSON perubahan produk saat `pending_action = 'update'`. |

---

### 2.5 Tabel `ulasan`
- **Model:** `App\Models\Ulasan` (`app/Models/Ulasan.php`)
- **Deskripsi:** Menyimpan ulasan dan rating bintang dari pengunjung publik serta metadata hasil pemeriksaan `CommentModerationService`.

| Nama Kolom | Tipe Data MySQL | Nullable | Default | Key / Indeks | Relasi / Constraint | Keterangan |
| :--- | :--- | :---: | :--- | :--- | :--- | :--- |
| `id` | `bigint unsigned` | Tidak | `AUTO_INCREMENT` | `PRI` | PK | Identitas unik ulasan. |
| `produk_id` | `bigint unsigned` | Tidak | — | `MUL` (`ulasan_produk_id_index`) | FK $\rightarrow$ `produk.id` (`ON DELETE CASCADE`) | Produk yang diulas. |
| `guest_username` | `varchar(255)` | Tidak | — | — | — | Nama pengulas tamu (maks. 100 karakter di validasi). |
| `rating` | `tinyint unsigned` | Tidak | — | — | Cast: `integer` (`1..5`) | Nilai rating bintang antara 1 sampai 5. |
| `komentar` | `text` | Ya | `NULL` | — | — | Teks komentar ulasan yang ditampilkan. |
| `original_komentar` | `text` | Ya | `NULL` | — | — | Salinan komentar asli sebelum penyensoran. |
| `is_censored` | `tinyint(1)` | Tidak | `0` | — | Cast: `boolean` | Penanda apakah komentar mengandung kata yang disensor. |
| `status` | `varchar(255)` | Tidak | `'aktif'` | — | — | Status keaktifan ulasan (`'aktif'`). |
| `created_at` | `datetime` | Ya | `NULL` | — | — | Waktu pengiriman ulasan. |
| `updated_at` | `datetime` | Ya | `NULL` | — | — | Waktu pembaruan ulasan. |
| `moderation_status` | `varchar(255)` | Tidak | `'approved'` | — | — | Status moderasi (`approved`, `flagged`, `rejected`, `censored`). |
| `display_content` | `text` | Ya | `NULL` | — | — | Teks komentar bersih untuk ditampilkan ke publik. |
| `detected_words` | `text` | Ya | `NULL` | — | Cast: `array` | JSON daftar kata terlarang yang terdeteksi. |
| `moderation_reason` | `text` | Ya | `NULL` | — | — | Alasan tindakan moderasi. |
| `moderated_at` | `datetime` | Ya | `NULL` | — | Cast: `datetime` | Waktu dilakukannya moderasi. |
| `moderated_by` | `bigint unsigned` | Ya | `NULL` | `MUL` (`ulasan_moderated_by_index`) | FK $\rightarrow$ `users.id` (`ON DELETE SET NULL`) | ID Admin yang memoderasi secara manual. |

---

### 2.6 Tabel `produk_views_logs`
- **Model:** `App\Models\ProdukViewLog` (`app/Models/ProdukViewLog.php`)
- **Deskripsi:** Mencatat setiap kejadian kunjungan halaman detail (`view`) dan klik tombol pemesanan WhatsApp (`whatsapp`) beserta stempel waktunya untuk analitik rentang tanggal.

| Nama Kolom | Tipe Data MySQL | Nullable | Default | Key / Indeks | Relasi / Constraint | Keterangan |
| :--- | :--- | :---: | :--- | :--- | :--- | :--- |
| `id` | `bigint unsigned` | Tidak | `AUTO_INCREMENT` | `PRI` | PK | Identitas unik log trafik. |
| `produk_id` | `bigint unsigned` | Tidak | — | `MUL` (`produk_views_logs_produk_id_created_at_index`) | FK $\rightarrow$ `produk.id` (`ON DELETE CASCADE`) | Produk yang dilihat atau diklik tombol WhatsApp-nya. |
| `type` | `varchar(255)` | Tidak | `'view'` | `MUL` (`produk_views_logs_type_created_at_index`) | Nilai: `'view'` atau `'whatsapp'` | Jenis interaksi pengunjung. |
| `created_at` | `datetime` | Ya | `NULL` | Bagian dari indeks komposit | — | Stempel waktu interaksi (dipakai filter laporan harian/bulanan). |
| `updated_at` | `datetime` | Ya | `NULL` | — | — | Stempel waktu pembaruan baris. |

---

## 3. Tabel Moderasi, Notifikasi, & Token API

### 3.1 Tabel `bad_words`
- **Model:** `App\Models\BadWord` (`app/Models/BadWord.php`)

| Nama Kolom | Tipe Data MySQL | Nullable | Default | Key / Indeks | Keterangan |
| :--- | :--- | :---: | :--- | :--- | :--- |
| `id` | `bigint unsigned` | Tidak | `AUTO_INCREMENT` | `PRI` | PK. |
| `word` | `varchar(255)` | Tidak | — | `UNI` (`bad_words_word_unique`) | Kata terlarang (disimpan huruf kecil). |
| `is_active` | `tinyint(1)` | Tidak | `1` | — | Status aktif kata dalam filter (`1` = aktif). |
| `created_at` | `datetime` | Ya | `NULL` | — | Waktu pembuatan. |
| `updated_at` | `datetime` | Ya | `NULL` | — | Waktu pembaruan. |
| `language` | `varchar(255)` | Tidak | `'indonesian'` | — | Bahasa kata (`indonesian`, `english`, `local`). |
| `severity` | `varchar(255)` | Tidak | `'medium'` | — | Tingkat keparahan (`low`, `medium`, `high`, `critical`). |
| `category` | `varchar(255)` | Ya | `NULL` | — | Kategori kata kasar (`profanity`, `insult`, `spam`, dll.). |
| `type` | `varchar(255)` | Tidak | `'custom'` | — | Asal entri (`default` atau `custom`). |

### 3.2 Tabel `whitelists`
- **Model:** `App\Models\Whitelist` (`app/Models/Whitelist.php`)

| Nama Kolom | Tipe Data MySQL | Nullable | Default | Key / Indeks | Keterangan |
| :--- | :--- | :---: | :--- | :--- | :--- |
| `id` | `bigint unsigned` | Tidak | `AUTO_INCREMENT` | `PRI` | PK. |
| `phrase` | `varchar(255)` | Tidak | — | `UNI` (`whitelists_phrase_unique`) | Kata/frasa aman yang dikecualikan dari deteksi *false-positive*. |
| `reason` | `varchar(255)` | Ya | `NULL` | — | Alasan pengecualian frasa. |
| `is_active` | `tinyint(1)` | Tidak | `1` | — | Status aktif (`1` = aktif). |
| `created_at` | `datetime` | Ya | `NULL` | — | Waktu pembuatan. |
| `updated_at` | `datetime` | Ya | `NULL` | — | Waktu pembaruan. |

### 3.3 Tabel `comment_moderation_logs`
- **Model:** `App\Models\CommentModerationLog` (`app/Models/CommentModerationLog.php`)

| Nama Kolom | Tipe Data MySQL | Nullable | Default | Key / Indeks | Relasi / Constraint | Keterangan |
| :--- | :--- | :---: | :--- | :--- | :--- | :--- |
| `id` | `bigint unsigned` | Tidak | `AUTO_INCREMENT` | `PRI` | PK | Identitas unik log moderasi. |
| `ulasan_id` | `bigint unsigned` | Tidak | — | `MUL` | FK $\rightarrow$ `ulasan.id` (`ON DELETE CASCADE`) | Ulasan yang dimoderasi. |
| `admin_id` | `bigint unsigned` | Ya | `NULL` | `MUL` | FK $\rightarrow$ `users.id` (`ON DELETE SET NULL`) | Admin yang mengeksekusi aksi. |
| `action` | `varchar(255)` | Tidak | — | — | — | Jenis aksi (`approve`, `reject`, `censor`, dll.). |
| `original_status` | `varchar(255)` | Ya | `NULL` | — | — | Status moderasi sebelum aksi. |
| `new_status` | `varchar(255)` | Tidak | — | — | — | Status moderasi sesudah aksi. |
| `created_at` | `datetime` | Ya | `NULL` | — | — | Waktu pencatatan log. |
| `updated_at` | `datetime` | Ya | `NULL` | — | — | Waktu pembaruan log. |

### 3.4 Tabel `notifications`
- **Model:** `Illuminate\Notifications\DatabaseNotification` (melalui trait `Notifiable` pada `App\Models\User`)

| Nama Kolom | Tipe Data MySQL | Nullable | Default | Key / Indeks | Keterangan |
| :--- | :--- | :---: | :--- | :--- | :--- |
| `id` | `char(36)` | Tidak | — | `PRI` | UUID unik notifikasi. |
| `type` | `varchar(255)` | Tidak | — | — | Nama kelas notifikasi (`App\Notifications\AdminActivityNotification`). |
| `notifiable_type` | `varchar(255)` | Tidak | — | `MUL` (`notifications_notifiable_type_notifiable_id_index`) | Model penerima (`App\Models\User`). |
| `notifiable_id` | `bigint unsigned` | Tidak | — | Bagian indeks komposit | ID akun penerima. |
| `data` | `text` | Tidak | — | — | Payload JSON berisi `title`, `message`, `category`, `action_url`, `meta`. |
| `read_at` | `timestamp` | Ya | `NULL` | — | Waktu notifikasi dibaca (`NULL` jika belum dibaca). |
| `created_at` | `timestamp` | Ya | `NULL` | — | Waktu pembuatan notifikasi. |
| `updated_at` | `timestamp` | Ya | `NULL` | — | Waktu pembaruan notifikasi. |

### 3.5 Tabel `personal_access_tokens`
- **Model:** `Laravel\Sanctum\PersonalAccessToken`

| Nama Kolom | Tipe Data MySQL | Nullable | Default | Key / Indeks | Keterangan |
| :--- | :--- | :---: | :--- | :--- | :--- |
| `id` | `bigint unsigned` | Tidak | `AUTO_INCREMENT` | `PRI` | PK. |
| `tokenable_type` | `varchar(255)` | Tidak | — | `MUL` (`personal_access_tokens_tokenable_type_tokenable_id_index`) | Model pemilik token (`App\Models\User`). |
| `tokenable_id` | `bigint unsigned` | Tidak | — | Bagian indeks komposit | ID pengguna pemilik token. |
| `name` | `text` | Tidak | — | — | Nama perangkat/sesi token (default `'api-token'`). |
| `token` | `varchar(64)` | Tidak | — | `UNI` (`personal_access_tokens_token_unique`) | Hash SHA-256 dari token Bearer. |
| `abilities` | `text` | Ya | `NULL` | — | Daftar izin token (JSON). |
| `last_used_at` | `timestamp` | Ya | `NULL` | — | Waktu terakhir token dipakai mengakses API. |
| `expires_at` | `timestamp` | Ya | `NULL` | `MUL` (`personal_access_tokens_expires_at_index`) | Waktu kedaluwarsa token. |
| `created_at` | `timestamp` | Ya | `NULL` | — | Waktu penerbitan token. |
| `updated_at` | `timestamp` | Ya | `NULL` | — | Waktu pembaruan token. |

---

## 4. Tabel Infrastruktur Framework Laravel

| Nama Tabel | Kolom Utama & Tipe | Keterangan Fungsi |
| :--- | :--- | :--- |
| `password_reset_tokens` | `email` (`varchar(255)` PK), `token` (`varchar(255)`), `created_at` (`timestamp nullable`) | Menyimpan token hash pengaturan ulang kata sandi. |
| `sessions` | `id` (`varchar(255)` PK), `user_id` (`bigint unsigned nullable` IX), `ip_address` (`varchar(45) nullable`), `user_agent` (`text nullable`), `payload` (`longtext`), `last_activity` (`int` IX) | Menyimpan data sesi web karena `SESSION_DRIVER=database`. |
| `cache` | `key` (`varchar(255)` PK), `value` (`mediumtext`), `expiration` (`int` IX) | Penyimpanan cache aplikasi (termasuk kamus `CommentModerationService`). |
| `cache_locks` | `key` (`varchar(255)` PK), `owner` (`varchar(255)`), `expiration` (`int` IX) | Menyimpan kunci konkuren untuk operasi cache. |
| `jobs` | `id` (`bigint unsigned` PK AI), `queue` (`varchar(255)` IX), `payload` (`longtext`), `attempts` (`tinyint unsigned`), `reserved_at` (`int unsigned nullable`), `available_at` (`int unsigned`), `created_at` (`int unsigned`) | Antrean pekerjaan asinkron jika `QUEUE_CONNECTION=database`. |
| `job_batches` | `id` (`varchar(255)` PK), `name`, `total_jobs`, `pending_jobs`, `failed_jobs`, `failed_job_ids`, `options`, `cancelled_at`, `created_at`, `finished_at` | Pencatatan eksekusi job secara berkelompok (*batch*). |
| `failed_jobs` | `id` (`bigint unsigned` PK AI), `uuid` (`varchar(255)` UK), `connection`, `queue`, `payload`, `exception`, `failed_at` (`timestamp`) | Menyimpan detail job yang gagal dieksekusi. |
| `migrations` | `id` (`int unsigned` PK AI), `migration` (`varchar(255)`), `batch` (`int`) | Mencatat seluruh 27 berkas migrasi yang telah dijalankan (Batch 1–9). |

---

## 5. Rujukan Kode

- `database/migrations/0001_01_01_000000_create_users_table.php` s.d. `database/migrations/2026_10_15_000005_add_google_id_to_users_table.php`
- `app/Models/User.php`, `app/Models/Penjual.php`, `app/Models/Kategori.php`, `app/Models/Produk.php`, `app/Models/Ulasan.php`, `app/Models/ProdukViewLog.php`, `app/Models/BadWord.php`, `app/Models/Whitelist.php`, `app/Models/CommentModerationLog.php`
