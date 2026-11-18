# Dokumentasi REST API & Rute Publik — SiswaMart

| Informasi Dokumen | Detail |
| :--- | :--- |
| **Judul** | Dokumentasi REST API (`/api/*` & `/api/v1/*`) dan Rute Web Publik Penting SiswaMart |
| **Versi** | `1.2.0` |
| **Tanggal** | 18 November 2026 |
| **Status** | Final (Source-Code & `php artisan route:list` Verified) |
| **Penulis** | Tim Dokumentasi Teknis & Rekayasa Perangkat Lunak |
| **Sumber Data** | `routes/api.php`, `routes/web.php`, `bootstrap/app.php`, `app/Http/Controllers/Api/*.php`, `app/Http/Controllers/Public/*.php` |

---

## Daftar Isi

1. [Konvensi Umum API & Amplop Respons JSON](#1-konvensi-umum-api--amplop-respons-json)
2. [Autentikasi, Keamanan, dan Pembatasan Laju (Rate Limiting)](#2-autentikasi-keamanan-dan-pembatasan-laju-rate-limiting)
3. [Indeks Lengkap 37 Rute REST API (`/api/*` & `/api/v1/*`)](#3-indeks-lengkap-37-rute-rest-api-api--apiv1)
4. [Spesifikasi Detail Endpoint API per Kelompok](#4-spesifikasi-detail-endpoint-api-per-kelompok)
   - [4.1 Autentikasi (`API-001` s.d. `API-004`)](#41-autentikasi-api-001-sd-api-004)
   - [4.2 Katalog Publik: Produk, Kategori, Penjual (`API-005` s.d. `API-013`)](#42-katalog-publik-produk-kategori-penjual-api-005-sd-api-013)
   - [4.3 Ulasan Produk (`API-014` s.d. `API-015`)](#43-ulasan-produk-api-014-sd-api-015)
   - [4.4 Profil Akun & Dasbor Penjual (`API-016` s.d. `API-019`)](#44-profil-akun--dasbor-penjual-api-016-sd-api-019)
   - [4.5 Notifikasi Pengguna (`API-020` s.d. `API-022`)](#45-notifikasi-pengguna-api-020-sd-api-022)
   - [4.6 Keranjang Belanja — Ditangguhkan HTTP 501 (`API-023` s.d. `API-026`)](#46-keranjang-belanja--ditangguhkan-http-501-api-023-sd-api-026)
5. [Dokumentasi Rute Web Publik Penting (`WEB-PUB-001` s.d. `WEB-PUB-004`)](#5-dokumentasi-rute-web-publik-penting-web-pub-001-sd-web-pub-004)
6. [Struktur Objek JSON Standar (Resource Transformers)](#6-struktur-objek-json-standar-resource-transformers)
7. [Rujukan Kode](#7-rujukan-kode)

---

## 1. Konvensi Umum API & Amplop Respons JSON

Seluruh endpoint REST API berada di bawah prefix `/api` (dengan alias `/api/v1` untuk 11 rute publik) dan mewarisi metode pembantu dari `App\Http\Controllers\Api\ApiController` (`app/Http/Controllers/Api/ApiController.php`).

### 1.1 Amplop Respons Sukses (`ApiController::success`)
```json
{
  "success": true,
  "message": "Pesan penjelasan keberhasilan.",
  "data": { ... }
}
```
*Catatan: Jika `data` bernilai `null`, kunci `"data"` tidak disertakan di dalam JSON (`ApiController.php`).*

### 1.2 Amplop Respons Kesalahan Umum (`ApiController::error`)
```json
{
  "success": false,
  "message": "Pesan kesalahan yang ramah pengguna.",
  "errors": { ... }
}
```

### 1.3 Amplop Kesalahan Validasi (`HTTP 422 Unprocessable Entity` — `bootstrap/app.php`)
```json
{
  "success": false,
  "message": "Data yang dikirim tidak valid.",
  "errors": {
    "nama_field": [
      "Pesan rincian kesalahan validasi."
    ]
  }
}
```

### 1.4 Daftar Kode Status HTTP
| Kode HTTP | Arti | Kondisi Pemicu di SiswaMart |
| :---: | :--- | :--- |
| `200 OK` | Berhasil | Pengambilan data, pembaruan data, atau perubahan status berhasil. |
| `201 Created` | Berhasil Dibuat | Pengiriman ulasan baru (`API-015`). |
| `401 Unauthorized` | Belum Terautentikasi | Header `Authorization: Bearer <token>` kosong, kedaluwarsa, atau tidak valid. |
| `403 Forbidden` | Akses Ditolak | Peran tidak sesuai (`admin` vs `penjual`), bukan pemilik sumber daya, mencoba `POST /api/register`, atau akun dinonaktifkan (`is_active = false`). |
| `404 Not Found` | Tidak Ditemukan | Produk tidak ditemukan, belum `aktif`, atau pemilik toko sedang nonaktif. |
| `422 Unprocessable Entity` | Validasi Gagal | Input form tidak valid, kata kasar terdeteksi pada ulasan, atau konflik status moderasi. |
| `429 Too Many Requests` | Terlalu Banyak Permintaan | Melebihi kuota *rate limit* (`throttle`). |
| `501 Not Implemented` | Belum Diimplementasikan | Seluruh operasi pada `/api/cart*` karena pemesanan dialihkan ke WhatsApp (`ADR-001`). |

---

## 2. Autentikasi, Keamanan, dan Pembatasan Laju (Rate Limiting)

1. **Laravel Sanctum Bearer Token:**
   - Klien memperoleh token dari `POST /api/login` (`API-001`).
   - Kirimkan header `Authorization: Bearer <plainTextToken>` dan `Accept: application/json` pada seluruh endpoint terproteksi.
2. **Pencabutan Token Otomatis (`EnsureUserIsActive`):**
   - Seluruh rute terproteksi dilindungi middleware `['api', 'auth:sanctum', 'active']`.
   - Jika pengguna yang sedang memegang token dinonaktifkan oleh Admin (`is_active == false`), middleware `EnsureUserIsActive` (`app/Http/Middleware/EnsureUserIsActive.php#L16-L23`) otomatis menghapus seluruh token pengguna (`$user->tokens()->delete()`) dan mengembalikan HTTP `403`.
3. **Daftar Rate Limit Aktual (`routes/api.php` & `routes/web.php`):**
   - `POST /api/login`: `throttle:5,1` (5 permintaan/menit) + `RateLimiter` internal 5 percobaan gagal per `login|IP`.
   - `POST /api/products/{product}/reviews` & `/api/v1/products/{product}/reviews`: `throttle:3,1` (3 permintaan/menit per IP).
   - `POST /api/products/{product}/view` & `/api/v1/products/{product}/view`: `throttle:60,1` (60 permintaan/menit per IP).
   - `POST /api/products/{product}/whatsapp` & `/api/v1/products/{product}/whatsapp`: `throttle:30,1` (30 permintaan/menit per IP).
   - `GET /{penjual:slug}/{produk:slug}` (Web): `throttle:60,1` (60 permintaan/menit per IP).
   - `GET /{penjual:slug}/{produk:slug}/whatsapp` (Web): `throttle:30,1` (30 permintaan/menit per IP).
   - `POST /{penjual:slug}/{produk:slug}/ulasan` (Web): `throttle:3,1` (3 permintaan/menit per IP).

---

## 3. Indeks Lengkap 37 Rute REST API (`routes/api.php`)

Total rute `/api/*` dari `php artisan route:list` adalah **37 rute**, terdiri atas **11 endpoint katalog/ulasan publik** di `/api/*` + **11 rute alias** identik di `/api/v1/*`, **2 rute autentikasi publik**, serta **13 rute terproteksi** `['auth:sanctum', 'active']`:

| ID API | Method | URI (`/api/*` & `/api/v1/*`) | Kontroler & Metode | Auth & Middleware | Status |
| :--- | :---: | :--- | :--- | :--- | :---: |
| **API-001** | `POST` | `/api/login` | `Api\AuthController@login` | Publik (`throttle:5,1`) | `[Implemented]` |
| **API-002** | `POST` | `/api/register` | `Api\AuthController@register` | Publik — **Selalu `403`** | `[Implemented]` |
| **API-003** | `POST` | `/api/logout` | `Api\AuthController@logout` | `auth:sanctum`, `active` | `[Implemented]` |
| **API-004** | `GET` | `/api/user` | `Api\AuthController@user` | `auth:sanctum`, `active` | `[Implemented]` |
| **API-005** | `GET` | `/api/products` *(+ `/api/v1/...`)* | `Api\ProductController@index` | Publik | `[Implemented]` |
| **API-006** | `GET` | `/api/products/{product}` *(+ `/api/v1/...`)* | `Api\ProductController@show` | Publik | `[Implemented]` |
| **API-007** | `POST` | `/api/products/{product}/whatsapp` *(+ `/api/v1/...`)* | `Api\ProductController@trackWhatsapp` | Publik (`throttle:30,1`) | `[Implemented]` |
| **API-008** | `POST` | `/api/products/{product}/view` *(+ `/api/v1/...`)* | `Api\ProductController@trackView` | Publik (`throttle:60,1`) | `[Implemented]` |
| **API-009** | `GET` | `/api/categories` *(+ `/api/v1/...`)* | `Api\CategoryController@index` | Publik | `[Implemented]` |
| **API-010** | `GET` | `/api/categories/{category}` *(+ `/api/v1/...`)* | `Api\CategoryController@show` | Publik | `[Implemented]` |
| **API-011** | `GET` | `/api/categories/{category}/products` *(+ `/api/v1/...`)* | `Api\CategoryController@products` | Publik | `[Implemented]` |
| **API-012** | `GET` | `/api/sellers` *(+ `/api/v1/...`)* | `Api\SellerController@index` | Publik | `[Implemented]` |
| **API-013** | `GET` | `/api/sellers/{seller}` *(+ `/api/v1/...`)* | `Api\SellerController@show` | Publik | `[Implemented]` |
| **API-014** | `GET` | `/api/products/{product}/reviews` *(+ `/api/v1/...`)* | `Api\ReviewController@index` | Publik | `[Implemented]` |
| **API-015** | `POST` | `/api/products/{product}/reviews` *(+ `/api/v1/...`)* | `Api\ReviewController@store` | Publik (`throttle:3,1`) | `[Implemented]` |
| **API-016** | `GET` | `/api/profile` | `Api\ProfileController@show` | `auth:sanctum`, `active` | `[Implemented]` |
| **API-017** | `PATCH` | `/api/profile` | `Api\ProfileController@update` | `auth:sanctum`, `active` | `[Implemented]` |
| **API-018** | `GET` | `/api/seller/dashboard` | `Api\SellerController@dashboard` | `auth:sanctum`, `active` (Penjual) | `[Implemented]` |
| **API-019** | `PATCH` | `/api/seller/store-status` | `Api\SellerController@toggleStoreStatus` | `auth:sanctum`, `active` (Penjual) | `[Implemented]` |
| **API-020** | `GET` | `/api/notifications` | `Api\NotificationController@index` | `auth:sanctum`, `active` | `[Implemented]` |
| **API-021** | `PATCH` | `/api/notifications/{notification}/read` | `Api\NotificationController@read` | `auth:sanctum`, `active` | `[Implemented]` |
| **API-022** | `PATCH` | `/api/notifications/read-all` | `Api\NotificationController@readAll` | `auth:sanctum`, `active` | `[Implemented]` |
| **API-023..026** | `GET`, `POST`, `PATCH`, `DELETE` | `/api/cart`, `/api/cart/{cart}` | `Api\CartController` (4 rute HTTP **501**) | `auth:sanctum`, `active` | `[Planned / 501]` |

*(Perhitungan total rute: `11` rute publik `/api/*` + `11` rute alias `/api/v1/*` + `2` rute autentikasi publik + `13` rute terproteksi = **37 rute**).*

---

## 4. Spesifikasi Detail Endpoint API per Kelompok

### 4.1 Autentikasi (`API-001` s.d. `API-004`)
**Kontroler:** `app/Http/Controllers/Api/AuthController.php`

#### `API-001` — `POST /api/login`
- **Auth:** Publik (`throttle:5,1`)
- **Validasi Body JSON:**
  - `email`: `required|email`
  - `password`: `required|string`
- **Contoh Request:**
  ```json
  {
    "email": "budi@smk.sch.id",
    "password": "PasswordRahasia123"
  }
  ```
- **Contoh Respons `200 OK`:**
  ```json
  {
    "success": true,
    "message": "Login berhasil",
    "data": {
      "user": {
        "id": 2,
        "name": "Budi Santoso",
        "email": "budi@smk.sch.id",
        "role": "penjual",
        "is_active": true
      },
      "token": "1|laravel_sanctum_token_example..."
    }
  }
  ```

#### `API-002` — `POST /api/register`
- **Auth:** Publik
- **Perilaku:** Selalu mengembalikan HTTP `403 Forbidden` karena pendaftaran penjual hanya dikelola oleh Admin (`ADR-004`).
- **Respons `403 Forbidden`:**
  ```json
  {
    "success": false,
    "message": "Pendaftaran mandiri akun penjual melalui API tidak diizinkan. Akun penjual dikelola secara terpusat oleh pihak sekolah."
  }
  ```

#### `API-003` — `POST /api/logout`
- **Auth:** `Bearer Token` (`auth:sanctum`, `active`)
- **Perilaku:** Menghapus `currentAccessToken()` yang sedang digunakan.
- **Respons `200 OK`:** `{"success": true, "message": "Logout berhasil"}`

#### `API-004` — `GET /api/user`
- **Auth:** `Bearer Token` (`auth:sanctum`, `active`)
- **Respons `200 OK`:** Mengembalikan objek `user` (`id`, `name`, `email`, `role`, `is_active`).

---

### 4.2 Katalog Publik: Produk, Kategori, Penjual (`API-005` s.d. `API-013`)
Seluruh endpoint pada bagian ini tersedia di prefix `/api/*` maupun `/api/v1/*`.

#### `API-005` — `GET /api/products` (`Api\ProductController@index`)
- **Query Parameters:**
  - `search` / `q` *(string)*: Pencarian `nama`, `deskripsi`, atau `penjual.nama_toko`.
  - `category` / `category_id` / `kategori` *(string/int)*: ID atau slug kategori.
  - `seller_id` / `penjual_id` *(int)*: Filter ID penjual.
  - `min_price`, `max_price` *(numeric)*: Rentang harga produk.
  - `sort` *(string)*: `'price_asc'`/`'termurah'`, `'price_desc'`/`'termahal'`, `'popular'`/`'terpopuler'`, `'rating'`, atau default `latest`.
  - `per_page` *(int)*: `1` s.d. `50` (default `12`).
- **Respons `200 OK`:** Objek paginasi Laravel berisi array `productData`.

#### `API-006` — `GET /api/products/{product}` (`Api\ProductController@show`)
- **Perilaku:** Mencari produk aktif by `slug` atau `id`, memverifikasi `status === 'aktif'` dan pemilik toko aktif (`404` bila tidak), menambah `views` (`+1`) dengan deduplikasi cache 24 jam bila menyertakan header `X-Device-Id`, mencatat `ProdukViewLog` (`type = 'view'`), dan mengembalikan detail produk beserta `related_products` (`produk_terkait`) maksimal 4 item.

#### `API-007` — `POST /api/products/{product}/whatsapp` (`Api\ProductController@trackWhatsapp`, `throttle:30,1`)
- **Perilaku:** Memanggil `WhatsappUrlService::generateForProduct()`, mencatat `ProdukViewLog` (`type = 'whatsapp'`), dan mengembalikan `whatsapp_url`, `phone`, `message`, serta `store_closed` (atau `422` bila nomor kosong / stok `habis`).

#### `API-008` — `POST /api/products/{product}/view` (`Api\ProductController@trackView`, `throttle:60,1`)
- **Perilaku:** Mencatat view produk dengan dukungan deduplikasi 24 jam via header `X-Device-Id`, mengembalikan `{id, views, incremented}`.

#### `API-009` & `API-010` — `GET /api/categories` & `GET /api/categories/{category}` (`Api\CategoryController`)
- **Respons `200 OK`:** Daftar/detail kategori (`id`, `name`, `slug`, `icon`, `product_count` untuk produk `publicVisible`).

#### `API-011` — `GET /api/categories/{category}/products` (`Api\CategoryController@products`)
- **Query Parameters:** `per_page` (`1..50`, default `12`).
- **Respons `200 OK`:** Daftar paginasi produk aktif pada kategori terkait.

#### `API-012` & `API-013` — `GET /api/sellers` & `GET /api/sellers/{seller}` (`Api\SellerController`)
- **Perilaku:** Menampilkan penjual yang `user.is_active == true` (mencari by `id` atau `nama_toko` pada `show`), lengkap dengan daftar `products` / `produk` pada endpoint detail. Nomor WhatsApp disembunyikan (`null`) pada endpoint publik.

---

### 4.3 Ulasan Produk (`API-014` s.d. `API-015`)
**Kontroler:** `app/Http/Controllers/Api/ReviewController.php`

#### `API-014` — `GET /api/products/{product}/reviews`
- **Query Parameters:** `per_page` (`1..50`, default `10`).
- **Respons `200 OK`:** Daftar paginasi ulasan `publicVisible()` urut terbaru.

#### `API-015` — `POST /api/products/{product}/reviews`
- **Auth:** Publik (`throttle:3,1`)
- **Validasi Body JSON:**
  - `guest_username`: `required|string|max:100` (diperiksa `CommentModerationService`).
  - `rating`: `required|integer|min:1|max:5`.
  - `comment` / `komentar`: `nullable|string|max:1000` (diperiksa `CommentModerationService`).
- **Respons `201 Created`:** Mengembalikan objek ulasan baru dan mengirim notifikasi ke Admin.
- **Respons `422 Unprocessable Entity` (Jika Mengandung Kata Kasar):**
  ```json
  {
    "success": false,
    "message": "Data yang dikirim tidak valid.",
    "errors": {
      "komentar": [
        "Ulasan mengandung kata yang tidak pantas. Silakan perbaiki lalu kirim kembali."
      ]
    }
  }
  ```

---

### 4.4 Profil Akun & Dasbor Penjual (`API-016` s.d. `API-019`)

#### `API-016` & `API-017` — `GET /api/profile` & `PATCH /api/profile` (`Api\ProfileController`)
- **Auth:** `Bearer Token` (`auth:sanctum`, `active`)
- **Validasi `PATCH`:** `name` (`sometimes|required|string|max:255`), `email` (`sometimes|required|email|max:255|unique`). Reset `email_verified_at` bila email berubah.

#### `API-018` — `GET /api/seller/dashboard` (`Api\SellerController@dashboard`)
- **Auth:** `Bearer Token` (`auth:sanctum`, `active`, wajib memiliki profil `penjual`, jika tidak mengembalikan `403`).
- **Respons `200 OK`:** Mengembalikan `seller` (termasuk `phone` / `nomor_whatsapp` pemilik), `stats` (`total_products`, `active_products`, `pending_products`, `rejected_products`, `total_views`, `total_reviews`), dan hingga `10` produk terbaru (`products`) beserta `moderation_status`, `pending_action`, dan `rejection_reason`.

#### `API-019` — `PATCH /api/seller/store-status` (`Api\SellerController@toggleStoreStatus`)
- **Auth:** `Bearer Token` (`auth:sanctum`, `active`, wajib memiliki profil `penjual`).
- **Perilaku:** Membalik `penjual.status_toko` antara `'buka'` dan `'tutup'`.

---

### 4.5 Notifikasi Pengguna (`API-020` s.d. `API-022`)
**Kontroler:** `app/Http/Controllers/Api/NotificationController.php`

#### `API-020` — `GET /api/notifications`
- **Respons `200 OK`:** Mengembalikan paginasi 20 notifikasi terbaru (`id`, `type`, `data`, `read_at`, `created_at`).

#### `API-021` — `PATCH /api/notifications/{notification}/read`
- **Respons `200 OK`:** Menandai satu notifikasi sebagai sudah dibaca (`markAsRead()`). Mengembalikan `404` jika UUID tidak ditemukan pada akun tersebut.

#### `API-022` — `PATCH /api/notifications/read-all`
- **Respons `200 OK`:** Menandai seluruh notifikasi yang belum dibaca milik pengguna sebagai sudah dibaca.

---

### 4.6 Keranjang Belanja — Ditangguhkan HTTP 501 (`API-023` s.d. `API-026`)
**Kontroler:** `app/Http/Controllers/Api/CartController.php`

Keempat rute berikut terdaftar di `routes/api.php` dan secara sengaja mengembalikan kode status **`501 Not Implemented`**:
- `GET /api/cart`
- `POST /api/cart`
- `PATCH /api/cart/{cart}`
- `DELETE /api/cart/{cart}`

**Respons JSON Aktual (`501 Not Implemented`):**
```json
{
  "success": false,
  "message": "Fitur keranjang internal belum diimplementasikan. Pemesanan produk SiswaMart saat ini dilakukan langsung melalui WhatsApp penjual.",
  "errors": {
    "code": "CART_NOT_IMPLEMENTED",
    "order_channel": "whatsapp"
  }
}
```

---

## 5. Dokumentasi Rute Web Publik Penting (`WEB-PUB-001` s.d. `WEB-PUB-004`)

Selain `/api/*`, rute web publik di `routes/web.php` melayani katalog produk terlingkup toko dan redirect kompatibilitas URL lama:

| ID Rute Web | Method & URI | Nama Rute | Middleware | Perilaku & Respons |
| :--- | :--- | :--- | :--- | :--- |
| **WEB-PUB-001** | `GET /{penjual:slug}/{produk:slug}` | `public.produk.show` | `web`, `throttle:60,1` (`scopeBindings`) | Menampilkan halaman detail produk (`200 OK`), menambah `views` (`+1` sekali per sesi), dan menyimpan `ProdukViewLog` (`type = 'view'`). Mengembalikan `404` jika produk tidak cocok toko atau tidak `publicVisible`. |
| **WEB-PUB-002** | `GET /{penjual:slug}/{produk:slug}/whatsapp` | `public.produk.whatsapp` | `web`, `throttle:30,1` (`scopeBindings`) | Mencatat `ProdukViewLog` (`type = 'whatsapp'`) lalu mengembalikan `302 Redirect` ke `https://wa.me/62...` (atau JSON bila AJAX) sesuai `status_toko`. Jika `stok === 'habis'`, menolak pemesanan. |
| **WEB-PUB-003** | `POST /{penjual:slug}/{produk:slug}/ulasan` | `public.ulasan.store` | `web`, `throttle:3,1` (`scopeBindings`) | Memvalidasi CSRF token, `guest_username`, `rating`, `komentar`, menjalankan `CommentModerationService`, menyimpan ulasan, memicu notifikasi Admin, dan mengembalikan `302 Redirect` ke `#ulasan`. |
| **WEB-PUB-004** | `GET /produk/{slug}` | `public.produk.legacy` | `web` | Mengalihkan (`301 Moved Permanently`) tautan lama ke rute canonical `/{penjual:slug}/{produk:slug}`. |

---

## 6. Struktur Objek JSON Standar (Resource Transformers)

Karena proyek menggunakan metode transformer terpusat di kontroler API (bukan kelas `JsonResource` terpisah), berikut struktur pasti dari objek JSON utama yang dikembalikan `ProductController::productData()` dan endpoint pemesanan WhatsApp:

### 6.1 Objek Produk Publik (`Api\ProductController::productData`)
```json
{
  "id": 10,
  "slug": "risoles-mayo-crispy",
  "nama": "Risoles Mayo Crispy",
  "name": "Risoles Mayo Crispy",
  "harga": 5000.0,
  "price": 5000.0,
  "deskripsi": "Risoles isi smoked beef, telur, dan keju lumer.",
  "description": "Risoles isi smoked beef, telur, dan keju lumer.",
  "stok": "tersedia",
  "stock_status": "tersedia",
  "available": true,
  "label_promosi": "Best Seller",
  "promotion_label": "Best Seller",
  "foto_utama": "https://siswamart.app/storage/produk/risoles.jpg",
  "image": "https://siswamart.app/storage/produk/risoles.jpg",
  "views": 42,
  "rating": 4.8,
  "average_rating": 4.8,
  "review_count": 5,
  "jumlah_ulasan": 5,
  "nama_toko": "Dapur Tata Boga",
  "status_toko": "buka",
  "kategori": {
    "id": 1,
    "nama": "Kuliner",
    "name": "Kuliner",
    "slug": "kuliner",
    "icon": "utensils"
  },
  "penjual": {
    "id": 3,
    "nama_toko": "Dapur Tata Boga",
    "name": "Dapur Tata Boga",
    "lokasi_kelas": "XI Kuliner 2",
    "class_location": "XI Kuliner 2",
    "status_toko": "buka",
    "store_status": "buka",
    "foto_toko": null,
    "image": null
  },
  "created_at": "2026-10-20T08:00:00.000000Z"
}
```
*(Catatan: pada detail produk (`detail = true`) disertakan juga `related_products` & `produk_terkait`; tautan pemesanan web canonical adalah `https://siswamart.app/dapur-tata-boga/risoles-mayo-crispy/whatsapp`, sedangkan endpoint `POST /api/products/{product}/whatsapp` mengembalikan `whatsapp_url`, `phone`, `message`, dan `store_closed`).*

---

## 7. Rujukan Kode

- `routes/api.php`
- `routes/web.php`
- `bootstrap/app.php`
- `app/Http/Controllers/Api/ApiController.php`
- `app/Http/Controllers/Api/AuthController.php`
- `app/Http/Controllers/Api/ProductController.php`
- `app/Http/Controllers/Api/CategoryController.php`
- `app/Http/Controllers/Api/SellerController.php`
- `app/Http/Controllers/Api/ReviewController.php`
- `app/Http/Controllers/Api/ProfileController.php`
- `app/Http/Controllers/Api/NotificationController.php`
- `app/Http/Controllers/Api/CartController.php`
