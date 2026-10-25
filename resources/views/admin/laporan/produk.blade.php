@extends('layouts.admin')

@section('title', 'Laporan Produk')
@section('page-title', 'Laporan Agregat Produk')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold text-[var(--sm-ink)]">Statistik & Laporan Produk</h2>
            <p class="text-[var(--sm-text-muted)] mt-1 text-sm">Ringkasan status promosi, stok, dan produk terpopuler.</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('admin.laporan.ulasan') }}" class="btn-outline text-sm">
                Lihat Laporan Ulasan
            </a>
        </div>
    </div>

    {{-- Kartu Ringkasan --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="stat-card p-4">
            <p class="text-xs text-[var(--sm-text-muted)]">Total Produk</p>
            <p class="text-2xl font-bold text-[var(--sm-ink)] mt-1">{{ number_format($totalProduk) }}</p>
        </div>
        <div class="stat-card p-4">
            <p class="text-xs text-[var(--sm-primary)]">Produk Aktif</p>
            <p class="text-2xl font-bold text-[var(--sm-primary)] mt-1">{{ number_format($produkByStatus['aktif']) }}</p>
        </div>
        <div class="stat-card p-4">
            <p class="text-xs text-[var(--sm-on-accent)]">Stok Tersedia</p>
            <p class="text-2xl font-bold text-[var(--sm-on-accent)] mt-1">{{ number_format($produkByStok['tersedia']) }}</p>
        </div>
        <div class="stat-card p-4">
            <p class="text-xs text-[var(--sm-ink)]">Stok Habis</p>
            <p class="text-2xl font-bold text-[var(--sm-ink)] mt-1">{{ number_format($produkByStok['habis']) }}</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Kategori Breakdown --}}
        <div class="admin-card p-6">
            <h3 class="text-base font-bold text-[var(--sm-ink)] mb-4">Distribusi per Kategori</h3>
            <div class="space-y-3">
                @forelse($kategoriStats as $kat)
                    <div class="flex items-center justify-between text-sm py-2 border-b border-[var(--sm-border)] last:border-none">
                        <span class="font-medium text-[var(--sm-ink)]">{{ $kat->nama }}</span>
                        <span class="px-2.5 py-0.5 rounded-full bg-[var(--sm-primary)] text-[var(--text-active)] text-xs font-semibold">
                            {{ $kat->produk_count }} produk
                        </span>
                    </div>
                @empty
                    <p class="text-sm text-[var(--sm-text-muted)]">Belum ada kategori.</p>
                @endforelse
            </div>
        </div>

        {{-- Top Produk by Views --}}
        <div class="admin-card p-6">
            <h3 class="text-base font-bold text-[var(--sm-ink)] mb-4">Top 10 Produk Paling Banyak Dilihat</h3>
            <div class="space-y-3">
                @forelse($topViews as $tp)
                    <div class="flex items-center justify-between text-sm py-2 border-b border-[var(--sm-border)] last:border-none">
                        <div>
                            <p class="font-medium text-[var(--sm-ink)]">{{ $tp->nama }}</p>
                            <p class="text-xs text-[var(--sm-text-muted)]">{{ $tp->penjual?->nama_toko ?? 'Toko' }}</p>
                        </div>
                        <span class="font-bold text-[var(--sm-ink)] text-sm">
                            {{ number_format($tp->views) }} <span class="text-xs font-normal text-[var(--sm-text-muted)]">views</span>
                        </span>
                    </div>
                @empty
                    <p class="text-sm text-[var(--sm-text-muted)]">Belum ada data kunjungan produk.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection

