<?php

namespace Tests\Feature;

use App\Models\Kategori;
use App\Models\Penjual;
use App\Models\Produk;
use App\Models\Ulasan;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class AdminRekapPenjualTest extends TestCase
{
    use DatabaseTransactions;

    private User $admin;
    private User $penjualUser;
    private Penjual $penjual;
    private Kategori $kategori;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::create([
            'name' => 'Admin Test Rekap',
            'email' => 'admin_rekap_' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'is_active' => true,
        ]);

        $this->penjualUser = User::create([
            'name' => 'Budi Santoso',
            'email' => 'budi_' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'role' => 'penjual',
            'is_active' => true,
        ]);

        $this->penjual = Penjual::create([
            'user_id' => $this->penjualUser->id,
            'nama_toko' => 'Warung Budi Berkah',
            'deskripsi_toko' => 'Aneka camilan siswa',
            'nomor_whatsapp' => '08987654321',
            'lokasi_kelas' => 'XII RPL 2',
            'status_toko' => 'buka',
        ]);

        $this->kategori = Kategori::firstOrCreate(
            ['nama' => 'Makanan Ringan'],
            ['slug' => 'makanan-ringan']
        );
    }

    public function test_guest_is_redirected_from_admin_dashboard_and_rekap(): void
    {
        $response = $this->get(route('admin.dashboard'));
        $response->assertRedirect(route('login'));

        $rekapResponse = $this->get(route('admin.laporan.index', ['kategori' => 'penjual']));
        $rekapResponse->assertRedirect(route('login'));
    }

    public function test_penjual_cannot_access_admin_dashboard_or_rekap(): void
    {
        $response = $this->actingAs($this->penjualUser)->get(route('admin.dashboard'));
        $response->assertStatus(403);

        $rekapResponse = $this->actingAs($this->penjualUser)->get(route('admin.laporan.index', ['kategori' => 'penjual']));
        $rekapResponse->assertStatus(403);
    }

    public function test_admin_dashboard_shows_stat_cards_and_shortcut_to_rekap_penjual(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Total Penjual');
        $response->assertSee('Produk Aktif');
        $response->assertSee('Perlu Moderasi');
        $response->assertSee(route('admin.laporan.index', ['kategori' => 'penjual']));
        $response->assertSee('Buka Rekap Penjual');
    }

    public function test_admin_rekap_penjual_shows_table_with_store_and_seller_details(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.laporan.index', ['kategori' => 'penjual']));

        $response->assertStatus(200);
        $response->assertSee('Rekap Penjual');
        $response->assertSee('PENJUAL & TOKO', false);
        $response->assertSee('TOTAL PRODUK');
        $response->assertSee('ULASAN & RATING', false);
        $response->assertSee('AKTIVITAS');
        $response->assertSee('Warung Budi Berkah');
        $response->assertSee('Budi Santoso');
        $response->assertSee('XII RPL 2');
        $response->assertSee('id="rekapSearchInput"', false);
    }

    public function test_admin_rekap_penjual_counts_total_products_and_reviews_accurately(): void
    {
        $produk = Produk::create([
            'penjual_id' => $this->penjual->id,
            'kategori_id' => $this->kategori->id,
            'nama' => 'Keripik Tempe Renyah',
            'slug' => 'keripik-tempe-renyah-' . uniqid(),
            'deskripsi' => 'Keripik tempe gurih',
            'harga' => 5000,
            'stok' => 'tersedia',
            'status' => 'aktif',
        ]);

        Ulasan::create([
            'produk_id' => $produk->id,
            'guest_username' => 'SiswaPengunjung',
            'rating' => 5,
            'komentar' => 'Enak dan renyah sekali!',
            'status' => 'aktif',
            'moderation_status' => 'approved',
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.laporan.index', ['kategori' => 'penjual']));

        $response->assertStatus(200);
        $response->assertSee('Keripik Tempe Renyah');
        $response->assertSee('1 Baru');
        $response->assertSee('1 ulasan');
        $response->assertSee('5.0');
        $response->assertSee('1 Aktif');
        $response->assertSee('Detail');
        $response->assertSee('wa.me/08987654321');
    }
}
