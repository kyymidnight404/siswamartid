# Diagram Activity, Sequence, dan State — SiswaMart

| Informasi Dokumen | Detail |
| :--- | :--- |
| **Judul** | Activity (Flowchart), Sequence, dan State Diagram Alur Utama SiswaMart |
| **Versi** | `1.2.0` |
| **Tanggal** | 18 November 2026 |
| **Status** | Final (Source-Code Backed) |
| **Penulis** | Tim Dokumentasi Teknis & Rekayasa Perangkat Lunak |
| **Sumber Data** | `Public\ProdukController`, `Public\UlasanController`, `Penjual\ProdukController`, `Admin\ProdukController`, `CommentModerationService`, `LaporanAdminService`, `LaporanPenjualService` |

---

## Daftar Isi

1. [Alur 1: Melihat Detail Produk & Pencatatan Analitik Kunjungan (`view`)](#1-alur-1-melihat-detail-produk--pencatatan-analitik-kunjungan-view)
2. [Alur 2: Klik Pemesanan WhatsApp & Penyesuaian Status Toko (`whatsapp`)](#2-alur-2-klik-pemesanan-whatsapp--penyesuaian-status-toko-whatsapp)
3. [Alur 3: Pengiriman Ulasan Tamu dengan Filter Kata Kasar (`CommentModerationService`)](#3-alur-3-pengiriman-ulasan-tamu-dengan-filter-kata-kasar-commentmoderationservice)
4. [Alur 4: Pengajuan Produk Baru, Edit (`pending_changes`), dan Hapus oleh Penjual](#4-alur-4-pengajuan-produk-baru-edit-pending_changes-dan-hapus-oleh-penjual)
5. [Alur 5: Moderasi Produk (`Approve` / `Reject`) oleh Admin & State Machine Produk](#5-alur-5-moderasi-produk-approve--reject-oleh-admin--state-machine-produk)
6. [Alur 6: Pembuatan dan Pengunduhan Laporan PDF & Excel (`.xlsx`)](#6-alur-6-pembuatan-dan-pengunduhan-laporan-pdf--excel-xlsx)
7. [Rujukan Kode](#7-rujukan-kode)

---

## 1. Alur 1: Melihat Detail Produk & Pencatatan Analitik Kunjungan (`view`)

Rute: `GET /{penjual:slug}/{produk:slug}` (`Public\ProdukController@show`, `throttle:60,1`; rute legacy `GET /produk/{slug}` melakukan redirect `301`).

### 1.1 Sequence Diagram Lihat Produk

```mermaid
sequenceDiagram
    autonumber
    actor Visitor as Pengunjung Publik
    participant Route as Middleware (throttle:60,1)
    participant Ctrl as Public\ProdukController
    participant Model as Produk & ProdukViewLog
    participant DB as MySQL Database

    Visitor->>Route: GET /{toko}/{produk}
    alt Melebihi 60 req/menit
        Route-->>Visitor: HTTP 429 Too Many Requests
    else Dalam batas kuota
        Route->>Ctrl: show(Produk $produk)
        Ctrl->>Model: Cek status === 'aktif' && penjual.user.is_active
        alt Produk tidak aktif / Penjual nonaktif
            Ctrl-->>Visitor: abort(404 Not Found)
        else Produk valid (publicVisible)
            Ctrl->>Model: $produk->increment('views')
            Model->>DB: UPDATE produk SET views = views + 1
            Ctrl->>Model: ProdukViewLog::create(['produk_id' => id, 'type' => 'view'])
            Model->>DB: INSERT INTO produk_views_logs
            Ctrl->>DB: Load kategori, penjual.user, ulasan (paginate 5), relatedProducts (limit 4)
            Ctrl-->>Visitor: Render view('public.produk.show') (HTTP 200)
        end
    end
```

---

## 2. Alur 2: Klik Pemesanan WhatsApp & Penyesuaian Status Toko (`whatsapp`)

Rute: `GET /{penjual:slug}/{produk:slug}/whatsapp` (`Public\ProdukController@trackWhatsapp`, `throttle:30,1`; rute legacy `GET /produk/{slug}/whatsapp` melakukan redirect `301`).

### 2.1 Activity Flowchart Klik WhatsApp

```mermaid
flowchart TD
    Start(["Pengunjung klik tombol Pesan via WhatsApp"]) --> CheckThrottle{"Lolos throttle:30,1?"}
    CheckThrottle -- "Tidak" --> Err429["HTTP 429 Too Many Requests"]
    CheckThrottle -- "Ya" --> CheckVisible{"produk.status == 'aktif' & penjual.user.is_active?"}
    CheckVisible -- "Tidak" --> Err404["HTTP 404 Not Found"]
    CheckVisible -- "Ya" --> CheckStok{"produk.stok === 'habis'?"}
    CheckStok -- "Ya" --> RedirectStok["Redirect kembali ke halaman produk + flash error stok habis"]
    CheckStok -- "Tidak" --> CleanPhone["Normalisasi nomor_whatsapp (preg_replace non-digit, 0... -> 62...)"]
    CleanPhone --> CheckPhone{"Nomor telepon valid (tidak kosong)?"}
    CheckPhone -- "Tidak" --> RedirectNoPhone["Redirect kembali + flash error nomor WA belum tersedia"]
    CheckPhone -- "Ya" --> LogWA["Simpan ProdukViewLog (type = 'whatsapp')"]
    LogWA --> CheckStore{"penjual.status_toko == 'tutup'?"}
    CheckStore -- "Ya (Toko Tutup)" --> MsgClosed["Susun pesan WA: konfirmasi ketersediaan & waktu buka kembali"]
    CheckStore -- "Tidak (Toko Buka)" --> MsgOpen["Susun pesan WA: pemesanan produk + harga Rupiah"]
    MsgClosed --> RedirectWA(["HTTP 302 Redirect ke https://wa.me/{phone}?text={message}"])
    MsgOpen --> RedirectWA
```

---

## 3. Alur 3: Pengiriman Ulasan Tamu dengan Filter Kata Kasar (`CommentModerationService`)

Rute: `POST /{penjual:slug}/{produk:slug}/ulasan` (`Public\UlasanController@store`, `throttle:3,1`) dan `POST /api/products/{product}/reviews` (`Api\ReviewController@store`, `throttle:3,1`).

### 3.1 Sequence Diagram Kirim Ulasan & Filter Kata Kasar

```mermaid
sequenceDiagram
    autonumber
    actor Visitor as Pengunjung Publik
    participant Ctrl as Public\UlasanController
    participant ModSvc as CommentModerationService
    participant Cache as Cache / DB (bad_words & whitelists)
    participant Ulasan as Model Ulasan
    participant NotifSvc as AdminNotificationService

    Visitor->>Ctrl: POST /{toko}/{produk}/ulasan (guest_username, rating, komentar)
    Ctrl->>Ctrl: Cek produk.status == 'aktif' & penjual.user.is_active
    Ctrl->>Ctrl: Validasi dasar (guest_username max:100, rating 1..5, komentar max:1000)
    Ctrl->>ModSvc: moderateComment(guest_username)
    ModSvc->>Cache: Ambil kamus bad_words (JSON + DB) & whitelists (TTL 24 jam)
    ModSvc->>ModSvc: Normalisasi leetspeak (0->o, 1->i, 3->e, 4->a, 5->s, 7->t) & karakter berulang
    ModSvc-->>Ctrl: usernameModeration (detected_words)
    alt Username mengandung kata terlarang
        Ctrl-->>Visitor: Redirect back + Error validasi 'guest_username'
    else Username bersih
        Ctrl->>ModSvc: moderateComment(komentar)
        ModSvc-->>Ctrl: commentModeration (detected_words, moderation_status)
        alt Komentar mengandung kata terlarang
            Ctrl-->>Visitor: Redirect back + Error validasi 'komentar'
        else Komentar bersih (Lolos Moderasi)
            Ctrl->>Ulasan: create(produk_id, guest_username, rating, komentar, status='aktif', moderation_status='approved')
            Ctrl->>NotifSvc: reviewSubmitted($produk, $ulasan)
            NotifSvc->>Cache: Simpan DatabaseNotification ke semua Admin aktif
            Ctrl-->>Visitor: Redirect back + Flash success "Ulasan berhasil dikirim"
        end
    end
```

---

## 4. Alur 4: Pengajuan Produk Baru, Edit (`pending_changes`), dan Hapus oleh Penjual

Rute: `POST /penjual/produk`, `PUT /penjual/produk/{produk}`, `DELETE /penjual/produk/{produk}` (`Penjual\ProdukController`).

### 4.1 Activity Flowchart Pengajuan Produk oleh Penjual

```mermaid
flowchart TD
    Start(["Penjual melakukan aksi pada Produk"]) --> ActionType{"Jenis Aksi?"}

    ActionType -- "1. Tambah Produk Baru (store)" --> ValidateCreate["Validasi input & unggah foto_utama ke storage/app/public/produk"]
    ValidateCreate --> SaveCreate["Simpan Produk: status='pending', pending_action='create', pending_changes=null"]
    SaveCreate --> NotifyAdmin["Kirim Notifikasi ke Admin (AdminNotificationService::productSubmitted)"]

    ActionType -- "2. Edit Produk (update)" --> ValidateUpdate["Validasi input & cek kepemilikan (penjual_id)"]
    ValidateUpdate --> IsActive{"Apakah produk.status === 'aktif'?"}
    IsActive -- "Ya (Produk sedang tayang)" --> SaveShadow["Simpan data baru ( & foto baru sementara) ke JSON pending_changes; set pending_action='update'; kolom utama & status='aktif' TIDAK diubah"]
    IsActive -- "Tidak (pending / ditolak / draft)" --> SaveDirect["Timpa kolom utama produk; set status='pending', pending_action='create', pending_changes=null, alasan_ditolak=null"]
    SaveShadow --> NotifyAdmin
    SaveDirect --> NotifyAdmin

    ActionType -- "3. Hapus Produk (destroy)" --> CheckPending{"Sudah ada ajuan moderasi menggantung?"}
    CheckPending -- "Ya" --> BlockDelete["Tolak dengan pesan error: produk masih menunggu moderasi"]
    CheckPending -- "Tidak" --> MarkDelete["Hapus foto sementara di pending_changes (jika ada); set pending_action='delete', pending_changes=null"]
    MarkDelete --> NotifyAdmin

    NotifyAdmin --> End(["Selesai: Menunggu Keputusan Admin"])
```

---

## 5. Alur 5: Moderasi Produk (`Approve` / `Reject`) oleh Admin & State Machine Produk

Rute: `PATCH /admin/produk/{produk}/update-status` (`Admin\ProdukController@updateStatus`).

### 5.1 Sequence Diagram Moderasi Produk oleh Admin

```mermaid
sequenceDiagram
    autonumber
    actor Admin as Admin Sekolah
    participant Ctrl as Admin\ProdukController
    participant Storage as Public Disk Storage
    participant DB as Tabel produk (MySQL)

    Admin->>Ctrl: PATCH /admin/produk/{produk}/update-status (status: 'aktif' | 'ditolak', alasan_ditolak)
    Ctrl->>Ctrl: Validasi status & wajib alasan_ditolak jika 'ditolak'
    Ctrl->>Ctrl: Deteksi action = produk.pending_action ?? (produk.status == 'pending' ? 'create' : null)

    alt Keputusan Admin: APPROVE (status == 'aktif')
        alt action === 'delete'
            Ctrl->>Storage: Hapus foto_utama & foto di pending_changes
            Ctrl->>DB: $produk->delete() (Hapus permanen dari DB)
            Ctrl-->>Admin: Redirect + Flash "Permintaan hapus disetujui"
        else action === 'update'
            Ctrl->>Storage: Hapus foto_utama lama jika diganti foto baru di pending_changes
            Ctrl->>DB: Salin pending_changes ke kolom utama; status='aktif', pending_action=null, pending_changes=null, alasan_ditolak=null
            Ctrl-->>Admin: Redirect + Flash "Perubahan produk disetujui"
        else action === 'create'
            Ctrl->>DB: Update status='aktif', pending_action=null, pending_changes=null, alasan_ditolak=null
            Ctrl-->>Admin: Redirect + Flash "Produk baru disetujui"
        end
    else Keputusan Admin: REJECT (status == 'ditolak')
        alt action === 'update' ATAU (action === 'delete' & produk.status === 'aktif')
            Ctrl->>Storage: Hapus foto baru sementara di pending_changes (jika ada)
            Ctrl->>DB: Pertahankan status='aktif' & kolom utama lama; set pending_action=null, pending_changes=null, alasan_ditolak=input
            Ctrl-->>Admin: Redirect + Flash "Ajuan ditolak, versi aktif tetap tayang"
        else action === 'create' (Produk belum aktif)
            Ctrl->>DB: Update status='ditolak', pending_action=null, pending_changes=null, alasan_ditolak=input
            Ctrl-->>Admin: Redirect + Flash "Produk baru ditolak"
        end
    end
```

### 5.2 State Diagram Siklus Hidup Produk (`status` & `pending_action`)

```mermaid
stateDiagram-v2
    [*] --> Pending_Create : Penjual tambah produk baru\n(status='pending', pending_action='create')

    Pending_Create --> Aktif_Clean : Admin Approve\n(status='aktif', pending_action=null)
    Pending_Create --> Ditolak_Clean : Admin Reject + alasan_ditolak\n(status='ditolak', pending_action=null)

    Ditolak_Clean --> Pending_Create : Penjual perbaiki & simpan ulang\n(status='pending', pending_action='create')
    Ditolak_Clean --> Ditolak_Delete : Penjual ajukan hapus\n(status='ditolak', pending_action='delete')
    Ditolak_Delete --> [*] : Admin Approve Hapus\n(produk dihapus permanen)
    Ditolak_Delete --> Ditolak_Clean : Admin Reject Hapus\n(pending_action=null)

    Aktif_Clean --> Aktif_PendingUpdate : Penjual edit produk aktif\n(status='aktif', pending_action='update',\ndata baru di pending_changes)
    Aktif_PendingUpdate --> Aktif_Clean : Admin Approve Update\n(pending_changes diterapkan ke kolom utama)\nATAU Admin Reject Update\n(pending_changes dibuang, data lama tetap tayang)

    Aktif_Clean --> Aktif_PendingDelete : Penjual ajukan hapus produk aktif\n(status='aktif', pending_action='delete')
    Aktif_PendingDelete --> [*] : Admin Approve Delete\n(foto & baris DB dihapus permanen)
    Aktif_PendingDelete --> Aktif_Clean : Admin Reject Delete\n(status tetap 'aktif', pending_action=null)
```

---

## 6. Alur 6: Pembuatan dan Pengunduhan Laporan PDF & Excel (`.xlsx`)

Rute:
- Admin: `GET /admin/laporan/penjual/pdf`, `GET /admin/laporan/ulasan/pdf`, `GET /admin/laporan/penjual/sheet`, `GET /admin/laporan/ulasan/sheet` (beserta laporan detail penjual).
- Penjual: `GET /penjual/laporan/pdf` (PDF A4 Portrait), `GET /penjual/laporan/sheet` (Excel 5 sheet).

### 6.1 Sequence Diagram Unduh Laporan PDF & Excel

```mermaid
sequenceDiagram
    autonumber
    actor User as Admin / Penjual
    participant Ctrl as LaporanController (Admin / Penjual)
    participant Svc as LaporanAdminService / LaporanPenjualService
    participant Style as StylesSheet Concern
    participant Writer as XlsxWriter / DomPDF
    participant DB as MySQL Database

    User->>Ctrl: GET .../pdf atau .../sheet (filter period / week_offset)
    Ctrl->>Svc: Ambil metrik ringkasan & detail laporan
    Svc->>DB: Query agregasi Produk, Penjual, Ulasan, & ProdukViewLog sesuai periode
    DB-->>Svc: Koleksi data & metrik statistik
    Svc-->>Ctrl: Array data laporan terstruktur

    alt Format Unduhan == PDF (.../pdf)
        Ctrl->>Writer: Pdf::loadView(..., reportData)->setPaper(...)
        Writer-->>Ctrl: Binary PDF stream
        Ctrl-->>User: HTTP 200 (Content-Type: application/pdf)
    else Format Unduhan == Excel (.../sheet)
        Ctrl->>Style: Sanitasi teks (StylesSheet::sanitizeText mencegah Formula Injection)
        Ctrl->>Writer: XlsxWriter::download(sheets, filename.xlsx)
        Writer->>Writer: Bangun XML OpenXML + paketkan ZIP (ZipArchive / fallback pure-PHP CRC32)
        Writer-->>User: HTTP 200 (Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet)
    end
```

---

## 7. Rujukan Kode

- `app/Http/Controllers/Public/ProdukController.php`
- `app/Http/Controllers/Public/UlasanController.php`
- `app/Services/CommentModerationService.php`
- `app/Http/Controllers/Penjual/ProdukController.php`
- `app/Http/Controllers/Admin/ProdukController.php`
- `app/Http/Controllers/Admin/LaporanController.php`
- `app/Http/Controllers/Penjual/LaporanController.php`
- `app/Exports/Concerns/StylesSheet.php`
- `app/Support/XlsxWriter.php`
