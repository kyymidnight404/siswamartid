# PRD Mobile App — SiswaMart

| Atribut | Detail |
|---|---|
| Produk | SiswaMart Mobile |
| Versi dokumen | 1.0.0 |
| Status | Draft — menunggu persetujuan desain dan product owner |
| Platform | Android dan iOS |
| Framework | Flutter |
| Backend | Existing Laravel 13 |
| Database | Existing MariaDB / MySQL |
| Kanal pemesanan | Direct-to-WhatsApp |
| Sumber kebenaran | Repository dan database SiswaMart yang sudah berjalan |

## 1. Ringkasan produk

SiswaMart Mobile adalah aplikasi katalog dan promosi produk siswa yang berjalan di Android dan iOS. Aplikasi membantu pengunjung menemukan produk, melihat informasi produk dan penjual, lalu menghubungi penjual melalui WhatsApp.

Aplikasi mobile merupakan client terpisah dari website, tetapi tidak memiliki backend atau database terpisah. Laravel tetap menjadi sumber kebenaran untuk produk, kategori, penjual, status publikasi, harga, dan informasi terkait.

## 2. Visi dan tujuan

**Visi:** membuat produk dan usaha siswa lebih mudah ditemukan melalui pengalaman mobile yang cepat, jelas, dan ramah pengguna.

Tujuan:
1. Mempermudah penemuan produk melalui katalog, pencarian, kategori, dan filter.
2. Menyajikan detail produk serta informasi toko yang dapat dipercaya.
3. Menghubungkan calon pembeli dengan penjual melalui WhatsApp.
4. Menggunakan backend dan database SiswaMart yang sudah ada.
5. Menjaga konsistensi identitas visual SiswaMart di website dan aplikasi.

## 3. Target pengguna

| Persona | Kebutuhan utama |
|---|---|
| Pengunjung / siswa | Menemukan produk dan melihat informasi dengan cepat |
| Calon pembeli | Memeriksa harga, stok, toko, lalu menghubungi penjual |
| Penjual | Produk yang sudah dipublikasikan tampil dengan informasi yang benar |

Versi pertama tidak menyediakan panel penjual atau panel admin di aplikasi. Penjual dan admin tetap menggunakan website yang ada.

## 4. Ruang lingkup

### 4.1 In scope — MVP

| ID | Fitur | Prioritas |
|---|---|---|
| MOB-FR-001 | Beranda dengan produk populer, produk terbaru, dan kategori | P0 |
| MOB-FR-002 | Katalog produk dengan pagination | P0 |
| MOB-FR-003 | Pencarian nama, deskripsi, dan nama toko | P0 |
| MOB-FR-004 | Filter kategori dan rentang harga | P0 |
| MOB-FR-005 | Pengurutan produk | P0 |
| MOB-FR-006 | Detail produk dan produk terkait | P0 |
| MOB-FR-007 | Profil penjual dan daftar produknya | P0 |
| MOB-FR-008 | Aksi hubungi penjual melalui WhatsApp | P0 |
| MOB-FR-009 | Loading, empty, error, dan offline state | P0 |
| MOB-FR-010 | Deep link ke halaman produk | P1 |
| MOB-FR-011 | Favorit lokal tanpa akun | P1 |
| MOB-FR-012 | Halaman informasi, kontak, dan kebijakan privasi | P1 |

### 4.2 Out of scope

- Keranjang belanja, checkout, dan payment gateway.
- Pembayaran atau pemrosesan transaksi di aplikasi.
- Chat internal.
- Registrasi atau login pengunjung.
- Dashboard admin dan dashboard penjual.
- Pengelolaan produk dari aplikasi mobile.
- Sinkronisasi favorit antarperangkat.
- Notifikasi push pada MVP.

Pemesanan tetap dilakukan melalui komunikasi langsung dengan penjual di WhatsApp.

## 5. Struktur navigasi dan layar

Bottom navigation yang disarankan:

1. **Beranda** — pengantar, kategori, produk populer, dan produk terbaru.
2. **Jelajahi** — katalog, pencarian, filter, dan pengurutan.
3. **Favorit** — produk yang disimpan secara lokal; dapat menjadi P1.
4. **Info** — tentang SiswaMart, cara penggunaan, kontak, dan kebijakan privasi.

Layar pendukung:
- Detail produk.
- Profil penjual.
- Hasil pencarian.
- Filter produk.
- Empty state dan error state.

### 5.1 Ketentuan beranda

Repository saat ini belum mempunyai entitas banner maupun endpoint banner publik. Halaman web beranda menyusun hero dari produk terbaru dan rekomendasi dari rating serta jumlah tayangan. Karena itu, MVP mobile menggunakan kategori, produk populer, dan produk terbaru; jangan membuat banner dinamis seolah-olah sudah tersedia di backend.

Beranda dapat mengambil data secara paralel dari:
- GET /api/v1/categories
- GET /api/v1/products?sort=popular&per_page=5
- GET /api/v1/products?sort=latest&per_page=5

Jika kebutuhan agregasi meningkat, endpoint home khusus dapat dipertimbangkan pada iterasi berikutnya.

## 6. Kebutuhan fungsional

### MOB-FR-001 — Beranda
- Menampilkan kategori dan kelompok produk yang berasal dari API.
- Memiliki pull-to-refresh.
- Dapat menampilkan sebagian konten walaupun salah satu request section gagal.
- Tidak menampilkan banner promosi yang belum didukung backend.

### MOB-FR-002 — Katalog
- Menampilkan foto, nama produk, harga, label promosi, status stok, nama toko, dan rating jika tersedia.
- Menggunakan pagination.
- Memuat halaman berikutnya tanpa menghapus hasil sebelumnya.
- Menampilkan fallback ketika gambar tidak tersedia.

### MOB-FR-003 — Pencarian
- Mencari berdasarkan nama produk, deskripsi, atau nama toko melalui backend.
- Menggunakan debounce.
- Menangani hasil kosong dan kegagalan jaringan.
- Mengabaikan response pencarian lama yang datang setelah pencarian terbaru.

### MOB-FR-004 — Filter dan pengurutan
- Mendukung kategori, rentang harga minimum/maksimum, serta sort.
- Filter dapat dikombinasikan dengan pencarian.
- Perubahan filter mengatur ulang pagination.
- Pilihan sort mengikuti parameter yang benar-benar didukung API.

### MOB-FR-005 — Detail produk
- Menampilkan foto, nama, harga, deskripsi, stok, label promosi, rating, ulasan, kategori, dan profil ringkas penjual sesuai data API.
- Menampilkan produk terkait jika tersedia.
- Menangani produk yang tidak lagi publik dengan pesan yang jelas.

### MOB-FR-006 — Profil penjual
- Menampilkan nama toko, deskripsi, lokasi kelas, foto, status toko, dan jumlah produk aktif.
- Daftar produk penjual dimuat menggunakan endpoint katalog dengan parameter seller_id agar pagination tetap tersedia.
- Nomor WhatsApp tidak dibaca dari profil publik.

### MOB-FR-007 — WhatsApp
- Aplikasi meminta URL WhatsApp dari endpoint produk.
- Aplikasi membuka URL yang dikembalikan backend menggunakan mekanisme external URL launcher.
- Backend menentukan nomor dan pesan awal sesuai kondisi toko serta stok.
- Pesan tidak pernah dikirim otomatis; pengguna tetap mengirim sendiri.
- Jika endpoint mengembalikan error, aplikasi menampilkan pesan dan tidak membuka URL yang dibentuk sendiri.

### MOB-FR-008 — Favorit (P1)
- Menyimpan ID produk secara lokal.
- Tidak memerlukan akun.
- Produk yang tidak lagi ditemukan dari API ditandai atau dihapus dengan penjelasan yang sesuai.
- Tidak mengirim data favorit ke backend.

## 7. Persyaratan non-fungsional

| Area | Persyaratan |
|---|---|
| Performa | Pagination, thumbnail, image caching, debounce pencarian |
| Reliabilitas | Semua layar API memiliki loading, success, empty, dan error state |
| Offline | Tampilkan informasi koneksi dan pertahankan cache/favorit yang aman |
| Keamanan | HTTPS, tidak menanam secret server di aplikasi, validasi tetap di backend |
| Privasi | Tidak mengumpulkan data pribadi yang tidak diperlukan |
| Aksesibilitas | Kontras memadai, area sentuh nyaman, semantic label |
| Kompatibilitas | Layout adaptif untuk ukuran layar Android dan iOS |

Target awal yang perlu divalidasi melalui pengujian: waktu muat layar utama di bawah 3 detik pada koneksi normal dan crash-free sessions minimal 99%.

## 8. Design direction

Aplikasi mengikuti karakter SiswaMart: modern, ringan, friendly, dan mobile-first.

Arah warna yang diminta untuk desain mobile adalah **biru, putih, dan oranye**. Terdapat perbedaan dengan design system repository saat ini yang mendokumentasikan biru-slate dan aksen kuning. Product owner perlu menyetujui token final sebelum UI diimplementasikan agar tidak terjadi dua identitas visual yang berbeda.

Panduan:
- Gunakan kartu dan tombol dengan sudut membulat.
- Utamakan keterbacaan harga dan nama produk.
- Gunakan oranye sebagai aksen/CTA, bukan untuk seluruh permukaan.
- Hindari layar yang terlalu padat.
- Gunakan skeleton/loading state yang konsisten.
- Jangan hanya mengandalkan warna untuk status.

## 9. Analitik dan privasi

Event yang dapat dipertimbangkan setelah evaluasi privasi:
- app_open
- catalog_view
- product_search
- category_filter
- product_view
- seller_view
- whatsapp_click
- favorite_add / favorite_remove

Jangan mengirim nomor telepon pribadi, isi pesan WhatsApp, atau informasi sensitif pengguna ke analytics. Analytics SDK bersifat opsional dan harus mengikuti kebijakan privasi.

## 10. Kriteria penerimaan MVP

MVP dapat diterima apabila:
1. Build aplikasi berjalan pada Android dan iOS.
2. Pengguna dapat melihat beranda, kategori, katalog, dan detail produk.
3. Pencarian, filter, sort, dan pagination bekerja dengan benar.
4. Hanya produk yang memenuhi aturan publikasi backend yang tampil.
5. Profil penjual dapat dibuka dan daftar produknya dapat dimuat.
6. CTA WhatsApp menggunakan URL dari API backend.
7. Produk dengan status habis atau pre-order mengikuti pesan yang dibuat backend.
8. Loading, empty, error, dan offline state berfungsi.
9. Tidak terdapat checkout atau transaksi internal.
10. Seluruh critical test lulus dan tidak ada critical bug yang menghambat alur utama.

## 11. Asumsi dan keputusan yang perlu dikunci

| Topik | Keputusan / status |
|---|---|
| Platform | Android dan iOS |
| Framework | Flutter |
| Backend | Laravel 13 yang sudah ada |
| Database | MariaDB / MySQL yang sudah ada |
| API | Public REST API di prefix /api/v1 |
| Domain kanonis | https://siswamart.app |
| Akun pengunjung | Tidak diperlukan untuk MVP |
| Pemesanan | Direct-to-WhatsApp |
| Banner dinamis | Belum tersedia; bukan fitur MVP sampai backend mendukung |
| Favorit | Lokal, prioritas P1 |
| Review submission | Tidak termasuk MVP; rating dan jumlah ulasan yang tersedia dapat ditampilkan |

## 12. Roadmap ringkas

| Fase | Fokus | Hasil |
|---|---|---|
| 1 | Audit dan kontrak API | Gap API dan keputusan desain disetujui |
| 2 | Penyelarasan API | Perbaikan kontrak, paginasi seller, dan dokumentasi |
| 3 | Fondasi Flutter | Struktur, theme, routing, state, networking |
| 4 | Fitur MVP | Beranda, katalog, pencarian, detail, penjual, WhatsApp |
| 5 | QA | Uji Android/iOS, performa, aksesibilitas, jaringan |
| 6 | Release | Distribusi internal dan persiapan store |

Estimasi awal sekitar tujuh minggu untuk satu developer; jadwal final ditetapkan setelah audit teknis dan pemilihan perangkat build iOS.

---

**Product principle:** Discover products easily. Connect with sellers directly.

**Architecture principle:** One backend. One database. Multiple clients.
