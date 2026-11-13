<?php

namespace App\Services;

use App\Models\Penjual;
use App\Models\Produk;
use App\Models\ProdukViewLog;
use App\Models\Ulasan;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class LaporanPenjualService
{
    /**
     * Sediakan data rekapitulasi tunggal dan konsisten untuk penjual (Web, PDF, XLSX).
     *
     * @return array<string, mixed>
     */
    public function getSummary(Penjual $penjual, int $weekOffset = 0): array
    {
        Carbon::setLocale('id');

        // Pastikan batas periode memakai zona waktu Asia/Jakarta secara inklusif
        $nowJakarta = Carbon::now('Asia/Jakarta');
        $periodEnd = $nowJakarta->copy()->addWeeks($weekOffset)->endOfDay();
        $periodStart = $periodEnd->copy()->subDays(6)->startOfDay();

        $previousEnd = $periodStart->copy()->subSecond();
        $previousStart = $previousEnd->copy()->subDays(6)->startOfDay();

        $nextPeriodStart = $periodStart->copy()->addDays(7);
        $canGoNext = $nextPeriodStart->lte($nowJakarta);

        $periodLabel = $periodStart->format('n') === $periodEnd->format('n')
            ? $periodStart->translatedFormat('j').' – '.$periodEnd->translatedFormat('j F Y')
            : $periodStart->translatedFormat('j F').' – '.$periodEnd->translatedFormat('j F Y');
        if ($periodStart->year !== $periodEnd->year) {
            $periodLabel = $periodStart->translatedFormat('j F Y').' – '.$periodEnd->translatedFormat('j F Y');
        }

        $sellerProdukQuery = Produk::query()->where('penjual_id', $penjual->id);
        $sellerProdukIds = (clone $sellerProdukQuery)->pluck('id')->all();

        // 1. Upload Produk dalam 7 hari terakhir
        $uploadedProducts = Produk::query()
            ->with('kategori')
            ->where('penjual_id', $penjual->id)
            ->whereBetween('created_at', [$periodStart, $periodEnd])
            ->orderByDesc('created_at')
            ->get();
        $uploadedProductsCount = $uploadedProducts->count();

        // 2. Ulasan Baru dalam 7 hari terakhir
        $recentReviews = Ulasan::query()
            ->with(['produk.kategori'])
            ->whereIn('produk_id', $sellerProdukIds)
            ->publicVisible()
            ->whereBetween('created_at', [$periodStart, $periodEnd])
            ->orderByDesc('created_at')
            ->get();
        $newReviewsCount = $recentReviews->count();

        // 3. Views Harian & Total Views (Satu sumber, dihitung dalam zona waktu Asia/Jakarta)
        $viewLogs = ProdukViewLog::query()
            ->whereIn('produk_id', $sellerProdukIds)
            ->where('type', 'view')
            ->whereBetween('created_at', [$periodStart, $periodEnd])
            ->get(['created_at']);

        // Inisialisasi 7 hari periode lengkap
        $dailyCounts = [];
        $dailyViews = collect(range(0, 6))->map(function (int $dayIndex) use ($periodStart, &$dailyCounts) {
            $date = $periodStart->copy()->addDays($dayIndex);
            $key = $date->format('Y-m-d');
            $dailyCounts[$key] = 0;

            return [
                'date' => $date,
                'key' => $key,
                'day_name' => $date->translatedFormat('l'),
                'short_day' => $date->translatedFormat('D'),
                'count' => 0,
            ];
        })->keyBy('key');

        foreach ($viewLogs as $log) {
            $logDateKey = $log->created_at->copy()->setTimezone('Asia/Jakarta')->format('Y-m-d');
            if ($dailyViews->has($logDateKey)) {
                $item = $dailyViews->get($logDateKey);
                $item['count']++;
                $dailyViews->put($logDateKey, $item);
            }
        }

        $dailyViewsList = $dailyViews->values();
        $totalViews = (int) $dailyViewsList->sum('count');

        // Views 7 hari sebelumnya
        $previousViews = ProdukViewLog::query()
            ->whereIn('produk_id', $sellerProdukIds)
            ->where('type', 'view')
            ->whereBetween('created_at', [$previousStart, $previousEnd])
            ->count();

        $viewsDifference = $totalViews - $previousViews;
        $viewsComparison = match (true) {
            $viewsDifference > 0 => '+'.$viewsDifference.' dari 7 hari sebelumnya',
            $viewsDifference < 0 => 'turun '.abs($viewsDifference).' dari 7 hari sebelumnya',
            default => 'sama dengan 7 hari sebelumnya',
        };

        // 4. Top 5 Produk Terpopuler
        $popularProducts = Produk::query()
            ->with('kategori')
            ->where('penjual_id', $penjual->id)
            ->withCount(['viewsLogs as period_views' => fn ($query) => $query
                ->where('type', 'view')
                ->whereBetween('created_at', [$periodStart, $periodEnd])])
            ->withCount(['ulasan as period_reviews' => fn ($query) => $query
                ->publicVisible()
                ->whereBetween('created_at', [$periodStart, $periodEnd])])
            ->withAvg(['ulasan as average_rating' => fn ($query) => $query
                ->publicVisible()
                ->whereBetween('created_at', [$periodStart, $periodEnd])], 'rating')
            ->having('period_views', '>', 0)
            ->orderByDesc('period_views')
            ->orderBy('nama')
            ->limit(5)
            ->get();

        $topProductViews = (int) ($popularProducts->first()?->period_views ?? 0);
        $popularViews = (int) $popularProducts->sum('period_views');
        $viewsFromPopularPercent = $totalViews > 0
            ? (int) round(($popularViews / $totalViews) * 100)
            : 0;

        $popularProducts->each(function (Produk $product) use ($topProductViews, $totalViews): void {
            $pViews = (int) $product->period_views;
            $product->bar_width = $topProductViews > 0
                ? round(($pViews / $topProductViews) * 100, 1)
                : 0;
            $product->views_percent = $totalViews > 0
                ? round(($pViews / $totalViews) * 100, 1)
                : 0;
            $product->formatted_rating = $product->average_rating !== null
                ? number_format((float) $product->average_rating, 1)
                : '—';
        });

        // Seluruh produk toko untuk keperluan detail Sheet/PDF
        $allProducts = Produk::query()
            ->with('kategori')
            ->where('penjual_id', $penjual->id)
            ->withCount(['viewsLogs as period_views' => fn ($query) => $query
                ->where('type', 'view')
                ->whereBetween('created_at', [$periodStart, $periodEnd])])
            ->withCount(['ulasan as period_reviews' => fn ($query) => $query
                ->publicVisible()
                ->whereBetween('created_at', [$periodStart, $periodEnd])])
            ->withAvg(['ulasan as average_rating' => fn ($query) => $query
                ->publicVisible()
                ->whereBetween('created_at', [$periodStart, $periodEnd])], 'rating')
            ->orderByDesc('period_views')
            ->orderBy('nama')
            ->get();

        return [
            'penjual' => $penjual,
            'weekOffset' => $weekOffset,
            'periodStart' => $periodStart,
            'periodEnd' => $periodEnd,
            'periodLabel' => $periodLabel,
            'canGoNext' => $canGoNext,
            'uploadedProductsCount' => $uploadedProductsCount,
            'uploadedProducts' => $uploadedProducts,
            'newReviewsCount' => $newReviewsCount,
            'recentReviews' => $recentReviews,
            'dailyViews' => $dailyViewsList,
            'totalViews' => $totalViews,
            'previousViews' => $previousViews,
            'viewsDifference' => $viewsDifference,
            'viewsComparison' => $viewsComparison,
            'popularProducts' => $popularProducts,
            'popularViews' => $popularViews,
            'viewsFromPopularPercent' => $viewsFromPopularPercent,
            'allProducts' => $allProducts,
            'exportedAt' => $nowJakarta,
        ];
    }
}
