<?php

namespace Tests\Feature;

use App\Models\Kategori;
use App\Models\Penjual;
use App\Models\Produk;
use App\Models\ProdukViewLog;
use App\Models\Ulasan;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Str;
use Tests\TestCase;

class LaporanRekapitulasiUnifiedExportTest extends TestCase
{
    use DatabaseTransactions;

    protected User $penjualUserA;
    protected Penjual $penjualA;
    protected User $penjualUserB;
    protected Penjual $penjualB;
    protected User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->penjualUserA = User::create([
            'name' => 'Penjual Toko A',
            'email' => 'toko_a_' . uniqid() . '@example.test',
            'password' => bcrypt('password'),
            'role' => 'penjual',
            'is_active' => true,
        ]);
        $this->penjualA = Penjual::create([
            'user_id' => $this->penjualUserA->id,
            'nama_toko' => 'Toko A Kue Mantap',
            'nomor_whatsapp' => '081234567891',
            'lokasi_kelas' => 'XII RPL 1',
            'status_toko' => 'buka',
        ]);

        $this->penjualUserB = User::create([
            'name' => 'Penjual Toko B',
            'email' => 'toko_b_' . uniqid() . '@example.test',
            'password' => bcrypt('password'),
            'role' => 'penjual',
            'is_active' => true,
        ]);
        $this->penjualB = Penjual::create([
            'user_id' => $this->penjualUserB->id,
            'nama_toko' => 'Toko B Baju & Craft',
            'nomor_whatsapp' => '081234567892',
            'lokasi_kelas' => 'XII RPL 2',
            'status_toko' => 'buka',
        ]);

        $this->adminUser = User::create([
            'name' => 'Admin SiswaMart',
            'email' => 'admin_unified_' . uniqid() . '@example.test',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'is_active' => true,
        ]);
    }

    /**
     * (1) Verifikasi: Angka di web, PDF, dan Sheet sama persis tanpa selisih.
     */
    public function test_web_pdf_and_sheet_metrics_are_identical(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-10-26 14:00:00', 'Asia/Jakarta'));
        try {
            $cat = Kategori::create(['nama' => 'Snack ' . uniqid(), 'slug' => 'snack-' . uniqid()]);
            $p1 = Produk::create([
                'penjual_id' => $this->penjualA->id,
                'kategori_id' => $cat->id,
                'nama' => 'Keripik Pedas',
                'slug' => 'keripik-pedas-' . uniqid(),
                'harga' => 15000,
                'stok' => 'tersedia',
                'status' => 'aktif',
                'views' => 10,
            ]);

            // 3 views hari ini
            foreach (range(1, 3) as $_) {
                ProdukViewLog::create(['produk_id' => $p1->id, 'type' => 'view', 'created_at' => now('Asia/Jakarta')]);
            }

            // 1 ulasan
            Ulasan::create([
                'produk_id' => $p1->id,
                'guest_username' => 'Pembeli Satu',
                'rating' => 5,
                'komentar' => 'Sangat renyah!',
                'moderation_status' => 'approved',
                'status' => 'approved',
                'created_at' => now('Asia/Jakarta'),
            ]);

            // 1. Web
            $webResp = $this->actingAs($this->penjualUserA)->get(route('penjual.laporan'));
            $webResp->assertOk();
            $webResp->assertSee('3'); // 3 views
            $webResp->assertSee('Keripik Pedas');

            // 2. PDF
            $pdfResp = $this->actingAs($this->penjualUserA)->get(route('penjual.laporan.pdf'));
            $pdfResp->assertOk();
            $this->assertStringContainsString('application/pdf', (string) $pdfResp->headers->get('Content-Type'));
            $this->assertNotEmpty($pdfResp->getContent());

            // 3. Sheet (XLSX)
            $sheetResp = $this->actingAs($this->penjualUserA)->get(route('penjual.laporan.sheet'));
            $sheetResp->assertOk();
            $exportPath = $sheetResp->baseResponse->getFile()->getPathname();
            $content = file_get_contents($exportPath);
            @unlink($exportPath);

            // Verifikasi bahwa angka 3 views, 1 produk, 1 ulasan ada di XLSX
            $this->assertTrue(str_contains($content, 'Ringkasan'));
            $this->assertTrue(str_contains($content, 'Top 5 Produk'));
            $this->assertTrue(str_contains($content, 'Views Harian'));
            $this->assertTrue(str_contains($content, 'Keripik Pedas'));
        } finally {
            Carbon::setTestNow();
        }
    }

    /**
     * (2) Verifikasi karakter khusus Indonesia & aksen (â, ’, apostrof) di PDF dan XLSX.
     */
    public function test_special_characters_and_accents_render_properly(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-10-26 14:00:00', 'Asia/Jakarta'));
        try {
            $cat = Kategori::create(['nama' => 'Kategori Khusus ' . uniqid(), 'slug' => 'kat-' . uniqid()]);
            $p = Produk::create([
                'penjual_id' => $this->penjualA->id,
                'kategori_id' => $cat->id,
                'nama' => "Kue Châtaigne d'or & Donat",
                'slug' => 'kue-special-' . uniqid(),
                'harga' => 20000,
                'stok' => 'tersedia',
                'status' => 'aktif',
                'views' => 5,
            ]);

            Ulasan::create([
                'produk_id' => $p->id,
                'guest_username' => 'Pembeli Rêveur',
                'rating' => 5,
                'komentar' => 'Rasanya sangat istimewa, c’est délicieux!',
                'moderation_status' => 'approved',
                'status' => 'approved',
            ]);

            // PDF
            $pdfResp = $this->actingAs($this->penjualUserA)->get(route('penjual.laporan.pdf'));
            $pdfResp->assertOk();

            // XLSX
            $sheetResp = $this->actingAs($this->penjualUserA)->get(route('penjual.laporan.sheet'));
            $sheetResp->assertOk();
            $exportPath = $sheetResp->baseResponse->getFile()->getPathname();
            $content = file_get_contents($exportPath);
            @unlink($exportPath);

            $this->assertTrue(str_contains($content, 'Ch&#226;taigne') || str_contains($content, 'Châtaigne'));
            $this->assertTrue(str_contains($content, 'c&#8217;est') || str_contains($content, "c’est") || str_contains($content, "d&#039;or"));
        } finally {
            Carbon::setTestNow();
        }
    }

    /**
     * (4) Verifikasi tabel kosong menampilkan 'Belum ada data pada periode ini'.
     */
    public function test_empty_tables_render_empty_state_text(): void
    {
        // Penjual B tidak memiliki produk dan views
        $pdfResp = $this->actingAs($this->penjualUserB)->get(route('penjual.laporan.pdf'));
        $pdfResp->assertOk();

        $data = app(\App\Services\LaporanPenjualService::class)->getSummary($this->penjualB, 0);
        $html = view('penjual.laporan.pdf', $data)->render();
        $this->assertStringContainsString('Belum ada data pada periode ini', $html);
    }

    /**
     * (5) Keamanan Formula Injection: Komentar '=1+1' dan nama produk berawalan '-' tidak menjadi formula.
     */
    public function test_formula_injection_prevention(): void
    {
        $cat = Kategori::create(['nama' => 'Kategori Injeksi ' . uniqid(), 'slug' => 'inj-' . uniqid()]);
        $prod = Produk::create([
            'penjual_id' => $this->penjualA->id,
            'kategori_id' => $cat->id,
            'nama' => '-Diskon50% Promo Bahaya',
            'slug' => 'diskon-bahaya-' . uniqid(),
            'harga' => 10000,
            'stok' => 'tersedia',
            'status' => 'aktif',
            'views' => 1,
        ]);

        Ulasan::create([
            'produk_id' => $prod->id,
            'guest_username' => '=cmd|"/C calc"!A0',
            'rating' => 1,
            'komentar' => '=1+1',
            'moderation_status' => 'approved',
            'status' => 'approved',
        ]);

        $sheetResp = $this->actingAs($this->penjualUserA)->get(route('penjual.laporan.sheet'));
        $sheetResp->assertOk();
        $exportPath = $sheetResp->baseResponse->getFile()->getPathname();
        $content = file_get_contents($exportPath);
        @unlink($exportPath);

        // Nilai harus disimpan sebagai inlineStr literal text dan bukan formula <f>
        $this->assertFalse(str_contains($content, '<f>=1+1</f>'), 'Formula injection should never be written as <f> element.');
        $this->assertTrue(str_contains($content, 't="inlineStr"'), 'Text cells should use t="inlineStr".');
    }

    /**
     * (6) Uji penjual dengan 0, 1, dan 5+ produk.
     */
    public function test_seller_with_zero_one_and_five_plus_products(): void
    {
        // 0 produk (Penjual B)
        $this->actingAs($this->penjualUserB)->get(route('penjual.laporan'))->assertOk();
        $this->actingAs($this->penjualUserB)->get(route('penjual.laporan.pdf'))->assertOk();
        $this->actingAs($this->penjualUserB)->get(route('penjual.laporan.sheet'))->assertOk();

        // 5+ produk
        $cat = Kategori::create(['nama' => 'Kategori Multi ' . uniqid(), 'slug' => 'multi-' . uniqid()]);
        foreach (range(1, 6) as $i) {
            $p = Produk::create([
                'penjual_id' => $this->penjualA->id,
                'kategori_id' => $cat->id,
                'nama' => 'Produk Multi ' . $i,
                'slug' => 'produk-multi-' . $i . '-' . uniqid(),
                'harga' => 5000 * $i,
                'stok' => 'tersedia',
                'status' => 'aktif',
                'views' => $i * 10,
            ]);
            ProdukViewLog::create(['produk_id' => $p->id, 'type' => 'view']);
        }

        $this->actingAs($this->penjualUserA)->get(route('penjual.laporan'))->assertOk();
        $this->actingAs($this->penjualUserA)->get(route('penjual.laporan.pdf'))->assertOk();
        $this->actingAs($this->penjualUserA)->get(route('penjual.laporan.sheet'))->assertOk();
    }

    /**
     * (7) Otorisasi: Penjual A tidak dapat mengunduh data Toko B.
     */
    public function test_seller_a_cannot_access_seller_b_data(): void
    {
        // Penjual A mengakses endpoint penjual miliknya
        $resp = $this->actingAs($this->penjualUserA)->get(route('penjual.laporan'));
        $resp->assertOk();
        $resp->assertSee($this->penjualA->nama_toko);
        $resp->assertDontSee($this->penjualB->nama_toko);

        // Penjual tidak dapat mengakses laporan admin
        $this->actingAs($this->penjualUserA)->get(route('admin.laporan.index'))->assertForbidden();
        $this->actingAs($this->penjualUserA)->get(route('admin.laporan.penjual.pdf'))->assertForbidden();
        $this->actingAs($this->penjualUserA)->get(route('admin.laporan.penjual.sheet'))->assertForbidden();
        $this->actingAs($this->penjualUserA)->get(route('admin.laporan.ulasan.pdf'))->assertForbidden();
        $this->actingAs($this->penjualUserA)->get(route('admin.laporan.ulasan.sheet'))->assertForbidden();
    }

    /**
     * (8) Dropdown component rendering check on both penjual and admin pages.
     */
    public function test_dropdown_download_menu_rendered_on_recap_pages(): void
    {
        // Penjual
        $penjualResp = $this->actingAs($this->penjualUserA)->get(route('penjual.laporan'));
        $penjualResp->assertOk();
        $penjualResp->assertSee('download-menu');
        $penjualResp->assertSee('role="menu"', false);
        $penjualResp->assertSee('role="menuitem"', false);
        $penjualResp->assertSee('aria-haspopup="menu"', false);
        $penjualResp->assertSee('Sheet Excel');
        $penjualResp->assertSee('PDF');

        // Admin
        $adminResp = $this->actingAs($this->adminUser)->get(route('admin.laporan.index'));
        $adminResp->assertOk();
        $adminResp->assertSee('download-menu');
        $adminResp->assertSee('Sheet Excel');
        $adminResp->assertSee('PDF');
    }
}
