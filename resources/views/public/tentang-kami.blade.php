@extends('layouts.public')

@section('title', 'Tentang Kami | SiswaMart')
@section('meta_description', 'Kenali SiswaMart Digital Menu, katalog dan platform promosi kuliner siswa di lingkungan sekolah.')

@section('content')

<main class="bg-[var(--sm-bg)] min-h-screen overflow-hidden">
    <div class="max-w-[1400px] mx-auto px-6 lg:px-[60px]">
        <nav class="pt-8 flex items-center gap-2 text-sm text-[var(--sm-text-muted)] font-bold uppercase tracking-widest" aria-label="Breadcrumb">
            <a href="{{ route('public.home') }}" class="hover:text-[var(--sm-primary)] transition-colors">BERANDA</a>
            <span class="text-[var(--sm-border)]">/</span>
            <span class="text-[var(--sm-ink)]">TENTANG KAMI</span>
        </nav>

        <section class="relative py-6 md:py-8 lg:py-10">
            <div class="absolute right-0 top-16 w-32 h-32 bg-[var(--sm-primary-soft)] border-4 border-[var(--sm-border)] rounded-full shadow-solid-md rotate-12 hidden md:block"></div>
            <div class="absolute w-16 h-16 bg-[var(--sm-surface)] border-4 border-[var(--sm-border)] rounded-full shadow-solid-sm -rotate-12 hidden md:block" style="right: 6rem; bottom: 2.5rem;"></div>

            <x-public.page-header class="relative z-10 max-w-4xl">
                <x-slot:title>Tentang <span class="text-[var(--sm-primary)]">Kami</span></x-slot:title>
                <x-slot:description>SiswaMart adalah katalog digital yang membantu makanan dan minuman buatan siswa lebih mudah ditemukan di lingkungan sekolah.</x-slot:description>
            </x-public.page-header>
        </section>

        <section class="grid grid-cols-2 gap-3 sm:gap-6 lg:gap-8 pb-16 items-stretch">
            <div class="min-w-0">
                <div class="h-full bg-[var(--sm-primary)] border-4 border-[var(--sm-border)] rounded-[40px] p-4 sm:p-6 md:p-7 relative z-10 shadow-solid-lg transform -rotate-1 hover:-translate-y-2 transition-transform duration-300">
                    <span class="text-3xl sm:text-5xl block mb-3 sm:mb-4" aria-hidden="true">01</span>
                    <h2 class="text-lg sm:text-3xl md:text-4xl font-black uppercase tracking-tighter leading-[0.95] mb-3 sm:mb-4">Mengapa SiswaMart Dibuat?</h2>
                    <p class="text-sm sm:text-base font-bold leading-relaxed">
                        Promosi produk kuliner siswa masih sering dilakukan secara konvensional: menawarkan dari kelas ke kelas, mengandalkan pesan berantai WhatsApp, atau memasang pengumuman fisik. Cara ini membuat jangkauan promosi menjadi terbatas dan informasi produk tidak terpusat.
                    </p>
                </div>
            </div>

            <div class="min-w-0">
                <div class="h-full bg-white border-4 border-[var(--sm-border)] rounded-[40px] p-4 sm:p-6 md:p-7 relative z-10 shadow-solid-md transform rotate-1 hover:-translate-y-2 transition-transform duration-300">
                    <span class="text-3xl sm:text-5xl block mb-3 sm:mb-4 text-[var(--sm-primary)]" aria-hidden="true">02</span>
                    <h2 class="text-lg sm:text-3xl md:text-4xl font-black uppercase tracking-tighter leading-[0.95] mb-3 sm:mb-4">Solusi Kami</h2>
                    <p class="text-sm sm:text-base font-bold text-[var(--sm-text-muted)] leading-relaxed">
                        SiswaMart hadir sebagai katalog dan platform promosi kuliner siswa yang terpusat. Pengunjung dapat menemukan dan melihat produk dalam satu tempat. SiswaMart tidak menyediakan checkout langsung, komunikasi pemesanan dilakukan melalui WhatsApp dengan penjual.
                    </p>
                </div>
            </div>
        </section>

        <section class="pb-24">
            <div class="flex flex-col md:flex-row md:items-end justify-between gap-5 mb-8">
                <div>
                    <span class="text-sm font-black uppercase tracking-[0.2em] text-[var(--sm-primary)]">Bersama di sekolah</span>
                    <h2 class="text-4xl md:text-5xl font-black uppercase tracking-tighter leading-[0.95] mt-3 max-w-3xl">Untuk Ekosistem Sekolah</h2>
                </div>
                <p class="text-base font-bold text-[var(--sm-text-muted)] max-w-md">Satu katalog untuk mempertemukan produk, pembeli, dan komunitas sekolah.</p>
            </div>

            <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-5 items-stretch">
                <div class="h-full bg-[var(--sm-primary-soft)] border-4 border-[var(--sm-border)] rounded-[40px] p-4 sm:p-6 shadow-solid-md transform hover:-translate-y-2 transition-transform duration-300">
                    <h3 class="text-base sm:text-xl md:text-2xl font-black uppercase tracking-tight leading-tight mb-2 sm:mb-3">Siswa Penjual</h3>
                    <p class="text-xs sm:text-sm md:text-base font-bold leading-relaxed">Mendapat ruang promosi yang lebih terpusat untuk memperkenalkan produk kuliner kepada warga sekolah.</p>
                </div>
                <div class="h-full bg-[var(--sm-surface)] border-4 border-[var(--sm-border)] rounded-[40px] p-4 sm:p-6 shadow-solid-md transform hover:-translate-y-2 transition-transform duration-300">
                    <h3 class="text-base sm:text-xl md:text-2xl font-black uppercase tracking-tight leading-tight mb-2 sm:mb-3">Pembeli</h3>
                    <p class="text-xs sm:text-sm md:text-base font-bold leading-relaxed">Lebih mudah menemukan pilihan makanan dan minuman karya siswa dalam satu katalog.</p>
                </div>
                <div class="h-full bg-[var(--sm-primary-soft)] border-4 border-[var(--sm-border)] rounded-[40px] p-4 sm:p-6 shadow-solid-md transform hover:-translate-y-2 transition-transform duration-300">
                    <h3 class="text-base sm:text-xl md:text-2xl font-black uppercase tracking-tight leading-tight mb-2 sm:mb-3">Guru</h3>
                    <p class="text-xs sm:text-sm md:text-base font-bold leading-relaxed">Dapat melihat aktivitas promosi produk siswa melalui platform yang lebih rapi dan terpusat.</p>
                </div>
                <div class="h-full bg-[var(--sm-primary-soft)] border-4 border-[var(--sm-border)] rounded-[40px] p-4 sm:p-6 shadow-solid-md transform hover:-translate-y-2 transition-transform duration-300">
                    <h3 class="text-base sm:text-xl md:text-2xl font-black uppercase tracking-tight leading-tight mb-2 sm:mb-3">Staf Sekolah</h3>
                    <p class="text-xs sm:text-sm md:text-base font-bold leading-relaxed">Memiliki media informasi bersama untuk mendukung ekosistem kuliner siswa di sekolah.</p>
                </div>
            </div>
        </section>
    </div>
</main>

@endsection

