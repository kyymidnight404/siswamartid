@extends('layouts.penjual')

@section('title', 'Dashboard Penjual')
@section('page-title', 'Dashboard Penjual')

@section('content')

    <div class="mb-8 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold text-[var(--sm-ink)]">Selamat datang, {{ Auth::user()->name }}</h2>
            <p class="text-[var(--sm-text-muted)] mt-1 text-sm">Kelola produk dan pantau performa tokomu.</p>
        </div>
        <a href="{{ route('penjual.produk.create') }}" class="btn-primary shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Tambah Produk
        </a>
    </div>

    <div class="mb-3 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h3 class="font-display text-base font-bold text-[var(--sm-ink)]">Rekapitulasi toko</h3>
            <p class="mt-1 text-xs text-[var(--sm-text-muted)]">Ringkasan produk, kunjungan, dan ulasan yang tercatat.</p>
        </div>
        <a href="{{ route('penjual.laporan') }}" class="btn-outline inline-flex w-fit items-center gap-2 text-sm">
            Lihat rekapitulasi lengkap
            <x-public.home-icon name="arrow-right" size="16" aria-hidden="true" />
        </a>
    </div>

    {{-- Store summary --}}
    <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-5 gap-4 mb-8">
        {{-- Total Produk --}}
        <div class="stat-card flex flex-col items-center text-center p-4">
            <div class="w-8 h-8 rounded-full flex items-center justify-center bg-[var(--sm-primary)] text-[var(--text-active)] mb-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
            </div>
            <p class="text-[var(--sm-text-muted)] text-xs mb-1">Total Produk</p>
            <p class="text-2xl font-display font-bold text-[var(--sm-ink)]">{{ number_format($totalProduk) }}</p>
        </div>
        
        {{-- Produk Aktif --}}
        <div class="stat-card flex flex-col items-center text-center p-4 relative overflow-hidden">
            <div class="w-8 h-8 rounded-full flex items-center justify-center bg-[var(--sm-primary-soft)] text-[var(--sm-primary)] mb-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
            </div>
            <p class="text-[var(--sm-text-muted)] text-xs mb-1">Produk Aktif</p>
            <p class="text-2xl font-display font-bold text-[var(--sm-ink)]">{{ number_format($produkAktif) }}</p>
        </div>

        {{-- Menunggu --}}
        <div class="stat-card flex flex-col items-center text-center p-4">
            <div class="w-8 h-8 rounded-full flex items-center justify-center bg-[var(--sm-primary-soft)] text-[var(--sm-ink)] mb-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <p class="text-[var(--sm-text-muted)] text-xs mb-1">Menunggu</p>
            <p class="text-2xl font-display font-bold text-[var(--sm-ink)]">{{ number_format($produkPending) }}</p>
        </div>

        {{-- Total Dilihat --}}
        <div class="stat-card flex flex-col items-center text-center p-4">
            <div class="w-8 h-8 rounded-full flex items-center justify-center bg-[var(--sm-primary-soft)] text-[var(--sm-ink)] mb-2">
                <x-public.home-icon name="eye" size="16" aria-hidden="true" />
            </div>
            <p class="text-[var(--sm-text-muted)] text-xs mb-1">Total Dilihat</p>
            <p class="text-2xl font-display font-bold text-[var(--sm-ink)]">{{ number_format($totalViews) }}</p>
        </div>

        {{-- Total ulasan publik --}}
        <div class="stat-card flex flex-col items-center text-center p-4">
            <div class="w-8 h-8 rounded-full flex items-center justify-center bg-[var(--sm-primary-soft)] text-[var(--sm-ink)] mb-2">
                <x-public.home-icon name="message-circle" size="16" aria-hidden="true" />
            </div>
            <p class="text-[var(--sm-text-muted)] text-xs mb-1">Total Ulasan</p>
            <p class="text-2xl font-display font-bold text-[var(--sm-ink)]">{{ number_format($totalUlasan) }}</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Produk Terbaru --}}
        <div class="lg:col-span-2 space-y-4">
            <div class="admin-card">
                <div class="admin-card-header">
                    <h3 class="font-display font-semibold text-[var(--sm-ink)] text-sm">Produk Terbaru</h3>
                    <a href="{{ route('penjual.produk.index') }}" class="text-[var(--sm-primary)] hover:underline text-xs font-medium">Lihat Semua Produk →</a>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left whitespace-nowrap">
                        <thead class="bg-[var(--sm-primary-soft)] border-b border-[var(--sm-border)]">
                            <tr>
                                <th class="px-5 py-3 text-xs font-semibold text-[var(--sm-text-muted)] uppercase tracking-wider">Produk</th>
                                <th class="px-5 py-3 text-xs font-semibold text-[var(--sm-text-muted)] uppercase tracking-wider">Status</th>
                                <th class="px-5 py-3 text-xs font-semibold text-[var(--sm-text-muted)] uppercase tracking-wider text-right">Views</th>
                                <th class="px-5 py-3 text-xs font-semibold text-[var(--sm-text-muted)] uppercase tracking-wider text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[var(--sm-border)]">
                            @forelse($terbaru as $item)
                            <tr class="hover:bg-[var(--sm-primary-soft)] transition-colors">
                                <td class="px-5 py-3">
                                    <div class="font-medium text-[var(--sm-ink)]">{{ $item->nama }}</div>
                                    <div class="text-xs text-[var(--sm-text-muted)] mt-0.5">Rp {{ number_format($item->harga, 0, ',', '.') }}</div>
                                </td>
                                <td class="px-5 py-3">
                                    <span class="badge badge-{{ $item->status === 'aktif' ? 'aktif' : ($item->status === 'pending' ? 'pending' : ($item->status === 'ditolak' ? 'ditolak' : 'draft')) }}">
                                        {{ ucfirst($item->status) }}
                                    </span>
                                </td>
                                <td class="px-5 py-3 font-semibold text-[var(--sm-text-muted)] text-right">{{ number_format($item->views) }}</td>
                                <td class="px-5 py-3 text-center">
                                    <a href="{{ route('penjual.produk.edit', $item) }}" class="p-1.5 inline-block rounded-full hover:bg-[var(--sm-primary)] text-[var(--sm-text-muted)] hover:text-[var(--sm-primary)] transition-colors" title="Edit">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="px-5 py-8 text-center text-[var(--sm-text-muted)] text-sm">
                                    Belum ada produk. Tambahkan produk pertamamu.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Performa Produk Sidebar --}}
        <div class="space-y-6">
            <div class="admin-card text-center">
                <div class="admin-card-header flex justify-center pb-4">
                    <h3 class="font-display font-semibold text-[var(--sm-ink)] text-sm">
                        <span class="mr-1"><x-public.home-icon name="trend-up" size="18" class="inline-block align-[-3px]" aria-hidden="true" /></span> Produk Terpopuler
                    </h3>
                </div>
                <div>
                    @forelse($topProduk as $idx => $populer)
                    <div class="flex flex-col items-center justify-center gap-2 px-5 py-4 border-b border-[var(--sm-border)] hover:bg-[var(--sm-primary-soft)] transition-colors">
                        <span class="text-xl font-display font-bold"
                            style="color: {{ $idx < 3 ? 'var(--sm-primary)' : 'var(--sm-text-muted)' }}">
                            {{ str_pad($idx+1, 2, '0', STR_PAD_LEFT) }}
                        </span>
                        <div class="w-full">
                            <p class="text-[var(--sm-ink)] text-sm font-medium">{{ $populer->nama }}</p>
                            <p class="text-[var(--sm-text-muted)] text-xs mt-1">{{ number_format($populer->views) }} dilihat · {{ number_format($populer->ulasan_count) }} ulasan</p>
                        </div>
                    </div>
                    @empty
                    <p class="text-sm text-[var(--sm-text-muted)] text-center py-6">Belum ada data performa.</p>
                    @endforelse
                </div>
                <div class="px-5 py-3 border-t border-[var(--sm-border)] bg-[var(--sm-primary-soft)] text-center">
                    <a href="{{ route('penjual.laporan') }}" class="text-[var(--sm-primary)] text-xs font-medium hover:underline">
                        Lihat Laporan Selengkapnya
                    </a>
                </div>
            </div>
            
            <div class="bg-[var(--sm-primary-soft)] border border-[var(--sm-ink)] rounded-2xl p-4">
                <div class="flex items-start gap-3">
                    <svg class="w-5 h-5 text-[var(--sm-primary)] flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <div>
                        <p class="text-sm font-semibold text-[var(--sm-ink)]">Media Promosi</p>
                        <p class="text-xs text-[var(--sm-ink)] mt-0.5">SiswaMart adalah katalog promosi. Pembeli menghubungimu langsung lewat WhatsApp.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection
