# Dokumentasi REST API & Rute Publik — SiswaMart

| Informasi Dokumen | Detail |
| :--- | :--- |
| **Judul** | Dokumentasi REST API (`/api/*`) dan Rute Web Publik Penting SiswaMart |
| **Versi** | `1.1.0` |
| **Tanggal** | 27 Oktober 2026 |
| **Status** | Final (Source-Code & `php artisan route:list` Verified) |
| **Penulis** | Tim Dokumentasi Teknis & Rekayasa Perangkat Lunak |
| **Sumber Data** | `routes/api.php`, `routes/web.php`, `bootstrap/app.php`, `app/Http/Controllers/Api/*.php`, `app/Http/Controllers/Public/*.php` |

---

## Daftar Isi

1. [Konvensi Umum API & Amplop Respons JSON](#1-konvensi-umum-api--amplop-respons-json)
2. [Autentikasi, Keamanan, dan Pembatasan Laju (Rate Limiting)](#2-autentikasi-keamanan-dan-pembatasan-laju-rate-limiting)
3. [Indeks Lengkap 26 Endpoint REST API (`API-001` s.d. `API-026`)](#3-indeks-lengkap-26-endpoint-rest-api-api-001-sd-api-026)
4. [Spesifikasi Detail Endpoint API per Kelompok](#4-spesifikasi-detail-endpoint-api-per-kelompok)
   - [4.1Autentikasi (`API-001` s.d. `API-004`)](#41-autentikasi-api-001-sd-api-004)
   - [4.2 Katalog Publik: Produk, Kategori, Penjual (`API-005` s.d. `API-012`)](#42-katalog-publik-produk-kategori-penjual-api-005-sd-api-012)
   - [4.3 Ulasan Produk (`API-013` s.d. `API-015`)](#43-ulasan-produk-api-013-sd-api-015)
   - [4.4 Profil Akun & Toko (`API-016` s.d. `API-017`)](#44-profil-akun--toko-api-016-sd-api-017)
   - [4.5 Manajemen Produk Penjual (`API-018` s.d. `API-021`)](#45-manajemen-produk-penjual-api-018-sd-api-021)
   - [4.6 Moderasi Produk Admin (`API-022` s.d. `API-023`)](#46-moderasi-produk-admin-api-022-sd-api-023)
   - [4.7 Notifikasi Pengguna (`API-024` s.d. `API-025`)](#47-notifikasi-pengguna-api-024-sd-api-025)
   - [4.8 Keranjang Belanja — Ditangguhkan HTTP 501 (`API-026`)](#48-keranjang-belanja--ditangguhkan-http-501-api-026)
5. [Dokumentasi Rute Web Publik Penting (`WEB-PUB-001` s.d. `WEB-PUB-003`)](#5-dokumentasi-rute-web-publik-penting-web-pub-001-sd-web-pub-003)
6. [Struktur Objek JSON Standar (Resource Transformers)](#6-struktur-objek-json-standar-resource-transformers)
7. [Rujukan Kode](#7-rujukan-kode)

---

## 1. Konvensi Umum API & Amplop Respons JSON

Seluruh endpoint REST API berada di bawah prefix `/api` dan mewarisi metode pembantu dari `App\Http\Controllers\Api\ApiController` (`app/Http/Controllers/Api/ApiController.php#L9-L38`).

### 1.1 Amplop Respons Sukses (`ApiController::success`)
```json
{
  "success": true,
  "message": "Pesan penjelasan keberhasilan.",
  "data": { ... }
}
```
*Catatan: Jika `data` bernilai `null`, kunci `"data"` tidak disertakan di dalam JSON (`ApiController.php#L18-L20`).*

### 1.2 Amplop Respons Kesalahan Umum (`ApiController::error`)
```json
{
  "success": false,
  "message": "Pesan kesalahan yang ramah pengguna.",
  "errors": { ... }
}
```

### 1.3 Amplop Kesalahan Validasi (`HTTP 422 Unprocessable Entity` — `bootstrap/app.php#L28-L43`)
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
| `200 OK` | Berhasil | Pengambilan data, pembaruan data, atau penghapusan berhasil. |
| `201 Created` | Berhasil Dibuat | Pengiriman ulasan baru (`API-014`) atau pengajuan produk baru (`API-019`). |
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
   - `POST /api/login` & `POST /api/register`: `throttle:5,1` (5 permintaan/menit) + `RateLimiter` internal 5 percobaan gagal per `login|IP`.
   - `POST /api/products/{produk:slug}/reviews`: `throttle:10,1` (10 permintaan/menit per IP).
   - `GET /produk/{produk:slug}` (Web): `throttle:60,1` (60 permintaan/menit per IP).
   - `GET /produk/{produk:slug}/whatsapp` (Web): `throttle:30,1` (30 permintaan/menit per IP).
   - `POST /produk/{produk:slug}/ulasan` (Web): `throttle:3,1` (3 permintaan/menit per IP).

---

## 3. Indeks Lengkap 26 Endpoint REST API (`API-001` s.d. `API-026`)

Jumlah rute `/api/*` dari `php artisan route:list --path=api` adalah **26 rute** (12 publik + 14 terproteksi `auth:sanctum,active`):

| ID API | Method | URI | Nama Rute | Kontroler & Metode | Auth & Middleware | Status |
| :--- | :---: | :--- | :--- | :--- | :--- | :---: |
| **API-001** | `POST` | `/api/login` | `api.auth.login` | `Api\AuthController@login` | Publik (`throttle:5,1`) | `[Implemented]` |
| **API-002** | `POST` | `/api/register` | `api.auth.register` | `Api\AuthController@register` | Publik (`throttle:5,1`) — **Selalu 403** | `[Implemented]` |
| **API-003** | `GET` | `/api/me` | `api.auth.me` | `Api\AuthController@me` | `auth:sanctum`, `active` | `[Implemented]` |
| **API-004** | `POST` | `/api/logout` | `api.auth.logout` | `Api\AuthController@logout` | `auth:sanctum`, `active` | `[Implemented]` |
| **API-005** | `GET` | `/api/products` | `api.products.index` | `Api\ProductController@index` | Publik | `[Implemented]` |
| **API-006** | `GET` | `/api/products/search` | `api.products.search` | `Api\ProductController@search` | Publik | `[Implemented]` |
| **API-007** | `GET` | `/api/products/popular` | `api.products.popular` | `Api\ProductController@popular` | Publik | `[Implemented]` |
| **API-008** | `GET` | `/api/products/{produk:slug}` | `api.products.show` | `Api\ProductController@show` | Publik | `[Implemented]` |
| **API-009** | `GET` | `/api/categories` | `api.categories.index` | `Api\CategoryController@index` | Publik | `[Implemented]` |
| **API-010** | `GET` | `/api/categories/{kategori:slug}/products` | `api.categories.products` | `Api\CategoryController@products` | Publik | `[Implemented]` |
| **API-011** | `GET` | `/api/sellers` | `api.sellers.index` | `Api\SellerController@index` | Publik | `[Implemented]` |
| **API-012** | `GET` | `/api/sellers/{penjual}` | `api.sellers.show` | `Api\SellerController@show` | Publik | `[Implemented]` |
| **API-013** | `GET` | `/api/products/{produk:slug}/reviews` | `api.reviews.index` | `Api\ReviewController@index` | Publik | `[Implemented]` |
| **API-014** | `POST` | `/api/products/{produk:slug}/reviews` | `api.reviews.store` | `Api\ReviewController@store` | Publik (`throttle:10,1`) | `[Implemented]` |
| **API-015** | `DELETE` | `/api/reviews/{ulasan}` | `api.reviews.destroy` | `Api\ReviewController@destroy` | `auth:sanctum`, `active` (Admin/Pemilik) | `[Implemented]` |
| **API-016** | `GET` | `/api/profile` | `api.profile.show` | `Api\ProfileController@show` | `auth:sanctum`, `active` | `[Implemented]` |
| **API-017** | `PUT` | `/api/profile` | `api.profile.update` | `Api\ProfileController@update` | `auth:sanctum`, `active` | `[Implemented]` |
| **API-018** | `GET` | `/api/seller/products` | `api.seller.products.index` | `Api\SellerProductController@index` | `auth:sanctum`, `active` (Penjual) | `[Implemented]` |
| **API-019** | `POST` | `/api/seller/products` | `api.seller.products.store` | `Api\SellerProductController@store` | `auth:sanctum`, `active` (Penjual) | `[Implemented]` |
| **API-020** | `PUT` | `/api/seller/products/{produk}` | `api.seller.products.update` | `Api\SellerProductController@update` | `auth:sanctum`, `active` (Penjual) | `[Implemented]` |
| **API-021** | `DELETE` | `/api/seller/products/{produk}` | `api.seller.products.destroy` | `Api\SellerProductController@destroy` | `auth:sanctum`, `active` (Penjual) | `[Implemented]` |
| **API-022** | `GET` | `/api/admin/moderation/products` | `api.admin.moderation.products.index` | `Api\AdminModerationController@index` | `auth:sanctum`, `active` (Admin) | `[Implemented]` |
| **API-023** | `PATCH` | `/api/admin/moderation/products/{produk}/status` | `api.admin.moderation.products.update_status` | `Api\AdminModerationController@updateStatus` | `auth:sanctum`, `active` (Admin) | `[Implemented]` |
| **API-024** | `GET` | `/api/notifications` | `api.notifications.index` | `Api\NotificationController@index` | `auth:sanctum`, `active` | `[Implemented]` |
| **API-025** | `PATCH` | `/api/notifications/{id}/read` | `api.notifications.read` | `Api\NotificationController@markAsRead` | `auth:sanctum`, `active` | `[Implemented]` |
| **API-026** | `GET`, `POST`, `PATCH`, `DELETE` | `/api/cart`, `/api/cart/{itemId}` | `api.cart.index`, `store`, `update`, `destroy` | `Api\CartController` (4 rute HTTP **501**) | `auth:sanctum`, `active` | `[Planned / 501]` |

*(Catatan: `API-026` mencakup 4 rute HTTP di `routes/api.php#L71-L75`: `GET /api/cart`, `POST /api/cart`, `PATCH /api/cart/{itemId}`, dan `DELETE /api/cart/{itemId}`, sehingga total rute terdaftar adalah 22 + 4 = **26 rute**).*

---

## 4. Spesifikasi Detail Endpoint API per Kelompok

### 4.1 Autentikasi (`API-001` s.d. `API-004`)
**Kontroler:** `app/Http/Controllers/Api/AuthController.php`

#### `API-001` — `POST /api/login`
- **Auth:** Publik (`throttle:5,1`)
- **Validasi Body JSON:**
  - `login` *(opsional jika `email` diisi)*: `required_without:email|string|max:255` (bisa berisi email atau username).
  - `email` *(opsional jika `login` diisi)*: `required_without:login|string|max:255`.
  - `password`: `required|string`.
  - `device_name` *(opsional)*: `nullable|string|max:100` (default: `"api-token"`).
- **Contoh Request:**
  ```json
  {
    "login": "penjual_rpl",
    "password": "PasswordRahasia123",
    "device_name": "android-client"
  }
  ```
- **Contoh Respons `200 OK`:**
  ```json
  {
    "success": true,
    "message": "Login berhasil.",
    "data": {
      "token": "1|laravel_sanctum_token_example...",
      "token_type": "Bearer",
      "user": {
        "id": 2,
        "name": "Budi Santoso",
        "username": "penjual_rpl",
        "email": "budi@smk.sch.id",
        "role": "penjual",
        "is_active": true,
        "must_change_password": false,
        "seller": {
          "id": 1,
          "nisn": "0012345678",
          "nama_toko": "RPL Creative Store",
          "nomor_whatsapp": "081234567890",
          "lokasi_kelas": "XII RPL 1",
          "alamat": "Jl. Pendidikan No. 1",
          "status_toko": "buka"
        }
      }
    }
  }
  ```

#### `API-002` — `POST /api/register`
- **Auth:** Publik (`throttle:5,1`)
- **Perilaku:** Selalu mengembalikan HTTP `403 Forbidden` karena pendaftaran penjual hanya boleh dilakukan oleh Admin (`ADR-004`).
- **Respons `403 Forbidden`:**
  ```json
  {
    "success": false,
    "message": "Pendaftaran akun penjual hanya dapat dilakukan oleh admin."
  }
  ```

#### `API-003` — `GET /api/me`
- **Auth:** `Bearer Token` (`auth:sanctum`, `active`)
- **Respons `200 OK`:** Mengembalikan objek `user` lengkap (sama seperti field `data.user` pada `API-001`).

#### `API-004` — `POST /api/logout`
- **Auth:** `Bearer Token` (`auth:sanctum`, `active`)
- **Perilaku:** Menghapus `currentAccessToken()` yang sedang digunakan.
- **Respons `200 OK`:** `{"success": true, "message": "Logout berhasil."}`

---

### 4.2 Katalog Publik: Produk, Kategori, Penjual (`API-005` s.d. `API-012`)

#### `API-005` — `GET /api/products` (`Api\ProductController@index`)
- **Query Parameters:**
  - `q` / `search` *(string)*: Pencarian nama, deskripsi, kategori, atau nama toko.
  - `category` *(string/int)*: Slug atau ID kategori.
  - `seller_id` *(int)*: Filter ID penjual.
  - `stok` *(string)*: `'tersedia'`, `'po'`, atau `'habis'`.
  - `min_harga`, `max_harga` *(numeric)*: Rentang harga (otomatis ditukar jika `min_harga > max_harga`).
  - `sort` *(string)*: `'terbaru'` (default), `'termurah'`, `'termahal'`, `'populer'`.
  - `per_page` *(int)*: `1` s.d. `50` (default `12`).
- **Respons `200 OK`:** Mengembalikan `data.items` (array `productData`) dan `data.pagination` (`current_page`, `last_page`, `per_page`, `total`).

#### `API-006` — `GET /api/products/search` (`Api\ProductController@search`)
- Alias fungsional ke `ProductController@index` dengan parameter filter dan format respons yang sama.

#### `API-007` — `GET /api/products/popular` (`Api\ProductController@popular`)
- **Query Parameters:** `limit` (`1` s.d. `24`, default `8`).
- **Perilaku:** Mengambil produk `publicVisible()` diurutkan berdasarkan `views DESC` dan `ulasan_avg_rating DESC`.

#### `API-008` — `GET /api/products/{produk:slug}` (`Api\ProductController@show`)
- **Perilaku:** Memverifikasi produk `publicVisible()`, menambah `views` (`+1`), mencatat `ProdukViewLog` (`type = 'view'`), dan mengembalikan detail produk beserta 5 ulasan terbaru (`includeReviews = true`). Jika tidak aktif, mengembalikan `404`.

#### `API-009` — `GET /api/categories` (`Api\CategoryController@index`)
- **Respons `200 OK`:** Mengembalikan daftar seluruh kategori urut abjad beserta `products_count` (hanya menghitung produk `publicVisible`).

#### `API-010` — `GET /api/categories/{kategori:slug}/products` (`Api\CategoryController@products`)
- **Query Parameters:** `q`/`search`, `stok`, `per_page` (`1..50`, default `12`).
- **Respons `200 OK`:** Mengembalikan objek `category`, `items`, dan `pagination`.

#### `API-011` — `GET /api/sellers` (`Api\SellerController@index`)
- **Query Parameters:** `q`/`search`, `status_toko` (`'buka'`/`'tutup'`), `per_page` (`1..50`, default `12`).
- **Perilaku:** Hanya menampilkan penjual yang akun `user.is_active == true`.

#### `API-012` — `GET /api/sellers/{penjual}` (`Api\SellerController@show`)
- **Perilaku:** Menampilkan detail toko beserta daftar produk `publicVisible` miliknya. Jika akun penjual nonaktif, mengembalikan `404`.

---

### 4.3 Ulasan Produk (`API-013` s.d. `API-015`)
**Kontroler:** `app/Http/Controllers/Api/ReviewController.php`

#### `API-013` — `GET /api/products/{produk:slug}/reviews`
- **Query Parameters:** `per_page` (`1..50`, default `10`).
- **Respons `200 OK`:** Mengembalikan `summary` (`average_rating`, `reviews_count`), `items`, dan `pagination`.

#### `API-014` — `POST /api/products/{produk:slug}/reviews`
- **Auth:** Publik (`throttle:10,1`)
- **Validasi Body JSON:**
  - `guest_username`: `required|string|max:100` (diperiksa oleh `CommentModerationService`).
  - `rating`: `required|integer|min:1|max:5`.
  - `komentar`: `required|string|max:1000` (diperiksa oleh `CommentModerationService`).
- **Respons `201 Created`:** Mengembalikan objek ulasan yang baru dibuat.
- **Respons `422 Unprocessable Entity` (Jika Mengandung Kata Kasar):**
  ```json
  {
    "success": false,
    "message": "Ulasan mengandung kata yang tidak pantas. Silakan gunakan bahasa yang sopan.",
    "errors": {
      "komentar": ["Ulasan mengandung kata yang tidak pantas."]
    }
  }
  ```

#### `API-015` — `DELETE /api/reviews/{ulasan}`
- **Auth:** `Bearer Token` (`auth:sanctum`, `active`) — Hanya diizinkan bagi **Admin** atau **Penjual pemilik produk yang diulas** (`ReviewController.php#L100-L106`).
- **Respons `200 OK`:** `{"success": true, "message": "Ulasan berhasil dihapus."}`

---

### 4.4 Profil Akun & Toko (`API-016` s.d. `API-017`)
**Kontroler:** `app/Http/Controllers/Api/ProfileController.php`

#### `API-016` — `GET /api/profile`
- **Auth:** `Bearer Token` (`auth:sanctum`, `active`)
- **Respons `200 OK`:** Mengembalikan profil pengguna dan objek `seller` (jika berperan sebagai penjual).

#### `API-017` — `PUT /api/profile`
- **Auth:** `Bearer Token` (`auth:sanctum`, `active`)
- **Validasi Body (`multipart/form-data` atau `application/json`):**
  - Umum: `name` (`sometimes|required|string|max:255`), `username` (`sometimes|required|alpha_dash|max:80|unique`), `email` (`sometimes|required|email|max:255|unique`), `current_password` (`required_with:password|current_password`), `password` (`nullable|confirmed|min:8`).
  - Khusus Penjual: `nama_toko` (`sometimes|required|string|max:255`), `deskripsi_toko`, `nomor_whatsapp`, `lokasi_kelas`, `alamat`, `status_toko` (`in:buka,tutup`), `foto_toko` (`image|mimes:jpeg,png,jpg,webp|max:2048`).

---

### 4.5 Manajemen Produk Penjual (`API-018` s.d. `API-021`)
**Kontroler:** `app/Http/Controllers/Api/SellerProductController.php` (Hanya untuk `user.role === 'penjual'`).

#### `API-018` — `GET /api/seller/products`
- **Query Parameters:** `status` (`pending`/`aktif`/`ditolak`/`draft`), `stok` (`tersedia`/`po`/`habis`), `q`/`search`, `per_page` (`1..50`, default `12`).
- **Respons `200 OK`:** Mengembalikan daftar produk milik penjual yang sedang login beserta informasi `pending_action`, `pending_changes`, dan `alasan_ditolak`.

#### `API-019` — `POST /api/seller/products`
- **Validasi (`multipart/form-data` atau JSON):**
  - `nama` (`required|string|max:255`), `kategori_id` (`required|exists:kategori,id`), `deskripsi` (`required|string`), `harga` (`required|numeric|min:0`), `stok` (`required|in:tersedia,po,habis`), `label_promosi` (`nullable|string|max:50`), `foto_utama` (`nullable|image|mimes:jpeg,png,jpg,webp|max:2048`).
- **Respons `201 Created`:** Produk disimpan dengan `status = 'pending'` dan `pending_action = 'create'`.

#### `API-020` — `PUT /api/seller/products/{produk}`
- **Perilaku:**
  - Jika `produk.penjual_id != penjual.id` $\rightarrow$ `403 Forbidden`.
  - Jika `produk.pending_action === 'delete'` $\rightarrow$ `422 Unprocessable Entity` (`"Produk sedang menunggu persetujuan hapus oleh admin."`).
  - Jika `produk.status === 'aktif'` $\rightarrow$ perubahan disimpan ke `pending_changes` dengan `pending_action = 'update'`.
  - Jika produk belum aktif $\rightarrow$ kolom utama diperbarui langsung dan `status = 'pending'`, `pending_action = 'create'`.

#### `API-021` — `DELETE /api/seller/products/{produk}`
- **Perilaku:** Menandai produk dengan `pending_action = 'delete'` dan mengirim notifikasi ke Admin. Jika produk sudah memiliki ajuan moderasi menggantung, mengembalikan `422`.

---

### 4.6 Moderasi Produk Admin (`API-022` s.d. `API-023`)
**Kontroler:** `app/Http/Controllers/Api/AdminModerationController.php` (Hanya untuk `user.role === 'admin'`).

#### `API-022` — `GET /api/admin/moderation/products`
- **Query Parameters:** `action` (`create`/`update`/`delete`), `q`/`search`, `per_page` (`1..50`, default `15`).
- **Respons `200 OK`:** Mengembalikan daftar produk yang berada dalam `scopePendingModeration()`.

#### `API-023` — `PATCH /api/admin/moderation/products/{produk}/status`
- **Validasi Body JSON:**
  - `status`: `required|in:aktif,ditolak`
  - `alasan_ditolak`: `required_if:status,ditolak|nullable|string|max:500`
- **Perilaku:** Mengeksekusi persetujuan atau penolakan ajuan `create`, `update`, atau `delete` persis seperti panel Admin Web (`UC-016`).

---

### 4.7 Notifikasi Pengguna (`API-024` s.d. `API-025`)
**Kontroler:** `app/Http/Controllers/Api/NotificationController.php`

#### `API-024` — `GET /api/notifications`
- **Query Parameters:** `unread_only` (`boolean`), `per_page` (`1..50`, default `15`).
- **Respons `200 OK`:** Mengembalikan `unread_count`, `items`, dan `pagination`.

#### `API-025` — `PATCH /api/notifications/{id}/read`
- **Respons `200 OK`:** Menandai satu notifikasi sebagai telah dibaca (`read_at = now()`). Mengembalikan `404` jika UUID tidak ditemukan pada akun tersebut.

---

### 4.8 Keranjang Belanja — Ditangguhkan HTTP 501 (`API-026`)
**Kontroler:** `app/Http/Controllers/Api/CartController.php` (`#L9-L36`)

Keempat rute berikut terdaftar di `routes/api.php#L71-L75` dan secara sengaja mengembalikan kode status **`501 Not Implemented`**:
- `GET /api/cart` (`api.cart.index`)
- `POST /api/cart` (`api.cart.store`)
- `PATCH /api/cart/{itemId}` (`api.cart.update`)
- `DELETE /api/cart/{itemId}` (`api.cart.destroy`)

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

## 5. Dokumentasi Rute Web Publik Penting (`WEB-PUB-001` s.d. `WEB-PUB-003`)

Selain `/api/*`, tiga rute web publik di `routes/web.php#L15-L24` berperan penting dalam alur bisnis utama:

| ID Rute Web | Method & URI | Nama Rute | Middleware | Perilaku & Respons |
| :--- | :--- | :--- | :--- | :--- |
| **WEB-PUB-001** | `GET /produk/{produk:slug}` | `public.produk.show` | `web`, `throttle:60,1` | Menampilkan halaman detail produk (`200 OK`), menambah `views` (`+1`), dan menyimpan `ProdukViewLog` (`type = 'view'`). Mengembalikan `404` jika produk tidak `publicVisible`. |
| **WEB-PUB-002** | `GET /produk/{produk:slug}/whatsapp` | `public.produk.whatsapp` | `web`, `throttle:30,1` | Mencatat `ProdukViewLog` (`type = 'whatsapp'`) lalu mengembalikan `302 Redirect` ke `https://wa.me/62...` dengan pesan dinamis sesuai `status_toko`. Jika `stok === 'habis'`, mengembalikan `302 Redirect` ke halaman produk dengan flash error. |
| **WEB-PUB-003** | `POST /produk/{produk:slug}/ulasan` | `public.ulasan.store` | `web`, `throttle:3,1` | Memvalidasi CSRF token, input `guest_username`, `rating`, `komentar`, menjalankan `CommentModerationService`, menyimpan ulasan, memicu notifikasi Admin, dan mengembalikan `302 Redirect` kembali ke halaman produk. |

---

## 6. Struktur Objek JSON Standar (Resource Transformers)

Karena proyek menggunakan metode transformer terpusat di kontroler API (bukan kelas `JsonResource` terpisah), berikut struktur pasti dari objek JSON utama:

### 6.1 Objek Produk Publik (`ProductController::productData` — `app/Http/Controllers/Api/ProductController.php#L127-L162`)
```json
{
  "id": 10,
  "nama": "Risoles Mayo Crispy",
  "slug": "risoles-mayo-crispy",
  "deskripsi": "Risoles isi smoked beef, telur, dan keju lumer.",
  "harga": 5000.0,
  "stok": "tersedia",
  "label_promosi": "Best Seller",
  "status": "aktif",
  "foto_url": "http://localhost/storage/produk/risoles.jpg",
  "views": 42,
  "reviews_count": 5,
  "average_rating": 4.8,
  "kategori": {
    "id": 1,
    "nama": "Kuliner",
    "slug": "kuliner"
  },
  "penjual": {
    "id": 3,
    "nama_toko": "Dapur Tata Boga",
    "lokasi_kelas": "XI Kuliner 2",
    "status_toko": "buka",
    "nomor_whatsapp": "081234567890",
    "foto_toko_url": null
  },
  "whatsapp_order_url": "http://localhost/produk/risoles-mayo-crispy/whatsapp",
  "created_at": "2026-10-20T08:00:00+07:00",
  "updated_at": "2026-10-25T10:30:00+07:00"
}
```

---

## 7. Rujukan Kode

- `routes/api.php#L1-L77`
- `routes/web.php#L9-L28`
- `bootstrap/app.php#L18-L45`
- `app/Http/Controllers/Api/ApiController.php#L1-L39`
- `app/Http/Controllers/Api/AuthController.php#L1-L133`
- `app/Http/Controllers/Api/ProductController.php#L1-L164`
- `app/Http/Controllers/Api/CategoryController.php#L1-L82`
- `app/Http/Controllers/Api/SellerController.php#L1-L97`
- `app/Http/Controllers/Api/ReviewController.php#L1-L127`
- `app/Http/Controllers/Api/ProfileController.php#L1-L135`
- `app/Http/Controllers/Api/SellerProductController.php#L1-L246`
- `app/Http/Controllers/Api/AdminModerationController.php#L1-L214`
- `app/Http/Controllers/Api/NotificationController.php#L1-L78`
- `app/Http/Controllers/Api/CartController.php#L1-L37`
