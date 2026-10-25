@extends('layouts.admin')

@section('title', 'Laporan Ulasan')
@section('page-title', 'Laporan Agregat Ulasan')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold text-[var(--sm-ink)]">Rekap Ulasan {{ $periodLabel }}</h2>
            <p class="text-[var(--sm-text-muted)] mt-1 text-sm">Periode {{ $start->format('d M Y') }}–{{ $end->format('d M Y') }} · hanya ulasan yang berhasil diterbitkan.</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('admin.laporan.produk') }}" class="btn-outline min-h-11 text-sm">
                Lihat Laporan Produk
            </a>
            <form method="GET" action="{{ route('admin.laporan.ulasan') }}" class="flex gap-2">
                <label class="sr-only" for="review-period">Periode rekap</label>
                <select id="review-period" name="period" class="form-input min-h-11 w-auto">
                    <option value="weekly" @selected($period === 'weekly')>Mingguan</option>
                    <option value="monthly" @selected($period === 'monthly')>Bulanan</option>
                </select>
                <button class="btn-primary min-h-11 text-sm" type="submit">Tampilkan</button>
            </form>
            <a href="{{ route('admin.laporan.ulasan.sheet', ['period' => $period]) }}" class="btn-outline min-h-11 text-sm">Unduh Sheet</a>
        </div>
    </div>

    {{-- Kartu Ringkasan --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
        <div class="stat-card p-4">
            <p class="text-xs text-[var(--sm-text-muted)]">Ulasan pada periode ini</p>
            <p class="text-2xl font-bold text-[var(--sm-ink)] mt-1">{{ number_format($totalUlasan) }}</p>
        </div>
        <div class="stat-card p-4">
            <p class="text-xs text-[var(--sm-primary)]">Ulasan diterbitkan</p>
            <p class="text-2xl font-bold text-[var(--sm-primary)] mt-1">{{ number_format($ulasanPublished) }}</p>
        </div>
        <div class="stat-card p-4">
            <p class="text-xs text-[var(--sm-primary)]">Rating Rata-rata</p>
            <p class="text-2xl font-bold text-[var(--sm-primary)] mt-1">{{ $avgRating }} <span class="text-sm font-normal text-[var(--sm-text-muted)]">/ 5.0</span></p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Distribusi Rating --}}
        <div class="admin-card p-6">
            <h3 class="text-base font-bold text-[var(--sm-ink)] mb-4">Distribusi Bintang</h3>
            <div class="space-y-3">
                @foreach($ratingDistribusi as $stars => $count)
                    <div class="flex items-center gap-3 text-sm">
                        <span class="w-12 font-medium text-[var(--sm-ink)]">{{ $stars }} Bintang</span>
                        <div class="flex-1 h-3 bg-[var(--sm-primary-soft)] rounded-full overflow-hidden">
                            @php
                                $percent = $ulasanPublished > 0 ? ($count / $ulasanPublished) * 100 : 0;
                            @endphp
                            <div class="h-full bg-[var(--sm-primary-soft)] rounded-full" style="width: {{ $percent }}%"></div>
                        </div>
                        <span class="w-12 text-right font-semibold text-[var(--sm-text-muted)] text-xs">{{ $count }}</span>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Produk Paling Banyak Mendapat Ulasan --}}
        <div class="admin-card p-6">
            <h3 class="text-base font-bold text-[var(--sm-ink)] mb-4">Produk dengan Ulasan Terbanyak</h3>
            <div class="space-y-3">
                @forelse($topRated as $prod)
                    <div class="flex items-center justify-between text-sm py-2 border-b border-[var(--sm-border)]/20 last:border-none">
                        <div>
                            <p class="font-medium text-[var(--sm-ink)]">{{ $prod->nama }}</p>
                            <p class="text-xs text-[var(--sm-text-muted)]">{{ $prod->penjual?->nama_toko ?? 'Toko' }} ({{ $prod->ulasan_count }} ulasan)</p>
                        </div>
                        <div class="flex items-center gap-1 font-bold text-[var(--sm-ink)]">
                            <span aria-hidden="true">★</span> {{ round((float) $prod->ulasan_avg_rating, 1) }} · {{ $prod->ulasan_count }}
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-[var(--sm-text-muted)]">Belum ada ulasan pada periode ini.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection

