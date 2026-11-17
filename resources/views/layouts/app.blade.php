<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>Profil Akun — SiswaMart</title>

        <link rel="stylesheet" href="{{ asset('css/fonts.css') }}?v={{ file_exists(public_path('css/fonts.css')) ? filemtime(public_path('css/fonts.css')) : time() }}">
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <link rel="stylesheet" href="{{ asset('css/siswamart-theme.css') }}?v={{ file_exists(public_path('css/siswamart-theme.css')) ? filemtime(public_path('css/siswamart-theme.css')) : time() }}">
    </head>
    <body class="account-page font-sans antialiased">
        <div class="min-h-screen bg-[var(--sm-bg)] px-4 pt-4 sm:px-6">
            @include('layouts.navigation')

            <!-- Page Heading -->
            @isset($header)
                <header class="mt-4 rounded-2xl border-2 border-[var(--sm-border)] bg-[var(--sm-surface)] shadow-[4px_4px_0px_0px_var(--sm-border)] max-w-7xl mx-auto">
                    <div class="py-5 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <!-- Page Content -->
            <main>
                {{ $slot }}
            </main>
        </div>
        <footer class="auth-site-footer">
            <span>© {{ now()->year }} SiswaMart · Produk karya siswa SMKN 11 Bandung</span>
            <a href="https://wa.me/6285724494193" target="_blank" rel="noopener noreferrer" class="inline-flex min-h-[44px] items-center">Hubungi SiswaMart · WhatsApp</a>
        </footer>
    </body>
</html>

