@extends('layouts.admin')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard Admin')

@section('content')

    <div class="mb-8">
        <h2 class="text-2xl font-bold text-[var(--sm-ink)]">Selamat datang, {{ Auth::user()?->name ?? 'Admin' }}</h2>
        <p class="text-[var(--sm-text-muted)] mt-1 text-sm">Atur penjual, produk, dan ulasan SiswaMart dari satu tempat.</p>
    </div>

    {{-- Stats Row --}}
    <div class="admin-dashboard-stats grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="stat-card flex flex-col items-center text-center">
            <div class="admin-stat-icon">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
            </div>
            <div class="admin-stat-copy">
                <span class="admin-stat-label">Total Penjual</span>
                <p class="admin-stat-number">{{ $totalPenjual }}</p>
                <p class="admin-stat-description">Akun penjual terdaftar</p>
                </div>
        </div>

        <div class="stat-card flex flex-col items-center text-center">
            <div class="admin-stat-icon">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
            </div>
            <div class="admin-stat-copy">
                <span class="admin-stat-label">Produk Aktif</span>
                <p class="admin-stat-number">{{ $produkAktif }}</p>
                <p class="admin-stat-description">Tampil di katalog</p>
            </div>
        </div>

        <div class="stat-card flex flex-col items-center text-center">
            <div class="admin-stat-icon admin-stat-icon--attention">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            </div>
            <div class="admin-stat-copy">
                <span class="admin-stat-label">Perlu Moderasi</span>
                <p class="admin-stat-number">{{ $perluModerasi }}</p>
                <p class="admin-stat-description">Menunggu persetujuan</p>
            </div>
        </div>
    </div>

    <section class="admin-moderation-status {{ $perluModerasi > 0 ? 'has-pending' : '' }} mb-8" aria-live="polite">
        <div class="admin-moderation-status__icon" aria-hidden="true">
            @if($perluModerasi > 0)
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M10.29 3.86l-8.1 14A2 2 0 003.92 21h16.16a2 2 0 001.73-3.14l-8.1-14a2 2 0 00-3.42 0z"/></svg>
            @else
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            @endif
        </div>
        <div class="admin-moderation-status__copy">
            @if($perluModerasi > 0)
                <h3>{{ $perluModerasi }} produk menunggu moderasi</h3>
                <p>Periksa produk baru yang menunggu persetujuan.</p>
            @else
                <h3>Semua produk sudah dimoderasi</h3>
                <p>Produk baru yang menunggu persetujuan akan muncul di sini.</p>
            @endif
        </div>
        @if($perluModerasi > 0)
            <a href="{{ route('admin.produk.index') }}" class="admin-moderation-status__action">Buka moderasi</a>
        @endif
    </section>

    {{-- Shortcut Cards --}}
    <h2 class="admin-dashboard-shortcuts-title">Pintasan</h2>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="admin-card text-center p-6 flex flex-col items-center justify-center gap-3 hover:shadow-md transition">
            <div class="w-12 h-12 bg-[var(--sm-primary-soft)] rounded-2xl flex items-center justify-center text-[var(--sm-primary)] border-2 border-[var(--sm-border)] shadow-sm">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
            </div>
            <h3 class="font-display font-semibold text-[var(--sm-ink)]">Kelola Penjual</h3>
            <p class="text-[var(--sm-text-muted)] text-xs max-w-sm mb-2">Buat, edit, dan atur akun siswa yang berjualan di SiswaMart.</p>
            <a href="{{ route('admin.penjual.index') }}" class="btn-primary">Kelola Penjual</a>
        </div>
        
        <div class="admin-card text-center p-6 flex flex-col items-center justify-center gap-3 hover:shadow-md transition">
            <div class="w-12 h-12 bg-[var(--sm-primary-soft)] rounded-2xl flex items-center justify-center text-[var(--sm-primary)] border-2 border-[var(--sm-border)] shadow-sm">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
            </div>
            <h3 class="font-display font-semibold text-[var(--sm-ink)]">Moderasi Produk</h3>
            <p class="text-[var(--sm-text-muted)] text-xs max-w-sm mb-2">Cek produk baru dan permohonan sebelum ditampilkan di katalog.</p>
            <a href="{{ route('admin.produk.index') }}" class="btn-primary">Cek Produk</a>
        </div>

        <div class="admin-card text-center p-6 flex flex-col items-center justify-center gap-3 hover:shadow-md transition">
            <div class="w-12 h-12 bg-[var(--sm-primary-soft)] rounded-2xl flex items-center justify-center text-[var(--sm-ink)] border-2 border-[var(--sm-border)] shadow-sm">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
            </div>
            <h3 class="font-display font-semibold text-[var(--sm-ink)]">Rekap Penjual</h3>
            <p class="text-[var(--sm-text-muted)] text-xs max-w-sm mb-2">Lihat performa toko, rating, ulasan, serta aktivitas upload & hapus produk.</p>
            <a href="{{ route('admin.laporan.index', ['kategori' => 'penjual']) }}" class="btn-primary">Buka Rekap Penjual</a>
        </div>
    </div>

@endsection
