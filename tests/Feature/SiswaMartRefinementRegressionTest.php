<?php

namespace Tests\Feature;

use App\Models\Kategori;
use App\Models\Penjual;
use App\Models\Produk;
use App\Models\Ulasan;
use App\Models\User;
use App\Services\CommentModerationService;
use App\Services\WhatsappUrlService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class SiswaMartRefinementRegressionTest extends TestCase
{
    use DatabaseTransactions;

    private User $sellerUser;

    private Penjual $seller;

    private Kategori $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->sellerUser = User::create([
            'name' => 'Penjual Refinement '.uniqid(),
            'email' => 'refinement_'.uniqid().'@example.com',
            'password' => bcrypt('password'),
            'role' => 'penjual',
            'is_active' => true,
        ]);

        $this->seller = Penjual::create([
            'user_id' => $this->sellerUser->id,
            'nama_toko' => 'Warung Siswa Hebat '.uniqid(),
            'deskripsi_toko' => 'Kantin karya siswa',
            'nomor_whatsapp' => '081234567890',
            'lokasi_kelas' => 'XII RPL 2',
            'status_toko' => 'buka',
        ]);

        $this->category = Kategori::create([
            'nama' => 'Kategori Refine '.uniqid(),
            'slug' => 'kategori-refine-'.uniqid(),
            'icon' => 'images/category/snack.png',
        ]);
    }

    /**
     * 1. WhatsApp Template untuk Pre-Order
     */
    public function test_whatsapp_template_for_preorder_product(): void
    {
        $product = Produk::create([
            'penjual_id' => $this->seller->id,
            'kategori_id' => $this->category->id,
            'nama' => 'Kue Brownies PO Spesial',
            'slug' => 'kue-brownies-po-'.uniqid(),
            'deskripsi' => 'Brownies lumer pre-order',
            'harga' => 15000,
            'stok' => 'po',
            'status' => 'aktif',
        ]);

        $waService = app(WhatsappUrlService::class);
        $result = $waService->generateForProduct($product, false);

        $this->assertTrue($result['success']);
        $this->assertStringContainsString('Pre-Order', urldecode($result['whatsapp_url']));
        $this->assertStringContainsString('perkiraan jadwal', urldecode($result['whatsapp_url']));
    }

    /**
     * 2. Produk Stok Habis menampilkan CTA non-aktif di detail page dan template WhatsApp menanyakan restock
     */
    public function test_out_of_stock_product_disables_order_cta(): void
    {
        $product = Produk::create([
            'penjual_id' => $this->seller->id,
            'kategori_id' => $this->category->id,
            'nama' => 'Keripik Tempe Habis',
            'slug' => 'keripik-tempe-habis-'.uniqid(),
            'deskripsi' => 'Keripik tempe gurih',
            'harga' => 8000,
            'stok' => 'habis',
            'status' => 'aktif',
        ]);

        $response = $this->get(route('public.produk.show', $product->slug));
        $response->assertStatus(200);
        $response->assertSee('Stok Saat Ini Habis');
        $response->assertSee('cursor-not-allowed');

        $waService = app(WhatsappUrlService::class);
        $result = $waService->generateForProduct($product, false);
        $this->assertTrue($result['success']);
        $this->assertStringContainsString('tersedia kembali', urldecode($result['whatsapp_url']));
    }

    /**
     * 3. Konsistensi status toko tutup: menambahkan pemberitahuan di WhatsApp dan badge di UI
     */
    public function test_closed_store_adds_notice_prefix(): void
    {
        $this->seller->update(['status_toko' => 'tutup']);

        $product = Produk::create([
            'penjual_id' => $this->seller->id,
            'kategori_id' => $this->category->id,
            'nama' => 'Roti Bakar Tutup',
            'slug' => 'roti-bakar-tutup-'.uniqid(),
            'deskripsi' => 'Roti bakar enak',
            'harga' => 10000,
            'stok' => 'tersedia',
            'status' => 'aktif',
        ]);

        $waService = app(WhatsappUrlService::class);
        $result = $waService->generateForProduct($product, false);
        $this->assertTrue($result['success']);
        $this->assertStringContainsString('Pemberitahuan: Toko sedang tutup', urldecode($result['whatsapp_url']));

        $response = $this->get(route('public.produk.show', $product->slug));
        $response->assertStatus(200);
        $response->assertSee('Toko Tutup');
    }

    /**
     * 4. WhatsApp Tracking via JSON/AJAX mengembalikan JSON 200 tanpa CORS error
     */
    public function test_whatsapp_tracking_returns_json_on_ajax(): void
    {
        $product = Produk::create([
            'penjual_id' => $this->seller->id,
            'kategori_id' => $this->category->id,
            'nama' => 'Cimol Bojot',
            'slug' => 'cimol-bojot-'.uniqid(),
            'deskripsi' => 'Cimol pedas',
            'harga' => 5000,
            'stok' => 'tersedia',
            'status' => 'aktif',
        ]);

        $response = $this->getJson(route('public.produk.whatsapp', $product->slug));
        $response->assertStatus(200);
        $response->assertJsonStructure(['success', 'whatsapp_url']);
        $this->assertTrue($response->json('success'));
        $this->assertStringContainsString('wa.me', $response->json('whatsapp_url'));
    }

    /**
     * 5. Session-based view deduplication mencegah views meningkat berkali-kali pada refresh
     */
    public function test_product_view_is_deduplicated_in_session(): void
    {
        $product = Produk::create([
            'penjual_id' => $this->seller->id,
            'kategori_id' => $this->category->id,
            'nama' => 'Jus Mangga Manis',
            'slug' => 'jus-mangga-manis-'.uniqid(),
            'deskripsi' => 'Jus buah segar',
            'harga' => 7000,
            'stok' => 'tersedia',
            'status' => 'aktif',
            'views' => 10,
        ]);

        // Kunjungan pertama
        $this->get(route('public.produk.show', $product->slug));
        $this->assertEquals(11, $product->fresh()->views);

        // Kunjungan kedua dalam sesi yang sama
        $this->get(route('public.produk.show', $product->slug));
        $this->assertEquals(11, $product->fresh()->views);
    }

    /**
     * 6. Sitemap.xml dinamis berfungsi, mereturn XML, dan tidak menyertakan halaman privat
     */
    public function test_dynamic_sitemap_returns_valid_xml_excluding_private_routes(): void
    {
        $product = Produk::create([
            'penjual_id' => $this->seller->id,
            'kategori_id' => $this->category->id,
            'nama' => 'Produk Sitemap Publik',
            'slug' => 'produk-sitemap-publik-'.uniqid(),
            'deskripsi' => 'Produk aktif publik',
            'harga' => 6000,
            'stok' => 'tersedia',
            'status' => 'aktif',
        ]);

        $inactiveProduct = Produk::create([
            'penjual_id' => $this->seller->id,
            'kategori_id' => $this->category->id,
            'nama' => 'Produk Sitemap Nonaktif',
            'slug' => 'produk-sitemap-nonaktif-'.uniqid(),
            'deskripsi' => 'Produk nonaktif tidak boleh masuk sitemap',
            'harga' => 6000,
            'stok' => 'tersedia',
            'status' => 'pending',
        ]);

        $response = $this->get(route('public.sitemap'));
        $response->assertStatus(200);
        $this->assertStringContainsString('xml', $response->headers->get('Content-Type'));
        $response->assertSee(route('public.produk.show', $product->slug));
        $response->assertDontSee(route('public.produk.show', $inactiveProduct->slug));
        $response->assertDontSee('/admin');
        $response->assertDontSee('/penjual');
    }

    /**
     * 7. Canonical URL, Open Graph, dan JSON-LD Product & BreadcrumbList di detail produk
     */
    public function test_seo_meta_and_json_ld_schema(): void
    {
        $product = Produk::create([
            'penjual_id' => $this->seller->id,
            'kategori_id' => $this->category->id,
            'nama' => 'Batagor Kuah Spesial',
            'slug' => 'batagor-kuah-'.uniqid(),
            'deskripsi' => 'Batagor kuah hangat gurih',
            'harga' => 12000,
            'stok' => 'tersedia',
            'status' => 'aktif',
        ]);

        $response = $this->get(route('public.produk.show', $product->slug));
        $response->assertStatus(200);

        // Canonical & Open Graph
        $response->assertSee('<link rel="canonical" href="'.route('public.produk.show', $product->slug).'">', false);
        $response->assertSee('<meta property="og:type" content="product">', false);
        $response->assertSee('<meta property="og:title" content="'.e($product->nama).' — SiswaMart">', false);

        // JSON-LD structured data
        $response->assertSee('"@type": "Product"', false);
        $response->assertSee('"@type": "BreadcrumbList"', false);
        $response->assertSee('"price": "12000"', false);
        $response->assertSee('https://schema.org/InStock', false);

        // Tanpa ulasan, tidak boleh ada aggregateRating palsu
        $response->assertDontSee('aggregateRating');

        // Tambahkan ulasan aktif
        Ulasan::create([
            'produk_id' => $product->id,
            'guest_username' => 'Pengulas Jujur',
            'rating' => 5,
            'komentar' => 'Rasanya enak sekali dan porsinya pas!',
            'status_moderasi' => 'disetujui',
        ]);

        $reviewedResponse = $this->get(route('public.produk.show', $product->slug));
        $reviewedResponse->assertSee('aggregateRating');
        $reviewedResponse->assertSee('"ratingValue": "5.0"', false);
        $reviewedResponse->assertSee('"reviewCount": "1"', false);
    }

    /**
     * 8. Security Headers pada HTTP Response
     */
    public function test_security_headers_are_present(): void
    {
        $response = $this->get(route('public.home'));
        $response->assertStatus(200);
        $response->assertHeader('X-Frame-Options', 'SAMEORIGIN');
        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
    }

    /**
     * 9. Sanitasi LIKE wildcard dan pencarian toko penjual di katalog
     */
    public function test_catalog_search_seller_store_name_and_wildcard_safety(): void
    {
        $uniqueShopName = 'Toko Khusus '.uniqid();
        $this->seller->update(['nama_toko' => $uniqueShopName]);

        $product = Produk::create([
            'penjual_id' => $this->seller->id,
            'kategori_id' => $this->category->id,
            'nama' => 'Makaroni Pedas Asin',
            'slug' => 'makaroni-pedas-'.uniqid(),
            'deskripsi' => 'Makaroni goreng renyah',
            'harga' => 5000,
            'stok' => 'tersedia',
            'status' => 'aktif',
        ]);

        // Cari berdasarkan nama toko
        $response = $this->get(route('public.kategori.index', ['q' => $uniqueShopName]));
        $response->assertStatus(200);
        $response->assertSee('Makaroni Pedas Asin');

        // Cari dengan karakter wildcard % dan _ tanpa error
        $wildcardResponse = $this->get(route('public.kategori.index', ['q' => '%_!']));
        $wildcardResponse->assertStatus(200);
    }

    /**
     * 10. Akses show produk penjual (/penjual/produk/{produk}) mengalihkan ke edit bagi pemilik dan 403 bagi non-pemilik
     */
    public function test_seller_product_show_route_redirects_to_edit_for_owner_and_forbids_non_owner(): void
    {
        $product = Produk::create([
            'penjual_id' => $this->seller->id,
            'kategori_id' => $this->category->id,
            'nama' => 'Produk Tes Show Penjual',
            'slug' => 'produk-tes-show-'.uniqid(),
            'deskripsi' => 'Deskripsi produk tes',
            'harga' => 10000,
            'stok' => 'tersedia',
            'status' => 'aktif',
        ]);

        // Seller pemilik mengakses produknya -> dialihkan ke form edit tanpa 500 BadMethodCallException
        $response = $this->actingAs($this->sellerUser)->get(route('penjual.produk.show', $product));
        $response->assertRedirect(route('penjual.produk.edit', $product));

        // Seller lain mengakses produk yang bukan miliknya -> 403 Forbidden
        $otherSellerUser = User::create([
            'name' => 'Penjual Lain '.uniqid(),
            'email' => 'other_seller_'.uniqid().'@example.com',
            'password' => bcrypt('password'),
            'role' => 'penjual',
            'is_active' => true,
        ]);
        Penjual::create([
            'user_id' => $otherSellerUser->id,
            'nama_toko' => 'Toko Lain '.uniqid(),
            'nomor_whatsapp' => '089876543210',
            'lokasi_kelas' => 'XII RPL 3',
        ]);

        $forbiddenResponse = $this->actingAs($otherSellerUser)->get(route('penjual.produk.show', $product));
        $forbiddenResponse->assertForbidden();
    }

    /**
     * 11. API Produk Kategori (/api/categories/{category}/products) memuat rating & review count tanpa N+1
     */
    public function test_api_category_products_returns_rating_and_review_count(): void
    {
        $product = Produk::create([
            'penjual_id' => $this->seller->id,
            'kategori_id' => $this->category->id,
            'nama' => 'Produk Kategori API Tes',
            'slug' => 'produk-kategori-api-'.uniqid(),
            'deskripsi' => 'Deskripsi produk kategori API',
            'harga' => 12000,
            'stok' => 'tersedia',
            'status' => 'aktif',
        ]);

        Ulasan::create([
            'produk_id' => $product->id,
            'guest_username' => 'Pembeli Satu',
            'rating' => 5,
            'komentar' => 'Sangat lezat!',
            'original_komentar' => 'Sangat lezat!',
            'status' => 'approved',
            'moderation_status' => 'approved',
        ]);

        $response = $this->getJson('/api/categories/'.$this->category->slug.'/products');
        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.data.0.rating', 5)
            ->assertJsonPath('data.data.0.review_count', 1);
    }

    /**
     * 12. API Seller Dashboard memuat performa produk beserta rating & ulasan
     */
    public function test_api_seller_dashboard_returns_products_with_ratings(): void
    {
        $product = Produk::create([
            'penjual_id' => $this->seller->id,
            'kategori_id' => $this->category->id,
            'nama' => 'Produk Dashboard API Tes',
            'slug' => 'produk-dash-api-'.uniqid(),
            'deskripsi' => 'Deskripsi',
            'harga' => 15000,
            'stok' => 'tersedia',
            'status' => 'aktif',
        ]);

        Ulasan::create([
            'produk_id' => $product->id,
            'guest_username' => 'Pembeli Review',
            'rating' => 4,
            'komentar' => 'Enak dan mantap',
            'original_komentar' => 'Enak dan mantap',
            'status' => 'approved',
            'moderation_status' => 'approved',
        ]);

        $token = $this->sellerUser->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/seller/dashboard');

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.stats.active_products', 1)
            ->assertJsonPath('data.products.0.rating', 4)
            ->assertJsonPath('data.products.0.review_count', 1);
    }

    /**
     * 13. Moderasi ulasan berjalan normal dengan cache database tanpa string truncation
     */
    public function test_review_moderation_works_with_database_cache_store(): void
    {
        config(['cache.default' => 'database']);
        app(CommentModerationService::class)->clearCache();

        $product = Produk::create([
            'penjual_id' => $this->seller->id,
            'kategori_id' => $this->category->id,
            'nama' => 'Produk Moderasi Cache DB Tes',
            'slug' => 'produk-mod-cache-'.uniqid(),
            'deskripsi' => 'Deskripsi',
            'harga' => 10000,
            'stok' => 'tersedia',
            'status' => 'aktif',
        ]);

        $response = $this->post(route('public.ulasan.store', $product->slug), [
            'guest_username' => 'Pembeli Bersih',
            'rating' => 5,
            'komentar' => 'Produk ini sangat enak, higienis, dan pengiriman tepat waktu!',
        ]);

        $response->assertRedirect(route('public.produk.show', $product->slug).'#ulasan');
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('ulasan', [
            'produk_id' => $product->id,
            'guest_username' => 'Pembeli Bersih',
            'rating' => 5,
            'moderation_status' => 'approved',
        ]);
    }
}
