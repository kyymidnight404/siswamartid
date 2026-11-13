@extends('exports.pdf.layout')

@section('title', 'Rekapitulasi Mingguan — ' . $penjual->nama_toko)
@section('page-orientation', 'portrait')

@section('report-title', 'Rekapitulasi Mingguan Penjual')
@section('report-subtitle')
    Toko: <strong>{{ $penjual->nama_toko }}</strong>
    @if($penjual->user?->name)
        &middot; Pemilik: {{ $penjual->user->name }}
    @endif
@endsection

@section('period-label', $periodLabel)

@section('content')
    {{-- b. Blok Ringkasan 4 Metrik (Tabel 2x2) --}}
    <table class="summary-grid">
        <tr>
            <td style="width: 50%;">
                <div class="summary-label">Produk Diunggah</div>
                <div class="summary-value">{{ number_format($uploadedProductsCount) }}</div>
                <div class="summary-note">Dalam 7 hari terakhir</div>
            </td>
            <td style="width: 50%;">
                <div class="summary-label">Ulasan Baru</div>
                <div class="summary-value">{{ number_format($newReviewsCount) }}</div>
                <div class="summary-note">Ulasan disetujui 7 hari terakhir</div>
            </td>
        </tr>
        <tr>
            <td style="width: 50%;">
                <div class="summary-label">Total Views Semua Produk</div>
                <div class="summary-value">{{ number_format($totalViews) }}</div>
                <div class="summary-note">{{ $viewsComparison }}</div>
            </td>
            <td style="width: 50%;">
                <div class="summary-label">Views Produk Populer (Top 5)</div>
                <div class="summary-value">{{ number_format($popularViews) }}</div>
                <div class="summary-note">{{ $viewsFromPopularPercent }}% dari total views</div>
            </td>
        </tr>
    </table>

    {{-- c. Tabel Top 5 Produk Terpopuler --}}
    <div class="section-bar">Top 5 Produk Terpopuler (7 Hari Terakhir)</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 8%;" class="text-center">Rank</th>
                <th style="width: 30%;">Produk</th>
                <th style="width: 17%;">Kategori</th>
                <th style="width: 12%;" class="text-right">Views 7 Hari</th>
                <th style="width: 9%;" class="text-right">% Total</th>
                <th style="width: 12%;" class="text-right">Total Views</th>
                <th style="width: 6%;" class="text-center">Rating</th>
                <th style="width: 6%;" class="text-center">Ulasan</th>
            </tr>
        </thead>
        <tbody>
            @forelse($popularProducts as $index => $prod)
                <tr>
                    <td class="text-center {{ $index === 0 ? 'rank-first' : '' }}">
                        #{{ $index + 1 }}
                    </td>
                    <td><strong>{{ $prod->nama }}</strong></td>
                    <td class="text-secondary">{{ $prod->kategori?->nama ?? '—' }}</td>
                    <td class="text-right font-bold">{{ number_format($prod->period_views) }}</td>
                    <td class="text-right">{{ $prod->views_percent }}%</td>
                    <td class="text-right text-secondary">{{ number_format($prod->views) }}</td>
                    <td class="text-center">{{ $prod->formatted_rating }}</td>
                    <td class="text-center">{{ number_format($prod->period_reviews) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center text-secondary" style="padding: 12px;">
                        Belum ada data pada periode ini
                    </td>
                </tr>
            @endforelse
        </tbody>
        @if($popularProducts->isNotEmpty())
            <tfoot>
                <tr>
                    <td colspan="3" class="text-left font-bold">Total Top 5</td>
                    <td class="text-right font-bold">{{ number_format($popularViews) }}</td>
                    <td class="text-right font-bold">{{ $viewsFromPopularPercent }}%</td>
                    <td class="text-right font-bold">{{ number_format($popularProducts->sum('views')) }}</td>
                    <td class="text-center font-bold">
                        {{ $popularProducts->avg('average_rating') !== null ? number_format((float) $popularProducts->avg('average_rating'), 1) : '—' }}
                    </td>
                    <td class="text-center font-bold">{{ number_format($popularProducts->sum('period_reviews')) }}</td>
                </tr>
            </tfoot>
        @endif
    </table>

    {{-- d. Views Per Hari (Tabel 7 Hari + Grafik Batang Sederhana) --}}
    @php
        $maxDaily = max(1, (int) $dailyViews->max('count'));
    @endphp
    <div class="section-bar">Views Per Hari (7 Hari Terakhir)</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 18%;">Tanggal</th>
                <th style="width: 18%;">Hari</th>
                <th style="width: 16%;" class="text-right">Views</th>
                <th style="width: 48%;">Grafik Frekuensi</th>
            </tr>
        </thead>
        <tbody>
            @foreach($dailyViews as $day)
                @php
                    $percent = $maxDaily > 0 ? round(($day['count'] / $maxDaily) * 100) : 0;
                @endphp
                <tr>
                    <td>{{ $day['date']->format('d/m/Y') }}</td>
                    <td>{{ $day['day_name'] }}</td>
                    <td class="text-right font-bold">{{ number_format($day['count']) }}</td>
                    <td>
                        <div class="bar-container">
                            <div class="bar-fill" style="width: {{ max(4, $percent) }}%;"></div>
                        </div>
                    </td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="2" class="text-left font-bold">Total Keseluruhan</td>
                <td class="text-right font-bold">{{ number_format($totalViews) }}</td>
                <td class="text-secondary">Akumulasi 7 hari</td>
            </tr>
        </tfoot>
    </table>

    {{-- e. Daftar Ringkas Produk Diunggah (Maks 10 baris) --}}
    @if($uploadedProducts->isNotEmpty())
        <div class="section-bar">Produk Diunggah (7 Hari Terakhir)</div>
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 20%;">Tanggal Unggah</th>
                    <th style="width: 35%;">Nama Produk</th>
                    <th style="width: 20%;">Kategori</th>
                    <th style="width: 15%;" class="text-right">Harga</th>
                    <th style="width: 10%;" class="text-center">Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($uploadedProducts->take(10) as $prod)
                    <tr>
                        <td>{{ $prod->created_at?->format('d/m/Y H:i') ?? '—' }}</td>
                        <td><strong>{{ $prod->nama }}</strong></td>
                        <td class="text-secondary">{{ $prod->kategori?->nama ?? '—' }}</td>
                        <td class="text-right">Rp {{ number_format($prod->harga, 0, ',', '.') }}</td>
                        <td class="text-center">{{ ucfirst($prod->status) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        @if($uploadedProducts->count() > 10)
            <p class="text-secondary" style="font-size: 10pt; margin-top: -6px; margin-bottom: 12px; font-style: italic;">
                dan {{ $uploadedProducts->count() - 10 }} lainnya, lihat sheet.
            </p>
        @endif
    @endif

    {{-- e. Daftar Ringkas Ulasan (Maks 10 baris) --}}
    @if($recentReviews->isNotEmpty())
        <div class="section-bar">Ulasan Baru (7 Hari Terakhir)</div>
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 18%;">Tanggal</th>
                    <th style="width: 25%;">Produk</th>
                    <th style="width: 12%;" class="text-center">Rating</th>
                    <th style="width: 45%;">Komentar</th>
                </tr>
            </thead>
            <tbody>
                @foreach($recentReviews->take(10) as $rev)
                    <tr>
                        <td>{{ $rev->created_at?->format('d/m/Y H:i') ?? '—' }}</td>
                        <td><strong>{{ $rev->produk?->nama ?? '—' }}</strong></td>
                        <td class="text-center font-bold">{{ $rev->rating }} / 5</td>
                        <td>{{ $rev->public_content ?: 'Tidak ada teks ulasan.' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        @if($recentReviews->count() > 10)
            <p class="text-secondary" style="font-size: 10pt; margin-top: -6px; margin-bottom: 12px; font-style: italic;">
                dan {{ $recentReviews->count() - 10 }} lainnya, lihat sheet.
            </p>
        @endif
    @endif
@endsection
