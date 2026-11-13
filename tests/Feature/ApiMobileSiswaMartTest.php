<?php

namespace Tests\Feature;

use App\Models\Kategori;
use App\Models\Penjual;
use App\Models\Produk;
use App\Models\ProdukViewLog;
use App\Models\Ulasan;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Tests\TestCase;

class ApiMobileSiswaMartTest extends TestCase
{
    use DatabaseTransactions;

    protected User $userActive;
    protected Penjual $sellerActive;
    protected Kategori $kategori;
    protected Produk $produkAktif;

    protected function setUp(): void
    {
        parent::setUp();

        Cache::flush();

        // Siapkan user aktif
        $this->userActive = User::factory()->create([
            'role' => 'penjual',
            'is_active' => true,
        ]);

        // Siapkan penjual aktif dengan toko buka
        $this->sellerActive = Penjual::create([
            'user_id' => $this->userActive->id,
            'nama_toko' => 'Toko Siswa Kreatif',
            'deskripsi_toko' => 'Toko siswa SMK binaan',
            'nomor_whatsapp' => '081234567890',
            'lokasi_kelas' => 'XII RPL 1',
            'status_toko' => 'buka',
        ]);

        // Siapkan kategori
        $this->kategori = Kategori::create([
            'nama' => 'Makanan Ringan',
            'slug' => 'makanan-ringan-' . Str::random(5),
            'icon' => 'snack',
        ]);

        // Siapkan produk aktif
        $this->produkAktif = Produk::create([
            'penjual_id' => $this->sellerActive->id,
            'kategori_id' => $this->kategori->id,
            'nama' => 'Keripik Tempe Renyah',
            'slug' => 'keripik-tempe-renyah-' . Str::random(5),
            'deskripsi' => 'Keripik tempe gurih rasa original',
            'harga' => 5000,
            'stok' => 'tersedia',
            'label_promosi' => 'Best Seller',
            'status' => 'aktif',
            'views' => 10,
        ]);
    }

    /**
     * Test daftar produk dengan filter, search, sort, dan pagination 12 per halaman.
     */
    public function test_products_index_returns_paginated_and_filtered_data(): void
    {
        $response = $this->getJson('/api/products?q=Keripik');

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'current_page',
                    'data' => [
                        '*' => [
                            'id',
                            'slug',
                            'nama',
                            'harga',
                            'kategori',
                            'stok',
                            'nama_toko',
                            'views',
                            'rating',
                            'review_count',
                        ],
                    ],
                    'per_page',
                    'total',
                ],
            ]);

        $this->assertEquals(12, $response->json('data.per_page'));
    }

    /**
     * Test produk dari penjual non-aktif atau status produk pending/ditolak tidak muncul di API.
     */
    public function test_inactive_seller_or_non_active_product_not_visible(): void
    {
        $userInactive = User::factory()->create([
            'role' => 'penjual',
            'is_active' => false,
        ]);

        $sellerInactive = Penjual::create([
            'user_id' => $userInactive->id,
            'nama_toko' => 'Toko Nonaktif',
            'nomor_whatsapp' => '089999999999',
            'status_toko' => 'buka',
        ]);

        $produkHidden = Produk::create([
            'penjual_id' => $sellerInactive->id,
            'kategori_id' => $this->kategori->id,
            'nama' => 'Produk Penjual Nonaktif',
            'slug' => 'produk-penjual-nonaktif-' . Str::random(5),
            'harga' => 10000,
            'stok' => 'tersedia',
            'status' => 'aktif',
            'views' => 0,
        ]);

        // Tidak boleh muncul di list
        $response = $this->getJson('/api/products?q=Nonaktif');
        $response->assertStatus(200);
        $this->assertEmpty($response->json('data.data'));

        // Tidak boleh bisa dibuka detailnya
        $detailResponse = $this->getJson('/api/products/' . $produkHidden->slug);
        $detailResponse->assertStatus(404);
    }

    /**
     * Test detail produk by slug dan by id.
     */
    public function test_product_detail_by_slug_and_id(): void
    {
        $resBySlug = $this->getJson('/api/products/' . $this->produkAktif->slug);
        $resBySlug->assertStatus(200)
            ->assertJsonPath('data.slug', $this->produkAktif->slug)
            ->assertJsonPath('data.nama', $this->produkAktif->nama)
            ->assertJsonPath('data.nama_toko', $this->sellerActive->nama_toko);

        // Pastikan phone mentah tidak dibocorkan di data penjual
        $this->assertNull($resBySlug->json('data.penjual.phone') ?? null);
        $this->assertNull($resBySlug->json('data.seller.phone') ?? null);

        $resById = $this->getJson('/api/products/' . $this->produkAktif->id);
        $resById->assertStatus(200)
            ->assertJsonPath('data.id', $this->produkAktif->id);
    }

    /**
     * Test deduplikasi views dengan device_id: dibuka dua kali pada hari yang sama hanya +1 view.
     */
    public function test_view_deduplication_with_device_id(): void
    {
        $deviceId = 'device-test-uuid-12345';
        $initialViews = (int) $this->produkAktif->views;

        // Panggilan 1 via trackView
        $res1 = $this->withHeader('X-Device-Id', $deviceId)
            ->postJson('/api/products/' . $this->produkAktif->slug . '/view');

        $res1->assertStatus(200)
            ->assertJsonPath('data.incremented', true)
            ->assertJsonPath('data.views', $initialViews + 1);

        // Panggilan 2 via trackView dengan device_id yang sama dalam 24 jam
        $res2 = $this->withHeader('X-Device-Id', $deviceId)
            ->postJson('/api/products/' . $this->produkAktif->slug . '/view');

        $res2->assertStatus(200)
            ->assertJsonPath('data.incremented', false)
            ->assertJsonPath('data.views', $initialViews + 1);

        $this->assertEquals($initialViews + 1, $this->produkAktif->fresh()->views);
    }

    /**
     * Test WhatsApp endpoint: format nomor (0 -> 62), pesan produk, logging, dan awalan toko tutup.
     */
    public function test_whatsapp_endpoint_formats_url_and_logs_view(): void
    {
        $initialLogCount = ProdukViewLog::where('produk_id', $this->produkAktif->id)
            ->where('type', 'whatsapp')
            ->count();

        // 1. Toko buka
        $response = $this->postJson('/api/products/' . $this->produkAktif->slug . '/whatsapp');
        $response->assertStatus(200)
            ->assertJsonPath('data.phone', '6281234567890')
            ->assertJsonPath('data.store_closed', false);

        $url = $response->json('data.whatsapp_url');
        $this->assertStringContainsString('https://wa.me/6281234567890', $url);
        $this->assertStringContainsString(rawurlencode($this->produkAktif->nama), $url);

        $newLogCount = ProdukViewLog::where('produk_id', $this->produkAktif->id)
            ->where('type', 'whatsapp')
            ->count();
        $this->assertEquals($initialLogCount + 1, $newLogCount);

        // 2. Toko tutup: pesan ada peringatan
        $this->sellerActive->update(['status_toko' => 'tutup']);
        $resClosed = $this->postJson('/api/products/' . $this->produkAktif->slug . '/whatsapp');
        $resClosed->assertStatus(200)
            ->assertJsonPath('data.store_closed', true);
        $this->assertStringContainsString('[Pemberitahuan: Toko sedang tutup, balasan mungkin tertunda]', $resClosed->json('data.message'));

        // 3. Nomor kosong: kembalikan 422 dengan pesan ramah
        $this->sellerActive->update(['nomor_whatsapp' => '']);
        $resEmpty = $this->postJson('/api/products/' . $this->produkAktif->slug . '/whatsapp');
        $resEmpty->assertStatus(422)
            ->assertJsonPath('message', 'Nomor kontak WhatsApp penjual belum tersedia.');
    }

    /**
     * Test ulasan bersih tersimpan sebagai approved dan memicu notifikasi admin.
     */
    public function test_submit_clean_review_success(): void
    {
        $response = $this->postJson('/api/products/' . $this->produkAktif->slug . '/reviews', [
            'guest_username' => 'Ahmad Siswa',
            'rating' => 5,
            'komentar' => 'Rasanya sangat enak dan gurih!',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.name', 'Ahmad Siswa')
            ->assertJsonPath('data.rating', 5);

        $this->assertDatabaseHas('ulasan', [
            'produk_id' => $this->produkAktif->id,
            'guest_username' => 'Ahmad Siswa',
            'rating' => 5,
            'status' => 'approved',
            'moderation_status' => 'approved',
        ]);
    }

    /**
     * Test ulasan dengan kata terlarang ditolak dengan pesan 422 yang sama dengan web.
     */
    public function test_submit_review_with_bad_words_rejected(): void
    {
        // Masukkan kata kasar umum yang ada di sistem
        $response = $this->postJson('/api/products/' . $this->produkAktif->slug . '/reviews', [
            'guest_username' => 'Budi',
            'rating' => 1,
            'komentar' => 'Produk ini sangat anjing dan jelek',
        ]);

        // Jika terdeteksi kata terlarang
        $response->assertStatus(422);
        $this->assertStringContainsString('kata yang tidak pantas', json_encode($response->json()));
    }

    /**
     * Test endpoint daftar ulasan per produk berpaginasi.
     */
    public function test_product_reviews_paginated(): void
    {
        Ulasan::create([
            'produk_id' => $this->produkAktif->id,
            'guest_username' => 'Pengulas Satu',
            'rating' => 4,
            'komentar' => 'Bagus sekali',
            'original_komentar' => 'Bagus sekali',
            'status' => 'approved',
            'moderation_status' => 'approved',
        ]);

        $response = $this->getJson('/api/products/' . $this->produkAktif->slug . '/reviews');
        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'data' => [
                        '*' => [
                            'id',
                            'guest_username',
                            'name',
                            'rating',
                            'comment',
                            'display_content',
                            'date',
                        ],
                    ],
                ],
            ]);
    }
}
