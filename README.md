# SiswaMart — Marketplace & Katalog Produk Kewirausahaan Siswa SMK

**SiswaMart** adalah platform katalog digital dan marketplace kewirausahaan sekolah berbasis web serta REST API yang memfasilitasi siswa SMK untuk memasarkan produk dan jasa kreatif mereka kepada warga sekolah maupun masyarakat umum, lengkap dengan pengawasan moderasi berlapis oleh pihak sekolah (Admin).

---

## Fitur Utama

1. **Etalase Publik & Pemesanan *Direct-to-WhatsApp* (`[Implemented]`)**
   - Eksplorasi produk unggulan, pencarian lintas kata kunci, filter multi-kategori, filter rentang harga, dan pengurutan harga/popularitas.
   - Pengalihan pemesanan langsung ke WhatsApp penjual (`GET /produk/{slug}/whatsapp`) dengan pencatatan log analitik (`produk_views_logs`) dan penyesuaian teks pesan otomatis berdasarkan status operasional toko (`buka`/`tutup`).
   - Ulasan dan rating bintang (1–5) oleh pengunjung publik yang dilindungi filter kata kasar & *leetspeak* otomatis (`CommentModerationService`).

2. **Panel Penjual Siswa (`/penjual/*` — `[Implemented]`)**
   - Dasbor metrik performa toko (total produk, tayangan `views`, klik WhatsApp, rata-rata rating).
   - Manajemen produk dengan **Moderasi Dua Tahap Preservatif (`pending_action` & `pending_changes`)**: produk yang sudah `aktif` tetap tayang di etalase publik sementara revisi data/foto menunggu persetujuan Admin.
   - Pengaturan profil toko dan sakelar cepat status operasional toko (`buka` $\leftrightarrow$ `tutup`).
   - Ekspor laporan performa toko ke format **PDF (DomPDF A4 Landscape)** dan **Excel (`.xlsx` 2 Sheet)**.

3. **Panel Admin Sekolah (`/admin/*` — `[Implemented]`)**
   - Dasbor analitik global lintas toko dan kategori.
   - Manajemen akun penjual terkurasi dengan validasi **NISN 10 digit angka unik**, sakelar aktif/nonaktif akun (mencabut sesi & token seketika), dan proteksi penghapusan penjual berproduk.
   - Moderasi ajuan produk (`create`, `update` dengan tabel komparasi *diff* nilai lama vs baru, dan `delete`) beserta kewajiban alasan penolakan (`alasan_ditolak`).
   - Manajemen kategori, moderasi komentar ulasan, dan lonceng notifikasi aktivitas real-time.
   - Ekspor laporan analitik global ke format **PDF** dan **Excel (`.xlsx` 3 Sheet: *Ringkasan*, *Performa Penjual*, *Daftar Produk*)**.

4. **Autentikasi Ganda & REST API (`/api/*` — `[Implemented]`)**
   - Login menggunakan **Email atau Username** serta **Google OAuth 2.0 (`Laravel Socialite`)** yang dibatasi khusus untuk akun terdaftar.
   - **26 rute REST API** berbasis **Laravel Sanctum Bearer Token** (22 endpoint fungsional aktif + 4 rute `/api/cart*` yang mengembalikan `501 Not Implemented` sesuai desain pemesanan via WhatsApp).

---

## Tech Stack & Versi Aktual

| Lapisan | Teknologi | Versi Aktual (`composer.json` / `package.json`) |
| :--- | :--- | :--- |
| **Runtime & Framework** | PHP & Laravel Framework | PHP `^8.3` (`8.3.30`), Laravel `^13.38` (`v13.38.0`) |
| **Autentikasi** | Laravel Breeze, Sanctum, & Socialite | Breeze `^2.4`, Sanctum `v4.3.3`, Socialite `^5.31` |
| **Frontend & Styling** | Blade, Tailwind CSS v4, Alpine.js, Vite | Tailwind CSS `^4.0.7`, Alpine.js `^3.14.9`, Vite `^6.2.4` |
| **Pelaporan Dokumen** | DomPDF & Custom OpenXML `XlsxWriter` | `barryvdh/laravel-dompdf` `^3.1` (`v3.1.2`), `App\Support\XlsxWriter` |
| **Database & Pengujian** | MySQL & PHPUnit | MySQL (19 tabel, 27 migrasi), PHPUnit `^12.5.12` (`12.5.33`) |

---

## Instalasi Singkat (Lokal / Laragon)

```bash
# 1. Instal dependensi PHP & Node.js
composer install
npm install

# 2. Salin konfigurasi environment & buat application key
copy .env.example .env
php artisan key:generate

# 3. Sesuaikan konfigurasi DB_DATABASE=siswamartid di .env, lalu jalankan migrasi & seeder
php artisan migrate
php artisan db:seed

# 4. Buat symbolic link untuk gambar produk & toko
php artisan storage:link

# 5. Kompilasi aset frontend
npm run build

# 6. Jalankan pengujian otomatis (menggunakan DatabaseTransactions yang aman)
php vendor/phpunit/phpunit/phpunit
```

Panduan instalasi lengkap dan penjelasan variabel `.env` tersedia di [`docs/09-operasional/INSTALASI.md`](docs/09-operasional/INSTALASI.md).

---

## Indeks Dokumentasi Lengkap (`docs/`)

Seluruh dokumentasi rekayasa perangkat lunak SiswaMart tersimpan di direktori **[`docs/`](docs/README.md)**:

- **Indeks Utama:** [`docs/README.md`](docs/README.md)
- **01 — Produk & Kebutuhan Bisnis:**
  - [`docs/01-prd/PRD.md`](docs/01-prd/PRD.md) — *Product Requirements Document* (v1.1.0)
  - [`docs/01-prd/GAP_ANALYSIS.md`](docs/01-prd/GAP_ANALYSIS.md) — Analisis Kesenjangan Implementasi
- **02 — Spesifikasi Kebutuhan Perangkat Lunak:**
  - [`docs/02-srs/SRS.md`](docs/02-srs/SRS.md) — *Software Requirements Specification* & *Traceability Matrix*
- **03 — Arsitektur Sistem:**
  - [`docs/03-arsitektur/ARSITEKTUR.md`](docs/03-arsitektur/ARSITEKTUR.md) — Lapisan Aplikasi, Komponen, & Siklus Request
  - [`docs/03-arsitektur/ADR.md`](docs/03-arsitektur/ADR.md) — *Architecture Decision Records* (`ADR-001` s.d. `ADR-006`)
- **04 — Database & Skema:**
  - [`docs/04-database/ERD.md`](docs/04-database/ERD.md) — Diagram ERD Mermaid (19 Tabel Aktual)
  - [`docs/04-database/KAMUS_DATA.md`](docs/04-database/KAMUS_DATA.md) — Kamus Data Per Tabel & Constraint
  - [`docs/04-database/MIGRASI.md`](docs/04-database/MIGRASI.md) — Daftar 27 Migrasi & Urutan Eksekusi
- **05 — Desain Sistem & Antarmuka:**
  - [`docs/05-desain/USE_CASE.md`](docs/05-desain/USE_CASE.md) — Diagram & Spesifikasi 20 Use Case (`UC-001`–`UC-020`)
  - [`docs/05-desain/ACTIVITY_SEQUENCE.md`](docs/05-desain/ACTIVITY_SEQUENCE.md) — Activity, Sequence, & State Diagram
  - [`docs/05-desain/ATURAN_BISNIS.md`](docs/05-desain/ATURAN_BISNIS.md) — Katalog 16 Aturan Bisnis (`BR-01`–`BR-16`)
  - [`docs/05-desain/DESIGN_SYSTEM.md`](docs/05-desain/DESIGN_SYSTEM.md) — Token Warna Hex, Tipografi, & Komponen UI
- **06 — Dokumentasi REST API:**
  - [`docs/06-api/API.md`](docs/06-api/API.md) — Referensi Lengkap 26 Endpoint API & Rute Publik
  - [`docs/06-api/openapi.yaml`](docs/06-api/openapi.yaml) — Spesifikasi OpenAPI 3.0.3
- **07 — Panduan Pengguna:**
  - [`docs/07-panduan/PANDUAN_PENGUNJUNG.md`](docs/07-panduan/PANDUAN_PENGUNJUNG.md) — Panduan Pembeli / Pengunjung
  - [`docs/07-panduan/PANDUAN_PENJUAL.md`](docs/07-panduan/PANDUAN_PENJUAL.md) — Panduan Penjual Siswa
  - [`docs/07-panduan/PANDUAN_ADMIN.md`](docs/07-panduan/PANDUAN_ADMIN.md) — Panduan Admin Sekolah
  - [`docs/07-panduan/FAQ_TROUBLESHOOTING.md`](docs/07-panduan/FAQ_TROUBLESHOOTING.md) — Penyelesaian Masalah Umum
- **08 — Pengujian Perangkat Lunak:**
  - [`docs/08-pengujian/RENCANA_PENGUJIAN.md`](docs/08-pengujian/RENCANA_PENGUJIAN.md) — Strategi & Lingkungan Pengujian
  - [`docs/08-pengujian/TEST_CASE.md`](docs/08-pengujian/TEST_CASE.md) — 38 Kasus Uji Black-Box & 10 Suite Otomatis
  - [`docs/08-pengujian/HASIL_PENGUJIAN.md`](docs/08-pengujian/HASIL_PENGUJIAN.md) — Hasil Eksekusi Nyata PHPUnit (74/75 Lulus)
- **09 — Operasional & Keamanan:**
  - [`docs/09-operasional/INSTALASI.md`](docs/09-operasional/INSTALASI.md) — Panduan Instalasi Lokal
  - [`docs/09-operasional/DEPLOYMENT.md`](docs/09-operasional/DEPLOYMENT.md) — Panduan Deploy cPanel & VPS
  - [`docs/09-operasional/KEAMANAN.md`](docs/09-operasional/KEAMANAN.md) — Kontrol Keamanan & Mitigasi Risiko
- **10 — Manajemen Proyek:**
  - [`docs/10-proyek/ROADMAP.md`](docs/10-proyek/ROADMAP.md) — Peta Jalan Pengembangan
  - [`docs/10-proyek/CHANGELOG.md`](docs/10-proyek/CHANGELOG.md) — Riwayat Perubahan Proyek
  - [`docs/10-proyek/KONTRIBUSI.md`](docs/10-proyek/KONTRIBUSI.md) — Panduan Kontribusi & Standar Kode
  - [`docs/10-proyek/GLOSARIUM.md`](docs/10-proyek/GLOSARIUM.md) — Glosarium Istilah Domain & Teknis
