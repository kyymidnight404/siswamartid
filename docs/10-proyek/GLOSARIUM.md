# Glosarium Istilah Domain & Teknis — SiswaMart

| Informasi Dokumen | Detail |
| :--- | :--- |
| **Judul** | Glosarium Istilah Domain Bisnis dan Teknis SiswaMart |
| **Versi** | `1.1.0` |
| **Tanggal** | 27 Oktober 2026 |
| **Status** | Final (Source-Code Backed) |
| **Penulis** | Tim Dokumentasi Teknis & Rekayasa Perangkat Lunak |
| **Sumber Data** | Seluruh model, kontroler, service, migrasi, dan dokumen `docs/` SiswaMart |

---

## Daftar Isi

1. [Istilah Domain Bisnis & Peran Pengguna](#1-istilah-domain-bisnis--peran-pengguna)
2. [Istilah Status Produk, Moderasi, dan Operasional Toko](#2-istilah-status-produk-moderasi-dan-operasional-toko)
3. [Istilah Moderasi Komentar & Keamanan](#3-istilah-moderasi-komentar--keamanan)
4. [Istilah Teknis Arsitektur & Pelaporan](#4-istilah-teknis-arsitektur--pelaporan)
5. [Rujukan Kode](#5-rujukan-kode)

---

## 1. Istilah Domain Bisnis & Peran Pengguna

| Istilah | Definisi dalam Konteks SiswaMart | Rujukan Kode |
| :--- | :--- | :--- |
| **SiswaMart** | Aplikasi marketplace dan katalog produk kewirausahaan siswa tingkat SMK yang menghubungkan penjual siswa dengan pembeli di lingkungan sekolah. | `config/app.php` |
| **Admin (`role = 'admin'`)** | Pihak sekolah/guru pembina yang memiliki hak akses penuh ke panel `/admin/*` untuk mengelola akun penjual, memoderasi produk, mengelola kategori, memoderasi komentar, dan mengunduh laporan analitik global. | `app/Models/User.php#L61-L64`, `app/Http/Middleware/EnsureAdmin.php` |
| **Penjual (`role = 'penjual'`)** | Siswa wirausaha yang telah didaftarkan oleh Admin menggunakan NISN 10 digit dan memiliki 1 profil toko di tabel `penjual` untuk mengelola produk di panel `/penjual/*`. | `app/Models/User.php#L66-L69`, `app/Models/Penjual.php` |
| **Pengunjung (*Guest*)** | Pembeli atau warga sekolah yang menjelajahi katalog publik, memesan via WhatsApp, dan menulis ulasan produk tanpa perlu login. | `routes/web.php#L9-L28` |
| **NISN** | Nomor Induk Siswa Nasional sepanjang tepat **10 digit angka unik** (`penjual.nisn`) yang wajib diisi saat Admin mendaftarkan penjual baru. | `app/Http/Controllers/Admin/PenjualController.php#L92` |
| **Direct-to-WhatsApp** | Model pemesanan SiswaMart di mana tombol beli pada halaman produk mengarahkan pembeli langsung ke percakapan WhatsApp penjual (`wa.me/62...`) dengan templat pesan otomatis, tanpa melalui keranjang belanja atau *payment gateway* internal. | `app/Http/Controllers/Public/ProdukController.php#L73-L120`, `ADR-001` |

---

## 2. Istilah Status Produk, Moderasi, dan Operasional Toko

| Istilah / Simbol Kode | Definisi dan Perilaku Sistem | Rujukan Kode |
| :--- | :--- | :--- |
| **`publicVisible` (`scopePublicVisible`)** | Query scope pada model `Produk` yang memastikan hanya produk dengan `status = 'aktif'` **dan** milik penjual yang `user.is_active = true` yang dapat tampil di halaman publik maupun API publik. | `app/Models/Produk.php#L47-L53` |
| **`pendingModeration` (`scopePendingModeration`)** | Query scope pada model `Produk` yang mengambil seluruh produk yang sedang membutuhkan keputusan Admin (`status = 'pending'` atau `pending_action IS NOT NULL`). | `app/Models/Produk.php#L55-L60` |
| **`pending_action`** | Kolom pada tabel `produk` yang menandai jenis pengajuan moderasi yang sedang menunggu keputusan Admin: `'create'` (produk baru), `'update'` (revisi produk aktif), `'delete'` (permintaan hapus produk), atau `null` (tidak ada ajuan menggantung). | `app/Models/Produk.php#L27`, `2026_10_15_000004` |
| **`pending_changes`** | Kolom bertipe `JSON` pada tabel `produk` yang menyimpan nilai perubahan baru (`nama`, `kategori_id`, `deskripsi`, `harga`, `stok`, `label_promosi`, `foto_utama`) ketika penjual mengedit produk yang sedang `aktif`. Selama belum disetujui Admin, halaman publik tetap menampilkan kolom utama lama. | `app/Models/Produk.php#L28`, `#L35`, `ADR-002` |
| **`alasan_ditolak`** | Catatan umpan balik dari Admin (maksimal 500 karakter) yang wajib diisi saat Admin menolak ajuan moderasi produk (`status = 'ditolak'`). | `app/Http/Controllers/Admin/ProdukController.php#L65` |
| **Status Produk (`produk.status`)** | Siklus hidup publikasi produk: `'draft'` (konsep), `'pending'` (menunggu moderasi awal), `'aktif'` (tayang di publik), dan `'ditolak'` (pengajuan produk baru ditolak Admin). | `2026_10_15_000002_normalize_erd_status_values.php` |
| **Status Stok (`produk.stok`)** | Tingkat ketersediaan barang: `'tersedia'` (*Ready Stock*), `'po'` (*Pre-Order*), dan `'habis'` (*Out of Stock* — memblokir tombol pemesanan WhatsApp). | `app/Http/Controllers/Public/ProdukController.php#L79-L83` |
| **Status Toko (`penjual.status_toko`)** | Status operasional toko penjual: `'buka'` (melayani pesanan normal) atau `'tutup'` (menampilkan lencana *Toko Tutup* dan mengubah teks pesan WhatsApp menjadi konfirmasi jam buka). | `app/Http/Controllers/Penjual/ProfilController.php#L90-L105` |

---

## 3. Istilah Moderasi Komentar & Keamanan

| Istilah | Definisi dalam Konteks SiswaMart | Rujukan Kode |
| :--- | :--- | :--- |
| **`CommentModerationService`** | Layanan inti penyaringan bahasa pada ulasan tamu yang memeriksa `guest_username` dan `komentar` terhadap kamus kata kasar sebelum ulasan disimpan ke database. | `app/Services/CommentModerationService.php` |
| **Normalisasi *Leetspeak*** | Proses mengubah angka dan simbol pengganti huruf (`0` $\rightarrow$ `o`, `1` $\rightarrow$ `i`, `3` $\rightarrow$ `e`, `4` $\rightarrow$ `a`, `5` $\rightarrow$ `s`, `7` $\rightarrow$ `t`, `@` $\rightarrow$ `a`, `$` $\rightarrow$ `s`) serta mereduksi huruf berulang agar kata kasar yang disamarkan tetap terdeteksi. | `app/Services/CommentModerationService.php#L200-L235` |
| **`bad_words`** | Tabel database (dipadukan dengan `resources/data/badwords-list.json`) yang menyimpan daftar kata terlarang beserta tingkat keparahan (`severity`) dan bahasanya. | `app/Models/BadWord.php` |
| **`whitelists`** | Tabel database yang menyimpan daftar frasa aman yang dikecualikan terlebih dahulu sebelum pemindaian kata kasar guna mencegah *false-positive*. | `app/Models/Whitelist.php` |
| **Formula Injection Sanitization** | Mekanisme keamanan pada `StylesSheet::sanitizeText()` yang menambahkan prefiks tanda kutip tunggal (`'`) pada sel Excel yang diawali karakter `=`, `+`, `-`, atau `@` agar tidak dieksekusi sebagai rumus berbahaya oleh aplikasi spreadsheet. | `app/Exports/Concerns/StylesSheet.php#L230-L245` |

---

## 4. Istilah Teknis Arsitektur & Pelaporan

| Istilah | Definisi dalam Konteks SiswaMart | Rujukan Kode |
| :--- | :--- | :--- |
| **`ProdukViewLog` (`produk_views_logs`)** | Model dan tabel analitik berwaktu yang mencatat dua jenis interaksi pengunjung: `'view'` (saat halaman detail produk dibuka) dan `'whatsapp'` (saat tombol pesan via WhatsApp diklik). | `app/Models/ProdukViewLog.php` |
| **`XlsxWriter`** | Generator berkas OpenXML `.xlsx` murni PHP di `app/Support/XlsxWriter.php` yang membangun dokumen Excel multi-sheet lengkap dengan *styling* dan pengarsipan ZIP internal tanpa memerlukan pustaka pihak ketiga. | `app/Support/XlsxWriter.php` |
| **Soft Neubrutalism / Playful Tactile** | Karakteristik desain antarmuka SiswaMart yang memadukan sudut membulat (`28px`), garis tepi tinta tegas (`2.5px solid #1F3550`), dan bayangan offset padat (`4px 4px 0 #1F3550`). | `public/css/siswamart-theme.css`, `public/css/homepage.css` |
| **`DatabaseTransactions`** | Trait pengujian Laravel yang membungkus setiap metode test di dalam transaksi database dan membatalkannya (`ROLLBACK`) di akhir test sehingga data lokal tetap aman. | `tests/Feature/ReportDownloadTest.php#L15` |

---

## 5. Rujukan Kode

- `app/Models/Produk.php`, `app/Models/Penjual.php`, `app/Models/User.php`, `app/Models/Ulasan.php`, `app/Models/ProdukViewLog.php`
- `app/Services/CommentModerationService.php`, `app/Services/AdminNotificationService.php`, `app/Services/LaporanAdminService.php`, `app/Services/LaporanPenjualService.php`
- `app/Support/XlsxWriter.php`, `app/Exports/Concerns/StylesSheet.php`
