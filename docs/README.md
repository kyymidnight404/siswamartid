# Dokumentasi Proyek SiswaMart

## Informasi Dokumen

| Atribut | Detail |
|---|---|
| **Judul Dokumen** | Indeks Utama Dokumentasi Proyek SiswaMart |
| **Versi Dokumen** | 1.1.0 |
| **Tanggal Pembaruan** | 27 Oktober 2026 |
| **Status** | Final (*Source-Code Backed*) |
| **Penulis** | Tim Pengembang & Dokumentasi Teknis SiswaMart (SMK RPL) |
| **Sumber Data** | Kode sumber aktual (`app/`, `routes/`, `database/`, `resources/`, `config/`, `tests/`), output `php artisan route:list`, `php artisan migrate:status`, dan eksekusi PHPUnit |

---

## Daftar Isi

1. [Tentang Dokumentasi Ini](#1-tentang-dokumentasi-ini)
2. [Ringkasan Statistik Proyek Terverifikasi](#2-ringkasan-statistik-proyek-terverifikasi)
3. [Konvensi Status Implementasi & Penomoran ID](#3-konvensi-status-implementasi--penomoran-id)
4. [Peta Struktur Dokumentasi (`docs/`)](#4-peta-struktur-dokumentasi-docs)
5. [Panduan Membaca Berdasarkan Peran](#5-panduan-membaca-berdasarkan-peran)
6. [Rujukan Kode](#6-rujukan-kode)

---

## 1. Tentang Dokumentasi Ini

Dokumentasi ini merupakan satu set dokumen rekayasa perangkat lunak lengkap untuk **SiswaMart** (*Platform Katalog Digital & Promosi Produk Kantin Sekolah serta Siswa Wirausaha*). Seluruh isi dokumentasi disusun untuk dua tujuan utama:

1. **Dokumen Tugas Akhir SMK Rekayasa Perangkat Lunak (RPL):** Menyediakan spesifikasi kebutuhan (PRD & SRS), perancangan basis data (ERD, Kamus Data, Migrasi), desain sistem (Use Case, Activity, Sequence, Aturan Bisnis, Design System), serta bukti pengujian (Black-Box & Otomatis PHPUnit).
2. **Pegangan Pengembangan & Operasional Berikutnya:** Menjadi rujukan teknis yang akurat bagi pengembang penerus dan administrator sekolah untuk instalasi lokal, deployment ke cPanel/DirectAdmin, audit keamanan, serta peta jalan pengembangan (*roadmap*).

> [!IMPORTANT]
> **Prinsip Source-Code Backed:** Setiap klaim fitur, skema tabel, rute, aturan validasi, dan angka statistik di dalam seluruh dokumen ini diturunkan langsung dari kode sumber di `C:\laragon\www\siswamartid` tanpa rekayasa. Fitur yang belum selesai atau masih direncanakan ditandai secara eksplisit.

---

## 2. Ringkasan Statistik Proyek Terverifikasi

Seluruh angka di bawah ini diambil langsung dari berkas proyek dan perintah CLI per **27 Oktober 2026**:

| Metrik | Nilai Aktual | Sumber Verifikasi |
|---|---|---|
| **Versi PHP Target / Platform** | `^8.3` (`8.3.30`) | `composer.json#L9`, `composer.json#L85` |
| **Versi Laravel Framework** | `^13.38` (`v13.38.0` terinstal) | `composer.json#L11`, `composer.lock` |
| **Paket Backend Utama** | `barryvdh/laravel-dompdf` (`v3.1.2`), `laravel/sanctum` (`v4.3.3`), `laravel/socialite` (`v5.31.0`), `laravel/breeze` (`v2.4.2`) | `composer.json#L10-L25`, `composer.lock` |
| **Paket Frontend Utama** | `tailwindcss` (`^4.0.7`), `@tailwindcss/vite` (`^4.0.7`), `alpinejs` (`^3.14.9`), `vite` (`^6.2.4`) | `package.json#L8-L14` |
| **Total Rute Terdaftar** | **102 rute** (32 Admin, 26 API, 19 Auth/Profil/Preview, 12 Penjual, 9 Publik, 4 Sistem/Framework) | `php artisan route:list --json` |
| **Total Model Eloquent** | **9 model** (`User`, `Penjual`, `Produk`, `Kategori`, `Ulasan`, `ProdukViewLog`, `BadWord`, `Whitelist`, `CommentModerationLog`) | `app/Models/*.php` |
| **Total Berkas Migrasi** | **27 berkas migrasi** (27 `Ran` / Batch 1–9, 0 `Pending` pada DB lokal saat ini; 5 migrasi Batch 9 bertanggal `2026_10_15` adalah *ex-pending*) | `php artisan migrate:status`, `database/migrations/` |
| **Total Tabel Basis Data Aktif** | **19 tabel** (9 tabel domain aplikasi + 2 tabel fitur/token `notifications` & `personal_access_tokens` + 7 tabel infrastruktur Laravel + 1 tabel `migrations`) | `SHOW TABLES` pada DB `siswamartid` |
| **Total Berkas Uji Otomatis** | **10 berkas test** di `tests/Feature/` (**75 test method**, **427 assertions**; hasil aktual: **74 Lulus**, **1 Gagal** karena data produk toko lokal) | `php vendor/phpunit/phpunit/phpunit` |
| **Total Skenario Uji Black-Box** | **38 kasus uji** (`TC-001` s.d. `TC-038`) | `Pengujian_Black_Box_SiswaMart.xlsx` |

---

## 3. Konvensi Status Implementasi & Penomoran ID

### 3.1 Label Status Fitur
Setiap fitur atau komponen diberi salah satu label status berikut:
- **`[Implemented]`**: Kode telah tersedia, terhubung ke rute/UI/API, dan berfungsi sesuai spesifikasi.
- **`[Partial]`**: Sebagian komponen tersedia (mis. kolom DB atau controller ada, atau rute mengembalikan `501 Not Implemented` / belum terhubung ke antarmuka).
- **`[Pending migrasi]`**: Menunjukkan elemen skema yang berasal dari 5 migrasi penyelarasan (`2026_10_15_000001` s.d. `000005` — Batch 9). Pada basis data lokal saat ini ke-5 migrasi tersebut telah dijalankan (`Ran`), namun pada lingkungan yang belum menjalankan Batch 9 perlu dieksekusi terlebih dahulu.
- **`[Planned]`**: Fitur atau penyempurnaan yang direncanakan pada peta jalan (*roadmap*) pengembangan berikutnya dan belum ada di kode sumber.

### 3.2 Standar Penomoran ID Lintas Dokumen
Seluruh dokumen menggunakan kode identifikasi yang konsisten dan saling merujuk:

| Prefiks ID | Kategori | Dokumen Utama |
|---|---|---|
| **`OBJ-xx`** | Sasaran / Tujuan Produk (*Objectives*) | [01-prd/PRD.md](01-prd/PRD.md) |
| **`FR-xxx`** (`FR-AUTH-xxx`, dst.) | Kebutuhan Fungsional (*Functional Requirements*) | [01-prd/PRD.md](01-prd/PRD.md), [02-srs/SRS.md](02-srs/SRS.md) |
| **`NFR-xxx`** | Kebutuhan Non-Fungsional (*Non-Functional Requirements*) | [01-prd/PRD.md](01-prd/PRD.md), [02-srs/SRS.md](02-srs/SRS.md) |
| **`BR-xxx`** (`BR-PROD-xx`, dst.) | Aturan Bisnis (*Business Rules*) | [01-prd/PRD.md](01-prd/PRD.md), [05-desain/ATURAN_BISNIS.md](05-desain/ATURAN_BISNIS.md) |
| **`UC-xxx`** | Spesifikasi *Use Case* | [05-desain/USE_CASE.md](05-desain/USE_CASE.md) |
| **`API-xxx` / `WEB-PUB-xxx`** | Endpoint REST API & Rute Publik Penting | [06-api/API.md](06-api/API.md), [06-api/openapi.yaml](06-api/openapi.yaml) |
| **`TC-xxx` / `TC-AUTO-xxx`** | Kasus Uji Black-Box & Suite Uji Otomatis | [08-pengujian/TEST_CASE.md](08-pengujian/TEST_CASE.md), [08-pengujian/HASIL_PENGUJIAN.md](08-pengujian/HASIL_PENGUJIAN.md) |
| **`RSK-xx`** | Identifikasi Risiko & Keamanan | [01-prd/PRD.md](01-prd/PRD.md), [09-operasional/KEAMANAN.md](09-operasional/KEAMANAN.md) |
| **`GAP-xxx`** | Analisis Kesenjangan (*Gap Analysis*) | [01-prd/GAP_ANALYSIS.md](01-prd/GAP_ANALYSIS.md) |
| **`ADR-xxx`** | *Architecture Decision Record* | [03-arsitektur/ADR.md](03-arsitektur/ADR.md) |

---

## 4. Peta Struktur Dokumentasi (`docs/`)

```text
docs/
├── README.md                          # Indeks utama & panduan membaca dokumentasi (berkas ini)
├── 01-prd/
│   ├── PRD.md                         # Product Requirements Document (Visi, Persona, FR, User Stories, Risiko)
│   └── GAP_ANALYSIS.md                # Analisis kesenjangan antara desain ideal vs kondisi aktual kode
├── 02-srs/
│   └── SRS.md                         # Software Requirements Specification & Matriks Keterlacakan (RTM)
├── 03-arsitektur/
│   ├── ARSITEKTUR.md                  # Arsitektur berlapis, struktur direktori, alur request, komponen
│   └── ADR.md                         # Architecture Decision Records (WhatsApp order, staging produk, dll.)
├── 04-database/
│   ├── ERD.md                         # Diagram Relasi Entitas (Mermaid ERD) skema aktual 19 tabel
│   ├── KAMUS_DATA.md                  # Spesifikasi lengkap kolom, tipe, indeks, dan FK seluruh tabel
│   └── MIGRASI.md                     # Inventaris 27 migrasi, status batch, tujuan, & urutan eksekusi
├── 05-desain/
│   ├── USE_CASE.md                    # Diagram & spesifikasi detail 20 Use Case (Pengunjung, Penjual, Admin)
│   ├── ACTIVITY_SEQUENCE.md           # Flowchart/Activity, Sequence, & State Diagram alur utama
│   ├── ATURAN_BISNIS.md               # Katalog lengkap aturan bisnis bernomor beserta rujukan baris kode
│   └── DESIGN_SYSTEM.md               # Token warna hex nyata, tipografi, radius, shadow, & komponen UI
├── 06-api/
│   ├── API.md                         # Dokumentasi 26 endpoint REST API + rute web publik penting
│   └── openapi.yaml                   # Spesifikasi standar OpenAPI 3.0.3 yang valid
├── 07-panduan/
│   ├── PANDUAN_PENGUNJUNG.md          # Panduan langkah demi langkah untuk pembeli/pengunjung
│   ├── PANDUAN_PENJUAL.md             # Panduan operasional penjual (toko, produk, moderasi, laporan PDF/Excel)
│   ├── PANDUAN_ADMIN.md               # Panduan administrator sekolah (penjual, moderasi diff, kategori, laporan)
│   └── FAQ_TROUBLESHOOTING.md         # Tanya jawab & solusi kendala teknis umum
├── 08-pengujian/
│   ├── RENCANA_PENGUJIAN.md           # Strategi, cakupan, lingkungan, dan kriteria kelulusan pengujian
│   ├── TEST_CASE.md                   # Tabel 38 Test Case Black-Box + pemetaan 10 suite test otomatis
│   └── HASIL_PENGUJIAN.md             # Laporan eksekusi nyata 75 test PHPUnit & analisis kegagalan
├── 09-operasional/
│   ├── INSTALASI.md                   # Panduan instalasi lokal (Laragon), variabel .env, migrate & seeder
│   ├── DEPLOYMENT.md                  # Panduan deployment ke hosting cPanel/DirectAdmin & VPS
│   └── KEAMANAN.md                    # Kontrol keamanan terverifikasi, rate limiting, moderasi, & mitigasi RSK
└── 10-proyek/
    ├── ROADMAP.md                     # Peta jalan pengembangan masa depan per fase & ketergantungan
    ├── CHANGELOG.md                   # Riwayat perubahan proyek berbasis riwayat komit Git
    ├── KONTRIBUSI.md                  # Standar kode, konvensi penamaan, alur Git, & panduan menambah fitur
    └── GLOSARIUM.md                   # Daftar istilah domain bisnis dan teknis SiswaMart
```

---

## 5. Panduan Membaca Berdasarkan Peran

| Peran Pembaca | Urutan Bacaan yang Disarankan |
|---|---|
| **Penguji / Guru Pembimbing Tugas Akhir SMK RPL** | 1. [01-prd/PRD.md](01-prd/PRD.md)<br>2. [02-srs/SRS.md](02-srs/SRS.md)<br>3. [04-database/ERD.md](04-database/ERD.md) & [04-database/KAMUS_DATA.md](04-database/KAMUS_DATA.md)<br>4. [05-desain/USE_CASE.md](05-desain/USE_CASE.md) & [05-desain/ACTIVITY_SEQUENCE.md](05-desain/ACTIVITY_SEQUENCE.md)<br>5. [08-pengujian/TEST_CASE.md](08-pengujian/TEST_CASE.md) & [08-pengujian/HASIL_PENGUJIAN.md](08-pengujian/HASIL_PENGUJIAN.md) |
| **Pengembang Baru (*Developer Onboarding*)** | 1. [09-operasional/INSTALASI.md](09-operasional/INSTALASI.md)<br>2. [03-arsitektur/ARSITEKTUR.md](03-arsitektur/ARSITEKTUR.md) & [03-arsitektur/ADR.md](03-arsitektur/ADR.md)<br>3. [05-desain/ATURAN_BISNIS.md](05-desain/ATURAN_BISNIS.md) & [05-desain/DESIGN_SYSTEM.md](05-desain/DESIGN_SYSTEM.md)<br>4. [06-api/API.md](06-api/API.md)<br>5. [10-proyek/KONTRIBUSI.md](10-proyek/KONTRIBUSI.md) & [01-prd/GAP_ANALYSIS.md](01-prd/GAP_ANALYSIS.md) |
| **Tim IT Sekolah / DevOps / SysAdmin** | 1. [09-operasional/INSTALASI.md](09-operasional/INSTALASI.md)<br>2. [04-database/MIGRASI.md](04-database/MIGRASI.md)<br>3. [09-operasional/DEPLOYMENT.md](09-operasional/DEPLOYMENT.md)<br>4. [09-operasional/KEAMANAN.md](09-operasional/KEAMANAN.md)<br>5. [07-panduan/FAQ_TROUBLESHOOTING.md](07-panduan/FAQ_TROUBLESHOOTING.md) |
| **Pengguna Akhir (Siswa, Penjual, Admin Sekolah)** | 1. [07-panduan/PANDUAN_PENGUNJUNG.md](07-panduan/PANDUAN_PENGUNJUNG.md)<br>2. [07-panduan/PANDUAN_PENJUAL.md](07-panduan/PANDUAN_PENJUAL.md)<br>3. [07-panduan/PANDUAN_ADMIN.md](07-panduan/PANDUAN_ADMIN.md)<br>4. [07-panduan/FAQ_TROUBLESHOOTING.md](07-panduan/FAQ_TROUBLESHOOTING.md)<br>5. [10-proyek/GLOSARIUM.md](10-proyek/GLOSARIUM.md) |

---

## 6. Rujukan Kode

- [`composer.json`](../composer.json) — Definisi versi PHP, Laravel Framework, dan paket dependensi PHP.
- [`package.json`](../package.json) — Definisi dependensi frontend (Tailwind CSS v4, Alpine.js, Vite).
- [`bootstrap/app.php`](../bootstrap/app.php) — Konfigurasi routing, alias middleware, trusted proxy, dan JSON exception rendering.
- [`routes/web.php`](../routes/web.php), [`routes/auth.php`](../routes/auth.php), [`routes/api.php`](../routes/api.php) — Definisi seluruh rute aplikasi.
- [`database/migrations/`](../database/migrations/) — 27 berkas migrasi skema basis data.
- [`tests/Feature/`](../tests/Feature/) — 10 berkas pengujian otomatis PHPUnit.
