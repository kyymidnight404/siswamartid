<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Penjual') | SiswaMart</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,400&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Fredoka:wght@500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
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
            background: var(--sm-surface);
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
    </style>
    <link rel="stylesheet" href="{{ asset('css/siswamart-theme.css') }}?v={{ file_exists(public_path('css/siswamart-theme.css')) ? filemtime(public_path('css/siswamart-theme.css')) : time() }}">
</head>
<body class="seller-dashboard flex min-h-screen">


    {{-- ── Sidebar ── --}}
    <div class="dashboard-sidebar-backdrop" data-sidebar-close aria-hidden="true"></div>
    <aside class="app-sidebar hidden lg:flex sticky top-0 h-screen max-h-screen" id="dashboardSidebar">
        <div class="h-16 flex items-center px-6 border-b border-[var(--sm-border)]">
            <x-brand-logo :href="route('public.home')" label="SiswaMart — Beranda" />
            <span class="ml-3 text-[10px] bg-[var(--sm-primary)] text-[var(--sm-border)] border-2 border-[var(--sm-border)] shadow-[2px_2px_0px_0px_var(--sm-border)] px-2 py-0.5 rounded-full font-bold">PENJUAL</span>
        </div>

        <nav class="flex-1 overflow-y-auto py-4">
            <div class="sidebar-section-title">Menu utama</div>
            <a href="{{ route('penjual.dashboard') }}" class="sidebar-link {{ request()->routeIs('penjual.dashboard') ? 'active' : '' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                Dashboard
            </a>

            <div class="sidebar-section-title mt-4">Katalog saya</div>
            <a href="{{ route('penjual.produk.index') }}" class="sidebar-link {{ request()->routeIs('penjual.produk.*') && !request()->routeIs('penjual.produk.create') ? 'active' : '' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                Produk Saya
            </a>
            <a href="{{ route('penjual.produk.create') }}" class="sidebar-link {{ request()->routeIs('penjual.produk.create') ? 'active' : '' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Tambah Produk
            </a>
            <a href="{{ route('penjual.laporan') }}" class="sidebar-link {{ request()->routeIs('penjual.laporan') ? 'active' : '' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                Laporan Promosi
            </a>
        </nav>

        {{-- User info --}}
        <div class="p-4 border-t-2 border-[var(--sm-border)] flex-shrink-0">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-9 h-9 rounded-full bg-[var(--sm-primary-soft)] flex items-center justify-center text-[var(--sm-ink)] border-2 border-[var(--sm-border)] font-bold text-sm shadow-[2px_2px_0px_0px_var(--sm-border)]">
                    {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                </div>
                <div class="min-w-0">
                    <p class="text-sm font-bold text-[var(--sm-ink)] truncate">{{ Auth::user()->name }}</p>
                    <p class="text-[var(--sm-text-muted)] font-semibold text-xs truncate">{{ Auth::user()->penjual?->nama_toko ?? 'Toko' }}</p>
                </div>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('public.produk.index') }}" target="_blank" class="flex-1 text-center font-bold text-xs text-[var(--sm-ink)] hover:bg-[var(--sm-primary-soft)] border-2 border-[var(--sm-border)] shadow-[2px_2px_0px_0px_var(--sm-border)] py-1.5 rounded-full transition-all hover:translate-x-[-1px] hover:translate-y-[-1px] hover:shadow-[3px_3px_0px_0px_var(--sm-border)]">
                    Katalog
                </a>
                <form method="POST" action="{{ route('logout') }}" class="flex-1">
                    @csrf
                    <button class="sidebar-logout-button w-full text-xs font-bold border-2 py-1.5 rounded-full transition-all">Keluar</button>
                </form>
            </div>
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
                    <h1 class="font-display font-black tracking-tight text-xl text-[var(--sm-ink)] truncate">@yield('page-title', 'Dashboard Penjual')</h1>
                </div>
                <div class="flex items-center gap-2 flex-shrink-0">
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
    </script>
</body>
</html>


