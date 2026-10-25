@extends('layouts.penjual')

@section('title', 'Rekapitulasi Mingguan — SiswaMart')
@section('page-title', 'Rekapitulasi Mingguan')

@section('content')
<style>
    @media print {
        .no-print { display: none !important; }
        .app-sidebar, .app-topbar { display: none !important; }
        body { background: white !important; color: black !important; }
        .admin-card, .stat-card { box-shadow: none !important; border: 1px solid var(--sm-primary-soft) !important; }
    }
</style>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div class="seller-report-page space-y-6">

    {{-- ─── Header & Navigasi Periode ───────────────────────────────── --}}
    <div class="admin-card p-6 bg-white relative overflow-hidden">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <span class="text-xs font-black uppercase text-[var(--sm-primary)] bg-[var(--sm-bg)] border border-[var(--sm-border)] px-3 py-0.5 rounded-full shadow-[2px_2px_0px_0px_var(--sm-border)]">
                        <x-public.home-icon name="chart-bar" size="18" class="inline-block align-[-3px]" aria-hidden="true" /> Performa Toko
                    </span>
                    <span class="text-xs font-bold text-[var(--sm-text-muted)]">{{ $penjual->nama_toko }}</span>
                </div>
                <h2 class="text-2xl md:text-3xl font-black text-[var(--sm-ink)] uppercase tracking-tight">Rekapitulasi Mingguan</h2>
                <p class="text-sm font-semibold text-[var(--sm-text-muted)]">Lihat perkembangan toko dan produkmu minggu ini.</p>
                <div class="mt-2 text-sm font-extrabold text-[var(--sm-ink)] flex items-center gap-2">
                    <span><x-public.home-icon name="calendar" size="16" class="inline-block align-[-3px]" aria-hidden="true" /></span> <span>{{ $periodLabel }}</span>
                </div>
            </div>

            {{-- Navigasi Minggu & Export --}}
            <div class="flex flex-wrap items-center gap-2 no-print">
                <a href="{{ route('penjual.laporan', ['week_offset' => $weekOffset - 1]) }}"
                   class="btn-outline text-xs px-3.5 py-2">
                    ← Minggu Lalu
                </a>

                <a href="{{ route('penjual.laporan', ['week_offset' => 0]) }}"
                   class="btn-outline text-xs px-3.5 py-2 {{ $weekOffset === 0 ? 'bg-[var(--sm-primary)] text-white font-black' : '' }}">
                    Minggu Ini
                </a>

                @if($canGoNext)
                    <a href="{{ route('penjual.laporan', ['week_offset' => $weekOffset + 1]) }}"
                       class="btn-outline text-xs px-3.5 py-2">
                        Minggu Depan →
                    </a>
                @else
                    <button disabled class="btn-outline text-xs px-3.5 py-2 opacity-40 cursor-not-allowed">
                        Minggu Depan →
                    </button>
                @endif

                <a href="{{ route('penjual.laporan.pdf', ['week_offset' => $weekOffset]) }}" class="btn-primary text-xs px-4 py-2 ml-1">
                    <x-public.home-icon name="download" size="16" class="inline-block align-[-3px]" aria-hidden="true" /> Unduh Rekap
                </a>
            </div>
        </div>
    </div>

    {{-- ─── 5 Compact Statistic Cards ──────────────────────────────── --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
        {{-- Total Produk --}}
        <div class="stat-card p-4 border-2 border-[var(--sm-border)]">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-black text-[var(--sm-text-muted)] uppercase tracking-wider">Total Produk</span>
                <span class="text-lg"><x-public.home-icon name="package" size="18" class="inline-block align-[-3px]" aria-hidden="true" /></span>
            </div>
            <p class="text-2xl font-black text-[var(--sm-ink)]">{{ number_format($totalProdukCount) }}</p>
            <p class="text-[11px] font-bold text-[var(--sm-text-muted)] mt-1">Produk di katalog</p>
        </div>

        {{-- Total Dilihat --}}
        <div class="stat-card p-4 border-2 border-[var(--sm-border)]">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-black text-[var(--sm-text-muted)] uppercase tracking-wider">Dilihat Minggu Ini</span>
                <span class="text-lg"><x-public.home-icon name="eye" size="18" class="inline-block align-[-3px]" aria-hidden="true" /></span>
            </div>
            <p class="text-2xl font-black text-[var(--sm-ink)]">{{ number_format($viewsThisWeek) }}</p>
            <p class="text-[11px] font-extrabold mt-1 {{ $viewsComp['type'] === 'up' ? 'text-[var(--sm-ink)]' : ($viewsComp['type'] === 'down' ? 'text-[var(--sm-ink)]' : 'text-[var(--sm-text-muted)]') }}">
                {{ $viewsComp['text'] }}
            </p>
        </div>

        {{-- Ulasan Baru --}}
        <div class="stat-card p-4 border-2 border-[var(--sm-border)]">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-black text-[var(--sm-text-muted)] uppercase tracking-wider">Ulasan Baru</span>
                <span class="text-lg"><x-public.home-icon name="star" size="16" class="inline-block align-[-3px]" aria-hidden="true" /></span>
            </div>
            <p class="text-2xl font-black text-[var(--sm-on-accent)]">{{ number_format($ulasanThisWeek) }}</p>
            <p class="text-[11px] font-extrabold mt-1 {{ $ulasanComp['type'] === 'up' ? 'text-[var(--sm-ink)]' : ($ulasanComp['type'] === 'down' ? 'text-[var(--sm-ink)]' : 'text-[var(--sm-text-muted)]') }}">
                {{ $ulasanComp['text'] }}
            </p>
        </div>
    </div>

    {{-- ─── GRAFIK PERFORMA 7 HARI ──────────────────────────────────── --}}
    <div class="admin-card p-6 bg-white">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h3 class="text-lg font-black uppercase text-[var(--sm-ink)]">Performa 7 Hari</h3>
                <p class="text-xs font-semibold text-[var(--sm-text-muted)]">Kunjungan produk per hari pada periode yang dipilih.</p>
            </div>
            <div class="flex items-center gap-4 text-xs font-bold">
                <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-full bg-[var(--sm-primary)] inline-block"></span> Kunjungan Views</span>
            </div>
        </div>

        <div class="relative h-64 w-full">
            <canvas id="performaChart"></canvas>
        </div>
    </div>

    {{-- ─── LAYOUT 2 KOLOM: PRODUK TERPOPULER & INTERAKSI PENGUNJUNG ──── --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

        {{-- Produk Terpopuler (Top 5) --}}
        <div class="lg:col-span-7 admin-card bg-white">
            <div class="admin-card-header bg-[var(--sm-bg)]">
                <h3 class="font-black text-sm uppercase text-[var(--sm-ink)] flex items-center gap-2">
                    <span><x-public.home-icon name="trend-up" size="18" class="inline-block align-[-3px]" aria-hidden="true" /></span> Produk Terpopuler
                </h3>
                <span class="text-xs font-bold text-[var(--sm-text-muted)]">Top 5 Periode Ini</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-sm">
                    <thead>
                        <tr class="border-b-2 border-[var(--sm-border)] text-xs font-black uppercase text-[var(--sm-text-muted)]">
                            <th class="py-3 px-4 text-center w-12">Rank</th>
                            <th class="py-3 px-4">Produk</th>
                            <th class="py-3 px-4 text-right">Views</th>
                            <th class="py-3 px-4 text-center">Rating</th>
                            <th class="py-3 px-4 text-center">Ulasan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y border-[var(--sm-border)] font-semibold">
                        @forelse($produkTerpopuler as $index => $item)
                            <tr class="hover:bg-[var(--sm-primary-soft)] transition-colors">
                                <td class="py-3 px-4 text-center font-black">
                                    @if($index < 3) <span class="inline-grid h-8 w-8 place-items-center rounded-full border-2 border-[var(--sm-ink)] bg-[var(--sm-primary-soft)] text-sm">{{ $index + 1 }}</span>
                                    @else <span class="text-xs text-[var(--sm-text-muted)]">#{{ $index + 1 }}</span>
                                    @endif
                                </td>
                                <td class="py-3 px-4">
                                    <div class="font-extrabold text-[var(--sm-ink)]">{{ $item->nama }}</div>
                                    <span class="text-[10px] text-[var(--sm-text-muted)] uppercase font-bold">{{ $item->kategori->nama ?? '-' }}</span>
                                </td>
                                <td class="py-3 px-4 text-right font-black text-[var(--sm-primary)]">
                                    {{ number_format($item->cur_views ?? $item->period_views) }}
                                </td>
                                <td class="py-3 px-4 text-center text-[var(--sm-ink)] font-bold whitespace-nowrap">
                                    <x-public.home-icon name="star" size="16" class="inline-block align-[-3px]" aria-hidden="true" /> {{ number_format($item->average_rating, 1) }}
                                </td>
                                <td class="py-3 px-4 text-center font-bold text-[var(--sm-text-muted)]">
                                    {{ $item->review_count }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-8 text-center text-[var(--sm-text-muted)] font-bold">
                                    Belum ada data produk populer untuk periode ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Interaksi Pengunjung --}}
        <div class="lg:col-span-5 admin-card bg-white p-6 flex flex-col justify-between">
            <div>
                <h3 class="font-black text-sm uppercase text-[var(--sm-ink)] mb-1 flex items-center gap-2">
                    <span><x-public.home-icon name="info-circle" size="18" class="inline-block align-[-3px]" aria-hidden="true" /></span> Interaksi Pengunjung
                </h3>
                <p class="text-xs font-semibold text-[var(--sm-text-muted)] mb-6">Seberapa tertarik pengunjung pada tokomu.</p>

                <div class="space-y-4">
                    <div class="flex items-center justify-between p-3 bg-[var(--sm-bg)] rounded-2xl border border-[var(--sm-border)]">
                        <div class="flex items-center gap-3">
                            <span class="text-2xl"><x-public.home-icon name="eye" size="18" class="inline-block align-[-3px]" aria-hidden="true" /></span>
                            <div>
                                <p class="text-xs font-bold text-[var(--sm-text-muted)] uppercase">Views Produk</p>
                                <p class="text-lg font-black text-[var(--sm-ink)]">{{ number_format($viewsThisWeek) }}</p>
                            </div>
                        </div>
                        <span class="text-xs font-extrabold text-[var(--sm-primary)] bg-[var(--sm-primary-soft)] px-2.5 py-1 rounded-full">Kunjungan</span>
                    </div>

                    <div class="flex items-center justify-between p-3 bg-[var(--sm-bg)] rounded-2xl border border-[var(--sm-border)]">
                        <div class="flex items-center gap-3">
                            <span class="text-2xl"><x-public.home-icon name="star" size="16" class="inline-block align-[-3px]" aria-hidden="true" /></span>
                            <div>
                                <p class="text-xs font-bold text-[var(--sm-text-muted)] uppercase">Ulasan Pengunjung</p>
                                <p class="text-lg font-black text-[var(--sm-on-accent)]">{{ number_format($ulasanThisWeek) }}</p>
                            </div>
                        </div>
                        <span class="text-xs font-extrabold text-[var(--sm-ink)] bg-[var(--sm-primary-soft)] px-2.5 py-1 rounded-full">Ulasan</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ─── TABEL PERFORMA SETIAP PRODUK ───────────────────────────── --}}
    <div class="admin-card bg-white">
        <div class="admin-card-header bg-[var(--sm-bg)]">
            <div>
                <h3 class="font-black text-sm uppercase text-[var(--sm-ink)]">Performa Semua Produk</h3>
                <p class="text-xs font-semibold text-[var(--sm-text-muted)]">Perbandingan kunjungan dan ulasan untuk semua produkmu.</p>
            </div>
            <span class="text-xs font-bold text-[var(--sm-text-muted)]">{{ count($performaProduk) }} produk</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-sm">
                <thead>
                    <tr class="border-b-2 border-[var(--sm-border)] text-xs font-black uppercase text-[var(--sm-text-muted)]">
                        <th class="py-3.5 px-4">Nama Produk</th>
                        <th class="py-3.5 px-4 text-right">Views Minggu Ini</th>
                        <th class="py-3.5 px-4 text-right">Views Minggu Lalu</th>
                        <th class="py-3.5 px-4 text-center">Perubahan</th>
                        <th class="py-3.5 px-4 text-center">Ulasan Baru</th>
                        <th class="py-3.5 px-4 text-center">Rating</th>
                    </tr>
                </thead>
                <tbody class="divide-y border-[var(--sm-border)] font-semibold">
                    @forelse($performaProduk as $p)
                        <tr class="hover:bg-[var(--sm-primary-soft)] transition-colors">
                            <td class="py-3.5 px-4 font-extrabold text-[var(--sm-ink)]">
                                {{ $p->nama }}
                                <span class="block text-[10px] text-[var(--sm-text-muted)] font-normal uppercase">{{ $p->kategori->nama ?? '-' }}</span>
                            </td>
                            <td class="py-3.5 px-4 text-right font-black text-[var(--sm-ink)]">
                                {{ number_format($p->cur_views) }}
                            </td>
                            <td class="py-3.5 px-4 text-right text-[var(--sm-text-muted)] font-bold">
                                {{ number_format($p->prev_views) }}
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                @if($p->trend === 'up')
                                    <span class="inline-flex items-center gap-1 text-xs font-black text-[var(--sm-ink)] bg-[var(--sm-primary-soft)] border border-[var(--sm-border)] px-2.5 py-0.5 rounded-full">
                                        ↑ +{{ $p->views_diff }}
                                    </span>
                                @elseif($p->trend === 'down')
                                    <span class="inline-flex items-center gap-1 text-xs font-black text-[var(--sm-ink)] bg-[var(--sm-primary-soft)] border border-[var(--sm-border)] px-2.5 py-0.5 rounded-full">
                                        ↓ {{ $p->views_diff }}
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 text-xs font-bold text-[var(--sm-text-muted)] bg-[var(--sm-primary-soft)] border border-[var(--sm-border)] px-2.5 py-0.5 rounded-full">
                                        — Tetap
                                    </span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-center font-bold text-[var(--sm-ink)]">
                                {{ $p->new_reviews }}
                            </td>
                            <td class="py-3.5 px-4 text-center text-[var(--sm-ink)] font-bold">
                                <x-public.home-icon name="star" size="16" class="inline-block align-[-3px]" aria-hidden="true" /> {{ number_format($p->average_rating, 1) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-[var(--sm-text-muted)] font-bold">
                                Belum ada produk di katalogmu.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- ─── ULASAN MINGGU INI ───────────────────────────────────────── --}}
    <div class="admin-card bg-white p-6 space-y-4">
        <div class="flex items-center justify-between border-b-2 border-[var(--sm-border)] pb-3">
            <div>
                <h3 class="text-lg font-black uppercase text-[var(--sm-ink)] flex items-center gap-2">
                    <span><x-public.home-icon name="message-circle" size="18" class="inline-block align-[-3px]" aria-hidden="true" /></span> Ulasan Minggu Ini
                </h3>
                <p class="text-xs font-semibold text-[var(--sm-text-muted)]">Komentar terbaru pengunjung tentang produkmu minggu ini.</p>
            </div>
            <span class="text-xs font-bold text-[var(--sm-text-muted)]">{{ count($ulasanMingguan) }} ulasan</span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @forelse($ulasanMingguan as $rev)
                <div class="bg-[var(--sm-bg)] p-4 rounded-2xl border-2 border-[var(--sm-border)] space-y-2">
                    <div class="flex items-center justify-between">
                        <div class="font-extrabold text-sm text-[var(--sm-ink)]">{{ $rev->guest_username }}</div>
                        <div class="review-rating-stars inline-flex shrink-0 items-center gap-0.5 text-[var(--sm-primary)]" role="img" aria-label="{{ $rev->rating }} dari 5 bintang">
                            @for($i=1; $i<=5; $i++)
                                <x-public.home-icon name="star" size="14" aria-hidden="true" />
                            @endfor
                        </div>
                    </div>
                    <p class="text-xs font-bold text-[var(--sm-text-muted)] leading-relaxed">
                        "{{ $rev->komentar ?? '-' }}"
                    </p>
                    <div class="flex items-center justify-between text-[10px] font-bold text-[var(--sm-text-muted)] pt-1 border-t border-[var(--sm-border)]">
                        <span class="truncate max-w-[200px] text-[var(--sm-primary-hover)]"><x-public.home-icon name="package" size="18" class="inline-block align-[-3px]" aria-hidden="true" /> {{ $rev->produk->nama ?? 'Produk' }}</span>
                        <span>{{ $rev->created_at->format('d M Y, H:i') }}</span>
                    </div>
                </div>
            @empty
                <div class="md:col-span-2 py-8 text-center text-[var(--sm-text-muted)] font-bold">
                    Belum ada ulasan baru pada periode minggu ini.
                </div>
            @endforelse
        </div>
    </div>

    {{-- ─── INSIGHT PERFORMA ────────────────────────────────────────── --}}
</div>

{{-- Script Inisialisasi Chart.js --}}
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('performaChart');
    if (!ctx) return;

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: @json($chartLabels),
            datasets: [
                {
                    label: 'Views Produk',
                    data: @json($chartViewsData),
                    borderColor: 'var(--sm-primary)',
                    backgroundColor: 'color-mix(in srgb, var(--sm-primary) 15%, transparent)',
                    borderWidth: 3,
                    tension: 0.3,
                    fill: true,
                    pointRadius: 5,
                    pointBackgroundColor: 'var(--sm-primary)'
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { precision: 0, font: { weight: 'bold' } }
                },
                x: {
                    ticks: { font: { weight: 'bold' } }
                }
            }
        }
    });
});
</script>
@endsection
