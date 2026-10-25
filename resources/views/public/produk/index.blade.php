@extends('layouts.public')

@section('title', 'Katalog Produk | SiswaMart')
@section('meta_description', 'Cari dan temukan berbagai produk makanan & minuman karya siswa di SiswaMart.')

@section('content')

<div class="bg-[var(--sm-bg)] pt-8 pb-6">
    <div class="max-w-[1400px] mx-auto px-6 lg:px-[60px]">

        {{-- ── Breadcrumb ────────────────────────────────────────── --}}
        <nav class="flex items-center gap-2 text-xs font-extrabold text-[var(--sm-text-muted)] mb-6 uppercase tracking-widest" aria-label="Breadcrumb">
            <a href="{{ route('public.home') }}" class="hover:text-[var(--sm-primary)] transition-colors">BERANDA</a>
            <span class="text-[var(--sm-border)]">/</span>
            <span class="text-[var(--sm-ink)]">KATALOG PRODUK</span>
        </nav>

        {{-- ── Header & Filter Block ───────────────────────────────── --}}
        <div class="bg-white border-2 border-[var(--sm-border)] shadow-solid-md rounded-3xl p-6 sm:p-8 mb-8">
            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6">
                
                {{-- Title & Subtitle --}}
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-3 mb-2 flex-wrap">
                        <h1 class="text-3xl sm:text-4xl lg:text-5xl font-black text-[var(--sm-ink)] uppercase tracking-tighter">
                            @if($search && $selectedKategori)
                                "<span class="text-[var(--sm-primary)]">{{ $search }}</span>"
                            @elseif($search)
                                Hasil: "<span class="text-[var(--sm-primary)]">{{ $search }}</span>"
                            @elseif($selectedKategori)
                                {{ $kategoriList->firstWhere('slug', $selectedKategori)?->nama ?? $selectedKategori }}
                            @else
                                Katalog Makanan & Minuman
                            @endif
                        </h1>
                        <span class="inline-flex items-center px-3 py-1 bg-[var(--sm-primary-soft)] text-[var(--sm-primary)] border-2 border-[var(--sm-primary)] rounded-full text-xs font-black uppercase tracking-wider">
                            {{ $produk->total() }} Produk
                        </span>
                    </div>
                    <p class="text-sm sm:text-base font-bold text-[var(--sm-text-muted)]">
                        Jelajahi berbagai pilihan jajanan, makanan berat, dan minuman segar buatan teman sekolah.
                    </p>
                </div>

                {{-- Action Controls: Sort & Filter Button --}}
                <div class="flex flex-wrap items-center gap-3 shrink-0">
                    <form action="{{ route('public.produk.index') }}" method="GET" class="flex items-center gap-3">
                        @if($search)<input type="hidden" name="q" value="{{ $search }}">@endif
                        @if($selectedKategori)<input type="hidden" name="kategori" value="{{ $selectedKategori }}">@endif

                        <div class="flex items-center gap-2">
                            <span class="text-xs font-extrabold text-[var(--sm-text-muted)] uppercase tracking-wider hidden sm:inline">Urutkan:</span>
                            <select name="sort" onchange="this.form.submit()"
                                    class="pl-4 pr-9 py-2.5 text-xs font-black uppercase tracking-wider border-2 border-[var(--sm-border)] bg-white rounded-full shadow-solid-sm focus:outline-none focus:ring-0 appearance-none cursor-pointer hover:-translate-y-0.5 transition-transform">
                                <option value="terbaru" {{ ($sort ?? '') == 'terbaru' ? 'selected' : '' }}>Terbaru</option>
                                <option value="terpopuler" {{ ($sort ?? '') == 'terpopuler' ? 'selected' : '' }}>Terpopuler</option>
                                <option value="termurah" {{ ($sort ?? '') == 'termurah' ? 'selected' : '' }}>Termurah</option>
                                <option value="termahal" {{ ($sort ?? '') == 'termahal' ? 'selected' : '' }}>Termahal</option>
                            </select>
                        </div>
                    </form>

                    <a href="{{ route('public.kategori.index') }}"
                       class="inline-flex items-center gap-2 px-5 py-2.5 text-xs font-black uppercase tracking-wider bg-[var(--sm-surface)] text-[var(--sm-ink)] border-2 border-[var(--sm-border)] rounded-full shadow-solid-sm hover:shadow-solid-md hover:-translate-y-0.5 transition-all whitespace-nowrap">
                        <svg class="w-4 h-4 text-[var(--sm-primary)]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                        </svg>
                        Semua Filter
                    </a>
                </div>

            </div>

            {{-- ── Category Filter Chips Row ─────────────────────────── --}}
            <div class="mt-6 pt-6 border-t-2 border-dashed border-[var(--sm-border)]/20 flex items-center gap-3 overflow-x-auto scrollbar-hide">
                <a href="{{ route('public.produk.index', array_filter(['q' => $search, 'sort' => $sort ?? null])) }}"
                   class="cat-chip {{ !$selectedKategori ? 'bg-[var(--sm-primary)] text-white shadow-solid-sm -translate-y-0.5' : 'bg-white text-[var(--sm-ink)] shadow-sm hover:shadow-solid-sm hover:-translate-y-0.5' }} flex-shrink-0 inline-flex items-center gap-2 px-5 py-2.5 rounded-full text-xs font-extrabold border-2 border-[var(--sm-border)] transition-all whitespace-nowrap uppercase tracking-wider">
                    <x-public.home-icon name="package" class="h-4 w-4" aria-hidden="true" /> Semua
                </a>
                @foreach($kategoriList as $kat)
                    @php
                        $isActive = $selectedKategori === $kat->slug;
                        $catIcons = [
                            'makanan' => 'burger',
                            'makanan-berat' => 'burger',
                            'minuman' => 'glass-full',
                            'snack'   => 'cookie',
                            'dessert' => 'cake',
                        ];
                        $icon = $catIcons[$kat->slug] ?? 'package';
                    @endphp
                    <a href="{{ route('public.produk.index', array_filter(['kategori' => $kat->slug, 'q' => $search, 'sort' => $sort ?? null])) }}"
                       class="cat-chip {{ $isActive ? 'bg-[var(--sm-primary)] text-white shadow-solid-sm -translate-y-0.5' : 'bg-white text-[var(--sm-ink)] shadow-sm hover:shadow-solid-sm hover:-translate-y-0.5' }} flex-shrink-0 inline-flex items-center gap-2 px-5 py-2.5 rounded-full text-xs font-extrabold border-2 border-[var(--sm-border)] transition-all whitespace-nowrap uppercase tracking-wider">
                        <x-public.home-icon :name="$icon" class="h-4 w-4" aria-hidden="true" /> {{ $kat->nama }}
                        @if($kat->produk_count > 0)
                            <span class="px-2 py-0.5 rounded-full text-[10px] {{ $isActive ? 'bg-white/20 text-white' : 'bg-[var(--sm-bg)] text-[var(--sm-text-muted)]' }}">
                                {{ $kat->produk_count }}
                            </span>
                        @endif
                    </a>
                @endforeach
            </div>

        </div>

    </div>
</div>

{{-- Results Grid --}}
<main class="max-w-[1400px] mx-auto px-6 lg:px-[60px] pb-20 bg-[var(--sm-bg)] min-h-[50vh]">
    @if($produk->isEmpty())
        <div class="empty-state max-w-2xl">
            <div class="text-7xl mb-4">
                <x-public.home-icon name="package" :size="72" aria-hidden="true" />
            </div>
            <h3 class="text-2xl sm:text-3xl font-black text-[var(--sm-ink)] mb-3 uppercase tracking-tight">Produk tidak ditemukan</h3>
            <p class="text-sm sm:text-base font-bold text-[var(--sm-text-muted)] mb-8">
                @if($search)
                        Belum ada produk yang cocok dengan "<strong>{{ $search }}</strong>". Coba kata kunci lain, ya.
                @else
                    Belum ada produk aktif di kategori ini.
                @endif
            </p>
            <a href="{{ route('public.produk.index') }}"
               class="btn-primary inline-flex items-center gap-2 px-8 py-3.5 text-sm font-black tracking-wider uppercase bg-[var(--sm-primary)] text-white hover:bg-[var(--sm-primary-hover)]">
                ATUR ULANG PENCARIAN
            </a>
        </div>
    @else
        <div class="product-card-grid grid grid-cols-1 gap-5 sm:grid-cols-2 sm:gap-6 lg:grid-cols-4">
            @foreach($produk as $item)
                <x-public.product-card :produk="$item" />
            @endforeach
        </div>
        
        <div class="mt-14 flex justify-center">
            {{ $produk->links() }}
        </div>
    @endif
</main>

@endsection

