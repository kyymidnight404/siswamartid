# ANALISIS KESENJANGAN (GAP ANALYSIS)
# SiswaMart — Platform Promosi & Katalog Produk Sekolah

Dokumen ini memuat audit teknis dan analisis kesenjangan sistemik (*gap analysis*) pada aplikasi **SiswaMart** berdasarkan inspeksi langsung kode sumber lokal (`c:\laragon\www\siswamartid`) per **25 Oktober 2026**.

Analisis ini membandingkan **perilaku ideal yang diharapkan (*expected behavior*)** dengan **bukti implementasi aktual di kode sumber (*current evidence*)**, mengidentifikasi tingkat keparahan (*severity*), menetapkan prioritas perbaikan, serta memberikan rekomendasi tindakan konkret bagi pengembang dan pemangku kepentingan sekolah.

---

## 1. TABEL UTAMA ANALISIS KESENJANGAN (GAP ANALYSIS MATRIX)

| ID Gap | Fitur / Isu | Perilaku yang Diharapkan | Bukti Kondisi Aktual di Kode Sumber | Kesenjangan (Gap) | Prioritas | Rekomendasi Tindakan |
|---|---|---|---|---|---|---|
| **GAP-DB-01** | Sinkronisasi Skema Database (*Pending Migrations*) | Seluruh migrasi database yang ada di repositori berstatus *Ran* dan struktur tabel di basis data aktif selaras 100% dengan kebutuhan kode dan model Eloquent. | Perintah `php artisan migrate:status` mencatat 5 migrasi bertanggal 15 Oktober 2026 berstatus **Pending**: `2026_10_15_000001` hingga `000005`. Basis data aktif belum memiliki kolom `username`, `google_id`, `pending_action`, dan `pending_changes`. | Terjadi ketidakselarasan skema (*schema drift*). Pengujian otomatis `GoogleLoginTest` mengalami 8 error `SQLSTATE 42S22: Column not found: 'username'/'google_id'`. | **P0 (Kritis)** | Cadangkan (*backup*) basis data aktif, kemudian jalankan `php artisan migrate --force` pada sesi terawasi untuk mengeksekusi 5 migrasi pending. |
| **GAP-SEC-01** | Standarisasi Otorisasi Kepemilikan Data (*Policies*) | Otorisasi aksi edit/hapus produk menggunakan *Laravel Authorization Policies* (`@can` / `$this->authorize('update', $produk)`). | Otorisasi saat ini dilakukan secara manual di dalam method controller menggunakan helper privat: `$penjualId = $this->getPenjualId(); abort_unless($produk->penjual_id === $penjualId, 403);`. | Tidak adanya lapisan otorisasi terpusat; rentan terhadap kelalaian otorisasi jika ada method baru atau endpoint API baru yang dibuat. | **P1 (Tinggi)** | Buat kelas `App\Policies\ProdukPolicy` dan `App\Policies\PenjualPolicy`, daftarkan di `AppServiceProvider`, dan panggil `$this->authorize()` di seluruh controller. |
| **GAP-AUTH-01** | Pendaftaran Akun Penjual Mandiri | Siswa yang ingin berwirausaha memiliki formulir permohonan pendaftaran penjual daring (*Online Seller Application*) yang dapat diisi mandiri untuk diverifikasi sekolah. | Rute `register` publik dikomentari di `routes/auth.php#L19-L22`. Akun penjual hanya dapat diinput secara manual satu per satu oleh Administrator melalui form `/admin/penjual/create`. | Beban administratif guru/admin tinggi jika ada puluhan siswa yang ingin berjualan; tidak ada jalur pengajuan online terstruktur bagi siswa. | **P2 (Sedang)** | Buat alur pendaftaran permohonan toko (*Store Application Flow*): siswa mengisi permohonan mandiri, data masuk ke antrean verifikasi Admin sebelum akun aktif. |
| **GAP-AUTH-02** | Google OAuth untuk Pendaftaran Akun Baru | Siswa dapat mendaftar langsung menggunakan akun Google resmi sekolah (`@sekolah.sch.id`). | `GoogleAuthController.php#L100-L104` menolak login jika email belum terdaftar di basis data: *"Email Google ini belum terdaftar di SiswaMart. Hubungi Admin..."*. | Google OAuth saat ini hanya berfungsi sebagai SSO untuk akun yang sudah di-input Admin secara manual, bukan mekanisme registrasi awal. | **P2 (Sedang)** | Sediakan konfigurasi opsional: jika domain email berasal dari domain resmi sekolah (`@sekolah.sch.id`), izinkan pembuatan akun penjual/siswa baru secara terarah. |
| **GAP-STK-01** | Manajemen Stok & Otomasi Ketersediaan | Stok produk memiliki jumlah numerik dan berkurang otomatis saat terjadi pembelian. | Kolom `stok` berupa enum string (`tersedia`, `po`, `habis`). Tidak ada pengurangan otomatis karena transaksi berlangsung di WhatsApp di luar kendali sistem web. | Penjual wajib mengingat dan mengubah status stok secara manual dari dasbor jika jajanan telah habis terjual di kelas. | **P2 (Sedang)** | Pertahankan model WhatsApp, namun tambahkan tombol aksi cepat *One-Click Toggle Habis* pada tabel index produk penjual tanpa harus membuka form edit. |
| **GAP-API-01** | Ketersediaan Endpoint REST API Cart | Endpoint API keranjang belanja (`/api/cart`) menyediakan fungsionalitas penyimpanan keranjang bagi aplikasi mobile client. | `routes/api.php` mendaftarkan rute cart, namun `App\Http\Controllers\Api\CartController` mengembalikan respon statis `HTTP 501 Not Implemented`. | Menggantungnya fungsionalitas API; berpotensi membingungkan pengembang pihak ketiga atau aplikasi mobile yang mengonsumsi endpoint tersebut. | **P3 (Rendah)** | Bersihkan rute keranjang belanja dari `routes/api.php` dan tegaskan dalam dokumentasi API bahwa SiswaMart berfokus pada model etalase non-keranjang. |
| **GAP-API-02** | Fitur Manajemen Produk pada REST API | REST API menyediakan endpoint CRUD bagi penjual untuk mengunggah dan mengelola produk via aplikasi seluler khusus. | `routes/api.php` hanya menyediakan endpoint publik (baca produk/kategori) dan ulasan. Tidak ada endpoint bagi penjual untuk membuat atau mengedit produk via API. | Penjual hanya dapat mengelola produk melalui antarmuka web Laravel Blade di browser. | **P3 (Rendah)** | Bangun `App\Http\Controllers\Api\SellerProductController` yang mengadopsi mekanisme `pending_changes` untuk integrasi mobile di masa depan. |
| **GAP-NOTIF-01** | Pengiriman Notifikasi ke Penjual (*Seller Alerts*) | Penjual menerima notifikasi instan ketika produknya disetujui, ditolak, atau ada ulasan baru yang masuk. | `AdminNotificationService.php` hanya mengirimkan notifikasi ke akun ber-role `admin`. Tidak ada mekanisme notifikasi database atau email yang dikirim ke akun `penjual`. | Penjual tidak mengetahui jika produknya disetujui/ditolak kecuali membuka dasbor web secara berkala. | **P2 (Sedang)** | Perluas sistem notifikasi agar mencakup role penjual saat Admin mengubah status produk atau saat ada ulasan baru pada produknya. |
| **GAP-UX-01** | Halaman Error Kustom (404, 403, 500) | Pengguna yang mengakses produk tidak aktif atau halaman terlarang disajikan halaman ramah berkarakter SiswaMart. | Berkas view kustom untuk error (`resources/views/errors/`) belum diisi dengan template Neubrutalism SiswaMart; saat ini mengandalkan halaman default framework. | Pengalaman visual terputus jika pembeli membuka tautan produk yang sudah dihapus atau tidak aktif. | **P3 (Rendah)** | Buat tampilan error kustom (`404.blade.php`, `403.blade.php`, `500.blade.php`) menggunakan token desain visual SiswaMart (krem, border ink tebal, ikon ramah). |

---

## 2. ANALISIS MENDALAM KESENJANGAN SKEMA BASIS DATA & MIGRASI

### Akar Masalah (*Root Cause Analysis*)
Pada tanggal 15 Oktober 2026, tim pengembang menambahkan 5 file migrasi untuk merapikan skema database agar selaras dengan `docs/ERD.md`:
1. `2026_10_15_000001_align_local_schema_with_erd.php`
2. `2026_10_15_000002_normalize_erd_status_values.php`
3. `2026_10_15_000003_remove_admin_creator_from_penjual.php`
4. `2026_10_15_000004_add_product_moderation_requests.php`
5. `2026_10_15_000005_add_google_id_to_users_table.php`

Namun, migrasi ini **belum pernah dieksekusi** pada database MySQL lokal `siswamartid`.

### Bukti Dampak pada Kode Sumber & Runtime:
1. **Defensive Coding di Controller:**
   Pengembang menyadari hal ini sehingga membuat kode bersyarat (*conditional guard*):
   ```php
   // Di Admin\ProdukController.php & Penjual\ProdukController.php:
   $hasPendingAction = Schema::hasColumn('produk', 'pending_action');
   
   // Di User.php:
   if (! Schema::hasColumn('users', 'username')) {
       return;
   }
   ```
2. **Kegagalan Test Suite Otomatis:**
   File uji `tests/Feature/Auth/GoogleLoginTest.php` tidak menggunakan pengecekan bersyarat dan langsung mengeksekusi:
   ```php
   User::create([
       'name' => 'Admin Penguji',
       'username' => 'admin_test_123',
       'email' => 'admin@example.com',
       ...
   ]);
   ```
   Hal ini memicu error SQL fatal: `SQLSTATE[42S22]: Column not found: 1054 Unknown column 'username' in 'field list'` dan `Unknown column 'google_id' in 'where clause'`.

### Mitigasi yang Direkomendasikan:
1. Verifikasi integritas data eksisting: pastikan tidak ada data `penjual` dengan NISN duplikat atau `user_id` duplikat yang dapat menggagalkan pembuatan indeks unik pada migrasi `000001`.
2. Jalankan perintah migrasi:
   ```bash
   php artisan migrate
   ```
3. Jalankan kembali test suite untuk membuktikan kelulusan 100%:
   ```bash
   php vendor/phpunit/phpunit/phpunit tests/Feature/Auth/GoogleLoginTest.php
   ```

---

## 3. ANALISIS KESENJANGAN AUTENTIKASI & REGISTRASI

```mermaid
graph LR
    subgraph Kondisi Saat Ini (As-Is)
        A1[Siswa Ingin Jualan] -->|Hubungi Guru Manual| A2[Admin Buka /admin/penjual/create]
        A2 -->|Input NISN & Toko Manual| A3[Akun Dibuat]
        A3 -->|Kredensial Dibagikan Manual| A4[Siswa Login]
    end

    subgraph Usulan Perbaikan (To-Be)
        B1[Siswa Ingin Jualan] -->|Buka Web /daftar-toko| B2[Form Pengajuan Mandiri Siswa]
        B2 -->|Status: Pending Verifikasi| B3[Admin Tinjau NISN & Stan di Dasbor]
        B3 -->|Admin Klik Approve| B4[Akun Otomatis Aktif & Siswa Menerima Akses]
    end
```

### Masalah pada Alur Saat Ini:
1. **Bottleneck pada Administrator:** Seluruh data profil penjual (mulai dari nama toko, deskripsi, nomor WA, hingga alamat) harus diketikkan langsung oleh Admin.
2. **Penyebaran Kredensial Berisiko:** Admin menentukan kata sandi awal secara manual dan harus menyerahkannya secara offline kepada siswa, yang berisiko lupa diganti atau disalahgunakan pihak lain.
3. **Google OAuth Terbatas:** Fitur Google login tidak dapat digunakan untuk memudahkan pendaftaran mandiri warga sekolah, melainkan murni SSO bagi akun yang sudah ada.

---

## 4. ANALISIS KESENJANGAN OTORISASI (AUTHORIZATION POLICIES)

### Kondisi Saat Ini:
Pada `App\Http\Controllers\Penjual\ProdukController.php`:
```php
public function edit(Produk $produk): View
{
    $penjualId = $this->getPenjualId();
    abort_unless($produk->penjual_id === $penjualId, 403, 'Akses ditolak.');
    ...
}
```
Pengecekan di atas ditulis berulang-ulang di method `edit()`, `update()`, dan `destroy()`. Jika di masa mendatang seorang pengembang menambahkan method baru (misal: `duplicateProduct()` atau `toggleFeatured()`), ada risiko tinggi pengecekan manual ini terlewatkan (*Broken Access Control* / OWASP Top 10 A01:2021).

### Usulan Implementasi Policy:
Buat `app/Policies/ProdukPolicy.php`:
```php
namespace App\Policies;

use App\Models\Produk;
use App\Models\User;

class ProdukPolicy
{
    public function view(User $user, Produk $produk): bool
    {
        return $user->role === 'admin' || ($user->role === 'penjual' && $user->penjual?->id === $produk->penjual_id);
    }

    public function update(User $user, Produk $produk): bool
    {
        return $user->role === 'penjual' && $user->penjual?->id === $produk->penjual_id;
    }

    public function delete(User $user, Produk $produk): bool
    {
        return $user->role === 'admin' || ($user->role === 'penjual' && $user->penjual?->id === $produk->penjual_id);
    }
}
```
Dan gunakan pada Controller:
```php
$this->authorize('update', $produk);
```

---

## 5. ANALISIS KESENJANGAN MANAJEMEN TRANSAKSI & STOK

### Karakteristik Model Transaksi SiswaMart:
SiswaMart tidak mencatat transaksi pembelian di dalam basis data (tidak ada tabel `orders`, `transactions`, atau `order_items`).

### Keterbatasan yang Muncul:
1. **Tidak Ada Riwayat Transaksi Finansial:** Laporan penjual hanya merekap jumlah orang yang mengklik tautan WhatsApp (`produk_views_logs.type = 'whatsapp'`), bukan berapa nominal omzet rupiah yang benar-benar terjual.
2. **Keterlambatan Pembaruan Stok:** Ketika 10 porsi jajanan habis dibeli langsung di stan sekolah, status produk di web tetap bertuliskan *Ready (Tersedia)* sampai siswa pemilik toko sempat membuka dasbor web untuk mengubahnya menjadi *Habis*.

### Rekomendasi Pragmatis (Tanpa Mengubah Arsitektur WhatsApp):
- Menghadirkan antarmuka cepat (*Quick Toggle Button*) di dasbor penjual menggunakan AJAX/Fetch API sehingga siswa dapat mengubah status produk menjadi "Habis" hanya dalam 1 detik lewat ponsel mereka tanpa memuat ulang formulir panjang.
- Menambahkan kolom keterangan jam operasional stan kantin (misal: "Buka saat Istirahat 1: 09.30 - 10.00 WIB").

---

## 6. MATRIKS PRIORITAS PERBAIKAN & RENCANA TINDAKAN (ACTION PLAN)

```mermaid
quadrantChart
    title Matriks Prioritas Perbaikan SiswaMart
    x-axis Kompleksitas Teknis Rendah --> Kompleksitas Teknis Tinggi
    y-axis Dampak Rendah --> Dampak Tinggi
    quadrant-1 Rencana Strategis (P1/P2)
    quadrant-2 Prioritas Utama Segera (P0)
    quadrant-3 Tugas Sekunder (P3)
    quadrant-4 Cepat Diselesaikan (P2)
    "Eksekusi Migrasi Pending (P0)": [0.25, 0.95]
    "Standarisasi Laravel Policy (P1)": [0.45, 0.85]
    "Notifikasi ke Penjual (P2)": [0.40, 0.70]
    "Quick-Toggle Stok Habis (P2)": [0.30, 0.65]
    "Halaman Error Kustom (P3)": [0.20, 0.35]
    "Pembersihan Rute Cart 501 (P3)": [0.15, 0.25]
    "Alur Pengajuan Toko Online (P2)": [0.75, 0.80]
```

### Rangkuman Jadwal Tindakan:
1. **Tindakan Segera (Sprint Berjalan / P0):**
   - Menjalankan migrasi `2026_10_15_000001` hingga `000005`.
   - Menjalankan ulang seluruh test suite PHPUnit hingga 34 skenario uji lulus 100%.
2. **Tindakan Jangka Pendek (Sprint Berikutnya / P1 & P2):**
   - Mengimplementasikan `ProdukPolicy` dan mengeliminasi validasi manual di controller.
   - Menambahkan tombol cepat *Tandai Habis* pada tabel inventaris penjual.
   - Mengirim notifikasi database kepada penjual saat produk mereka disetujui atau ditolak admin.
3. **Tindakan Jangka Menengah (P3 & Peningkatan UI):**
   - Menghapus rute `/api/cart` untuk menjaga integritas dokumentasi arsitektur.
   - Merancang template halaman error ramah `errors/404.blade.php` dan `errors/403.blade.php`.

---
*Dokumen Analisis Kesenjangan ini disusun berdasarkan bukti faktual kode repositori SiswaMart per 25 Oktober 2026.*
