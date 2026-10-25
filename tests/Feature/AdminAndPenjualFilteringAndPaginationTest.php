<?php

namespace Tests\Feature;

use App\Models\Kategori;
use App\Models\Penjual;
use App\Models\Produk;
use App\Models\Ulasan;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class AdminAndPenjualFilteringAndPaginationTest extends TestCase
{
    use DatabaseTransactions;

    private User $admin;
    private User $penjualUser;
    private Penjual $penjual;
    private Kategori $kategori1;
    private Kategori $kategori2;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::create([
            'name' => 'Admin Super',
            'email' => 'adminsuper_' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'is_active' => true,
        ]);

        $this->penjualUser = User::create([
            'name' => 'Siswa Penjual A',
            'email' => 'penjuala_' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'role' => 'penjual',
            'is_active' => true,
        ]);

        $this->penjual = Penjual::create([
            'user_id' => $this->penjualUser->id,
            'nama_toko' => 'Toko Siswa Ceria',
            'deskripsi_toko' => 'Toko snack nikmat',
            'nomor_whatsapp' => '081234567891',
            'lokasi_kelas' => 'XII RPL 3',
            'status_toko' => 'buka',
        ]);

        $this->kategori1 = Kategori::firstOrCreate(
            ['nama' => 'Snack Kering'],
            ['slug' => 'snack-kering', 'icon' => 'images/category/snack.png']
        );

        $this->kategori2 = Kategori::firstOrCreate(
            ['nama' => 'Minuman Segar'],
            ['slug' => 'minuman-segar', 'icon' => 'images/category/minuman.png']
        );
    }

    public function test_admin_penjual_search_status_filter_and_pagination(): void
    {
        // 1. Check index contains search input, status filter, and pagination
        $response = $this->actingAs($this->admin)->get(route('admin.penjual.index'));
        $response->assertStatus(200);
        $response->assertSee('name="search"', false);
        $response->assertSee('name="status"', false);
        $response->assertSee('Toko Siswa Ceria');
        $response->assertSee('Baris:');
        $response->assertSee('Halaman:');

        // 2. Search specific seller
        $searchResponse = $this->actingAs($this->admin)->get(route('admin.penjual.index', ['search' => 'Siswa Ceria']));
        $searchResponse->assertStatus(200);
        $searchResponse->assertSee('Toko Siswa Ceria');

        // 3. Search non-matching seller
        $notFoundResponse = $this->actingAs($this->admin)->get(route('admin.penjual.index', ['search' => 'TokoTidakAda999']));
        $notFoundResponse->assertStatus(200);
        $notFoundResponse->assertDontSee('Toko Siswa Ceria');

        // 4. Filter status
        $statusResponse = $this->actingAs($this->admin)->get(route('admin.penjual.index', ['status' => 'buka']));
        $statusResponse->assertStatus(200);
        $statusResponse->assertSee('Toko Siswa Ceria');

        // 5. Per page 'all'
        $allResponse = $this->actingAs($this->admin)->get(route('admin.penjual.index', ['per_page' => 'all']));
        $allResponse->assertStatus(200);
        $allResponse->assertSee('Toko Siswa Ceria');
    }

    public function test_admin_produk_search_category_filter_and_pagination(): void
    {
        $produk1 = Produk::create([
            'penjual_id' => $this->penjual->id,
            'kategori_id' => $this->kategori1->id,
            'nama' => 'Keripik Pisang Keju',
            'slug' => 'keripik-pisang-keju-' . uniqid(),
            'deskripsi' => 'Keripik pisang gurih dengan taburan keju',
            'harga' => 10000,
            'stok' => 'tersedia',
            'status' => 'aktif',
        ]);

        $produk2 = Produk::create([
            'penjual_id' => $this->penjual->id,
            'kategori_id' => $this->kategori2->id,
            'nama' => 'Es Teh Manis Jumbo',
            'slug' => 'es-teh-manis-jumbo-' . uniqid(),
            'deskripsi' => 'Minuman teh segar manis',
            'harga' => 5000,
            'stok' => 'tersedia',
            'status' => 'pending',
        ]);

        // 1. Check index controls
        $response = $this->actingAs($this->admin)->get(route('admin.produk.index'));
        $response->assertStatus(200);
        $response->assertSee('name="search"', false);
        $response->assertSee('name="kategori_id"', false);
        $response->assertSee('Baris:');
        $response->assertSee('Halaman:');

        // 2. Search product name
        $searchResponse = $this->actingAs($this->admin)->get(route('admin.produk.index', ['search' => 'Keripik Pisang']));
        $searchResponse->assertStatus(200);
        $searchResponse->assertSee('Keripik Pisang Keju');
        $searchResponse->assertDontSee('Es Teh Manis Jumbo');

        // 3. Filter by category
        $catResponse = $this->actingAs($this->admin)->get(route('admin.produk.index', ['kategori_id' => $this->kategori2->id]));
        $catResponse->assertStatus(200);
        $catResponse->assertSee('Es Teh Manis Jumbo');
        $catResponse->assertDontSee('Keripik Pisang Keju');

        // 4. Filter by status 'pending'
        $statusResponse = $this->actingAs($this->admin)->get(route('admin.produk.index', ['status' => 'pending']));
        $statusResponse->assertStatus(200);
        $statusResponse->assertSee('Es Teh Manis Jumbo');
        $statusResponse->assertDontSee('Keripik Pisang Keju');
    }

    public function test_admin_kategori_search_and_pagination(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.categories.index'));
        $response->assertStatus(200);
        $response->assertSee('name="search"', false);
        $response->assertSee('placeholder="Cari kategori..."', false);
        $response->assertDontSee('>Slug<', false);
        $response->assertSee('Baris:');
        $response->assertSee('Halaman:');
        $response->assertSee('Snack Kering');

        $searchResponse = $this->actingAs($this->admin)->get(route('admin.categories.index', ['search' => 'Minuman Segar']));
        $searchResponse->assertStatus(200);
        $searchResponse->assertSee('Minuman Segar');
        $searchResponse->assertDontSee('Snack Kering');

        // Test store without slug in request
        $storeResponse = $this->actingAs($this->admin)->post(route('admin.categories.store'), [
            'nama' => 'Kategori Baru Tanpa Slug',
            'icon' => 'images/category/snack.png',
        ]);
        $storeResponse->assertRedirect(route('admin.categories.index'));
        $this->assertDatabaseHas('kategori', [
            'nama' => 'Kategori Baru Tanpa Slug',
            'slug' => 'kategori-baru-tanpa-slug',
        ]);

        $createdCategory = Kategori::where('nama', 'Kategori Baru Tanpa Slug')->first();

        // Test update without slug in request
        $updateResponse = $this->actingAs($this->admin)->put(route('admin.categories.update', $createdCategory), [
            'nama' => 'Kategori Baru Diedit',
            'icon' => 'images/category/dessert.png',
        ]);
        $updateResponse->assertRedirect(route('admin.categories.index'));
        $this->assertDatabaseHas('kategori', [
            'id' => $createdCategory->id,
            'nama' => 'Kategori Baru Diedit',
            'slug' => 'kategori-baru-diedit',
            'icon' => 'images/category/dessert.png',
        ]);

        $createdCategory->delete();
    }

    public function test_admin_komentar_search_category_and_rating_filter(): void
    {
        $produk = Produk::create([
            'penjual_id' => $this->penjual->id,
            'kategori_id' => $this->kategori1->id,
            'nama' => 'Kue Cubit Coklat',
            'slug' => 'kue-cubit-coklat-' . uniqid(),
            'deskripsi' => 'Kue cubit lezat lumer',
            'harga' => 8000,
            'stok' => 'tersedia',
            'status' => 'aktif',
        ]);

        $ulasan = Ulasan::create([
            'produk_id' => $produk->id,
            'guest_username' => 'PembeliKenyang',
            'rating' => 5,
            'komentar' => 'Rasanya sangat memuaskan dan manis sekali!',
            'status' => 'aktif',
            'moderation_status' => 'approved',
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.komentar.index'));
        $response->assertStatus(200);
        $response->assertSee('name="search"', false);
        $response->assertSee('name="kategori_id"', false);
        $response->assertSee('name="rating"', false);
        $response->assertSee('Baris:');
        $response->assertSee('Halaman:');
        $response->assertSee('PembeliKenyang');
        $response->assertSee('Kue Cubit Coklat');

        // Filter by rating 5
        $ratingResponse = $this->actingAs($this->admin)->get(route('admin.komentar.index', ['rating' => 5]));
        $ratingResponse->assertStatus(200);
        $ratingResponse->assertSee('PembeliKenyang');

        // Filter by rating 1 (should not contain this 5-star review)
        $rating1Response = $this->actingAs($this->admin)->get(route('admin.komentar.index', ['rating' => 1]));
        $rating1Response->assertStatus(200);
        $rating1Response->assertDontSee('PembeliKenyang');
    }

    public function test_penjual_produk_search_category_stok_and_pagination(): void
    {
        $produk = Produk::create([
            'penjual_id' => $this->penjual->id,
            'kategori_id' => $this->kategori1->id,
            'nama' => 'Basreng Pedas Nampol',
            'slug' => 'basreng-pedas-nampol-' . uniqid(),
            'deskripsi' => 'Bakso goreng gurih dan renyah',
            'harga' => 7000,
            'stok' => 'habis',
            'status' => 'aktif',
        ]);

        // 1. Check index controls
        $response = $this->actingAs($this->penjualUser)->get(route('penjual.produk.index'));
        $response->assertStatus(200);
        $response->assertSee('name="search"', false);
        $response->assertSee('name="kategori_id"', false);
        $response->assertSee('name="stok"', false);
        $response->assertSee('Baris:');
        $response->assertSee('Halaman:');
        $response->assertSee('Basreng Pedas Nampol');

        // 2. Search product
        $searchResponse = $this->actingAs($this->penjualUser)->get(route('penjual.produk.index', ['search' => 'Basreng']));
        $searchResponse->assertStatus(200);
        $searchResponse->assertSee('Basreng Pedas Nampol');

        // 3. Filter by stock 'habis'
        $stokResponse = $this->actingAs($this->penjualUser)->get(route('penjual.produk.index', ['stok' => 'habis']));
        $stokResponse->assertStatus(200);
        $stokResponse->assertSee('Basreng Pedas Nampol');

        // 4. Filter by stock 'tersedia' (should not show this 'habis' product)
        $stokAvailResponse = $this->actingAs($this->penjualUser)->get(route('penjual.produk.index', ['stok' => 'tersedia']));
        $stokAvailResponse->assertStatus(200);
        $stokAvailResponse->assertDontSee('Basreng Pedas Nampol');
    }

    public function test_every_existing_registered_seller_has_at_least_10_products(): void
    {
        $existingSellers = Penjual::whereIn('id', [4, 5, 6, 7])->withCount('produk')->get();
        $this->assertNotEmpty($existingSellers);
        foreach ($existingSellers as $seller) {
            $this->assertGreaterThanOrEqual(10, $seller->produk_count, "Penjual {$seller->nama_toko} harus memiliki minimal 10 produk.");
        }
    }
}
