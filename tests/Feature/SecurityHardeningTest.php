<?php

namespace Tests\Feature;

use App\Models\BadWord;
use App\Models\Kategori;
use App\Models\Penjual;
use App\Models\Produk;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class SecurityHardeningTest extends TestCase
{
    use DatabaseTransactions;

    protected User $admin;
    protected User $penjualUser;
    protected Penjual $penjual;
    protected Kategori $kategori;
    protected Produk $produk;

    protected function setUp(): void
    {
        parent::setUp();

        // Admin User
        $this->admin = User::create([
            'name' => 'Admin Security Test',
            'email' => 'admin-sec-' . uniqid() . '@siswamartid.test',
            'username' => 'admin_sec_' . uniqid(),
            'password' => bcrypt('password123'),
            'role' => 'admin',
            'is_active' => true,
        ]);

        // Penjual User & Profile
        $this->penjualUser = User::create([
            'name' => 'Penjual Security Test',
            'email' => 'seller-sec-' . uniqid() . '@siswamartid.test',
            'username' => 'seller_sec_' . uniqid(),
            'password' => bcrypt('password123'),
            'role' => 'penjual',
            'is_active' => true,
        ]);

        $this->penjual = Penjual::create([
            'user_id' => $this->penjualUser->id,
            'nama_toko' => 'Toko Security Test ' . uniqid(),
            'deskripsi_toko' => 'Toko untuk pengujian keamanan',
            'nomor_whatsapp' => '081299998888',
            'lokasi_kelas' => 'XII RPL 1',
            'nisn' => str_pad((string) rand(1000000000, 9999999999), 10, '0', STR_PAD_LEFT),
            'alamat' => 'Gedung Kejuruan Lt 2',
            'status_toko' => 'buka',
        ]);

        // Kategori
        $this->kategori = Kategori::create([
            'nama' => 'Kategori Sec ' . uniqid(),
            'slug' => 'kategori-sec-' . uniqid(),
            'icon' => 'tag',
        ]);

        // Produk Aktif
        $this->produk = Produk::create([
            'penjual_id' => $this->penjual->id,
            'kategori_id' => $this->kategori->id,
            'nama' => 'Produk Security Test',
            'slug' => 'produk-sec-' . uniqid(),
            'deskripsi' => 'Deskripsi produk security',
            'harga' => 15000,
            'stok' => 'tersedia',
            'status' => 'aktif',
        ]);
    }

    /**
     * Uji 1: Penonaktifan akun penjual oleh admin mencabut token Sanctum,
     * dan akun tidak aktif diblokir dari API dengan HTTP 403.
     */
    public function test_deactivated_seller_tokens_are_revoked_and_api_access_blocked(): void
    {
        // 1. Seller membuat token API
        $token = $this->penjualUser->createToken('test-sec-token')->plainTextToken;
        $this->assertDatabaseHas('personal_access_tokens', [
            'tokenable_id' => $this->penjualUser->id,
        ]);

        // 2. Admin menonaktifkan penjual melalui web endpoint toggleStatus
        $response = $this->actingAs($this->admin)->patch(
            route('admin.penjual.toggleStatus', $this->penjualUser->id)
        );
        $response->assertRedirect(route('admin.penjual.index'));

        $this->penjualUser->refresh();
        $this->assertFalse((bool) $this->penjualUser->is_active);

        // 3. Pastikan token telah dicabut dari database
        $this->assertDatabaseMissing('personal_access_tokens', [
            'tokenable_id' => $this->penjualUser->id,
        ]);

        // Reset session & auth state dari actingAs web
        \Illuminate\Support\Facades\Auth::logout();
        $this->app['auth']->forgetGuards();

        // 4. Request dengan token lama menghasilkan 401 Unauthenticated
        $apiResponse = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/user');
        $apiResponse->assertStatus(401);

        // 5. Jika akun tidak aktif dipaksa memiliki token, middleware active memblokir dengan 403
        $forcedToken = $this->penjualUser->createToken('forced-token')->plainTextToken;
        $blockedResponse = $this->withHeader('Authorization', 'Bearer ' . $forcedToken)
            ->getJson('/api/user');
        $blockedResponse->assertStatus(403);
        $blockedResponse->assertJsonFragment([
            'success' => false,
            'message' => 'Akun Anda sedang dinonaktifkan oleh pihak sekolah. Silakan hubungi administrator.',
        ]);

        // Pastikan forced token juga dihapus oleh middleware EnsureUserIsActive
        $this->assertDatabaseMissing('personal_access_tokens', [
            'tokenable_id' => $this->penjualUser->id,
        ]);
    }

    /**
     * Uji 2: Admin yang statusnya is_active = false tidak dapat mengakses dashboard admin.
     */
    public function test_deactivated_admin_cannot_access_admin_dashboard(): void
    {
        $this->admin->update(['is_active' => false]);

        $response = $this->actingAs($this->admin)->get(route('admin.dashboard'));
        $response->assertRedirect(route('login'));
        $response->assertSessionHasErrors(['email']);
    }

    /**
     * Uji 3: Moderasi kata kotor berlaku untuk guest_username pada web dan API.
     */
    public function test_guest_username_moderation_rejects_bad_words(): void
    {
        BadWord::firstOrCreate(
            ['word' => 'kataterlarangsec'],
            ['category' => 'kasar', 'is_active' => true]
        );

        // 1. Submit ulasan pada Web dengan username terlarang
        $webResponse = $this->post(
            route('public.ulasan.store', $this->produk->slug),
            [
                'guest_username' => 'user_kataterlarangsec_test',
                'rating' => 5,
                'komentar' => 'Produk sangat bagus dan mantap!',
            ]
        );
        $webResponse->assertRedirect(route('public.produk.show', $this->produk->slug) . '#ulasan');
        $webResponse->assertSessionHas('review_warning');

        // Pastikan ulasan tidak tersimpan di database
        $this->assertDatabaseMissing('ulasan', [
            'produk_id' => $this->produk->id,
            'guest_username' => 'user_kataterlarangsec_test',
        ]);

        // 2. Submit ulasan pada API dengan username terlarang
        $apiResponse = $this->postJson(
            "/api/products/{$this->produk->id}/reviews",
            [
                'guest_username' => 'user_kataterlarangsec_api',
                'rating' => 4,
                'comment' => 'Produk enak dan renyah.',
            ]
        );
        $apiResponse->assertStatus(422);
        $apiResponse->assertJsonValidationErrors(['guest_username']);
    }

    /**
     * Uji 4: Produk dari penjual yang tidak aktif tidak dapat diulas (Web & API menghasilkan 404).
     */
    public function test_cannot_review_product_of_deactivated_seller(): void
    {
        // Nonaktifkan penjual
        $this->penjualUser->update(['is_active' => false]);

        // 1. Web review submission
        $webResponse = $this->post(
            route('public.ulasan.store', $this->produk->slug),
            [
                'guest_username' => 'GuestBuyer',
                'rating' => 5,
                'komentar' => 'Ulasan untuk penjual nonaktif',
            ]
        );
        $webResponse->assertStatus(404);

        // 2. API review submission
        $apiResponse = $this->postJson(
            "/api/products/{$this->produk->id}/reviews",
            [
                'guest_username' => 'GuestBuyer',
                'rating' => 5,
                'comment' => 'Ulasan untuk penjual nonaktif',
            ]
        );
        $apiResponse->assertStatus(404);

        // 3. API reviews list
        $listResponse = $this->getJson("/api/products/{$this->produk->id}/reviews");
        $listResponse->assertStatus(404);
    }
}
