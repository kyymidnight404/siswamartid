# Laporan Audit Kode Tidak Terpakai (Unused & Dead Code Audit)
**SiswaMart Codebase Audit**

- **Tanggal Audit:** 28 Oktober 2026
- **Lokasi Laporan:** `docs/audit/UNUSED_CODE_AUDIT.md`
- **Metode Pemeriksaan:** Analisis Statis, Pemeriksaan Referensi AST/Grep, Route Listing, Analisis Linting (Laravel Pint), dan Eksekusi Automated Test Suite (PHPUnit).
- **Status Tindakan:** **AUDIT ONLY** (Tidak ada source code yang dihapus, dimodifikasi, atau dipindahkan selama tahap audit).

---

## 1. Ringkasan Eksekutif

Audit komprehensif dilakukan terhadap seluruh lapisan arsitektur SiswaMart, mencakup berkas PHP (Controller, Model, Service, Middleware, Traits, Notifications, Requests), View Blade & Komponen UI, Aset Statis Publik (gambar, ikon, arsip zip, CSS, JS), Rute Aplikasi (Web, Auth, API, Console), Database Schema (Migrations, Seeders, Factories), serta Skrip Development & File Pengujian.

### Metrik Ringkasan Temuan
- **Total Kandidat Temuan Unik:** **28 temuan**
- **Distribusi Berdasarkan Kategori:**
  - **Kategori A (Kandidat Aman Dihapus):** **10 temuan** — Bukti definitif bahwa berkas/simbol tidak digunakan sama sekali dan aman dilepas.
  - **Kategori B (Perlu Verifikasi / Keputusan Bisnis):** **4 temuan** — Kode tidak aktif saat ini, namun berhubungan dengan fitur ditangguhkan / roadmap atau konfigurasi operasional khusus.
  - **Kategori C (Duplikat atau Implementasi Lama):** **4 temuan** — Kode/aset peninggalan implementasi lama yang sudah digantikan oleh arsitektur baru.
  - **Kategori D (Tidak Direkomendasikan untuk Dihapus):** **5 temuan** — Kode/fitur yang tampak tidak dipanggil eksplisit namun dibutuhkan oleh framework, backwards compatibility, atau arsitektur cadangan roadmap.
  - **Kategori E (Perbaikan Non-Penghapusan / Refactoring):** **5 temuan** — Kode aktif yang membutuhkan perbaikan definisi rute, pembersihan import, variabel closure, sanitasi file sementara, atau perbaikan test assertion.

### Dampak & Potensi Pengurangan Kompleksitas
1. **Pengurangan Ukuran Repositori & Publik:** Pembersihan aset dan view dead code dapat menghemat lebih dari **2,1 MB** ruang disk (terutama `public/siswamart-icons.zip` sebesar 1,99 MB, `resources/views/welcome.blade.php` sebesar 72 KB, dan `public/assets/icons/` sebesar ~35 KB).
2. **Eliminasi Kerentanan & Residu Operasional:** Identifikasi skrip publik berisiko tinggi (`public/deploy-helper.php` dengan kunci rahasia teks biasa) dan rute hantu (`penjual.produk.show`) yang dapat memicu `BadMethodCallException` bila diakses pengguna.
3. **Peningkatan Kualitas Kode & Maintainability:** Menghilangkan kebingungan developer atas keberadaan 2 kontroler moderasi ulasan (`Admin\UlasanController` vs `Admin\KomentarController`), 2 format kamus kata kasar (`config/moderation.php` vs `badwords-list.json`), serta controller tanpa rute (`Admin\AdminController`).

---

## 2. Tabel Ringkasan Temuan Audit

| ID | File dan Lokasi | Nama Function / Simbol / Objek | Kategori | Bukti Utama | Risiko | Rekomendasi | Tindakan |
|---|---|---|:---:|---|:---:|---|---|
| **UC-001** | `resources/views/welcome.blade.php:1-218` | Template Blade Starter Default | **A** | Tidak ada rute/controller yang me-render view ini. Homepage aktif menggunakan `public.home.index`. Berkas berisi 71.9 KB CSS inline bawaan. | Rendah | Hapus berkas template bawaan Laravel skeleton. | Hapus |
| **UC-002** | `resources/views/dashboard.blade.php:1-19` | Template Blade Breeze Default | **A** | Zero reference. Rute `/admin/dashboard` me-render `admin.dashboard`, `/penjual/dashboard` me-render `penjual.dashboard`. | Rendah | Hapus berkas stub default Breeze. | Hapus |
| **UC-003** | `app/Services/BadWordFilterService.php:1-31` | Class `BadWordFilterService` | **A** | Zero reference di seluruh codebase. Merupakan wrapper adapter legacy ke `CommentModerationService` yang tidak dipanggil manapun. | Rendah | Hapus file service adapter yang sudah usang. | Hapus |
| **UC-004** | `config/moderation.php:1-30` | Konfigurasi `config('moderation')` | **A** | Zero reference di seluruh `app/`. Moderasi kata kasar kini didorong oleh `badwords-list.json` dan tabel `bad_words`. | Rendah | Hapus berkas konfigurasi legacy yang tidak dibaca. | Hapus |
| **UC-005** | `public/siswamart-icons.zip` | Berkas Arsip Zip Ikon Raster | **A** | Arsip zip 1.99 MB di folder publik tanpa tautan download, rujukan kode, atau endpoint unduhan. | Rendah | Hapus berkas arsip residu dari folder publik. | Hapus |
| **UC-006** | `public/assets/icons/*.png` (33 file) | Aset Ikon Raster PNG | **A** | Zero reference di Blade maupun CSS. Seluruh UI SiswaMart telah bermigrasi ke SVG Tabler inline (`<x-public.home-icon>`). | Rendah | Hapus folder ikon PNG yang telah digantikan SVG. | Hapus |
| **UC-007** | `public/email-preview.html:1-118` | Berkas Statis HTML Preview Email | **A** | Berkas dump statis pengujian lokal. Preview email aktif dilayani secara dinamis melalui route `/preview/reset-password-email`. | Rendah | Hapus berkas dump statis dari direktori publik. | Hapus |
| **UC-008** | `resources/views/admin/ulasan/index.blade.php:1-3` | Template Blade `admin.ulasan.index` | **A** | Hanya berisi 1 baris `@include('admin.komentar.index')`, hanya dipanggil oleh `Admin\UlasanController` yang tidak terdaftar di rute. | Rendah | Hapus berkas alias view yang tidak terpakai. | Hapus |
| **UC-009** | `resources/views/admin/laporan/produk.blade.php:1-81` | Template Blade Laporan Produk Lama | **A** | Route `/admin/laporan/produk` mengalihkan 302 ke `admin.laporan.index`. View ini mengandalkan variabel usang yang tidak lagi dikirim. | Rendah | Hapus template sisa sebelum konsolidasi laporan. | Hapus |
| **UC-010** | `resources/views/admin/laporan/ulasan.blade.php:1-81` | Template Blade Laporan Ulasan Lama | **A** | Route `/admin/laporan/ulasan` mengalihkan 302 ke `admin.laporan.index`. View ini tidak pernah dirender oleh controller manapun. | Rendah | Hapus template sisa sebelum konsolidasi laporan. | Hapus |
| **UC-011** | `resources/views/public/produk/index.blade.php:1-143` | Template Blade Katalog Publik Lama | **A** | Route `/produk` dialihkan 301 permanen ke `/kategori` (`public.kategori.index`). Halaman katalog produk independen tidak aktif. | Rendah | Hapus template katalog lama yang telah digantikan modul kategori. | Hapus |
| **UC-012** | `app/Http/Controllers/Public/ProdukController.php:17-48` | Method `ProdukController::index()` | **A** | Route `/produk` ditangani oleh Closure 301 di `routes/web.php:14`. Method controller ini unreachable dan tidak pernah dieksekusi. | Rendah | Hapus method `index()` dari controller setelah verifikasi rute. | Hapus |
| **UC-013** | `resources/views/components/dashboard-icon.blade.php:1-23` | Komponen Blade `<x-dashboard-icon>` | **A** | Zero reference di seluruh berkas `.blade.php`. Komponen duplikat pembungkus `<x-public.home-icon>`. | Rendah | Hapus komponen wrapper yang tidak pernah dipanggil. | Hapus |
| **UC-014** | `resources/views/components/ui-icon.blade.php:1-17` | Komponen Blade `<x-ui-icon>` | **A** | Zero reference di seluruh berkas `.blade.php`. Komponen duplikat pembungkus `<x-public.home-icon>`. | Rendah | Hapus komponen wrapper yang tidak pernah dipanggil. | Hapus |
| **UC-015** | `app/Http/Controllers/Admin/UlasanController.php:1-44` | Class `Admin\UlasanController` | **C** | Rute `/admin/ulasan` dan `/admin/komentar` keduanya mengarah ke `Admin\KomentarController`. Controller ulasan lama ditinggalkan tanpa rute. | Rendah | Hapus controller ulasan lama (sudah superseded oleh KomentarController). | Hapus |
| **UC-016** | `app/Http/Controllers/Admin/AdminController.php:1-179` | Class `Admin\AdminController` | **B** | Rute CRUD sub-admin tidak terdaftar di `routes/web.php`. View `admin.admin.*` tidak ada (akan crash jika dipanggil). Tercatat di `GAP-ADM-01`. | Sedang | Verifikasi apakah fitur manajemen sub-admin akan dibuatkan UI/rute atau dihapus. | Verifikasi |
| **UC-017** | `app/Http/Controllers/Auth/RegisteredUserController.php:1-52` & `resources/views/auth/register.blade.php:1-53` | Fitur Registrasi Mandiri Publik | **B** | Rute `register` dikomentari di `routes/auth.php:19-22`. SiswaMart menonaktifkan self-registration (penjual didaftarkan via Admin/Google). | Sedang | Hapus modul self-registration sesuai keputusan bisnis sekolah. | Selesai |
| **UC-018** | `public/deploy-helper.php:1-130` | Skrip `deploy-helper.php` | **B** | Berkas PHP mandiri di folder publik dengan kunci rahasia statis `siswamart2026`. Berguna saat rilis shared-hosting, namun berisiko di repo utama. | Tinggi | Pindahkan ke direktori non-publik (`scripts/deploy/`) atau batasi IP/environment. | Verifikasi |
| **UC-019** | `app/Models/CommentModerationLog.php:1-33` & relasi `Ulasan::moderationLogs()` | Model Log Moderasi Komentar | **B** | Model & tabel migrasi tersedia, namun tidak ada controller/service yang menulis log ini saat admin menyetujui/menolak/menghapus komentar. | Sedang | Pertahankan model dan tabel untuk implementasi audit trail di Roadmap Fase 3. | Pertahankan |
| **UC-020** | `app/Models/Whitelist.php:1-40`, tabel `whitelists`, & `database/seeders/WhitelistSeeder.php` | Whitelist Kamus Moderasi | **D** | Dikecualikan sementara (`CommentModerationService:42`). Model dan seeder dicadangkan untuk fitur filter whitelist di Roadmap Fase 3. | Rendah | Jangan hapus; fitur valid yang menunggu integrasi engine moderasi. | Pertahankan |
| **UC-021** | `routes/api.php:70-73` & `app/Http/Controllers/Api/CartController.php` | Rute `/api/cart*` (HTTP 501) | **D** | Ke-4 rute mengembalikan HTTP 501. Merupakan kontrak arsitektural (ADR-03 / PRD) yang sengaja dipasang untuk penanda roadmap. | Rendah | Jangan hapus; bagian dari desain API contract SiswaMart. | Pertahankan |
| **UC-022** | Rute Redireksi Kompatibilitas (`routes/web.php:14, 39, 40, 45`) | Rute Redireksi `/produk`, `/admin/laporan/*` | **D** | Rute pengalihan aktif yang menjaga URL lama tetap berfungsi saat diakses pengguna/bookmark lama. | Rendah | Jangan hapus; diperlukan untuk menjaga HTTP 301/302 compatibility. | Pertahankan |
| **UC-023** | `app/Exports/Concerns/StylesSheet.php:1-107` | Trait `StylesSheet` | **D** | Digunakan aktif oleh `App\Support\XlsxWriter` (lines 5 & 14) untuk pencegahan formula injection dan standardisasi format Excel. | Rendah | Jangan hapus; komponen keamanan aktif dari eksportir Excel. | Pertahankan |
| **UC-024** | `routes/web.php:80` | Phantom Route `penjual.produk.show` | **E** | `Route::resource('produk', Penjual\ProdukController::class)` menghasilkan rute `show`, namun controller tidak mengimplementasikannya. | Sedang | Pasang `->except(['show'])` pada definisi resource route penjual. | Perbaiki |
| **UC-025** | Berbagai File Kontroller & Servis (6 Berkas) | Unused PHP Import Statements | **E** | Terdeteksi oleh static check & Pint: import class yang tidak pernah digunakan dalam kode (misal `Ulasan`, `Carbon`, `Request`, `Collection`). | Sangat Rendah | Bersihkan unused `use` statement pada berkas terkait. | Rapikan |
| **UC-026** | `tests/Feature/Auth/PasswordResetTest.php:48, 83` | Unused Closure Variable Captures | **E** | Variabel `$admin` dan `$seller` diimpor ke closure (`use ($admin)`) pada assertion notifikasi tetapi tidak pernah dibaca. | Sangat Rendah | Hapus variabel yang tidak dibaca dari parameter closure `use (...)`. | Rapikan |
| **UC-027** | `tests/Feature/AdminAndPenjualFilteringAndPaginationTest.php:273-280` | Assertion Hardcoded ID Database | **E** | Test `test_every_existing_registered_seller_has_at_least_10_products` mengasumsikan data seed `id [4,5,6,7]` memiliki >=10 produk. | Rendah | Refaktor pengujian agar menggunakan factory atau toleransi dinamis. | Perbaiki |
| **UC-028** | `storage/app/*.tmp` (26 berkas) & `.gitignore` | Berkas Sementara Ekspor XLSX | **E** | Berkas temporer `tempnam()` yang tercipta saat pengujian laporan menumpuk di `storage/app/` dan belum diabaikan di `.gitignore`. | Sangat Rendah | Tambahkan `/storage/app/*.tmp` ke `.gitignore` dan jadwalkan pembersihan. | Rapikan |

---

## 3. Detail Lengkap Setiap Temuan

### [UC-001] resources/views/welcome.blade.php
- **Path Relatif:** `resources/views/welcome.blade.php` (baris 1–218, 71.920 bytes)
- **Nama Simbol / Objek:** Template Blade Bawaan Laravel Skeleton
- **Tujuan Awal:** Halaman selamat datang default instalasi framework Laravel.
- **Bukti Tidak Digunakan:**
  - Pencarian `view('welcome')` dan referensi string `'welcome'` pada seluruh controller dan routes menghasilkan 0 rujukan.
  - Halaman storefront publik SiswaMart dilayani oleh `routes/web.php:9` via `App\Http\Controllers\Public\HomeController@index` yang me-render `resources/views/public/home/index.blade.php`.
- **Dampak Jika Dihapus:** Tidak ada dampak regresi. Menghemat ~72 KB ruang kode mentah.
- **Fitur Terdampak:** Tidak ada.
- **Rekomendasi Tindakan:** **Aman Dihapus (Kategori A)**.

---

### [UC-002] resources/views/dashboard.blade.php
- **Path Relatif:** `resources/views/dashboard.blade.php` (baris 1–19, 528 bytes)
- **Nama Simbol / Objek:** Template Blade Bawaan Laravel Breeze
- **Tujuan Awal:** Halaman dashboard umum ("You're logged in!") yang digenerate oleh starter kit Laravel Breeze.
- **Bukti Tidak Digunakan:**
  - SiswaMart membedakan dasbor menjadi dua entitas terisolasi: Admin (`/admin/dashboard` me-render `admin.dashboard`) dan Penjual (`/penjual/dashboard` me-render `penjual.dashboard`).
  - Tidak ada rute pada `routes/web.php` yang memanggil `view('dashboard')`.
- **Dampak Jika Dihapus:** Nol risiko regresi pada aplikasi inti.
- **Rekomendasi Tindakan:** **Aman Dihapus (Kategori A)**.

---

### [UC-003] app/Services/BadWordFilterService.php
- **Path Relatif:** `app/Services/BadWordFilterService.php` (baris 1–31, 800 bytes)
- **Nama Simbol / Objek:** Class `App\Services\BadWordFilterService`
- **Tujuan Awal:** Legacy Adapter untuk memeriksa teks kata kasar sebelum sistem moderasi ulasan terpadu diperkenalkan.
- **Bukti Tidak Digunakan:**
  - Pencarian referensi FQCN dan nama class `BadWordFilterService` di seluruh direktori `app/`, `tests/`, `routes/`, `config/`, dan `resources/` hanya menemukan deklarasi kelas itu sendiri di baris 5 file tersebut.
  - Seluruh modul moderasi aktif (`Public\UlasanController`, `Api\ReviewController`) langsung menginjeksi dan menggunakan `App\Services\CommentModerationService`.
- **Dampak Jika Dihapus:** Tidak ada kode aktif yang memanggil class ini.
- **Rekomendasi Tindakan:** **Aman Dihapus (Kategori A / C)**.

---

### [UC-004] config/moderation.php
- **Path Relatif:** `config/moderation.php` (baris 1–30, 592 bytes)
- **Nama Simbol / Objek:** Berkas Konfigurasi `config/moderation.php` (`bad_words` array)
- **Tujuan Awal:** Menyimpan daftar statis 15 kata kasar awal untuk prototipe moderasi ulasan.
- **Bukti Tidak Digunakan:**
  - Pencarian string `config('moderation')`, `config('moderation.bad_words')`, atau `Config::get('moderation')` menghasilkan 0 rujukan di seluruh repository.
  - Mesin moderasi aktif (`CommentModerationService.php:201-233`) mengambil kamus dari berkas `resources/dictionaries/badwords-list.json` (350+ kata) dan database `bad_words` table.
- **Dampak Jika Dihapus:** Tidak ada modul yang membaca konfigurasi ini.
- **Rekomendasi Tindakan:** **Aman Dihapus (Kategori A / C)**.

---

### [UC-005] public/siswamart-icons.zip
- **Path Relatif:** `public/siswamart-icons.zip` (1.990.533 bytes, ~1.99 MB)
- **Nama Simbol / Objek:** Berkas Arsip Zip Aset Ikon
- **Tujuan Awal:** Kumpulan bundel ikon grafis yang diarsipkan saat fase eksplorasi aset desain antarmuka.
- **Bukti Tidak Digunakan:**
  - Pencarian `siswamart-icons.zip` di seluruh codebase menghasilkan 0 rujukan.
  - Tidak ada tombol download atau endpoint yang menyajikan file ini ke pengguna.
- **Dampak Jika Dihapus:** Repositori dan build deployment berkurang sebesar ~2 MB tanpa mempengaruhi UI.
- **Rekomendasi Tindakan:** **Aman Dihapus (Kategori A)**.

---

### [UC-006] public/assets/icons/
- **Path Relatif:** `public/assets/icons/*.png` (33 file PNG, ~35 KB)
- **Nama Simbol / Objek:** Kumpulan Ikon Raster PNG (`activity.png`, `dashboard.png`, `product.png`, dll.)
- **Tujuan Awal:** Ikon navigasi dan dashboard berbasis file PNG raster awal.
- **Bukti Tidak Digunakan:**
  - Pencarian pola `assets/icons` di seluruh `resources/views/` dan `public/css/` menghasilkan 0 kecocokan.
  - Seluruh ikon antarmuka SiswaMart telah distandardisasi menjadi SVG Tabler melalui komponen Blade `<x-public.home-icon name="..." />`.
- **Dampak Jika Dihapus:** Folder `public/assets/icons/` dapat dihapus seutuhnya tanpa efek visual.
- **Rekomendasi Tindakan:** **Aman Dihapus (Kategori A / C)**.

---

### [UC-007] public/email-preview.html
- **Path Relatif:** `public/email-preview.html` (baris 1–118, 7.860 bytes)
- **Nama Simbol / Objek:** Berkas HTML Dump Preview Email Reset Password
- **Tujuan Awal:** File preview statis lokal untuk mengecek kompatibilitas tampilan email reset password di browser.
- **Bukti Tidak Digunakan:**
  - SiswaMart telah memiliki rute resmi untuk pratinjau email di environment lokal: `routes/web.php:95-106` (`/preview/reset-password-email`), yang langsung merender `ResetPasswordNotification->toMail($user)->render()`.
  - Berkas di direktori `public/` ini tidak terhubung ke rute atau tautan apapun.
- **Dampak Jika Dihapus:** Mengurangi file sampah statis di web root.
- **Rekomendasi Tindakan:** **Aman Dihapus (Kategori A)**.

---

### [UC-008] resources/views/admin/ulasan/index.blade.php
- **Path Relatif:** `resources/views/admin/ulasan/index.blade.php` (baris 1–3, 35 bytes)
- **Nama Simbol / Objek:** Template Blade Alias Ulasan Admin
- **Tujuan Awal:** Berkas penghubung legacy yang hanya berisi: `@include('admin.komentar.index')`.
- **Bukti Tidak Digunakan:**
  - Satu-satunya file yang memanggil view ini adalah `Admin\UlasanController.php:23`. Namun `Admin\UlasanController` sendiri tidak memiliki rute aktif.
  - Rute `/admin/ulasan` di `routes/web.php:68` langsung mengarah ke `Admin\KomentarController@index`, yang langsung me-render view definitif `admin.komentar.index`.
- **Dampak Jika Dihapus:** Menghilangkan lapisan redirect view yang redundan.
- **Rekomendasi Tindakan:** **Aman Dihapus (Kategori A)**.

---

### [UC-009 & UC-010] resources/views/admin/laporan/produk.blade.php & ulasan.blade.php
- **Path Relatif:**
  - `resources/views/admin/laporan/produk.blade.php` (baris 1–81, 3.889 bytes)
  - `resources/views/admin/laporan/ulasan.blade.php` (baris 1–81, 4.870 bytes)
- **Nama Simbol / Objek:** Template Blade Laporan Produk & Ulasan Terpisah (Legacy)
- **Tujuan Awal:** Halaman terpisah untuk laporan agregat sebelum modul laporan dikonsolidasikan.
- **Bukti Tidak Digunakan:**
  - Di `app/Http/Controllers/Admin/LaporanController.php`:
    - Method `produk()` (baris 45-51) mengembalikan `redirect()->route('admin.laporan.index', ['kategori' => 'penjual', ...])`.
    - Method `ulasan()` (baris 56-62) mengembalikan `redirect()->route('admin.laporan.index', ['kategori' => 'ulasan', ...])`.
  - Halaman terpadu disajikan oleh `resources/views/admin/laporan/index.blade.php`.
  - Jika view lama ini dipaksa di-render, akan terjadi fatal error `UndefinedVariableException` karena variabel yang di-passing controller sudah berbeda.
- **Dampak Jika Dihapus:** Mengeliminasi duplikasi kode laporan yang sudah tidak berlaku.
- **Rekomendasi Tindakan:** **Aman Dihapus (Kategori A / C)**.

---

### [UC-011 & UC-012] resources/views/public/produk/index.blade.php & ProdukController::index()
- **Path Relatif:**
  - `resources/views/public/produk/index.blade.php` (baris 1–143, 9.466 bytes)
  - `app/Http/Controllers/Public/ProdukController.php` (baris 17–48)
- **Nama Simbol / Objek:** Template Blade Katalog Produk & Method `Public\ProdukController::index()`
- **Tujuan Awal:** Halaman katalog produk publik independen (`/produk`).
- **Bukti Tidak Digunakan:**
  - Di `routes/web.php:14`:
    `Route::get('/produk', fn () => redirect()->route('public.kategori.index', [], 301));`
  - Telah diverifikasi oleh `tests/Feature/PublicCatalogRedirectTest.php` bahwa setiap request ke `/produk` secara sengaja di-redirect 301 ke `/kategori`.
  - Method `ProdukController::index()` tidak pernah tercapai (*unreachable*), dan view `public.produk.index` tidak pernah dirender.
- **Dampak Jika Dihapus:** Tidak ada regresi karena route `/produk` tetap melakukan redirect via Closure.
- **Rekomendasi Tindakan:** **Aman Dihapus (Kategori A / C)**.

---

### [UC-013 & UC-014] resources/views/components/dashboard-icon.blade.php & ui-icon.blade.php
- **Path Relatif:**
  - `resources/views/components/dashboard-icon.blade.php` (baris 1–23, 1.239 bytes)
  - `resources/views/components/ui-icon.blade.php` (baris 1–17, 774 bytes)
- **Nama Simbol / Objek:** Komponen Blade `<x-dashboard-icon>` dan `<x-ui-icon>`
- **Tujuan Awal:** Adapter wrapper untuk memetakan nama ikon ke Tabler SVG icon names.
- **Bukti Tidak Digunakan:**
  - Pencarian pola tag `<x-dashboard-icon` dan `<x-ui-icon` di seluruh folder `resources/views/` menghasilkan 0 hasil.
  - Komponen lain langsung memanggil `<x-public.home-icon :name="..." />` secara langsung.
- **Dampak Jika Dihapus:** Nol risiko regresi.
- **Rekomendasi Tindakan:** **Aman Dihapus (Kategori A / C)**.

---

### [UC-015] app/Http/Controllers/Admin/UlasanController.php
- **Path Relatif:** `app/Http/Controllers/Admin/UlasanController.php` (baris 1–44, 1.034 bytes)
- **Nama Simbol / Objek:** Class `App\Http\Controllers\Admin\UlasanController`
- **Tujuan Awal:** Moderasi ulasan admin (metode `index`, `approve`, `reject`) berbasis status manual.
- **Bukti Tidak Digunakan:**
  - Rute `/admin/komentar` dan `/admin/ulasan` pada `routes/web.php:64-68` keduanya mengarah ke `Admin\KomentarController`.
  - Alur bisnis ulasan kini menggunakan auto-moderasi (`CommentModerationService`) dengan status otomatis (`approved`, `flagged_auto_censor`) dan hapus manual via `KomentarController@destroy`.
  - Tidak ada rute yang terhubung ke `Admin\UlasanController`.
- **Dampak Jika Dihapus:** Menghilangkan kebingungan developer atas dua controller moderasi ulasan.
- **Rekomendasi Tindakan:** **Aman Dihapus (Kategori C)**.
- **Status Eksekusi:** **Selesai (Tahap 2 / ACT-15)**. Berkas telah dihapus dengan aman setelah verifikasi rute dan pengujian fitur moderasi.

---

### [UC-016] app/Http/Controllers/Admin/AdminController.php
- **Path Relatif:** `app/Http/Controllers/Admin/AdminController.php` (baris 1–179, 4.340 bytes)
- **Nama Simbol / Objek:** Class `App\Http\Controllers\Admin\AdminController`
- **Tujuan Awal:** CRUD lengkap akun sesama admin (tambah sub-admin, edit password, toggle aktif, hapus).
- **Bukti Kondisi:**
  - Berkas ini lengkap dengan method `index`, `create`, `store`, `edit`, `update`, `toggleStatus`, `destroy`.
  - Namun tidak ada rute yang didaftarkan di `routes/web.php`.
  - Template view yang dirujuk (`admin.admin.index`, `admin.admin.create`, `admin.admin.edit`) **belum pernah dibuat** di folder `resources/views/admin/`.
  - Masalah ini sudah didokumentasikan di `docs/01-prd/GAP_ANALYSIS.md` sebagai **GAP-ADM-01**.
- **Risiko Jika Langsung Dihapus:** Kehilangan implementasi logika jika pihak sekolah berencana mengaktifkan fitur delegasi sub-admin.
- **Langkah Verifikasi:** Konfirmasi apakah manajemen akun sub-admin akan diimplementasikan ke antarmuka web, atau akun admin dikelola seutuhnya melalui `AdminSeeder` / database console.
- **Rekomendasi Tindakan:** **Perlu Verifikasi (Kategori B)**.

---

### [UC-017] app/Http/Controllers/Auth/RegisteredUserController.php & resources/views/auth/register.blade.php
- **Path Relatif:**
  - `app/Http/Controllers/Auth/RegisteredUserController.php` (baris 1–52, 1.365 bytes)
  - `resources/views/auth/register.blade.php` (baris 1–53, 2.214 bytes)
- **Nama Simbol / Objek:** Modul Registrasi Mandiri Pengguna (Breeze Scaffolding)
- **Tujuan Awal:** Form registrasi mandiri untuk user umum.
- **Bukti Kondisi:**
  - Pada `routes/auth.php:19-22`, rute pendaftaran secara eksplisit dinonaktifkan:
    ```php
    // Route::get('register', [RegisteredUserController::class, 'create'])->name('register');
    // Route::post('register', [RegisteredUserController::class, 'store']);
    ```
  - Pada halaman login (`resources/views/auth/login.blade.php`), tidak ada tautan ke formulir registrasi.
  - Sesuai PRD SiswaMart, akun penjual dibuat secara resmi oleh Admin Sekolah melalui menu `admin/penjual/create` atau login SSO Google untuk menjaga integritas siswa penjual.
- **Rekomendasi Tindakan:** **Aman Dihapus (Kategori B)**. Kebijakan bisnis sekolah menetapkan pendaftaran akun penjual dilakukan secara terpusat oleh Admin atau login SSO Google.
- **Status Eksekusi:** **Selesai (ACT-17)**. `RegisteredUserController.php` dan `resources/views/auth/register.blade.php` telah dihapus, deklarasi rute registrasi serta import-nya di `routes/auth.php` telah dibersihkan.

---

### [UC-018] public/deploy-helper.php
- **Path Relatif:** `public/deploy-helper.php` (baris 1–130, 4.842 bytes)
- **Nama Simbol / Objek:** Skrip Utilitas Deployment HTTP
- **Tujuan Awal:** Menjalankan perintah artisan (`migrate`, `cache:clear`, `storage:link`) pada hosting cPanel/DirectAdmin yang tidak memiliki akses SSH Terminal.
- **Bukti & Temuan Keamanan:**
  - Memiliki kunci pengaman statis hardcoded di baris 6: `$secretKey = 'siswamart2026';`.
  - Berada langsung di direktori web-root (`public/`).
  - Bila web server online diakses publik, skrip ini berpotensi dipindai oleh pihak luar.
- **Rekomendasi Tindakan:** **Perlu Verifikasi & Amankan Segera (Kategori B / Keamanan Tinggi)**.
  - Pindahkan ke folder di luar web-root (misalnya `scripts/deployment/deploy-helper.php`).
  - Atau ganti pembacaan secret key menggunakan `env('DEPLOY_HELPER_KEY')` dan aktifkan hanya saat environment `local`/`staging`.

---

### [UC-019] app/Models/CommentModerationLog.php & comment_moderation_logs Table
- **Path Relatif:** `app/Models/CommentModerationLog.php` (baris 1–33, 651 bytes)
- **Nama Simbol / Objek:** Model Eloquent `CommentModerationLog` & Relasi `Ulasan::moderationLogs()`
- **Tujuan Awal:** Pencatatan log jejak audit tindakan moderasi komentar (admin yang memoderasi, status awal, status baru).
- **Bukti Kondisi:**
  - Model dan tabel migrasi (`2026_09_21_000003_create_comment_moderation_logs_table.php`) telah ada di skema database.
  - Namun tidak ada baris kode pada `KomentarController`, `CommentModerationService`, maupun Controller lain yang melakukan `CommentModerationLog::create(...)`.
  - Tercatat dalam ERD dan Kamus Data sebagai bagian dari modul audit moderasi.
- **Rekomendasi Tindakan:** **Pertahankan / Jangan Dihapus (Kategori B / D)**. Model ini dicadangkan untuk penyelesaian fitur logging di Roadmap Fase 3.

---

### [UC-020] app/Models/Whitelist.php, Tabel whitelists, & WhitelistSeeder
- **Path Relatif:** `app/Models/Whitelist.php` (baris 1–40), `database/seeders/WhitelistSeeder.php`
- **Nama Simbol / Objek:** Model `Whitelist`, Tabel Database `whitelists`, dan `WhitelistSeeder`
- **Tujuan Awal:** Menyimpan frasa aman yang dikecualikan dari filter kata kasar guna mencegah *false positive*.
- **Bukti Kondisi:**
  - `database/seeders/DatabaseSeeder.php:20` memanggil `WhitelistSeeder::class` yang mengisi tabel `whitelists`.
  - Namun pada `app/Services/CommentModerationService.php:42` tertulis catatan: `// Daftar kata terlarang tidak dapat dilewati oleh whitelist.`
  - Didokumentasikan secara resmi pada `docs/01-prd/PRD.md:417` (RSK-06) bahwa antarmuka CRUD kamus dan integrasi engine whitelist dijadwalkan pada Roadmap Fase 3.
- **Rekomendasi Tindakan:** **Tidak Direkomendasikan untuk Dihapus (Kategori D)**. Skema dan seeder valid untuk kelanjutan pengembangan jangka menengah.

---

### [UC-021] routes/api.php & app/Http/Controllers/Api/CartController.php
- **Path Relatif:** `routes/api.php` (baris 70–73), `app/Http/Controllers/Api/CartController.php` (baris 1–37)
- **Nama Simbol / Objek:** Endpoint REST API `/api/cart*` (HTTP 501 Not Implemented)
- **Tujuan Awal:** Kontrak API eksplisit untuk fungsionalitas keranjang belanja mobile di masa depan.
- **Bukti Kondisi:**
  - Kontroler secara eksplisit me-return `HTTP 501 Not Implemented` untuk method `index`, `store`, `update`, `destroy`.
  - Berdasarkan ADR-03 (`docs/03-arsitektur/ADR.md:48`), GAP-API-01, dan PRD-157, SiswaMart sengaja memasang rute stub ini sebagai penanda arsitektur bahwa transaksi keranjang belanja internal ditangguhkan ke roadmap masa depan.
- **Rekomendasi Tindakan:** **Tidak Direkomendasikan untuk Dihapus (Kategori D)**. Mempertahankan kontrak API resmi dan spesifikasi OpenAPI/Swagger.

---

### [UC-022] Rute-rute Redireksi Kompatibilitas
- **Path Relatif:**
  - `routes/web.php:14` (`GET /produk`)
  - `routes/web.php:39` (`GET /admin/laporan/produk`)
  - `routes/web.php:40` (`GET /admin/laporan/ulasan`)
  - `routes/web.php:45` (`GET /admin/laporan/pdf`)
- **Nama Simbol / Objek:** Rute Pengalihan URL Warisan (*Compatibility Redirects*)
- **Tujuan Awal:** Menjaga agar tautan lama, riwayat browser, atau bookmark pengguna tetap diarahkan secara anggun ke modul baru.
- **Bukti Kondisi:**
  - Rute-rute ini aktif dieksekusi dan mengembalikan response 301/302 atau meneruskan ke handler terkait.
- **Rekomendasi Tindakan:** **Tidak Direkomendasikan untuk Dihapus (Kategori D)**. Mencegah broken link (HTTP 404) bagi pengguna.

---

### [UC-023] app/Exports/Concerns/StylesSheet.php
- **Path Relatif:** `app/Exports/Concerns/StylesSheet.php` (baris 1–107, 2.794 bytes)
- **Nama Simbol / Objek:** Trait `App\Exports\Concerns\StylesSheet`
- **Tujuan Awal:** Standardisasi warna tabel, border, format Rupiah/tanggal, dan sanitasi formula spreadsheet.
- **Bukti Kondisi:**
  - Meskipun `LaporanAdminExport.php` dan `LaporanPenjualExport.php` telah dikonsolidasikan, trait `StylesSheet` **digunakan secara langsung** oleh engine `App\Support\XlsxWriter` (`use StylesSheet;` di baris 5 dan 14).
  - Metode `sanitizeText()` di trait ini adalah benteng pertahanan utama SiswaMart terhadap kerentanan *CSV/Excel Formula Injection* (`BR-REP-01`, `NFR-011`).
- **Rekomendasi Tindakan:** **Tidak Direkomendasikan untuk Dihapus (Kategori D)**. Komponen keamanan vital yang sedang aktif dipakai.

---

### [UC-024] routes/web.php (Phantom Route penjual.produk.show)
- **Path Relatif:** `routes/web.php:80`
- **Nama Simbol / Objek:** Rute `penjual.produk.show` (`GET /penjual/produk/{produk}`)
- **Tujuan Awal:** Deklarasi resource CRUD produk untuk penjual:
  `Route::resource('produk', \App\Http\Controllers\Penjual\ProdukController::class)->parameters(['produk' => 'produk']);`
- **Bukti Masalah:**
  - `Route::resource` secara default mendaftarkan 7 rute RESTful, termasuk `show`.
  - Pada `App\Http\Controllers\Penjual\ProdukController`, method `show()` **tidak diimplementasikan**.
  - Jika seorang penjual mengetikkan URL `/penjual/produk/1` di browser, sistem akan melempar fatal error: `BadMethodCallException: Method App\Http\Controllers\Penjual\ProdukController::show does not exist.`
- **Rekomendasi Tindakan:** **Perbaikan Non-Penghapusan (Kategori E)**.
  Ubah deklarasi rute menjadi:
  ```php
  Route::resource('produk', \App\Http\Controllers\Penjual\ProdukController::class)
      ->except(['show'])
      ->parameters(['produk' => 'produk']);
  ```

---

### [UC-025] Unused PHP Import Statements
- **Path Relatif & Baris:**
  1. `app/Http/Controllers/Admin/DashboardController.php:8-12`
     - `use App\Models\Ulasan;` (baris 8)
     - `use App\Notifications\AdminActivityNotification;` (baris 9)
     - `use Carbon\Carbon;` (baris 10)
     - `use Illuminate\Http\Request;` (baris 11)
     - `use Illuminate\Support\Facades\DB;` (baris 12)
  2. `app/Http/Controllers/Admin/LaporanController.php:10`
     - `use Carbon\Carbon;` (baris 10)
  3. `app/Http/Controllers/Admin/UlasanController.php:7`
     - `use Illuminate\Http\Request;` (baris 7)
  4. `app/Http/Controllers/Public/HomeController.php:10`
     - `use Illuminate\Http\Request;` (baris 10)
  5. `app/Services/LaporanPenjualService.php:10`
     - `use Illuminate\Support\Collection;` (baris 10)
  6. `routes/auth.php:10`
     - `use App\Http\Controllers\Auth\RegisteredUserController;` (baris 10)
  7. `tests/Feature/LaporanRekapitulasiUnifiedExportTest.php:9`
     - `use App\Models\Ulasan;` (baris 9)
- **Bukti:** Terdeteksi oleh static analyzer dan Laravel Pint fixer `no_unused_imports`.
- **Rekomendasi Tindakan:** **Perbaikan Non-Penghapusan / Rapikan (Kategori E)**.

---

### [UC-026] tests/Feature/Auth/PasswordResetTest.php (Unused Closure Captures)
- **Path Relatif:** `tests/Feature/Auth/PasswordResetTest.php` (baris 48 dan 83)
- **Nama Simbol:** Variabel Closure `$admin` dan `$seller`
- **Bukti:**
  - Baris 48:
    ```php
    Notification::assertSentTo(
        $admin,
        ResetPasswordNotification::class,
        function (ResetPasswordNotification $notification) use ($admin) {
            return ! empty($notification->token);
        }
    );
    ```
    Variabel `$admin` di-pass ke `use ($admin)`, namun di dalam closure hanya `$notification->token` yang dievaluasi.
  - Baris 83 memiliki pola yang identik untuk `$seller`.
- **Rekomendasi Tindakan:** **Perbaikan Non-Penghapusan / Rapikan (Kategori E)**. Hapus `use ($admin)` dan `use ($seller)`.

---

### [UC-027] tests/Feature/AdminAndPenjualFilteringAndPaginationTest.php (Brittle Hardcoded ID Test)
- **Path Relatif:** `tests/Feature/AdminAndPenjualFilteringAndPaginationTest.php` (baris 273–280)
- **Nama Simbol:** `test_every_existing_registered_seller_has_at_least_10_products()`
- **Bukti:**
  - Test ini mengeksekusi: `Penjual::whereIn('id', [4, 5, 6, 7])->withCount('produk')->get();` dan mengasumsikan setiap toko memiliki minimal 10 produk.
  - Pada lingkungan pengujian lokal saat ini, penjual ID 4 ("Edmund's Hearth") hanya memiliki 1 produk, sehingga menyebabkan 1 test failure pada test runner (82 passed, 1 failed).
- **Rekomendasi Tindakan:** **Perbaikan Non-Penghapusan (Kategori E)**. Buat data penjual secara mandiri menggunakan Factory atau `DatabaseTransactions` alih-alih bergantung pada state ID hardcoded database lokal.

---

### [UC-028] storage/app/*.tmp & Konfigurasi .gitignore
- **Path Relatif:** `storage/app/rek*.tmp` (26 berkas)
- **Nama Simbol:** Berkas Sementara Hasil Pengujian Ekspor XLSX (`tempnam()`)
- **Bukti:**
  - Ketika `LaporanController` mengekspor buku kerja Excel, digunakan fungsi `tempnam(storage_path('app'), 'rekap-...')`.
  - Berkas sementara ini terakumulasi di folder `storage/app/` saat test suite dijalankan dan belum terdaftar dalam `.gitignore`.
- **Rekomendasi Tindakan:** **Perbaikan Non-Penghapusan (Kategori E)**. Tambahkan `/storage/app/*.tmp` ke berkas `.gitignore`.

---

## 4. Rencana Eksekusi Bertahap (Action Checklist)

Checklist ini disusun secara terstruktur berdasarkan tingkat risiko agar dapat ditinjau dan disetujui bertahap:

### Tahap 1 — Pembersihan Berkas Aman (Kategori A)
> **Tingkat Risiko:** Sangat Rendah. Tidak mempengaruhi fungsionalitas aplikasi yang sedang berjalan.
- [x] **ACT-01** (UC-001): Hapus `resources/views/welcome.blade.php` (~72 KB). *(Selesai)*
- [x] **ACT-02** (UC-002): Hapus `resources/views/dashboard.blade.php` (528 B). *(Selesai)*
- [x] **ACT-03** (UC-003): Hapus `app/Services/BadWordFilterService.php`. *(Selesai)*
- [x] **ACT-04** (UC-004): Hapus `config/moderation.php`. *(Selesai)*
- [x] **ACT-05** (UC-005): Hapus `public/siswamart-icons.zip` (~1.99 MB). *(Selesai)*
- [x] **ACT-06** (UC-006): Hapus direktori `public/assets/icons/` beserta 33 file PNG di dalamnya. *(Selesai)*
- [x] **ACT-07** (UC-007): Hapus `public/email-preview.html`. *(Selesai)*
- [x] **ACT-08** (UC-008): Hapus `resources/views/admin/ulasan/index.blade.php`. *(Selesai)*
- [x] **ACT-09** (UC-009): Hapus `resources/views/admin/laporan/produk.blade.php`. *(Selesai)*
- [x] **ACT-10** (UC-010): Hapus `resources/views/admin/laporan/ulasan.blade.php`. *(Selesai)*
- [x] **ACT-11** (UC-011): Hapus `resources/views/public/produk/index.blade.php`. *(Selesai)*
- [x] **ACT-12** (UC-012): Hapus method `index()` pada `app/Http/Controllers/Public/ProdukController.php`. *(Selesai)*
- [x] **ACT-13** (UC-013): Hapus `resources/views/components/dashboard-icon.blade.php`. *(Selesai)*
- [x] **ACT-14** (UC-014): Hapus `resources/views/components/ui-icon.blade.php`. *(Selesai)*

### Tahap 2 — Konsolidasi Implementasi Lama (Kategori C)
> **Tingkat Risiko:** Rendah. Membersihkan modul yang sudah digantikan oleh arsitektur baru.
- [x] **ACT-15** (UC-015): Hapus `app/Http/Controllers/Admin/UlasanController.php` (sudah sepenuhnya digantikan oleh `Admin\KomentarController`). *(Selesai)*

### Tahap 3 — Peninjauan & Keputusan Bisnis (Kategori B)
> **Tingkat Risiko:** Sedang. Membutuhkan konfirmasi kebutuhan roadmap.
- [ ] **ACT-16** (UC-016): Putuskan nasib `Admin\AdminController`: Daftarkan rute & buat view, atau hapus controller jika admin dikelola via terminal.
- [x] **ACT-17** (UC-017): Hapus fitur self-registration (`RegisteredUserController`, `auth/register.blade.php`, dan rute `register` di `routes/auth.php`) sesuai keputusan bisnis. *(Selesai)*
- [ ] **ACT-18** (UC-018): Amankan `public/deploy-helper.php`: Pindahkan keluar direktori publik (`scripts/deploy/`) atau batasi environment.

### Tahap 4 — Perbaikan Non-Penghapusan & Refactoring (Kategori E)
> **Tingkat Risiko:** Sangat Rendah. Meningkatkan stabilitas dan kebersihan kode.
- [ ] **ACT-19** (UC-024): Pasang `->except(['show'])` pada `Route::resource('produk', ...)` penjual di `routes/web.php:80`.
- [ ] **ACT-20** (UC-025): Hapus unused `use` import statements pada ke-6 file yang teridentifikasi.
- [ ] **ACT-21** (UC-026): Hapus unused closure captures (`use ($admin)`, `use ($seller)`) pada `PasswordResetTest.php`.
- [ ] **ACT-22** (UC-027): Refaktor assertion pada `test_every_existing_registered_seller_has_at_least_10_products` agar tidak brittle.
- [ ] **ACT-23** (UC-028): Tambahkan `/storage/app/*.tmp` ke `.gitignore` dan bersihkan berkas temporary lama.

### Tahap 5 — Verifikasi Regresi Paska Eksekusi
- [ ] **ACT-24**: Jalankan `php artisan route:list` untuk memastikan integritas routing.
- [ ] **ACT-25**: Jalankan `php vendor/bin/phpunit` untuk memastikan seluruh fitur lulus uji tanpa regresi.
- [ ] **ACT-26**: Jalankan `php vendor/bin/pint --test` untuk verifikasi standar kode.

---

## 5. Log Perintah Audit yang Dijalankan

Berikut adalah daftar perintah pemeriksaan aktual yang dijalankan selama audit:

1. **Pemeriksaan Versi Framework:**
   - Perintah: `php artisan --version`
   - Hasil: `Laravel Framework 13.38.0` (Status: OK).
2. **Audit Routing Aplikasi:**
   - Perintah: `php artisan route:list --json`
   - Hasil: Berhasil memetakan seluruh rute aktif, mendeteksi phantom route `penjual.produk.show`, serta rute redirect kompatibilitas.
3. **Pemeriksaan Test Suite (Automated Testing):**
   - Perintah: `php vendor/bin/phpunit`
   - Hasil: 83 pengujian, 82 passed, 496 assertions, 1 failed (`test_every_existing_registered_seller_has_at_least_10_products` akibat brittle ID checking pada DB lokal).
4. **Analisis Statis & Linting (Pint):**
   - Perintah: `php vendor/bin/pint --test`
   - Hasil: Mengidentifikasi unused imports (`no_unused_imports`) dan format inconsistencies pada berkas PHP.
5. **Pemeriksaan Git & Working Tree:**
   - Perintah: `git status --short`
   - Hasil: Memastikan tidak ada berkas sumber yang berubah selama proses audit, serta mendeteksi berkas temporer `storage/app/*.tmp`.

---

## 6. Cakupan Pemeriksaan

### Area yang Diperiksa Penuh
- Seluruh direktori `app/` (Controllers, Models, Services, Notifications, Providers, Support, View Components).
- Seluruh berkas routing di `routes/` (`web.php`, `api.php`, `auth.php`, `console.php`).
- Seluruh berkas template di `resources/views/` (Admin, Penjual, Public, Auth, Layouts, Components, Errors, Emails, Exports).
- Seluruh aset di `public/` (CSS, gambar, ikon, zip, build manifest, helper scripts).
- Direktori `config/` dan berkas konfigurasi utama.
- Direktori `database/` (Migrations, Seeders, Factories).
- Seluruh file pengujian di `tests/Feature/`.

### Batasan / Area yang Belum / Tidak Diperiksa
- Folder `vendor/` dan `node_modules/` (dikecualikan sesuai batasan audit agar tidak menyentuh dependensi pihak ketiga).
- Berkas kompilasi bundle Vite di `public/build/assets/` (dianalisis dari sisi manifest, bukan isi internal minified bundle).
- Berkas `.env` (nilai secret tidak pernah diekstrak atau dicatat, sesuai aturan keamanan).

---
*Laporan ini siap digunakan sebagai acuan pengambilan keputusan sebelum tahap eksekusi refactoring & dead code cleanup.*
