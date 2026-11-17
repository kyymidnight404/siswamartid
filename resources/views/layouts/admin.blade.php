<!DOCTYPE html>
<html lang="id" class="dashboard-document">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin') | SiswaMart</title>
    <link rel="stylesheet" href="{{ asset('css/fonts.css') }}?v={{ file_exists(public_path('css/fonts.css')) ? filemtime(public_path('css/fonts.css')) : time() }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="{{ asset('css/siswamart-theme.css') }}?v={{ file_exists(public_path('css/siswamart-theme.css')) ? filemtime(public_path('css/siswamart-theme.css')) : time() }}">
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
            width: var(--sm-sidebar-width, 264px);
            flex-shrink: 0;
            display: flex;
            flex-direction: column;
            position: fixed;
            top: 0;
            left: 0;
            bottom: 0;
            height: 100vh;
            height: 100dvh;
            max-height: 100vh;
            max-height: 100dvh;
            z-index: 40;
        }
        .dashboard-content-shell {
            margin-left: var(--sm-sidebar-width, 264px);
            width: calc(100% - var(--sm-sidebar-width, 264px));
            min-width: 0;
            min-height: 100vh;
            min-height: 100dvh;
        }
        @media (max-width: 899px) {
            .app-sidebar {
                width: min(300px, calc(100vw - 48px));
                transform: translateX(-100%);
                z-index: 60;
            }
            .dashboard-content-shell {
                margin-left: 0;
                width: 100%;
            }
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
            font-size: 0.7rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .badge-aktif { background: var(--sm-primary-soft); color: var(--sm-primary); }
        .badge-pending { background: var(--sm-primary-soft); color: var(--sm-ink); }
        .badge-ditolak { background: var(--sm-primary-soft); color: var(--sm-ink); }
    </style>
</head>
<body class="admin-dashboard dashboard-shell flex min-h-screen">

    {{-- ── Sidebar ── --}}
    <div class="dashboard-sidebar-backdrop" data-sidebar-close aria-hidden="true"></div>
    <aside class="app-sidebar" id="dashboardSidebar">
        @include('layouts.partials.dashboard-sidebar-brand')

        <nav class="dashboard-sidebar-menu py-4">
            <div class="sidebar-section-title">Menu utama</div>
            <a href="{{ route('admin.dashboard') }}" class="sidebar-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" @if(request()->routeIs('admin.dashboard')) aria-current="page" @endif>
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                Dashboard
            </a>

            <div class="sidebar-section-title mt-4">Manajemen</div>
            <a href="{{ route('admin.categories.index') }}" class="sidebar-link {{ request()->routeIs('admin.categories.*') ? 'active' : '' }}" @if(request()->routeIs('admin.categories.*')) aria-current="page" @endif>
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                Kategori
            </a>
            <a href="{{ route('admin.penjual.index') }}" class="sidebar-link {{ request()->routeIs('admin.penjual.*') ? 'active' : '' }}" @if(request()->routeIs('admin.penjual.*')) aria-current="page" @endif>
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                Penjual
            </a>
            <a href="{{ route('admin.produk.index') }}" class="sidebar-link {{ request()->routeIs('admin.produk.*') ? 'active' : '' }}" @if(request()->routeIs('admin.produk.*')) aria-current="page" @endif>
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                Moderasi produk
            </a>
            <a href="{{ route('admin.komentar.index') }}" class="sidebar-link {{ request()->routeIs('admin.komentar.*') || request()->routeIs('admin.ulasan.*') ? 'active' : '' }}" @if(request()->routeIs('admin.komentar.*') || request()->routeIs('admin.ulasan.*')) aria-current="page" @endif>
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                Kelola komentar
            </a>
            <a href="{{ route('admin.laporan.index') }}" class="sidebar-link {{ request()->routeIs('admin.laporan.*') ? 'active' : '' }}" @if(request()->routeIs('admin.laporan.*')) aria-current="page" @endif>
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 6h13M8 12h13M8 18h13M3 6h.01M3 12h.01M3 18h.01"/></svg>
                Laporan
            </a>
        </nav>

        @include('layouts.partials.dashboard-sidebar-footer', ['user' => Auth::user(), 'role' => 'Admin'])
    </aside>

    {{-- ── Main Area ── --}}
    <div class="dashboard-content-shell flex-1 min-w-0 flex flex-col">
        {{-- Topbar --}}
        <header class="app-topbar">
            <div class="flex items-center gap-3 w-full mx-auto">
                <button type="button" class="p-2 rounded-full hover:bg-[var(--sm-primary-soft)] text-[var(--sm-text-muted)] flex-shrink-0" data-sidebar-toggle aria-label="Buka menu" aria-controls="dashboardSidebar" aria-expanded="false">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>
                <div class="flex-1 min-w-0">
                    <h1 class="font-display font-black tracking-tight text-xl text-[var(--sm-ink)] truncate">@yield('page-title', 'Admin Panel')</h1>
                </div>
                <div class="flex items-center gap-2 flex-shrink-0">
                    @php
                        $notifSummary = app(\App\Services\AdminNotificationService::class)->summaryForAdmin(Auth::user());
                        $adminNotifications = $notifSummary['notifications'];
                        $unreadNotificationCount = $notifSummary['unread_count'];
                        $notifCounts = $notifSummary['counts'];
                    @endphp
                    <div class="relative" x-data="{ open: false }" @keydown.escape.window="open = false">
                        <button type="button" @click.stop="open = !open" class="notification-icon-btn relative w-10 h-10 rounded-full bg-[var(--sm-surface)] text-[var(--sm-ink)] border-2 border-[var(--sm-border)] flex items-center justify-center shadow-[2px_2px_0px_0px_var(--sm-border)] hover:-translate-y-0.5 transition-all" aria-label="Notifikasi" :aria-expanded="open.toString()">
                            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6 6 0 10-12 0v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                            </svg>
                            <span id="adminNotificationCount" class="absolute -top-1 -right-1 min-w-5 h-5 px-1 rounded-full bg-[var(--sm-accent)] text-[var(--sm-on-accent)] text-[10px] font-black flex items-center justify-center border-2 border-[var(--sm-surface)] {{ $unreadNotificationCount > 0 ? '' : 'hidden' }}">{{ $unreadNotificationCount > 99 ? '99+' : $unreadNotificationCount }}</span>
                        </button>

                        <div id="adminNotificationDropdown"
                             x-show="open"
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
                                <div class="flex items-center gap-3 min-w-0">
                                    <div class="admin-notif-bell-badge" aria-hidden="true">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.9">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6 6 0 10-12 0v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                                        </svg>
                                    </div>
                                    <div class="min-w-0">
                                        <h2 class="text-[15px] font-extrabold text-[var(--sm-ink)] leading-tight">Notifikasi</h2>
                                        <p class="text-[12px] text-[var(--sm-text-muted)] leading-snug mt-0.5 truncate">Update terbaru di SiswaMart</p>
                                    </div>
                                </div>
                                <div id="adminNotificationMarkAllWrapper" class="flex-shrink-0 {{ $unreadNotificationCount > 0 ? '' : 'hidden' }}">
                                    <form method="POST" action="{{ route('admin.notifications.read_all') }}" class="m-0 p-0">
                                        @csrf
                                        <button type="submit" class="notification-header-action" title="Tandai semua notifikasi sudah dibaca">
                                            <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.3">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                            </svg>
                                            <span>Tandai semua dibaca</span>
                                        </button>
                                    </form>
                                </div>
                            </div>

                            <div class="admin-notification-tabs" role="tablist" aria-label="Filter kategori notifikasi">
                                <button type="button" class="admin-notification-tab is-active" data-notif-tab="semua" role="tab" aria-selected="true">
                                    <span>Semua</span>
                                    <span id="adminNotifTabCountSemua" class="admin-notification-tab__count">{{ $notifCounts['semua'] ?? 0 }}</span>
                                </button>
                                <button type="button" class="admin-notification-tab" data-notif-tab="produk" role="tab" aria-selected="false">
                                    <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.9" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 16V8a2 2 0 00-1-1.73l-7-4a2 2 0 00-2 0l-7 4A2 2 0 003 8v8a2 2 0 001 1.73l7 4a2 2 0 002 0l7-4A2 2 0 0021 16z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.27 6.96L12 12.01l8.73-5.05M12 22.08V12"/>
                                    </svg>
                                    <span>Produk</span>
                                    <span id="adminNotifTabCountProduk" class="admin-notification-tab__count {{ ($notifCounts['produk'] ?? 0) > 0 ? '' : 'hidden' }}">{{ $notifCounts['produk'] ?? 0 }}</span>
                                </button>
                                <button type="button" class="admin-notification-tab" data-notif-tab="ulasan" role="tab" aria-selected="false">
                                    <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.9" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M7 8h10M7 12h6m-5 8l-4 2V6a2 2 0 012-2h12a2 2 0 012 2v10a2 2 0 01-2 2H8z"/>
                                    </svg>
                                    <span>Ulasan</span>
                                    <span id="adminNotifTabCountUlasan" class="admin-notification-tab__count {{ ($notifCounts['ulasan'] ?? 0) > 0 ? '' : 'hidden' }}">{{ $notifCounts['ulasan'] ?? 0 }}</span>
                                </button>
                            </div>

                            <div id="adminNotificationList" class="admin-notification-list">
                                @forelse($adminNotifications as $item)
                                    @include('admin.notifikasi.partials.item', ['item' => $item])
                                @empty
                                    <div class="px-4 py-8 text-center flex flex-col items-center justify-center">
                                        <div class="w-10 h-10 rounded-full bg-[var(--sm-primary-soft)] text-[var(--sm-primary)] flex items-center justify-center mb-2">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6 6 0 10-12 0v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                                            </svg>
                                        </div>
                                        <p class="text-xs font-bold text-[var(--sm-ink)]">Belum ada notifikasi</p>
                                        <p class="text-[11px] text-[var(--sm-text-muted)] mt-0.5">Semua notifikasi baru akan tampil di sini</p>
                                    </div>
                                @endforelse
                            </div>

                            <div class="admin-notification-footer">
                                <a href="{{ route('admin.notifications.index') }}" class="admin-notification-footer-btn">
                                    <span class="inline-flex items-center gap-2">
                                        <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.9" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                        </svg>
                                        <span>Lihat Semua Notifikasi</span>
                                    </span>
                                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                                    </svg>
                                </a>
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

        <main class="dashboard-main flex-1 p-6">
            <div class="max-w-6xl mx-auto">
                @yield('content')
            </div>
        </main>
    </div>

    @include('layouts.partials.dashboard-drawer-script')
    <script>
        // Auto-hide flash
        setTimeout(() => {
            const el = document.getElementById('adminFlash');
            if (el) { el.style.transition = 'opacity 0.4s'; el.style.opacity = '0'; setTimeout(() => el.remove(), 400); }
        }, 3500);

        const notificationList = document.getElementById('adminNotificationList');
        const notificationCount = document.getElementById('adminNotificationCount');
        const notificationMarkAllWrapper = document.getElementById('adminNotificationMarkAllWrapper');
        const notificationTabs = document.querySelectorAll('[data-notif-tab]');
        const tabCountSemua = document.getElementById('adminNotifTabCountSemua');
        const tabCountProduk = document.getElementById('adminNotifTabCountProduk');
        const tabCountUlasan = document.getElementById('adminNotifTabCountUlasan');
        const notificationUrl = @json(route('admin.notifications.poll'));
        const notificationReadUrl = @json(route('admin.notifications.read', ['notification' => '__ID__']));
        const notificationCsrfToken = @json(csrf_token());
        let activeNotifTab = 'semua';
        let notificationState = @json($notifSummary);
        const escapeHtml = (value) => String(value ?? '').replace(/[&<>'"]/g, (char) => ({'&':'&amp;','<':'&lt;','>':'&gt;',"'":'&#39;','"':'&quot;'}[char]));

        const getNotificationIconJs = (activity, title, category) => {
            const act = (activity || '').toLowerCase();
            const tit = (title || '').toLowerCase();
            if (act.includes('edit') || tit.includes('perubahan')) {
                return {
                    cls: '',
                    svg: '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.9"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>'
                };
            }
            if (act.includes('hapus') || tit.includes('hapus')) {
                return {
                    cls: 'is-warning',
                    svg: '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.9"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>'
                };
            }
            if (category === 'ulasan' || act.includes('ulasan') || tit.includes('ulasan')) {
                return {
                    cls: '',
                    svg: '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.9"><path stroke-linecap="round" stroke-linejoin="round" d="M7 8h10M7 12h6m-5 8l-4 2V6a2 2 0 012-2h12a2 2 0 012 2v10a2 2 0 01-2 2H8z"/></svg>'
                };
            }
            return {
                cls: '',
                svg: '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.9"><path stroke-linecap="round" stroke-linejoin="round" d="M21 16V8a2 2 0 00-1-1.73l-7-4a2 2 0 00-2 0l-7 4A2 2 0 003 8v8a2 2 0 001 1.73l7 4a2 2 0 002 0l7-4A2 2 0 0021 16z"/><path stroke-linecap="round" stroke-linejoin="round" d="M3.27 6.96L12 12.01l8.73-5.05M12 22.08V12"/></svg>'
            };
        };

        const renderNotificationItemJs = (item) => {
            const data = item.data || {};
            const icon = getNotificationIconJs(data.activity, data.title, item.category);
            const isUnread = !item.read_at;
            const actionUrl = notificationReadUrl.replace('__ID__', encodeURIComponent(item.id));
            const unreadDot = isUnread ? '<span class="admin-notif-unread-dot" title="Belum dibaca"></span>' : '';

            return `
                <form method="POST" action="${actionUrl}" class="block w-full m-0 p-0">
                    <input type="hidden" name="_token" value="${escapeHtml(notificationCsrfToken)}">
                    <button type="submit" class="notification-item-btn notification-item-card ${isUnread ? 'is-unread' : 'is-read'}">
                        <div class="flex items-start gap-3">
                            <div class="admin-notif-item-icon ${icon.cls}">
                                ${icon.svg}
                            </div>
                            <div class="min-w-0 flex-1">
                                <div class="flex items-start justify-between gap-2">
                                    <h3 class="text-[13px] font-bold text-[var(--sm-ink)] leading-snug">${escapeHtml(data.title || 'Aktivitas baru')}</h3>
                                    ${unreadDot}
                                </div>
                                <p class="text-[12px] text-[var(--sm-text-muted)] mt-0.5 leading-snug line-clamp-2 break-words">${escapeHtml(data.description || '')}</p>
                                <div class="flex items-center gap-1 mt-1.5 text-[11px] font-medium text-[var(--sm-text-muted)]">
                                    <svg class="w-3.5 h-3.5 opacity-75 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
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

        const renderNotificationEmptyJs = (tab) => {
            const labelMap = {
                produk: 'Belum ada notifikasi produk',
                ulasan: 'Belum ada notifikasi ulasan',
                semua: 'Belum ada notifikasi',
            };
            return `
                <div class="px-4 py-8 text-center flex flex-col items-center justify-center">
                    <div class="w-10 h-10 rounded-full bg-[var(--sm-primary-soft)] text-[var(--sm-primary)] flex items-center justify-center mb-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6 6 0 10-12 0v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                        </svg>
                    </div>
                    <p class="text-xs font-bold text-[var(--sm-ink)]">${labelMap[tab] || labelMap.semua}</p>
                    <p class="text-[11px] text-[var(--sm-text-muted)] mt-0.5">Notifikasi terbaru akan tampil di sini</p>
                </div>
            `;
        };

        const renderAdminNotifications = () => {
            const data = notificationState || {};
            const unreadCount = Number(data.unread_count || 0);
            const counts = data.counts || {};
            const allItems = Array.isArray(data.notifications) ? data.notifications : [];
            const visibleItems = activeNotifTab === 'semua'
                ? allItems
                : allItems.filter((item) => item.category === activeNotifTab);

            if (notificationCount) {
                notificationCount.textContent = unreadCount > 99 ? '99+' : unreadCount;
                notificationCount.classList.toggle('hidden', unreadCount < 1);
            }

            if (notificationMarkAllWrapper) {
                notificationMarkAllWrapper.classList.toggle('hidden', unreadCount < 1);
            }

            if (tabCountSemua) {
                tabCountSemua.textContent = Number(counts.semua ?? allItems.length);
            }
            if (tabCountProduk) {
                const countProduk = Number(counts.produk ?? 0);
                tabCountProduk.textContent = countProduk;
                tabCountProduk.classList.toggle('hidden', countProduk < 1);
            }
            if (tabCountUlasan) {
                const countUlasan = Number(counts.ulasan ?? 0);
                tabCountUlasan.textContent = countUlasan;
                tabCountUlasan.classList.toggle('hidden', countUlasan < 1);
            }

            notificationTabs.forEach((btn) => {
                const isActive = btn.dataset.notifTab === activeNotifTab;
                btn.classList.toggle('is-active', isActive);
                btn.setAttribute('aria-selected', isActive ? 'true' : 'false');
            });

            if (notificationList) {
                notificationList.innerHTML = visibleItems.length
                    ? visibleItems.map(renderNotificationItemJs).join('')
                    : renderNotificationEmptyJs(activeNotifTab);
            }
        };

        notificationTabs.forEach((btn) => {
            btn.addEventListener('click', (event) => {
                event.stopPropagation();
                activeNotifTab = btn.dataset.notifTab || 'semua';
                renderAdminNotifications();
            });
        });

        const refreshAdminNotifications = async () => {
            try {
                const response = await fetch(notificationUrl, { headers: { 'Accept': 'application/json' } });
                if (!response.ok) return;
                notificationState = await response.json();
                renderAdminNotifications();
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
