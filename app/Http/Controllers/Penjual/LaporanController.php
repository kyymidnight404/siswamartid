<?php

namespace App\Http\Controllers\Penjual;

use App\Http\Controllers\Controller;
use App\Models\Penjual;
use App\Services\LaporanPenjualService;
use App\Services\PdfFilenameService;
use App\Support\XlsxWriter;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class LaporanController extends Controller
{
    public function __construct(
        protected LaporanPenjualService $laporanService,
        protected PdfFilenameService $filenameService
    ) {}

    /**
     * Tampilkan ringkasan tujuh hari untuk toko penjual.
     */
    public function index(Request $request): View|RedirectResponse
    {
        $penjual = auth()->user()->penjual;
        if (! $penjual) {
            abort(403, 'Akses khusus penjual.');
        }

        $weekOffset = (int) $request->query('week_offset', 0);
        if ($weekOffset > 0) {
            return redirect()->route('penjual.laporan');
        }

        $data = $this->laporanService->getSummary($penjual, $weekOffset);

        return view('penjual.laporan.index', $data);
    }

    /**
     * Unduh Rekapitulasi Mingguan Laporan Promosi Penjual dalam format Excel (xlsx).
     */
    public function sheet(Request $request, XlsxWriter $writer): BinaryFileResponse
    {
        $penjual = auth()->user()->penjual;
        if (! $penjual) {
            abort(403, 'Akses khusus penjual.');
        }

        $weekOffset = min(0, (int) $request->query('week_offset', 0));
        $data = $this->laporanService->getSummary($penjual, $weekOffset);

        $periodLabel = $data['periodLabel'];
        $periodStart = $data['periodStart'];
        $periodEnd = $data['periodEnd'];
        $exportedAt = $data['exportedAt'];

        // ── Sheet 1: Ringkasan (Metrik | Nilai | Keterangan) ──────────────────
        $ringkasanHeaders = ['Metrik', 'Nilai', 'Keterangan'];
        $ringkasanRows = [
            [
                'Periode Dari',
                $writer->formatDate($periodStart),
                'Batas awal periode inklusif (WIB)',
            ],
            [
                'Periode Sampai',
                $writer->formatDate($periodEnd),
                'Batas akhir periode inklusif (WIB)',
            ],
            [
                'Produk Diunggah',
                $writer->formatNumber($data['uploadedProductsCount']),
                'Total produk baru diunggah dalam 7 hari terakhir',
            ],
            [
                'Ulasan Baru',
                $writer->formatNumber($data['newReviewsCount']),
                'Total ulasan pembeli masuk dan disetujui dalam 7 hari terakhir',
            ],
            [
                'Total Views Semua Produk',
                $writer->formatNumber($data['totalViews']),
                $data['viewsComparison'],
            ],
            [
                'Views Produk Populer (Top 5)',
                $writer->formatNumber($data['popularViews']),
                $data['viewsFromPopularPercent'] . '% dari total seluruh views toko',
            ],
        ];
        $sheetRingkasan = [
            'name' => 'Ringkasan',
            'title' => 'Rekapitulasi Penjual — ' . $penjual->nama_toko,
            'metadata' => [
                'Toko: ' . $penjual->nama_toko . ($penjual->user ? ' (Pemilik: ' . $penjual->user->name . ')' : ''),
                'Periode: ' . $periodLabel . ' (7 hari terakhir)',
                'Diekspor: ' . $exportedAt->format('d/m/Y H:i') . ' WIB',
            ],
            'headers' => $ringkasanHeaders,
            'rows' => $ringkasanRows,
            'widths' => [32, 22, 50],
            'autoFilter' => false,
            'freezePane' => true,
        ];

        // ── Sheet 2: Top 5 Produk ─────────────────────────────────────────────
        $top5Headers = ['Peringkat', 'Produk', 'Kategori', 'Views 7 Hari', '% Dari Total', 'Views Sepanjang Waktu', 'Rating', 'Ulasan'];
        $top5Rows = [];
        foreach ($data['popularProducts'] as $index => $product) {
            $top5Rows[] = [
                [
                    'type' => 'center',
                    'value' => 'Top ' . ($index + 1),
                    'highlight' => ($index === 0 ? 'rank1' : null),
                ],
                $writer->sanitizeText($product->nama),
                $writer->sanitizeText($product->kategori?->nama ?? '-'),
                $writer->formatNumber((int) $product->period_views),
                $writer->formatPercent((float) $product->views_percent),
                $writer->formatNumber((int) $product->views),
                $writer->formatNumber($product->average_rating !== null ? round((float) $product->average_rating, 1) : 0),
                $writer->formatNumber((int) $product->period_reviews),
            ];
        }
        if (! empty($top5Rows)) {
            $top5Rows[] = [
                '__type' => 'total',
                'Total Top 5',
                '',
                '',
                $writer->formatNumber($data['popularViews']),
                $writer->formatPercent((float) $data['viewsFromPopularPercent']),
                $writer->formatNumber((int) $data['popularProducts']->sum('views')),
                $writer->formatNumber($data['popularProducts']->avg('average_rating') !== null ? round((float) $data['popularProducts']->avg('average_rating'), 1) : 0),
                $writer->formatNumber((int) $data['popularProducts']->sum('period_reviews')),
            ];
        }
        $sheetTop5 = [
            'name' => 'Top 5 Produk',
            'title' => 'Top 5 Produk Terpopuler — ' . $penjual->nama_toko,
            'metadata' => [
                'Periode: ' . $periodLabel,
                'Diekspor: ' . $exportedAt->format('d/m/Y H:i') . ' WIB',
            ],
            'headers' => $top5Headers,
            'rows' => $top5Rows,
            'widths' => [14, 32, 24, 16, 16, 24, 12, 12],
            'autoFilter' => true,
            'freezePane' => true,
        ];

        // ── Sheet 3: Views Harian (+ Native Chart) ────────────────────────────
        $viewsHarianHeaders = ['Tanggal', 'Hari', 'Views'];
        $viewsHarianRows = [];
        foreach ($data['dailyViews'] as $day) {
            $viewsHarianRows[] = [
                $writer->formatDate($day['date']),
                $day['day_name'],
                $writer->formatNumber((int) $day['count']),
            ];
        }
        $viewsHarianRows[] = [
            '__type' => 'total',
            'Total 7 Hari',
            '',
            $writer->formatNumber($data['totalViews']),
        ];
        $sheetViewsHarian = [
            'name' => 'Views Harian',
            'title' => 'Tinjauan Views Harian — ' . $penjual->nama_toko,
            'metadata' => [
                'Periode: ' . $periodLabel,
                'Diekspor: ' . $exportedAt->format('d/m/Y H:i') . ' WIB',
            ],
            'headers' => $viewsHarianHeaders,
            'rows' => $viewsHarianRows,
            'widths' => [18, 20, 16],
            'autoFilter' => true,
            'freezePane' => true,
            'chart' => [
                'title' => 'Grafik Views Harian',
                'categories' => "'Views Harian'!\$B\$5:\$B\$11",
                'values' => "'Views Harian'!\$C\$5:\$C\$11",
                'count' => 7,
            ],
        ];

        // ── Sheet 4: Produk Diunggah ──────────────────────────────────────────
        $uploadedHeaders = ['Tanggal Unggah', 'Produk', 'Kategori', 'Harga', 'Status'];
        $uploadedRows = [];
        foreach ($data['uploadedProducts'] as $prod) {
            $uploadedRows[] = [
                $writer->formatDate($prod->created_at),
                $writer->sanitizeText($prod->nama),
                $writer->sanitizeText($prod->kategori?->nama ?? '-'),
                $writer->formatRupiah((float) $prod->harga),
                ucfirst($prod->status),
            ];
        }
        $sheetUploaded = [
            'name' => 'Produk Diunggah',
            'title' => 'Daftar Produk Diunggah 7 Hari Terakhir — ' . $penjual->nama_toko,
            'metadata' => [
                'Periode: ' . $periodLabel,
                'Diekspor: ' . $exportedAt->format('d/m/Y H:i') . ' WIB',
            ],
            'headers' => $uploadedHeaders,
            'rows' => $uploadedRows,
            'widths' => [20, 34, 24, 20, 14],
            'autoFilter' => true,
            'freezePane' => true,
        ];

        // ── Sheet 5: Ulasan ───────────────────────────────────────────────────
        $reviewsHeaders = ['Tanggal', 'Produk', 'Rating', 'Komentar'];
        $reviewsRows = [];
        foreach ($data['recentReviews'] as $rev) {
            $reviewsRows[] = [
                $writer->formatDate($rev->created_at),
                $writer->sanitizeText($rev->produk?->nama ?? '-'),
                $writer->formatNumber((int) $rev->rating),
                [
                    'type' => 'wrap',
                    'value' => $writer->sanitizeText(trim(($rev->guest_username ?: 'Pengunjung') . ' (' . $rev->rating . '/5): ' . ($rev->public_content ?: 'Tidak ada teks ulasan.'))),
                ],
            ];
        }
        $sheetReviews = [
            'name' => 'Ulasan',
            'title' => 'Daftar Ulasan Masuk 7 Hari Terakhir — ' . $penjual->nama_toko,
            'metadata' => [
                'Periode: ' . $periodLabel,
                'Diekspor: ' . $exportedAt->format('d/m/Y H:i') . ' WIB',
            ],
            'headers' => $reviewsHeaders,
            'rows' => $reviewsRows,
            'widths' => [18, 30, 12, 60],
            'autoFilter' => true,
            'freezePane' => true,
        ];

        $safeStoreName = Str::slug($penjual->nama_toko ?: 'toko');
        $filename = 'rekap-penjual-' . $safeStoreName . '-' . $periodEnd->format('Ymd') . '.xlsx';
        $path = tempnam(storage_path('app'), 'rekap-penjual-');
        if ($path === false) {
            abort(500, 'File ekspor tidak dapat dibuat.');
        }

        $writer->writeWorkbook($path, [
            $sheetRingkasan,
            $sheetTop5,
            $sheetViewsHarian,
            $sheetUploaded,
            $sheetReviews,
        ]);

        return response()->download($path, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }

    /**
     * Unduh PDF Rekapitulasi Mingguan Laporan Promosi Penjual.
     */
    public function pdf(Request $request): Response
    {
        $penjual = auth()->user()->penjual;
        if (! $penjual) {
            abort(403, 'Akses khusus penjual.');
        }

        $weekOffset = min(0, (int) $request->query('week_offset', 0));
        $data = $this->laporanService->getSummary($penjual, $weekOffset);

        $type = strtolower((string) $request->query('type', $request->query('kategori', '')));
        $filename = in_array($type, ['rekap-produk', 'produk'], true)
            ? $this->filenameService->rekapProduk($penjual, $data['exportedAt'] ?? now('Asia/Jakarta'))
            : $this->filenameService->rekapPenjual($penjual, $data['exportedAt'] ?? now('Asia/Jakarta'));

        return Pdf::setOption(['isPhpEnabled' => true])
            ->loadView('penjual.laporan.pdf', $data)
            ->setPaper('a4', 'portrait')
            ->download($filename);
    }
}
