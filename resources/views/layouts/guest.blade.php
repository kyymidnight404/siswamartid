<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@if(request()->routeIs('login'))Masuk | SiswaMart @else{{ config('app.name', 'SiswaMart') }}@endif</title>

    <link rel="stylesheet" href="{{ asset('css/fonts.css') }}?v={{ file_exists(public_path('css/fonts.css')) ? filemtime(public_path('css/fonts.css')) : time() }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="{{ asset('css/siswamart-theme.css') }}?v={{ file_exists(public_path('css/siswamart-theme.css')) ? filemtime(public_path('css/siswamart-theme.css')) : time() }}">
</head>
<body class="auth-page {{ request()->routeIs('login', 'password.*', 'verification.*') ? 'auth-page--login' : '' }} {{ request()->routeIs('login') ? 'auth-login-layout' : '' }} antialiased min-h-screen flex flex-col justify-center items-center p-4">

    <!-- Removed Theme Toggle -->

    @unless (request()->routeIs('login', 'password.*', 'verification.*'))
    <nav class="auth-site-nav" aria-label="Navigasi utama">
        <x-brand-logo :href="route('public.home')" class="auth-site-logo" label="SiswaMart — Beranda" />
        <div class="auth-site-links">
            <a href="{{ route('public.home') }}">Beranda</a>
            <a href="{{ route('public.kategori.index') }}">Kategori</a>
            <a href="{{ route('public.cara_pemesanan') }}">Cara Memesan</a>
            <a href="{{ route('public.tentang_kami') }}">Tentang Kami</a>
        </div>
        <a href="{{ route('public.kategori.index') }}" class="auth-site-action">Jelajahi katalog</a>
    </nav>
    @endunless

    @if(request()->routeIs('login'))
        <main class="auth-login-shell">
            <aside class="auth-login-hero" aria-label="Tentang SiswaMart">
                <x-brand-logo static label="SiswaMart" />
                <p class="auth-login-tagline">Jajanan karya teman sekolah, siap menemani harimu.</p>
                <ul class="auth-login-benefits" aria-label="Keunggulan SiswaMart">
                    <li><span class="auth-login-benefit-icon"><x-public.home-icon name="whatsapp" :size="20" /></span><span>Pesan langsung via WhatsApp</span></li>
                    <li><span class="auth-login-benefit-icon"><x-public.home-icon name="school" :size="20" /></span><span>Dari siswa SMKN 11</span></li>
                    <li><span class="auth-login-benefit-icon"><x-public.home-icon name="price" :size="20" /></span><span>Harga ramah kantong</span></li>
                </ul>
            </aside>
            <section class="auth-login-column">
                <a href="{{ route('public.home') }}" class="auth-home-link">
                    <svg aria-hidden="true" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5m7 7-7-7 7-7" /></svg>
                    <span>Kembali ke beranda</span>
                </a>
                <div class="auth-card">{{ $slot }}</div>
            </section>
        </main>
    @else
        <main class="auth-shell"><div class="auth-card">{{ $slot }}</div></main>
    @endif

    @unless (request()->routeIs('login', 'password.*', 'verification.*'))
    <footer class="auth-site-footer">
        <span>© {{ date('Y') }} SiswaMart · Produk karya siswa SMKN 11 Bandung</span>
        <a href="https://wa.me/6285724494193" target="_blank" rel="noopener noreferrer">Hubungi SiswaMart · WhatsApp</a>
    </footer>
    @endunless

    <!-- Removed Dark Mode Script -->
</body>
</html>
