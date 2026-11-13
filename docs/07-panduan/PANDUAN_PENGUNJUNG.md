# Panduan Pengguna Pengunjung Publik — SiswaMart

| Informasi Dokumen | Detail |
| :--- | :--- |
| **Judul** | Panduan Penggunaan untuk Pengunjung Publik (Pembeli) |
| **Versi** | `1.1.0` |
| **Tanggal** | 27 Oktober 2026 |
| **Status** | Final (Source-Code Backed) |
| **Penulis** | Tim Dokumentasi Teknis & Rekayasa Perangkat Lunak |
| **Sumber Data** | `routes/web.php`, `app/Http/Controllers/Public/`, `resources/views/public/` |

---

## Daftar Isi

1. [Gambaran Umum untuk Pengunjung](#1-gambaran-umum-untuk-pengunjung)
2. [Menjelajahi Beranda dan Kategori Produk](#2-menjelajahi-beranda-dan-kategori-produk)
3. [Mencari dan Menyaring Produk (Filter Kategori, Harga, & Urutan)](#3-mencari-dan-menyaring-produk-filter-kategori-harga--urutan)
4. [Membaca Halaman Detail Produk, Status Stok, dan Status Toko](#4-membaca-halaman-detail-produk-status-stok-dan-status-toko)
5. [Cara Memesan Produk Melalui WhatsApp](#5-cara-memesan-produk-melalui-whatsapp)
6. [Cara Memberikan Ulasan dan Rating Bintang](#6-cara-memberikan-ulasan-dan-rating-bintang)
7. [Rujukan Kode](#7-rujukan-kode)

---

## 1. Gambaran Umum untuk Pengunjung

Sebagai pengunjung (siswa, guru, staf sekolah, maupun masyarakat umum), Anda **tidak perlu membuat akun atau login** untuk:
- Menjelajahi seluruh produk wirausaha siswa di Beranda (`/`) dan Katalog Kategori (`/kategori`).
- Melihat detail produk, harga, lokasi kelas penjual, dan ulasan pembeli lain (`/produk/{slug}`).
- Memesan produk secara langsung ke WhatsApp penjual (`/produk/{slug}/whatsapp`).
- Memberikan rating bintang (1–5) dan komentar ulasan (`/produk/{slug}/ulasan`).

Informasi tambahan mengenai tata cara berbelanja dan profil platform juga tersedia melalui menu **Cara Pemesanan** (`/cara-pemesanan`) dan **Tentang Kami** (`/tentang-kami`).

---

## 2. Menjelajahi Beranda dan Kategori Produk

1. Buka halaman utama **Beranda** (`GET /`).
2. Di halaman Beranda, Anda dapat melihat:
   - **Kotak Pencarian Cepat** dan daftar **Kategori Utama** (*Kuliner*, *Kerajinan*, *Jasa*, *Preloved*, dll.).
   - **Produk Pilihan / Terbaru** lengkap dengan label promosi, harga Rupiah, nama toko, dan rata-rata rating bintang.
3. Klik salah satu kartu kategori atau menu **Kategori** (`GET /kategori`) untuk melihat katalog lengkap atau masuk ke kategori spesifik (`GET /kategori/{slug}`).

---

## 3. Mencari dan Menyaring Produk (Filter Kategori, Harga, & Urutan)

Pada halaman Katalog (`/kategori`), gunakan panel filter untuk menemukan produk dengan cepat (`Public\KategoriController@index`):

| Parameter Filter | Cara Penggunaan | Perilaku Sistem |
| :--- | :--- | :--- |
| **Kata Kunci (`q`)** | Ketik nama produk, deskripsi, atau nama toko di kotak pencarian. | Mencocokkan kata kunci pada nama produk, deskripsi, nama kategori, dan nama toko penjual. |
| **Kategori (`kategori[]`)** | Centang satu atau beberapa kategori sekaligus. | Menampilkan produk dari kategori-kategori yang dipilih. |
| **Rentang Harga (`min_harga` & `max_harga`)** | Isi harga minimum dan/atau maksimum dalam Rupiah. | Jika Anda tidak sengaja mengisi `min_harga` lebih besar dari `max_harga`, sistem otomatis menukar keduanya agar hasil tetap akurat (`KategoriController.php#L25-L29`). |
| **Urutkan (`sort`)** | Pilih urutan tampilan produk. | Pilihan tersedia: **Terbaru** (`terbaru`), **Harga Termurah** (`termurah`), atau **Harga Termahal** (`termahal`). |

---

## 4. Membaca Halaman Detail Produk, Status Stok, dan Status Toko

Klik kartu produk mana pun untuk membuka halaman detail (`GET /produk/{slug}`). Perhatikan arti indikator status berikut sebelum memesan:

### 4.1 Arti Indikator Status Stok (`produk.stok`)
| Lencana Stok | Kode Sistem | Arti & Ketersediaan Pemesanan |
| :--- | :---: | :--- |
| **Stok Tersedia** | `tersedia` | Barang siap dibeli langsung (*ready stock*). Tombol WhatsApp **aktif**. |
| **Pre-Order (PO)** | `po` | Barang dibuat berdasarkan pesanan terlebih dahulu. Tombol WhatsApp **aktif**. |
| **Stok Habis** | `habis` | Persediaan barang sedang kosong. Tombol WhatsApp **dinonaktifkan** dan produk tidak dapat dipesan sementara waktu. |

### 4.2 Arti Indikator Status Operasional Toko (`penjual.status_toko`)
| Lencana Toko | Kode Sistem | Dampak pada Pemesanan WhatsApp |
| :--- | :---: | :--- |
| **Toko Buka** | `buka` | Penjual sedang aktif melayani pesanan. Pesan WhatsApp otomatis berisi format pemesanan langsung. |
| **Toko Tutup** | `tutup` | Toko sedang di luar jam operasional/istirahat. Anda **tetap dapat menekan tombol WhatsApp** (selama stok bukan `habis`) untuk mengirim pesan konfirmasi kapan toko buka kembali. |

---

## 5. Cara Memesan Produk Melalui WhatsApp

1. Pastikan produk yang Anda pilih berstatus **Stok Tersedia** atau **Pre-Order (PO)**.
2. Klik tombol **"Pesan via WhatsApp"** pada halaman detail produk (`GET /produk/{slug}/whatsapp`).
3. Sistem akan mencatat klik pemesanan dan secara otomatis mengarahkan Anda ke aplikasi **WhatsApp** (`wa.me/62...`) dengan teks pesan yang sudah terisi otomatis:
   - **Saat Toko Buka:**
     > *"Halo Kak, saya tertarik dengan produk *{Nama Produk}* (Rp {Harga}) di SiswaMart. Apakah stoknya masih tersedia?"*
   - **Saat Toko Tutup:**
     > *"Halo Kak, saya melihat produk *{Nama Produk}* (Rp {Harga}) di SiswaMart. Saya lihat status toko sedang tutup, apakah saya masih bisa memesan atau kapan toko buka kembali?"*
4. Kirimkan pesan tersebut di WhatsApp dan sepakati jumlah pesanan serta lokasi serah terima (*COD* di kelas/kantin sekolah) bersama penjual.

---

## 6. Cara Memberikan Ulasan dan Rating Bintang

1. Gulir ke bagian **Ulasan Pembeli** di bagian bawah halaman detail produk.
2. Isi formulir ulasan (`POST /produk/{slug}/ulasan`):
   - **Nama Anda (`guest_username`):** Wajib diisi, maksimal 100 karakter.
   - **Rating (`rating`):** Pilih 1 hingga 5 bintang.
   - **Komentar (`komentar`):** Wajib diisi, maksimal 1.000 karakter.
3. Klik **Kirim Ulasan**.

> [!IMPORTANT]
> **Aturan Etika & Filter Kata Kasar Otomatis:**
> SiswaMart dilengkapi penyaring bahasa otomatis (`CommentModerationService`). Apabila Nama atau Komentar Anda mengandung kata kasar, umpatan, atau variasi angka/simbol (*leetspeak*), ulasan akan **ditolak secara otomatis** dengan pesan peringatan. Selain itu, pengiriman ulasan dibatasi maksimal **3 kali per menit** untuk mencegah spam.

---

## 7. Rujukan Kode

- `routes/web.php#L9-L28`
- `app/Http/Controllers/Public/HomeController.php#L13-L108`
- `app/Http/Controllers/Public/KategoriController.php#L14-L163`
- `app/Http/Controllers/Public/ProdukController.php#L15-L120`
- `app/Http/Controllers/Public/UlasanController.php#L18-L62`
- `resources/views/public/produk/show.blade.php`
- `resources/views/public/cara-pemesanan.blade.php`
