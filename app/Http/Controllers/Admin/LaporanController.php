<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Kategori;
use App\Models\Penjual;
use App\Models\Produk;
use App\Models\Ulasan;
use App\Services\LaporanAdminService;
use App\Services\PdfFilenameService;
use App\Support\XlsxWriter;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class LaporanController extends Controller
{
    public function __construct(
        protected LaporanAdminService $adminService,
        protected PdfFilenameService $filenameService
    ) {}

    public function index(Request $request): View
    {
        $request->validate(['kategori' => ['nullable', 'in:penjual,ulasan']]);
        $kategoriLaporan = $request->query('kategori', 'penjual');
        $period = $this->selectedPeriod($request);
        [$start, $end, $periodLabel] = $this->adminService->getPeriodRange($period);

        $sellerReport = null;
        $reviewReport = null;

        if ($kategoriLaporan === 'penjual') {
            $sellerReport = $this->adminService->getSellerPerformance($start, $end, $period);
        } else {
            $reviewReport = $this->adminService->getReviewPerformance($start, $end);
        }

        return view('admin.laporan.index', compact(
            'kategoriLaporan', 'period', 'periodLabel', 'start', 'end', 'sellerReport', 'reviewReport'
        ));
    }

    /** Keep existing report URLs working while opening the consolidated report page. */
    public function produk(Request $request): RedirectResponse
    {
        return redirect()->route('admin.laporan.index', [
            'kategori' => 'penjual',
            'period' => $request->query('period', 'weekly'),
        ]);
    }

    /**
     * Laporan Agregat Ulasan SiswaMart.
     */
    public function ulasan(Request $request): RedirectResponse
    {
        return redirect()->route('admin.laporan.index', [
            'kategori' => 'ulasan',
            'period' => $request->query('period', 'weekly'),
        ]);
    }

    public function ulasanPdf(Request $request): Response
    {
        $period = $this->selectedPeriod($request);
        [$start, $end, $periodLabel] = $this->adminService->getPeriodRange($period);
        $reviewReport = $this->adminService->getReviewPerformance($start, $end);

        $filename = $this->filenameService->rekapUlasan($period, $reviewReport['exportedAt'] ?? now('Asia/Jakarta'));

        return Pdf::setOption(['isPhpEnabled' => true])
            ->loadView('admin.laporan.ulasan-pdf', [
                'period' => $period,
                'periodLabel' => $periodLabel,
                'start' => $start,
                'end' => $end,
                'reviews' => $reviewReport['reviews'],
                'averageRating' => $reviewReport['average'],
                'ratingDistribution' => $reviewReport['distribution'],
                'exportedAt' => $reviewReport['exportedAt'],
            ])
            ->setPaper('a4', 'portrait')
            ->download($filename);
    }

    public function penjualPdf(Request $request): Response
    {
        $period = $this->selectedPeriod($request);
        [$start, $end, $periodLabel] = $this->adminService->getPeriodRange($period);
        $sellerReport = $this->adminService->getSellerPerformance($start, $end, $period);

        $filename = $this->filenameService->rekapKinerja($period, $sellerReport['exportedAt'] ?? now('Asia/Jakarta'));

        return Pdf::setOption(['isPhpEnabled' => true])
            ->loadView('admin.laporan.penjual-pdf', [
                'period' => $period,
                'periodLabel' => $periodLabel,
                'start' => $start,
                'end' => $end,
                'sellerReport' => $sellerReport,
                'exportedAt' => $sellerReport['exportedAt'],
            ])
            ->setPaper('a4', 'portrait')
            ->download($filename);
    }

    public function penjualSheet(Request $request, XlsxWriter $writer): BinaryFileResponse
    {
        $period = $this->selectedPeriod($request);
        [$start, $end, $periodLabel] = $this->adminService->getPeriodRange($period);
        $sellerReport = $this->adminService->getSellerPerformance($start, $end, $period);
        $exportedAt = $sellerReport['exportedAt'];

        // ── Sheet 1: Ringkasan ────────────────────────────────────────────────
        $summaryHeaders = ['Metrik', 'Nilai', 'Keterangan'];
        $summaryRows = [
            ['Periode Dari', $writer->formatDate($start), 'Batas awal periode inklusif (WIB)'],
            ['Periode Sampai', $writer->formatDate($end), 'Batas akhir periode inklusif (WIB)'],
            ['Total Penjual Terdaftar', $writer->formatNumber($sellerReport['total_sellers']), 'Jumlah akun toko penjual aktif & nonaktif'],
            ['Produk Ditambahkan', $writer->formatNumber($sellerReport['products_added']), 'Produk baru diupload selama periode ' . $periodLabel],
            ['Produk Dihapus', $writer->formatNumber($sellerReport['products_deleted']), 'Produk dihapus selama periode ' . $periodLabel],
            ['Permintaan Edit', $writer->formatNumber($sellerReport['edits_requested']), 'Pengajuan revisi data produk'],
            ['Total Kunjungan (Views)', $writer->formatNumber($sellerReport['views']), 'Akumulasi seluruh halaman produk dilihat'],
            ['Ulasan Baru Diterbitkan', $writer->formatNumber($sellerReport['reviews']), 'Ulasan pembeli berhasil disetujui'],
            ['Rata-rata Rating Keseluruhan', $writer->formatNumber($sellerReport['avg_rating_semua'] ?? 0), 'Skala kepuasan 1 sampai 5 bintang'],
        ];
        $sheetRingkasan = [
            'name' => 'Ringkasan',
            'title' => 'Rekap Kinerja Penjual SiswaMart — ' . $periodLabel,
            'metadata' => [
                'Periode: ' . $start->format('d/m/Y') . ' – ' . $end->format('d/m/Y') . ' (' . ucfirst($period) . ')',
                'Diekspor: ' . $exportedAt->format('d/m/Y H:i') . ' WIB',
            ],
            'headers' => $summaryHeaders,
            'rows' => $summaryRows,
            'widths' => [30, 20, 50],
            'autoFilter' => false,
            'freezePane' => true,
        ];

        // ── Sheet 2: Kinerja Penjual ──────────────────────────────────────────
        $sellerHeaders = [
            'Nama Penjual', 'Nama Toko', 'Total Produk', 'Produk Aktif', 'Menunggu Moderasi',
            'Produk Ditambahkan', 'Produk Dihapus', 'Permintaan Edit', 'Dilihat', 'Ulasan Baru', 'Rating Rata-rata',
        ];
        $sellerRows = $sellerReport['sellers']->map(fn (Penjual $seller) => [
            $writer->sanitizeText($seller->user?->name ?? ''),
            $writer->sanitizeText($seller->nama_toko ?? ''),
            $writer->formatNumber((int) $seller->produk_count),
            $writer->formatNumber((int) $seller->produk_aktif_count),
            $writer->formatNumber((int) $seller->produk_pending_count),
            $writer->formatNumber((int) $seller->period_added),
            $writer->formatNumber((int) $seller->period_deleted),
            $writer->formatNumber((int) $seller->period_edits_requested),
            $writer->formatNumber((int) $seller->period_views),
            $writer->formatNumber((int) $seller->period_reviews),
            $seller->avg_rating === null ? '-' : $writer->formatNumber((float) $seller->avg_rating),
        ])->all();

        $sheetKinerja = [
            'name' => 'Kinerja Penjual',
            'title' => 'Tabel Kinerja Penjual — ' . $periodLabel,
            'metadata' => [
                'Periode: ' . $start->format('d/m/Y') . ' – ' . $end->format('d/m/Y'),
                'Diekspor: ' . $exportedAt->format('d/m/Y H:i') . ' WIB',
            ],
            'headers' => $sellerHeaders,
            'rows' => $sellerRows,
            'widths' => [24, 26, 14, 14, 18, 18, 16, 16, 14, 14, 16],
            'autoFilter' => true,
            'freezePane' => true,
        ];

        // ── Sheet 3: Produk Terpopuler ────────────────────────────────────────
        $topProdHeaders = ['Peringkat', 'Nama Produk', 'Penjual', 'Dilihat', 'Ulasan'];
        $topProdRows = [];
        foreach ($sellerReport['top_products'] as $idx => $prod) {
            $topProdRows[] = [
                ['type' => 'center', 'value' => '#' . ($idx + 1), 'highlight' => ($idx === 0 ? 'rank1' : null)],
                $writer->sanitizeText($prod->nama),
                $writer->sanitizeText($prod->penjual?->nama_toko ?? 'Penjual tidak tersedia'),
                $writer->formatNumber((int) $prod->period_views),
                $writer->formatNumber((int) $prod->period_reviews),
            ];
        }
        $sheetTopProd = [
            'name' => 'Produk Populer',
            'title' => 'Top 10 Produk Paling Banyak Dilihat — ' . $periodLabel,
            'metadata' => [
                'Periode: ' . $start->format('d/m/Y') . ' – ' . $end->format('d/m/Y'),
                'Diekspor: ' . $exportedAt->format('d/m/Y H:i') . ' WIB',
            ],
            'headers' => $topProdHeaders,
            'rows' => $topProdRows,
            'widths' => [12, 35, 26, 16, 14],
            'autoFilter' => true,
            'freezePane' => true,
        ];

        // ── Sheet 4: Aktivitas Produk ─────────────────────────────────────────
        $eventHeaders = ['Waktu', 'Penjual', 'Aktivitas'];
        $eventRows = [];
        foreach ($sellerReport['events'] as $event) {
            $eventRows[] = [
                $writer->formatDate($event['created_at']),
                $writer->sanitizeText($event['seller_name']),
                $writer->sanitizeText($event['description']),
            ];
        }
        $sheetEvents = [
            'name' => 'Aktivitas',
            'title' => 'Riwayat Aktivitas Produk — ' . $periodLabel,
            'metadata' => [
                'Periode: ' . $start->format('d/m/Y') . ' – ' . $end->format('d/m/Y'),
                'Diekspor: ' . $exportedAt->format('d/m/Y H:i') . ' WIB',
            ],
            'headers' => $eventHeaders,
            'rows' => $eventRows,
            'widths' => [20, 28, 48],
            'autoFilter' => true,
            'freezePane' => true,
        ];

        $path = tempnam(storage_path('app'), 'rekap-kinerja-penjual-');
        if ($path === false) {
            abort(500, 'File ekspor tidak dapat dibuat.');
        }

        $writer->writeWorkbook($path, [
            $sheetRingkasan,
            $sheetKinerja,
            $sheetTopProd,
            $sheetEvents,
        ]);

        return response()->download($path, 'rekap-kinerja-penjual-' . $period . '-' . $start->format('Ymd') . '.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }

    public function ulasanSheet(Request $request, XlsxWriter $writer): BinaryFileResponse
    {
        $period = $this->selectedPeriod($request);
        [$start, $end, $periodLabel] = $this->adminService->getPeriodRange($period);
        $reviewReport = $this->adminService->getReviewPerformance($start, $end);
        $exportedAt = $reviewReport['exportedAt'];

        // ── Sheet 1: Ringkasan Ulasan ─────────────────────────────────────────
        $summaryHeaders = ['Metrik', 'Nilai', 'Keterangan'];
        $summaryRows = [
            ['Periode Dari', $writer->formatDate($start), 'Batas awal periode inklusif (WIB)'],
            ['Periode Sampai', $writer->formatDate($end), 'Batas akhir periode inklusif (WIB)'],
            ['Total Ulasan Diterbitkan', $writer->formatNumber($reviewReport['total']), 'Ulasan pembeli berhasil disetujui'],
            ['Rating Rata-rata', $writer->formatNumber($reviewReport['average']), 'Skala kepuasan 1 sampai 5 bintang'],
        ];
        foreach ($reviewReport['distribution'] as $stars => $cnt) {
            $summaryRows[] = [
                $stars . ' Bintang',
                $writer->formatNumber($cnt),
                'Distribusi penilaian ulasan ' . $stars . ' bintang',
            ];
        }
        $sheetRingkasan = [
            'name' => 'Ringkasan',
            'title' => 'Rekapitulasi Ulasan SiswaMart — ' . $periodLabel,
            'metadata' => [
                'Periode: ' . $start->format('d/m/Y') . ' – ' . $end->format('d/m/Y') . ' (' . ucfirst($period) . ')',
                'Diekspor: ' . $exportedAt->format('d/m/Y H:i') . ' WIB',
            ],
            'headers' => $summaryHeaders,
            'rows' => $summaryRows,
            'widths' => [28, 18, 48],
            'autoFilter' => false,
            'freezePane' => true,
        ];

        // ── Sheet 2: Daftar Ulasan ────────────────────────────────────────────
        $reviewHeaders = ['Tanggal', 'Pengulas', 'Rating', 'Produk', 'Penjual', 'Ulasan'];
        $reviewRows = $reviewReport['reviews']->map(fn ($review) => [
            $writer->formatDate($review->created_at),
            $writer->sanitizeText($review->guest_username ?: 'Pengunjung'),
            $writer->formatNumber((int) $review->rating),
            $writer->sanitizeText($review->produk?->nama ?? 'Produk telah dihapus'),
            $writer->sanitizeText($review->produk?->penjual?->nama_toko ?? 'Penjual tidak tersedia'),
            ['type' => 'wrap', 'value' => $writer->sanitizeText($review->public_content ?: 'Tidak ada teks ulasan.')],
        ])->all();

        $sheetUlasan = [
            'name' => 'Daftar Ulasan',
            'title' => 'Daftar Ulasan Publik — ' . $periodLabel,
            'metadata' => [
                'Periode: ' . $start->format('d/m/Y') . ' – ' . $end->format('d/m/Y'),
                'Diekspor: ' . $exportedAt->format('d/m/Y H:i') . ' WIB',
            ],
            'headers' => $reviewHeaders,
            'rows' => $reviewRows,
            'widths' => [18, 22, 10, 28, 24, 60],
            'autoFilter' => true,
            'freezePane' => true,
        ];

        $path = tempnam(storage_path('app'), 'rekap-ulasan-');
        if ($path === false) {
            abort(500, 'File ekspor tidak dapat dibuat.');
        }

        $writer->writeWorkbook($path, [
            $sheetRingkasan,
            $sheetUlasan,
        ]);

        return response()->download($path, 'rekap-ulasan-' . $period . '-' . $start->format('Ymd') . '.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }

    /**
     * Ekspor rekapitulasi data master akun penjual untuk admin (kompatibilitas route admin.penjual.export).
     */
    public function penjualExport(Request $request, XlsxWriter $writer): BinaryFileResponse
    {
        $sellers = Penjual::with('user')
            ->withCount('produk')
            ->withCount(['produk as produk_aktif_count' => fn ($q) => $q->where('status', 'aktif')])
            ->latest()
            ->get();

        $headers = [
            'Nama Penjual', 'NISN', 'Nama Toko', 'Lokasi / Kelas', 'Nomor WhatsApp',
            'Status Toko', 'Status Akun', 'Total Produk', 'Produk Aktif', 'Tanggal Bergabung',
        ];

        $rows = $sellers->map(fn (Penjual $seller) => [
            $writer->sanitizeText($seller->user?->name ?? ''),
            $writer->sanitizeText($seller->nisn ?? '-'),
            $writer->sanitizeText($seller->nama_toko ?? ''),
            $writer->sanitizeText($seller->lokasi_kelas ?? '-'),
            $writer->sanitizeText($seller->nomor_whatsapp ?? '-'),
            ucfirst($seller->status_toko ?? 'tutup'),
            ($seller->user?->is_active ?? false) ? 'Aktif' : 'Nonaktif',
            $writer->formatNumber((int) $seller->produk_count),
            $writer->formatNumber((int) $seller->produk_aktif_count),
            $writer->formatDate($seller->created_at),
        ])->all();

        $sheet = [
            'name' => 'Data Penjual',
            'title' => 'Rekapitulasi Penjual SiswaMart',
            'metadata' => [
                'Waktu Ekspor: ' . now('Asia/Jakarta')->format('d/m/Y H:i') . ' WIB',
                'Dicetak oleh Admin Resmi SiswaMart',
            ],
            'headers' => $headers,
            'rows' => $rows,
            'widths' => [24, 16, 26, 18, 18, 14, 14, 14, 14, 18],
            'autoFilter' => true,
            'freezePane' => true,
        ];

        $filename = 'rekapitulasi-penjual-siswamart-' . now('Asia/Jakarta')->format('Y-m-d') . '.xlsx';
        $path = tempnam(storage_path('app'), 'rekap-master-penjual-');
        if ($path === false) {
            abort(500, 'File ekspor tidak dapat dibuat.');
        }

        $writer->writeWorkbook($path, [$sheet]);

        return response()->download($path, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }

    /**
     * Laporan Detail Statistik Penjual.
     */
    public function penjualDetail(Request $request, Penjual $penjual): View
    {
        $penjual->load(['user']);

        // Query dasar produk penjual yang berstatus approved / disetujui Admin
        $approvedBase = $penjual->produk()
            ->where(function ($q) {
                $q->whereNotNull('approved_at')
                  ->orWhere('status', 'aktif');
            });

        // 1. Total produk yang telah disetujui Admin
        $totalApprovedProducts = (clone $approvedBase)->count();

        // 2. ID produk approved untuk agregasi ulasan & rating
        $approvedProductIds = (clone $approvedBase)->pluck('id');

        // 3. Total ulasan/rating yang diterima
        $totalUlasan = Ulasan::whereIn('produk_id', $approvedProductIds)
            ->publicVisible()
            ->count();

        // 4. Rata-rata rating seluruh produk approved
        $avgRatingAll = Ulasan::whereIn('produk_id', $approvedProductIds)
            ->publicVisible()
            ->avg('rating');
        $avgRatingAll = $avgRatingAll !== null ? round((float) $avgRatingAll, 1) : null;

        // Filter dan Pencarian:
        $search = trim((string) ($request->query('q') ?? $request->query('search')));
        $kategoriId = $request->query('kategori_id');
        $uploadFrom = $request->query('upload_from');
        $uploadTo = $request->query('upload_to');
        $approvalFrom = $request->query('approval_from');
        $approvalTo = $request->query('approval_to');
        $sort = $request->query('sort', 'terbaru');
        $perPage = in_array((int) $request->query('per_page', 10), [10, 25, 50], true)
            ? (int) $request->query('per_page', 10)
            : 10;

        $productsQuery = (clone $approvedBase)
            ->with([
                'kategori',
                'ulasan' => fn ($q) => $q->publicVisible()->latest(),
            ])
            ->withAvg(['ulasan as avg_rating' => fn ($q) => $q->publicVisible()], 'rating')
            ->withCount(['ulasan as total_ulasan' => fn ($q) => $q->publicVisible()]);

        if ($search !== '') {
            $productsQuery->where('nama', 'like', "%{$search}%");
        }

        if ($kategoriId) {
            $productsQuery->where('kategori_id', $kategoriId);
        }

        if ($uploadFrom) {
            $productsQuery->whereDate('created_at', '>=', $uploadFrom);
        }
        if ($uploadTo) {
            $productsQuery->whereDate('created_at', '<=', $uploadTo);
        }

        if ($approvalFrom) {
            $productsQuery->whereDate('approved_at', '>=', $approvalFrom);
        }
        if ($approvalTo) {
            $productsQuery->whereDate('approved_at', '<=', $approvalTo);
        }

        switch ($sort) {
            case 'approval_terbaru':
                $productsQuery->orderByDesc('approved_at')->orderByDesc('id');
                break;
            case 'rating_tertinggi':
                $productsQuery->orderByDesc('avg_rating')->orderByDesc('total_ulasan')->orderByDesc('id');
                break;
            case 'rating_terendah':
                $productsQuery->orderBy('avg_rating')->orderBy('total_ulasan')->orderByDesc('id');
                break;
            case 'terlama':
                $productsQuery->orderBy('created_at')->orderBy('id');
                break;
            case 'terbaru':
            default:
                $productsQuery->orderByDesc('created_at')->orderByDesc('id');
                break;
        }

        $products = $productsQuery->paginate($perPage)->withQueryString();
        $kategoris = Kategori::orderBy('nama')->get();

        return view('admin.laporan.penjual-detail', compact(
            'penjual',
            'totalApprovedProducts',
            'avgRatingAll',
            'totalUlasan',
            'products',
            'kategoris',
            'search',
            'kategoriId',
            'uploadFrom',
            'uploadTo',
            'approvalFrom',
            'approvalTo',
            'sort',
            'perPage'
        ));
    }

    /**
     * Unduh Laporan Rekapitulasi Produk & Audit Penjual (PDF Portrait).
     */
    public function penjualDetailPdf(Request $request, Penjual $penjual): Response
    {
        $penjual->load(['user']);

        // 1. Data Indikator Rekapitulasi Audit (Lifetime)
        $totalUploaded = $penjual->produk()->count();
        $totalApproved = $penjual->produk()->where('status', 'aktif')->count();
        $totalPending = $penjual->produk()->where('status', 'pending')->count();
        $totalRejected = $penjual->produk()->where('status', 'ditolak')->count();
        $approvedPercentage = $totalUploaded > 0 ? round(($totalApproved / $totalUploaded) * 100) : 0;

        $productIds = $penjual->produk()->pluck('id');
        $totalUlasan = Ulasan::whereIn('produk_id', $productIds)->publicVisible()->count();
        $avgRating = Ulasan::whereIn('produk_id', $productIds)->publicVisible()->avg('rating');
        $avgRating = $avgRating !== null ? round((float) $avgRating, 1) : null;

        // 2. Data Rincian Katalog Produk & Rating
        $search = trim((string) ($request->query('q') ?? $request->query('search')));
        $kategoriId = $request->query('kategori_id');
        $uploadFrom = $request->query('upload_from');
        $uploadTo = $request->query('upload_to');
        $approvalFrom = $request->query('approval_from');
        $approvalTo = $request->query('approval_to');
        $sort = $request->query('sort', 'terbaru');

        $hasFilters = $search !== '' || !empty($kategoriId) || !empty($uploadFrom) || !empty($uploadTo) || !empty($approvalFrom) || !empty($approvalTo);
        $subSectionTitle = $hasFilters ? '(DATA TERFILTER)' : '(SELURUH DATA)';

        $productsQuery = $penjual->produk()
            ->with(['kategori'])
            ->withAvg(['ulasan as avg_rating' => fn ($q) => $q->publicVisible()], 'rating')
            ->withCount(['ulasan as total_ulasan' => fn ($q) => $q->publicVisible()]);

        if ($search !== '') {
            $productsQuery->where('nama', 'like', "%{$search}%");
        }
        if ($kategoriId) {
            $productsQuery->where('kategori_id', $kategoriId);
        }
        if ($uploadFrom) {
            $productsQuery->whereDate('created_at', '>=', $uploadFrom);
        }
        if ($uploadTo) {
            $productsQuery->whereDate('created_at', '<=', $uploadTo);
        }
        if ($approvalFrom) {
            $productsQuery->whereDate('approved_at', '>=', $approvalFrom);
        }
        if ($approvalTo) {
            $productsQuery->whereDate('approved_at', '<=', $approvalTo);
        }

        switch ($sort) {
            case 'approval_terbaru':
                $productsQuery->orderByDesc('approved_at')->orderByDesc('id');
                break;
            case 'rating_tertinggi':
                $productsQuery->orderByDesc('avg_rating')->orderByDesc('total_ulasan')->orderByDesc('id');
                break;
            case 'rating_terendah':
                $productsQuery->orderBy('avg_rating')->orderBy('total_ulasan')->orderByDesc('id');
                break;
            case 'terlama':
                $productsQuery->orderBy('created_at')->orderBy('id');
                break;
            case 'terbaru':
            default:
                $productsQuery->orderByDesc('created_at')->orderByDesc('id');
                break;
        }

        $products = $productsQuery->get();

        $rekapDate = Carbon::now('Asia/Jakarta')->locale('id')->translatedFormat('l, j F Y');
        $exportedAt = Carbon::now('Asia/Jakarta');

        $type = strtolower((string) $request->query('type', $request->query('kategori', '')));
        $filename = in_array($type, ['rekap-produk', 'produk'], true)
            ? $this->filenameService->rekapProduk($penjual, $exportedAt)
            : $this->filenameService->detailPenjual($penjual, $exportedAt);

        return Pdf::setOption(['isPhpEnabled' => true])
            ->loadView('admin.laporan.penjual-detail-pdf', [
                'penjual' => $penjual,
                'totalUploaded' => $totalUploaded,
                'totalApproved' => $totalApproved,
                'totalPending' => $totalPending,
                'totalRejected' => $totalRejected,
                'approvedPercentage' => $approvedPercentage,
                'totalUlasan' => $totalUlasan,
                'avgRating' => $avgRating,
                'products' => $products,
                'subSectionTitle' => $subSectionTitle,
                'rekapDate' => $rekapDate,
                'exportedAt' => $exportedAt,
            ])
            ->setPaper('a4', 'portrait')
            ->download($filename);
    }

    /**
     * Unduh Laporan Rekapitulasi Produk & Audit Penjual (Excel XLSX).
     */
    public function penjualDetailSheet(Request $request, Penjual $penjual, XlsxWriter $writer): BinaryFileResponse
    {
        $penjual->load(['user']);

        $totalUploaded = $penjual->produk()->count();
        $totalApproved = $penjual->produk()->where('status', 'aktif')->count();
        $totalPending = $penjual->produk()->where('status', 'pending')->count();
        $totalRejected = $penjual->produk()->where('status', 'ditolak')->count();
        $approvedPercentage = $totalUploaded > 0 ? round(($totalApproved / $totalUploaded) * 100) : 0;

        $productIds = $penjual->produk()->pluck('id');
        $totalUlasan = Ulasan::whereIn('produk_id', $productIds)->publicVisible()->count();
        $avgRating = Ulasan::whereIn('produk_id', $productIds)->publicVisible()->avg('rating');
        $avgRating = $avgRating !== null ? round((float) $avgRating, 1) : null;

        $products = $penjual->produk()
            ->with(['kategori'])
            ->withAvg(['ulasan as avg_rating' => fn ($q) => $q->publicVisible()], 'rating')
            ->withCount(['ulasan as total_ulasan' => fn ($q) => $q->publicVisible()])
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->get();

        $exportedAt = Carbon::now('Asia/Jakarta');

        // Gabungkan seluruh tabel ke dalam SATU sheet (page) yang sama
        $rows = [];

        // ── Bagian I: Indikator Rekapitulasi Audit (Lifetime) ──
        $rows[] = [
            '__type' => 'section',
            'I. INDIKATOR REKAPITULASI AUDIT (LIFETIME)', '', '', '', '', '', '',
        ];
        $rows[] = [
            '__type' => 'header',
            'No', 'Indikator Rekapitulasi', 'Jumlah / Total Data', '', '', '', '',
        ];
        $rows[] = [1, 'Total Produk Diunggah (Lifetime)', $totalUploaded . ' Produk', '', '', '', ''];
        $rows[] = [2, 'Produk Disetujui (Tayang di Katalog Website)', $totalApproved . ' Produk (' . $approvedPercentage . '%)', '', '', '', ''];
        $rows[] = [3, 'Produk Pending / Ditolak Moderasi Admin', $totalPending . ' Pending / ' . $totalRejected . ' Ditolak', '', '', '', ''];
        $rows[] = [4, 'Total Ulasan & Rata-Rata Rating Toko', '★ ' . ($avgRating !== null ? number_format($avgRating, 1) : '0.0') . ' (' . $totalUlasan . ' Ulasan)', '', '', '', ''];

        // Spacer pemisah antar bagian
        $rows[] = [
            '__type' => 'spacer',
            '', '', '', '', '', '', '',
        ];

        // ── Bagian II: Rincian Katalog Produk & Rating (Seluruh Data) ──
        $rows[] = [
            '__type' => 'section',
            'II. RINCIAN KATALOG PRODUK & RATING (SELURUH DATA)', '', '', '', '', '', '',
        ];
        $rows[] = [
            '__type' => 'header',
            'No', 'Nama Produk', 'Kategori', 'Tanggal Upload', 'Tgl Masuk Katalog', 'Rating', 'Total Ulasan',
        ];

        foreach ($products as $idx => $prod) {
            $masukKatalog = 'PENDING';
            if ($prod->status === 'aktif') {
                $masukKatalog = $prod->approved_at
                    ? $prod->approved_at->locale('id')->translatedFormat('d M Y, H.i')
                    : ($prod->updated_at ? $prod->updated_at->locale('id')->translatedFormat('d M Y, H.i') : 'AKTIF');
            } elseif ($prod->status === 'ditolak') {
                $masukKatalog = 'DITOLAK';
            }

            $rows[] = [
                $idx + 1,
                $writer->sanitizeText($prod->nama),
                $writer->sanitizeText($prod->kategori?->nama ?? '—'),
                $prod->created_at ? $prod->created_at->locale('id')->translatedFormat('d M Y, H.i') : '—',
                $masukKatalog,
                $writer->formatNumber(round((float) ($prod->avg_rating ?? 0), 1)),
                $writer->formatNumber((int) ($prod->total_ulasan ?? 0)),
            ];
        }

        $sheet = [
            'name' => 'Rekapitulasi Produk',
            'title' => 'Laporan Rekapitulasi Produk — ' . $penjual->nama_toko,
            'metadata' => [
                'Pemilik: ' . ($penjual->user?->name ?? '—') . ' (@' . ($penjual->user?->username ?? '—') . ') | Kelas: ' . ($penjual->lokasi_kelas ?? '—'),
                'Tanggal Rekap: ' . $exportedAt->locale('id')->translatedFormat('l, j F Y'),
                'Waktu Ekspor: ' . $exportedAt->format('d/m/Y H:i') . ' WIB',
            ],
            'headers' => [],
            'rows' => $rows,
            'widths' => [8, 38, 22, 22, 22, 12, 16],
            'orientation' => 'portrait',
            'autoFilter' => false,
            'freezePane' => false,
        ];

        $path = tempnam(storage_path('app'), 'rekap-detail-penjual-');
        if ($path === false) {
            abort(500, 'File ekspor tidak dapat dibuat.');
        }

        $writer->writeWorkbook($path, [$sheet]);

        $safeStoreName = Str::slug($penjual->nama_toko ?: 'toko');
        $filename = 'laporan-detail-' . $safeStoreName . '-' . $exportedAt->format('Ymd') . '.xlsx';

        return response()->download($path, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }

    /**
     * Endpoint API JSON untuk detail rating individual per produk (Modal Admin).
     */
    public function produkRating(Request $request, Produk $produk): JsonResponse
    {
        $produk->load(['kategori']);

        // Query ulasan valid & publik khusus produk ini
        $baseQuery = $produk->ulasan()->publicVisible();

        $totalRating = (clone $baseQuery)->count();
        $avgRating = (clone $baseQuery)->avg('rating');
        $avgRating = $avgRating !== null ? round((float) $avgRating, 1) : null;

        // Distribusi rating 1..5 bintang
        $distributionCounts = (clone $baseQuery)
            ->selectRaw('rating, count(*) as count')
            ->groupBy('rating')
            ->pluck('count', 'rating')
            ->toArray();

        $distribusi = [
            5 => (int) ($distributionCounts[5] ?? 0),
            4 => (int) ($distributionCounts[4] ?? 0),
            3 => (int) ($distributionCounts[3] ?? 0),
            2 => (int) ($distributionCounts[2] ?? 0),
            1 => (int) ($distributionCounts[1] ?? 0),
        ];

        // Pagination untuk rating individual
        $perPage = in_array((int) $request->query('per_page', 5), [5, 10, 20, 50], true)
            ? (int) $request->query('per_page', 5)
            : 5;

        $paginated = (clone $baseQuery)
            ->latest('created_at')
            ->latest('id')
            ->paginate($perPage);

        $fotoUrl = ($produk->foto_utama && Storage::disk('public')->exists($produk->foto_utama))
            ? asset('storage/' . $produk->foto_utama)
            : null;

        $ratings = collect($paginated->items())->map(function (Ulasan $u) {
            $content = $u->public_content;
            $hasComment = $content !== null && trim($content) !== '';

            return [
                'id' => $u->id,
                'guest_username' => $u->guest_username ?: 'Anonim',
                'rating' => (int) $u->rating,
                'komentar' => $hasComment ? $content : null,
                'has_komentar' => $hasComment,
                'tanggal' => $u->created_at ? $u->created_at->translatedFormat('d F Y') : '-',
                'tanggal_waktu' => $u->created_at ? $u->created_at->format('d M Y, H:i') : '-',
                'tanggal_relatif' => $u->created_at ? $u->created_at->diffForHumans() : '',
            ];
        })->values()->all();

        return response()->json([
            'success' => true,
            'product' => [
                'id' => $produk->id,
                'nama' => $produk->nama,
                'kategori' => $produk->kategori?->nama ?? 'Tanpa Kategori',
                'foto_url' => $fotoUrl,
            ],
            'summary' => [
                'avg_rating' => $avgRating,
                'total_rating' => $totalRating,
                'distribusi' => $distribusi,
            ],
            'ratings' => $ratings,
            'pagination' => [
                'current_page' => $paginated->currentPage(),
                'last_page' => $paginated->lastPage(),
                'per_page' => $paginated->perPage(),
                'total' => $paginated->total(),
                'has_more' => $paginated->hasMorePages(),
            ],
        ]);
    }

    public function pdf(Request $request): Response
    {
        $kategori = $request->query('kategori', 'penjual');
        if ($kategori === 'ulasan') {
            return $this->ulasanPdf($request);
        }

        return $this->penjualPdf($request);
    }

    private function selectedPeriod(Request $request): string
    {
        $request->validate(['period' => ['nullable', 'in:weekly,monthly']]);

        return $request->query('period', 'weekly');
    }
}
