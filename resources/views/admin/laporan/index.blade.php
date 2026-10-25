@extends('layouts.admin')

@section('title', 'Rekap Admin')
@section('page-title', 'Rekap Admin')

@section('content')
<div class="space-y-6">
    <section class="flex flex-col gap-4 xl:flex-row xl:items-end xl:justify-between">
        <div>
            <h2 class="text-2xl font-black text-[var(--sm-ink)]">Rekap {{ $kategoriLaporan === 'penjual' ? 'Kinerja Penjual' : 'Ulasan' }}</h2>
            <p class="mt-1 text-sm text-[var(--sm-text-muted)]">Periode {{ $start->format('d M Y') }}–{{ $end->format('d M Y') }}.</p>
        </div>
        <form method="GET" action="{{ route('admin.laporan.index') }}" class="flex flex-wrap items-center gap-2">
            <input type="hidden" name="kategori" value="{{ $kategoriLaporan }}">
            <label class="sr-only" for="report-period">Periode rekap</label>
            <select id="report-period" name="period" class="form-input min-h-11 w-auto">
                <option value="weekly" @selected($period === 'weekly')>Mingguan</option>
                <option value="monthly" @selected($period === 'monthly')>Bulanan</option>
            </select>
            <button type="submit" class="btn-primary min-h-11">Tampilkan</button>
            <a href="{{ route('admin.laporan.penjual.sheet', ['period' => $period]) }}"
               class="{{ $kategoriLaporan === 'penjual' ? 'btn-primary' : 'btn-outline' }} min-h-11">
                <x-public.home-icon name="download" size="16" class="inline-block align-[-3px]" aria-hidden="true" /> Unduh Sheet Penjual
            </a>
            <a href="{{ route('admin.laporan.ulasan.sheet', ['period' => $period]) }}"
               class="{{ $kategoriLaporan === 'ulasan' ? 'btn-primary' : 'btn-outline' }} min-h-11">
                <x-public.home-icon name="download" size="16" class="inline-block align-[-3px]" aria-hidden="true" /> Unduh Sheet Ulasan
            </a>
        </form>
    </section>

    <nav class="admin-card flex flex-wrap gap-2 p-3" aria-label="Kategori rekap">
        <a href="{{ route('admin.laporan.index', ['kategori' => 'penjual', 'period' => $period]) }}"
           class="{{ $kategoriLaporan === 'penjual' ? 'btn-primary' : 'btn-outline' }} min-h-11" aria-current="{{ $kategoriLaporan === 'penjual' ? 'page' : 'false' }}">
            Performa Penjual
        </a>
        <a href="{{ route('admin.laporan.index', ['kategori' => 'ulasan', 'period' => $period]) }}"
           class="{{ $kategoriLaporan === 'ulasan' ? 'btn-primary' : 'btn-outline' }} min-h-11" aria-current="{{ $kategoriLaporan === 'ulasan' ? 'page' : 'false' }}">
            Rekap Ulasan
        </a>
    </nav>

    @if($kategoriLaporan === 'penjual')
        <section class="space-y-5" aria-label="Performa penjual">
            <div class="grid grid-cols-2 gap-4 xl:grid-cols-6">
                <div class="stat-card p-4"><p class="text-xs text-[var(--sm-text-muted)]">Total penjual</p><p class="mt-1 text-2xl font-black">{{ number_format($sellerReport['total_sellers']) }}</p></div>
                <div class="stat-card p-4"><p class="text-xs text-[var(--sm-text-muted)]">Produk ditambahkan</p><p class="mt-1 text-2xl font-black text-[var(--sm-ink)]">{{ number_format($sellerReport['products_added']) }}</p></div>
                <div class="stat-card p-4"><p class="text-xs text-[var(--sm-text-muted)]">Produk dihapus</p><p class="mt-1 text-2xl font-black text-[var(--sm-ink)]">{{ number_format($sellerReport['products_deleted']) }}</p></div>
                <div class="stat-card p-4"><p class="text-xs text-[var(--sm-text-muted)]">Permintaan edit</p><p class="mt-1 text-2xl font-black">{{ number_format($sellerReport['edits_requested']) }}</p></div>
                <div class="stat-card p-4"><p class="text-xs text-[var(--sm-text-muted)]">Dilihat</p><p class="mt-1 text-2xl font-black">{{ number_format($sellerReport['views']) }}</p></div>
                <div class="stat-card p-4"><p class="text-xs text-[var(--sm-text-muted)]">Ulasan baru</p><p class="mt-1 text-2xl font-black">{{ number_format($sellerReport['reviews']) }}</p></div>
            </div>

            <section class="admin-card">
                {{-- Card Header --}}
                <div class="p-5 border-b-2 border-[var(--sm-border)] flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 bg-[var(--sm-surface)]">
                    <div>
                        <div class="flex items-center gap-2">
                            <div class="w-8 h-8 rounded-lg bg-[var(--sm-primary-soft)] flex items-center justify-center text-[var(--sm-border)] font-bold">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                            </div>
                            <h3 class="text-lg font-black text-[var(--sm-ink)]">Rekap Penjual</h3>
                        </div>
                        <p class="text-xs text-[var(--sm-text-muted)] mt-1">
                            Ringkasan performa penjual, katalog produk, ulasan & rating, serta riwayat upload & hapus dalam periode {{ $periodLabel }}.
                        </p>
                    </div>

                    <div class="flex flex-wrap items-center gap-3">
                        {{-- Quick Live Filter / Search Input --}}
                        <div class="admin-search-field relative min-w-[240px]">
                            <svg class="w-4 h-4 absolute left-3.5 top-1/2 -translate-y-1/2 text-[var(--sm-text-muted)]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                            <input type="text"
                                   id="rekapSearchInput"
                                   placeholder="Cari toko atau penjual..."
                                   class="form-input !h-10 !text-xs pl-12 !py-2 w-full">
                        </div>

                        <a href="{{ route('admin.laporan.penjual.sheet', ['period' => $period]) }}"
                           class="btn-primary !text-xs !py-2 !px-3.5">
                            <x-public.home-icon name="download" size="16" class="inline-block align-[-3px]" aria-hidden="true" /> Unduh Sheet
                        </a>
                    </div>
                </div>

                {{-- Table Content --}}
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left" id="rekapPenjualTable">
                        <thead class="bg-[var(--sm-primary-soft)] border-b-2 border-[var(--sm-border)]">
                            <tr>
                                <th class="px-5 py-3.5 text-xs font-bold text-[var(--sm-text-muted)] uppercase tracking-wider">
                                    PENJUAL & TOKO
                                </th>
                                <th class="px-5 py-3.5 text-xs font-bold text-[var(--sm-text-muted)] uppercase tracking-wider text-center">
                                    TOTAL PRODUK
                                </th>
                                <th class="px-5 py-3.5 text-xs font-bold text-[var(--sm-text-muted)] uppercase tracking-wider text-center">
                                    ULASAN & RATING
                                </th>
                                <th class="px-5 py-3.5 text-xs font-bold text-[var(--sm-text-muted)] uppercase tracking-wider text-center">
                                    AKTIVITAS {{ strtoupper($periodLabel) }} (UPLOAD & HAPUS)
                                </th>
                                <th class="px-5 py-3.5 text-xs font-bold text-[var(--sm-text-muted)] uppercase tracking-wider text-right">
                                    AKSI
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[var(--sm-border)]/20">
                            @forelse($sellerReport['sellers'] as $seller)
                            <tr class="hover:bg-[var(--sm-primary-soft)] transition-colors rekap-row"
                                data-search="{{ strtolower($seller->nama_toko . ' ' . ($seller->user?->name ?? '') . ' ' . ($seller->user?->email ?? '')) }}">

                                {{-- a. Nama Penjual dan Nama Toko --}}
                                <td class="px-5 py-4">
                                    <div class="flex items-center gap-3.5">
                                        @if($seller->foto_toko && \Illuminate\Support\Facades\Storage::disk('public')->exists($seller->foto_toko))
                                            <img src="{{ asset('storage/' . $seller->foto_toko) }}"
                                                 alt="{{ $seller->nama_toko }}"
                                                 class="w-11 h-11 rounded-2xl object-cover border-2 border-[var(--sm-border)] shadow-sm flex-shrink-0">
                                        @else
                                            <div class="w-11 h-11 rounded-2xl bg-[var(--sm-primary-soft)] border-2 border-[var(--sm-border)] text-[var(--sm-border)] font-black text-base flex items-center justify-center flex-shrink-0 shadow-sm">
                                                {{ strtoupper(substr($seller->nama_toko, 0, 1)) }}
                                            </div>
                                        @endif
                                        <div>
                                            <div class="flex items-center gap-2">
                                                <a href="{{ route('admin.penjual.show', $seller->user) }}" class="font-bold text-[var(--sm-ink)] hover:text-[var(--sm-primary)] transition">
                                                    {{ $seller->nama_toko }}
                                                </a>
                                                @if($seller->status_toko === 'buka')
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-[var(--sm-primary-soft)] text-[var(--sm-primary)] border border-[var(--sm-primary)]/20">
                                                        Buka
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-[var(--sm-primary-soft)] text-[var(--sm-text-muted)] border border-[var(--sm-border)]">
                                                        Tutup
                                                    </span>
                                                @endif
                                            </div>
                                            <div class="text-xs text-[var(--sm-text-muted)] mt-0.5 flex items-center gap-1.5 font-medium">
                                                <svg class="w-3.5 h-3.5 text-[var(--sm-text-muted)]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                                <span>{{ $seller->user?->name ?? 'Penjual' }}</span>
                                                @if($seller->lokasi_kelas)
                                                    <span class="text-[var(--sm-text-muted)]">•</span>
                                                    <span>{{ $seller->lokasi_kelas }}</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                {{-- b. Hitung Total Produk --}}
                                <td class="px-5 py-4 text-center">
                                    <div class="inline-flex flex-col items-center">
                                        <span class="text-lg font-black text-[var(--sm-ink)]">
                                            {{ $seller->produk_count }}
                                        </span>
                                        <div class="flex items-center gap-1.5 mt-0.5">
                                            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold bg-[var(--sm-primary-soft)] text-[var(--sm-primary)]">
                                                {{ $seller->produk_aktif_count }} Aktif
                                            </span>
                                            @if(($seller->produk_pending_count ?? 0) > 0)
                                                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold bg-[var(--sm-primary-soft)] text-[var(--sm-ink)]">
                                                    {{ $seller->produk_pending_count }} Pending
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </td>

                                {{-- c. Hitung Total Ulasan & Rating --}}
                                <td class="px-5 py-4 text-center">
                                    <div class="inline-flex flex-col items-center">
                                        @if($seller->avg_rating !== null)
                                            <div class="flex items-center gap-1">
                                                <div class="flex text-[var(--sm-ink)]">
                                                    @for($i = 1; $i <= 5; $i++)
                                                        @if($i <= round($seller->avg_rating))
                                                            <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                                        @else
                                                            <svg class="w-3.5 h-3.5 text-[var(--sm-text-muted)] fill-current" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                                        @endif
                                                    @endfor
                                                </div>
                                                <span class="text-xs font-black text-[var(--sm-ink)] ml-0.5">
                                                    {{ number_format($seller->avg_rating, 1) }}
                                                </span>
                                            </div>
                                            <span class="text-[11px] text-[var(--sm-text-muted)] mt-0.5 font-medium">
                                                {{ $seller->total_ulasan }} ulasan
                                            </span>
                                        @else
                                            <span class="text-xs text-[var(--sm-text-muted)] italic">Belum ada ulasan</span>
                                            <span class="text-[10px] text-[var(--sm-text-muted)] mt-0.5">0 ulasan</span>
                                        @endif
                                    </div>
                                </td>

                                {{-- d. Produk yang baru diupload & dihapus penjual per minggu (7hari) --}}
                                <td class="px-5 py-4 text-center">
                                    <div class="inline-flex items-center gap-2">
                                        {{-- Upload Baru Periode Ini --}}
                                        <div class="flex items-center gap-1 px-2.5 py-1 rounded-xl border text-xs font-bold {{ $seller->period_added > 0 ? 'bg-[var(--sm-primary-soft)] text-[var(--sm-primary)] border-[var(--sm-primary)]/30 shadow-xs' : 'bg-[var(--sm-primary-soft)] text-[var(--sm-text-muted)] border-[var(--sm-border)]' }}"
                                             title="{{ $seller->period_added }} produk baru diupload pada periode {{ $periodLabel }}">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M12 4v16m8-8H4"/></svg>
                                            <span>{{ $seller->period_added }} Baru</span>
                                        </div>

                                        {{-- Dihapus Periode Ini --}}
                                        <div class="flex items-center gap-1 px-2.5 py-1 rounded-xl border text-xs font-bold {{ $seller->period_deleted > 0 ? 'bg-[var(--sm-primary-soft)] text-[var(--sm-primary)] border-[var(--sm-primary)]/30 shadow-xs' : 'bg-[var(--sm-primary-soft)] text-[var(--sm-text-muted)] border-[var(--sm-border)]' }}"
                                             title="{{ $seller->period_deleted }} produk dihapus pada periode {{ $periodLabel }}">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            <span>{{ $seller->period_deleted }} Dihapus</span>
                                        </div>
                                    </div>

                                    @if(($seller->recent_products ?? collect())->count() > 0)
                                        <div class="mt-1 text-[10px] text-[var(--sm-text-muted)] truncate max-w-[200px] mx-auto">
                                            <span class="text-[var(--sm-ink)] font-medium">Baru: {{ $seller->recent_products->pluck('nama')->take(2)->implode(', ') }}</span>
                                        </div>
                                    @endif
                                </td>

                                {{-- Aksi --}}
                                <td class="px-5 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('admin.penjual.show', $seller->user) }}"
                                           class="btn-outline !text-xs !py-1 !px-2.5" title="Detail Penjual">
                                            Detail
                                        </a>
                                        @if($seller->nomor_whatsapp)
                                            <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $seller->nomor_whatsapp) }}"
                                               target="_blank" rel="noopener noreferrer"
                                               class="p-1.5 rounded-lg border border-[var(--sm-border)] text-[var(--sm-text-muted)] hover:text-[var(--sm-ink)] hover:border-[var(--sm-border)] hover:bg-[var(--sm-primary-soft)] transition"
                                               title="Hubungi WhatsApp ({{ $seller->nomor_whatsapp }})">
                                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766.001-3.187-2.575-5.77-5.764-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.299.045-.677.063-1.092-.069-.252-.08-.575-.187-.988-.365-1.739-.751-2.874-2.502-2.961-2.617-.087-.116-.708-.94-.708-1.793s.448-1.273.607-1.446c.159-.173.346-.217.462-.217l.332.006c.106.005.249-.04.39.298.144.347.491 1.2.534 1.287.043.087.072.188.014.304-.058.116-.087.188-.173.289l-.26.304c-.087.086-.177.18-.076.354.101.174.449.741.964 1.201.662.591 1.221.774 1.394.86s.275.072.376-.044c.101-.116.433-.506.549-.68.116-.174.231-.145.39-.087s1.011.477 1.184.564.289.13.332.202c.045.072.045.419-.099.824zm-3.423-14.416c-6.627 0-12 5.373-12 12 0 2.159.57 4.184 1.564 5.938l-1.637 5.98 6.136-1.61c1.704.935 3.661 1.469 5.741 1.469 6.627 0 12-5.373 12-12 0-6.627-5.373-12-12-12z"/></svg>
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="px-5 py-12 text-center text-[var(--sm-text-muted)]">
                                    <div class="flex flex-col items-center justify-center gap-2">
                                        <svg class="w-10 h-10 text-[var(--sm-text-muted)]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                                        <p class="font-bold text-sm">Belum ada akun penjual terdaftar</p>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Table Pagination Bar --}}
                <div id="rekapPaginationContainer" class="p-4 border-t-2 border-[var(--sm-border)] bg-[var(--sm-surface)] flex flex-col md:flex-row md:items-center md:justify-between gap-4 text-xs font-semibold text-[var(--sm-text-muted)]">
                    <div class="flex items-center gap-2">
                        <span id="rekapInfoText">
                            Menampilkan <strong id="rekapFirstItem" class="text-[var(--sm-ink)] font-black">1</strong> – <strong id="rekapLastItem" class="text-[var(--sm-ink)] font-black">10</strong> dari <strong id="rekapTotalItems" class="text-[var(--sm-ink)] font-black">{{ $sellerReport['sellers']->count() }}</strong> data
                        </span>
                    </div>

                    <div class="flex flex-wrap items-center gap-3 sm:gap-4">
                        {{-- Paging 10 - 25 - 50 - Semua --}}
                        <div class="flex items-center gap-1.5">
                            <span class="text-[11px] uppercase tracking-wider font-bold">Baris:</span>
                            <select id="rekapPerPageSelect" aria-label="Pilih jumlah baris" class="form-input !h-8 !py-1 !px-2.5 !text-xs !w-auto font-bold bg-[var(--sm-bg)] border-2 border-[var(--sm-border)] rounded-xl cursor-pointer">
                                <option value="10">10</option>
                                <option value="25">25</option>
                                <option value="50">50</option>
                                <option value="all">Semua</option>
                            </select>
                        </div>

                        {{-- Dropdown Lompat Halaman --}}
                        <div class="flex items-center gap-1.5">
                            <span class="text-[11px] uppercase tracking-wider font-bold">Halaman:</span>
                            <select id="rekapPageSelect" aria-label="Lompat ke halaman" class="form-input !h-8 !py-1 !px-2.5 !text-xs !w-auto font-bold bg-[var(--sm-bg)] border-2 border-[var(--sm-border)] rounded-xl cursor-pointer">
                                <option value="1">1 dari 1</option>
                            </select>
                        </div>

                        {{-- Prev / Next Nav Buttons --}}
                        <div class="flex items-center gap-1">
                            <button type="button" id="rekapPrevBtn" class="p-1.5 rounded-xl border-2 border-[var(--sm-border)] bg-[var(--sm-surface)] text-[var(--sm-ink)] hover:bg-[var(--sm-primary-soft)] shadow-[2px_2px_0px_0px_var(--sm-border)] transition cursor-pointer disabled:opacity-40 disabled:cursor-not-allowed" title="Halaman sebelumnya">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/></svg>
                            </button>
                            <button type="button" id="rekapNextBtn" class="p-1.5 rounded-xl border-2 border-[var(--sm-border)] bg-[var(--sm-surface)] text-[var(--sm-ink)] hover:bg-[var(--sm-primary-soft)] shadow-[2px_2px_0px_0px_var(--sm-border)] transition cursor-pointer disabled:opacity-40 disabled:cursor-not-allowed" title="Halaman selanjutnya">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Table Footer Summary --}}
                <div class="p-4 border-t-2 border-[var(--sm-border)] bg-[var(--sm-primary-soft)] flex flex-col sm:flex-row sm:items-center sm:justify-between text-xs text-[var(--sm-text-muted)] gap-2">
                    <div>
                        Menampilkan <strong>{{ $sellerReport['sellers']->count() }}</strong> toko penjual terdaftar.
                    </div>
                    <div class="flex items-center gap-4">
                        <span>Rata-rata Rating Keseluruhan: <strong class="text-[var(--sm-ink)]">{{ $sellerReport['avg_rating_semua'] !== null ? $sellerReport['avg_rating_semua'] . ' / 5.0 ⭐' : '—' }}</strong></span>
                        <span>Total Ulasan Masuk: <strong class="text-[var(--sm-ink)]">{{ number_format($sellerReport['total_ulasan_semua']) }}</strong></span>
                    </div>
                </div>
            </section>

            <div class="grid grid-cols-1 gap-5 xl:grid-cols-2">
                <section class="admin-card">
                    <div class="admin-card-header"><div><h3 class="font-black">Produk paling banyak dilihat</h3><p class="mt-1 text-xs text-[var(--sm-text-muted)]">Kunjungan tercatat pada periode ini.</p></div></div>
                    <div class="divide-y divide-[var(--sm-border)]/15 px-5">
                        @forelse($sellerReport['top_products'] as $product)
                            <div class="flex items-center justify-between gap-4 py-3 text-sm">
                                <div class="min-w-0"><p class="truncate font-bold">{{ $product->nama }}</p><p class="truncate text-xs text-[var(--sm-text-muted)]">{{ $product->penjual?->nama_toko ?? 'Penjual tidak tersedia' }} · {{ $product->period_reviews }} ulasan</p></div>
                                <div class="shrink-0 text-right"><p class="font-black">{{ number_format($product->period_views) }} dilihat</p></div>
                            </div>
                        @empty
                            <p class="py-8 text-center text-sm text-[var(--sm-text-muted)]">Belum ada kunjungan produk pada periode ini.</p>
                        @endforelse
                    </div>
                </section>

                <section class="admin-card">
                    <div class="admin-card-header"><div><h3 class="font-black">Aktivitas produk</h3><p class="mt-1 text-xs text-[var(--sm-text-muted)]">Penambahan dan penghapusan yang tercatat pada periode ini.</p></div></div>
                    <div class="divide-y divide-[var(--sm-border)]/15 px-5">
                        @forelse($sellerReport['events'] as $event)
                            <div class="flex items-start justify-between gap-4 py-3 text-sm">
                                <div><p class="font-bold">{{ $event['description'] }}</p><p class="text-xs text-[var(--sm-text-muted)]">{{ $event['seller_name'] }} · {{ match($event['activity']) { 'produk.ditambahkan' => 'Produk ditambahkan', 'produk.dihapus' => 'Produk dihapus', default => 'Permintaan edit produk' } }}</p></div>
                                <time class="shrink-0 text-xs text-[var(--sm-text-muted)]">{{ $event['created_at']->format('d M, H:i') }}</time>
                            </div>
                        @empty
                            <p class="py-8 text-center text-sm text-[var(--sm-text-muted)]">Belum ada aktivitas tambah/hapus yang tercatat pada periode ini.</p>
                        @endforelse
                    </div>
                    <p class="border-t border-[var(--sm-border)]/15 px-5 py-3 text-xs text-[var(--sm-text-muted)]">Aktivitas tambah, hapus, dan permintaan edit dihitung dari notifikasi yang tersimpan. Riwayat kunjungan produk yang sudah dihapus tidak tersedia karena log ikut terhapus.</p>
                </section>
            </div>
        </section>
    @else
        <section class="space-y-5" aria-label="Rekap ulasan">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                <div class="stat-card p-4"><p class="text-xs text-[var(--sm-text-muted)]">Ulasan diterbitkan</p><p class="mt-1 text-2xl font-black">{{ number_format($reviewReport['total']) }}</p></div>
                <div class="stat-card p-4"><p class="text-xs text-[var(--sm-text-muted)]">Rating rata-rata</p><p class="mt-1 text-2xl font-black text-[var(--sm-ink)]">{{ number_format($reviewReport['average'], 2) }} / 5</p></div>
                <div class="stat-card p-4">
                    <p class="text-xs text-[var(--sm-text-muted)]">Sebaran rating</p>
                    <div class="mt-2 grid grid-cols-1 gap-1.5">
                        @foreach($reviewReport['distribution'] as $stars => $count)
                            <div class="flex items-center justify-between gap-4 text-sm font-bold">
                                <span class="inline-flex items-center gap-2"><x-public.home-icon name="star" :size="16" :filled="true" class="h-4 w-4 text-[var(--sm-ink)]" />{{ $stars }} bintang</span>
                                <span class="tabular-nums">{{ number_format($count) }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <section class="admin-card">
                <div class="admin-card-header"><div><h3 class="font-black">Produk dengan ulasan terbanyak</h3><p class="mt-1 text-xs text-[var(--sm-text-muted)]">Termasuk nilai rating rata-rata pada periode ini.</p></div></div>
                <div class="divide-y divide-[var(--sm-border)]/15 px-5">
                    @forelse($reviewReport['top_products'] as $product)
                        <div class="flex items-center justify-between gap-4 py-3 text-sm">
                            <div><p class="font-bold">{{ $product->nama }}</p><p class="text-xs text-[var(--sm-text-muted)]">{{ $product->penjual?->nama_toko ?? 'Penjual tidak tersedia' }}</p></div>
                            <div class="shrink-0 text-right"><p class="font-black">{{ $product->period_reviews }} ulasan</p><p class="text-xs text-[var(--sm-ink)]">{{ number_format((float) $product->period_rating, 1) }} / 5</p></div>
                        </div>
                    @empty
                        <p class="py-8 text-center text-sm text-[var(--sm-text-muted)]">Belum ada ulasan pada periode ini.</p>
                    @endforelse
                </div>
            </section>
        </section>
    @endif

    {{-- Client-side Realtime Table Search & Pagination Script --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('rekapSearchInput');
            const perPageSelect = document.getElementById('rekapPerPageSelect');
            const pageSelect = document.getElementById('rekapPageSelect');
            const prevBtn = document.getElementById('rekapPrevBtn');
            const nextBtn = document.getElementById('rekapNextBtn');
            const infoFirst = document.getElementById('rekapFirstItem');
            const infoLast = document.getElementById('rekapLastItem');
            const infoTotal = document.getElementById('rekapTotalItems');

            const allRows = Array.from(document.querySelectorAll('.rekap-row'));
            if (!allRows.length) return;

            let currentPage = 1;
            let perPage = 10;
            let filteredRows = allRows.slice();

            function updatePagination() {
                const total = filteredRows.length;
                let totalPages = 1;

                if (perPage === 'all') {
                    totalPages = 1;
                    currentPage = 1;
                } else {
                    const size = parseInt(perPage, 10);
                    totalPages = Math.max(1, Math.ceil(total / size));
                    if (currentPage > totalPages) currentPage = totalPages;
                    if (currentPage < 1) currentPage = 1;
                }

                if (pageSelect) {
                    pageSelect.innerHTML = '';
                    for (let i = 1; i <= totalPages; i++) {
                        const opt = document.createElement('option');
                        opt.value = i;
                        opt.textContent = i + ' dari ' + totalPages;
                        if (i === currentPage) opt.selected = true;
                        pageSelect.appendChild(opt);
                    }
                    pageSelect.disabled = totalPages <= 1;
                }

                if (prevBtn) prevBtn.disabled = currentPage <= 1;
                if (nextBtn) nextBtn.disabled = currentPage >= totalPages;

                let startIndex = 0;
                let endIndex = total;

                if (perPage !== 'all') {
                    const size = parseInt(perPage, 10);
                    startIndex = (currentPage - 1) * size;
                    endIndex = Math.min(startIndex + size, total);
                }

                allRows.forEach(row => row.style.display = 'none');

                for (let i = startIndex; i < endIndex; i++) {
                    if (filteredRows[i]) {
                        filteredRows[i].style.display = '';
                    }
                }

                if (infoFirst) infoFirst.textContent = total > 0 ? (startIndex + 1) : 0;
                if (infoLast) infoLast.textContent = endIndex;
                if (infoTotal) infoTotal.textContent = total;
            }

            function filterRows() {
                const query = (searchInput ? searchInput.value : '').toLowerCase().trim();
                filteredRows = allRows.filter(row => {
                    const searchTarget = row.getAttribute('data-search') || '';
                    return searchTarget.includes(query);
                });
                currentPage = 1;
                updatePagination();
            }

            if (searchInput) {
                searchInput.addEventListener('input', filterRows);
            }

            if (perPageSelect) {
                perPageSelect.addEventListener('change', function(e) {
                    perPage = e.target.value === 'all' ? 'all' : parseInt(e.target.value, 10);
                    currentPage = 1;
                    updatePagination();
                });
            }

            if (pageSelect) {
                pageSelect.addEventListener('change', function(e) {
                    currentPage = parseInt(e.target.value, 10);
                    updatePagination();
                });
            }

            if (prevBtn) {
                prevBtn.addEventListener('click', function() {
                    if (currentPage > 1) {
                        currentPage--;
                        updatePagination();
                    }
                });
            }

            if (nextBtn) {
                nextBtn.addEventListener('click', function() {
                    currentPage++;
                    updatePagination();
                });
            }

            updatePagination();
        });
    </script>
</div>
@endsection
