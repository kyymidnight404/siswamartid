<?php

namespace Tests\Feature;

use App\Models\Kategori;
use App\Models\Penjual;
use App\Models\Produk;
use App\Models\Ulasan;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class AdminSellerReportTest extends TestCase
{
    use DatabaseTransactions;

    private User $admin;
    private User $sellerUser;
    private Penjual $seller;
    private Kategori $kategori1;
    private Kategori $kategori2;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::create([
            'name' => 'Admin Penguji',
            'username' => 'admin_uji_' . uniqid(),
            'email' => 'admin_uji_' . uniqid() . '@smk.sch.id',
            'password' => bcrypt('password123'),
            'role' => 'admin',
            'is_active' => true,
        ]);

        $this->sellerUser = User::create([
            'name' => 'Siti Aisyah',
            'username' => 'siti_boga_' . uniqid(),
            'email' => 'siti_' . uniqid() . '@smk.sch.id',
            'password' => bcrypt('password123'),
            'role' => 'penjual',
            'is_active' => true,
        ]);

        $this->seller = Penjual::create([
            'user_id' => $this->sellerUser->id,
            'nama_toko' => 'Dapur Aisyah Manis',
            'deskripsi_toko' => 'Kue basah dan jajanan pasar',
            'nomor_whatsapp' => '081234567890',
            'lokasi_kelas' => 'XII Tata Boga 1',
            'status_toko' => 'buka',
            'nisn' => '12345' . substr(strval(rand(10000, 99999)), 0, 5),
            'alamat' => 'Jl. Pendidikan Sekolah No. 5',
        ]);

        $this->kategori1 = Kategori::firstOrCreate(
            ['slug' => 'kuliner-test'],
            ['nama' => 'Kuliner Test']
        );

        $this->kategori2 = Kategori::firstOrCreate(
            ['slug' => 'kerajinan-test'],
            ['nama' => 'Kerajinan Test']
        );
    }

    /**
     * Test 1: Halaman Rekap Toko memiliki search bar dan tombol / link Lihat Laporan dengan ID valid.
     */
    public function test_admin_can_see_rekap_toko_with_search_bar_and_report_links(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.laporan.index', ['kategori' => 'penjual']));

        $response->assertOk();
        $response->assertSee('rekapSearchInput', false);
        $response->assertSee('Cari username atau nama toko penjual', false);
        $response->assertSee(route('admin.laporan.penjual.detail', $this->seller->id), false);
        $response->assertSee('Lihat Laporan', false);
    }

    /**
     * Test 2: Pengguna non-admin (guest / penjual) tidak dapat mengakses laporan detail penjual.
     */
    public function test_guest_and_non_admin_cannot_access_seller_report_detail(): void
    {
        // Guest
        $guestResponse = $this->get(route('admin.laporan.penjual.detail', $this->seller->id));
        $guestResponse->assertRedirect(route('login'));

        // Penjual (non-admin)
        $sellerResponse = $this->actingAs($this->sellerUser)->get(route('admin.laporan.penjual.detail', $this->seller->id));
        $sellerResponse->assertForbidden();
    }

    /**
     * Test 3: Admin dapat melihat halaman laporan penjual dengan informasi header & metrik yang akurat.
     */
    public function test_admin_can_view_seller_report_detail_with_correct_metrics(): void
    {
        // Buat 2 produk approved (aktif)
        $p1 = Produk::create([
            'penjual_id' => $this->seller->id,
            'kategori_id' => $this->kategori1->id,
            'nama' => 'Bolu Kukus Pelangi',
            'slug' => 'bolu-kukus-pelangi-' . uniqid(),
            'deskripsi' => 'Bolu kukus lembut',
            'harga' => 15000,
            'stok' => 'tersedia',
            'status' => 'aktif',
            'approved_at' => now()->subDays(2),
        ]);

        $p2 = Produk::create([
            'penjual_id' => $this->seller->id,
            'kategori_id' => $this->kategori1->id,
            'nama' => 'Pastel Ayam Renyah',
            'slug' => 'pastel-ayam-renyah-' . uniqid(),
            'deskripsi' => 'Pastel isi ayam dan sayur',
            'harga' => 5000,
            'stok' => 'tersedia',
            'status' => 'aktif',
            'approved_at' => now()->subDay(),
        ]);

        // Buat ulasan untuk p1: rating 5 dan 4 (avg = 4.5)
        Ulasan::create([
            'produk_id' => $p1->id,
            'guest_username' => 'Pembeli Satu',
            'rating' => 5,
            'komentar' => 'Sangat lezat dan lembut!',
            'status' => 'aktif',
            'moderation_status' => 'approved',
        ]);
        Ulasan::create([
            'produk_id' => $p1->id,
            'guest_username' => 'Pembeli Dua',
            'rating' => 4,
            'komentar' => 'Enak, tapi ukurannya agak kecil.',
            'status' => 'aktif',
            'moderation_status' => 'approved',
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.laporan.penjual.detail', $this->seller->id));

        $response->assertOk();
        $response->assertSee('Laporan Statistik Penjual');
        $response->assertSee($this->seller->nama_toko);
        $response->assertSee('@' . $this->sellerUser->username);
        $response->assertSee('Bolu Kukus Pelangi');
        $response->assertSee('Pastel Ayam Renyah');
        $response->assertSee('Total Produk Approved');
        $response->assertSee('4.5'); // rata-rata rating
        $response->assertSee('Total Ulasan Masuk');
        $response->assertSee('openModalFromButton($el)', false);
        $response->assertSee('data-prod-id="' . $p1->id . '"', false);
        $response->assertSee('data-prod-url="' . route('admin.laporan.produk.rating', $p1->id) . '"', false);
    }

    /**
     * Test 4: Produk yang pending atau ditolak tanpa approval tidak masuk ke laporan riwayat produk approved.
     */
    public function test_only_approved_products_are_listed_in_seller_report_history(): void
    {
        // Produk Approved
        $approvedProduct = Produk::create([
            'penjual_id' => $this->seller->id,
            'kategori_id' => $this->kategori1->id,
            'nama' => 'Kue Lumpur Surga',
            'slug' => 'kue-lumpur-surga-' . uniqid(),
            'deskripsi' => 'Lumpur surga manis legit',
            'harga' => 10000,
            'stok' => 'tersedia',
            'status' => 'aktif',
            'approved_at' => now(),
        ]);

        // Produk Pending yang belum pernah diapprove
        $pendingProduct = Produk::create([
            'penjual_id' => $this->seller->id,
            'kategori_id' => $this->kategori1->id,
            'nama' => 'Kue Lapis Belum Approved',
            'slug' => 'kue-lapis-belum-approved-' . uniqid(),
            'deskripsi' => 'Masih menunggu moderasi',
            'harga' => 8000,
            'stok' => 'tersedia',
            'status' => 'pending',
            'approved_at' => null,
        ]);

        // Produk Ditolak
        $rejectedProduct = Produk::create([
            'penjual_id' => $this->seller->id,
            'kategori_id' => $this->kategori1->id,
            'nama' => 'Kue Gosong Ditolak',
            'slug' => 'kue-gosong-ditolak-' . uniqid(),
            'deskripsi' => 'Ditolak admin karena tidak layak',
            'harga' => 3000,
            'stok' => 'tersedia',
            'status' => 'ditolak',
            'approved_at' => null,
            'alasan_ditolak' => 'Foto produk buram',
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.laporan.penjual.detail', $this->seller->id));

        $response->assertOk();
        $response->assertSee($approvedProduct->nama);
        $response->assertDontSee($pendingProduct->nama);
        $response->assertDontSee($rejectedProduct->nama);
    }

    /**
     * Test 5: Produk tanpa rating menampilkan teks "Belum ada rating", bukan 0 atau 0.0 ambigu.
     */
    public function test_product_without_rating_displays_proper_unrated_label(): void
    {
        $unratedProduct = Produk::create([
            'penjual_id' => $this->seller->id,
            'kategori_id' => $this->kategori1->id,
            'nama' => 'Donat Kentang Salju',
            'slug' => 'donat-kentang-salju-' . uniqid(),
            'deskripsi' => 'Donat empuk tabur gula',
            'harga' => 4000,
            'stok' => 'tersedia',
            'status' => 'aktif',
            'approved_at' => now(),
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.laporan.penjual.detail', $this->seller->id));

        $response->assertOk();
        $response->assertSee($unratedProduct->nama);
        $response->assertSee('Belum ada rating');
    }

    /**
     * Test 6: Filter pencarian nama produk pada laporan penjual berfungsi dengan baik.
     */
    public function test_seller_report_filter_by_product_name(): void
    {
        $p1 = Produk::create([
            'penjual_id' => $this->seller->id,
            'kategori_id' => $this->kategori1->id,
            'nama' => 'Nasi Bakar Tongkol Suwir',
            'slug' => 'nasi-bakar-tongkol-' . uniqid(),
            'deskripsi' => 'Gurih pedas harum',
            'harga' => 12000,
            'stok' => 'tersedia',
            'status' => 'aktif',
            'approved_at' => now(),
        ]);

        $p2 = Produk::create([
            'penjual_id' => $this->seller->id,
            'kategori_id' => $this->kategori1->id,
            'nama' => 'Jus Alpukat Kocok',
            'slug' => 'jus-alpukat-kocok-' . uniqid(),
            'deskripsi' => 'Alpukat segar susu cokelat',
            'harga' => 10000,
            'stok' => 'tersedia',
            'status' => 'aktif',
            'approved_at' => now(),
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.laporan.penjual.detail', [
            'penjual' => $this->seller->id,
            'q' => 'Tongkol',
        ]));

        $response->assertOk();
        $response->assertSee('Nasi Bakar Tongkol Suwir');
        $response->assertDontSee('Jus Alpukat Kocok');
    }

    /**
     * Test 7: Filter berdasarkan kategori produk.
     */
    public function test_seller_report_filter_by_category(): void
    {
        $pKuliner = Produk::create([
            'penjual_id' => $this->seller->id,
            'kategori_id' => $this->kategori1->id,
            'nama' => 'Lemper Bakar Ayam',
            'slug' => 'lemper-bakar-ayam-' . uniqid(),
            'deskripsi' => 'Lemper ketan isi ayam',
            'harga' => 4000,
            'stok' => 'tersedia',
            'status' => 'aktif',
            'approved_at' => now(),
        ]);

        $pKerajinan = Produk::create([
            'penjual_id' => $this->seller->id,
            'kategori_id' => $this->kategori2->id,
            'nama' => 'Gantungan Kunci Rajut',
            'slug' => 'gantungan-kunci-rajut-' . uniqid(),
            'deskripsi' => 'Rajutan benang wol',
            'harga' => 8000,
            'stok' => 'tersedia',
            'status' => 'aktif',
            'approved_at' => now(),
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.laporan.penjual.detail', [
            'penjual' => $this->seller->id,
            'kategori_id' => $this->kategori2->id,
        ]));

        $response->assertOk();
        $response->assertSee('Gantungan Kunci Rajut');
        $response->assertDontSee('Lemper Bakar Ayam');
    }

    /**
     * Test 8: Filter rentang tanggal upload dan tanggal approval.
     */
    public function test_seller_report_filter_by_date_ranges(): void
    {
        $oldProduct = Produk::create([
            'penjual_id' => $this->seller->id,
            'kategori_id' => $this->kategori1->id,
            'nama' => 'Produk Upload Bulan Lalu',
            'slug' => 'produk-upload-bulan-lalu-' . uniqid(),
            'deskripsi' => 'Item lama',
            'harga' => 5000,
            'stok' => 'tersedia',
            'status' => 'aktif',
            'approved_at' => now()->subDays(28),
        ]);
        $oldProduct->created_at = now()->subDays(30);
        $oldProduct->timestamps = false;
        $oldProduct->save();

        $recentProduct = Produk::create([
            'penjual_id' => $this->seller->id,
            'kategori_id' => $this->kategori1->id,
            'nama' => 'Produk Upload Minggu Ini',
            'slug' => 'produk-upload-minggu-ini-' . uniqid(),
            'deskripsi' => 'Item baru',
            'harga' => 5000,
            'stok' => 'tersedia',
            'status' => 'aktif',
            'approved_at' => now()->subDay(),
        ]);
        $recentProduct->created_at = now()->subDays(2);
        $recentProduct->timestamps = false;
        $recentProduct->save();

        // Filter upload minggu ini
        $response = $this->actingAs($this->admin)->get(route('admin.laporan.penjual.detail', [
            'penjual' => $this->seller->id,
            'upload_from' => now()->subDays(7)->toDateString(),
            'upload_to' => now()->toDateString(),
        ]));

        $response->assertOk();
        $response->assertSee('Produk Upload Minggu Ini');
        $response->assertDontSee('Produk Upload Bulan Lalu');

        // Filter approval bulan lalu
        $responseApproval = $this->actingAs($this->admin)->get(route('admin.laporan.penjual.detail', [
            'penjual' => $this->seller->id,
            'approval_from' => now()->subDays(35)->toDateString(),
            'approval_to' => now()->subDays(25)->toDateString(),
        ]));

        $responseApproval->assertOk();
        $responseApproval->assertSee('Produk Upload Bulan Lalu');
        $responseApproval->assertDontSee('Produk Upload Minggu Ini');
    }

    /**
     * Test 9: Approval produk oleh admin mencatat kolom approved_at secara permanen.
     */
    public function test_product_approval_by_admin_sets_approved_at_timestamp(): void
    {
        $pendingProd = Produk::create([
            'penjual_id' => $this->seller->id,
            'kategori_id' => $this->kategori1->id,
            'nama' => 'Kue Sus Fla Vanila',
            'slug' => 'kue-sus-fla-vanila-' . uniqid(),
            'deskripsi' => 'Kue sus isi fla vanila lembut',
            'harga' => 6000,
            'stok' => 'tersedia',
            'status' => 'pending',
            'pending_action' => 'create',
            'approved_at' => null,
        ]);

        $this->assertNull($pendingProd->approved_at);

        // Admin approve via route admin.produk.updateStatus
        $response = $this->actingAs($this->admin)->patch(route('admin.produk.updateStatus', $pendingProd), [
            'decision' => 'approve',
        ]);

        $response->assertRedirect(route('admin.produk.index'));

        $pendingProd->refresh();
        $this->assertEquals('aktif', $pendingProd->status);
        $this->assertNotNull($pendingProd->approved_at);
        $this->assertTrue($pendingProd->approved_at->isToday() || $pendingProd->approved_at->isYesterday());
    }

    /**
     * Test 10: Endpoint API detail rating produk mengembalikan JSON terstruktur lengkap dengan informasi produk, summary, dan daftar rating.
     */
    public function test_admin_can_fetch_product_ratings_endpoint_with_correct_structure(): void
    {
        $prod = Produk::create([
            'penjual_id' => $this->seller->id,
            'kategori_id' => $this->kategori1->id,
            'nama' => 'Pie Susu Bali Krispi',
            'slug' => 'pie-susu-bali-' . uniqid(),
            'deskripsi' => 'Pie susu manis legit',
            'harga' => 7000,
            'stok' => 'tersedia',
            'status' => 'aktif',
            'approved_at' => now(),
        ]);

        Ulasan::create([
            'produk_id' => $prod->id,
            'guest_username' => 'PembeliPie',
            'rating' => 5,
            'komentar' => 'Rasanya sangat autentik!',
            'status' => 'aktif',
            'moderation_status' => 'approved',
        ]);

        $response = $this->actingAs($this->admin)->getJson(route('admin.laporan.produk.rating', $prod->id));

        $response->assertOk();
        $response->assertJsonStructure([
            'success',
            'product' => ['id', 'nama', 'kategori', 'foto_url'],
            'summary' => ['avg_rating', 'total_rating', 'distribusi' => ['1', '2', '3', '4', '5']],
            'ratings' => [
                '*' => ['id', 'guest_username', 'rating', 'komentar', 'has_komentar', 'tanggal', 'tanggal_waktu'],
            ],
            'pagination' => ['current_page', 'last_page', 'per_page', 'total', 'has_more'],
        ]);

        $response->assertJson([
            'success' => true,
            'product' => [
                'id' => $prod->id,
                'nama' => 'Pie Susu Bali Krispi',
                'kategori' => $this->kategori1->nama,
            ],
            'summary' => [
                'avg_rating' => 5.0,
                'total_rating' => 1,
                'distribusi' => [
                    '5' => 1,
                    '4' => 0,
                    '3' => 0,
                    '2' => 0,
                    '1' => 0,
                ],
            ],
        ]);
    }

    /**
     * Test 11: Guest dan user penjual (non-admin) tidak dapat mengakses endpoint API detail rating produk.
     */
    public function test_guest_and_seller_cannot_access_product_rating_endpoint(): void
    {
        $prod = Produk::create([
            'penjual_id' => $this->seller->id,
            'kategori_id' => $this->kategori1->id,
            'nama' => 'Puding Cokelat Silky',
            'slug' => 'puding-cokelat-' . uniqid(),
            'deskripsi' => 'Puding lembut',
            'harga' => 8000,
            'stok' => 'tersedia',
            'status' => 'aktif',
            'approved_at' => now(),
        ]);

        // Guest
        $guestResp = $this->getJson(route('admin.laporan.produk.rating', $prod->id));
        $guestResp->assertUnauthorized();

        // Penjual
        $sellerResp = $this->actingAs($this->sellerUser)->getJson(route('admin.laporan.produk.rating', $prod->id));
        $sellerResp->assertForbidden();
    }

    /**
     * Test 12: Endpoint detail rating produk hanya menampilkan rating produk terpilih dan tidak mencampur rating produk lain milik penjual sama.
     */
    public function test_product_ratings_endpoint_does_not_mix_ratings_from_other_products(): void
    {
        $prodA = Produk::create([
            'penjual_id' => $this->seller->id,
            'kategori_id' => $this->kategori1->id,
            'nama' => 'Produk A Brownies',
            'slug' => 'produk-a-brownies-' . uniqid(),
            'deskripsi' => 'Brownies legit',
            'harga' => 25000,
            'stok' => 'tersedia',
            'status' => 'aktif',
            'approved_at' => now(),
        ]);

        $prodB = Produk::create([
            'penjual_id' => $this->seller->id,
            'kategori_id' => $this->kategori1->id,
            'nama' => 'Produk B Risoles',
            'slug' => 'produk-b-risoles-' . uniqid(),
            'deskripsi' => 'Risoles mayonaise',
            'harga' => 4000,
            'stok' => 'tersedia',
            'status' => 'aktif',
            'approved_at' => now(),
        ]);

        // Ulasan untuk Produk A
        Ulasan::create([
            'produk_id' => $prodA->id,
            'guest_username' => 'PembeliBrownies',
            'rating' => 5,
            'komentar' => 'Brownies terbaik di sekolah!',
            'status' => 'aktif',
            'moderation_status' => 'approved',
        ]);

        // Ulasan untuk Produk B
        Ulasan::create([
            'produk_id' => $prodB->id,
            'guest_username' => 'PembeliRisoles',
            'rating' => 3,
            'komentar' => 'Risoles agak dingin.',
            'status' => 'aktif',
            'moderation_status' => 'approved',
        ]);

        $responseA = $this->actingAs($this->admin)->getJson(route('admin.laporan.produk.rating', $prodA->id));

        $responseA->assertOk();
        $responseA->assertJsonCount(1, 'ratings');
        $responseA->assertJsonPath('ratings.0.guest_username', 'PembeliBrownies');
        $responseA->assertJsonPath('ratings.0.komentar', 'Brownies terbaik di sekolah!');

        // Pastikan tidak ada data ulasan produk B
        $responseA->assertJsonMissing(['guest_username' => 'PembeliRisoles']);
    }

    /**
     * Test 13: Rating tanpa komentar tertulis tetap ditampilkan dengan benar.
     */
    public function test_product_ratings_endpoint_includes_ratings_without_comments(): void
    {
        $prod = Produk::create([
            'penjual_id' => $this->seller->id,
            'kategori_id' => $this->kategori1->id,
            'nama' => 'Keripik Singkong Balado',
            'slug' => 'keripik-singkong-' . uniqid(),
            'deskripsi' => 'Renyah pedas manis',
            'harga' => 6000,
            'stok' => 'tersedia',
            'status' => 'aktif',
            'approved_at' => now(),
        ]);

        Ulasan::create([
            'produk_id' => $prod->id,
            'guest_username' => 'SilentReviewer',
            'rating' => 4,
            'komentar' => null, // Tanpa komentar
            'status' => 'aktif',
            'moderation_status' => 'approved',
        ]);

        $response = $this->actingAs($this->admin)->getJson(route('admin.laporan.produk.rating', $prod->id));

        $response->assertOk();
        $response->assertJsonCount(1, 'ratings');
        $response->assertJsonPath('ratings.0.guest_username', 'SilentReviewer');
        $response->assertJsonPath('ratings.0.rating', 4);
        $response->assertJsonPath('ratings.0.has_komentar', false);
        $response->assertJsonPath('ratings.0.komentar', null);
    }

    /**
     * Test 14: Pagination dan status has_more berjalan jika jumlah rating melebihi per_page.
     */
    public function test_product_ratings_endpoint_supports_pagination(): void
    {
        $prod = Produk::create([
            'penjual_id' => $this->seller->id,
            'kategori_id' => $this->kategori1->id,
            'nama' => 'Es Teh Manis Jumbo',
            'slug' => 'es-teh-jumbo-' . uniqid(),
            'deskripsi' => 'Segar manis',
            'harga' => 3000,
            'stok' => 'tersedia',
            'status' => 'aktif',
            'approved_at' => now(),
        ]);

        // Buat 7 ulasan
        for ($i = 1; $i <= 7; $i++) {
            Ulasan::create([
                'produk_id' => $prod->id,
                'guest_username' => 'Pembeli_' . $i,
                'rating' => ($i % 5) + 1,
                'komentar' => 'Ulasan nomor ' . $i,
                'status' => 'aktif',
                'moderation_status' => 'approved',
            ]);
        }

        // Halaman 1 (per_page = 5)
        $respPage1 = $this->actingAs($this->admin)->getJson(route('admin.laporan.produk.rating', [
            'produk' => $prod->id,
            'per_page' => 5,
            'page' => 1,
        ]));

        $respPage1->assertOk();
        $respPage1->assertJsonCount(5, 'ratings');
        $respPage1->assertJsonPath('pagination.total', 7);
        $respPage1->assertJsonPath('pagination.current_page', 1);
        $respPage1->assertJsonPath('pagination.has_more', true);

        // Halaman 2 (sisa 2 item)
        $respPage2 = $this->actingAs($this->admin)->getJson(route('admin.laporan.produk.rating', [
            'produk' => $prod->id,
            'per_page' => 5,
            'page' => 2,
        ]));

        $respPage2->assertOk();
        $respPage2->assertJsonCount(2, 'ratings');
        $respPage2->assertJsonPath('pagination.current_page', 2);
        $respPage2->assertJsonPath('pagination.has_more', false);
    }

    /**
     * Test 15: Produk yang belum memiliki ulasan mengembalikan data kosong dan view menyertakan teks empty state.
     */
    public function test_unrated_product_returns_empty_summary_and_empty_state_exists(): void
    {
        $unratedProd = Produk::create([
            'penjual_id' => $this->seller->id,
            'kategori_id' => $this->kategori1->id,
            'nama' => 'Kue Cucur Tradisional',
            'slug' => 'kue-cucur-' . uniqid(),
            'deskripsi' => 'Kue cucur gula merah',
            'harga' => 2000,
            'stok' => 'tersedia',
            'status' => 'aktif',
            'approved_at' => now(),
        ]);

        $apiResp = $this->actingAs($this->admin)->getJson(route('admin.laporan.produk.rating', $unratedProd->id));
        $apiResp->assertOk();
        $apiResp->assertJsonPath('summary.total_rating', 0);
        $apiResp->assertJsonPath('summary.avg_rating', null);
        $apiResp->assertJsonCount(0, 'ratings');

        // Periksa tampilan view memiliki teks empty state yang ditentukan
        $pageResp = $this->actingAs($this->admin)->get(route('admin.laporan.penjual.detail', $this->seller->id));
        $pageResp->assertOk();
        $pageResp->assertSee('Belum ada rating untuk produk ini');
    }

    /**
     * Test 16: Admin dapat mengunduh laporan PDF detail statistik penjual dalam format portrait.
     */
    public function test_admin_can_download_seller_detail_pdf_portrait(): void
    {
        Produk::create([
            'penjual_id' => $this->seller->id,
            'kategori_id' => $this->kategori1->id,
            'nama' => 'Produk Uji PDF',
            'slug' => 'produk-uji-pdf-' . uniqid(),
            'deskripsi' => 'Deskripsi produk',
            'harga' => 15000,
            'stok' => 'tersedia',
            'status' => 'aktif',
            'approved_at' => now(),
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.laporan.penjual.detail.pdf', $this->seller->id));

        $response->assertOk();
        $this->assertEquals('application/pdf', $response->headers->get('Content-Type'));
        $this->assertStringContainsString('detail-penjual-', (string) $response->headers->get('Content-Disposition'));
        $this->assertStringContainsString('.pdf', (string) $response->headers->get('Content-Disposition'));
    }

    /**
     * Test 17: Admin dapat mengunduh laporan Excel (XLSX) detail statistik penjual dalam 1 sheet yang sama (tidak dipisah).
     */
    public function test_admin_can_download_seller_detail_sheet(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.laporan.penjual.detail.sheet', $this->seller->id));

        $response->assertOk();
        $this->assertStringContainsString('.xlsx', (string) $response->headers->get('Content-Disposition'));

        // Verifikasi bahwa file excel hanya memiliki 1 sheet (halaman/tab sama, tidak dipisah)
        $file = $response->getFile();
        $rawContent = file_get_contents($file->getPathname());
        $this->assertStringContainsString('name="Rekapitulasi Produk"', $rawContent);
        $this->assertStringContainsString('xl/worksheets/sheet1.xml', $rawContent);
        $this->assertStringNotContainsString('xl/worksheets/sheet2.xml', $rawContent);

        // Konten sheet memuat Bagian I dan Bagian II dalam 1 halaman
        $this->assertStringContainsString('I. INDIKATOR REKAPITULASI AUDIT (LIFETIME)', $rawContent);
        $this->assertStringContainsString('II. RINCIAN KATALOG PRODUK &amp; RATING (SELURUH DATA)', $rawContent);
    }

    /**
     * Test 18: Pengguna non-admin tidak dapat mengunduh laporan PDF atau Excel detail penjual.
     */
    public function test_guest_and_non_admin_cannot_download_seller_detail_pdf_or_sheet(): void
    {
        // Guest
        $guestPdf = $this->get(route('admin.laporan.penjual.detail.pdf', $this->seller->id));
        $guestPdf->assertRedirect(route('login'));

        $guestSheet = $this->get(route('admin.laporan.penjual.detail.sheet', $this->seller->id));
        $guestSheet->assertRedirect(route('login'));

        // Penjual
        $sellerPdf = $this->actingAs($this->sellerUser)->get(route('admin.laporan.penjual.detail.pdf', $this->seller->id));
        $sellerPdf->assertForbidden();

        $sellerSheet = $this->actingAs($this->sellerUser)->get(route('admin.laporan.penjual.detail.sheet', $this->seller->id));
        $sellerSheet->assertForbidden();
    }

    /**
     * Test 19: View PDF detail penjual memiliki orientasi portrait, indikator lifetime, dan kolom total ulasan.
     */
    public function test_seller_detail_pdf_view_renders_portrait_layout_and_sections(): void
    {
        $prod = Produk::create([
            'penjual_id' => $this->seller->id,
            'kategori_id' => $this->kategori1->id,
            'nama' => 'Kue Lumpur Surga',
            'slug' => 'kue-lumpur-' . uniqid(),
            'deskripsi' => 'Kue manis lembut',
            'harga' => 5000,
            'stok' => 'tersedia',
            'status' => 'aktif',
            'approved_at' => now(),
        ]);

        Ulasan::create([
            'produk_id' => $prod->id,
            'guest_username' => 'Pembeli Review',
            'rating' => 5,
            'komentar' => 'Sangat enak!',
            'status' => 'approved',
            'is_visible' => true,
        ]);

        $rendered = view('admin.laporan.penjual-detail-pdf', [
            'penjual' => $this->seller,
            'totalUploaded' => 1,
            'totalApproved' => 1,
            'totalPending' => 0,
            'totalRejected' => 0,
            'approvedPercentage' => 100,
            'totalUlasan' => 1,
            'avgRating' => 5.0,
            'products' => collect([$prod]),
            'subSectionTitle' => '(SELURUH DATA)',
            'rekapDate' => 'Jumat, 6 November 2026',
            'exportedAt' => now('Asia/Jakarta'),
        ])->render();

        $this->assertStringContainsString('size: a4 portrait', $rendered);
        $this->assertStringContainsString('LAPORAN REKAPITULASI PRODUK — ' . strtoupper($this->seller->nama_toko), $rendered);
        $this->assertStringContainsString('I. INDIKATOR REKAPITULASI AUDIT (LIFETIME)', $rendered);
        $this->assertStringContainsString('II. RINCIAN KATALOG PRODUK &amp; RATING (SELURUH DATA)', $rendered);
        $this->assertStringContainsString('TOTAL ULASAN', $rendered);
        $this->assertStringContainsString('Kue Lumpur Surga', $rendered);
    }
}
