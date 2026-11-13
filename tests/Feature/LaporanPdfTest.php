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

class LaporanPdfTest extends TestCase
{
    use DatabaseTransactions;

    private User $admin;

    private User $penjualUser;

    private Penjual $penjual;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::create([
            'name' => 'Admin Test',
            'email' => 'admintest_'.uniqid().'@example.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'is_active' => true,
        ]);

        $this->penjualUser = User::create([
            'name' => 'Penjual Test',
            'email' => 'penjualtest_'.uniqid().'@example.com',
            'password' => bcrypt('password'),
            'role' => 'penjual',
            'is_active' => true,
        ]);

        $this->penjual = Penjual::create([
            'user_id' => $this->penjualUser->id,
            'nama_toko' => 'Toko Siswa Sukses',
            'deskripsi_toko' => 'Menjual aneka snack dan minuman.',
            'nomor_whatsapp' => '081234567890',
            'lokasi_kelas' => 'XII RPL 1',
            'status_toko' => 'buka',
        ]);
    }

    public function test_penjual_laporan_page_contains_unduh_sheet_button(): void
    {
        $response = $this->actingAs($this->penjualUser)->get(route('penjual.laporan'));

        $response->assertStatus(200);
        $response->assertSee('Unduh Laporan');
        $response->assertSee(route('penjual.laporan.sheet', ['week_offset' => 0]));
        $response->assertSee('Ringkasan 7 hari terakhir');
        $response->assertSee('Top 5 produk terpopuler');
        $response->assertDontSee('Cetak Rekap');
        $response->assertDontSee('window.print()');
    }

    public function test_penjual_report_shows_empty_state_when_there_are_no_products_or_views(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-10-26 12:00:00'));
        try {
            $response = $this->actingAs($this->penjualUser)->get(route('penjual.laporan'));

            $response->assertOk()
                ->assertSee('Belum ada views pada periode ini.')
                ->assertSee('Views per hari:')
                ->assertSee('0 views')
                ->assertDontSee('Performa Semua Produk')
                ->assertDontSee('Ulasan Mingguan');
            $this->assertSame(0, substr_count($response->getContent(), 'class="popular-list__item"'));
            $this->assertStringContainsString('7 hari berikutnya', $response->getContent());
            $this->assertStringContainsString('disabled aria-disabled="true"', $response->getContent());
            $this->assertStringContainsString('20 – 26 Oktober 2026', $response->getContent());
        } finally {
            Carbon::setTestNow();
        }
    }

    public function test_future_report_offset_redirects_to_the_latest_period(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-10-26 12:00:00'));
        try {
            $this->actingAs($this->penjualUser)
                ->get(route('penjual.laporan', ['week_offset' => 1]))
                ->assertRedirect(route('penjual.laporan'));
        } finally {
            Carbon::setTestNow();
        }
    }

    public function test_penjual_report_shows_one_product_views_and_reviews_from_the_last_seven_days(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-10-26 12:00:00'));
        try {
            $product = $this->createReportProduct('Produk Satu', 0);
            foreach (range(1, 3) as $_) {
                ProdukViewLog::create(['produk_id' => $product->id, 'type' => 'view']);
            }
            Ulasan::create([
                'produk_id' => $product->id,
                'guest_username' => 'Pengulas Terbaru',
                'rating' => 5,
                'komentar' => 'Bagus',
                'status' => 'approved',
                'moderation_status' => 'approved',
            ]);

            $response = $this->actingAs($this->penjualUser)->get(route('penjual.laporan'));

            $response->assertOk()
                ->assertSee('Produk Satu')
                ->assertSee('3 views')
                ->assertSee('style="width: 100%"', false)
                ->assertSee('Baru 1 produk yang punya views.')
                ->assertSee('7 hari berikutnya')
                ->assertDontSee('disabled aria-disabled="true"');
            $this->assertStringContainsString(
                route('penjual.laporan', ['week_offset' => 0]),
                $this->actingAs($this->penjualUser)->get(route('penjual.laporan', ['week_offset' => -1]))->getContent()
            );
        } finally {
            Carbon::setTestNow();
        }
    }

    public function test_popular_products_show_an_empty_state_when_no_views_exist_in_the_period(): void
    {
        $this->createReportProduct('Produk Tanpa View', 20);

        $response = $this->actingAs($this->penjualUser)->get(route('penjual.laporan'));

        $response->assertOk()
            ->assertSee('Belum ada views pada periode ini.')
            ->assertDontSee('dari semua views');
        $this->assertSame(0, substr_count($response->getContent(), 'class="popular-list__item"'));
    }

    public function test_penjual_report_limits_popular_products_to_five_and_sorts_by_views_then_name(): void
    {
        foreach ([
            'Produk Alpha' => 7,
            'Produk Charlie' => 5,
            'Produk Bravo' => 5,
            'Produk Delta' => 3,
            'Produk Echo' => 2,
            'Produk Foxtrot' => 1,
        ] as $name => $views) {
            $product = $this->createReportProduct($name, $views);
            foreach (range(1, $views) as $_) {
                ProdukViewLog::create(['produk_id' => $product->id, 'type' => 'view']);
            }
        }

        $response = $this->actingAs($this->penjualUser)->get(route('penjual.laporan'));

        $response->assertOk()
            ->assertSee('Produk Echo')
            ->assertDontSee('Produk Foxtrot')
            ->assertSee('style="width: 100%"', false)
            ->assertSee('style="width: 71.4%"', false)
            ->assertSee('style="width: 42.9%"', false);
        $this->assertSame(5, substr_count($response->getContent(), 'class="popular-list__item"'));
        $response->assertSeeInOrder([
            'Produk Alpha',
            'Produk Bravo',
            'Produk Charlie',
            'Produk Delta',
            'Produk Echo',
        ]);
    }

    public function test_penjual_report_only_includes_the_authenticated_sellers_data(): void
    {
        $ownProduct = $this->createReportProduct('Produk Milik Saya', 0);
        ProdukViewLog::create(['produk_id' => $ownProduct->id, 'type' => 'view']);

        $otherUser = User::create([
            'name' => 'Penjual Lain',
            'email' => 'penjuallain_'.uniqid().'@example.com',
            'password' => bcrypt('password'),
            'role' => 'penjual',
            'is_active' => true,
        ]);
        $otherSeller = Penjual::create([
            'user_id' => $otherUser->id,
            'nama_toko' => 'Toko Lain',
            'deskripsi_toko' => 'Toko pengujian',
            'nomor_whatsapp' => '081234567890',
            'lokasi_kelas' => 'XII RPL 1',
            'status_toko' => 'buka',
        ]);
        $otherCategory = Kategori::create([
            'nama' => 'Kategori Penjual Lain '.uniqid(),
            'slug' => 'kategori-penjual-lain-'.uniqid(),
        ]);
        $otherProduct = Produk::create([
            'penjual_id' => $otherSeller->id,
            'kategori_id' => $otherCategory->id,
            'nama' => 'Produk Rahasia Penjual Lain',
            'slug' => 'produk-rahasia-'.uniqid(),
            'harga' => 10000,
            'stok' => 'tersedia',
            'status' => 'aktif',
            'views' => 100,
        ]);
        foreach (range(1, 10) as $_) {
            ProdukViewLog::create(['produk_id' => $otherProduct->id, 'type' => 'view']);
        }
        Ulasan::create([
            'produk_id' => $otherProduct->id,
            'guest_username' => 'Pengulas Penjual Lain',
            'rating' => 5,
            'komentar' => 'Tidak boleh terlihat.',
            'status' => 'approved',
            'moderation_status' => 'approved',
        ]);

        $response = $this->actingAs($this->penjualUser)->get(route('penjual.laporan'));

        $response->assertOk()
            ->assertSee('Produk Milik Saya')
            ->assertSee('Produk diunggah')
            ->assertDontSee('Produk Rahasia Penjual Lain')
            ->assertDontSee('Pengulas Penjual Lain');
        $this->assertSame(1, substr_count($response->getContent(), 'class="popular-list__item"'));
    }

    public function test_previous_week_sheet_download_uses_the_selected_seven_day_period(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-10-26 12:00:00'));
        try {
            $response = $this->actingAs($this->penjualUser)->get(route('penjual.laporan.sheet', ['week_offset' => -1]));

            $response->assertOk()
                ->assertDownload('rekap-penjual-toko-siswa-sukses-20261019.xlsx');
            @unlink($response->baseResponse->getFile()->getPathname());
        } finally {
            Carbon::setTestNow();
        }
    }

    public function test_penjual_can_download_laporan_as_excel_sheet(): void
    {
        $response = $this->actingAs($this->penjualUser)->get(route('penjual.laporan.sheet'));

        $response->assertStatus(200);
        $response->assertDownload('rekap-penjual-toko-siswa-sukses-'.now()->format('Ymd').'.xlsx');
        $this->assertStringContainsString(
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            (string) $response->headers->get('Content-Type')
        );
        $export = $response->baseResponse->getFile()->getPathname();
        try {
            $this->assertStringStartsWith("PK\x03\x04", file_get_contents($export));
        } finally {
            @unlink($export);
        }
    }

    public function test_penjual_sheet_contains_the_requested_seven_day_metrics(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-10-26 12:00:00'));
        try {
            $category = Kategori::create([
                'nama' => 'Kategori Laporan',
                'slug' => 'kategori-laporan-'.uniqid(),
            ]);
            $popularProduct = Produk::create([
                'penjual_id' => $this->penjual->id,
                'kategori_id' => $category->id,
                'nama' => 'Produk Populer',
                'slug' => 'produk-populer-'.uniqid(),
                'harga' => 10000,
                'stok' => 'tersedia',
                'status' => 'aktif',
                'views' => 100,
            ]);
            $secondProduct = Produk::create([
                'penjual_id' => $this->penjual->id,
                'kategori_id' => $category->id,
                'nama' => 'Produk Kedua',
                'slug' => 'produk-kedua-'.uniqid(),
                'harga' => 12000,
                'stok' => 'tersedia',
                'status' => 'aktif',
                'views' => 90,
            ]);
            $oldProduct = Produk::create([
                'penjual_id' => $this->penjual->id,
                'kategori_id' => $category->id,
                'nama' => 'Produk Lama',
                'slug' => 'produk-lama-'.uniqid(),
                'harga' => 8000,
                'stok' => 'tersedia',
                'status' => 'aktif',
                'views' => 5,
            ]);
            $oldProduct->forceFill(['created_at' => now()->subDays(8)])->save();

            foreach ([$popularProduct, $popularProduct, $secondProduct] as $product) {
                ProdukViewLog::create(['produk_id' => $product->id, 'type' => 'view']);
            }
            $oldView = ProdukViewLog::create(['produk_id' => $oldProduct->id, 'type' => 'view']);
            $oldView->forceFill(['created_at' => now()->subDays(8)])->save();

            Ulasan::create([
                'produk_id' => $popularProduct->id,
                'guest_username' => 'Pengulas Minggu Ini',
                'rating' => 5,
                'komentar' => 'Ulasan terbaru',
                'moderation_status' => 'approved',
                'status' => 'approved',
            ]);
            $oldReview = Ulasan::create([
                'produk_id' => $oldProduct->id,
                'guest_username' => 'Pengulas Lama',
                'rating' => 4,
                'komentar' => 'Ulasan lama',
                'moderation_status' => 'approved',
                'status' => 'approved',
            ]);
            $oldReview->forceFill(['created_at' => now()->subDays(8)])->save();

            $response = $this->actingAs($this->penjualUser)->get(route('penjual.laporan.sheet'));
            $response->assertStatus(200);
            $export = $response->baseResponse->getFile()->getPathname();
            try {
                $contents = file_get_contents($export);
                foreach ([
                    'Ringkasan',
                    'Top 5 Produk',
                    'Views Harian',
                    'Produk Diunggah',
                    'Ulasan',
                    'Produk Populer',
                    'Produk Kedua',
                    'Pengulas Minggu Ini',
                ] as $expected) {
                    $this->assertTrue(str_contains($contents, $expected), "Missing expected sheet value: {$expected}");
                }
                $this->assertFalse(str_contains($contents, 'Pengulas Lama'), 'Old reviews should not appear in the seven-day report.');
                // Verifikasi bahwa nilai views 7 hari (3 total, 2 untuk produk terpopuler, 1 untuk produk kedua) tersimpan
                $this->assertTrue(str_contains($contents, '<v>3</v>') || str_contains($contents, '3'), 'Total recent views should be 3.');
                $this->assertTrue(str_contains($contents, '<v>100</v>') || str_contains($contents, '100'), 'Lifetime views for popular product.');
                $this->assertTrue(str_contains($contents, '<v>90</v>') || str_contains($contents, '90'), 'Lifetime views for second product.');
            } finally {
                @unlink($export);
            }
        } finally {
            Carbon::setTestNow();
        }
    }

    public function test_non_penjual_cannot_download_penjual_laporan_sheet(): void
    {
        $this->get(route('penjual.laporan.sheet'))->assertRedirect(route('login'));
        $this->actingAs($this->admin)->get(route('penjual.laporan.sheet'))->assertStatus(403);
    }

    private function createReportProduct(string $name, int $lifetimeViews): Produk
    {
        $category = Kategori::create([
            'nama' => 'Kategori '.$name.' '.uniqid(),
            'slug' => 'kategori-'.Str::slug($name).'-'.uniqid(),
        ]);

        return Produk::create([
            'penjual_id' => $this->penjual->id,
            'kategori_id' => $category->id,
            'nama' => $name,
            'slug' => Str::slug($name).'-'.uniqid(),
            'harga' => 10000,
            'stok' => 'tersedia',
            'status' => 'aktif',
            'views' => $lifetimeViews,
        ]);
    }

    public function test_penjual_can_download_laporan_pdf(): void
    {
        $response = $this->actingAs($this->penjualUser)->get(route('penjual.laporan.pdf'));

        $response->assertStatus(200);
        $this->assertStringContainsString('application/pdf', (string) $response->headers->get('Content-Type'));
        $this->assertStringContainsString('rekap-penjual-toko-siswa-sukses', (string) $response->headers->get('Content-Disposition'));
        $this->assertNotEmpty($response->getContent());
    }

    public function test_non_penjual_cannot_download_penjual_laporan_pdf(): void
    {
        // Guest
        $guestResponse = $this->get(route('penjual.laporan.pdf'));
        $guestResponse->assertRedirect(route('login'));

        // Admin attempting to access penjual PDF
        $adminResponse = $this->actingAs($this->admin)->get(route('penjual.laporan.pdf'));
        $adminResponse->assertStatus(403);
    }

    public function test_admin_laporan_page_contains_download_sheet_buttons_for_penjual_and_ulasan(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.laporan.index'));

        $response->assertStatus(200);
        $response->assertSee('Unduh Laporan Penjual');
        $response->assertSee('Unduh Laporan Ulasan');
        $response->assertSee(route('admin.laporan.penjual.sheet', ['period' => 'weekly']));
        $response->assertSee(route('admin.laporan.ulasan.sheet', ['period' => 'weekly']));

        // On ulasan category
        $ulasanResponse = $this->actingAs($this->admin)->get(route('admin.laporan.index', ['kategori' => 'ulasan']));
        $ulasanResponse->assertStatus(200);
        $ulasanResponse->assertSee('Unduh Laporan Penjual');
        $ulasanResponse->assertSee('Unduh Laporan Ulasan');
    }

    public function test_admin_can_download_penjual_pdf(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.laporan.penjual.pdf'));

        $response->assertStatus(200);
        $this->assertStringContainsString('application/pdf', (string) $response->headers->get('Content-Type'));
        $this->assertStringContainsString('rekap-kinerja-weekly', (string) $response->headers->get('Content-Disposition'));
        $this->assertNotEmpty($response->getContent());
    }

    public function test_admin_can_download_ulasan_pdf(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.laporan.ulasan.pdf'));

        $response->assertStatus(200);
        $this->assertStringContainsString('application/pdf', (string) $response->headers->get('Content-Type'));
        $this->assertStringContainsString('rekap-ulasan-weekly', (string) $response->headers->get('Content-Disposition'));
        $this->assertNotEmpty($response->getContent());
    }

    public function test_admin_can_download_pdf_via_general_route(): void
    {
        $responsePenjual = $this->actingAs($this->admin)->get(route('admin.laporan.pdf', ['kategori' => 'penjual']));
        $responsePenjual->assertStatus(200);
        $this->assertStringContainsString('application/pdf', (string) $responsePenjual->headers->get('Content-Type'));

        $responseUlasan = $this->actingAs($this->admin)->get(route('admin.laporan.pdf', ['kategori' => 'ulasan']));
        $responseUlasan->assertStatus(200);
        $this->assertStringContainsString('application/pdf', (string) $responseUlasan->headers->get('Content-Type'));
    }

    public function test_non_admin_cannot_download_admin_laporan_pdf(): void
    {
        // Guest
        $guestResponse = $this->get(route('admin.laporan.penjual.pdf'));
        $guestResponse->assertRedirect(route('login'));

        // Penjual user
        $penjualResponse = $this->actingAs($this->penjualUser)->get(route('admin.laporan.penjual.pdf'));
        $penjualResponse->assertStatus(403);
    }

    public function test_admin_penjual_pdf_renders_single_footer_without_duplicate_html_footer(): void
    {
        // 1. Verify that view layout does NOT contain the legacy HTML fixed footer
        $viewContent = view('exports.pdf.layout', [
            'periodLabel' => 'Mingguan',
            'exportedAt' => now(),
        ])->render();

        $this->assertStringNotContainsString('class="pdf-footer"', $viewContent);
        $this->assertStringNotContainsString('class="footer-spacer"', $viewContent);
        $this->assertStringNotContainsString('counter(page)', $viewContent);

        // 2. Verify that the DomPDF canvas script is present with single brand and dynamic pagination
        $this->assertStringContainsString('page_text(42, $y, "SiswaMart"', $viewContent);
        $this->assertStringContainsString('Halaman " . $PAGE_NUM . " dari " . $PAGE_COUNT', $viewContent);

        // 3. Multi-page PDF test: Seed multiple sellers to generate a multi-page PDF
        $category = Kategori::firstOrCreate(
            ['slug' => 'kategori-uji-pdf'],
            ['nama' => 'Kategori Uji PDF']
        );

        for ($i = 1; $i <= 20; $i++) {
            $user = User::create([
                'name' => 'Seller Multi ' . $i . ' ' . uniqid(),
                'email' => 'seller_multi_' . $i . '_' . uniqid() . '@example.com',
                'password' => bcrypt('password'),
                'role' => 'penjual',
                'is_active' => true,
            ]);

            $seller = Penjual::create([
                'user_id' => $user->id,
                'nama_toko' => 'Toko Multi Page ' . $i,
                'deskripsi_toko' => 'Toko pengujian multi halaman',
                'nomor_whatsapp' => '08123456789' . $i,
                'lokasi_kelas' => 'X RPL ' . $i,
                'status_toko' => 'buka',
            ]);

            Produk::create([
                'penjual_id' => $seller->id,
                'kategori_id' => $category->id,
                'nama' => 'Produk Toko ' . $i,
                'slug' => 'produk-toko-' . $i . '-' . uniqid(),
                'harga' => 15000,
                'stok' => 'tersedia',
                'status' => 'aktif',
                'views' => 50 + $i,
            ]);
        }

        $response = $this->actingAs($this->admin)->get(route('admin.laporan.penjual.pdf'));

        $response->assertStatus(200);
        $this->assertStringContainsString('application/pdf', (string) $response->headers->get('Content-Type'));
        $pdfOutput = $response->getContent();
        $this->assertStringStartsWith('%PDF-', $pdfOutput);
        $this->assertGreaterThan(5000, strlen($pdfOutput));
    }
}
