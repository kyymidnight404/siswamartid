@php($isHome = request()->routeIs('public.home'))
<!DOCTYPE html>
<html lang="id">
<head>
    <link rel="icon" type="image/png" sizes="96x96" href="{{ asset('favicon.png') }}">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="@yield('meta_description', 'SiswaMart — Media promosi produk siswa. Temukan makanan, minuman, snack, dan produk kreatif karya siswa sekolah.')">
    <title>@yield('title', 'SiswaMart')</title>
    <style>[x-cloak] { display: none !important; }</style>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="{{ asset('css/siswamart-theme.css') }}?v={{ file_exists(public_path('css/siswamart-theme.css')) ? filemtime(public_path('css/siswamart-theme.css')) : time() }}">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Fredoka:wght@500;600;700&display=swap">
    @if($isHome)
        <link rel="stylesheet" href="{{ asset('css/homepage.css') }}?v={{ file_exists(public_path('css/homepage.css')) ? filemtime(public_path('css/homepage.css')) : time() }}">
    @endif
</head>
<body class="bg-[var(--color-bg)] text-[var(--color-text)] min-h-screen antialiased flex flex-col {{ $isHome ? 'home-page' : 'public-site-page' }}"
      x-data="{ mobileMenuOpen: false }">

{{-- ─── Navbar ──────────────────────────────────────────────────────── --}}
<header style="position: fixed; top: 16px; left: 50%; transform: translateX(-50%); z-index: 99999; pointer-events: auto;"
        class="home-container home-navbar"
        x-data="{ mobileMenuOpen: false, searchOpen: false }">
    <div :class="(mobileMenuOpen || searchOpen) ? 'rounded-2xl sm:rounded-3xl' : 'rounded-full'"
         class="bg-[var(--color-primary)] shadow-[4px_4px_0_var(--ink)] px-4 sm:px-8 py-2.5 sm:py-3 border-0 transition-all duration-200 w-full">

        {{-- Desktop Navigation Row --}}
        <div class="hidden lg:flex home-navbar__desktop items-center gap-5 lg:gap-7">
            {{-- 1. Beranda --}}
            <a href="{{ route('public.home') }}"
               id="nav-beranda"
               class="inline-flex items-center justify-center px-3.5 py-1.5 rounded-full text-sm lg:text-base font-extrabold tracking-wide transition-colors {{ request()->routeIs('public.home') ? 'text-[var(--ink)] bg-white/20 is-active' : 'text-[var(--ink)] hover:text-[var(--ink)]' }}">
                Beranda
            </a>

            {{-- 2. Kategori --}}
            <a href="{{ route('public.kategori.index') }}"
               id="nav-kategori"
               class="inline-flex items-center justify-center px-3.5 py-1.5 rounded-full text-sm lg:text-base font-extrabold tracking-wide transition-colors {{ request()->routeIs('public.kategori.*') ? 'text-[var(--ink)] bg-white/20 is-active' : 'text-[var(--ink)] hover:text-[var(--ink)]' }}">
                Kategori
            </a>

            {{-- 3. SiswaMart Text Logo (Siswa Hitam, Mart Orange) --}}
            <a href="{{ route('public.home') }}"
               id="nav-logo"
               class="px-3.5 py-1 bg-white rounded-full shadow-sm text-base lg:text-lg font-black tracking-tight hover:scale-105 transition-all inline-flex items-center"
               aria-label="SiswaMart — Beranda">
                <span class="text-[var(--ink)]">Siswa</span><span class="text-[var(--color-primary)]">Mart</span>
            </a>

            {{-- 4. Cara Memesan --}}
            <a href="{{ route('public.cara_pemesanan') }}"
               id="nav-cara-pemesanan"
               class="inline-flex items-center justify-center px-3.5 py-1.5 rounded-full text-sm lg:text-base font-extrabold tracking-wide transition-colors {{ request()->routeIs('public.cara_pemesanan') ? 'text-[var(--ink)] bg-white/20 is-active' : 'text-[var(--ink)] hover:text-[var(--ink)]' }}">
                Cara Memesan
            </a>

            {{-- 5. Tentang Kami --}}
            <a href="{{ route('public.tentang_kami') }}"
               id="nav-tentang-kami"
               class="inline-flex items-center justify-center px-3.5 py-1.5 rounded-full text-sm lg:text-base font-extrabold tracking-wide transition-colors {{ request()->routeIs('public.tentang_kami') ? 'text-[var(--ink)] bg-white/20 is-active' : 'text-[var(--ink)] hover:text-[var(--ink)]' }}">
                Tentang Kami
            </a>

            {{-- 6. Search Icon & Inline Expanding Search Bar --}}
            <div class="relative flex items-center"
                 @keydown.escape.window="if('{{ request('q') }}' === '') searchOpen = false">

                {{-- Collapsed Search Icon Button --}}
                <button x-show="!searchOpen && '{{ request('q') }}' === ''"
                        @click="searchOpen = true; $nextTick(() => $refs.searchInput?.focus())"
                        type="button"
                        class="p-2 text-white hover:text-[var(--ink)] hover:bg-white/20 rounded-full transition-colors flex items-center justify-center"
                        :aria-expanded="searchOpen.toString()"
                        aria-controls="nav-search-form"
                        aria-label="Cari produk">
                    @if($isHome)
                        <x-public.home-icon name="search" class="home-navbar__search-icon" />
                    @else
                        <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    @endif
                </button>

                {{-- Expanded Inline Search Bar --}}
                <div x-show="searchOpen || '{{ request('q') }}' !== ''"
                     @click.away="if('{{ request('q') }}' === '') searchOpen = false"
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0 scale-95"
                     x-transition:enter-end="opacity-100 scale-100"
                     class="relative flex items-center"
                     style="{{ request('q') ? '' : 'display: none;' }}">
                    <form action="{{ request()->routeIs('public.kategori.*') ? url()->current() : route('public.kategori.index') }}" method="GET" id="nav-search-form" class="relative flex items-center w-48 sm:w-56 lg:w-60" style="position: relative;">
                        @if(request('min_price')) <input type="hidden" name="min_price" value="{{ request('min_price') }}"> @endif
                        @if(request('max_price')) <input type="hidden" name="max_price" value="{{ request('max_price') }}"> @endif
                        @if(request('sort')) <input type="hidden" name="sort" value="{{ request('sort') }}"> @endif
                        @if(request('kategori') && !request()->routeIs('public.kategori.*')) <input type="hidden" name="kategori" value="{{ request('kategori') }}"> @endif

                        @if($isHome)
                            <x-public.home-icon name="search" class="home-navbar__search-field-icon" />
                        @else
                            <svg class="absolute w-4 h-4 text-neutral-400 pointer-events-none" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%);" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                        @endif
                        <label for="nav-search-input" class="sr-only">Cari produk</label>
                        <input x-ref="searchInput"
                               type="text"
                               name="q"
                               id="nav-search-input"
                               placeholder="Cari jajanan, makanan, atau minuman..."
                               value="{{ request('q') }}"
                                   class="w-full py-1.5 text-xs sm:text-sm text-[var(--ink)] bg-white rounded-full border border-[var(--ink)]/15 outline-none shadow-sm focus:ring-2 focus:ring-[var(--ink)]/20 transition-all font-medium"
                                   style="padding-left: 36px; padding-right: 34px;">

                        <button @click="if('{{ request('q') }}' === '') searchOpen = false; else window.location.href='{{ request()->routeIs('public.kategori.*') ? url()->current() : route('public.kategori.index') }}';"
                                type="button"
                                class="text-neutral-400 hover:text-neutral-700 p-0.5 rounded-full"
                                style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); display: flex; align-items: center; justify-content: center;"
                                aria-label="Tutup pencarian">
                            @if($isHome)
                                <x-public.home-icon name="x" class="home-navbar__close-icon" />
                            @else
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            @endif
                        </button>
                    </form>
                </div>
            </div>

            {{-- 7. Dashboard / Auth Button --}}
            @auth
                @if(auth()->user()->role === 'admin')
                    <a href="{{ route('admin.dashboard') }}"
                       id="nav-admin-dashboard"
                       class="text-xs sm:text-sm font-extrabold text-white bg-[var(--ink)] hover:bg-[var(--ink)] px-4 py-2 rounded-full transition-colors shadow-sm">
                        Dashboard
                    </a>
                @elseif(auth()->user()->role === 'penjual')
                    <a href="{{ route('penjual.dashboard') }}"
                       id="nav-penjual-dashboard"
                       class="text-xs sm:text-sm font-extrabold text-white bg-[var(--ink)] hover:bg-[var(--ink)] px-4 py-2 rounded-full transition-colors shadow-sm">
                        {{ $isHome ? 'Dashboard' : 'Toko Saya' }}
                    </a>
                @endif
            @else
                <a href="{{ route('login') }}"
                   id="nav-login"
                   class="text-xs sm:text-sm font-extrabold text-white bg-[var(--ink)] hover:bg-[var(--ink)] px-4 py-2 rounded-full transition-colors shadow-sm">
                    Masuk
                </a>
            @endauth
        </div>

        {{-- Mobile Bar --}}
        <div class="flex lg:hidden items-center justify-between gap-3 w-full">
            <a href="{{ route('public.home') }}"
               class="home-navbar__mobile-logo">
                <span class="text-[var(--ink)]">Siswa</span><span class="text-[var(--color-primary)]">Mart</span>
            </a>

            <div class="flex items-center gap-1.5">
                <button @click="searchOpen = !searchOpen; if(searchOpen) mobileMenuOpen = false"
                        type="button"
                        class="p-2 text-white hover:bg-white/20 rounded-full transition-colors"
                        :aria-expanded="searchOpen.toString()"
                        aria-controls="mobile-nav-search-form"
                        aria-label="Cari">
                    @if($isHome)
                        <x-public.home-icon name="search" class="home-navbar__search-icon" />
                    @else
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    @endif
                </button>

                <button @click="mobileMenuOpen = !mobileMenuOpen; if(mobileMenuOpen) searchOpen = false"
                        class="p-2 text-white hover:bg-white/20 rounded-full transition-colors"
                        id="mobile-menu-btn"
                        :aria-expanded="mobileMenuOpen.toString()"
                        aria-controls="mobile-navigation-menu"
                        :aria-label="mobileMenuOpen ? 'Tutup menu navigasi' : 'Buka menu navigasi'">
                    @if($isHome)
                        <x-public.home-icon name="menu-2" class="home-navbar__menu-icon" x-show="!mobileMenuOpen" />
                        <x-public.home-icon name="x" class="home-navbar__menu-icon" x-show="mobileMenuOpen" style="display:none;" />
                    @else
                        <svg x-show="!mobileMenuOpen" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                        <svg x-show="mobileMenuOpen" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5" style="display:none;">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    @endif
                </button>
            </div>
        </div>

        {{-- Mobile Search Dropdown --}}
        <div x-show="searchOpen" x-transition class="lg:hidden pt-3 border-t border-white/20 mt-2 text-[var(--ink)]" style="display:none;">
            <form action="{{ request()->routeIs('public.kategori.*') ? url()->current() : route('public.kategori.index') }}" method="GET" id="mobile-nav-search-form" class="relative">
                @if(request('min_price')) <input type="hidden" name="min_price" value="{{ request('min_price') }}"> @endif
                @if(request('max_price')) <input type="hidden" name="max_price" value="{{ request('max_price') }}"> @endif
                @if(request('sort')) <input type="hidden" name="sort" value="{{ request('sort') }}"> @endif
                @if(request('kategori') && !request()->routeIs('public.kategori.*')) <input type="hidden" name="kategori" value="{{ request('kategori') }}"> @endif

                @if($isHome)
                    <x-public.home-icon name="search" class="home-navbar__search-field-icon home-navbar__mobile-search-icon" />
                @else
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                @endif
                <label for="mobile-nav-search-input" class="sr-only">Cari produk</label>
                <input type="text" id="mobile-nav-search-input" name="q" placeholder="Cari jajanan, makanan, atau minuman..." value="{{ request('q') }}"
                       class="w-full pl-9 pr-3 py-2 text-xs text-[var(--ink)] bg-white border border-neutral-200 rounded-lg outline-none focus:bg-white focus:border-neutral-400">
            </form>
        </div>

        {{-- Mobile Navigation Dropdown --}}
        <div x-show="mobileMenuOpen"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 -translate-y-1"
             x-transition:enter-end="opacity-100 translate-y-0"
             class="lg:hidden pt-3 pb-1 border-t border-white/20 mt-2.5 space-y-1 text-white"
             id="mobile-navigation-menu"
             style="display:none;">
            <a href="{{ route('public.home') }}"
               class="flex items-center px-4 py-2.5 rounded-xl text-sm font-extrabold transition-colors {{ request()->routeIs('public.home') ? 'bg-white text-[var(--color-primary)]' : 'text-white hover:bg-white/15' }}"
               id="mobile-nav-beranda">Beranda</a>
            <a href="{{ route('public.kategori.index') }}"
               class="flex items-center px-4 py-2.5 rounded-xl text-sm font-extrabold transition-colors {{ request()->routeIs('public.kategori.*') ? 'bg-white text-[var(--color-primary)]' : 'text-white hover:bg-white/15' }}"
               id="mobile-nav-kategori">Kategori</a>
            <a href="{{ route('public.cara_pemesanan') }}"
               class="flex items-center px-4 py-2.5 rounded-xl text-sm font-extrabold transition-colors {{ request()->routeIs('public.cara_pemesanan') ? 'bg-white/20 text-white font-extrabold' : 'text-white hover:bg-white/15' }}"
               id="mobile-nav-cara-pemesanan">Cara Memesan</a>
            <a href="{{ route('public.tentang_kami') }}"
               class="flex items-center px-4 py-2.5 rounded-xl text-sm font-extrabold transition-colors {{ request()->routeIs('public.tentang_kami') ? 'bg-white/20 text-white font-extrabold' : 'text-white hover:bg-white/15' }}"
               id="mobile-nav-tentang-kami">Tentang Kami</a>

            @auth
                @if(auth()->user()->role === 'admin')
                    <a href="{{ route('admin.dashboard') }}"
                       id="mobile-nav-admin-dashboard"
                       class="flex items-center justify-center px-4 py-2.5 mt-2 rounded-xl text-sm font-extrabold text-[var(--cream)] hover:text-[var(--ink)] bg-[var(--ink)] hover:bg-[var(--cream)] transition-colors shadow-sm">
                        Dashboard Admin
                    </a>
                @elseif(auth()->user()->role === 'penjual')
                    <a href="{{ route('penjual.dashboard') }}"
                       id="mobile-nav-penjual-dashboard"
                       class="flex items-center justify-center px-4 py-2.5 mt-2 rounded-xl text-sm font-extrabold text-[var(--cream)] hover:text-[var(--ink)] bg-[var(--ink)] hover:bg-[var(--cream)] transition-colors shadow-sm">
                        Toko Saya
                    </a>
                @endif
            @else
                <a href="{{ route('login') }}"
                   id="mobile-nav-login"
                   class="flex items-center justify-center px-4 py-2.5 mt-2 rounded-xl text-sm font-extrabold text-[var(--cream)] hover:text-[var(--ink)] bg-[var(--ink)] hover:bg-[var(--cream)] transition-colors shadow-sm">
                    Masuk
                </a>
            @endauth
        </div>
    </div>
</header>

{{-- ─── Page Content ───────────────────────────────────────────────── --}}
<main class="flex-grow {{ $isHome ? 'home-main' : 'pt-24 sm:pt-28' }}">
    <div class="home-container mb-5 {{ $isHome ? 'hidden' : '' }}">
        <div class="home-location-banner flex items-center gap-3 rounded-2xl border-2 border-[var(--color-border)] bg-[#A3D9C9] px-4 py-3 sm:px-5 shadow-solid-sm">
            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full border-2 border-[var(--color-border)] bg-white" aria-hidden="true">
                <svg class="h-5 w-5 text-[var(--color-text)]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M20 10c0 5-8 12-8 12S4 15 4 10a8 8 0 1 1 16 0Z"/><circle cx="12" cy="10" r="2.5"/>
                </svg>
            </span>
            <p class="text-xs sm:text-sm font-bold leading-snug text-[var(--color-text)]">Untuk saat ini, SiswaMart melayani penjualan di area SMKN 11 Bandung.</p>
        </div>
    </div>
    @yield('content')
</main>

{{-- ─── Footer ─────────────────────────────────────────────────────── --}}
<footer class="home-footer">
    <div class="home-container home-footer__inner">
        <div class="home-footer__grid">
            <div>
                <a href="{{ route('public.home') }}" class="inline-flex items-center text-3xl font-black tracking-tight" aria-label="SiswaMart — Beranda">
                    <span class="text-[var(--color-text)]">Siswa</span><span class="text-[var(--color-primary)]">Mart</span>
                </a>
                <p class="mt-3 max-w-xs text-sm leading-6 text-[var(--ink-soft)]">
                    Media promosi produk makanan dan minuman karya siswa SMKN 11 Bandung.
                </p>
                <a href="https://wa.me/6285724494193" target="_blank" rel="noopener noreferrer" class="home-footer__contact-button">
                    <span class="text-sm">Ada pertanyaan?</span>
                    <span class="font-extrabold">Hubungi SiswaMart</span>
                </a>
            </div>

            <nav aria-label="Jelajahi SiswaMart">
                <h2 class="mb-3 text-base font-extrabold">Jelajahi</h2>
                <ul class="space-y-2 text-sm text-[var(--ink-soft)]">
                    <li><a href="{{ route('public.home') }}" class="transition-colors hover:text-[var(--color-primary-dark)]">Beranda</a></li>
                    <li><a href="{{ route('public.produk.index') }}" class="transition-colors hover:text-[var(--color-primary-dark)]">Semua Produk</a></li>
                    <li><a href="{{ route('public.kategori.index') }}" class="transition-colors hover:text-[var(--color-primary-dark)]">Kategori</a></li>
                </ul>
            </nav>

            <nav aria-label="Informasi SiswaMart">
                <h2 class="mb-3 text-base font-extrabold">Informasi</h2>
                <ul class="space-y-2 text-sm text-[var(--ink-soft)]">
                    <li><a href="{{ route('public.cara_pemesanan') }}" class="transition-colors hover:text-[var(--color-primary-dark)]">Cara Memesan</a></li>
                    <li><a href="{{ route('public.tentang_kami') }}" class="transition-colors hover:text-[var(--color-primary-dark)]">Tentang Kami</a></li>
                    <li><span>Melayani area SMKN 11 Bandung</span></li>
                </ul>
            </nav>

            <div>
                <h2 class="mb-3 text-base font-extrabold">Hubungi Kami</h2>
                <ul class="space-y-2 text-sm text-[var(--ink-soft)]">
                    <li>
                        <a href="https://wa.me/6285724494193" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-2 transition-colors hover:text-[var(--color-primary-dark)]">
                            <span class="home-footer__social-icon" aria-hidden="true"><x-public.home-icon name="brand-whatsapp" /></span>
                            <span>WhatsApp</span><span class="text-[var(--ink-soft)]">0857 2449 4193</span>
                        </a>
                    </li>
                    <li>
                        <a href="https://www.instagram.com/siswamartid/" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-2 transition-colors hover:text-[var(--color-primary-dark)]">
                            <span class="home-footer__social-icon" aria-hidden="true"><x-public.home-icon name="brand-instagram" /></span>
                            <span>Instagram</span><span class="text-[var(--ink-soft)]">@siswamartid</span>
                        </a>
                    </li>
                    <li>
                        <a href="https://www.tiktok.com/@siswamartid" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-2 transition-colors hover:text-[var(--color-primary-dark)]">
                            <span class="home-footer__social-icon" aria-hidden="true"><x-public.home-icon name="brand-tiktok" /></span>
                            <span>TikTok</span><span class="text-[var(--ink-soft)]">@siswamartid</span>
                        </a>
                    </li>
                </ul>
            </div>

        </div>

        <div class="home-footer__copyright">
            <p>© {{ now()->year }} SiswaMart. Semua hak dilindungi.</p>
            <p>Produk karya siswa SMKN 11 Bandung.</p>
        </div>
    </div>
</footer>

@stack('modals')

@unless($isHome)
    <x-back-floating />
@endunless

</body>
</html>

