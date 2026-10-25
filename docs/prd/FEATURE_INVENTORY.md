# INVENTARIS FITUR (FEATURE INVENTORY)
# SiswaMart — Platform Promosi & Katalog Produk Sekolah

Dokumen ini memuat inventaris lengkap seluruh fitur aplikasi **SiswaMart** yang diaudit langsung dari repositori kode sumber lokal (`c:\laragon\www\siswamartid`) per **25 Oktober 2026**.

Setiap fitur diklasifikasikan berdasarkan status implementasi yang didukung bukti berkas (*source-code evidence*):
- **Implemented:** Terverifikasi ada dan berfungsi di dalam kode sumber aplikasi.
- **Partially Implemented:** Komponen telah dibuat (misal rute atau model ada), namun alur kerja belum lengkap atau mengembalikan respon belum selesai.
- **Planned or Referenced:** Disebutkan dalam dokumentasi atau komentar kode, tetapi belum diimplementasikan.
- **Missing:** Fitur umum marketplace yang tidak ditemukan bukti implementasinya sama sekali di kode sumber.
- **Needs Verification:** Bergantung pada konfigurasi lingkungan pihak ketiga (misal: kredensial live OAuth/Hosting).

---

## 1. MODUL AUTENTIKASI, AKUN & KEAMANAN SESI

| ID Fitur | Nama Fitur | Deskripsi Implementasi Faktual | Role Pengguna | Status | Bukti Kode Sumber (File, Class, Method, Route) |
|---|---|---|---|---|---|
| **FEAT-AUTH-01** | Login Berbasis Email / Username | Pengguna dapat masuk menggunakan email atau username dengan verifikasi kata sandi ter-hash (Bcrypt). | Admin, Penjual | **Implemented** | `app/Http/Requests/Auth/LoginRequest.php#L45-L66`, `routes/auth.php#L24-L27` |
| **FEAT-AUTH-02** | Pembatasan Percobaan Login (*Rate Limiting*) | Membatasi percobaan login maksimal 5 kali per IP/email sebelum akun dikunci sementara (*Lockout*). | Admin, Penjual | **Implemented** | `app/Http/Requests/Auth/LoginRequest.php#L73-L89` |
| **FEAT-AUTH-03** | Pengecekan Akun Aktif saat Login | Pengguna dengan `is_active = false` ditolak login dan sesi langsung dibatalkan (*force logout*). | Penjual | **Implemented** | `app/Http/Requests/Auth/LoginRequest.php#L56-L63`, `app/Http/Middleware/EnsurePenjual.php#L26-L34` |
| **FEAT-AUTH-04** | Google OAuth Single Sign-On (SSO) | Login instan menggunakan akun Google via Laravel Socialite; membatasi hanya untuk email yang sudah terdaftar sebelumnya di SiswaMart. | Admin, Penjual | **Implemented** | `app/Http/Controllers/Auth/GoogleAuthController.php#L17-L140`, `routes/auth.php#L16-L17` |
| **FEAT-AUTH-05** | Penanganan Exception OAuth Stateless | Fallback otomatis ke mode stateless jika terjadi `InvalidStateException` saat proses redirect Google di browser seluler. | Admin, Penjual | **Implemented** | `app/Http/Controllers/Auth/GoogleAuthController.php#L42-L53` |
| **FEAT-AUTH-06** | Penyesuaian Redirect URI Dinamis | Penyesuaian konfigurasi `services.google.redirect` jika mendeteksi perbedaan host antara lingkungan lokal dan produksi. | Sistem | **Implemented** | `app/Http/Controllers/Auth/GoogleAuthController.php#L146-L164` |
| **FEAT-AUTH-07** | Penutupan Registrasi Mandiri Publik | Rute registrasi publik sengaja dikomentari agar siswa tidak dapat membuat akun penjual secara liar tanpa izin sekolah. | Publik | **Implemented** | `routes/auth.php#L19-L22` |
| **FEAT-AUTH-08** | Lupa & Reset Kata Sandi | Pengiriman tautan reset kata sandi ke email melalui token reset standar Laravel Breeze. | Pengguna | **Implemented** | `app/Http/Controllers/Auth/PasswordResetLinkController.php`, `NewPasswordController.php` |
| **FEAT-AUTH-09** | Pembaruan Kata Sandi Mandiri | Pengguna yang sudah login dapat memperbarui kata sandi akun melalui form profil Breeze. | Admin, Penjual | **Implemented** | `app/Http/Controllers/Auth/PasswordController.php#L15-L26`, `routes/auth.php#L59` |
| **FEAT-AUTH-10** | Penghapusan Akun Mandiri Terproteksi | Pengguna dapat menghapus akunnya, namun sistem menolak jika akun penjual masih memiliki produk di katalog. | Penjual | **Implemented** | `app/Http/Controllers/ProfileController.php#L43-L63` |
| **FEAT-AUTH-11** | Pembatasan Rute Admin (*EnsureAdmin*) | Middleware yang memastikan hanya pengguna dengan `role = 'admin'` yang dapat mengakses rute berawalan `/admin/*`. | Admin | **Implemented** | `app/Http/Middleware/EnsureAdmin.php#L16-L27`, `bootstrap/app.php#L22` |
| **FEAT-AUTH-12** | Pembatasan Rute Penjual (*EnsurePenjual*) | Middleware yang memastikan hanya pengguna dengan `role = 'penjual'` yang dapat mengakses rute `/penjual/*`. | Penjual | **Implemented** | `app/Http/Middleware/EnsurePenjual.php#L16-L37`, `bootstrap/app.php#L23` |

---

## 2. MODUL PROFIL & IDENTITAS PENJUAL

| ID Fitur | Nama Fitur | Deskripsi Implementasi Faktual | Role Pengguna | Status | Bukti Kode Sumber (File, Class, Method, Route) |
|---|---|---|---|---|---|
| **FEAT-SELLER-01** | Entitas Data Profil Penjual | Relasi `hasOne` dari User ke tabel `penjual` yang memuat nama toko, deskripsi, WA, kelas, NISN, alamat, dan foto. | Penjual | **Implemented** | `app/Models/User.php#L68-L71`, `app/Models/Penjual.php#L11-L73` |
| **FEAT-SELLER-02** | Validasi NISN 10 Digit Unik | Validasi Nomor Induk Siswa Nasional wajib berupa angka 10 digit dan tidak boleh duplikat di tabel `penjual`. | Admin | **Implemented** | `app/Http/Controllers/Admin/PenjualController.php#L87`, `migration: 2026_08_21_025055` |
| **FEAT-SELLER-03** | Pencatatan Alamat Domisili Siswa | Kolom `alamat` wajib diisi saat pembuatan akun penjual sebagai data pertanggungjawaban pihak sekolah. | Admin | **Implemented** | `app/Http/Controllers/Admin/PenjualController.php#L89-L107` |
| **FEAT-SELLER-04** | Informasi Lokasi Stan / Ruang Kelas | Atribut `lokasi_kelas` (misal: "Kantin No. 2" atau "Kelas XII RPL 1") untuk memudahkan pembeli mendatangi penjual. | Penjual | **Implemented** | `resources/views/public/produk/show.blade.php#L210-L225`, `app/Models/Penjual.php` |
| **FEAT-SELLER-05** | Pengalih Status Toko Buka / Tutup | Penjual dapat mengubah status toko menjadi `buka` atau `tutup` langsung dari dasbor dengan tombol PATCH. | Penjual | **Implemented** | `app/Http/Controllers/Penjual/DashboardController.php#L52-L61`, `routes/web.php#L67` |
| **FEAT-SELLER-06** | Pelacakan Aktivitas Terakhir Penjual | Kolom `last_login_at`, `last_activity_at`, dan `last_activity_type` terisi otomatis saat penjual login atau mengelola produk. | Penjual | **Implemented** | `app/Http/Controllers/Auth/AuthenticatedSessionController.php#L32-L38`, `Penjual/ProdukController.php#L27-L33` |

---

## 3. MODUL MANAJEMEN PRODUK OLEH PENJUAL

| ID Fitur | Nama Fitur | Deskripsi Implementasi Faktual | Role Pengguna | Status | Bukti Kode Sumber (File, Class, Method, Route) |
|---|---|---|---|---|---|
| **FEAT-PROD-01** | Inventaris Produk Penjual | Tabel daftar produk milik penjual dilengkapi tab hitungan status (*All, Aktif, Pending, Ditolak*), pencarian, dan filter kategori. | Penjual | **Implemented** | `app/Http/Controllers/Penjual/ProdukController.php#L38-L101`, `resources/views/penjual/produk/index.blade.php` |
| **FEAT-PROD-02** | Pengajuan Produk Baru (*Pending Create*) | Penjual mengunggah produk baru; produk tersimpan dengan `status = 'pending'`, `pending_action = 'create'`, menunggu kurasi Admin. | Penjual | **Implemented** | `app/Http/Controllers/Penjual/ProdukController.php#L136-L181` |
| **FEAT-PROD-03** | Pengajuan Revisi Produk (*Pending Update*) | Penjual mengajukan perubahan data produk aktif; perubahan ditampung pada `pending_changes` (JSON) tanpa mengubah data tayang publik. | Penjual | **Implemented** | `app/Http/Controllers/Penjual/ProdukController.php#L200-L250` |
| **FEAT-PROD-04** | Pengajuan Hapus Produk (*Pending Delete*) | Penjual mengajukan penghapusan produk aktif; status ditandai `pending_action = 'delete'` menunggu persetujuan Admin. | Penjual | **Implemented** | `app/Http/Controllers/Penjual/ProdukController.php#L255-L288` |
| **FEAT-PROD-05** | Pilihan Status Ketersediaan Stok | Penjual dapat memilih status ketersediaan produk antara `tersedia` (*Ready*), `po` (*Pre-Order*), atau `habis`. | Penjual | **Implemented** | `resources/views/penjual/produk/create.blade.php#L95-L111`, `app/Models/Produk.php` |
| **FEAT-PROD-06** | Pengunggahan & Pratinjau Foto Produk | Unggah foto produk utama (maksimal 2MB, jpg/png/webp) dengan pratinjau instan di sisi klien sebelum submit. | Penjual | **Implemented** | `resources/views/penjual/produk/create.blade.php#L56-L92` |
| **FEAT-PROD-07** | Pembuatan Slug Produk Otomatis | Menghasilkan slug URL ramah mesin pencari secara otomatis dari nama produk dengan penanganan duplikasi nama bertingkat. | Penjual | **Implemented** | `app/Http/Controllers/Penjual/ProdukController.php#L118-L130` |
| **FEAT-PROD-08** | Label Promosi Kustom | Kolom opsional untuk menambahkan badge promosi khusus pada kartu produk (misal: "Pedas Mampus", "Best Seller"). | Penjual | **Implemented** | `app/Models/Produk.php#L32`, `resources/views/penjual/produk/create.blade.php` |

---

## 4. MODUL KATALOG PUBLIK & PENCARIAN (PENGUNJUNG)

| ID Fitur | Nama Fitur | Deskripsi Implementasi Faktual | Role Pengguna | Status | Bukti Kode Sumber (File, Class, Method, Route) |
|---|---|---|---|---|---|
| **FEAT-PUB-01** | Halaman Beranda (*Home Page*) | Menampilkan hero banner SiswaMart, grid kategori berikon pastel, 8 produk unggulan terbaru, blok panduan cara memesan, dan footer. | Publik | **Implemented** | `app/Http/Controllers/Public/HomeController.php#L14-L24`, `resources/views/public/home/index.blade.php` |
| **FEAT-PUB-02** | Halaman Indeks Kategori | Menampilkan seluruh kategori produk yang tersedia beserta jumlah produk aktif di dalamnya. | Publik | **Implemented** | `app/Http/Controllers/Public/KategoriController.php#L16-L23`, `resources/views/public/kategori/index.blade.php` |
| **FEAT-PUB-03** | Katalog Produk Berfilter Kategori | Menampilkan daftar produk yang disaring khusus berdasarkan kategori terpilih via URL slug `/kategori/{slug}`. | Publik | **Implemented** | `app/Http/Controllers/Public/KategoriController.php#L28-L50`, `resources/views/public/kategori/show.blade.php` |
| **FEAT-PUB-04** | Pencarian Multi-Kolom Produk | Pencarian teks bebas pada kolom `nama`, `deskripsi`, dan relasi `nama_toko` penjual. | Publik | **Implemented** | `app/Http/Controllers/Public/ProdukController.php#L29-L35` |
| **FEAT-PUB-05** | Pengurutan Produk (*Sorting Options*) | Pilihan urutan: Terbaru (*default*), Termurah (`orderBy('harga')`), Termahal (`orderByDesc('harga')`), dan Terpopuler (`orderByDesc('views')`). | Publik | **Implemented** | `app/Http/Controllers/Public/ProdukController.php#L37-L42` |
| **FEAT-PUB-06** | Paginasi Katalog Publik | Menampilkan 12 produk per halaman dengan navigasi halaman bernuansa tombol pill bulat. | Publik | **Implemented** | `app/Http/Controllers/Public/ProdukController.php#L44` |
| **FEAT-PUB-07** | Detail Produk Komprehensif | Menampilkan foto utama / fallback SVG kategori, nama, harga, deskripsi lengkap, kartu profil stan penjual, dan ulasan. | Publik | **Implemented** | `app/Http/Controllers/Public/ProdukController.php#L53-L86`, `resources/views/public/produk/show.blade.php` |
| **FEAT-PUB-08** | Rekomendasi Produk Terkait | Menampilkan maksimal 4 produk lain dari kategori yang sama pada bagian bawah detail produk. | Publik | **Implemented** | `app/Http/Controllers/Public/ProdukController.php#L76-L83` |
| **FEAT-PUB-09** | Penghitung Penayangan (*Views Counter*) | Peningkatan angka penayangan (`views++`) setiap kali halaman detail produk dibuka dan pencatatan riwayat di `produk_views_logs`. | Publik | **Implemented** | `app/Http/Controllers/Public/ProdukController.php#L57-L65` |
| **FEAT-PUB-10** | Halaman Statis Cara Pemesanan | Halaman informasi 4 langkah pemesanan: Pilih Produk, Lihat Ulasan, Hubungi via WA, dan Ambil di Stan/Kelas. | Publik | **Implemented** | `routes/web.php#L18`, `resources/views/public/cara-pemesanan.blade.php` |
| **FEAT-PUB-11** | Halaman Statis Tentang Kami | Halaman penjelasan latar belakang SiswaMart, visi misi kewirausahaan siswa, dan tim pengembang. | Publik | **Implemented** | `routes/web.php#L19`, `resources/views/public/tentang-kami.blade.php` |

---

## 5. MODUL HUBUNGAN WHATSAPP & TRANSAKSI LUAR JARINGAN

| ID Fitur | Nama Fitur | Deskripsi Implementasi Faktual | Role Pengguna | Status | Bukti Kode Sumber (File, Class, Method, Route) |
|---|---|---|---|---|---|
| **FEAT-WA-01** | Tautan Dinamis Click-to-Chat | Rute khusus `/produk/{slug}/whatsapp` yang memvalidasi ketersediaan nomor penjual dan melakukan *redirect away* ke WhatsApp Web / App. | Publik | **Implemented** | `routes/web.php#L16`, `app/Http/Controllers/Public/ProdukController.php#L91-L121` |
| **FEAT-WA-02** | Normalisasi Nomor Telepon Indonesia | Membersihkan karakter non-digit, mengonversi angka awalan `0` menjadi kode negara `62` (misal: `0812` -> `62812`). | Publik | **Implemented** | `app/Http/Controllers/Public/ProdukController.php#L95-L103` |
| **FEAT-WA-03** | Format Pesan Otomatis (*Pre-filled Message*) | Menghasilkan pesan pembuka otomatis berisi nama produk yang dipesan dalam format tebal (*bold* WhatsApp). | Publik | **Implemented** | `app/Http/Controllers/Public/ProdukController.php#L117-L118` |
| **FEAT-WA-04** | Peringatan Otomatis Toko Tutup | Jika status toko penjual sedang `tutup`, pesan WhatsApp otomatis disisipi catatan: *"[Pemberitahuan: Toko sedang tutup, balasan mungkin tertunda]"*. | Publik | **Implemented** | `app/Http/Controllers/Public/ProdukController.php#L113-L115` |
| **FEAT-WA-05** | Pencatatan Metrik Klik WhatsApp | Setiap klik tombol pesan dicatat sebagai konversi potensial pada tabel `produk_views_logs` (`type = 'whatsapp'`). | Publik | **Implemented** | `app/Http/Controllers/Public/ProdukController.php#L105-L111` |

---

## 6. MODUL ULASAN, RATING & MODERASI KOMENTAR OTOMATIS

| ID Fitur | Nama Fitur | Deskripsi Implementasi Faktual | Role Pengguna | Status | Bukti Kode Sumber (File, Class, Method, Route) |
|---|---|---|---|---|---|
| **FEAT-REV-01** | Pengiriman Ulasan Tanpa Akun (*Guest Review*) | Pengunjung dapat mengisi form ulasan produk dengan memasukkan nama samaran (*guest username*), rating bintang (1-5), dan komentar teks. | Publik | **Implemented** | `app/Http/Controllers/Public/UlasanController.php#L18-L64`, `routes/web.php#L22-L24` |
| **FEAT-REV-02** | Pembatasan Pengiriman Ulasan (*Throttling*) | Membatasi satu alamat IP maksimal mengirimkan 3 ulasan per menit (`throttle:3,1`) untuk mencegah spam bot. | Publik | **Implemented** | `routes/web.php#L24` |
| **FEAT-REV-03** | Penyaring Kata Kasar Otomatis (*BadWordFilter*) | Deteksi kata kotor, pelecehan, dan makian dari tabel `bad_words` dengan normalisasi leet-speak (mengubah angka ke huruf serupa). | Publik | **Implemented** | `app/Services/CommentModerationService.php#L40-L100` |
| **FEAT-REV-04** | Penolakan Ulasan Terindikasi Kotor | Jika ulasan mengandung kata pada kamus kata terlarang, sistem menolak menyimpan ke database dan mengembalikan peringatan ramah. | Publik | **Implemented** | `app/Http/Controllers/Public/UlasanController.php#L33-L37` |
| **FEAT-REV-05** | Penghitungan Rata-rata Rating Produk | Accessor Eloquent `average_rating` menghitung rerata bintang ulasan publik yang disetujui secara otomatis. | Publik | **Implemented** | `app/Models/Produk.php#L91-L94` |
| **FEAT-REV-06** | Dasbor Moderasi Komentar oleh Admin | Admin dapat memantau seluruh ulasan, memfilter berdasarkan rating atau kategori, dan menghapus komentar yang tidak pantas. | Admin | **Implemented** | `app/Http/Controllers/Admin/KomentarController.php#L14-L64`, `routes/web.php#L57-L62` |

---

## 7. MODUL MODERASI PRODUK OLEH ADMINISTRATOR

| ID Fitur | Nama Fitur | Deskripsi Implementasi Faktual | Role Pengguna | Status | Bukti Kode Sumber (File, Class, Method, Route) |
|---|---|---|---|---|---|
| **FEAT-MOD-01** | Antrean Moderasi Produk Pending | Halaman kurasi admin menampilkan seluruh produk berstatus `pending` atau memiliki `pending_action` tertunda. | Admin | **Implemented** | `app/Http/Controllers/Admin/ProdukController.php#L21-L81` |
| **FEAT-MOD-02** | Detail Komparasi Revisi Produk | Admin dapat melihat data produk live berdampingan dengan usulan perubahan data di `pending_changes` sebelum memutuskan. | Admin | **Implemented** | `app/Http/Controllers/Admin/ProdukController.php#L86-L95`, `resources/views/admin/produk/show.blade.php` |
| **FEAT-MOD-03** | Persetujuan Produk Baru / Revisi | Admin menyetujui produk; sistem mengubah status menjadi `aktif` atau menerapkan `pending_changes` ke kolom utama. | Admin | **Implemented** | `app/Http/Controllers/Admin/ProdukController.php#L144-L177` |
| **FEAT-MOD-04** | Penolakan Pengajuan Produk | Admin menolak produk baru atau revisi disertai kewajiban mengisi alasan penolakan (`alasan_ditolak`). | Admin | **Implemented** | `app/Http/Controllers/Admin/ProdukController.php#L178-L194` |
| **FEAT-MOD-05** | Persetujuan Permintaan Hapus Produk | Admin menyetujui permintaan hapus dari penjual; produk dihapus permanen dari basis data dan berkas gambar dibersihkan dari disk. | Admin | **Implemented** | `app/Http/Controllers/Admin/ProdukController.php#L111-L142` |
| **FEAT-MOD-06** | Pembersihan Foto Lama Otomatis | Sistem menghapus berkas foto lama dari direktori penyimpanan `storage` saat foto produk baru disetujui admin. | Admin | **Implemented** | `app/Http/Controllers/Admin/ProdukController.php#L168-L170` |

---

## 8. MODUL MANAJEMEN PENJUAL OLEH ADMINISTRATOR

| ID Fitur | Nama Fitur | Deskripsi Implementasi Faktual | Role Pengguna | Status | Bukti Kode Sumber (File, Class, Method, Route) |
|---|---|---|---|---|---|
| **FEAT-ADMIN-S01** | Daftar Penjual Terdaftar | Tabel seluruh penjual sekolah dengan pencarian multi-kolom (Nama, Email, Nama Toko, WhatsApp, Lokasi Kelas, NISN) dan filter status. | Admin | **Implemented** | `app/Http/Controllers/Admin/PenjualController.php#L21-L64` |
| **FEAT-ADMIN-S02** | Pembuatan Akun Penjual Baru | Transaksi DB membuat akun login (`User`) dan profil toko (`Penjual`) lengkap dengan NISN, lokasi, dan nomor WhatsApp. | Admin | **Implemented** | `app/Http/Controllers/Admin/PenjualController.php#L77-L118` |
| **FEAT-ADMIN-S03** | Perubahan Profil Penjual oleh Admin | Admin dapat mengedit data siswa, nama toko, lokasi kelas, mengganti foto profil toko, atau mereset kata sandi penjual. | Admin | **Implemented** | `app/Http/Controllers/Admin/PenjualController.php#L135-L190` |
| **FEAT-ADMIN-S04** | Tinjauan Profil Penjual (*Show Seller*) | Ringkasan detail toko, total produk aktif, total produk pending, produk ditolak, dan akumulasi penayangan (*views*). | Admin | **Implemented** | `app/Http/Controllers/Admin/PenjualController.php#L192-L214` |
| **FEAT-ADMIN-S05** | Pengaktifan / Penonaktifan Penjual | Admin dapat menonaktifkan akun penjual seketika (`is_active = false`), yang otomatis menyembunyikan seluruh produknya dari katalog. | Admin | **Implemented** | `app/Http/Controllers/Admin/PenjualController.php#L220-L231` |
| **FEAT-ADMIN-S06** | Pencegahan Hapus Penjual Berproduk | Sistem mencegah penghapusan akun penjual jika penjual masih memiliki produk tercatat di etalase katalog. | Admin | **Implemented** | `app/Http/Controllers/Admin/PenjualController.php#L239-L246` |

---

## 9. MODUL MANAJEMEN KATEGORI PRODUK

| ID Fitur | Nama Fitur | Deskripsi Implementasi Faktual | Role Pengguna | Status | Bukti Kode Sumber (File, Class, Method, Route) |
|---|---|---|---|---|---|
| **FEAT-CAT-01** | Daftar Kategori & Jumlah Produk | Menampilkan tabel master kategori produk dilengkapi statistik berapa banyak produk yang bernaung di bawah tiap kategori. | Admin | **Implemented** | `app/Http/Controllers/Admin/KategoriController.php#L20-L41` |
| **FEAT-CAT-02** | Tambah Kategori Baru | Admin menambahkan nama kategori unik dan memilih ikon terdaftar dari daftar prasetel (*icon options*). | Admin | **Implemented** | `app/Http/Controllers/Admin/KategoriController.php#L43-L57` |
| **FEAT-CAT-03** | Edit Kategori Produk | Memperbarui nama dan ikon kategori produk. | Admin | **Implemented** | `app/Http/Controllers/Admin/KategoriController.php#L59-L73` |
| **FEAT-CAT-04** | Proteksi Penghapusan Kategori | Sistem memblokir penghapusan kategori jika masih ada produk aktif yang terkait dengan kategori tersebut. | Admin | **Implemented** | `app/Http/Controllers/Admin/KategoriController.php#L75-L84` |

---

## 10. MODUL SISTEM NOTIFIKASI & AKTIVITAS

| ID Fitur | Nama Fitur | Deskripsi Implementasi Faktual | Role Pengguna | Status | Bukti Kode Sumber (File, Class, Method, Route) |
|---|---|---|---|---|---|
| **FEAT-NOTIF-01** | Pembuatan Notifikasi Internal Admin | Notifikasi tersimpan ke tabel `notifications` saat ada ulasan baru masuk, produk baru diunggah, revisi diminta, atau produk dihapus. | Sistem / Admin | **Implemented** | `app/Services/AdminNotificationService.php#L10-L16`, `app/Notifications/AdminActivityNotification.php` |
| **FEAT-NOTIF-02** | Polling Notifikasi AJAX | Endpoint JSON `/admin/notifikasi/poll` untuk memeriksa pembaruan 8 notifikasi terbaru dan jumlah yang belum dibaca secara real-time. | Admin | **Implemented** | `app/Http/Controllers/Admin/NotificationController.php#L12-L25` |
| **FEAT-NOTIF-03** | Tandai Notifikasi Dibaca | Menandai satu notifikasi telah dibaca lalu mengalihkan Admin ke halaman target entitas terkait. | Admin | **Implemented** | `app/Http/Controllers/Admin/NotificationController.php#L27-L35` |
| **FEAT-NOTIF-04** | Tandai Semua Notifikasi Dibaca | Menandai seluruh notifikasi yang belum dibaca milik Admin menjadi terbaca dalam satu klik. | Admin | **Implemented** | `app/Http/Controllers/Admin/NotificationController.php#L37-L42` |

---

## 11. MODUL PELAPORAN & EKSPOR BERKAS PDF

| ID Fitur | Nama Fitur | Deskripsi Implementasi Faktual | Role Pengguna | Status | Bukti Kode Sumber (File, Class, Method, Route) |
|---|---|---|---|---|---|
| **FEAT-REP-01** | Dasbor Laporan Promosi Penjual | Laporan ringkasan performa penjual: perbandingan views minggu ini vs minggu lalu, klik WA, dan kepuasan ulasan pembeli. | Penjual | **Implemented** | `app/Http/Controllers/Penjual/LaporanController.php#L22-L37` |
| **FEAT-REP-02** | Unduh Rekap Penjual Format PDF | Ekspor laporan performa promosi mingguan toko format PDF A4 Portrait menggunakan Barryvdh DomPDF. | Penjual | **Implemented** | `app/Http/Controllers/Penjual/LaporanController.php#L42-L62`, `resources/views/penjual/laporan/pdf.blade.php` |
| **FEAT-REP-03** | Navigasi Periode Mingguan (*Week Offset*) | Penjual dapat meninjau riwayat laporan minggu-minggu sebelumnya menggunakan query parameter `week_offset`. | Penjual | **Implemented** | `app/Http/Controllers/Penjual/LaporanController.php#L29-L32` |
| **FEAT-REP-04** | Dasbor Laporan Agregat Sekolah | Dasbor Admin untuk melihat rekapitulasi performa seluruh penjual atau rekap ulasan dengan pilihan filter periode (*weekly, monthly, yearly*). | Admin | **Implemented** | `app/Http/Controllers/Admin/LaporanController.php#L21-L40` |
| **FEAT-REP-05** | Unduh Rekap Kinerja Penjual PDF | Ekspor rekap performa seluruh penjual sekolah dalam format PDF A4 Landscape. | Admin | **Implemented** | `app/Http/Controllers/Admin/LaporanController.php#L84-L95`, `resources/views/admin/laporan/penjual-pdf.blade.php` |
| **FEAT-REP-06** | Unduh Rekap Kepuasan Ulasan PDF | Ekspor seluruh ulasan publik yang masuk dan distribusi bintang rating dalam format PDF A4 Landscape. | Admin | **Implemented** | `app/Http/Controllers/Admin/LaporanController.php#L62-L82`, `resources/views/admin/laporan/ulasan-pdf.blade.php` |

---

## 12. MODUL REST API & INTEGRASI

| ID Fitur | Nama Fitur | Deskripsi Implementasi Faktual | Role Pengguna | Status | Bukti Kode Sumber (File, Class, Method, Route) |
|---|---|---|---|---|---|
| **FEAT-API-01** | API Autentikasi Pengguna | Endpoint login dan registrasi via API menghasilkan personal access token Sanctum. | API Client | **Implemented** | `routes/api.php#L20-L21`, `app/Http/Controllers/Api/AuthController.php` |
| **FEAT-API-02** | API Daftar Produk Publik | Endpoint JSON untuk membaca katalog produk publik dengan filter dan pengurutan. | API Client | **Implemented** | `routes/api.php#L24-L25`, `app/Http/Controllers/Api/ProductController.php` |
| **FEAT-API-03** | API Pelacakan Klik WhatsApp | Endpoint `POST /api/products/{id}/whatsapp` untuk mencatat metrik konversi via API. | API Client | **Implemented** | `routes/api.php#L26`, `app/Http/Controllers/Api/ProductController.php` |
| **FEAT-API-04** | API Kategori & Penjual | Endpoint JSON untuk mengambil master kategori dan profil publik penjual. | API Client | **Implemented** | `routes/api.php#L30-L36`, `CategoryController.php`, `SellerController.php` |
| **FEAT-API-05** | API Ulasan Produk | Endpoint publik untuk melihat ulasan dan mengirim ulasan baru dengan perlindungan throttle (10 req/menit). | API Client | **Implemented** | `routes/api.php#L39-L43`, `app/Http/Controllers/Api/ReviewController.php` |
| **FEAT-API-06** | API Keranjang Belanja (*Cart*) | Rute keranjang belanja didefinisikan, namun controller sengaja mengembalikan pesan galat bahwa fitur keranjang belum ada di SiswaMart. | Pengguna | **Partially Implemented (Explicit 501)** | `routes/api.php#L63-L67`, `app/Http/Controllers/Api/CartController.php#L9-L14` |

---

## 13. FITUR UMUM E-COMMERCE YANG TIDAK TERSEDIA (OUT-OF-SCOPE / MISSING)

| Fitur | Status dalam SiswaMart | Alasan & Rasionalitas Desain |
|---|---|---|
| **Online Payment Gateway (Transfer / QRIS Otomatis)** | **Missing (Sengaja Ditiadakan)** | SiswaMart didesain untuk komunitas sekolah fisik. Menghindari izin perbankan rumit bagi siswa di bawah umur; pembayaran dilakukan tunai / COD saat jam sekolah. |
| **Shopping Cart & Checkout Web** | **Missing (Sengaja Ditiadakan)** | Menggunakan komunikasi langsung via WhatsApp untuk memastikan ketersediaan jajanan secara instan sebelum siswa membayar. |
| **Jasa Ekspedisi & Ongkos Kirim** | **Missing (Sengaja Ditiadakan)** | Produk diambil sendiri di stan kantin atau ruang kelas siswa yang bersangkutan. |
| **Sistem Chat Internal dalam Aplikasi** | **Missing (Digantikan WhatsApp)** | Mengoptimalkan aplikasi WhatsApp yang sudah umum terpasang di seluruh smartphone siswa dan orang tua. |
| **Pendaftaran Mandiri Penjual via Web** | **Missing (Ditutup Demi Keamanan)** | Mencegah orang luar atau oknum siswa tidak resmi berjualan tanpa persetujuan sekolah. |

---
*Dokumen inventaris ini diverifikasi secara mandiri berdasarkan kode sumber aktual SiswaMart pada 25 Oktober 2026.*
