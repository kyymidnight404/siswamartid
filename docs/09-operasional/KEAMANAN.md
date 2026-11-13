# Dokumentasi Keamanan Sistem — SiswaMart

| Informasi Dokumen | Detail |
| :--- | :--- |
| **Judul** | Kontrol Keamanan Terverifikasi, Analisis Risiko, dan Rekomendasi SiswaMart |
| **Versi** | `1.1.0` |
| **Tanggal** | 27 Oktober 2026 |
| **Status** | Final (Source-Code Backed) |
| **Penulis** | Tim Dokumentasi Teknis & Rekayasa Perangkat Lunak |
| **Sumber Data** | `bootstrap/app.php`, `app/Http/Middleware/`, `app/Http/Requests/`, `app/Http/Controllers/Auth/`, `app/Services/CommentModerationService.php`, `app/Exports/Concerns/StylesSheet.php` |

---

## Daftar Isi

1. [Ringkasan Kontrol Keamanan yang Terverifikasi di Kode](#1-ringkasan-kontrol-keamanan-yang-terverifikasi-di-kode)
2. [Autentikasi, Hashing, dan Manajemen Sesi/Token](#2-autentikasi-hashing-dan-manajemen-sesitoken)
3. [Otorisasi Berbasis Peran (RBAC) dan Pencabutan Akses Seketika](#3-otorisasi-berbasis-peran-rbac-dan-pencabutan-akses-seketika)
4. [Keamanan Integrasi Google OAuth 2.0](#4-keamanan-integrasi-google-oauth-20)
5. [Pembatasan Laju Permintaan (Rate Limiting / Anti-Brute Force & Anti-Spam)](#5-pembatasan-laju-permintaan-rate-limiting--anti-brute-force--anti-spam)
6. [Perlindungan Injeksi (SQLi, XSS, CSRF, dan Formula Injection Excel)](#6-perlindungan-injeksi-sqli-xss-csrf-dan-formula-injection-excel)
7. [Keamanan Unggahan Berkas dan Moderasi Konten](#7-keamanan-unggahan-berkas-dan-moderasi-konten)
8. [Daftar Risiko Keamanan (`RSK-01` s.d. `RSK-06`) & Rekomendasi Mitigasi](#8-daftar-risiko-keamanan-rsk-01-sd-rsk-06--rekomendasi-mitigasi)
9. [Rujukan Kode](#9-rujukan-kode)

---

## 1. Ringkasan Kontrol Keamanan yang Terverifikasi di Kode

Setiap klaim kontrol keamanan pada tabel berikut telah diverifikasi langsung pada baris kode sumber aplikasi:

| Domain Keamanan | Mekanisme yang Diterapkan | Status | Rujukan Kode |
| :--- | :--- | :---: | :--- |
| **Hashing Kata Sandi** | Bcrypt otomatis melalui Eloquent cast `'password' => 'hashed'` | `[Implemented]` | `app/Models/User.php#L47` |
| **Otorisasi Admin Web** | Middleware `EnsureAdmin` (`role === 'admin'` & `is_active == true`) | `[Implemented]` | `app/Http/Middleware/EnsureAdmin.php#L16-L31` |
| **Otorisasi Penjual Web** | Middleware `EnsurePenjual` (`role === 'penjual'` & `is_active == true`) | `[Implemented]` | `app/Http/Middleware/EnsurePenjual.php#L16-L31` |
| **Pencabutan Token API Aktif** | Middleware `EnsureUserIsActive` menghapus seluruh token Sanctum jika `is_active == false` | `[Implemented]` | `app/Http/Middleware/EnsureUserIsActive.php#L16-L23` |
| **Anti-Enumerasi Reset Password** | Respons identik untuk email terdaftar maupun tidak terdaftar | `[Implemented]` | `app/Http/Controllers/Auth/PasswordResetLinkController.php#L36-L44` |
| **Proteksi Akun Google OAuth** | Cek wajib `email_verified`, tolak akun baru, dan tolak konflik `google_id` | `[Implemented]` | `app/Http/Controllers/Auth/GoogleAuthController.php#L56-L94` |
| **Sanitasi Formula Excel** | `StylesSheet::sanitizeText()` menambahkan `'` pada teks berawalan `=`, `+`, `-`, `@` | `[Implemented]` | `app/Exports/Concerns/StylesSheet.php#L230-L245` |
| **Moderasi Kata Kasar Otomatis** | `CommentModerationService` (normalisasi leetspeak + regex batas kata) | `[Implemented]` | `app/Services/CommentModerationService.php#L43-L280` |
| **Isolasi Rute Preview Lokal** | Rute `/preview/reset-password-email` dibatasi `app()->environment('local')` | `[Implemented]` | `routes/web.php#L94-L106` |

---

## 2. Autentikasi, Hashing, dan Manajemen Sesi/Token

1. **Hashing Kata Sandi:**
   - Model `User` mendeklarasikan `'password' => 'hashed'` (`app/Models/User.php#L47`) dan `config/hashing.php` menggunakan algoritma **Bcrypt**. Tidak ada kata sandi yang disimpan dalam teks biasa (*plain text*).
2. **Regenerasi Sesi Anti-Session Fixation:**
   - Saat login berhasil (`AuthenticatedSessionController@store#L30` dan `GoogleAuthController@callback#L106`), sistem selalu memanggil `$request->session()->regenerate()`.
   - Saat logout (`AuthenticatedSessionController@destroy#L46-L47`), sesi di-`invalidate()` dan token CSRF di-`regenerateToken()`.
3. **Token API Laravel Sanctum:**
   - Endpoint API menggunakan token personal (`personal_access_tokens`) yang di-hash dengan SHA-256 di database.

---

## 3. Otorisasi Berbasis Peran (RBAC) dan Pencabutan Akses Seketika

Sistem memisahkan hak akses secara ketat antara `admin` dan `penjual`:
- **Pemeriksaan Kepemilikan Sumber Daya (*Object-Level Authorization*):**
  - Pada `Penjual\ProdukController` (`#L110`, `#L130`, `#L193`) dan `Api\SellerProductController` (`#L101`, `#L167`), sistem memeriksa `$produk->penjual_id !== $penjual->id` dan segera melempar `abort(403)` jika penjual mencoba memodifikasi produk milik toko lain.
  - Pada `Api\ReviewController@destroy` (`#L100-L106`), penghapusan ulasan hanya diizinkan bagi `admin` atau `penjual` pemilik produk yang diulas.
- **Pencabutan Akses Seketika (*Immediate Access Revocation*):**
  - Apabila Admin menonaktifkan akun (`is_active = false`), baik sesi Web (`EnsureAdmin`, `EnsurePenjual`) maupun seluruh token Bearer Sanctum (`EnsureUserIsActive`) langsung dihancurkan pada permintaan berikutnya.

---

## 4. Keamanan Integrasi Google OAuth 2.0

Kontrol keamanan berlapis pada `app/Http/Controllers/Auth/GoogleAuthController.php`:
1. **Penolakan Email Belum Terverifikasi (`#L56-L63`):** Memeriksa klaim `email_verified` / `verified_email` pada payload OAuth Google dan menolak login jika bernilai `false`.
2. **Penolakan Pembuatan Akun Otomatis (`#L75-L79`):** Jika email Google tidak ditemukan di tabel `users`, login langsung ditolak.
3. **Pencegahan Pengambilalihan Akun / *Account Takeover* (`#L81-L87`):** Jika akun di database sudah terikat ke `google_id` tertentu dan ada upaya masuk dengan `google_id` berbeda, sistem menolak akses dengan pesan peringatan.

---

## 5. Pembatasan Laju Permintaan (Rate Limiting / Anti-Brute Force & Anti-Spam)

Daftar seluruh pembatasan laju (*rate limiter*) yang aktif di kode sumber:

| Rute / Fitur | Konfigurasi Throttle | Lokasi Kode | Tujuan Keamanan |
| :--- | :--- | :--- | :--- |
| `POST /login` (Web) | `RateLimiter::tooManyAttempts` (maks **5x gagal** per `login\|IP`) | `app/Http/Requests/Auth/LoginRequest.php#L82-L101` | Mencegah serangan *brute-force* kata sandi. |
| `POST /api/login` & `/api/register` | `throttle:5,1` (**5 req/menit**) + `RateLimiter` 5x gagal | `routes/api.php#L19-L20`, `Api\AuthController.php#L47` | Mencegah *credential stuffing* pada endpoint API. |
| `POST /forgot-password` & `/reset-password` | `throttle:5,1` (**5 req/menit**) | `routes/auth.php#L31`, `#L39` | Mencegah banjir pengiriman email reset password. |
| `POST /produk/{produk:slug}/ulasan` (Web) | `throttle:3,1` (**3 req/menit** per IP) | `routes/web.php#L21-L23` | Mencegah spam ulasan tamu di halaman produk. |
| `POST /api/products/{produk:slug}/reviews` | `throttle:10,1` (**10 req/menit** per IP) | `routes/api.php#L31-L32` | Mencegah spam ulasan melalui API. |
| `GET /produk/{produk:slug}/whatsapp` | `throttle:30,1` (**30 req/menit** per IP) | `routes/web.php#L18-L20` | Mencegah manipulasi/bot penghitung klik WhatsApp. |
| `GET /produk/{produk:slug}` | `throttle:60,1` (**60 req/menit** per IP) | `routes/web.php#L15-L17` | Mencegah inflasi palsu penghitung `views` produk. |

---

## 6. Perlindungan Injeksi (SQLi, XSS, CSRF, dan Formula Injection Excel)

1. **SQL Injection (SQLi):** Seluruh kueri menggunakan Eloquent ORM dan *parameter binding* PDO (`where`, `whereHas`). Tidak ada interpolasi input mentah pengguna ke dalam SQL.
2. **Cross-Site Scripting (XSS):** Seluruh template Blade di `resources/views/` merender variabel menggunakan sintaks ter-escape `{{ ... }}`.
3. **Cross-Site Request Forgery (CSRF):** Seluruh rute mutasi Web (`POST`, `PUT`, `PATCH`, `DELETE`) berada di dalam grup middleware `web` yang memverifikasi `@csrf` token secara otomatis.
4. **CSV / Excel Formula Injection:** Metode `StylesSheet::sanitizeText()` (`app/Exports/Concerns/StylesSheet.php#L230-L245`) menghapus karakter kontrol (`\x00-\x08`, dll.) dan menyisipkan tanda kutip tunggal (`'`) di depan teks yang diawali `=`, `+`, `-`, atau `@` sebelum ditulis ke file `.xlsx`.

---

## 7. Keamanan Unggahan Berkas dan Moderasi Konten

1. **Validasi Unggahan Gambar:**
   - Foto produk (`foto_utama`) dan foto toko (`foto_toko`) divalidasi dengan aturan `image|mimes:jpeg,png,jpg,webp|max:2048` (maksimal **2 MB**).
   - Berkas disimpan dengan nama acak hasil hash Laravel (`store('produk', 'public')`), bukan nama berkas asli dari pengguna, sehingga mencegah *path traversal* dan eksekusi skrip berbahaya.
2. **Pembersihan Berkas Yatim (*Orphan File Cleanup*):**
   - Saat produk dihapus atau ajuan foto di `pending_changes` ditolak/diganti, sistem menghapus berkas gambar yang tidak terpakai melalui `Storage::disk('public')->delete(...)` (`Admin\ProdukController.php#L165-L185`).

---

## 8. Daftar Risiko Keamanan (`RSK-01` s.d. `RSK-06`) & Rekomendasi Mitigasi

Selaras dengan bagian Risiko pada `docs/01-prd/PRD.md`:

| ID Risiko | Deskripsi Risiko | Tingkat Risiko | Mitigasi Aktual & Tindakan yang Wajib Dilakukan |
| :--- | :--- | :---: | :--- |
| **RSK-01** | **Keberadaan `public/deploy-helper.php` di repositori:** Berkas ini mengizinkan eksekusi `migrate`, `storage:link`, dan `optimize:clear` melalui HTTP dengan kunci statis di dalam kode. | **Tinggi** *(jika ikut ter-deploy)* | **Wajib dihapus** dari folder `public/` / `public_html/` pada server produksi segera setelah instalasi selesai (lihat `DEPLOYMENT.md` Bagian 4). |
| **RSK-02** | **Penghitung `views` bertambah setiap muat ulang halaman (`F5`):** Meskipun sudah dibatasi `throttle:60,1`, satu pengunjung masih dapat menambah `views` hingga 60 kali per menit. | **Rendah** | Mitigasi saat ini: `throttle:60,1`. Rekomendasi fase berikutnya: lakukan *deduplication* berbasis sesi atau hash IP per produk per jam. |
| **RSK-03** | **Penggunaan `VerifyCsrfToken::except(['*'])` di satu berkas test:** Pada `AdminAndPenjualFilteringAndPaginationTest.php#L26`, CSRF dinonaktifkan dalam `setUp()` untuk kemudahan pengujian rute form. | **Rendah** *(hanya di test)* | Hanya berlaku saat PHPUnit berjalan; di aplikasi nyata middleware CSRF tetap aktif penuh. |
| **RSK-04** | **Penyalahgunaan Ulasan Tamu Tanpa Login:** Pengunjung tidak perlu login untuk mengirim ulasan. | **Sedang** | Telah dimitigasi oleh `CommentModerationService` (filter 350+ kata kasar & leetspeak), `throttle:3,1`, dan fitur hapus ulasan di panel Admin. |
| **RSK-05** | **Kegagalan Verifikasi SSL Google OAuth di Windows Lokal:** Lingkungan pengembangan Windows terkadang tidak memiliki CA bundle terkonfigurasi. | **Rendah** | Gunakan variabel `GOOGLE_HTTP_VERIFY` yang menunjuk ke `cacert.pem` di lokal, dan pastikan tetap bernilai `true` di server produksi Linux (`config/services.php#L42`). |
| **RSK-06** | **Ketidaksinkronan Skema jika Migrasi Batch 9 Belum Dijalankan:** Server lama yang belum menjalankan 5 migrasi `2026_10_15_*` akan mengalami error kolom hilang. | **Sedang** | Selalu jalankan `php artisan migrate:status` dan `php artisan migrate --force` saat memperbarui server (lihat `MIGRASI.md`). |

---

## 9. Rujukan Kode

- `bootstrap/app.php#L18-L45`
- `app/Http/Middleware/EnsureAdmin.php#L1-L33`
- `app/Http/Middleware/EnsurePenjual.php#L1-L33`
- `app/Http/Middleware/EnsureUserIsActive.php#L1-L28`
- `app/Http/Requests/Auth/LoginRequest.php#L44-L108`
- `app/Http/Controllers/Auth/GoogleAuthController.php#L23-L133`
- `app/Http/Controllers/Auth/PasswordResetLinkController.php#L24-L45`
- `app/Services/CommentModerationService.php#L18-L280`
- `app/Exports/Concerns/StylesSheet.php#L230-L245`
- `public/deploy-helper.php#L1-L163`
