@extends('layouts.public')

@section('title', 'Cara Menghubungi Penjual — SiswaMart')
@section('meta_description', 'Jelajahi produk siswa SiswaMart, baca ulasan, dan hubungi penjual secara langsung melalui WhatsApp.')

@section('content')

<main class="bg-[var(--sm-bg)] min-h-screen">
    {{-- ─── Breadcrumb ──────────────────────────────────────────── --}}
    <div class="max-w-[1400px] mx-auto px-6 lg:px-[60px] pt-8">
        <nav class="flex items-center gap-2 text-sm text-[var(--sm-text-muted)] font-bold uppercase tracking-widest" aria-label="Breadcrumb">
            <a href="{{ route('public.home') }}" class="hover:text-[var(--sm-primary)] transition-colors">BERANDA</a>
            <span class="text-[var(--sm-border)]">/</span>
            <span class="text-[var(--sm-ink)] truncate max-w-[200px] sm:max-w-xs">CARA PEMESANAN</span>
        </nav>
    </div>

    {{-- ─── Cara Pemesanan Header ─────────────────────────────────────────────── --}}
    <section class="max-w-[1400px] mx-auto px-6 lg:px-[60px] py-16 lg:py-24">
        <div class="text-center max-w-4xl mx-auto mb-20 relative">
            {{-- Decorative elements --}}
            <div class="absolute -top-10 -left-10 w-24 h-24 bg-[var(--sm-primary-soft)] border-4 border-[var(--sm-border)] rounded-full shadow-solid-sm z-0 hidden md:block transform -rotate-12"></div>
            <div class="absolute -bottom-10 -right-10 w-32 h-32 bg-[var(--sm-surface)] border-4 border-[var(--sm-border)] rounded-full shadow-solid-sm z-0 hidden md:block transform rotate-12"></div>
            
            <x-public.page-header class="relative z-10">
                <x-slot:title>Gimana Cara Menghubungi Penjual?</x-slot:title>
                <x-slot:description>Lihat produk, baca ulasan, lalu lanjutkan komunikasi langsung.</x-slot:description>
            </x-public.page-header>
        </div>

        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6 lg:gap-10 relative">
            
            {{-- Step 1 --}}
            <div class="bg-[var(--sm-bg)] border-4 border-[var(--sm-border)] p-4 sm:p-6 lg:p-8 rounded-[40px] relative z-10 text-center shadow-solid-md transform hover:-translate-y-2 transition-transform duration-300">
                <div class="absolute -top-3 -right-3 sm:-top-4 sm:-right-4 w-10 h-10 sm:w-12 sm:h-12 bg-white border-2 sm:border-4 border-[var(--sm-border)] rounded-full flex items-center justify-center font-black text-lg sm:text-2xl text-[var(--sm-ink)] shadow-solid-sm transform rotate-12">1</div>
                <div class="w-14 h-14 sm:w-20 sm:h-20 bg-white rounded-full flex items-center justify-center mb-4 sm:mb-6 lg:mb-8 mx-auto shadow-[4px_4px_0px_0px_var(--sm-border)] border-2 sm:border-4 border-[var(--sm-border)] transform -rotate-6">
                    <x-public.home-icon name="search" :size="40" aria-hidden="true" />
                </div>
                <h3 class="text-base sm:text-xl lg:text-2xl font-black text-[var(--sm-ink)] mb-2 sm:mb-4 uppercase tracking-tighter">Temukan Produk</h3>
                <p class="text-xs sm:text-sm lg:text-base text-[var(--sm-text-muted)] font-bold leading-relaxed">
                    Jelajahi produk yang dipromosikan oleh siswa SMKN 11.
                </p>
            </div>

            {{-- Step 2 --}}
            <div class="bg-[var(--sm-primary-soft)] border-4 border-[var(--sm-border)] p-4 sm:p-6 lg:p-8 rounded-[40px] relative z-10 text-center shadow-solid-md transform hover:-translate-y-2 transition-transform duration-300 lg:translate-y-8">
                <div class="absolute -top-3 -right-3 sm:-top-4 sm:-right-4 w-10 h-10 sm:w-12 sm:h-12 bg-white border-2 sm:border-4 border-[var(--sm-border)] rounded-full flex items-center justify-center font-black text-lg sm:text-2xl text-[var(--sm-ink)] shadow-solid-sm transform -rotate-12">2</div>
                <div class="w-14 h-14 sm:w-20 sm:h-20 bg-white rounded-full flex items-center justify-center mb-4 sm:mb-6 lg:mb-8 mx-auto shadow-[4px_4px_0px_0px_var(--sm-border)] border-2 sm:border-4 border-[var(--sm-border)] transform rotate-6">
                    <x-public.home-icon name="star" :size="40" aria-hidden="true" />
                </div>
                <h3 class="text-base sm:text-xl lg:text-2xl font-black text-[var(--sm-ink)] mb-2 sm:mb-4 uppercase tracking-tighter">Lihat Detail & Ulasan</h3>
                <p class="text-xs sm:text-sm lg:text-base text-[var(--sm-text-muted)] font-bold leading-relaxed">
                    Baca deskripsi, harga, status produk, dan komentar pengunjung.
                </p>
            </div>

            {{-- Step 3 --}}
            <div class="bg-[var(--sm-primary-soft)] border-4 border-[var(--sm-border)] p-4 sm:p-6 lg:p-8 rounded-[40px] relative z-10 text-center shadow-solid-md transform hover:-translate-y-2 transition-transform duration-300">
                <div class="absolute -top-3 -right-3 sm:-top-4 sm:-right-4 w-10 h-10 sm:w-12 sm:h-12 bg-white border-2 sm:border-4 border-[var(--sm-border)] rounded-full flex items-center justify-center font-black text-lg sm:text-2xl text-[var(--sm-ink)] shadow-solid-sm transform rotate-12">3</div>
                <div class="w-14 h-14 sm:w-20 sm:h-20 bg-white rounded-full flex items-center justify-center mb-4 sm:mb-6 lg:mb-8 mx-auto shadow-[4px_4px_0px_0px_var(--sm-border)] border-2 sm:border-4 border-[var(--sm-border)] transform -rotate-6">
                    <x-public.home-icon name="brand-whatsapp" :size="40" aria-hidden="true" />
                </div>
                <h3 class="text-base sm:text-xl lg:text-2xl font-black text-[var(--sm-ink)] mb-2 sm:mb-4 uppercase tracking-tighter">Chat Penjual</h3>
                <p class="text-white text-xs sm:text-sm lg:text-base font-bold leading-relaxed drop-shadow-md">
                    Gunakan tombol WhatsApp pada halaman detail produk.
                </p>
            </div>

            {{-- Step 4 --}}
            <div class="bg-[var(--sm-surface)] border-4 border-[var(--sm-border)] p-4 sm:p-6 lg:p-8 rounded-[40px] relative z-10 text-center shadow-solid-md transform hover:-translate-y-2 transition-transform duration-300 lg:translate-y-8">
                <div class="absolute -top-3 -right-3 sm:-top-4 sm:-right-4 w-10 h-10 sm:w-12 sm:h-12 bg-white border-2 sm:border-4 border-[var(--sm-border)] rounded-full flex items-center justify-center font-black text-lg sm:text-2xl text-[var(--sm-ink)] shadow-solid-sm transform -rotate-12">4</div>
                <div class="w-14 h-14 sm:w-20 sm:h-20 bg-white rounded-full flex items-center justify-center mb-4 sm:mb-6 lg:mb-8 mx-auto shadow-[4px_4px_0px_0px_var(--sm-border)] border-2 sm:border-4 border-[var(--sm-border)] transform rotate-6">
                    <x-public.home-icon name="circle-check" :size="40" aria-hidden="true" />
                </div>
                <h3 class="text-base sm:text-xl lg:text-2xl font-black text-[var(--sm-ink)] mb-2 sm:mb-4 uppercase tracking-tighter">Atur Langsung</h3>
                <p class="text-xs sm:text-sm lg:text-base text-[var(--sm-text-muted)] font-bold leading-relaxed">
                    Tanyakan ketersediaan dan sepakati hal selanjutnya langsung dengan penjual. SiswaMart tidak memproses transaksi.
                </p>
            </div>
        </div>
    </section>

    {{-- ─── CTA Bottom ──────────────────────────────────────────────────── --}}
    <section class="max-w-[1000px] mx-auto px-6 lg:px-[60px] pb-24">
        <div class="bg-[var(--sm-primary)] border-4 border-[var(--sm-border)] rounded-[40px] px-8 py-16 text-center shadow-[8px_8px_0px_0px_var(--sm-border)] transform -rotate-1 relative overflow-hidden">
            {{-- Background blobs --}}
            <div class="absolute top-0 left-0 w-full h-full opacity-10 pointer-events-none">
                <div class="absolute top-10 left-10 w-32 h-32 bg-white rounded-full mix-blend-overlay"></div>
                <div class="absolute bottom-10 right-10 w-48 h-48 bg-white rounded-full mix-blend-overlay"></div>
            </div>

            <h2 class="text-4xl lg:text-5xl font-black text-white mb-6 uppercase tracking-tighter relative z-10">
                Sedang Cari Produk?
            </h2>
            <p class="text-white/90 text-lg sm:text-xl font-bold mb-10 max-w-xl mx-auto relative z-10">
                Temukan produk karya siswa dan hubungi penjual melalui WhatsApp.
            </p>
            <a href="{{ route('public.kategori.index') }}" class="inline-flex items-center gap-3 px-10 py-5 bg-white text-[var(--sm-ink)] border-4 border-[var(--sm-border)] rounded-2xl shadow-[4px_4px_0px_0px_var(--sm-border)] font-black text-xl hover:-translate-y-1 hover:shadow-[6px_6px_0px_0px_var(--sm-border)] transition-all uppercase tracking-wider relative z-10 transform rotate-1">
                LIHAT KATALOG
            </a>
        </div>
    </section>
</main>

@endsection

