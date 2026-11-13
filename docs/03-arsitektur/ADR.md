# Architecture Decision Records (ADR) — SiswaMart

| Informasi Dokumen | Detail |
| :--- | :--- |
| **Judul** | Architecture Decision Records (ADR) SiswaMart |
| **Versi** | `1.1.0` |
| **Tanggal** | 27 Oktober 2026 |
| **Status** | Final (Source-Code Backed) |
| **Penulis** | Tim Dokumentasi Teknis & Rekayasa Perangkat Lunak |
| **Sumber Data** | Kode sumber `app/`, `routes/`, `database/migrations/`, `public/index.php`, `docs/01-prd/PRD.md` |

---

## Daftar Isi

1. [Ringkasan Indeks ADR](#1-ringkasan-indeks-adr)
2. [ADR-001: Pemesanan Direct-to-WhatsApp Tanpa Keranjang Belanja & Payment Gateway](#2-adr-001-pemesanan-direct-to-whatsapp-tanpa-keranjang-belanja--payment-gateway)
3. [ADR-002: Moderasi Produk Dua Tahap dengan Preservasi Etalase (`pending_changes`)](#3-adr-002-moderasi-produk-dua-tahap-dengan-preservasi-etalase-pending_changes)
4. [ADR-003: Moderasi Otomatis Ulasan Tamu Pra-Simpan Berbasis Kamus & Normalisasi Leetspeak](#4-adr-003-moderasi-otomatis-ulasan-tamu-pra-simpan-berbasis-kamus--normalisasi-leetspeak)
5. [ADR-004: Registrasi Penjual Tertutup (Dikurasi Admin dengan NISN 10 Digit & Pembatasan Google OAuth)](#5-adr-004-registrasi-penjual-tertutup-dikurasi-admin-dengan-nisn-10-digit--pembatasan-google-oauth)
6. [ADR-005: Generator Excel OpenXML Murni PHP (`XlsxWriter`) Tanpa Ketergantungan Pustaka Pihak Ketiga](#6-adr-005-generator-excel-openxml-murni-php-xlsxwriter-tanpa-ketergantungan-pustaka-pihak-ketiga)
7. [ADR-006: Resolusi Multi-Path pada `public/index.php` untuk Kompatibilitas Shared Hosting](#7-adr-006-resolusi-multi-path-pada-publicindexphp-untuk-kompatibilitas-shared-hosting)
8. [Rujukan Kode](#8-rujukan-kode)

---

## 1. Ringkasan Indeks ADR

| ID ADR | Keputusan Arsitektur | Status | Tanggal Efektif | Dampak Utama |
| :--- | :--- | :--- | :--- | :--- |
| **ADR-001** | Pemesanan *Direct-to-WhatsApp* (Tanpa Checkout/Payment Gateway Internal) | `[Implemented]` | Agustus 2026 | Kesederhanaan alur transaksi COD sekolah; endpoint `/api/cart*` mengembalikan HTTP `501`. |
| **ADR-002** | Moderasi Produk Preservatif via `pending_action` & `pending_changes` (JSON) | `[Implemented]` | 15 Oktober 2026 | Produk aktif tetap tayang di publik saat penjual mengajukan edit atau hapus. |
| **ADR-003** | Filter Kata Kasar Otomatis Pra-Simpan (`CommentModerationService`) | `[Implemented]` | 21 September 2026 | Ulasan tamu tidak membutuhkan antrean persetujuan manual sebelum tampil, namun tetap aman dari kata kasar. |
| **ADR-004** | Registrasi Penjual Tertutup & Google OAuth Hanya untuk Akun Terdaftar | `[Implemented]` | 15 Oktober 2026 | Hanya siswa terverifikasi (NISN 10 digit) yang dapat berjualan; tidak ada rute `/register` publik. |
| **ADR-005** | Generator `.xlsx` OpenXML Murni PHP (`XlsxWriter`) | `[Implemented]` | 25 Oktober 2026 | Ekspor Excel multi-sheet tetap jalan meskipun ekstensi PHP `ext-zip` tidak tersedia di hosting. |
| **ADR-006** | Resolusi Multi-Path `public/index.php` & Konfigurasi `public_path` Dinamis | `[Implemented]` | 25 Oktober 2026 | Aplikasi dapat di-deploy di Laragon lokal maupun struktur `core/` + `public_html/` cPanel tanpa mengubah kode inti. |

---

## 2. ADR-001: Pemesanan Direct-to-WhatsApp Tanpa Keranjang Belanja & Payment Gateway

- **Status:** Diterima & Diimplementasikan (`[Implemented]`)
- **Konteks:** SiswaMart digunakan di lingkungan sekolah (SMK) di mana transaksi antara pembeli (siswa/guru/warga sekolah) dan penjual (siswa wirausaha) dilakukan secara tatap muka di lingkungan sekolah/kelas (*Cash on Delivery* / pre-order langsung). Mengimplementasikan *payment gateway* komersial dan manajemen logistik pengiriman ekspedisi akan menambah biaya transaksi dan kerumitan operasional yang tidak diperlukan oleh siswa.
- **Keputusan:**
  1. Mengarahkan pemesanan langsung ke nomor WhatsApp penjual melalui rute pelacakan `GET /produk/{produk:slug}/whatsapp` (`Public\ProdukController::trackWhatsapp`).
  2. Mencatat setiap klik ke tabel `produk_views_logs` dengan `type = 'whatsapp'` sebelum melakukan *redirect* HTTP `302` ke `https://wa.me/{nomor}?text={pesan}`.
  3. Menyesuaikan templat pesan otomatis berdasarkan status toko (`status_toko = 'buka'` vs `'tutup'`) dan memblokir pemesanan jika `stok = 'habis'`.
  4. Menyediakan kontrak rute `/api/cart*` yang secara eksplisit mengembalikan HTTP `501 Not Implemented` (`Api\CartController`) sebagai penanda arsitektur bahwa fitur keranjang belanja internal ditangguhkan ke roadmap masa depan.
- **Konsekuensi:**
  - (+) Pengalaman pemesanan sangat cepat tanpa paksaan membuat akun pembeli.
  - (+) Analitik minat beli tetap terukur melalui log klik WhatsApp (`type = 'whatsapp'`) di dasbor dan laporan.
  - (-) Sistem tidak mencatat nilai uang transaksi riil (*completed order*) karena penyelesaian pembayaran berlangsung di luar aplikasi.
- **Rujukan Kode:** `app/Http/Controllers/Public/ProdukController.php#L73-L120`, `app/Http/Controllers/Api/CartController.php#L9-L36`, `routes/api.php#L71-L75`.

---

## 3. ADR-002: Moderasi Produk Dua Tahap dengan Preservasi Etalase (`pending_changes`)

- **Status:** Diterima & Diimplementasikan (`[Implemented]`)
- **Konteks:** Sebagai platform edukasi sekolah, setiap konten produk siswa harus diawasi oleh guru/admin sekolah. Namun, jika produk yang sudah berstatus `aktif` langsung berubah menjadi `pending` (hilang dari etalase) setiap kali penjual memperbaiki harga atau deskripsi, penjual akan kehilangan trafik pembeli selama menunggu admin melakukan moderasi.
- **Keputusan:**
  1. Menambahkan kolom `pending_action` (`varchar(20) nullable`: `'create'`, `'update'`, `'delete'`) dan `pending_changes` (`json nullable`) pada tabel `produk` melalui migrasi `2026_10_15_000004_add_product_moderation_requests.php`.
  2. Saat penjual membuat produk baru: `status = 'pending'` dan `pending_action = 'create'`.
  3. Saat penjual mengedit produk yang sudah `aktif`: kolom utama produk (`nama`, `harga`, `foto_utama`, dll.) **tidak ditimpa** dan `status` tetap `'aktif'`. Data baru disimpan ke dalam JSON `pending_changes` (termasuk path foto baru sementara di `foto_utama`) dengan `pending_action = 'update'`.
  4. Saat penjual menghapus produk yang sudah `aktif`: produk tidak langsung dihapus dari database, melainkan ditandai `pending_action = 'delete'` hingga disetujui admin.
  5. Saat admin menyetujui (`approve`) atau menolak (`reject`), sistem menyalin nilai `pending_changes` ke kolom utama atau membersihkan berkas foto sementara yang tidak terpakai agar tidak meninggalkan sampah di *storage*.
- **Konsekuensi:**
  - (+) Produk aktif tetap tampil di etalase publik (`scopePublicVisible`) selama revisi sedang ditinjau admin.
  - (+) Admin dapat melihat tabel komparasi *diff* nilai lama vs nilai baru secara berdampingan di halaman detail moderasi (`resources/views/admin/produk/show.blade.php`).
  - (-) Logika pembersihan berkas gambar (`foto_utama`) harus menangani foto lama vs foto di dalam `pending_changes` secara hati-hati.
- **Rujukan Kode:** `database/migrations/2026_10_15_000004_add_product_moderation_requests.php#L11-L26`, `app/Http/Controllers/Penjual/ProdukController.php#L126-L246`, `app/Http/Controllers/Admin/ProdukController.php#L61-L163`.

---

## 4. ADR-003: Moderasi Otomatis Ulasan Tamu Pra-Simpan Berbasis Kamus & Normalisasi Leetspeak

- **Status:** Diterima & Diimplementasikan (`[Implemented]`)
- **Konteks:** Pengunjung publik dapat mengirim ulasan produk tanpa harus login (`guest_username`, `rating`, `komentar`). Karena tidak ada autentikasi pembeli, risiko penyalahgunaan kata kasar atau perundungan siber (*cyberbullying*) di lingkungan sekolah tinggi.
- **Keputusan:**
  1. Membangun layanan `CommentModerationService` yang dieksekusi secara sinkron **sebelum** ulasan disimpan ke database.
  2. Menggabungkan kamus statis `resources/data/badwords-list.json` (350 kata) dengan tabel database `bad_words` dan `whitelists`, lalu menyimpannya dalam cache selama 24 jam (`86400` detik).
  3. Menerapkan normalisasi berlapis: konversi angka/simbol *leetspeak* (`0->o`, `1->i`, `3->e`, `4->a`, `5->s`, `7->t`, `@->a`, `$->s`), pemangkasan karakter berulang (`>2` karakter menjadi `1`), dan pencocokan batas kata (*word boundary* `\b`).
  4. Jika ditemukan kata kasar, ulasan **ditolak pada tahap validasi** dengan pesan bahasa Indonesia dan tidak disimpan ke tabel `ulasan` (`Public\UlasanController::store` & `Api\ReviewController::store`). Selain itu, rute ulasan dibatasi dengan *rate limiter* (`throttle:3,1` di Web dan `throttle:10,1` di API).
- **Konsekuensi:**
  - (+) Ulasan yang sopan langsung tayang tanpa membebani admin dengan antrean moderasi manual.
  - (+) Admin tetap memiliki wewenang pasca-publikasi untuk menghapus ulasan dan menjalankan audit massal (`php artisan comments:audit-clean`).
- **Rujukan Kode:** `app/Services/CommentModerationService.php#L18-L280`, `app/Http/Controllers/Public/UlasanController.php#L18-L62`, `app/Console/Commands/AuditCleanCommentsCommand.php#L1-L115`.

---

## 5. ADR-004: Registrasi Penjual Tertutup (Dikurasi Admin dengan NISN 10 Digit & Pembatasan Google OAuth)

- **Status:** Diterima & Diimplementasikan (`[Implemented]`)
- **Konteks:** SiswaMart dikhususkan bagi siswa aktif sekolah yang telah diverifikasi identitasnya oleh pihak sekolah. Membuka pendaftaran penjual secara bebas kepada publik akan memungkinkan pihak luar sekolah membuka toko palsu.
- **Keputusan:**
  1. Meniadakan rute registrasi mandiri di `routes/auth.php` dan menolak `POST /api/register` dengan kode HTTP `403 Forbidden` (`Api\AuthController::register`).
  2. Pembuatan akun penjual hanya dapat dilakukan oleh Admin melalui `Admin\PenjualController::store` dengan validasi ketat `nisn` tepat **10 digit angka unik** (`regex:/^\d{10}$/`).
  3. Integrasi Google OAuth (`Auth\GoogleAuthController::callback`) dibatasi **hanya untuk mencocokkan akun yang sudah terdaftar** di tabel `users` (berdasarkan `google_id` atau `email` terverifikasi), bukan membuat akun baru secara otomatis.
- **Konsekuensi:**
  - (+) Keamanan dan keaslian identitas penjual terjamin 100% sesuai data induk siswa sekolah.
  - (+) Mencegah pengambilalihan akun (*account takeover*) karena `GoogleAuthController` memeriksa klaim `email_verified` dari Google dan menolak penautan jika akun sudah terikat ke `google_id` berbeda.
- **Rujukan Kode:** `routes/auth.php#L15-L65`, `app/Http/Controllers/Api/AuthController.php#L34-L40`, `app/Http/Controllers/Admin/PenjualController.php#L90-L120`, `app/Http/Controllers/Auth/GoogleAuthController.php#L47-L116`.

---

## 6. ADR-005: Generator Excel OpenXML Murni PHP (`XlsxWriter`) Tanpa Ketergantungan Pustaka Pihak Ketiga

- **Status:** Diterima & Diimplementasikan (`[Implemented]`)
- **Konteks:** Fitur unduh laporan Excel (`.xlsx`) dibutuhkan oleh Admin dan Penjual. Pada banyak lingkungan *shared hosting* sekolah atau instalasi PHP minimalis, pustaka berat seperti `maatwebsite/excel` (`phpoffice/phpspreadsheet`) sering terkendala batas memori atau tidak aktifnya ekstensi `ZipArchive` (`ext-zip`).
- **Keputusan:**
  1. Membangun kelas generator OpenXML `.xlsx` mandiri `App\Support\XlsxWriter` (`app/Support/XlsxWriter.php`) yang menyusun struktur XML (`[Content_Types].xml`, `xl/workbook.xml`, `xl/styles.xml`, `xl/worksheets/sheet*.xml`) secara langsung.
  2. Menyertakan implementasi pengarsipan biner ZIP (`buildZipArchive` dengan CRC32) di dalam `XlsxWriter` sebagai *fallback* otomatis apabila kelas `ZipArchive` bawaan PHP tidak tersedia (`app/Support/XlsxWriter.php#L347-L427`).
  3. Menambahkan sanitasi *Formula Injection* (`StylesSheet::sanitizeText`) yang menambahkan prefiks tanda kutip tunggal (`'`) apabila nilai sel diawali karakter `=`, `+`, `-`, atau `@`.
- **Konsekuensi:**
  - (+) Ekspor `.xlsx` multi-sheet dengan *styling* penuh (header warna, zebra striping, *freeze pane*, format mata uang Rupiah) berjalan cepat, hemat memori, dan bebas ketergantungan ekstensi server.
  - (+) Aman dari serangan *CSV/Excel Formula Injection*.
- **Rujukan Kode:** `app/Support/XlsxWriter.php#L1-L429`, `app/Exports/Concerns/StylesSheet.php#L230-L245`, `app/Exports/LaporanAdminExport.php#L1-L261`, `app/Exports/LaporanPenjualExport.php#L1-L163`.

---

## 7. ADR-006: Resolusi Multi-Path pada `public/index.php` untuk Kompatibilitas Shared Hosting

- **Status:** Diterima & Diimplementasikan (`[Implemented]`)
- **Konteks:** Pengembangan dilakukan di Laragon lokal (struktur direktori standar Laravel), sedangkan lingkungan produksi sering menggunakan cPanel/DirectAdmin di mana berkas publik ditempatkan di `public_html/` dan kode inti ditempatkan di folder terpisah (`../core/` atau `../siswamart/`).
- **Keputusan:**
  1. Memodifikasi `public/index.php` (`public/index.php#L14-L46`) agar memeriksa daftar kandidat direktori aplikasi secara berurutan (`__DIR__.'/..'`, `__DIR__.'/../core'`, `__DIR__.'/../siswamart'`) dan menggunakan direktori pertama yang memiliki `vendor/autoload.php` serta `bootstrap/app.php`.
  2. Mengikat path publik aktual melalui `$app->usePublicPath(__DIR__)` (`public/index.php#L44`) dan menyelaraskan `config/dompdf.php#L42` agar resolusi aset PDF tetap akurat di semua topologi direktori.
- **Konsekuensi:**
  - (+) Berkas kode yang sama dapat langsung dijalankan di Laragon lokal maupun cPanel tanpa perlu mengedit ulang `index.php` secara manual setiap kali melakukan deployment.
- **Rujukan Kode:** `public/index.php#L14-L48`, `config/dompdf.php#L42`.

---

## 8. Rujukan Kode

- `app/Http/Controllers/Public/ProdukController.php#L73-L120`
- `app/Http/Controllers/Api/CartController.php#L9-L36`
- `app/Http/Controllers/Penjual/ProdukController.php#L126-L246`
- `app/Http/Controllers/Admin/ProdukController.php#L61-L163`
- `app/Services/CommentModerationService.php#L1-L361`
- `app/Http/Controllers/Auth/GoogleAuthController.php#L47-L116`
- `app/Support/XlsxWriter.php#L1-L429`
- `app/Exports/Concerns/StylesSheet.php#L230-L245`
- `public/index.php#L1-L48`
