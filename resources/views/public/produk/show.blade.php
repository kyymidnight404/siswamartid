@extends('layouts.public')

@section('title', $produk->nama . ' — SiswaMart')

@section('meta_description', 'Lihat detail ' . $produk->nama . ' dari ' . ($produk->penjual->nama_toko ?? 'SiswaMart') . '. ' . \Illuminate\Support\Str::limit($produk->deskripsi ?? '', 120))

@section('content')

@php
    /*
    |--------------------------------------------------------------------------
    | WhatsApp
    |--------------------------------------------------------------------------
    */

    $rawWaNumber = $produk->penjual->nomor_whatsapp ?? '';

    // Bersihkan nomor dari spasi, tanda +, strip, dan karakter lainnya.
    $waNumber = preg_replace('/[^0-9]/', '', $rawWaNumber);

    // 08xxxxxxxx -> 628xxxxxxxx
    if (str_starts_with($waNumber, '0')) {
        $waNumber = '62' . substr($waNumber, 1);
    }

    $waMsg = "Halo, saya tertarik dengan produk:\n*"
        . $produk->nama
        . "*\n\nApakah produk ini masih tersedia?";

    $waLink = $waNumber
        ? 'https://wa.me/' . $waNumber . '?text=' . rawurlencode($waMsg)
        : '#';
@endphp


<main class="max-w-[1400px] mx-auto px-4 sm:px-6 lg:px-[60px] py-6 pb-28 sm:py-12 sm:pb-32 lg:py-16">

    {{-- ============================================================
         BREADCRUMB
         ============================================================ --}}
    <nav
        class="flex flex-wrap items-center gap-2 text-sm text-[var(--sm-text-muted)] font-bold mb-8 uppercase tracking-widest"
        aria-label="Breadcrumb"
    >
        <a
            href="{{ route('public.home') }}"
            class="hover:text-[var(--sm-primary)] transition-colors"
        >
            BERANDA
        </a>

        <span class="text-[var(--sm-border)]">/</span>

        @if($produk->kategori)

            <a
                href="{{ route('public.kategori.show', $produk->kategori->slug) }}"
                class="hover:text-[var(--sm-primary)] transition-colors"
            >
                {{ $produk->kategori->nama }}
            </a>

            <span class="text-[var(--sm-border)]">/</span>

        @endif

        <span class="text-[var(--sm-ink)] truncate max-w-[200px] sm:max-w-xs">
            {{ $produk->nama }}
        </span>
    </nav>


    {{-- ============================================================
         PRODUCT DETAIL
         ============================================================ --}}
    <div
        class="bg-[var(--sm-surface)] border-2 lg:border-4 border-[var(--sm-border)] rounded-[24px] lg:rounded-[40px] overflow-hidden shadow-solid-lg mb-10 lg:mb-16 relative z-10"
    >

        <div class="flex flex-col lg:flex-row">

            {{-- ====================================================
                 FOTO PRODUK
                 ==================================================== --}}
            <div
                class="lg:w-[45%] xl:w-[50%] bg-[var(--sm-primary-soft)] relative flex items-center justify-center min-h-[280px] sm:min-h-[360px] lg:min-h-[600px] p-5 sm:p-8 lg:p-12 border-b-2 lg:border-b-0 lg:border-r-4 border-[var(--sm-border)]"
            >

                @php
                    $detailCategory = $produk->kategori->slug ?? '';
                    $detailIcon = ['makanan' => 'burger', 'makanan-berat' => 'burger', 'minuman' => 'glass-full', 'snack' => 'cookie', 'dessert' => 'cake'][$detailCategory] ?? 'package';
                    $detailTint = ['makanan' => 'var(--sm-primary-soft)', 'makanan-berat' => 'var(--sm-primary-soft)', 'minuman' => 'var(--sm-primary-soft)', 'snack' => 'var(--sm-primary-soft)', 'dessert' => 'var(--sm-primary-soft)'][$detailCategory] ?? 'var(--sm-primary-soft)';
                @endphp
                @if($produk->foto_utama)

                    <img
                        src="{{ asset('storage/' . $produk->foto_utama) }}"
                        alt="{{ $produk->nama }}"
                        class="max-w-full max-h-[340px] lg:max-h-[500px] object-contain drop-shadow-2xl hover:scale-105 transition-transform duration-500"
                        loading="eager"
                        onerror="this.onerror=null;this.hidden=true;this.parentElement.querySelector('[data-detail-fallback]').hidden=false;"
                    >
                @endif
                <div data-detail-fallback @if($produk->foto_utama) hidden @endif class="product-detail-fallback" style="--product-placeholder: {{ $detailTint }}" role="img" aria-label="Ilustrasi kategori {{ $produk->kategori->nama ?? 'produk' }}">
                    <span class="product-detail-fallback__icon" aria-hidden="true"><x-public.home-icon :name="$detailIcon" :size="84" :stroke-width="1.5" /></span>
                    <span class="text-sm font-bold text-[var(--sm-text-muted)]">{{ $produk->kategori->nama ?? 'Produk SiswaMart' }}</span>
                </div>


                {{-- Label promosi --}}
                @if($produk->label_promosi)

                    <div class="absolute top-6 left-6 rotate-[-5deg]">

                        <span
                            class="inline-block bg-[var(--sm-primary)] text-white border-2 border-[var(--sm-border)] shadow-solid-sm text-sm font-black uppercase tracking-widest px-4 py-2 rounded-full"
                        >
                            {{ $produk->label_promosi }}
                        </span>

                    </div>

                @endif

            </div>


            {{-- ====================================================
                 DETAIL PRODUK
                 ==================================================== --}}
            <div
                class="lg:w-[55%] xl:w-[50%] p-5 sm:p-8 md:p-12 xl:p-16 flex flex-col bg-white"
            >

                {{-- Kategori --}}
                <div class="mb-4">

                    <a
                        href="{{ route('public.kategori.show', $produk->kategori->slug ?? '') }}"
                        class="inline-flex items-center text-xs font-black uppercase tracking-widest text-[var(--sm-ink)] bg-[var(--sm-surface)] border-2 border-[var(--sm-border)] shadow-[2px_2px_0px_0px_var(--sm-border)] px-4 py-1.5 rounded-full hover:translate-x-0.5 hover:-translate-y-0.5 transition-transform"
                    >
                        {{ $produk->kategori->nama ?? 'Umum' }}
                    </a>

                </div>


                {{-- Nama Produk --}}
                <h1
                    class="text-3xl sm:text-4xl md:text-5xl lg:text-6xl font-black text-[var(--sm-ink)] leading-[1.1] mb-4 sm:mb-6 uppercase tracking-tighter"
                >
                    {{ $produk->nama }}
                </h1>


                {{-- Harga --}}
                <p
                    class="text-3xl sm:text-4xl md:text-5xl font-black text-[var(--sm-primary)] mb-5 sm:mb-8 drop-shadow-sm"
                >
                    Rp {{ number_format($produk->harga, 0, ',', '.') }}
                </p>


                {{-- =================================================
                     STATUS + RATING + VIEWS
                     ================================================= --}}
                <div
                    class="flex flex-wrap items-center gap-2 sm:gap-4 mb-6 sm:mb-10"
                >

                    @if($produk->stok === 'tersedia')

                        <span
                            class="inline-flex items-center gap-2 text-sm font-black text-[var(--sm-primary)] bg-[var(--sm-primary-soft)] border-2 border-[var(--sm-primary)] shadow-[2px_2px_0px_0px_var(--sm-primary)] px-4 py-2 rounded-full uppercase tracking-wider"
                        >

                            <svg
                                class="w-4 h-4"
                                fill="currentColor"
                                viewBox="0 0 20 20"
                            >
                                <path
                                    fill-rule="evenodd"
                                    d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 1.414z"
                                    clip-rule="evenodd"
                                />
                            </svg>

                            Tersedia

                        </span>

                    @elseif($produk->stok === 'po')

                        <span
                            class="inline-flex items-center gap-2 text-sm font-black text-[var(--sm-ink)] bg-[var(--sm-accent)] border-2 border-[var(--sm-ink)] shadow-[2px_2px_0px_0px_var(--sm-ink)] px-4 py-2 rounded-full uppercase tracking-wider"
                        >
                            PRE-ORDER
                        </span>

                    @else

                        <span
                            class="inline-flex items-center gap-2 text-sm font-black text-white bg-[var(--sm-ink)] border-2 border-[var(--sm-border)] shadow-[2px_2px_0px_0px_var(--sm-border)] px-4 py-2 rounded-full uppercase tracking-wider"
                        >
                            Saat Ini Habis
                        </span>

                    @endif


                    {{-- Rating --}}
                    @if($produk->review_count > 0)

                        <div
                            class="flex items-center gap-1.5 bg-white px-4 py-2 rounded-full border-2 border-[var(--sm-border)] shadow-[2px_2px_0px_0px_var(--sm-border)]"
                        >

                            <span class="text-[var(--sm-ink)] text-lg leading-none">
                                <x-public.home-icon name="star" :filled="true" :size="15" aria-hidden="true" />
                            </span>

                            <span
                                class="text-sm font-black text-[var(--sm-ink)]"
                            >
                                {{ number_format($produk->average_rating, 1) }}
                            </span>

                            <span
                                class="text-sm font-bold text-[var(--sm-text-muted)]"
                            >
                                ({{ $produk->review_count }})
                            </span>

                        </div>

                    @endif


                    {{-- Views --}}
                    @if($produk->views > 0)

                        <span
                            class="inline-flex items-center gap-1.5 text-sm font-bold text-[var(--sm-text-muted)]"
                        >
                            <x-public.click-icon class="h-4 w-4 shrink-0" />
                            {{ number_format($produk->views, 0, ',', '.') }} views
                        </span>

                    @endif

                </div>


{{-- =================================================
     DESKRIPSI PRODUK
     ================================================= --}}
@if($produk->deskripsi)

    <div
        class="mb-6 sm:mb-10 bg-[var(--sm-bg)] p-4 sm:p-6 rounded-2xl sm:rounded-3xl border-2 border-[var(--sm-border)] shadow-solid-sm"
        style="text-align: left !important;"
    >

        <h2
            class="text-sm font-black text-[var(--sm-ink)] uppercase tracking-widest mb-3"
            style="text-align: left !important;"
        >
            TENTANG PRODUK
        </h2>

        <p
            class="text-base font-bold text-[var(--sm-text-muted)] leading-relaxed whitespace-pre-wrap w-full"
            style="text-align: left !important; margin-left: 0 !important; margin-right: 0 !important;"
        >{{ $produk->deskripsi }}</p>

    </div>

@endif


                {{-- =================================================
                     INFORMASI TOKO
                     ================================================= --}}
                <div class="mt-auto">

                    <div
                        class="flex items-center gap-3 sm:gap-4 mb-6 sm:mb-8 p-3 sm:p-4 bg-[var(--sm-primary-soft)] border-2 border-[var(--sm-border)] rounded-2xl sm:rounded-[24px] shadow-solid-sm transform sm:rotate-1"
                    >

                        <div
                            class="w-16 h-16 rounded-full bg-white border-2 border-[var(--sm-border)] flex items-center justify-center text-[var(--sm-ink)] font-black text-2xl flex-shrink-0 shadow-sm"
                        >
                            {{ strtoupper(substr($produk->penjual->nama_toko ?? '?', 0, 1)) }}
                        </div>

                        <div class="min-w-0">

                            <h2
                                class="text-xs font-black text-[var(--sm-ink)] opacity-70 uppercase tracking-widest mb-1"
                            >
                                DARI TOKO
                            </h2>

                            <div
                                class="font-black text-[var(--sm-ink)] text-xl leading-snug uppercase"
                            >
                                {{ $produk->penjual->nama_toko ?? '-' }}
                            </div>

                            @if($produk->penjual->lokasi_kelas)

                                <div
                                    class="flex items-center gap-1 text-sm font-bold text-[var(--sm-text-muted)] mt-1"
                                >
                                    <x-public.home-icon name="map-pin" :size="16" aria-hidden="true" /> {{ $produk->penjual->lokasi_kelas }}
                                </div>

                            @endif

                        </div>

                    </div>


                    {{-- Jika WhatsApp tidak tersedia --}}
                    @if(!$waNumber)

                        <p
                            class="mb-4 rounded-2xl border-2 border-[var(--sm-border)] bg-[var(--sm-bg)] px-4 py-3 text-center text-sm font-bold text-[var(--sm-text-muted)]"
                        >
                            Nomor WhatsApp penjual belum tersedia.
                        </p>

                    @endif


                    <p
                        class="text-xs font-bold text-[var(--sm-text-muted)] text-center leading-relaxed"
                    >
                        Komunikasi dan kesepakatan dilakukan langsung dengan penjual.
                    </p>

                </div>

            </div>

        </div>

    </div>


    {{-- ============================================================
         RATING & ULASAN
         ============================================================ --}}
    <div id="ulasan" class="grid scroll-mt-28 lg:grid-cols-3 gap-10">

        <div class="lg:col-span-2 space-y-10">

            <div
                class="bg-[var(--sm-surface)] border-2 lg:border-4 border-[var(--sm-border)] rounded-[24px] lg:rounded-[40px] p-4 sm:p-8 lg:p-12 shadow-solid-md relative"
            >

                {{-- Decorative Blob --}}
                <div
                    class="absolute -top-4 -right-3 sm:-top-6 sm:-right-6 w-12 h-12 sm:w-20 sm:h-20 bg-[var(--sm-primary-soft)] border-2 sm:border-4 border-[var(--sm-border)] rounded-full shadow-solid-sm z-0 hidden sm:flex items-center justify-center text-3xl transform rotate-12"
                >
                    <x-public.home-icon name="star" :filled="true" :size="18" aria-hidden="true" />
                </div>


                {{-- Header --}}
                <div class="mb-8 relative z-10">

                    <h2
                        class="text-2xl sm:text-3xl font-black text-[var(--sm-ink)] uppercase tracking-tighter"
                    >
                        Kata Mereka
                    </h2>

                </div>


                {{-- Success Message --}}
                @if(session('review_warning'))
                    <div class="relative z-10 mb-6 flex items-start gap-3 rounded-2xl border-2 border-[var(--sm-border)] bg-[var(--sm-primary-soft)] px-5 py-4 font-bold text-[var(--sm-ink)] shadow-solid-sm" role="alert">
                        <x-public.home-icon name="alert-triangle" :size="20" class="mt-0.5 shrink-0" aria-hidden="true" />
                        <p class="text-sm sm:text-base">{{ session('review_warning') }}</p>
                    </div>
                @endif

                @if(session('success'))

                    <div
                        class="bg-[var(--sm-primary-soft)] border-2 border-[var(--sm-primary)] text-[var(--sm-ink)] font-bold px-5 py-4 rounded-2xl mb-8 shadow-solid-sm flex items-center gap-3 relative z-10"
                    >

                        <span class="text-2xl flex-shrink-0">
                            <x-public.home-icon name="circle-check" :size="20" aria-hidden="true" />
                        </span>

                        <p class="text-sm sm:text-base font-black">
                            {{ session('success') }}
                        </p>

                    </div>

                @endif


                {{-- Review Summary --}}
                <div
                    class="flex flex-col sm:flex-row items-center justify-between gap-5 sm:gap-8 mb-6 sm:mb-10 p-4 sm:p-8 bg-white rounded-2xl sm:rounded-3xl border-2 border-[var(--sm-border)] shadow-solid-sm relative z-10"
                >

                    <div class="flex items-center gap-8">

                        <div class="text-center sm:text-left">

                            <div
                                class="text-6xl font-black text-[var(--sm-ink)]"
                            >
                                {{ number_format($produk->average_rating, 1) }}
                            </div>

                            <div
                                class="text-sm font-bold text-[var(--sm-text-muted)] uppercase tracking-wider mt-2"
                            >
                                {{ $produk->review_count }} ULASAN
                            </div>

                        </div>


                        <div
                            class="hidden sm:block w-0.5 h-16 bg-[var(--sm-border)]"
                        ></div>


                        <div class="flex flex-col gap-1">

                            <div
                                class="flex text-[var(--sm-ink)] text-xl tracking-tighter"
                            >
                                <span class="inline-flex items-center gap-1" aria-label="Rating 5 bintang"><x-public.home-icon name="star" :filled="true" :size="16" /><x-public.home-icon name="star" :filled="true" :size="16" /><x-public.home-icon name="star" :filled="true" :size="16" /><x-public.home-icon name="star" :filled="true" :size="16" /><x-public.home-icon name="star" :filled="true" :size="16" /></span>
                            </div>

                            <span
                                class="text-xs font-bold text-[var(--sm-text-muted)] uppercase"
                            >
                                Berdasarkan ulasan pembeli
                            </span>

                        </div>

                    </div>


                    <button
                        type="button"
                        @click="$dispatch('open-review-modal')"
                        class="btn-primary product-review-trigger"
                    >
                        <x-public.home-icon name="edit" :size="16" aria-hidden="true" /> TULIS ULASAN
                    </button>

                </div>


                {{-- Review List --}}
                @if($ulasan->isEmpty())

                    <div
                        class="text-center py-10 px-6 bg-white rounded-3xl border-2 border-[var(--sm-border)] shadow-solid-sm relative z-10"
                    >

                        <div class="text-4xl mb-3">
                            <x-public.home-icon name="message-circle" :size="28" aria-hidden="true" />
                        </div>

                        <p
                            class="text-base sm:text-lg font-bold text-[var(--sm-text-muted)] mb-6"
                        >
                            Belum ada ulasan untuk produk ini. Jadilah yang pertama!
                        </p>

                    </div>

                @else

                    <div class="space-y-4 sm:space-y-6 relative z-10">

                        @foreach($ulasan as $rev)

                            <div
                                class="bg-white p-4 sm:p-6 rounded-2xl sm:rounded-3xl border-2 border-[var(--sm-border)] shadow-[3px_3px_0px_0px_var(--sm-border)] sm:shadow-[4px_4px_0px_0px_var(--sm-border)]"
                            >

                                <div
                                    class="flex items-center justify-between mb-4"
                                >

                                    <div
                                        class="font-black text-[var(--sm-ink)] uppercase text-sm sm:text-lg"
                                    >
                                        {{ $rev->guest_username }}
                                    </div>

                                    <div
                                        class="flex text-[var(--sm-ink)] text-sm tracking-tighter"
                                    >

                                        @for($i = 1; $i <= 5; $i++)

                                            <x-public.home-icon name="star" :filled="$i <= $rev->rating" :size="14" aria-hidden="true" />

                                        @endfor

                                    </div>

                                </div>


                                @if($rev->public_content)

                                    <p
                                        class="text-base font-bold text-[var(--sm-text-muted)] mb-3"
                                    >
                                        {{ $rev->public_content }}
                                    </p>

                                @endif


                                <div
                                    class="text-xs font-bold text-[var(--sm-text-muted)] opacity-50 uppercase tracking-widest"
                                >
                                    {{ $rev->created_at->diffForHumans() }}
                                </div>

                            </div>

                        @endforeach

                    </div>

                @endif

            </div>

        </div>


        {{-- ========================================================
             CARA MENGHUBUNGI
             ======================================================== --}}
        <div class="lg:col-span-1">

            <div
                class="bg-[var(--sm-surface)] border-4 border-[var(--sm-border)] rounded-[40px] p-8 shadow-solid-md transform rotate-1 sticky top-24"
            >

                <div
                    class="w-16 h-16 bg-white border-4 border-[var(--sm-border)] rounded-full flex items-center justify-center text-3xl mb-6 shadow-[4px_4px_0px_0px_var(--sm-border)] transform -rotate-12"
                >
                    <x-public.home-icon name="message-circle" :size="24" aria-hidden="true" />
                </div>

                <h2
                    class="text-2xl font-black text-[var(--sm-ink)] mb-6 uppercase tracking-tighter"
                >
                    Cara Menghubungi
                </h2>

                <ol
                    class="list-decimal list-inside space-y-4 text-base font-bold text-[var(--sm-ink)] mb-8"
                >

                    <li>
                        Hubungi penjual melalui WhatsApp.
                    </li>

                    <li>
                        Tanyakan detail dan ketersediaan produk.
                    </li>

                    <li>
                        Atur kesepakatan langsung dengan penjual.
                    </li>

                </ol>

            </div>

        </div>

    </div>


    {{-- ============================================================
         PRODUK TERKAIT
         ============================================================ --}}
    @if($produkTerkait && $produkTerkait->isNotEmpty())

        <div
            class="mt-24 pt-16 border-t-4 border-[var(--sm-border)]"
        >

            <h2
                class="text-4xl font-black text-[var(--sm-ink)] mb-10 uppercase tracking-tighter text-center"
            >
                Boleh Dicoba Juga Nih
            </h2>


            <div
                class="product-card-grid grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4"
            >

                @foreach($produkTerkait as $item)

                    <x-public.product-card :produk="$item" />

                @endforeach

            </div>

        </div>

    @endif

</main>


{{-- ================================================================
     FLOATING WHATSAPP BUTTON
     ================================================================ --}}

<div
    id="floating-whatsapp"
    class="fixed right-4 bottom-5 sm:right-6 sm:bottom-6 z-[99999] flex flex-col items-center gap-2"
    style="position:fixed !important;right:16px !important;bottom:20px !important;z-index:99999 !important;"
>

    {{-- Teks --}}
    <span
        class="whitespace-nowrap rounded-full border-2 border-[var(--sm-border)] bg-[var(--sm-bg)] px-4 py-2 text-xs sm:text-sm font-black text-[var(--sm-ink)] shadow-[3px_3px_0px_0px_var(--sm-border)]"
    >
        Pesan via WhatsApp
    </span>


    {{-- Tombol --}}
    @if($waNumber)

        <a
            href="{{ $waLink }}"
            onclick="fetch('{{ route('public.produk.whatsapp', $produk->slug) }}', {keepalive:true, credentials:'same-origin'}).catch(() => {})"
            target="_blank"
            rel="noopener noreferrer"
            class="flex h-14 w-14 sm:h-16 sm:w-16 items-center justify-center rounded-full border-2 border-[var(--sm-border)] bg-[var(--sm-accent)] text-2xl sm:text-3xl text-[var(--sm-on-accent)] shadow-[4px_4px_0px_0px_var(--sm-border)] transition-all duration-200 hover:scale-105 hover:bg-[var(--sm-accent-hover)] active:translate-x-1 active:translate-y-1 active:shadow-none focus-visible:outline focus-visible:outline-4 focus-visible:outline-offset-2 focus-visible:outline-[var(--sm-ink)]"
            aria-label="Pesan {{ $produk->nama }} melalui WhatsApp"
        >

            <span aria-hidden="true">
                <x-public.home-icon name="message-circle" :size="24" aria-hidden="true" />
            </span>

        </a>

    @else

        <div
            class="flex h-14 w-14 sm:h-16 sm:w-16 items-center justify-center rounded-full border-2 border-[var(--sm-border)] bg-[var(--sm-primary-soft)] text-xl text-white shadow-[4px_4px_0px_0px_var(--sm-border)]"
            title="Nomor WhatsApp penjual belum tersedia"
            aria-label="Nomor WhatsApp penjual belum tersedia"
        >
            <x-public.home-icon name="message-circle" :size="24" aria-hidden="true" />
        </div>

    @endif

</div>


{{-- ================================================================
     MODAL ULASAN
     ================================================================ --}}

@push('modals')

<div
    x-data="{
        open: {{ $errors->any() ? 'true' : 'false' }},
        rating: {{ old('rating', 0) }},
        hoverRating: 0
    }"
    x-init="$watch('open', val => { document.body.style.overflow = val ? 'hidden' : '' })"
    @open-review-modal.window="open = true"
    @keydown.escape.window="if(open) open = false"
>

    <div
        x-show="open"
        x-cloak
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        style="position:fixed;inset:0;width:100vw;height:100vh;z-index:999999;"
        role="dialog"
        aria-modal="true"
        aria-labelledby="review-modal-title"
    >

        {{-- Backdrop --}}
        <div
            @click="open = false"
            style="position:absolute;inset:0;background:color-mix(in srgb,var(--sm-ink) 70%,transparent);backdrop-filter:blur(4px);-webkit-backdrop-filter:blur(4px);z-index:1;"
        ></div>


        {{-- Modal --}}
        <div
            style="position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);z-index:2;width:min(520px,calc(100vw - 32px));max-height:90vh;overflow-y:auto;background:var(--sm-surface);border-radius:24px;border:3px solid var(--sm-border,var(--sm-ink));box-shadow:6px 6px 0 0 var(--sm-border,var(--sm-ink));padding:28px;"
        >

            {{-- Header --}}
            <div
                style="display:flex;align-items:flex-start;justify-content:space-between;gap:16px;margin-bottom:20px;padding-bottom:16px;border-bottom:2px solid var(--sm-border,var(--sm-ink));"
            >

                <div>

                    <p
                        style="font-size:12px;font-weight:900;color:var(--sm-primary,var(--sm-primary));text-transform:uppercase;letter-spacing:0.1em;margin:0 0 4px 0;"
                    >
                        {{ $produk->nama }}
                    </p>

                    <h3
                        id="review-modal-title"
                        style="font-size:24px;font-weight:900;color:var(--sm-ink,var(--sm-ink));text-transform:uppercase;letter-spacing:-0.02em;margin:0;"
                    >
                        Tulis Ulasan
                    </h3>

                </div>


                <button
                    type="button"
                    @click="open = false"
                    style="width:40px;height:40px;flex-shrink:0;display:flex;align-items:center;justify-content:center;border-radius:50%;background:var(--sm-surface);border:2px solid var(--sm-border,var(--sm-ink));color:var(--sm-ink,var(--sm-ink));font-size:22px;font-weight:900;cursor:pointer;box-shadow:2px 2px 0 0 var(--sm-border,var(--sm-ink));"
                    aria-label="Tutup modal ulasan"
                >
                    &times;
                </button>

            </div>


            {{-- Form --}}
            <form
                action="{{ route('public.ulasan.store', $produk->slug) }}"
                method="POST"
            >

                @csrf


                {{-- Rating --}}
                <div style="margin-bottom:20px;">

                    <label
                        style="display:block;font-size:13px;font-weight:900;color:var(--sm-ink,var(--sm-ink));text-transform:uppercase;letter-spacing:0.1em;margin-bottom:8px;"
                    >
                        Bintang
                        <span style="color:var(--sm-ink);">*</span>
                    </label>


                    <div
                        style="display:flex;gap:6px;cursor:pointer;"
                        @mouseleave="hoverRating = 0"
                    >

                        <template x-for="i in 5" :key="i">

                            <button
                                type="button"
                                @click="rating = i"
                                @mouseenter="hoverRating = i"
                                style="font-size:32px;background:none;border:none;cursor:pointer;padding:2px;line-height:1;transition:transform 0.15s;"
                                :style="'color:' + (i <= (hoverRating || rating) ? 'var(--sm-primary)' : 'var(--sm-primary-soft)')"
                                :aria-label="'Rating ' + i + ' bintang'"
                            >
                                <x-public.home-icon name="star" :filled="true" :size="15" aria-hidden="true" />
                            </button>

                        </template>

                    </div>


                    <input
                        type="hidden"
                        name="rating"
                        x-model="rating"
                    >


                    @error('rating')

                        <span
                            style="display:block;font-size:12px;font-weight:700;color:var(--sm-ink);margin-top:6px;"
                        >
                            {{ $message }}
                        </span>

                    @enderror

                </div>


                {{-- Nama --}}
                <div style="margin-bottom:20px;">

                    <label
                        for="review-guest-username"
                        style="display:block;font-size:13px;font-weight:900;color:var(--sm-ink,var(--sm-ink));text-transform:uppercase;letter-spacing:0.1em;margin-bottom:8px;"
                    >
                        Namamu
                        <span style="color:var(--sm-ink);">*</span>
                    </label>


                    <input
                        type="text"
                        name="guest_username"
                        id="review-guest-username"
                        value="{{ old('guest_username') }}"
                        required
                        maxlength="100"
                        placeholder="Siapa namamu?"
                        style="width:100%;padding:12px 16px;font-size:15px;font-weight:700;background:var(--sm-surface);border:2px solid var(--sm-border,var(--sm-ink));border-radius:16px;box-shadow:3px 3px 0 0 var(--sm-border,var(--sm-ink));outline:none;box-sizing:border-box;"
                    >


                    @error('guest_username')

                        <span
                            style="display:block;font-size:12px;font-weight:700;color:var(--sm-ink);margin-top:6px;"
                        >
                            {{ $message }}
                        </span>

                    @enderror

                </div>


                {{-- Komentar --}}
                <div style="margin-bottom:24px;">

                    <label
                        for="review-komentar"
                        style="display:block;font-size:13px;font-weight:900;color:var(--sm-ink,var(--sm-ink));text-transform:uppercase;letter-spacing:0.1em;margin-bottom:8px;"
                    >
                        Komentar
                    </label>


                    <textarea
                        name="komentar"
                        id="review-komentar"
                        rows="3"
                        maxlength="1000"
                        placeholder="Gimana pengalaman atau rasa makanan ini?"
                        style="width:100%;padding:12px 16px;font-size:15px;font-weight:700;background:var(--sm-surface);border:2px solid var(--sm-border,var(--sm-ink));border-radius:16px;box-shadow:3px 3px 0 0 var(--sm-border,var(--sm-ink));outline:none;resize:vertical;min-height:80px;box-sizing:border-box;"
                    >{{ old('komentar') }}</textarea>


                    @error('komentar')

                        <span
                            style="display:block;font-size:12px;font-weight:700;color:var(--sm-ink);margin-top:6px;"
                        >
                            {{ $message }}
                        </span>

                    @enderror

                </div>


                {{-- Submit --}}
                <button
                    type="submit"
                    style="width:100%;padding:14px 20px;font-size:16px;font-weight:900;text-transform:uppercase;letter-spacing:0.05em;color:var(--sm-surface);background:var(--sm-primary,var(--sm-primary));border:2px solid var(--sm-border,var(--sm-ink));border-radius:16px;box-shadow:4px 4px 0 0 var(--sm-border,var(--sm-ink));cursor:pointer;transition:all 0.15s;"
                    onmouseover="this.style.transform='translate(2px,2px)';this.style.boxShadow='2px 2px 0 0 var(--sm-border,var(--sm-ink))'"
                    onmouseout="this.style.transform='';this.style.boxShadow='4px 4px 0 0 var(--sm-border,var(--sm-ink))'"
                >
                    KIRIM ULASAN
                </button>

            </form>


            {{-- Simpan username reviewer --}}
            <script>
                (() => {
                    const el = document.getElementById('review-guest-username');

                    if (!el) return;

                    const key = 'siswamart_guest_username';

                    if (!el.value) {
                        el.value = localStorage.getItem(key) || '';
                    }

                    if (el.form) {
                        el.form.addEventListener('submit', () => {
                            localStorage.setItem(key, el.value.trim());
                        });
                    }
                })();
            </script>

        </div>

    </div>

</div>

@endpush

@endsection

