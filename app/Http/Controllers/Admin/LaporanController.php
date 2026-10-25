<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Penjual;
use App\Models\Produk;
use App\Models\ProdukViewLog;
use App\Models\Ulasan;
use App\Notifications\AdminActivityNotification;
use App\Support\XlsxWriter;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class LaporanController extends Controller
{
    public function index(Request $request): View
    {
        $request->validate(['kategori' => ['nullable', 'in:penjual,ulasan']]);
        $kategoriLaporan = $request->query('kategori', 'penjual');
        $period = $this->selectedPeriod($request);
        [$start, $end, $periodLabel] = $this->periodRange($period);

        $sellerReport = null;
        $reviewReport = null;

        if ($kategoriLaporan === 'penjual') {
            $sellerReport = $this->sellerPerformance($start, $end);
        } else {
            $reviewReport = $this->reviewPerformance($start, $end);
        }

        return view('admin.laporan.index', compact(
            'kategoriLaporan', 'period', 'periodLabel', 'start', 'end', 'sellerReport', 'reviewReport'
        ));
    }

    /** Keep existing report URLs working while opening the consolidated report page. */
    public function produk(Request $request)
    {
        return redirect()->route('admin.laporan.index', [
            'kategori' => 'penjual',
            'period' => $request->query('period', 'weekly'),
        ]);
    }

    /**
     * Laporan Agregat Ulasan SiswaMart.
     */
    public function ulasan(Request $request): \Illuminate\Http\RedirectResponse
    {
        return redirect()->route('admin.laporan.index', [
            'kategori' => 'ulasan',
            'period' => $request->query('period', 'weekly'),
        ]);
    }

    public function ulasanPdf(Request $request): Response
    {
        $period = $this->selectedPeriod($request);
        [$start, $end, $periodLabel] = $this->periodRange($period);
        $reviews = Ulasan::with(['produk.penjual'])
            ->publicVisible()
            ->whereBetween('created_at', [$start, $end])
            ->orderBy('created_at')
            ->get();
        $averageRating = round((float) $reviews->avg('rating'), 2);
        $ratingDistribution = [];
        for ($stars = 5; $stars >= 1; $stars--) {
            $ratingDistribution[$stars] = $reviews->where('rating', $stars)->count();
        }

        $filename = 'rekap-ulasan-' . $period . '-' . $start->format('Ymd') . '.pdf';

        return Pdf::loadView('admin.laporan.ulasan-pdf', compact(
            'periodLabel', 'start', 'end', 'reviews', 'averageRating', 'ratingDistribution'
        ))->setPaper('a4', 'landscape')->download($filename);
    }

    public function penjualPdf(Request $request): Response
    {
        $period = $this->selectedPeriod($request);
        [$start, $end, $periodLabel] = $this->periodRange($period);
        $sellerReport = $this->sellerPerformance($start, $end);

        $filename = 'rekap-kinerja-penjual-' . $period . '-' . $start->format('Ymd') . '.pdf';

        return Pdf::loadView('admin.laporan.penjual-pdf', compact(
            'period', 'periodLabel', 'start', 'end', 'sellerReport'
        ))->setPaper('a4', 'landscape')->download($filename);
    }

    public function penjualSheet(Request $request, XlsxWriter $writer): BinaryFileResponse
    {
        $period = $this->selectedPeriod($request);
        [$start, $end] = $this->periodRange($period);
        $sellerReport = $this->sellerPerformance($start, $end);
        $headers = [
            'Nama Penjual', 'Nama Toko', 'Total Produk', 'Produk Aktif', 'Menunggu Moderasi',
            'Produk Ditambahkan', 'Produk Dihapus', 'Permintaan Edit', 'Dilihat', 'Ulasan Baru', 'Rating Rata-rata',
        ];
        $rows = $sellerReport['sellers']->map(fn (Penjual $seller) => [
            (string) ($seller->user?->name ?? ''),
            (string) ($seller->nama_toko ?? ''),
            (string) $seller->produk_count,
            (string) $seller->produk_aktif_count,
            (string) $seller->produk_pending_count,
            (string) $seller->period_added,
            (string) $seller->period_deleted,
            (string) $seller->period_edits_requested,
            (string) $seller->period_views,
            (string) $seller->period_reviews,
            $seller->avg_rating === null ? '' : (string) $seller->avg_rating,
        ])->all();

        $path = tempnam(storage_path('app'), 'rekap-kinerja-penjual-');
        if ($path === false) abort(500, 'File ekspor tidak dapat dibuat.');
        $writer->write($path, $headers, $rows, 'Rekap Kinerja Penjual SiswaMart');

        return response()->download($path, 'rekap-kinerja-penjual-' . $period . '-' . $start->format('Ymd') . '.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }

    public function ulasanSheet(Request $request, XlsxWriter $writer): BinaryFileResponse
    {
        $period = $this->selectedPeriod($request);
        [$start, $end, $periodLabel] = $this->periodRange($period);
        $reviews = Ulasan::with(['produk.penjual'])
            ->publicVisible()
            ->whereBetween('created_at', [$start, $end])
            ->orderBy('created_at')
            ->get();
        $headers = ['Tanggal', 'Pengulas', 'Rating', 'Produk', 'Penjual', 'Ulasan'];
        $rows = $reviews->map(fn (Ulasan $review) => [
            (string) ($review->created_at?->format('d/m/Y H:i') ?? ''),
            (string) ($review->guest_username ?: 'Pengunjung'),
            (string) $review->rating,
            (string) ($review->produk?->nama ?? 'Produk telah dihapus'),
            (string) ($review->produk?->penjual?->nama_toko ?? 'Penjual tidak tersedia'),
            (string) ($review->public_content ?: 'Tidak ada teks ulasan.'),
        ])->all();

        $path = tempnam(storage_path('app'), 'rekap-ulasan-');
        if ($path === false) abort(500, 'File ekspor tidak dapat dibuat.');
        $writer->write($path, $headers, $rows, 'Rekap Ulasan SiswaMart - ' . $periodLabel);

        return response()->download($path, 'rekap-ulasan-' . $period . '-' . $start->format('Ymd') . '.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
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

    private function sellerPerformance(Carbon $start, Carbon $end): array
    {
        $events = $this->sellerProductEvents($start, $end);
        $eventCounts = [];
        foreach ($events as $event) {
            $act = $event['activity'];
            if ($act === 'produk.hapus_diminta') {
                $act = 'produk.dihapus';
            }
            $eventCounts[$event['seller_id']][$act] = ($eventCounts[$event['seller_id']][$act] ?? 0) + 1;
        }

        $periodViews = ProdukViewLog::query()
            ->join('produk', 'produk.id', '=', 'produk_views_logs.produk_id')
            ->whereBetween('produk_views_logs.created_at', [$start, $end])
            ->select('produk.penjual_id')
            ->selectRaw("SUM(CASE WHEN produk_views_logs.type = 'view' THEN 1 ELSE 0 END) AS view_count")
            ->groupBy('produk.penjual_id')
            ->get()
            ->keyBy('penjual_id');

        $periodReviews = Ulasan::query()
            ->where(function ($query) {
                $query->where(function ($approved) {
                    $approved->where('ulasan.moderation_status', 'approved')
                        ->whereNotIn('ulasan.status', ['disembunyikan', 'ditolak', 'dihapus', 'rejected']);
                })->orWhere(function ($legacy) {
                    $legacy->whereNull('ulasan.moderation_status')
                        ->whereIn('ulasan.status', ['aktif', 'disetujui', 'disensor']);
                });
            })
            ->join('produk', 'produk.id', '=', 'ulasan.produk_id')
            ->whereBetween('ulasan.created_at', [$start, $end])
            ->select('produk.penjual_id')
            ->selectRaw('COUNT(ulasan.id) AS review_count')
            ->selectRaw('AVG(ulasan.rating) AS average_rating')
            ->groupBy('produk.penjual_id')
            ->get()
            ->keyBy('penjual_id');

        // Total ulasan dan rating kumulatif per penjual
        $cumulativeReviews = DB::table('ulasan')
            ->join('produk', 'produk.id', '=', 'ulasan.produk_id')
            ->where(function ($query) {
                $query->where('ulasan.moderation_status', 'approved')
                      ->whereNotIn('ulasan.status', ['disembunyikan', 'ditolak', 'dihapus', 'rejected'])
                      ->orWhere(function ($legacy) {
                          $legacy->whereNull('ulasan.moderation_status')
                                 ->whereIn('ulasan.status', ['aktif', 'disetujui', 'disensor']);
                      });
            })
            ->select('produk.penjual_id')
            ->selectRaw('COUNT(ulasan.id) as total_ulasan')
            ->selectRaw('ROUND(AVG(ulasan.rating), 1) as avg_rating')
            ->groupBy('produk.penjual_id')
            ->get()
            ->keyBy('penjual_id');

        // Produk baru dibuat dalam periode
        $recentProductsBySeller = Produk::whereBetween('created_at', [$start, $end])
            ->select('id', 'penjual_id', 'nama', 'created_at', 'status')
            ->get()
            ->groupBy('penjual_id');

        $hasPendingAction = Schema::hasColumn('produk', 'pending_action');

        // Produk diajukan hapus dalam periode
        $pendingDeleteBySeller = $hasPendingAction
            ? Produk::where('pending_action', 'delete')
                ->whereBetween('updated_at', [$start, $end])
                ->select('id', 'penjual_id', 'nama', 'updated_at')
                ->get()
                ->groupBy('penjual_id')
            : collect();

        $sellers = Penjual::with('user')
            ->withCount('produk')
            ->withCount(['produk as produk_aktif_count' => function ($query) use ($hasPendingAction) {
                $query->where('status', 'aktif');
                if ($hasPendingAction) {
                    $query->whereNull('pending_action');
                }
            }])
            ->withCount(['produk as produk_pending_count' => function ($query) use ($hasPendingAction) {
                $query->where('status', 'pending');
                if ($hasPendingAction) {
                    $query->orWhereNotNull('pending_action');
                }
            }])
            ->withSum('produk', 'views')
            ->get()
            ->map(function (Penjual $seller) use (
                $periodViews,
                $periodReviews,
                $cumulativeReviews,
                $eventCounts,
                $recentProductsBySeller,
                $pendingDeleteBySeller
            ) {
                $views = $periodViews->get($seller->id);
                $reviews = $periodReviews->get($seller->id);
                $cumul = $cumulativeReviews->get($seller->id);

                $seller->period_views = (int) ($views->view_count ?? 0);
                $seller->period_reviews = (int) ($reviews->review_count ?? 0);
                $seller->period_rating = $reviews?->average_rating !== null ? round((float) $reviews->average_rating, 1) : null;

                // Ulasan & Rating kumulatif toko
                $seller->total_ulasan = (int) ($cumul->total_ulasan ?? 0);
                $seller->avg_rating = $cumul?->avg_rating !== null ? (float) $cumul->avg_rating : null;

                // Produk baru diupload pada periode
                $dbAdded = $recentProductsBySeller->get($seller->id)?->count() ?? 0;
                $notifAdded = $eventCounts[$seller->id]['produk.ditambahkan'] ?? 0;
                $seller->period_added = max($dbAdded, $notifAdded);
                $seller->produk_baru_minggu = $seller->period_added;
                $seller->recent_products = $recentProductsBySeller->get($seller->id) ?? collect();

                // Produk dihapus pada periode
                $notifDeleted = $eventCounts[$seller->id]['produk.dihapus'] ?? 0;
                $pendingDelCount = $pendingDeleteBySeller->get($seller->id)?->count() ?? 0;
                $seller->period_deleted = max($notifDeleted, $pendingDelCount);
                $seller->produk_dihapus_minggu = $seller->period_deleted;
                $seller->pending_deleted_products = $pendingDeleteBySeller->get($seller->id) ?? collect();

                $seller->period_edits_requested = $eventCounts[$seller->id]['produk.diedit'] ?? 0;

                return $seller;
            })
            ->sortByDesc('produk_count')
            ->values();

        $topProducts = Produk::with('penjual')
            ->where('status', 'aktif')
            ->withCount(['viewsLogs as period_views' => fn ($query) => $query->where('type', 'view')->whereBetween('created_at', [$start, $end])])
            ->withCount(['ulasan as period_reviews' => fn ($query) => $query->publicVisible()->whereBetween('created_at', [$start, $end])])
            ->orderByDesc('period_views')
            ->orderByDesc('period_reviews')
            ->limit(10)
            ->get();

        $totalUlasanSemua = $sellers->sum('total_ulasan');
        $sellersWithRating = $sellers->whereNotNull('avg_rating');
        $avgRatingSemua = $sellersWithRating->count() > 0
            ? round($sellersWithRating->avg('avg_rating'), 1)
            : null;

        return [
            'total_sellers' => $sellers->count(),
            'products_added' => max($events->where('activity', 'produk.ditambahkan')->count(), $sellers->sum('period_added')),
            'products_deleted' => max($events->whereIn('activity', ['produk.dihapus', 'produk.hapus_diminta'])->count(), $sellers->sum('period_deleted')),
            'edits_requested' => $events->where('activity', 'produk.diedit')->count(),
            'views' => $sellers->sum('period_views'),
            'reviews' => $sellers->sum('period_reviews'),
            'sellers' => $sellers,
            'top_products' => $topProducts,
            'events' => $events->take(12),
            'total_ulasan_semua' => $totalUlasanSemua,
            'avg_rating_semua' => $avgRatingSemua,
        ];
    }

    private function sellerProductEvents(Carbon $start, Carbon $end)
    {
        if (! Schema::hasTable('notifications')) {
            return collect();
        }

        $sellerIdsByName = Penjual::with('user')->get()->mapWithKeys(function (Penjual $seller) {
            return [mb_strtolower(trim($seller->user?->name ?? '')) => $seller->id];
        });
        $rows = DB::table('notifications')
            ->where('type', AdminActivityNotification::class)
            ->whereBetween('created_at', [$start, $end])
            ->orderByDesc('created_at')
            ->get(['data', 'created_at']);

        $events = collect();
        $seen = [];
        foreach ($rows as $row) {
            $data = json_decode($row->data, true) ?: [];
            $activity = $data['activity'] ?? null;
            $actor = trim($data['actor'] ?? '');
            $sellerId = $data['seller_id'] ?? $sellerIdsByName->get(mb_strtolower($actor));

            if (! in_array($activity, ['produk.ditambahkan', 'produk.dihapus', 'produk.hapus_diminta', 'produk.diedit'], true) || ! $sellerId) {
                continue;
            }

            $at = Carbon::parse($row->created_at);
            $dedupeKey = implode('|', [$activity, $sellerId, $data['entity_id'] ?? '', $at->format('Y-m-d H:i')]);
            if (isset($seen[$dedupeKey])) {
                continue;
            }
            $seen[$dedupeKey] = true;

            $events->push([
                'seller_id' => $sellerId,
                'seller_name' => $actor,
                'activity' => $activity,
                'description' => $data['description'] ?? 'Aktivitas produk',
                'created_at' => $at,
            ]);
        }

        return $events;
    }

    private function reviewPerformance(Carbon $start, Carbon $end): array
    {
        $reviews = Ulasan::publicVisible()->whereBetween('created_at', [$start, $end]);
        $total = (clone $reviews)->count();
        $distribution = [];
        for ($stars = 5; $stars >= 1; $stars--) {
            $distribution[$stars] = (clone $reviews)->where('rating', $stars)->count();
        }

        $topProducts = Produk::with('penjual')
            ->withCount(['ulasan as period_reviews' => fn ($query) => $query->publicVisible()->whereBetween('created_at', [$start, $end])])
            ->withAvg(['ulasan as period_rating' => fn ($query) => $query->publicVisible()->whereBetween('created_at', [$start, $end])], 'rating')
            ->having('period_reviews', '>=', 1)
            ->orderByDesc('period_reviews')
            ->orderByDesc('period_rating')
            ->limit(10)
            ->get();

        return [
            'total' => $total,
            'average' => round((float) (clone $reviews)->avg('rating'), 2),
            'distribution' => $distribution,
            'top_products' => $topProducts,
        ];
    }

    /** @return array{Carbon, Carbon, string} */
    private function periodRange(string $period): array
    {
        $today = now();

        if ($period === 'monthly') {
            $start = $today->copy()->startOfMonth();
            $end = $today->copy()->endOfMonth();
            $label = 'Bulanan';
        } else {
            $start = $today->copy()->startOfWeek(Carbon::MONDAY);
            $end = $today->copy()->endOfWeek(Carbon::SUNDAY);
            $label = 'Mingguan';
        }

        return [$start, $end, $label];
    }
}
