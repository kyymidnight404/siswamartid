# Design System & Panduan Gaya Antarmuka — SiswaMart

| Informasi Dokumen | Detail |
| :--- | :--- |
| **Judul** | Design System, Token Visual, dan Komponen UI SiswaMart |
| **Versi** | `1.1.0` |
| **Tanggal** | 27 Oktober 2026 |
| **Status** | Final (Source-Code Backed) |
| **Penulis** | Tim Dokumentasi Teknis & Rekayasa Perangkat Lunak |
| **Sumber Data** | `public/css/siswamart-theme.css`, `public/css/homepage.css`, `resources/css/app.css`, `app/Exports/Concerns/StylesSheet.php`, `resources/views/components/` |

---

## Daftar Isi

1. [Filosofi Desain Visual](#1-filosofi-desain-visual)
2. [Token Warna Aktual (CSS Variables & Hex Nyata)](#2-token-warna-aktual-css-variables--hex-nyata)
3. [Tipografi dan Hierarki Huruf](#3-tipografi-dan-hierarki-huruf)
4. [Radius Sudut, Garis Tepi (Borders), dan Bayangan (Shadows)](#4-radius-sudut-garis-tepi-borders-dan-bayangan-shadows)
5. [Breakpoint Responsif dan Tata Letak](#5-breakpoint-responsif-dan-tata-letak)
6. [Katalog Komponen UI Blade (`resources/views/components/`)](#6-katalog-komponen-ui-blade-resourcesviewscomponents)
7. [Token Gaya Dokumen Ekspor (PDF & Excel `.xlsx`)](#7-token-gaya-dokumen-ekspor-pdf--excel-xlsx)
8. [Rujukan Kode](#8-rujukan-kode)

---

## 1. Filosofi Desain Visual

Antarmuka SiswaMart menerapkan gaya **Playful Tactile / Soft Neubrutalism** untuk lingkungan sekolah dengan ciri khas:
- **Palet Biru-Slate Sekolah & Aksen Kuning Hangat** (`#3F6A96` sebagai warna primer, `#1F3550` sebagai warna tinta/garis tepi, dan `#F2B84B` sebagai aksen).
- **Garis Tepi Tegas (*Solid Ink Outlines*)** setebal `2px` hingga `2.5px` berwarna biru tua (`#1F3550`) dengan sudut membulat lembut (`18px` s.d. `36px`).
- **Bayangan Offset Taktil Tanpa Blur (*Hard Offset Shadows*)** seperti `4px 4px 0 #1F3550` yang bergerak turun (`translate(1px, 1px)`) saat tombol ditekan, memberikan umpan balik fisik yang jelas bagi pengguna.

> [!NOTE]
> Dokumen lama di riwayat Git sempat mencatat palet awal (`#2647EB` / `#00C49A`). Seluruh nilai di bawah ini diambil langsung dari berkas CSS produksi yang aktif saat ini (`public/css/siswamart-theme.css` dan `public/css/homepage.css`).

---

## 2. Token Warna Aktual (CSS Variables & Hex Nyata)

### 2.1 Token Warna Inti (`public/css/siswamart-theme.css#L1-L20` & `public/css/homepage.css#L1-L26`)

| Variabel CSS | Nilai Hex Nyata | Peran Visual | Lokasi Definisi |
| :--- | :---: | :--- | :--- |
| `--sm-bg` / `--bg-canvas` | `#F5F8FC` | Warna latar belakang halaman utama (biru keabu-abuan sangat muda) | `siswamart-theme.css#L3`, `homepage.css#L4` |
| `--sm-surface` / `--bg-surface` | `#FFFFFF` | Warna permukaan kartu produk, modal, tabel, dan input form | `siswamart-theme.css#L4`, `homepage.css#L5` |
| `--sm-primary-soft` / `--bg-soft` | `#E3ECF5` | Latar belakang lembut untuk kartu sekunder, header tabel, dan hover | `siswamart-theme.css#L5`, `homepage.css#L6` |
| `--sm-primary` / `--brand-primary` | `#3F6A96` | Warna identitas utama (navbar, sidebar, tombol aksi utama) | `siswamart-theme.css#L6`, `homepage.css#L8` |
| `--sm-primary-hover` / `--brand-primary-hover` | `#35597E` | Warna kondisi hover pada elemen primer | `siswamart-theme.css#L7`, `homepage.css#L9` |
| `--sm-ink` / `--ink-strong` | `#1F3550` | Warna teks judul utama, ikon, border tegas, dan bayangan offset | `siswamart-theme.css#L8`, `homepage.css#L10` |
| `--sm-text-muted` / `--ink-muted` | `#4C6078` | Warna teks sekunder, deskripsi, dan keterangan waktu | `siswamart-theme.css#L9`, `homepage.css#L11` |
| `--sm-on-primary` | `#F5F8FC` | Warna teks di atas latar `--sm-primary` | `siswamart-theme.css#L10` |
| `--sm-sidebar-label` | `#EAF1F8` | Warna label grup menu pada sidebar Admin & Penjual | `siswamart-theme.css#L11` |
| `--sm-accent` / `--brand-accent` | `#F2B84B` | Warna aksen kuning emas (lencana promosi, CTA sorotan, bintang) | `siswamart-theme.css#L12`, `homepage.css#L12` |
| `--sm-accent-hover` | `#E5A735` | Warna hover pada tombol beraksen kuning | `siswamart-theme.css#L13` |
| `--sm-on-accent` | `#1F2A3A` | Warna teks gelap kontras tinggi di atas latar `--sm-accent` | `siswamart-theme.css#L14` |
| `--sm-border` | `#1F3550` | Warna garis tepi utama kartu dan kontainer | `siswamart-theme.css#L15` |
| `--sm-input-border` | `#7D93AC` | Warna garis tepi default untuk kotak input form | `siswamart-theme.css#L16` |
| `--bg-soft-alt` | `#DCE7F3` | Variasi latar lembut pada blok beranda | `homepage.css#L7` |

### 2.2 Warna Semantik Status & Tombol Aksi (`siswamart-theme.css#L130-L152`, `status-badge.blade.php`)

| Konteks Semantik | Nilai Hex / Kelas | Penggunaan di Antarmuka |
| :--- | :--- | :--- |
| **Sukses / Aktif / Tersedia / Setujui** | `#15803D` (Hover `#166534`), Badge `bg-emerald-50 text-emerald-700` | Tombol Setujui Moderasi (`.sm-action-approve`), lencana produk `Aktif`, stok `Tersedia`, toko `Buka`. |
| **Peringatan / Pending / Pre-Order / Edit** | `#D97706` (Hover `#B45309`), Badge `bg-amber-50 text-amber-700` | Tombol Edit (`.sm-action-edit`), lencana `Menunggu Moderasi`, stok `Pre-Order (PO)`. |
| **Bahaya / Ditolak / Habis / Hapus** | `#DC2626` (Hover `#B91C1C`), Error `#8B1E2D` / `#FDECEC` / `#8A1F1F` | Tombol Hapus/Tolak (`.sm-action-delete`), kotak validasi error (`.sm-auth-error`), lencana `Ditolak` / `Stok Habis`. |

---

## 3. Tipografi dan Hierarki Huruf

Sistem memuat tiga keluarga huruf (*font family*) dari Google Fonts dan Bunny Fonts (`resources/views/layouts/public.blade.php`, `resources/views/layouts/app.blade.php`, `resources/css/app.css`):

| Peran Tipografi | Font Family | Ketebalan (*Weights*) | Karakteristik & Penggunaan | Rujukan Berkas |
| :--- | :--- | :--- | :--- | :--- |
| **Heading & Display (`--font-display`)** | `'Fredoka'`, sans-serif | `500`, `600`, `700` | Sudut huruf membulat yang ramah; dipakai untuk judul halaman (`h1`–`h4`), nama produk di kartu, harga, dan tombol CTA. | `homepage.css#L17`, `siswamart-theme.css#L62-L65` |
| **Body & UI (`--font-body`)** | `'Plus Jakarta Sans'`, sans-serif | `400`, `500`, `600`, `700`, `800` | Keterbacaan tinggi untuk tabel data, paragraf deskripsi, label form, dan laporan. | `homepage.css#L18`, `siswamart-theme.css#L22-L26` |
| **Fallback Default Tailwind** | `'Figtree'`, sans-serif | `400`, `500`, `600` | Didefinisikan pada `@theme` di `resources/css/app.css#L5`. | `resources/css/app.css#L5` |

---

## 4. Radius Sudut, Garis Tepi (Borders), dan Bayangan (Shadows)

Diambil langsung dari `public/css/homepage.css#L14-L25` dan `public/css/siswamart-theme.css#L17-L115`:

| Token / Properti | Nilai Aktual | Elemen Pengguna |
| :--- | :--- | :--- |
| `--r-block` | `36px` | Kontainer hero banner beranda dan blok promosi besar |
| `--r-card` / `--r-tile` | `28px` | Kartu produk (`.product-card`), kartu kategori, dan kartu autentikasi (`26px`) |
| `--r-input` | `18px` | Kotak pencarian, input form (`.sm-auth-input`: `14px`), dan tombol aksi |
| `--r-pill` | `999px` | Lencana status (*pill badge*), tombol filter cepat, dan avatar |
| `--border-main` | `2.5px solid #1F3550` | Garis tepi tegas pada kartu beranda, tombol utama, dan kontainer |
| `--shadow-card` | `4px 4px 0 #1F3550` | Bayangan offset standar kartu produk dan tombol (`.sm-auth-btn`) |
| `--shadow-card-lg` | `6px 6px 0 #1F3550` | Bayangan saat kartu produk di-hover (`transform: translateY(-3px)`) |
| `--shadow-soft` | `0 12px 30px rgba(31, 53, 80, 0.08)` | Bayangan lembut untuk kartu panel autentikasi dan modal |
| `box-shadow` (Input Focus) | `0 0 0 3px rgba(63, 106, 150, 0.2)` | Cincin fokus (*focus ring*) aksesibilitas pada seluruh input dan select |

---

## 5. Breakpoint Responsif dan Tata Letak

Selain breakpoint bawaan Tailwind CSS (`sm: 640px`, `md: 768px`, `lg: 1024px`, `xl: 1280px`), `public/css/homepage.css` menerapkan breakpoint adaptif khusus untuk grid katalog:

| Breakpoint Media Query | Perilaku Tata Letak (*Layout Behavior*) | Rujukan Kode |
| :--- | :--- | :--- |
| **`> 1100px` (Desktop Lebar)** | Grid produk menampilkan **4 kolom** (`repeat(4, minmax(0, 1fr))`), tata letak beranda 2 kolom penuh. | `public/css/homepage.css#L280-L310` |
| **`<= 1100px` (Laptop Kecil / Tablet Landscape)** | Grid produk menyusut menjadi **3 kolom**, spasi blok dikurangi secara proporsional. | `public/css/homepage.css#L670-L710` |
| **`<= 780px` (Tablet Portrait)** | Navigasi atas beralih ke menu ringkas, grid produk menjadi **2 kolom**, sidebar filter katalog ditumpuk. | `public/css/homepage.css#L712-L760` |
| **`<= 520px` (Mobile Layar Kecil)** | Grid produk dan kartu fitur menyesuaikan menjadi **1–2 kolom** rapat dengan ukuran huruf judul yang diperkecil (`clamp()`). | `public/css/homepage.css#L762-L810` |

---

## 6. Katalog Komponen UI Blade (`resources/views/components/`)

Proyek menyediakan **20 komponen Blade reusable** (`<x-*>`) untuk menjaga konsistensi visual di seluruh halaman:

| Nama Komponen Blade | Berkas Sumber (`resources/views/components/...`) | Fungsi & Props Utama |
| :--- | :--- | :--- |
| `<x-brand-logo>` | `brand-logo.blade.php` | Menampilkan logo resmi SiswaMart (`images/logo-siswamart.png`). |
| `<x-public.product-card>` | `public/product-card.blade.php` | Kartu produk katalog publik lengkap dengan foto, pita `label_promosi`, lencana stok (`tersedia`/`po`/`habis`), lencana `Toko Tutup`, harga Rupiah, nama toko, dan rating bintang. |
| `<x-public.category-select>` | `public/category-select.blade.php` | Dropdown pemilih kategori berbasis Alpine.js dengan ikon kustom. |
| `<x-public.home-icon>` | `public/home-icon.blade.php` | Kumpulan ikon SVG vektor untuk beranda dan kategori (`food`, `craft`, `service`, `preloved`, `search`, `whatsapp`, `star`, dll.). |
| `<x-ui.status-badge>` | `ui/status-badge.blade.php` | Lencana status seragam untuk `status` produk (`aktif`, `pending`, `ditolak`), `stok`, dan `status_toko`. |
| `<x-ui.download-menu>` | `ui/download-menu.blade.php` | Tombol dropdown unduh laporan dengan pilihan **Download PDF** dan **Download Excel (.xlsx)**. |
| `<x-ui.custom-select>` | `ui/custom-select.blade.php` | Komponen select kustom berbasis Alpine.js untuk filter panel Admin & Penjual. |
| `<x-admin.table-pagination>` | `admin/table-pagination.blade.php` | Navigasi paginasi tabel seragam disertai informasi rentang baris (`Menampilkan X–Y dari Z data`). |
| `<x-modal>` | `modal.blade.php` | Dialog modal aksesibel berbasis Alpine.js dengan transisi backdrop. |
| `<x-primary-button>`, `<x-secondary-button>`, `<x-danger-button>` | `primary-button.blade.php`, `secondary-button.blade.php`, `danger-button.blade.php` | Komponen tombol aksi standar untuk formulir. |
| `<x-text-input>`, `<x-input-label>`, `<x-input-error>` | `text-input.blade.php`, `input-label.blade.php`, `input-error.blade.php` | Elemen input formulir beserta label dan pesan validasi error. |

---

## 7. Token Gaya Dokumen Ekspor (PDF & Excel `.xlsx`)

Untuk memastikan laporan yang diunduh selaras secara visual dengan aplikasi web, `app/Exports/Concerns/StylesSheet.php` mendefinisikan palet warna khusus untuk buku kerja Excel (`.xlsx`):

| Konstanta `StylesSheet` | Nilai Hex (`RRGGBB`) | Penggunaan pada Lembar Kerja Excel | Rujukan Kode |
| :--- | :---: | :--- | :--- |
| `COLOR_NAVY_DARK` | `#0F172A` | Latar belakang baris judul utama laporan (`title`) | `StylesSheet.php#L12` |
| `COLOR_NAVY` | `#1E293B` | Latar belakang header tabel utama (`table_header`) | `StylesSheet.php#L13` |
| `COLOR_SLATE_DARK` | `#334155` | Latar belakang sub-bagian (`section`) | `StylesSheet.php#L14` |
| `COLOR_ZEBRA` | `#F8FAFC` | Warna selang-seling baris data (*zebra striping*) | `StylesSheet.php#L17` |
| `COLOR_BORDER` | `#CBD5E1` | Garis tepi tipis antar sel tabel | `StylesSheet.php#L19` |
| `COLOR_SUCCESS_BG` / `TEXT` | `#DCFCE7` / `#166534` | Sel status `AKTIF` / `TERSEDIA` / `BUKA` | `StylesSheet.php#L22-L23` |
| `COLOR_WARNING_BG` / `TEXT` | `#FEF3C7` / `#92400E` | Sel status `PENDING` / `PO` | `StylesSheet.php#L24-L25` |
| `COLOR_DANGER_BG` / `TEXT` | `#FEE2E2` / `#991B1B` | Sel status `DITOLAK` / `HABIS` / `TUTUP` | `StylesSheet.php#L26-L27` |

---

## 8. Rujukan Kode

- `public/css/siswamart-theme.css#L1-L215`
- `public/css/homepage.css#L1-L820`
- `resources/css/app.css#L1-L15`
- `resources/views/components/public/product-card.blade.php#L1-L145`
- `resources/views/components/ui/status-badge.blade.php#L1-L55`
- `resources/views/components/ui/download-menu.blade.php#L1-L55`
- `app/Exports/Concerns/StylesSheet.php#L10-L246`
