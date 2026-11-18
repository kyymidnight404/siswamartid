# Mobile API Specification — SiswaMart

| Atribut | Detail |
|---|---|
| Dokumen | Mobile API Specification |
| Versi | 1.0.0 |
| Status | Draft — source-code audited |
| Backend | Laravel 13 |
| Production base URL | https://siswamart.app/api/v1 |
| API format | JSON over HTTPS |
| Database | Existing MariaDB / MySQL |

## 1. Tujuan dan aturan penggunaan

Dokumen ini menentukan kontrak API yang digunakan SiswaMart Mobile MVP. Spesifikasi membedakan:
- **Existing:** rute dan perilaku yang ditemukan di routes/api.php serta controller/service aktual.
- **Recommended:** perubahan atau peningkatan yang perlu dilakukan sebelum atau setelah integrasi mobile.

Mobile harus menggunakan prefix /api/v1 untuk seluruh endpoint katalog publik. Jangan membuat endpoint seolah-olah sudah tersedia bila belum ada di backend.

## 2. Base URL dan headers

Production base URL:

```text
https://siswamart.app/api/v1
```

Headers umum:
```http
Accept: application/json
Content-Type: application/json
```

Untuk GET detail produk, kirim header:
```http
X-Device-Id: <random-installation-uuid>
```

UUID dibuat oleh aplikasi dan disimpan lokal. Jangan menggunakan IMEI, advertising ID, atau identifier hardware.

Endpoint katalog publik pada grup v1 tidak memerlukan Authorization Bearer Token. Sanctum hanya digunakan untuk endpoint akun yang tidak menjadi bagian dari katalog MVP.

## 3. Response envelope

### 3.1 Success

```json
{
  "success": true,
  "message": "Daftar produk berhasil diambil",
  "data": {}
}
```

### 3.2 Error

```json
{
  "success": false,
  "message": "Produk tidak ditemukan atau tidak aktif",
  "errors": {}
}
```

### 3.3 Validation error

HTTP 422:
```json
{
  "success": false,
  "message": "Data yang dikirim tidak valid.",
  "errors": {
    "field": ["Pesan validasi."]
  }
}
```

### 3.4 Pagination

Endpoint produk mengembalikan paginator Laravel di dalam envelope data. Bentuk ringkas:
```json
{
  "success": true,
  "message": "Daftar produk berhasil diambil",
  "data": {
    "current_page": 1,
    "data": [],
    "first_page_url": "...",
    "from": 1,
    "last_page": 4,
    "last_page_url": "...",
    "links": [],
    "next_page_url": "...",
    "path": "...",
    "per_page": 12,
    "prev_page_url": null,
    "to": 12,
    "total": 42
  }
}
```

Karena ada dua lapisan properti bernama data, parser Flutter membaca item melalui data.data dan metadata pagination melalui data.current_page, data.last_page, data.per_page, dan data.total.

## 4. Existing public endpoints

Semua endpoint pada bagian ini terdaftar di /api/* dan alias /api/v1/*. Gunakan hanya /api/v1 di aplikasi mobile.

### API-MOB-001 — List products

```http
GET /products
```

Query parameters:

| Parameter | Type | Default | Keterangan |
|---|---|---|---|
| search | string | — | Pencarian nama, deskripsi, atau nama toko |
| q | string | — | Alias search |
| category | string | — | ID atau slug kategori |
| category_id | string/int | — | Alias category |
| kategori | string/int | — | Alias category |
| seller_id | integer | — | Filter produk berdasarkan penjual |
| penjual_id | integer | — | Alias seller_id |
| min_price | number | — | Harga minimum |
| max_price | number | — | Harga maksimum |
| sort | string | latest | price_asc, price_desc, popular, rating; aliases termurah, termahal, terpopuler juga diterima |
| per_page | integer | 12 | Dibatasi backend antara 1 dan 50 |
| page | integer | 1 | Nomor halaman paginator Laravel |

Perilaku aktual:
- Query menerapkan Produk::publicVisible().
- Produk harus berstatus aktif dan akun user pemilik toko harus aktif.
- Pencarian mencakup nama, deskripsi, dan nama toko.
- Respons produk menyertakan kategori dan ringkasan penjual.
- Respons dipaginasi dan tiap item diformat oleh ProductController::productData().

Field produk yang digunakan mobile:
- id, slug
- nama / name
- harga / price
- deskripsi / description
- stok / stock_status
- available
- label_promosi / promotion_label
- foto_utama / image
- views
- rating / average_rating
- review_count / jumlah_ulasan
- nama_toko, status_toko
- kategori / category
- penjual / seller
- created_at

Rekomendasi client: tetapkan satu nama field kanonis di DTO, tetapi parser dapat membaca alias lama yang tersedia agar tetap kompatibel.

### API-MOB-002 — Product detail

```http
GET /products/{product}
```

Parameter path:
- product: slug atau ID numerik.

Header opsional namun direkomendasikan:
- X-Device-Id: UUID acak aplikasi yang persisten di perangkat.

Perilaku aktual:
- Hanya produk publik yang dapat ditemukan.
- Detail mencakup related_products dan produk_terkait (maksimal empat item).
- Request detail juga menambah views dan membuat log view.
- Jika X-Device-Id dikirim, kenaikan view dideduplikasi melalui cache selama 24 jam.
- Tanpa header tersebut, pengambilan detail dapat menambah view pada setiap request.

Penting: jangan memanggil POST /products/{product}/view setelah GET detail. GET detail sudah melakukan tracking sehingga pemanggilan keduanya berpotensi menghitung view dua kali.

### API-MOB-003 — Generate WhatsApp URL

```http
POST /products/{product}/whatsapp
```

Body: tidak diperlukan.

Perilaku aktual:
- Memvalidasi produk tetap publik.
- Menggunakan WhatsappUrlService untuk membentuk nomor dan pesan.
- Mencatat interaksi WhatsApp pada log produk.
- Rate limit aktual: 30 request per menit.
- Mengembalikan URL WhatsApp yang telah di-encode.

Success data:
```json
{
  "success": true,
  "message": "Tautan WhatsApp berhasil dibuat",
  "data": {
    "whatsapp_url": "https://wa.me/62xxxxxxxxxx?text=...",
    "phone": "62xxxxxxxxxx",
    "message": "Halo, saya tertarik dengan produk...",
    "store_closed": false
  }
}
```

Kondisi pesan:
- Stok tersedia: pesan menanyakan apakah produk masih tersedia.
- Stok po: pesan menanyakan jadwal dan cara pre-order.
- Stok habis: pesan menanyakan kapan produk tersedia kembali; endpoint service tetap dapat menghasilkan tautan untuk pertanyaan tersebut.
- Toko tutup: pesan menyertakan pemberitahuan bahwa balasan mungkin tertunda.
- Nomor WhatsApp kosong: endpoint mengembalikan error HTTP 422.

Client harus membuka data.whatsapp_url apa adanya melalui external URL launcher. Jangan membentuk nomor, mengganti nomor, atau menambahkan pesan sendiri. Pesan tidak dikirim otomatis oleh aplikasi.

### API-MOB-004 — Track product view

```http
POST /products/{product}/view
```

Header:
- X-Device-Id: UUID acak aplikasi.

Response data:
- id
- views
- incremented

Status: existing, tetapi tidak perlu dipanggil dalam alur detail MVP karena GET product detail sudah mencatat view.

### API-MOB-005 — List categories

```http
GET /categories
```

Response item:
- id
- name
- slug
- icon
- product_count

Perilaku aktual:
- Kategori diurutkan berdasarkan nama.
- product_count dihitung dari produk yang memenuhi publicVisible().
- Controller saat ini tidak memfilter kategori berdasarkan kolom status aktif.

### API-MOB-006 — Category detail

```http
GET /categories/{category}
```

Parameter category menerima slug atau ID melalui route binding Kategori.

Response:
- id
- name
- slug
- icon
- product_count

### API-MOB-007 — Products by category

```http
GET /categories/{category}/products?per_page=12&page=1
```

Perilaku:
- Mengembalikan produk publik dalam kategori.
- Pagination Laravel.
- per_page dibatasi 1 sampai 50.
- Urutan terbaru.
- Untuk katalog yang memerlukan kombinasi search, filter harga, seller, dan sort, lebih disarankan memakai GET /products?category={slug}.

### API-MOB-008 — List sellers

```http
GET /sellers
```

Response seller:
- id
- name / nama_toko
- description / deskripsi_toko
- class_location / lokasi_kelas
- store_status / status_toko
- image / foto_toko
- active_product_count
- phone dan nomor_whatsapp bernilai null pada response publik.

### API-MOB-009 — Seller detail

```http
GET /sellers/{seller}
```

Parameter:
- Gunakan seller ID dari field penjual.id pada product response.
- Jangan mengasumsikan slug toko didukung oleh controller saat ini.

Response:
- metadata penjual yang sama dengan list sellers.
- products / produk berisi produk publik milik penjual.

Catatan performa:
- Controller saat ini memuat seluruh produk penjual tanpa pagination pada response detail.
- Untuk layar profil penjual mobile, gunakan GET /sellers/{sellerId} untuk metadata lalu GET /products?seller_id={sellerId}&page=1&per_page=12 untuk daftar produk terpaginasikan. Aplikasi tidak perlu mengonsumsi array produk embedded apabila daftar terpisah digunakan.

### API-MOB-010 — Product reviews (optional)

```http
GET /products/{product}/reviews?per_page=10&page=1
```

Perilaku:
- Mengembalikan ulasan publik dengan pagination.
- per_page dibatasi 1 sampai 50.
- Bukan blocker katalog MVP; gunakan rating dan review_count dari product response bila halaman ulasan penuh belum termasuk scope.

### API-MOB-011 — Submit product review (out of MVP)

```http
POST /products/{product}/reviews
```

Body:
```json
{
  "guest_username": "Nama Pengunjung",
  "rating": 5,
  "comment": "Produk bagus."
}
```

Perilaku:
- Publik, tanpa login.
- guest_username wajib maksimal 100 karakter.
- rating wajib integer 1–5.
- comment atau komentar opsional, maksimal 1000 karakter.
- Dilindungi CommentModerationService.
- Rate limit aktual: 3 request per menit.
- Berhasil membuat ulasan dengan HTTP 201.

Catatan: submit review tidak termasuk MVP kecuali product owner memutuskan sebaliknya.

## 5. Endpoint yang tidak digunakan oleh katalog MVP

| Endpoint | Alasan |
|---|---|
| POST /login dan /register | Pengunjung katalog tidak memerlukan akun |
| POST /logout dan GET /user | Tidak ada sesi akun pengunjung pada MVP |
| GET/PATCH /profile | Profil akun terproteksi |
| GET /seller/dashboard | Fitur penjual, bukan katalog |
| PATCH /seller/store-status | Fitur penjual terproteksi |
| GET/PATCH /notifications | Notifikasi akun terproteksi |
| GET/POST/PATCH/DELETE /cart | Seluruh route cart mengembalikan HTTP 501 sesuai keputusan pemesanan via WhatsApp |

Jangan menyimpan token admin atau penjual pada aplikasi katalog.

## 6. Gaps and recommended changes

| Gap | Status | Rekomendasi |
|---|---|---|
| Banner API | Tidak tersedia; tidak ditemukan entitas/model banner pada audit awal | Tidak menjadi fitur MVP; evaluasi setelah rilis |
| Home aggregator | Tidak tersedia | Compose beranda dari categories + popular + latest. Tambahkan endpoint aggregator hanya bila pengukuran menunjukkan manfaat |
| Seller products pagination | Seller detail saat ini embed semua produk tanpa pagination | Mobile harus memakai GET /products?seller_id=... untuk daftar terpaginasikan |
| Public seller phone | Response publik sengaja menyembunyikan phone | Pertahankan; CTA kontak menggunakan endpoint WhatsApp per produk |
| OpenAPI synchronization | File docs/06-api/openapi.yaml mendokumentasikan operasi yang tidak tampak di routes/api.php saat audit (contoh grup SellerProducts/AdminModeration) | Sinkronkan OpenAPI dengan route:list dan source code sebelum code generation |
| Product detail view tracking | GET detail dan POST view sama-sama melakukan tracking | Gunakan GET detail + X-Device-Id saja pada MVP |
| Response naming | Beberapa payload menyediakan alias Indonesia dan Inggris | DTO Flutter harus menormalisasi field; pertimbangkan satu kontrak kanonis pada API v2 mendatang |

## 7. Public visibility rule

Sumber aturan adalah scope Produk::publicVisible():
1. produk.status harus bernilai aktif.
2. user pemilik penjual harus is_active = true.

Client tidak boleh menampilkan produk pending, ditolak, atau produk milik akun penjual nonaktif. Backend adalah otoritas final dan akan mengembalikan 404 untuk detail produk yang tidak lagi publik.

Status stok adalah domain yang berbeda dari status publikasi:
- tersedia: dapat ditanyakan ketersediaannya.
- po: pesan WhatsApp diarahkan untuk pre-order.
- habis: pesan WhatsApp menanyakan informasi restock.
- status_toko tutup: pesan memberitahu bahwa balasan mungkin tertunda.

Client harus menampilkan status berdasarkan nilai aktual yang dikembalikan backend, bukan menyimpulkan dari status publikasi.

## 8. Rate limits and client behavior

| Endpoint | Existing limit |
|---|---:|
| POST /products/{product}/whatsapp | 30 request / minute |
| POST /products/{product}/view | 60 request / minute |
| POST /products/{product}/reviews | 3 request / minute |

Panduan client:
- Hindari memanggil endpoint tracking berulang.
- Jangan melakukan retry otomatis pada POST WhatsApp.
- Saat menerima HTTP 429, beri pesan singkat dan izinkan pengguna mencoba lagi setelah jeda.
- Gunakan pagination dan debounce agar tidak membanjiri API.

## 9. API test checklist for mobile readiness

- [ ] GET /products mengembalikan hanya produk publicVisible.
- [ ] Pencarian dan filter kategori berjalan.
- [ ] Filter min_price dan max_price berjalan.
- [ ] Semua opsi sort yang didukung diuji.
- [ ] Paginator terbaca sebagai data.data dan metadata data.current_page, data.last_page.
- [ ] GET detail dengan slug dan ID diuji.
- [ ] X-Device-Id melakukan deduplikasi view 24 jam.
- [ ] POST WhatsApp menghasilkan URL valid untuk status tersedia, po, dan habis.
- [ ] Kondisi toko tutup menambahkan pemberitahuan pesan.
- [ ] Nomor kosong menghasilkan HTTP 422.
- [ ] Kategori tidak ditemukan menghasilkan HTTP 404.
- [ ] Seller public response tidak mengekspos phone.
- [ ] Seller product listing melalui seller_id mendukung pagination.
- [ ] HTTP 429 ditangani di Flutter.
- [ ] API v1 dan dokumentasi OpenAPI sudah direkonsiliasi.

## 10. Source references

- routes/api.php
- bootstrap/app.php
- app/Http/Controllers/Api/ApiController.php
- app/Http/Controllers/Api/ProductController.php
- app/Http/Controllers/Api/CategoryController.php
- app/Http/Controllers/Api/SellerController.php
- app/Http/Controllers/Api/ReviewController.php
- app/Models/Produk.php
- app/Models/Kategori.php
- app/Services/WhatsappUrlService.php
- docs/06-api/API.md
- docs/06-api/openapi.yaml
- docs/03-arsitektur/ARSITEKTUR.md
- docs/05-desain/DESIGN_SYSTEM.md
