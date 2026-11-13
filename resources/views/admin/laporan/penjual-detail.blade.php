@extends('layouts.admin')

@section('title', 'Laporan Statistik Penjual — ' . $penjual->nama_toko)
@section('page-title', 'Laporan Penjual')

@section('content')
<div class="space-y-6" x-data="sellerRatingModal()">
    {{-- Navigasi Kembali & Header --}}
    <section class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <div class="flex items-center gap-2">
                <a href="{{ route('admin.laporan.index', ['kategori' => 'penjual']) }}"
                   class="inline-flex items-center gap-1.5 text-xs font-bold text-[var(--sm-primary)] hover:text-[var(--sm-ink)] transition">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    <span>Kembali ke Rekap Toko</span>
                </a>
            </div>
            <h2 class="text-2xl font-black text-[var(--sm-ink)] mt-1">Laporan Statistik Penjual</h2>
            <p class="text-xs text-[var(--sm-text-muted)] mt-0.5">
                Aktivitas dan performa seluruh produk yang telah diunggah dan disetujui oleh Admin untuk toko <strong>{{ $penjual->nama_toko }}</strong>.
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2.5">
            <x-ui.download-menu
                :pdf-url="route('admin.laporan.penjual.detail.pdf', array_merge(['penjual' => $penjual->id], request()->query()))"
                :xlsx-url="route('admin.laporan.penjual.detail.sheet', array_merge(['penjual' => $penjual->id], request()->query()))"
                label="Unduh Laporan"
            />
            <a href="{{ route('admin.penjual.show', $penjual->user) }}" class="btn-outline !text-xs !py-2.5 !px-3.5 font-bold" title="Buka Detail Akun">
                Profil Toko
            </a>
            @if($penjual->nomor_whatsapp)
                <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $penjual->nomor_whatsapp) }}"
                   target="_blank" rel="noopener noreferrer"
                   class="btn-primary !text-xs !py-2.5 !px-3.5 font-bold inline-flex items-center gap-1.5">
                    <svg class="w-4 h-4 flex-shrink-0" fill="currentColor" viewBox="0 0 24 24"><path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766.001-3.187-2.575-5.77-5.764-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.299.045-.677.063-1.092-.069-.252-.08-.575-.187-.988-.365-1.739-.751-2.874-2.502-2.961-2.617-.087-.116-.708-.94-.708-1.793s.448-1.273.607-1.446c.159-.173.346-.217.462-.217l.332.006c.106.005.249-.04.39.298.144.347.491 1.2.534 1.287.043.087.072.188.014.304-.058.116-.087.188-.173.289l-.26.304c-.087.086-.177.18-.076.354.101.174.449.741.964 1.201.662.591 1.221.774 1.394.86s.275.072.376-.044c.101-.116.433-.506.549-.68.116-.174.231-.145.39-.087s1.011.477 1.184.564.289.13.332.202c.045.072.045.419-.099.824zm-3.423-14.416c-6.627 0-12 5.373-12 12 0 2.159.57 4.184 1.564 5.938l-1.637 5.98 6.136-1.61c1.704.935 3.661 1.469 5.741 1.469 6.627 0 12-5.373 12-12 0-6.627-5.373-12-12-12z"/></svg>
                    <span>WhatsApp</span>
                </a>
            @endif
        </div>
    </section>

    {{-- 1. Kartu Informasi Penjual & Toko --}}
    <section class="admin-card p-5 border-2 border-[var(--sm-border)] bg-[var(--sm-surface)]">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-5">
            <div class="flex items-center gap-4">
                @if($penjual->foto_toko && \Illuminate\Support\Facades\Storage::disk('public')->exists($penjual->foto_toko))
                    <img src="{{ asset('storage/' . $penjual->foto_toko) }}"
                         alt="{{ $penjual->nama_toko }}"
                         class="w-16 h-16 rounded-2xl object-cover border-2 border-[var(--sm-border)] shadow-[2px_2px_0px_0px_var(--sm-border)] flex-shrink-0">
                @else
                    <div class="w-16 h-16 rounded-2xl bg-[var(--sm-primary-soft)] border-2 border-[var(--sm-border)] text-[var(--sm-border)] font-black text-2xl flex items-center justify-center flex-shrink-0 shadow-[2px_2px_0px_0px_var(--sm-border)]">
                        {{ strtoupper(substr($penjual->nama_toko, 0, 1)) }}
                    </div>
                @endif
                <div>
                    <div class="flex flex-wrap items-center gap-2">
                        <h3 class="text-xl font-black text-[var(--sm-ink)]">{{ $penjual->nama_toko }}</h3>
                        @if($penjual->user?->username)
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-[var(--sm-primary-soft)] text-[var(--sm-primary)] border border-[var(--sm-primary)]/30">
                                {{ '@' . $penjual->user->username }}
                            </span>
                        @endif
                        @if($penjual->status_toko === 'buka')
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-300">
                                Toko Buka
                            </span>
                        @else
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-slate-100 text-slate-700 border border-slate-300">
                                Toko Tutup
                            </span>
                        @endif
                    </div>
                    <div class="text-xs text-[var(--sm-text-muted)] mt-1.5 flex flex-wrap items-center gap-x-3 gap-y-1 font-medium">
                        <span class="flex items-center gap-1">
                            <svg class="w-3.5 h-3.5 text-[var(--sm-text-muted)] flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                            <strong>{{ $penjual->user?->name ?? 'Penjual' }}</strong>
                        </span>
                        <span>•</span>
                        <span>Email: <strong>{{ $penjual->user?->email }}</strong></span>
                        @if($penjual->nisn)
                            <span>•</span>
                            <span>NISN: <strong>{{ $penjual->nisn }}</strong></span>
                        @endif
                        @if($penjual->lokasi_kelas)
                            <span>•</span>
                            <span>Kelas: <strong>{{ $penjual->lokasi_kelas }}</strong></span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- 2. Tiga Kartu Ringkasan Statistik (Approved, Rating, Ulasan) --}}
    <section class="grid grid-cols-1 gap-4 sm:grid-cols-3" aria-label="Statistik performa produk disetujui">
        {{-- Card 1: Total Produk Approved --}}
        <div class="stat-card p-5 border-2 border-[var(--sm-border)] bg-[var(--sm-surface)] rounded-2xl shadow-[3px_3px_0px_0px_var(--sm-border)]">
            <div class="flex items-center justify-between">
                <p class="text-xs font-bold uppercase tracking-wider text-[var(--sm-text-muted)]">Total Produk Approved</p>
                <div class="w-8 h-8 rounded-xl bg-emerald-100 text-emerald-800 flex items-center justify-center font-bold">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                </div>
            </div>
            <p class="mt-2 text-3xl font-black text-[var(--sm-ink)]">{{ number_format($totalApprovedProducts) }}</p>
            <p class="text-[11px] text-[var(--sm-text-muted)] mt-1">Produk telah disetujui Admin & masuk katalog</p>
        </div>

        {{-- Card 2: Rata-rata Rating --}}
        <div class="stat-card p-5 border-2 border-[var(--sm-border)] bg-[var(--sm-surface)] rounded-2xl shadow-[3px_3px_0px_0px_var(--sm-border)]">
            <div class="flex items-center justify-between">
                <p class="text-xs font-bold uppercase tracking-wider text-[var(--sm-text-muted)]">Rata-rata Rating</p>
                <div class="w-8 h-8 rounded-xl bg-amber-100 text-amber-800 flex items-center justify-center font-bold">
                    <svg class="w-4 h-4 flex-shrink-0 fill-current" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                </div>
            </div>
            @if($avgRatingAll !== null)
                <div class="mt-2 flex items-baseline gap-2">
                    <p class="text-3xl font-black text-[var(--sm-ink)]">{{ number_format($avgRatingAll, 1) }}</p>
                    <span class="text-sm font-bold text-[var(--sm-text-muted)] inline-flex items-center gap-1">
                        <span>/ 5.0</span>
                        <svg class="w-4 h-4 flex-shrink-0 text-amber-500 fill-current" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                    </span>
                </div>
                <p class="text-[11px] text-[var(--sm-text-muted)] mt-1">Berdasarkan ulasan produk yang tersimpan</p>
            @else
                <p class="mt-2 text-xl font-black text-[var(--sm-text-muted)] italic">Belum ada rating</p>
                <p class="text-[11px] text-[var(--sm-text-muted)] mt-1">Belum ada ulasan yang diterima</p>
            @endif
        </div>

        {{-- Card 3: Total Ulasan/Rating Diterima --}}
        <div class="stat-card p-5 border-2 border-[var(--sm-border)] bg-[var(--sm-surface)] rounded-2xl shadow-[3px_3px_0px_0px_var(--sm-border)]">
            <div class="flex items-center justify-between">
                <p class="text-xs font-bold uppercase tracking-wider text-[var(--sm-text-muted)]">Total Ulasan Masuk</p>
                <div class="w-8 h-8 rounded-xl bg-blue-100 text-blue-800 flex items-center justify-center font-bold">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>
                </div>
            </div>
            <div class="mt-2 flex items-baseline gap-1.5">
                <p class="text-3xl font-black text-[var(--sm-ink)]">{{ number_format($totalUlasan) }}</p>
                <span class="text-sm font-bold text-[var(--sm-text-muted)]">ulasan</span>
            </div>
            <p class="text-[11px] text-[var(--sm-text-muted)] mt-1">Total ulasan pembeli dari produk approved</p>
        </div>
    </section>

    {{-- 3. Toolbar Pencarian & Filter Riwayat Produk --}}
    <section class="admin-card p-5 border-2 border-[var(--sm-border)] bg-[var(--sm-surface)]">
        <form method="GET" action="{{ route('admin.laporan.penjual.detail', $penjual->id) }}" class="space-y-4">
            <input type="hidden" name="per_page" value="{{ $perPage }}">

            <div class="grid grid-cols-1 gap-3 md:grid-cols-2 lg:grid-cols-4">
                {{-- Search Produk --}}
                <div class="admin-search-field relative">
                    <label class="sr-only" for="input-search-produk">Cari nama produk</label>
                    <svg class="w-4 h-4 absolute left-3.5 top-1/2 -translate-y-1/2 text-[var(--sm-text-muted)] pointer-events-none flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <input type="text"
                           id="input-search-produk"
                           name="q"
                           value="{{ $search }}"
                           placeholder="Cari nama produk..."
                           class="form-input !h-10 !text-xs pl-12 !py-2 w-full">
                </div>

                {{-- Filter Kategori --}}
                <div>
                    <label class="sr-only" for="filter-kategori">Filter Kategori</label>
                    <select id="filter-kategori" name="kategori_id" class="form-input !h-10 !text-xs !py-2 w-full font-bold">
                        <option value="">Semua Kategori</option>
                        @foreach($kategoris as $kat)
                            <option value="{{ $kat->id }}" @selected((string)$kategoriId === (string)$kat->id)>
                                {{ $kat->nama }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Pengurutan (Sort) --}}
                <div class="md:col-span-2 lg:col-span-2">
                    <label class="sr-only" for="filter-sort">Urutan</label>
                    <select id="filter-sort" name="sort" class="form-input !h-10 !text-xs !py-2 w-full font-bold">
                        <option value="terbaru" @selected($sort === 'terbaru')>Tanggal Upload Terbaru</option>
                        <option value="terlama" @selected($sort === 'terlama')>Tanggal Upload Terlama</option>
                        <option value="approval_terbaru" @selected($sort === 'approval_terbaru')>Tanggal Disetujui Terbaru</option>
                        <option value="rating_tertinggi" @selected($sort === 'rating_tertinggi')>Rating Tertinggi</option>
                        <option value="rating_terendah" @selected($sort === 'rating_terendah')>Rating Terendah</option>
                    </select>
                </div>
            </div>

            {{-- Rentang Tanggal Upload & Tanggal Approval --}}
            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-4 pt-2 border-t border-[var(--sm-border)]/20">
                {{-- Tanggal Upload Dari --}}
                <div>
                    <label class="block text-[11px] font-bold text-[var(--sm-text-muted)] uppercase tracking-wider mb-1" for="upload_from">
                        Upload Dari
                    </label>
                    <input type="date"
                           id="upload_from"
                           name="upload_from"
                           value="{{ $uploadFrom }}"
                           class="form-input !h-9 !text-xs !py-1 w-full">
                </div>

                {{-- Tanggal Upload Sampai --}}
                <div>
                    <label class="block text-[11px] font-bold text-[var(--sm-text-muted)] uppercase tracking-wider mb-1" for="upload_to">
                        Upload Sampai
                    </label>
                    <input type="date"
                           id="upload_to"
                           name="upload_to"
                           value="{{ $uploadTo }}"
                           class="form-input !h-9 !text-xs !py-1 w-full">
                </div>

                {{-- Tanggal Disetujui Dari --}}
                <div>
                    <label class="block text-[11px] font-bold text-[var(--sm-text-muted)] uppercase tracking-wider mb-1" for="approval_from">
                        Disetujui Dari
                    </label>
                    <input type="date"
                           id="approval_from"
                           name="approval_from"
                           value="{{ $approvalFrom }}"
                           class="form-input !h-9 !text-xs !py-1 w-full">
                </div>

                {{-- Tanggal Disetujui Sampai --}}
                <div>
                    <label class="block text-[11px] font-bold text-[var(--sm-text-muted)] uppercase tracking-wider mb-1" for="approval_to">
                        Disetujui Sampai
                    </label>
                    <input type="date"
                           id="approval_to"
                           name="approval_to"
                           value="{{ $approvalTo }}"
                           class="form-input !h-9 !text-xs !py-1 w-full">
                </div>
            </div>

            {{-- Action Buttons --}}
            <div class="flex items-center justify-end gap-2 pt-1">
                @if($search || $kategoriId || $uploadFrom || $uploadTo || $approvalFrom || $approvalTo || $sort !== 'terbaru')
                    <a href="{{ route('admin.laporan.penjual.detail', $penjual->id) }}" class="btn-outline !h-9 !text-xs !py-1 !px-3">
                        Reset Filter
                    </a>
                @endif
                <button type="submit" class="btn-primary !h-9 !text-xs !py-1 !px-4">
                    Terapkan Filter
                </button>
            </div>
        </form>
    </section>

    {{-- 4. Tabel Riwayat Produk Approved --}}
    <section class="admin-card border-2 border-[var(--sm-border)] bg-[var(--sm-surface)] overflow-hidden">
        <div class="p-5 border-b-2 border-[var(--sm-border)] flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 bg-[var(--sm-surface)]">
            <div>
                <h3 class="text-lg font-black text-[var(--sm-ink)] flex items-center gap-2">
                    <span>Riwayat Produk Disetujui</span>
                    <span class="text-xs font-bold px-2 py-0.5 rounded-full bg-[var(--sm-primary-soft)] text-[var(--sm-primary)] border border-[var(--sm-primary)]/20">
                        {{ $products->total() }} Produk
                    </span>
                </h3>
                <p class="text-xs text-[var(--sm-text-muted)] mt-0.5">
                    Produk yang pernah disetujui Admin dan masuk katalog SiswaMart.
                </p>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-[var(--sm-primary-soft)] border-b-2 border-[var(--sm-border)]">
                    <tr>
                        <th class="px-4 py-3.5 text-xs font-bold text-[var(--sm-text-muted)] uppercase tracking-wider text-center w-12">
                            NO
                        </th>
                        <th class="px-5 py-3.5 text-xs font-bold text-[var(--sm-text-muted)] uppercase tracking-wider">
                            NAMA PRODUK
                        </th>
                        <th class="px-4 py-3.5 text-xs font-bold text-[var(--sm-text-muted)] uppercase tracking-wider">
                            KATEGORI
                        </th>
                        <th class="px-4 py-3.5 text-xs font-bold text-[var(--sm-text-muted)] uppercase tracking-wider text-center">
                            TANGGAL UPLOAD
                        </th>
                        <th class="px-4 py-3.5 text-xs font-bold text-[var(--sm-text-muted)] uppercase tracking-wider text-center">
                            TANGGAL DISETUJUI
                        </th>
                        <th class="px-4 py-3.5 text-xs font-bold text-[var(--sm-text-muted)] uppercase tracking-wider text-center">
                            RATING
                        </th>
                        <th class="px-4 py-3.5 text-xs font-bold text-[var(--sm-text-muted)] uppercase tracking-wider text-center">
                            TOTAL ULASAN
                        </th>
                        <th class="px-4 py-3.5 text-xs font-bold text-[var(--sm-text-muted)] uppercase tracking-wider text-center">
                            STATUS
                        </th>
                        <th class="px-5 py-3.5 text-xs font-bold text-[var(--sm-text-muted)] uppercase tracking-wider text-right">
                            AKSI
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[var(--sm-border)]/20">
                    @forelse($products as $prod)
                        @php
                            $reviewCount = $prod->total_ulasan ?? ($prod->ulasan ? $prod->ulasan->count() : 0);
                            $prodFotoUrl = ($prod->foto_utama && \Illuminate\Support\Facades\Storage::disk('public')->exists($prod->foto_utama))
                                ? asset('storage/' . $prod->foto_utama)
                                : null;
                        @endphp
                        <tr class="hover:bg-[var(--sm-primary-soft)]/50 transition-colors">
                            {{-- No --}}
                            <td class="px-4 py-4 text-center font-bold text-xs text-[var(--sm-text-muted)]">
                                {{ $products->firstItem() + $loop->index }}
                            </td>

                            {{-- Nama Produk & Harga --}}
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-3">
                                    @if($prodFotoUrl)
                                        <img src="{{ $prodFotoUrl }}"
                                             alt="{{ $prod->nama }}"
                                             class="w-11 h-11 rounded-xl object-cover border border-[var(--sm-border)] flex-shrink-0">
                                    @else
                                        <div class="w-11 h-11 rounded-xl bg-[var(--sm-primary-soft)] border border-[var(--sm-border)] text-[var(--sm-text-muted)] font-black text-sm flex items-center justify-center flex-shrink-0">
                                            {{ strtoupper(substr($prod->nama, 0, 1)) }}
                                        </div>
                                    @endif
                                    <div>
                                        <a href="{{ route('admin.produk.show', $prod) }}"
                                           class="font-bold text-[var(--sm-ink)] hover:text-[var(--sm-primary)] transition"
                                           title="Lihat detail moderasi produk">
                                            {{ $prod->nama }}
                                        </a>
                                        <div class="text-xs text-[var(--sm-text-muted)] font-medium mt-0.5">
                                            Rp {{ number_format($prod->harga, 0, ',', '.') }}
                                        </div>
                                    </div>
                                </div>
                            </td>

                            {{-- Kategori --}}
                            <td class="px-4 py-4">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold bg-[var(--sm-primary-soft)] text-[var(--sm-ink)] border border-[var(--sm-border)]/30">
                                    {{ $prod->kategori?->nama ?? '-' }}
                                </span>
                            </td>

                            {{-- Tanggal Upload --}}
                            <td class="px-4 py-4 text-center text-xs font-semibold text-[var(--sm-ink)] whitespace-nowrap">
                                {{ $prod->created_at ? $prod->created_at->format('d M Y, H:i') : '-' }}
                            </td>

                            {{-- Tanggal Disetujui --}}
                            <td class="px-4 py-4 text-center text-xs font-semibold text-emerald-700 whitespace-nowrap">
                                @if($prod->approved_at)
                                    <span class="inline-flex items-center gap-1 font-bold">
                                        <svg class="w-3.5 h-3.5 text-emerald-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                        {{ $prod->approved_at->format('d M Y, H:i') }}
                                    </span>
                                @else
                                    <span class="text-[var(--sm-text-muted)] italic">-</span>
                                @endif
                            </td>

                            {{-- Rating --}}
                            <td class="px-4 py-4 text-center">
                                @if($prod->avg_rating !== null)
                                    <button type="button"
                                            data-prod-id="{{ $prod->id }}"
                                            data-prod-name="{{ $prod->nama }}"
                                            data-prod-kategori="{{ $prod->kategori?->nama ?? 'Tanpa Kategori' }}"
                                            data-prod-foto="{{ $prodFotoUrl }}"
                                            data-prod-url="{{ route('admin.laporan.produk.rating', $prod->id) }}"
                                            data-prod-avg="{{ number_format($prod->avg_rating, 1) }}"
                                            data-prod-total="{{ (int) $reviewCount }}"
                                            @click="openModalFromButton($el)"
                                            class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-xl bg-amber-50 border border-amber-200 hover:bg-amber-100 hover:border-amber-300 transition cursor-pointer"
                                            title="Klik untuk melihat detail rating produk ini">
                                        <svg class="w-4 h-4 text-amber-500 flex-shrink-0 fill-current" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                        <span class="font-black text-xs text-[var(--sm-ink)]">
                                            {{ number_format($prod->avg_rating, 1) }}
                                        </span>
                                    </button>
                                @else
                                    <button type="button"
                                            data-prod-id="{{ $prod->id }}"
                                            data-prod-name="{{ $prod->nama }}"
                                            data-prod-kategori="{{ $prod->kategori?->nama ?? 'Tanpa Kategori' }}"
                                            data-prod-foto="{{ $prodFotoUrl }}"
                                            data-prod-url="{{ route('admin.laporan.produk.rating', $prod->id) }}"
                                            data-prod-avg=""
                                            data-prod-total="0"
                                            @click="openModalFromButton($el)"
                                            class="text-xs text-[var(--sm-text-muted)] italic hover:text-[var(--sm-primary)] hover:underline cursor-pointer"
                                            title="Klik untuk melihat detail rating produk ini">
                                        Belum ada rating
                                    </button>
                                @endif
                            </td>

                            {{-- Total Ulasan --}}
                            <td class="px-4 py-4 text-center text-xs font-bold text-[var(--sm-ink)]">
                                {{ number_format($prod->total_ulasan) }}
                            </td>

                            {{-- Status Produk --}}
                            <td class="px-4 py-4 text-center">
                                <x-ui.status-badge :status="$prod->status" />
                            </td>

                            {{-- Aksi --}}
                            <td class="px-5 py-4 text-right">
                                <button type="button"
                                        data-prod-id="{{ $prod->id }}"
                                        data-prod-name="{{ $prod->nama }}"
                                        data-prod-kategori="{{ $prod->kategori?->nama ?? 'Tanpa Kategori' }}"
                                        data-prod-foto="{{ $prodFotoUrl }}"
                                        data-prod-url="{{ route('admin.laporan.produk.rating', $prod->id) }}"
                                        data-prod-avg="{{ $prod->avg_rating !== null ? number_format($prod->avg_rating, 1) : '' }}"
                                        data-prod-total="{{ (int) $reviewCount }}"
                                        @click="openModalFromButton($el)"
                                        class="btn-outline !text-xs !py-1 !px-2.5 font-bold inline-flex items-center gap-1 cursor-pointer"
                                        title="Lihat Detail Rating Produk">
                                    <svg class="w-3.5 h-3.5 text-amber-500 flex-shrink-0 fill-current" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                    <span>Detail Rating</span>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-5 py-14 text-center text-[var(--sm-text-muted)]">
                                <div class="flex flex-col items-center justify-center gap-2">
                                    <svg class="w-10 h-10 text-[var(--sm-text-muted)]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                                    <p class="font-bold text-sm text-[var(--sm-ink)]">Belum ada riwayat produk disetujui</p>
                                    <p class="text-xs text-[var(--sm-text-muted)]">
                                        @if($search || $kategoriId || $uploadFrom || $uploadTo || $approvalFrom || $approvalTo)
                                            Tidak ada produk yang cocok dengan kriteria filter yang Anda tentukan.
                                        @else
                                            Penjual ini belum memiliki produk yang disetujui oleh Admin.
                                        @endif
                                    </p>
                                    @if($search || $kategoriId || $uploadFrom || $uploadTo || $approvalFrom || $approvalTo)
                                        <a href="{{ route('admin.laporan.penjual.detail', $penjual->id) }}" class="btn-outline !text-xs !py-1.5 !px-3 mt-2">
                                            Reset Filter
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($products->total() > 0)
            <x-admin.table-pagination :paginator="$products" />
        @endif
    </section>

    {{-- 5. Modal Detail Rating Produk (Alpine.js) --}}
    <div x-show="showModal"
         x-cloak
         @keydown.escape.window="closeModal()"
         class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-5 bg-black/50 backdrop-blur-xs"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">

        <div class="bg-[var(--sm-surface)] border-2 border-[var(--sm-border)] rounded-2xl shadow-[6px_6px_0px_0px_var(--sm-border)] w-full max-w-2xl max-h-[90vh] flex flex-col overflow-hidden"
             @click.outside="closeModal()"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 translate-y-4 scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 scale-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 translate-y-0 scale-100"
             x-transition:leave-end="opacity-0 translate-y-4 scale-95">

            {{-- 1. Informasi Produk & Header Modal --}}
            <div class="p-4 sm:p-5 border-b-2 border-[var(--sm-border)] bg-[var(--sm-primary-soft)] flex items-start justify-between gap-3 flex-shrink-0">
                <div class="flex items-center gap-3.5 min-w-0">
                    {{-- Thumbnail Produk --}}
                    <template x-if="selectedProd && selectedProd.foto_url">
                        <img :src="selectedProd.foto_url"
                             :alt="selectedProd ? selectedProd.nama : 'Produk'"
                             class="w-13 h-13 sm:w-14 sm:h-14 rounded-xl object-cover border-2 border-[var(--sm-border)] bg-[var(--sm-surface)] shadow-[2px_2px_0px_0px_var(--sm-border)] flex-shrink-0">
                    </template>
                    <template x-if="!selectedProd || !selectedProd.foto_url">
                        <div class="w-13 h-13 sm:w-14 sm:h-14 rounded-xl bg-[var(--sm-surface)] border-2 border-[var(--sm-border)] shadow-[2px_2px_0px_0px_var(--sm-border)] flex items-center justify-center flex-shrink-0 font-black text-lg text-[var(--sm-primary)]">
                            <span x-text="selectedProd && selectedProd.nama ? selectedProd.nama.substring(0, 1).toUpperCase() : '?'"></span>
                        </div>
                    </template>

                    <div class="min-w-0">
                        <div class="flex items-center gap-2">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold bg-[var(--sm-surface)] border border-[var(--sm-border)]/40 text-[var(--sm-ink)]">
                                <span x-text="selectedProd ? selectedProd.kategori : '-'"></span>
                            </span>
                            <span class="text-[11px] font-bold text-[var(--sm-text-muted)]" x-text="selectedProd ? 'ID #' + selectedProd.id : ''"></span>
                        </div>
                        <h3 class="text-base sm:text-lg font-black text-[var(--sm-ink)] mt-1 leading-snug line-clamp-1"
                            x-text="selectedProd ? selectedProd.nama : 'Detail Rating Produk'"></h3>
                    </div>
                </div>

                {{-- Tombol Tutup Header (Rounded Symmetrical, Tidak Lonjong) --}}
                <button type="button"
                        @click="closeModal()"
                        class="w-9 h-9 rounded-xl border-2 border-[var(--sm-border)] bg-[var(--sm-surface)] text-[var(--sm-ink)] hover:bg-[var(--sm-primary-soft)] hover:shadow-[2px_2px_0px_0px_var(--sm-border)] transition cursor-pointer flex items-center justify-center flex-shrink-0"
                        title="Tutup Modal (Esc)">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            {{-- 2. Modal Body (Scrollable tanpa scrolling halaman) --}}
            <div class="p-4 sm:p-5 overflow-y-auto space-y-5 flex-1">
                {{-- Ringkasan Rating & Distribusi --}}
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 p-4 rounded-xl border-2 border-[var(--sm-border)] bg-[var(--sm-surface)] shadow-[2px_2px_0px_0px_var(--sm-border)]">
                    {{-- Skor Rata-rata & Total --}}
                    <div class="flex flex-col items-center justify-center text-center p-2 border-b md:border-b-0 md:border-r border-[var(--sm-border)]/20">
                        <template x-if="selectedProd && selectedProd.avg_rating">
                            <div>
                                <div class="flex items-baseline justify-center gap-1">
                                    <span class="text-4xl font-black text-[var(--sm-ink)]" x-text="selectedProd.avg_rating"></span>
                                    <span class="text-xs font-bold text-[var(--sm-text-muted)]">/ 5.0</span>
                                </div>
                                <div class="flex text-amber-500 justify-center my-1.5">
                                    <template x-for="i in 5" :key="i">
                                        <svg class="w-4 h-4 fill-current" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                    </template>
                                </div>
                                <span class="text-xs text-[var(--sm-text-muted)] font-bold" x-text="(selectedProd.total_ulasan) + ' rating diterima'"></span>
                            </div>
                        </template>
                        <template x-if="!selectedProd || !selectedProd.avg_rating">
                            <div>
                                <span class="text-sm font-black text-[var(--sm-text-muted)] italic">Belum ada rating</span>
                                <p class="text-xs text-[var(--sm-text-muted)] mt-1">0 rating diterima</p>
                            </div>
                        </template>
                    </div>

                    {{-- Distribusi Bintang 1-5 --}}
                    <div class="col-span-2 space-y-1.5 text-xs font-bold">
                        <template x-for="stars in [5, 4, 3, 2, 1]" :key="stars">
                            <div class="flex items-center gap-2">
                                <span class="w-12 text-right text-[var(--sm-text-muted)] flex items-center justify-end gap-1 font-bold">
                                    <span x-text="stars"></span>
                                    <svg class="w-3.5 h-3.5 text-amber-500 fill-current" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                </span>
                                <div class="flex-1 h-2.5 rounded-full bg-[var(--sm-border)]/15 overflow-hidden">
                                    <div class="h-full bg-amber-400 rounded-full transition-all duration-300"
                                         :style="'width: ' + getPercent(stars) + '%'"></div>
                                </div>
                                <span class="w-14 text-right text-[var(--sm-text-muted)] text-[11px]" x-text="getCount(stars) + ' (' + getPercent(stars) + '%)'"></span>
                            </div>
                        </template>
                    </div>
                </div>

                {{-- 3. Daftar Seluruh Rating --}}
                <div class="space-y-3">
                    <div class="flex items-center justify-between border-b border-[var(--sm-border)]/20 pb-2">
                        <h4 class="text-xs font-black text-[var(--sm-ink)] uppercase tracking-wider flex items-center gap-2">
                            <svg class="w-4 h-4 text-[var(--sm-primary)]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>
                            <span>Daftar Seluruh Rating & Ulasan</span>
                        </h4>
                        <span class="text-xs font-bold px-2 py-0.5 rounded-full bg-[var(--sm-primary-soft)] text-[var(--sm-primary)] border border-[var(--sm-primary)]/20"
                              x-text="(ratings.length) + (totalRatings ? ' dari ' + totalRatings : '') + ' rating'"></span>
                    </div>

                    {{-- State: Sedang Memuat Awal --}}
                    <template x-if="loadingInitial">
                        <div class="p-8 text-center border-2 border-dashed border-[var(--sm-border)]/20 rounded-xl bg-[var(--sm-bg)]">
                            <div class="flex items-center justify-center gap-2 text-xs font-bold text-[var(--sm-text-muted)]">
                                <svg class="animate-spin w-4 h-4 text-[var(--sm-primary)]" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                                <span>Memuat daftar rating...</span>
                            </div>
                        </div>
                    </template>

                    {{-- State: Daftar Rating Tersedia --}}
                    <template x-if="!loadingInitial && ratings.length > 0">
                        <div class="space-y-3">
                            <template x-for="item in ratings" :key="item.id">
                                <div class="p-4 rounded-xl border-2 border-[var(--sm-border)]/30 bg-[var(--sm-surface)] space-y-2 hover:border-[var(--sm-border)] transition">
                                    {{-- Baris 1: Visual Bintang (5/5) & Tanggal --}}
                                    <div class="flex flex-wrap items-center justify-between gap-2">
                                        <div class="flex items-center gap-2">
                                            <div class="flex text-amber-500">
                                                <template x-for="s in 5" :key="s">
                                                    <svg class="w-4 h-4" :class="s <= item.rating ? 'fill-current text-amber-500' : 'fill-current text-slate-200'" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                                </template>
                                            </div>
                                            <span class="text-xs font-black text-[var(--sm-ink)]" x-text="item.rating + ' / 5'"></span>
                                        </div>
                                        <div class="flex items-center gap-1 text-[11px] font-semibold text-[var(--sm-text-muted)]">
                                            <span>Tanggal:</span>
                                            <span class="text-[var(--sm-ink)] font-bold" x-text="item.tanggal || item.tanggal_waktu"></span>
                                        </div>
                                    </div>

                                    {{-- Baris 2: Username Pengguna --}}
                                    <div class="text-xs flex items-center gap-1.5">
                                        <span class="font-bold text-[var(--sm-text-muted)]">Username:</span>
                                        <span class="font-extrabold text-[var(--sm-ink)]" x-text="item.guest_username"></span>
                                    </div>

                                    {{-- Baris 3: Ulasan / Komentar --}}
                                    <div>
                                        <template x-if="item.has_komentar && item.komentar">
                                            <div class="p-3 rounded-lg bg-[var(--sm-bg)] border border-[var(--sm-border)]/20 text-xs text-[var(--sm-ink)] leading-relaxed">
                                                <span class="block text-[10px] font-bold uppercase tracking-wider text-[var(--sm-text-muted)] mb-1">Ulasan:</span>
                                                <p x-text="item.komentar" class="whitespace-pre-line font-medium"></p>
                                            </div>
                                        </template>
                                        <template x-if="!item.has_komentar || !item.komentar">
                                            <div class="p-2.5 rounded-lg bg-[var(--sm-bg)]/60 border border-dashed border-[var(--sm-border)]/30 text-[11px] text-[var(--sm-text-muted)] italic">
                                                <span class="font-bold not-italic">Ulasan:</span> <em>Rating diberikan tanpa komentar tertulis.</em>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </template>

                            {{-- Tombol Muat Lebih Banyak (Pagination) --}}
                            <template x-if="hasMore">
                                <div class="pt-2">
                                    <button type="button"
                                            @click="loadMore()"
                                            :disabled="loadingMore"
                                            class="btn-outline w-full !py-2.5 !text-xs font-bold flex items-center justify-center gap-2 cursor-pointer disabled:opacity-50">
                                        <svg x-show="loadingMore" class="animate-spin w-4 h-4 text-[var(--sm-primary)]" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                                        <span x-text="loadingMore ? 'Memuat rating berikutnya...' : 'Muat Lebih Banyak Rating (' + (totalRatings - ratings.length) + ' sisa)'"></span>
                                    </button>
                                </div>
                            </template>
                        </div>
                    </template>

                    {{-- State: Kosong (Belum ada rating untuk produk ini) --}}
                    <template x-if="!loadingInitial && ratings.length === 0">
                        <div class="p-8 text-center border-2 border-dashed border-[var(--sm-border)]/30 rounded-2xl bg-[var(--sm-bg)]">
                            <div class="w-12 h-12 rounded-full bg-amber-100 text-amber-600 mx-auto flex items-center justify-center mb-3">
                                <svg class="w-6 h-6 fill-current" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                            </div>
                            <h5 class="text-sm font-bold text-[var(--sm-ink)]">Belum ada rating untuk produk ini</h5>
                            <p class="text-xs text-[var(--sm-text-muted)] mt-1">Produk ini belum menerima rating atau ulasan dari pengunjung.</p>
                        </div>
                    </template>
                </div>
            </div>

            {{-- 4. Modal Footer --}}
            <div class="p-4 border-t-2 border-[var(--sm-border)] bg-[var(--sm-surface)] flex justify-end flex-shrink-0">
                <button type="button" @click="closeModal()" class="btn-primary !rounded-xl !text-xs !py-2.5 !px-6 font-bold shadow-[2px_2px_0px_0px_var(--sm-border)]">
                    Tutup
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    function sellerRatingModal() {
        return {
            showModal: false,
            selectedProd: null,
            ratings: [],
            currentPage: 1,
            lastPage: 1,
            totalRatings: 0,
            hasMore: false,
            loadingInitial: false,
            loadingMore: false,
            errorMessage: null,

            openModalFromButton(btn) {
                if (!btn) return;
                const prodData = {
                    id: btn.getAttribute('data-prod-id'),
                    nama: btn.getAttribute('data-prod-name') || 'Detail Rating Produk',
                    kategori: btn.getAttribute('data-prod-kategori') || '-',
                    foto_url: btn.getAttribute('data-prod-foto') || null,
                    rating_url: btn.getAttribute('data-prod-url'),
                    avg_rating: btn.getAttribute('data-prod-avg') || null,
                    total_ulasan: parseInt(btn.getAttribute('data-prod-total') || '0', 10),
                    distribusi: { 5: 0, 4: 0, 3: 0, 2: 0, 1: 0 }
                };
                this.openRatingModal(prodData);
            },

            openRatingModal(prodData) {
                if (typeof prodData === 'string') {
                    try { prodData = JSON.parse(prodData); } catch (e) { prodData = {}; }
                }
                this.selectedProd = prodData || {};
                this.ratings = [];
                this.currentPage = 1;
                this.lastPage = 1;
                this.totalRatings = this.selectedProd.total_ulasan || 0;
                this.hasMore = false;
                this.errorMessage = null;
                this.showModal = true;
                this.loadingInitial = true;

                this.fetchRatings(1, false);
            },

            async fetchRatings(page, append = false) {
                if (!this.selectedProd || !this.selectedProd.rating_url) {
                    this.loadingInitial = false;
                    this.loadingMore = false;
                    return;
                }

                this.errorMessage = null;

                try {
                    const response = await fetch(`${this.selectedProd.rating_url}?page=${page}&per_page=5`, {
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });

                    if (!response.ok) throw new Error('Gagal memuat data rating.');

                    const data = await response.json();

                    if (data.success) {
                        if (data.summary) {
                            this.selectedProd.avg_rating = data.summary.avg_rating !== null ? Number(data.summary.avg_rating).toFixed(1) : null;
                            this.selectedProd.total_ulasan = data.summary.total_rating;
                            this.selectedProd.distribusi = data.summary.distribusi;
                            this.totalRatings = data.summary.total_rating;
                        }
                        if (data.product) {
                            this.selectedProd.foto_url = data.product.foto_url || this.selectedProd.foto_url;
                            this.selectedProd.nama = data.product.nama || this.selectedProd.nama;
                            this.selectedProd.kategori = data.product.kategori || this.selectedProd.kategori;
                        }

                        if (append) {
                            this.ratings = this.ratings.concat(data.ratings);
                        } else {
                            this.ratings = data.ratings || [];
                        }

                        if (data.pagination) {
                            this.currentPage = data.pagination.current_page;
                            this.lastPage = data.pagination.last_page;
                            this.hasMore = Boolean(data.pagination.has_more);
                        }
                    } else {
                        throw new Error(data.message || 'Gagal memuat data.');
                    }
                } catch (error) {
                    console.error('Error fetching ratings:', error);
                    this.errorMessage = error.message || 'Terjadi kesalahan saat memuat rating.';
                } finally {
                    this.loadingInitial = false;
                    this.loadingMore = false;
                }
            },

            loadMore() {
                if (this.loadingMore || !this.hasMore) return;
                this.loadingMore = true;
                this.fetchRatings(this.currentPage + 1, true);
            },

            closeModal() {
                this.showModal = false;
                this.selectedProd = null;
                this.ratings = [];
                this.loadingInitial = false;
                this.loadingMore = false;
                this.errorMessage = null;
            },

            getCount(stars) {
                if (!this.selectedProd || !this.selectedProd.distribusi) return 0;
                return this.selectedProd.distribusi[stars] || 0;
            },

            getPercent(stars) {
                const total = this.totalRatings || (this.selectedProd && this.selectedProd.total_ulasan) || 0;
                if (!total || total <= 0) return 0;
                const count = this.getCount(stars);
                return Math.round((count / total) * 100);
            }
        };
    }
    window.sellerRatingModal = sellerRatingModal;
    document.addEventListener('alpine:init', () => {
        if (window.Alpine) {
            window.Alpine.data('sellerRatingModal', sellerRatingModal);
        }
    });
</script>
@endsection
