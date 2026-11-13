# Katalog Aturan Bisnis (Business Rules) — SiswaMart

| Informasi Dokumen | Detail |
| :--- | :--- |
| **Judul** | Katalog Aturan Bisnis (Business Rules) SiswaMart |
| **Versi** | `1.1.0` |
| **Tanggal** | 27 Oktober 2026 |
| **Status** | Final (Source-Code Backed) |
| **Penulis** | Tim Dokumentasi Teknis & Rekayasa Perangkat Lunak |
| **Sumber Data** | `app/Models/`, `app/Http/Controllers/`, `app/Http/Middleware/`, `app/Http/Requests/`, `app/Services/`, `app/Exports/` |

---

## Daftar Isi

1. [Ringkasan Indeks Aturan Bisnis](#1-ringkasan-indeks-aturan-bisnis)
2. [Aturan Domain Produk & Moderasi (`BR-PROD-01` s.d. `BR-PROD-05`)](#2-aturan-domain-produk--moderasi-br-prod-01-sd-br-prod-05)
3. [Aturan Domain Penjual & Operasional Toko (`BR-SELLER-01`, `BR-SELLER-02`, `BR-STORE-01`)](#3-aturan-domain-penjual--operasional-toko-br-seller-01-br-seller-02-br-store-01)
4. [Aturan Domain Ulasan & Moderasi Kata Kasar (`BR-REV-01` s.d. `BR-REV-03`)](#4-aturan-domain-ulasan--moderasi-kata-kasar-br-rev-01-sd-br-rev-03)
5. [Aturan Domain Autentikasi & Keamanan Akun (`BR-AUTH-01` s.d. `BR-AUTH-03`)](#5-aturan-domain-autentikasi--keamanan-akun-br-auth-01-sd-br-auth-03)
6. [Aturan Domain Kategori & Pelaporan (`BR-KAT-01`, `BR-REP-01`)](#6-aturan-domain-kategori--pelaporan-br-kat-01-br-rep-01)
7. [Rujukan Kode](#7-rujukan-kode)

---

## 1. Ringkasan Indeks Aturan Bisnis

Penomoran di bawah ini selaras sepenuhnya dengan `docs/01-prd/PRD.md` dan `docs/02-srs/SRS.md`:

| ID Aturan | Kode Alias PRD | Nama Aturan Bisnis | Domain | Status |
| :--- | :---: | :--- | :--- | :---: |
| **BR-PROD-01** | `BR-01` | Visibilitas Produk di Etalase Publik (`publicVisible`) | Produk | `[Implemented]` |
| **BR-PROD-02** | `BR-02` | Moderasi Wajib untuk Produk Baru (`pending_action = 'create'`) | Produk | `[Implemented]` |
| **BR-PROD-03** | `BR-03` | Preservasi Produk Aktif Saat Diedit (`pending_action = 'update'`) | Produk | `[Implemented]` |
| **BR-PROD-04** | `BR-04` | Persetujuan Admin untuk Penghapusan Produk (`pending_action = 'delete'`) | Produk | `[Implemented]` |
| **BR-PROD-05** | `BR-05` | Kewajiban Alasan Penolakan & Efek *Rollback* Saat Ajuan Ditolak | Produk | `[Implemented]` |
| **BR-SELLER-01** | `BR-06` | Proteksi Penghapusan Penjual yang Masih Memiliki Produk | Penjual | `[Implemented]` |
| **BR-SELLER-02** | `BR-07` | Format Wajib NISN 10 Digit Angka Unik & Relasi `1:1` Akun-Toko | Penjual | `[Implemented]` |
| **BR-STORE-01** | `BR-08` | Perilaku Tombol Pemesanan WhatsApp Berdasarkan Stok & Status Toko | Toko & WA | `[Implemented]` |
| **BR-REV-01** | `BR-09` | Penyaringan Kata Kasar Pra-Simpan (`CommentModerationService`) | Ulasan | `[Implemented]` |
| **BR-REV-02** | `BR-10` | Pengecualian Frasa Aman (*Whitelist*) Berprioritas Tinggi | Ulasan | `[Implemented]` |
| **BR-REV-03** | `BR-11` | Pembatasan Laju (*Rate Limiting*) Pengiriman Ulasan Tamu | Ulasan | `[Implemented]` |
| **BR-AUTH-01** | `BR-12` | Pencabutan Seketika Sesi Web & Token API Saat Akun Dinonaktifkan | Autentikasi | `[Implemented]` |
| **BR-AUTH-02** | `BR-13` | Pembatasan Google OAuth Hanya untuk Akun yang Sudah Terdaftar | Autentikasi | `[Implemented]` |
| **BR-AUTH-03** | `BR-14` | Registrasi Penjual Tertutup & Perlindungan Anti-Enumerasi Reset Password | Autentikasi | `[Implemented]` |
| **BR-KAT-01** | `BR-15` | Proteksi Penghapusan Kategori yang Masih Memiliki Produk | Kategori | `[Implemented]` |
| **BR-REP-01** | `BR-16` | Sanitasi Anti-Injeksi Formula pada Ekspor Laporan Excel (`.xlsx`) | Laporan | `[Implemented]` |

---

## 2. Aturan Domain Produk & Moderasi (`BR-PROD-01` s.d. `BR-PROD-05`)

### BR-PROD-01 (`BR-01`): Visibilitas Produk di Etalase Publik (`publicVisible`)
- **Deskripsi:** Sebuah produk **hanya boleh tampil** di halaman publik (Beranda, Katalog Kategori, Detail Produk, Hasil Pencarian, Endpoint `/api/products*`, dan Ulasan Publik) apabila memenuhi **dua syarat sekaligus**:
  1. `produk.status === 'aktif'`
  2. Akun pemilik toko berstatus aktif (`penjual.user.is_active === true`).
- **Dampak:** Jika Admin menonaktifkan akun seorang penjual (`is_active = false`), seluruh produk penjual tersebut otomatis disembunyikan dari publik tanpa harus mengubah `produk.status` satu per satu.
- **Rujukan Kode:**
  - `app/Models/Produk.php#L47-L53` (`scopePublicVisible`)
  - `app/Http/Controllers/Public/ProdukController.php#L17`, `#L75`
  - `app/Http/Controllers/Api/ProductController.php#L17`, `#L71`

### BR-PROD-02 (`BR-02`): Moderasi Wajib untuk Produk Baru (`pending_action = 'create'`)
- **Deskripsi:** Setiap produk baru yang ditambahkan oleh Penjual tidak langsung tampil di publik, melainkan wajib disimpan dengan `status = 'pending'`, `pending_action = 'create'`, dan `pending_changes = null`, serta memicu notifikasi ke Admin.
- **Validasi Input:**
  - `nama`: wajib, string, maks 255 karakter.
  - `kategori_id`: wajib, harus ada di tabel `kategori`.
  - `deskripsi`: wajib, string.
  - `harga`: wajib, numerik, minimal `0`.
  - `stok`: wajib, salah satu dari `'tersedia'`, `'po'`, atau `'habis'`.
  - `label_promosi`: opsional, string, maks 50 karakter.
  - `foto_utama`: wajib saat buat baru di Web (`image|mimes:jpeg,png,jpg,webp|max:2048` / 2 MB).
- **Rujukan Kode:**
  - `app/Http/Controllers/Penjual/ProdukController.php#L61-L103`
  - `app/Http/Controllers/Api/SellerProductController.php#L56-L95`

### BR-PROD-03 (`BR-03`): Preservasi Produk Aktif Saat Diedit (`pending_action = 'update'`)
- **Deskripsi:** Apabila Penjual mengedit produk yang saat itu berstatus `aktif`:
  1. Kolom utama produk (`nama`, `kategori_id`, `deskripsi`, `harga`, `stok`, `label_promosi`, `foto_utama`) **dilarang ditimpa langsung**, dan `produk.status` **tetap `'aktif'`**.
  2. Nilai-nilai baru (termasuk path berkas gambar baru yang diunggah) disimpan di dalam kolom JSON `pending_changes`, dengan `pending_action = 'update'` dan `alasan_ditolak = null`.
  3. Selama menunggu keputusan Admin, pengunjung publik tetap melihat data produk versi terakhir yang telah disetujui, sedangkan Admin melihat tabel komparasi *diff* nilai lama vs nilai baru.
- **Catatan Produk Non-Aktif:** Jika produk yang diedit berstatus `pending`, `ditolak`, atau `draft`, perubahan langsung menimpa kolom utama dan status diatur ke `status = 'pending'`, `pending_action = 'create'`.
- **Rujukan Kode:**
  - `app/Http/Controllers/Penjual/ProdukController.php#L140-L181`
  - `app/Http/Controllers/Api/SellerProductController.php#L118-L157`
  - `app/Models/Produk.php#L67-L85` (`getPendingChange`)

### BR-PROD-04 (`BR-04`): Persetujuan Admin untuk Penghapusan Produk (`pending_action = 'delete'`)
- **Deskripsi:** Penjual tidak dapat menghapus baris produk secara langsung dari database. Saat Penjual menekan tombol hapus, sistem menyetel `pending_action = 'delete'` dan mengirim notifikasi ke Admin. Produk baru benar-benar dihapus dari database dan *storage* setelah Admin menekan **Setujui (`Approve`)** pada ajuan hapus tersebut.
- **Pembatasan:** Jika produk sedang memiliki ajuan moderasi yang belum selesai (`hasPendingModerationRequest() == true`), pengajuan hapus baru ditolak sampai ajuan sebelumnya diputuskan Admin.
- **Rujukan Kode:**
  - `app/Http/Controllers/Penjual/ProdukController.php#L191-L219`
  - `app/Http/Controllers/Admin/ProdukController.php#L76-L86`

### BR-PROD-05 (`BR-05`): Kewajiban Alasan Penolakan & Efek *Rollback* Saat Ajuan Ditolak
- **Deskripsi:**
  1. Saat Admin menolak ajuan moderasi (`status = 'ditolak'`), field `alasan_ditolak` **wajib diisi** (`required_if:status,ditolak|string|max:500`).
  2. Jika ajuan yang ditolak adalah revisi (`pending_action = 'update'`) atau penghapusan (`pending_action = 'delete'`) atas produk yang sudah `aktif`, maka foto sementara di `pending_changes` dihapus, `pending_action` dan `pending_changes` dikosongkan (`null`), `alasan_ditolak` disimpan untuk dibaca penjual, namun **`produk.status` tetap dipertahankan `'aktif'`** agar produk lama tidak hilang dari etalase.
  3. Jika ajuan yang ditolak adalah produk baru (`pending_action = 'create'`), maka `produk.status` diubah menjadi `'ditolak'`.
- **Rujukan Kode:**
  - `app/Http/Controllers/Admin/ProdukController.php#L61-L163`
  - `app/Http/Controllers/Api/AdminModerationController.php#L47-L166`

---

## 3. Aturan Domain Penjual & Operasional Toko (`BR-SELLER-01`, `BR-SELLER-02`, `BR-STORE-01`)

### BR-SELLER-01 (`BR-06`): Proteksi Penghapusan Penjual yang Masih Memiliki Produk
- **Deskripsi:** Admin dilarang menghapus akun Penjual (`DELETE /admin/penjual/{penjual}`) apabila penjual tersebut masih memiliki minimal 1 produk terdaftar (`$penjual->produk()->count() > 0`). Untuk menyembunyikan toko yang sudah memiliki produk, Admin harus menggunakan fitur **Nonaktifkan Akun (`toggle`)**.
- **Rujukan Kode:** `app/Http/Controllers/Admin/PenjualController.php#L202-L210`.

### BR-SELLER-02 (`BR-07`): Format Wajib NISN 10 Digit Angka Unik & Relasi `1:1` Akun-Toko
- **Deskripsi:**
  1. Setiap penjual yang didaftarkan oleh Admin wajib memiliki `nisn` tepat **10 digit angka** (`required|string|size:10|regex:/^\d{10}$/|unique:penjual,nisn`).
  2. Setiap akun `users` hanya boleh memiliki maksimal 1 profil `penjual` (ditegakkan oleh indeks unik `penjual_user_id_unique` di database).
- **Rujukan Kode:**
  - `app/Http/Controllers/Admin/PenjualController.php#L90-L103`, `#L147-L160`
  - `database/migrations/2026_10_15_000001_align_local_schema_with_erd.php#L98-L100`

### BR-STORE-01 (`BR-08`): Perilaku Tombol Pemesanan WhatsApp Berdasarkan Stok & Status Toko
- **Deskripsi:**
  1. Jika `produk.stok === 'habis'`, tombol WhatsApp dinonaktifkan di antarmuka dan akses langsung ke `/produk/{slug}/whatsapp` diblokir dengan pesan error.
  2. Nomor WhatsApp penjual dinormalisasi dari awalan lokal `08...` menjadi format internasional `628...` (`Public\ProdukController@trackWhatsapp#L88-L93`).
  3. Jika `penjual.status_toko === 'buka'`, pesan otomatis WhatsApp berbunyi: `"Halo Kak, saya tertarik dengan produk *{nama}* ({harga}) di SiswaMart. Apakah stoknya masih tersedia?"`.
  4. Jika `penjual.status_toko === 'tutup'`, pemesanan tetap diizinkan sebagai konfirmasi *pre-order*/tanya stok dengan pesan khusus: `"Halo Kak, saya melihat produk *{nama}* ({harga}) di SiswaMart. Saya lihat status toko sedang tutup, apakah saya masih bisa memesan atau kapan toko buka kembali?"`.
- **Rujukan Kode:** `app/Http/Controllers/Public/ProdukController.php#L73-L120`.

---

## 4. Aturan Domain Ulasan & Moderasi Kata Kasar (`BR-REV-01` s.d. `BR-REV-03`)

### BR-REV-01 (`BR-09`): Penyaringan Kata Kasar Pra-Simpan (`CommentModerationService`)
- **Deskripsi:** Setiap ulasan tamu wajib melewati pemeriksaan `CommentModerationService::moderateComment()` pada field `guest_username` dan `komentar` **sebelum** disimpan ke database:
  1. Menggabungkan kamus statis `resources/data/badwords-list.json` (350 kata) dan tabel `bad_words` (`is_active = true`), di-cache selama 24 jam.
  2. Menormalisasi karakter *leetspeak* (`0->o`, `1->i`, `3->e`, `4->a`, `5->s`, `7->t`, `@->a`, `$->s`) serta mereduksi huruf berulang (`>2` kali berurutan menjadi 1 huruf).
  3. Mencocokkan kata terlarang menggunakan ekspresi reguler batas kata (`\b`). Jika terdeteksi minimal 1 kata kasar, penyimpanan dibatalkan dan sistem mengembalikan HTTP `422` / redirect back dengan pesan kesalahan.
- **Rujukan Kode:**
  - `app/Services/CommentModerationService.php#L43-L280`
  - `app/Http/Controllers/Public/UlasanController.php#L27-L44`
  - `app/Http/Controllers/Api/ReviewController.php#L49-L64`

### BR-REV-02 (`BR-10`): Pengecualian Frasa Aman (*Whitelist*) Berprioritas Tinggi
- **Deskripsi:** Sebelum teks diperiksa terhadap daftar kata kasar, seluruh frasa aktif di tabel `whitelists` (`is_active = true`) dihapus/dikecualikan sementara dari teks uji (`removeWhitelistedPhrases`) agar kata umum yang kebetulan mirip potongan kata kasar tidak terkena *false positive*.
- **Rujukan Kode:** `app/Services/CommentModerationService.php#L186-L198`.

### BR-REV-03 (`BR-11`): Pembatasan Laju (*Rate Limiting*) Pengiriman Ulasan Tamu
- **Deskripsi:** Pengiriman ulasan tamu dibatasi maksimal **3 kali per menit per IP** pada rute Web (`POST /produk/{produk:slug}/ulasan`, `throttle:3,1`) dan **10 kali per menit per IP** pada rute API (`POST /api/products/{produk:slug}/reviews`, `throttle:10,1`).
- **Rujukan Kode:** `routes/web.php#L21-L23`, `routes/api.php#L31-L32`.

---

## 5. Aturan Domain Autentikasi & Keamanan Akun (`BR-AUTH-01` s.d. `BR-AUTH-03`)

### BR-AUTH-01 (`BR-12`): Pencabutan Seketika Sesi Web & Token API Saat Akun Dinonaktifkan
- **Deskripsi:**
  1. Pengguna dengan `users.is_active == false` ditolak saat mencoba login (Web, API, maupun Google OAuth).
  2. Jika akun dinonaktifkan oleh Admin ketika pengguna sedang login:
     - Pada **Web**: middleware `EnsureAdmin` dan `EnsurePenjual` langsung memanggil `Auth::guard('web')->logout()`, `invalidate()` sesi, dan mengarahkan pengguna ke halaman `/login`.
     - Pada **API**: middleware `EnsureUserIsActive` (`active`) langsung menghapus seluruh token Sanctum pengguna (`$user->tokens()->delete()`) dan mengembalikan HTTP `403 Forbidden`.
- **Rujukan Kode:**
  - `app/Http/Middleware/EnsurePenjual.php#L18-L25`
  - `app/Http/Middleware/EnsureAdmin.php#L18-L25`
  - `app/Http/Middleware/EnsureUserIsActive.php#L16-L23`

### BR-AUTH-02 (`BR-13`): Pembatasan Google OAuth Hanya untuk Akun yang Sudah Terdaftar
- **Deskripsi:** Login Google OAuth (`GoogleAuthController@callback`) **tidak pernah membuat akun baru**. Sistem hanya mengizinkan login apabila:
  1. Klaim `email_verified` dari Google bernilai `true`.
  2. Ditemukan baris di `users` dengan `google_id` yang sama atau `email` yang sama (dan belum ditautkan ke `google_id` milik orang lain).
- **Rujukan Kode:** `app/Http/Controllers/Auth/GoogleAuthController.php#L56-L94`.

### BR-AUTH-03 (`BR-14`): Registrasi Penjual Tertutup & Perlindungan Anti-Enumerasi Reset Password
- **Deskripsi:**
  1. Tidak ada fitur pendaftaran akun mandiri untuk publik (`POST /api/register` selalu mengembalikan HTTP `403 Forbidden`).
  2. Formulir Lupa Kata Sandi (`POST /forgot-password`, `throttle:5,1`) selalu menampilkan pesan sukses generik yang identik baik ketika email ditemukan maupun tidak ditemukan di database.
- **Rujukan Kode:**
  - `app/Http/Controllers/Api/AuthController.php#L34-L40`
  - `app/Http/Controllers/Auth/PasswordResetLinkController.php#L36-L44`

---

## 6. Aturan Domain Kategori & Pelaporan (`BR-KAT-01`, `BR-REP-01`)

### BR-KAT-01 (`BR-15`): Proteksi Penghapusan Kategori yang Masih Memiliki Produk
- **Deskripsi:** Kategori tidak dapat dihapus oleh Admin jika masih memiliki produk terkait (`$kategori->produk()->count() > 0`), baik dicek di level kontroler (`Admin\KategoriController@destroy`) maupun ditegakkan oleh constraint FK database `produk_ibfk_2` (`ON DELETE NO ACTION / RESTRICT`).
- **Rujukan Kode:** `app/Http/Controllers/Admin/KategoriController.php#L68-L72`, `database/migrations/2026_08_21_025057_create_produk_table.php#L17`.

### BR-REP-01 (`BR-16`): Sanitasi Anti-Injeksi Formula pada Ekspor Laporan Excel (`.xlsx`)
- **Deskripsi:** Seluruh nilai teks dinamis (nama produk, nama toko, deskripsi, kategori) yang ditulis ke dalam sel Excel melalui trait `StylesSheet::sanitizeText()` dibersihkan dari karakter kontrol ASCII dan secara otomatis diberi prefiks tanda kutip tunggal (`'`) apabila diawali karakter pemicu formula spreadsheet (`=`, `+`, `-`, `@`).
- **Rujukan Kode:** `app/Exports/Concerns/StylesSheet.php#L230-L245`.

---

## 7. Rujukan Kode

- `app/Models/Produk.php#L47-L86`
- `app/Http/Controllers/Penjual/ProdukController.php#L61-L246`
- `app/Http/Controllers/Admin/ProdukController.php#L61-L163`
- `app/Http/Controllers/Admin/PenjualController.php#L90-L215`
- `app/Http/Controllers/Admin/KategoriController.php#L66-L78`
- `app/Http/Controllers/Public/ProdukController.php#L15-L120`
- `app/Http/Controllers/Public/UlasanController.php#L18-L62`
- `app/Services/CommentModerationService.php#L18-L280`
- `app/Http/Middleware/EnsureUserIsActive.php#L12-L26`
- `app/Http/Controllers/Auth/GoogleAuthController.php#L47-L116`
- `app/Exports/Concerns/StylesSheet.php#L230-L245`
