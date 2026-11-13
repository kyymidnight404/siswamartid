<?php

namespace Tests\Feature;

use App\Models\Kategori;
use App\Models\Penjual;
use App\Models\Produk;
use App\Models\User;
use App\Services\PdfFilenameService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class PdfFilenameStandardizationTest extends TestCase
{
    use DatabaseTransactions;

    private User $admin;

    private User $penjualUser;

    private Penjual $penjual;

    private PdfFilenameService $filenameService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->filenameService = new PdfFilenameService();

        $this->admin = User::create([
            'name' => 'Admin Standardization Test',
            'email' => 'admin_standard_'.uniqid().'@example.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'is_active' => true,
        ]);

        $this->penjualUser = User::create([
            'name' => 'Penjual Standardization Test',
            'email' => 'penjual_standard_'.uniqid().'@example.com',
            'password' => bcrypt('password'),
            'role' => 'penjual',
            'is_active' => true,
        ]);

        $this->penjual = Penjual::create([
            'user_id' => $this->penjualUser->id,
            'nama_toko' => 'The Noble Brewer',
            'deskripsi_toko' => 'Toko pengujian standardisasi',
            'nomor_whatsapp' => '081234567890',
            'lokasi_kelas' => 'XII RPL 2',
            'status_toko' => 'buka',
        ]);
    }

    /**
     * 1. Test unit format Service untuk seluruh 6 kategori:
     * A. Detail Penjual
     * B. Rekap Penjual
     * C. Rekap Ulasan (weekly & monthly)
     * D. Rekap Kinerja Mingguan
     * E. Rekap Kinerja Bulanan
     * F. Rekap Produk
     */
    public function test_service_generates_exact_filenames_according_to_specification(): void
    {
        $fixedDate = Carbon::parse('2026-11-06 14:30:00', 'Asia/Jakarta');

        // A. Laporan Detail Penjual: detail-penjual-{slug-toko}-{tanggal}.pdf
        $this->assertSame(
            'detail-penjual-edmunds-hearth-20261106.pdf',
            $this->filenameService->detailPenjual("Edmund's Hearth", $fixedDate)
        );

        // B. Rekap Penjual: rekap-penjual-{slug-toko}-{tanggal}.pdf
        $this->assertSame(
            'rekap-penjual-the-noble-brewer-20261106.pdf',
            $this->filenameService->rekapPenjual('The Noble Brewer', $fixedDate)
        );

        // C. Rekap Ulasan (Weekly): rekap-ulasan-{periode}-{tanggal}.pdf
        $this->assertSame(
            'rekap-ulasan-weekly-20261106.pdf',
            $this->filenameService->rekapUlasan('weekly', $fixedDate)
        );

        // C. Rekap Ulasan (Monthly): rekap-ulasan-{periode}-{tanggal}.pdf
        $this->assertSame(
            'rekap-ulasan-monthly-20261106.pdf',
            $this->filenameService->rekapUlasan('monthly', $fixedDate)
        );

        // D. Rekap Kinerja Mingguan: rekap-kinerja-weekly-{tanggal}.pdf
        $this->assertSame(
            'rekap-kinerja-weekly-20261106.pdf',
            $this->filenameService->rekapKinerja('weekly', $fixedDate)
        );

        // E. Rekap Kinerja Bulanan: rekap-kinerja-monthly-{tahunbulan}.pdf
        $this->assertSame(
            'rekap-kinerja-monthly-202611.pdf',
            $this->filenameService->rekapKinerja('monthly', $fixedDate)
        );

        // F. Rekap Produk: rekap-produk-{slug-toko}-{tanggal}.pdf
        $this->assertSame(
            'rekap-produk-edmunds-hearth-20261106.pdf',
            $this->filenameService->rekapProduk("Edmund's Hearth", $fixedDate)
        );
    }

    /**
     * 2. Test penanganan slug toko dengan spasi, karakter khusus, dan batas panjang.
     */
    public function test_slugify_store_handles_special_characters_and_whitespace(): void
    {
        $this->assertSame('the-noble-brewer', $this->filenameService->slugifyStore('The Noble Brewer'));
        $this->assertSame('edmunds-hearth', $this->filenameService->slugifyStore("Edmund's Hearth"));
        $this->assertSame('toko-kafe-100-halal', $this->filenameService->slugifyStore('Toko & Kafe (100% Halal!)'));
        $this->assertSame('toko', $this->filenameService->slugifyStore('   '));
        $this->assertSame('toko', $this->filenameService->slugifyStore(null));

        // Long name truncation (<= 50 chars)
        $longName = str_repeat('tokotoko-', 15);
        $slug = $this->filenameService->slugifyStore($longName);
        $this->assertLessThanOrEqual(50, strlen($slug));
        $this->assertFalse(str_ends_with($slug, '-'));
    }

    /**
     * 3. Test HTTP: Detail Penjual (Admin)
     * Format: detail-penjual-{slug-toko}-{tanggal}.pdf
     */
    public function test_admin_detail_penjual_pdf_filename(): void
    {
        $today = now('Asia/Jakarta')->format('Ymd');
        $response = $this->actingAs($this->admin)->get(route('admin.laporan.penjual.detail.pdf', $this->penjual));

        $response->assertOk();
        $disposition = (string) $response->headers->get('Content-Disposition');
        $this->assertStringContainsString("detail-penjual-the-noble-brewer-{$today}.pdf", $disposition);
    }

    /**
     * 4. Test HTTP: Rekap Produk via Detail Penjual (Admin)
     * Format: rekap-produk-{slug-toko}-{tanggal}.pdf
     */
    public function test_admin_rekap_produk_pdf_filename(): void
    {
        $today = now('Asia/Jakarta')->format('Ymd');
        $response = $this->actingAs($this->admin)->get(route('admin.laporan.penjual.detail.pdf', [
            'penjual' => $this->penjual->id,
            'type' => 'rekap-produk',
        ]));

        $response->assertOk();
        $disposition = (string) $response->headers->get('Content-Disposition');
        $this->assertStringContainsString("rekap-produk-the-noble-brewer-{$today}.pdf", $disposition);
    }

    /**
     * 5. Test HTTP: Rekap Penjual Mingguan (Penjual)
     * Format: rekap-penjual-{slug-toko}-{tanggal}.pdf
     */
    public function test_penjual_rekap_penjual_pdf_filename(): void
    {
        $today = now('Asia/Jakarta')->format('Ymd');
        $response = $this->actingAs($this->penjualUser)->get(route('penjual.laporan.pdf'));

        $response->assertOk();
        $disposition = (string) $response->headers->get('Content-Disposition');
        $this->assertStringContainsString("rekap-penjual-the-noble-brewer-{$today}.pdf", $disposition);
    }

    /**
     * 6. Test HTTP: Rekap Produk (Penjual)
     * Format: rekap-produk-{slug-toko}-{tanggal}.pdf
     */
    public function test_penjual_rekap_produk_pdf_filename(): void
    {
        $today = now('Asia/Jakarta')->format('Ymd');
        $response = $this->actingAs($this->penjualUser)->get(route('penjual.laporan.pdf', ['type' => 'rekap-produk']));

        $response->assertOk();
        $disposition = (string) $response->headers->get('Content-Disposition');
        $this->assertStringContainsString("rekap-produk-the-noble-brewer-{$today}.pdf", $disposition);
    }

    /**
     * 7. Test HTTP: Rekap Ulasan Mingguan (Admin)
     * Format: rekap-ulasan-weekly-{tanggal}.pdf
     */
    public function test_admin_rekap_ulasan_weekly_pdf_filename(): void
    {
        $today = now('Asia/Jakarta')->format('Ymd');
        $response = $this->actingAs($this->admin)->get(route('admin.laporan.ulasan.pdf', ['period' => 'weekly']));

        $response->assertOk();
        $disposition = (string) $response->headers->get('Content-Disposition');
        $this->assertStringContainsString("rekap-ulasan-weekly-{$today}.pdf", $disposition);
    }

    /**
     * 8. Test HTTP: Rekap Ulasan Bulanan (Admin)
     * Format: rekap-ulasan-monthly-{tanggal}.pdf
     */
    public function test_admin_rekap_ulasan_monthly_pdf_filename(): void
    {
        $today = now('Asia/Jakarta')->format('Ymd');
        $response = $this->actingAs($this->admin)->get(route('admin.laporan.ulasan.pdf', ['period' => 'monthly']));

        $response->assertOk();
        $disposition = (string) $response->headers->get('Content-Disposition');
        $this->assertStringContainsString("rekap-ulasan-monthly-{$today}.pdf", $disposition);
    }

    /**
     * 9. Test HTTP: Rekap Kinerja Mingguan (Admin)
     * Format: rekap-kinerja-weekly-{tanggal}.pdf
     */
    public function test_admin_rekap_kinerja_weekly_pdf_filename(): void
    {
        $today = now('Asia/Jakarta')->format('Ymd');
        $response = $this->actingAs($this->admin)->get(route('admin.laporan.penjual.pdf', ['period' => 'weekly']));

        $response->assertOk();
        $disposition = (string) $response->headers->get('Content-Disposition');
        $this->assertStringContainsString("rekap-kinerja-weekly-{$today}.pdf", $disposition);
    }

    /**
     * 10. Test HTTP: Rekap Kinerja Bulanan (Admin)
     * Format: rekap-kinerja-monthly-{tahunbulan}.pdf
     */
    public function test_admin_rekap_kinerja_monthly_pdf_filename(): void
    {
        $yearMonth = now('Asia/Jakarta')->format('Ym');
        $response = $this->actingAs($this->admin)->get(route('admin.laporan.penjual.pdf', ['period' => 'monthly']));

        $response->assertOk();
        $disposition = (string) $response->headers->get('Content-Disposition');
        $this->assertStringContainsString("rekap-kinerja-monthly-{$yearMonth}.pdf", $disposition);
    }
}
