@extends('layouts.admin')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard Admin')

@section('content')

    <div class="mb-8">
        <h2 class="text-2xl font-bold text-[var(--text-main)]">Selamat datang, {{ Auth::user()?->name ?? 'Admin' }} 👋</h2>
        <p class="text-[var(--text-muted)] mt-1 text-sm">Atur penjual, produk, dan ulasan SiswaMart dari satu tempat.</p>
    </div>

    {{-- Stats Row --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="stat-card flex flex-col items-center text-center">
            <div class="flex flex-col items-center gap-2 mb-3">
                <div class="w-10 h-10 rounded-lg flex items-center justify-center bg-[#e8f0fe]">
                    <svg class="w-5 h-5 text-[#1a73e8]" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                </div>
                <span class="text-xs font-medium px-2 py-1 rounded-full bg-[#e8f0fe] text-[#1a73e8]">Total Penjual</span>
            </div>
            <p class="text-3xl font-display font-bold text-[var(--text-main)]">{{ $totalPenjual }}</p>
            <p class="text-[var(--text-muted)] text-xs mt-1">Akun penjual terdaftar</p>
        </div>

        <div class="stat-card flex flex-col items-center text-center">
            <div class="flex flex-col items-center gap-2 mb-3">
                <div class="w-10 h-10 rounded-lg flex items-center justify-center bg-[#e6f4ea]">
                    <svg class="w-5 h-5 text-[#137333]" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                </div>
                <span class="text-xs font-medium px-2 py-1 rounded-full bg-[#e6f4ea] text-[#137333]">Produk Aktif</span>
            </div>
            <p class="text-3xl font-display font-bold text-[var(--text-main)]">{{ $produkAktif }}</p>
            <p class="text-[var(--text-muted)] text-xs mt-1">Tampil di katalog</p>
        </div>

        <div class="stat-card flex flex-col items-center text-center">
            <div class="flex flex-col items-center gap-2 mb-3">
                <div class="w-10 h-10 rounded-lg flex items-center justify-center bg-[#fef7e0]">
                    <svg class="w-5 h-5 text-[#b06000]" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                </div>
                <span class="text-xs font-medium px-2 py-1 rounded-full bg-[#fef7e0] text-[#b06000]">Perlu Moderasi</span>
            </div>
            <p class="text-3xl font-display font-bold text-[var(--text-main)]">{{ $perluModerasi }}</p>
            <p class="text-[var(--text-muted)] text-xs mt-1">Menunggu persetujuan</p>
        </div>
    </div>

    {{-- Shortcut Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="admin-card text-center p-6 flex flex-col items-center justify-center gap-3 hover:shadow-md transition">
            <div class="w-12 h-12 bg-[#e8f0fe] rounded-2xl flex items-center justify-center text-[#1a73e8] border-2 border-[var(--border-color)] shadow-sm">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
            </div>
            <h3 class="font-display font-semibold text-[var(--text-main)]">Kelola Penjual</h3>
            <p class="text-[var(--text-muted)] text-xs max-w-sm mb-2">Buat, edit, dan atur akun siswa yang berjualan di SiswaMart.</p>
            <a href="{{ route('admin.penjual.index') }}" class="btn-primary">Kelola Penjual</a>
        </div>
        
        <div class="admin-card text-center p-6 flex flex-col items-center justify-center gap-3 hover:shadow-md transition">
            <div class="w-12 h-12 bg-[#fef7e0] rounded-2xl flex items-center justify-center text-[#b06000] border-2 border-[var(--border-color)] shadow-sm">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
            </div>
            <h3 class="font-display font-semibold text-[var(--text-main)]">Moderasi Produk</h3>
            <p class="text-[var(--text-muted)] text-xs max-w-sm mb-2">Cek produk baru dan permohonan sebelum ditampilkan di katalog.</p>
            <a href="{{ route('admin.produk.index') }}" class="btn-primary" style="background:#fbbc04; color:#202124;">Cek Produk</a>
        </div>

        <div class="admin-card text-center p-6 flex flex-col items-center justify-center gap-3 hover:shadow-md transition">
            <div class="w-12 h-12 bg-[#f3e8fd] rounded-2xl flex items-center justify-center text-[#7b1fa2] border-2 border-[var(--border-color)] shadow-sm">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
            </div>
            <h3 class="font-display font-semibold text-[var(--text-main)]">Rekap Penjual</h3>
            <p class="text-[var(--text-muted)] text-xs max-w-sm mb-2">Lihat performa toko, rating, ulasan, serta aktivitas upload & hapus produk.</p>
            <a href="{{ route('admin.laporan.index', ['kategori' => 'penjual']) }}" class="btn-primary" style="background:#ce93d8; color:#1f1325;">Buka Rekap Penjual</a>
        </div>
    </div>

@endsection
