@extends('layouts.admin')

@section('title', 'Detail Penjual')
@section('page-title', 'Detail Penjual')

@section('content')
    @php($profile = $penjual->penjual)

    <div class="space-y-5">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <div>
                    <p class="text-xs font-bold uppercase tracking-wide text-[var(--sm-text-muted)]">Data akun penjual</p>
                    <h2 class="text-xl font-bold text-[var(--sm-ink)]">{{ $profile?->nama_toko ?? $penjual->name }}</h2>
                </div>
            </div>
            <a href="{{ route('admin.penjual.edit', $penjual) }}" class="btn-primary inline-flex items-center justify-center px-5 py-2">Edit Data Penjual</a>
        </div>

        <section class="admin-card flex flex-col gap-4 p-5 sm:flex-row sm:items-center sm:p-6">
            @if($profile?->foto_toko)
                <img src="{{ asset('storage/' . $profile->foto_toko) }}" alt="Foto toko {{ $profile->nama_toko }}" class="h-20 w-20 shrink-0 rounded-2xl border-2 border-[var(--sm-border)] object-cover sm:h-24 sm:w-24">
            @else
                <div class="flex h-20 w-20 shrink-0 items-center justify-center rounded-2xl border-2 border-[var(--sm-border)] bg-[var(--sm-primary-soft)] font-display text-3xl font-bold sm:h-24 sm:w-24" aria-hidden="true">{{ strtoupper(substr($penjual->name, 0, 1)) }}</div>
            @endif
            <div class="min-w-0 flex-1">
                <div class="flex flex-wrap items-center gap-2">
                    <h3 class="text-2xl font-bold text-[var(--sm-ink)]">{{ $penjual->name }}</h3>
                    <span class="badge {{ $penjual->is_active ? 'badge-aktif' : 'badge-ditolak' }} px-3 py-1 text-xs">{{ $penjual->is_active ? 'Akun aktif' : 'Akun nonaktif' }}</span>
                </div>
                <p class="mt-1 break-all text-sm text-[var(--sm-text-muted)]">{{ $penjual->email }}</p>
                <p class="mt-2 text-sm font-semibold">{{ $profile?->nama_toko ?? 'Nama toko belum tersedia' }}</p>
            </div>
        </section>

        <section class="grid grid-cols-2 gap-3 xl:grid-cols-4" aria-label="Ringkasan produk penjual">
            <div class="stat-card p-4"><p class="text-xs font-semibold text-[var(--sm-text-muted)]">Total produk</p><p class="mt-1 text-2xl font-black">{{ number_format($profile?->produk_count ?? 0) }}</p></div>
            <div class="stat-card p-4"><p class="text-xs font-semibold text-[var(--sm-text-muted)]">Produk aktif</p><p class="mt-1 text-2xl font-black text-[var(--sm-ink)]">{{ number_format($profile?->produk_aktif_count ?? 0) }}</p></div>
            <div class="stat-card p-4"><p class="text-xs font-semibold text-[var(--sm-text-muted)]">Menunggu moderasi</p><p class="mt-1 text-2xl font-black">{{ number_format($profile?->produk_pending_count ?? 0) }}</p></div>
            <div class="stat-card p-4"><p class="text-xs font-semibold text-[var(--sm-text-muted)]">Total dilihat</p><p class="mt-1 text-2xl font-black">{{ number_format((int) ($profile?->produk_sum_views ?? 0)) }}</p></div>
        </section>

        <div class="grid grid-cols-1 gap-5 xl:grid-cols-2">
            <section class="admin-card p-5 sm:p-6">
                <h3 class="mb-4 text-lg font-bold">Informasi toko</h3>
                <dl class="grid grid-cols-1 gap-x-6 gap-y-4 text-sm sm:grid-cols-2">
                    <div><dt class="text-[var(--sm-text-muted)]">Nama toko</dt><dd class="mt-1 font-semibold">{{ $profile?->nama_toko ?? '-' }}</dd></div>
                    <div><dt class="text-[var(--sm-text-muted)]">NISN</dt><dd class="mt-1 font-semibold">{{ $profile?->nisn ?? '-' }}</dd></div>
                    <div><dt class="text-[var(--sm-text-muted)]">Nomor WhatsApp</dt><dd class="mt-1 font-semibold">{{ $profile?->nomor_whatsapp ?? '-' }}</dd></div>
                    <div><dt class="text-[var(--sm-text-muted)]">Lokasi / Kelas</dt><dd class="mt-1 font-semibold">{{ $profile?->lokasi_kelas ?? '-' }}</dd></div>
                    <div class="sm:col-span-2"><dt class="text-[var(--sm-text-muted)]">Alamat</dt><dd class="mt-1 whitespace-pre-line font-semibold">{{ $profile?->alamat ?? '-' }}</dd></div>
                    @if($profile?->deskripsi_toko)
                        <div class="sm:col-span-2"><dt class="text-[var(--sm-text-muted)]">Deskripsi toko</dt><dd class="mt-1 whitespace-pre-line leading-relaxed">{{ $profile->deskripsi_toko }}</dd></div>
                    @endif
                </dl>
            </section>

            <section class="admin-card p-5 sm:p-6">
                <h3 class="mb-4 text-lg font-bold">Aktivitas akun</h3>
                <dl class="grid grid-cols-1 gap-4 text-sm sm:grid-cols-2">
                    <div class="rounded-2xl border border-[var(--sm-border)]/20 bg-[var(--sm-bg)] p-4"><dt class="text-[var(--sm-text-muted)]">Login terakhir</dt><dd class="mt-1 font-semibold">{{ $profile?->last_login_at?->format('d M Y, H:i') ?? 'Belum ada' }}</dd></div>
                    <div class="rounded-2xl border border-[var(--sm-border)]/20 bg-[var(--sm-bg)] p-4"><dt class="text-[var(--sm-text-muted)]">Aktivitas terakhir</dt><dd class="mt-1 font-semibold">{{ $profile?->last_activity_at?->format('d M Y, H:i') ?? 'Belum ada' }}</dd></div>
                    <div class="rounded-2xl border border-[var(--sm-border)]/20 bg-[var(--sm-bg)] p-4 sm:col-span-2"><dt class="text-[var(--sm-text-muted)]">Jenis aktivitas</dt><dd class="mt-1 font-semibold">{{ $profile?->last_activity_type ?? 'Belum tercatat' }}</dd></div>
                    <div class="rounded-2xl border border-[var(--sm-border)]/20 bg-[var(--sm-bg)] p-4 sm:col-span-2"><dt class="text-[var(--sm-text-muted)]">Produk ditolak</dt><dd class="mt-1 font-semibold">{{ number_format($profile?->produk_ditolak_count ?? 0) }}</dd></div>
                </dl>
            </section>
        </div>
    </div>
@endsection
