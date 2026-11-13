<?php

namespace App\Services;

use App\Models\Penjual;
use App\Models\Produk;
use App\Models\ProdukViewLog;
use App\Models\Ulasan;
use App\Notifications\AdminActivityNotification;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class LaporanAdminService
{
    /**
     * Hitung rentang periode berdasarkan tipe (weekly / monthly).
     *
     * @return array{0: Carbon, 1: Carbon, 2: string}
     */
    public function getPeriodRange(string $period): array
    {
        Carbon::setLocale('id');
        $now = Carbon::now('Asia/Jakarta');

        if ($period === 'monthly') {
            $start = $now->copy()->startOfMonth();
            $end = $now->copy()->endOfMonth();
            $label = 'Bulanan';
        } else {
            $start = $now->copy()->startOfWeek(Carbon::MONDAY)->startOfDay();
            $end = $now->copy()->endOfWeek(Carbon::SUNDAY)->endOfDay();
            $label = 'Mingguan';
        }

        return [$start, $end, $label];
    }

    /**
     * Data performa seluruh penjual dalam periode.
     *
     * @return array<string, mixed>
     */
    public function getSellerPerformance(Carbon $start, Carbon $end, string $period = 'weekly'): array
    {
        Carbon::setLocale('id');
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
            'exportedAt' => Carbon::now('Asia/Jakarta'),
        ];
    }

    /**
     * Data performa ulasan agregat admin dalam periode.
     *
     * @return array<string, mixed>
     */
    public function getReviewPerformance(Carbon $start, Carbon $end): array
    {
        Carbon::setLocale('id');
        $reviewsQuery = Ulasan::with(['produk.penjual'])
            ->publicVisible()
            ->whereBetween('created_at', [$start, $end]);

        $reviews = (clone $reviewsQuery)->orderBy('created_at')->get();
        $total = $reviews->count();
        $average = round((float) $reviews->avg('rating'), 2);

        $distribution = [];
        for ($stars = 5; $stars >= 1; $stars--) {
            $distribution[$stars] = $reviews->where('rating', $stars)->count();
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
            'average' => $average,
            'distribution' => $distribution,
            'top_products' => $topProducts,
            'reviews' => $reviews,
            'exportedAt' => Carbon::now('Asia/Jakarta'),
        ];
    }

    private function sellerProductEvents(Carbon $start, Carbon $end): Collection
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
}
