<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin') | SiswaMart</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,400&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Fredoka:wght@500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        [x-cloak] { display: none !important; }
        * { font-family: 'Plus Jakarta Sans', ui-sans-serif, system-ui, sans-serif; box-sizing: border-box; }
        .font-display { font-family: 'Plus Jakarta Sans', sans-serif; font-weight: 800; }
        body { background: var(--sm-bg); color: var(--sm-ink); }

        /* Scrollbar */
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-thumb { background: color-mix(in srgb, var(--sm-ink) 25%, transparent); border-radius: 3px; }

        /* ── Sidebar ── */
        .app-sidebar {
            background: var(--sm-surface);
            border-right: 1px solid var(--sm-border);
            width: 256px;
            flex-shrink: 0;
            display: flex;
            flex-direction: column;
            position: sticky;
            top: 0;
            height: 100vh;
            max-height: 100vh;
        }
        .sidebar-section-title {
            font-size: 0.7rem;
            font-weight: 600;
            color: var(--sm-text-muted);
            letter-spacing: 0.08em;
            text-transform: uppercase;
            padding: 16px 16px 6px;
        }
        .sidebar-link {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 16px;
            border-radius: 9999px;
            margin: 4px 12px;
            font-size: 0.875rem;
            font-weight: 700;
            color: var(--sm-text-muted);
            transition: all 0.15s;
            text-decoration: none;
            border: 2px solid transparent;
        }
        .sidebar-link svg { flex-shrink: 0; }
        .sidebar-link:hover {
            background: var(--sm-primary-soft);
            color: var(--sm-ink);
            border-color: var(--sm-border);
            transform: translate(-2px, -2px);
            box-shadow: 4px 4px 0px 0px var(--sm-border);
        }
        .sidebar-link.active {
            background: var(--sm-primary);
            color: var(--sm-surface);
            font-weight: 800;
            border: 2px solid var(--sm-border);
            box-shadow: 2px 2px 0px 0px var(--sm-border);
        }
        .sidebar-link.active svg { color: var(--sm-surface); }

        /* ── Topbar ── */
        .app-topbar {
            background: var(--sm-surface);
            border-bottom: 1px solid var(--sm-border);
            height: 64px;
            display: flex;
            align-items: center;
            padding: 0 24px;
            gap: 12px;
            position: sticky;
            top: 0;
            z-index: 30;
        }

        /* ── Cards & Tables ── */
        .admin-card {
            background: var(--sm-surface);
            border: 2px solid var(--sm-border);
            border-radius: 24px;
            box-shadow: 4px 4px 0px 0px var(--sm-border);
            overflow: hidden;
        }
        .admin-card-header {
            padding: 16px 20px;
            border-bottom: 2px solid var(--sm-border);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .stat-card {
            background: var(--sm-surface);
            border: 2px solid var(--sm-border);
            border-radius: 24px;
            box-shadow: 4px 4px 0px 0px var(--sm-border);
            padding: 20px;
        }

        /* Forms */
        .form-input {
            width: 100%;
            background: var(--sm-bg);
            border: 2px solid var(--sm-border);
            color: var(--sm-ink);
            border-radius: 16px;
            padding: 10px 16px;
            font-size: 0.875rem;
            font-weight: 500;
            box-shadow: 2px 2px 0px 0px var(--sm-border);
            transition: all 0.2s;
            box-sizing: border-box;
        }
        input.form-input:not([type="checkbox"]):not([type="radio"]) {
            height: 48px;
            min-height: 48px;
            max-height: 48px;
            line-height: 20px;
            padding-top: 12px;
            padding-bottom: 12px;
            padding-left: 16px;
            padding-right: 16px;
            vertical-align: middle;
            box-sizing: border-box;
        }
        select.form-input {
            height: 48px;
            min-height: 48px;
            max-height: 48px;
            line-height: 22px;
            padding-top: 11px;
            padding-bottom: 11px;
            padding-left: 16px;
            padding-right: 36px;
            vertical-align: middle;
            box-sizing: border-box;
        }
        :is(div,label).form-input {
            display: flex;
            align-items: center;
            justify-content: space-between;
            height: 48px;
            min-height: 48px;
            max-height: 48px;
            padding-top: 0;
            padding-bottom: 0;
            padding-left: 12px;
            padding-right: 14px;
            vertical-align: middle;
            box-sizing: border-box;
        }
        textarea.form-input {
            padding: 14px 16px;
            line-height: 1.6;
            box-sizing: border-box;
        }
        .form-input::placeholder {
            color: var(--sm-text-muted);
            opacity: 0.85;
            line-height: normal;
        }
        .form-input:focus { outline: none; background: var(--sm-surface); transform: translate(-2px, -2px); box-shadow: 4px 4px 0px 0px var(--sm-border); }
        label.form-input,
        label.form-input:hover,
        label.form-input:active,
        label.form-input:focus,
        label.form-input:focus-within {
            transform: none !important;
            transition: none !important;
            animation: none !important;
            box-shadow: 2px 2px 0px 0px var(--sm-border) !important;
            background: var(--sm-surface) !important;
            border-color: var(--sm-border) !important;
            outline: none !important;
            cursor: pointer !important;
        }
        label.form-input * {
            transform: none !important;
            transition: none !important;
            animation: none !important;
        }

        /* Buttons */
        .btn-primary {
            background: var(--sm-primary);
            color: var(--sm-border);
            font-weight: 800;
            font-size: 0.875rem;
            padding: 8px 16px;
            border-radius: 9999px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s;
            border: 2px solid var(--sm-border);
            box-shadow: 4px 4px 0px 0px var(--sm-border);
        }
        .btn-primary:hover {
            background: var(--sm-primary-hover);
            transform: translate(-2px, -2px);
            box-shadow: 6px 6px 0px 0px var(--sm-border);
        }

        .btn-outline {
            background: var(--sm-surface);
            color: var(--sm-ink);
            border: 2px solid var(--sm-border);
            font-weight: 800;
            font-size: 0.875rem;
            padding: 8px 16px;
            border-radius: 9999px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s;
            box-shadow: 4px 4px 0px 0px var(--sm-border);
        }
        .btn-outline:hover {
            background: var(--sm-primary-soft);
            transform: translate(-2px, -2px);
            box-shadow: 6px 6px 0px 0px var(--sm-border);
        }

        /* Flash */
        .admin-flash {
            position: fixed;
            bottom: 24px;
            left: 24px;
            background: var(--sm-surface);
            border: 1px solid var(--sm-border);
            box-shadow: 4px 4px 0 var(--sm-ink);
            padding: 12px 20px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 0.875rem;
            font-weight: 500;
            color: var(--sm-ink);
            z-index: 9999;
            animation: slideUp 0.3s ease-out forwards;
        }
        @keyframes slideUp { from { transform: translateY(100%); opacity: 0; } to { transform: translateY(0); opacity: 1; } }

        /* Status Badges */
        .badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 4px 10px;
            border-radius: 9999px;
            font-size: 0.65rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .badge-aktif { background: var(--sm-primary-soft); color: var(--sm-primary); }
        .badge-pending { background: var(--sm-primary-soft); color: var(--sm-ink); }
        .badge-ditolak { background: var(--sm-primary-soft); color: var(--sm-ink); }

        /* Notification Dropdown Custom Refinements */
        .admin-notification-dropdown {
            width: min(380px, calc(100vw - 24px)) !important;
            max-width: 380px !important;
            background: var(--sm-surface) !important;
            border: 1.5px solid color-mix(in srgb, var(--sm-ink) 20%, transparent) !important;
            border-radius: 16px !important;
            box-shadow: 0 12px 30px -4px color-mix(in srgb, var(--sm-ink) 16%, transparent), 0 4px 12px -2px color-mix(in srgb, var(--sm-ink) 8%, transparent) !important;
            max-height: 420px !important;
            flex-direction: column !important;
            overflow: hidden !important;
            z-index: 50 !important;
        }
        .admin-notification-dropdown[style*="display: none"] {
            display: none !important;
        }
        @media (min-width: 640px) {
            .admin-notification-dropdown { width: 380px !important; }
        }
        .admin-notification-header {
            display: flex !important;
            align-items: center !important;
            justify-content: space-between !important;
            padding: 12px 16px !important;
            background: var(--sm-surface) !important;
            border-bottom: 1px solid color-mix(in srgb, var(--sm-ink) 10%, transparent) !important;
            flex-shrink: 0 !important;
        }
        .notification-header-action {
            background: transparent !important;
            border: none !important;
            box-shadow: none !important;
            padding: 0 !important;
            margin: 0 !important;
            min-height: auto !important;
            height: auto !important;
            border-radius: 0 !important;
            font-size: 11px !important;
            font-weight: 700 !important;
            color: var(--sm-primary) !important;
            cursor: pointer !important;
            display: inline-flex !important;
            align-items: center !important;
            gap: 4px !important;
            transform: none !important;
            transition: color 0.15s ease !important;
        }
        .notification-header-action:hover {
            background: transparent !important;
            box-shadow: none !important;
            transform: none !important;
            color: var(--sm-primary-hover) !important;
            text-decoration: underline !important;
        }
        .admin-notification-list {
            flex: 1 1 auto !important;
            overflow-y: auto !important;
            overflow-x: hidden !important;
            max-height: 350px !important;
            padding: 10px 12px !important;
            display: flex !important;
            flex-direction: column !important;
            gap: 8px !important;
        }
        .admin-notification-list::-webkit-scrollbar { width: 5px !important; }
        .admin-notification-list::-webkit-scrollbar-track { background: transparent !important; }
        .admin-notification-list::-webkit-scrollbar-thumb { background: color-mix(in srgb, var(--sm-ink) 18%, transparent) !important; border-radius: 999px !important; }
        .admin-notification-list::-webkit-scrollbar-thumb:hover { background: color-mix(in srgb, var(--sm-ink) 35%, transparent) !important; }
        .notification-item-card {
            display: block !important;
            width: 100% !important;
            text-align: left !important;
            text-decoration: none !important;
            border-radius: 12px !important;
            padding: 12px 14px !important;
            min-height: auto !important;
            height: auto !important;
            margin: 0 !important;
            cursor: pointer !important;
            box-shadow: 0 1px 3px color-mix(in srgb, var(--sm-ink) 5%, transparent) !important;
            transform: none !important;
            transition: all 0.15s ease-in-out !important;
            color: var(--sm-ink) !important;
        }
        .notification-item-card.is-unread {
            background-color: var(--sm-bg) !important;
            border: 1px solid color-mix(in srgb, var(--sm-primary) 30%, transparent) !important;
            border-left: 3.5px solid var(--sm-primary) !important;
        }
        .notification-item-card.is-read {
            background-color: var(--sm-surface) !important;
            border: 1px solid color-mix(in srgb, var(--sm-ink) 10%, transparent) !important;
            border-left: 3.5px solid color-mix(in srgb, var(--sm-ink) 15%, transparent) !important;
        }
        .notification-item-card:hover {
            background-color: var(--sm-primary-soft) !important;
            border-color: color-mix(in srgb, var(--sm-primary) 45%, transparent) !important;
            box-shadow: 0 4px 10px color-mix(in srgb, var(--sm-ink) 8%, transparent) !important;
            transform: translateY(-1px) !important;
        }
        .notification-item-card:active {
            transform: translateY(0) !important;
            box-shadow: 0 1px 2px color-mix(in srgb, var(--sm-ink) 5%, transparent) !important;
        }
        .notification-icon-btn {
            width: 40px !important;
            height: 40px !important;
            min-height: 40px !important;
            max-height: 40px !important;
            border-radius: 50% !important;
            padding: 0 !important;
            flex-shrink: 0 !important;
        }
    </style>
    <link rel="stylesheet" href="{{ asset('css/siswamart-theme.css') }}?v={{ file_exists(public_path('css/siswamart-theme.css')) ? filemtime(public_path('css/siswamart-theme.css')) : time() }}">
</head>
<body class="admin-dashboard flex min-h-screen">

    {{-- ── Sidebar ── --}}
    <div class="dashboard-sidebar-backdrop" data-sidebar-close aria-hidden="true"></div>
    <aside class="app-sidebar hidden lg:flex sticky top-0 h-screen max-h-screen" id="dashboardSidebar">
        <div class="h-16 flex items-center px-6 border-b border-[var(--sm-border)]">
            <x-brand-logo :href="route('public.home')" label="SiswaMart — Beranda" />
        </div>

        <nav class="flex-1 overflow-y-auto py-4">
            <div class="sidebar-section-title">Menu utama</div>
            <a href="{{ route('admin.dashboard') }}" class="sidebar-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                Dashboard
            </a>

            <div class="sidebar-section-title mt-4">Manajemen</div>
            <a href="{{ route('admin.categories.index') }}" class="sidebar-link {{ request()->routeIs('admin.categories.*') ? 'active' : '' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                Kategori
            </a>
            <a href="{{ route('admin.penjual.index') }}" class="sidebar-link {{ request()->routeIs('admin.penjual.*') ? 'active' : '' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                Penjual
            </a>
            <a href="{{ route('admin.produk.index') }}" class="sidebar-link {{ request()->routeIs('admin.produk.*') ? 'active' : '' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                Moderasi Produk
            </a>
            <a href="{{ route('admin.komentar.index') }}" class="sidebar-link {{ request()->routeIs('admin.komentar.*') || request()->routeIs('admin.ulasan.*') ? 'active' : '' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                Kelola Komentar
            </a>
            <a href="{{ route('admin.laporan.index') }}" class="sidebar-link {{ request()->routeIs('admin.laporan.*') ? 'active' : '' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 6h13M8 12h13M8 18h13M3 6h.01M3 12h.01M3 18h.01"/></svg>
                Rekap Admin
            </a>
        </nav>

        {{-- User info --}}
        <div class="p-4 border-t-2 border-[var(--sm-border)] flex-shrink-0">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-9 h-9 rounded-full bg-[var(--sm-primary-soft)] flex items-center justify-center text-[var(--sm-ink)] border-2 border-[var(--sm-border)] font-bold text-sm shadow-[2px_2px_0px_0px_var(--sm-border)]">
                    {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                </div>
                <div class="min-w-0">
                    <p class="sidebar-user-name text-sm font-bold truncate">{{ Auth::user()->name }}</p>
                    <p class="text-[var(--sm-text-muted)] font-semibold text-xs capitalize">Admin SiswaMart</p>
                </div>
            </div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button class="sidebar-logout-button w-full text-xs font-bold border-2 py-1.5 rounded-full transition-all">Keluar</button>
            </form>
        </div>
    </aside>

    {{-- ── Main Area ── --}}
    <div class="flex-1 min-w-0 flex flex-col min-h-screen">
        {{-- Topbar --}}
        <header class="app-topbar">
            <div class="flex items-center gap-3 w-full mx-auto">
                <button type="button" class="p-2 rounded-full hover:bg-[var(--sm-primary-soft)] text-[var(--sm-text-muted)] lg:hidden flex-shrink-0" data-sidebar-toggle aria-label="Buka menu" aria-controls="dashboardSidebar" aria-expanded="false">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>
                <div class="flex-1 min-w-0">
                    <h1 class="font-display font-black tracking-tight text-xl text-[var(--sm-ink)] truncate">@yield('page-title', 'Admin Panel')</h1>
                </div>
                <div class="flex items-center gap-2 flex-shrink-0">
                    @php
                        $adminNotifications = Auth::user()->notifications()->latest()->limit(8)->get();
                        $unreadNotificationCount = Auth::user()->unreadNotifications()->count();

                        $resolveNotificationIcon = function ($activity, $title) {
                            $act = strtolower($activity ?? '');
                            $tit = strtolower($title ?? '');

                            if (str_contains($act, 'ulasan') || str_contains($tit, 'ulasan')) {
                                return [
                                    'bg' => 'bg-[var(--sm-primary-soft)]/60 text-[var(--sm-primary)] border border-[var(--sm-primary-soft)]',
                                    'icon' => '<svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>'
                                ];
                            }
                            if (str_contains($act, 'edit') || str_contains($tit, 'perubahan')) {
                                return [
                                    'bg' => 'bg-[var(--sm-primary-soft)] text-[var(--sm-primary-hover)] border border-[var(--sm-primary-soft)]',
                                    'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>'
                                ];
                            }
                            if (str_contains($act, 'hapus') || str_contains($tit, 'hapus')) {
                                return [
                                    'bg' => 'bg-[var(--sm-primary-soft)] text-[var(--sm-ink)] border border-[var(--sm-primary-soft)]',
                                    'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>'
                                ];
                            }
                            if (str_contains($act, 'status') || str_contains($tit, 'diperbarui')) {
                                return [
                                    'bg' => 'bg-[var(--sm-primary-soft)] text-[var(--sm-ink)] border border-[var(--sm-primary-soft)]',
                                    'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>'
                                ];
                            }
                            return [
                                'bg' => 'bg-[var(--sm-primary-soft)] text-[var(--sm-ink)] border border-[var(--sm-primary-soft)]',
                                'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>'
                            ];
                        };
                    @endphp
                    <div class="relative" x-data="{ open: false }" @keydown.escape.window="open = false">
                        <button type="button" @click.stop="open = !open" class="notification-icon-btn relative w-10 h-10 rounded-full bg-[var(--sm-surface)] text-[var(--sm-ink)] border-2 border-[var(--sm-border)] flex items-center justify-center shadow-[2px_2px_0px_0px_var(--sm-border)] hover:-translate-y-0.5 transition-all" aria-label="Notifikasi" :aria-expanded="open.toString()">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6 6 0 10-12 0v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                            </svg>
                            <span id="adminNotificationCount" class="absolute -top-1 -right-1 min-w-5 h-5 px-1 rounded-full bg-[var(--sm-accent)] text-[var(--sm-on-accent)] text-[10px] font-black flex items-center justify-center border-2 border-[var(--sm-surface)] {{ $unreadNotificationCount > 0 ? '' : 'hidden' }}">{{ $unreadNotificationCount > 99 ? '99+' : $unreadNotificationCount }}</span>
                        </button>

                        <div x-show="open"
                             @click.outside="open = false"
                             x-cloak
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
                             x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                             x-transition:leave="transition ease-in duration-150"
                             x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                             x-transition:leave-end="opacity-0 scale-95 -translate-y-1"
                             class="admin-notification-dropdown fixed sm:absolute top-[64px] sm:top-[calc(100%+8px)] left-3 right-3 sm:left-auto sm:right-0 z-50 flex flex-col">

                            <div class="admin-notification-header">
                                <div class="flex items-center gap-2">
                                    <h2 class="text-sm font-extrabold text-[var(--sm-ink)]">Notifikasi</h2>
                                    <span id="adminNotificationBadge" class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-[var(--sm-primary)]/15 text-[var(--sm-primary-hover)] {{ $unreadNotificationCount > 0 ? '' : 'hidden' }}">
                                        <span id="adminNotificationBadgeText">{{ $unreadNotificationCount }} baru</span>
                                    </span>
                                </div>
                                <div id="adminNotificationMarkAllWrapper" class="{{ $unreadNotificationCount > 0 ? '' : 'hidden' }}">
                                    <form method="POST" action="{{ route('admin.notifications.read_all') }}" class="m-0 p-0">
                                        @csrf
                                        <button type="submit" class="notification-header-action" title="Tandai semua notifikasi sudah dibaca">
                                            Tandai semua dibaca
                                        </button>
                                    </form>
                                </div>
                            </div>

                            <div id="adminNotificationList" class="admin-notification-list">
                                @forelse($adminNotifications as $notification)
                                    @php
                                        $notificationData = $notification->data;
                                        $iconMeta = $resolveNotificationIcon($notificationData['activity'] ?? '', $notificationData['title'] ?? '');
                                    @endphp
                                    <form method="POST" action="{{ route('admin.notifications.read', $notification->id) }}" class="block w-full m-0 p-0">
                                        @csrf
                                        <button type="submit" class="notification-item-btn notification-item-card {{ $notification->read_at ? 'is-read' : 'is-unread' }}">
                                            <div class="flex items-start gap-3">
                                                <div class="w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0 {{ $iconMeta['bg'] }}">
                                                    {!! $iconMeta['icon'] !!}
                                                </div>
                                                <div class="min-w-0 flex-1">
                                                    <div class="flex items-center justify-between gap-2">
                                                        <h3 class="text-xs font-bold text-[var(--sm-ink)] truncate leading-tight">
                                                            {{ $notificationData['title'] ?? 'Aktivitas baru' }}
                                                        </h3>
                                                        @unless($notification->read_at)
                                                            <span class="w-2 h-2 rounded-full bg-[var(--sm-primary)] flex-shrink-0" title="Belum dibaca"></span>
                                                        @endunless
                                                    </div>
                                                    <p class="text-[12px] text-[var(--sm-ink)] mt-1 leading-snug line-clamp-2 break-words">
                                                        {{ $notificationData['description'] ?? '' }}
                                                    </p>
                                                    <div class="flex items-center gap-1 mt-1.5 text-[10px] font-medium text-[var(--sm-ink)]">
                                                        <svg class="w-3 h-3 opacity-60 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                        </svg>
                                                        <span>{{ $notification->created_at?->diffForHumans() }}</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </button>
                                    </form>
                                @empty
                                    <div class="px-4 py-8 text-center flex flex-col items-center justify-center">
                                        <div class="w-10 h-10 rounded-full bg-[var(--sm-primary-soft)] text-[var(--sm-primary)] flex items-center justify-center mb-2">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6 6 0 10-12 0v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                                            </svg>
                                        </div>
                                        <p class="text-xs font-bold text-[var(--sm-ink)]">Belum ada notifikasi</p>
                                        <p class="text-[11px] text-[var(--sm-ink)] mt-0.5">Semua notifikasi baru akan tampil di sini</p>
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                    <div class="w-10 h-10 rounded-full bg-[var(--sm-primary-soft)] text-[var(--sm-ink)] border-2 border-[var(--sm-border)] font-bold text-sm flex items-center justify-center shadow-[2px_2px_0px_0px_var(--sm-border)]">
                        {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                    </div>
                </div>
            </div>
        </header>

        {{-- Flash Messages --}}
        @if(session('success'))
        <div id="adminFlash" class="admin-flash">
            <svg class="w-4 h-4 text-[var(--sm-primary)] flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
            {{ session('success') }}
        </div>
        @endif
        @if(session('error'))
        <div id="adminFlash" class="admin-flash">
            <svg class="w-4 h-4 text-[var(--sm-primary)] flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
            {{ session('error') }}
        </div>
        @endif

        <main class="flex-1 p-6 overflow-x-hidden">
            <div class="max-w-6xl mx-auto">
                @yield('content')
            </div>
        </main>
    </div>

    <script>
        (() => {
            const toggle = document.querySelector('[data-sidebar-toggle]');
            const close = document.querySelector('[data-sidebar-close]');
            const setSidebarOpen = (isOpen) => {
                document.body.classList.toggle('mobile-sidebar-open', isOpen);
                toggle?.setAttribute('aria-expanded', String(isOpen));
            };
            toggle?.addEventListener('click', () => setSidebarOpen(!document.body.classList.contains('mobile-sidebar-open')));
            close?.addEventListener('click', () => setSidebarOpen(false));
            document.addEventListener('keydown', (event) => {
                if (event.key === 'Escape') setSidebarOpen(false);
            });
            window.addEventListener('resize', () => {
                if (window.innerWidth >= 1024) setSidebarOpen(false);
            });
        })();

        // Auto-hide flash
        setTimeout(() => {
            const el = document.getElementById('adminFlash');
            if (el) { el.style.transition = 'opacity 0.4s'; el.style.opacity = '0'; setTimeout(() => el.remove(), 400); }
        }, 3500);

        const notificationList = document.getElementById('adminNotificationList');
        const notificationCount = document.getElementById('adminNotificationCount');
        const notificationBadge = document.getElementById('adminNotificationBadge');
        const notificationBadgeText = document.getElementById('adminNotificationBadgeText');
        const notificationMarkAllWrapper = document.getElementById('adminNotificationMarkAllWrapper');
        const notificationUrl = @json(route('admin.notifications.poll'));
        const notificationReadUrl = @json(route('admin.notifications.read', ['notification' => '__ID__']));
        const notificationCsrfToken = @json(csrf_token());
        const escapeHtml = (value) => String(value ?? '').replace(/[&<>'"]/g, (char) => ({'&':'&amp;','<':'&lt;','>':'&gt;',"'":'&var(--sm-ink);','"':'&quot;'}[char]));

        const getNotificationIconJs = (activity, title) => {
            const act = (activity || '').toLowerCase();
            const tit = (title || '').toLowerCase();
            if (act.includes('ulasan') || tit.includes('ulasan')) {
                return {
                    bg: 'bg-[var(--sm-primary-soft)]/60 text-[var(--sm-primary)] border border-[var(--sm-primary-soft)]',
                    svg: '<svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>'
                };
            }
            if (act.includes('edit') || tit.includes('perubahan')) {
                return {
                    bg: 'bg-[var(--sm-primary-soft)] text-[var(--sm-primary-hover)] border border-[var(--sm-primary-soft)]',
                    svg: '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>'
                };
            }
            if (act.includes('hapus') || tit.includes('hapus')) {
                return {
                    bg: 'bg-[var(--sm-primary-soft)] text-[var(--sm-ink)] border border-[var(--sm-primary-soft)]',
                    svg: '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>'
                };
            }
            if (act.includes('status') || tit.includes('diperbarui')) {
                return {
                    bg: 'bg-[var(--sm-primary-soft)] text-[var(--sm-ink)] border border-[var(--sm-primary-soft)]',
                    svg: '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>'
                };
            }
            return {
                bg: 'bg-[var(--sm-primary-soft)] text-[var(--sm-ink)] border border-[var(--sm-primary-soft)]',
                svg: '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>'
            };
        };

        const renderNotificationItemJs = (item) => {
            const data = item.data || {};
            const icon = getNotificationIconJs(data.activity, data.title);
            const isUnread = !item.read_at;
            const actionUrl = notificationReadUrl.replace('__ID__', encodeURIComponent(item.id));
            const unreadDot = isUnread ? '<span class="w-2 h-2 rounded-full bg-[var(--sm-primary)] flex-shrink-0" title="Belum dibaca"></span>' : '';

            return `
                <form method="POST" action="${actionUrl}" class="block w-full m-0 p-0">
                    <input type="hidden" name="_token" value="${escapeHtml(notificationCsrfToken)}">
                    <button type="submit" class="notification-item-btn notification-item-card ${isUnread ? 'is-unread' : 'is-read'}">
                        <div class="flex items-start gap-3">
                            <div class="w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0 ${icon.bg}">
                                ${icon.svg}
                            </div>
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center justify-between gap-2">
                                    <h3 class="text-xs font-bold text-[var(--sm-ink)] truncate leading-tight">${escapeHtml(data.title || 'Aktivitas baru')}</h3>
                                    ${unreadDot}
                                </div>
                                <p class="text-[12px] text-[var(--sm-ink)] mt-1 leading-snug line-clamp-2 break-words">${escapeHtml(data.description || '')}</p>
                                <div class="flex items-center gap-1 mt-1.5 text-[10px] font-medium text-[var(--sm-ink)]">
                                    <svg class="w-3 h-3 opacity-60 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <span>${escapeHtml(item.created_at || '')}</span>
                                </div>
                            </div>
                        </div>
                    </button>
                </form>
            `;
        };

        const refreshAdminNotifications = async () => {
            try {
                const response = await fetch(notificationUrl, { headers: { 'Accept': 'application/json' } });
                if (!response.ok) return;
                const data = await response.json();

                if (notificationCount) {
                    notificationCount.textContent = data.unread_count > 99 ? '99+' : data.unread_count;
                    notificationCount.classList.toggle('hidden', data.unread_count < 1);
                }

                if (notificationBadge && notificationBadgeText) {
                    notificationBadgeText.textContent = data.unread_count + ' baru';
                    notificationBadge.classList.toggle('hidden', data.unread_count < 1);
                }

                if (notificationMarkAllWrapper) {
                    notificationMarkAllWrapper.classList.toggle('hidden', data.unread_count < 1);
                }

                if (notificationList) {
                    notificationList.innerHTML = data.notifications && data.notifications.length
                        ? data.notifications.map(renderNotificationItemJs).join('')
                        : `
                            <div class="px-4 py-8 text-center flex flex-col items-center justify-center">
                                <div class="w-10 h-10 rounded-full bg-[var(--sm-primary-soft)] text-[var(--sm-primary)] flex items-center justify-center mb-2">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6 6 0 10-12 0v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                                    </svg>
                                </div>
                                <p class="text-xs font-bold text-[var(--sm-ink)]">Belum ada notifikasi</p>
                                <p class="text-[11px] text-[var(--sm-ink)] mt-0.5">Semua notifikasi baru akan tampil di sini</p>
                            </div>
                        `;
                }
            } catch (error) {
                // Polling is best-effort; the persisted notification menu remains usable.
            }
        };
        setInterval(refreshAdminNotifications, 10000);
    </script>
@unless(request()->routeIs('admin.dashboard'))
@endunless
</body>
</html>


