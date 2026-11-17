@extends('layouts.admin')

@section('title', 'Notifikasi')
@section('page-title', 'Notifikasi')

@section('content')
<div class="admin-card">
    <div class="admin-card-header flex-wrap gap-3">
        <div>
            <h2 class="text-base font-extrabold text-[var(--sm-ink)]">Semua Notifikasi</h2>
            <p class="text-xs text-[var(--sm-text-muted)] mt-0.5">Update terbaru aktivitas produk dan ulasan di SiswaMart.</p>
        </div>

        @if($unreadCount > 0)
            <form method="POST" action="{{ route('admin.notifications.read_all') }}" class="m-0 p-0">
                @csrf
                <button type="submit" class="btn-outline text-xs">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                    </svg>
                    Tandai semua dibaca ({{ $unreadCount }})
                </button>
            </form>
        @endif
    </div>

    <div class="px-5 pt-4 pb-2 border-b border-[color-mix(in_srgb,var(--sm-ink)_10%,transparent)] flex flex-wrap items-center gap-2">
        @php
            $tabs = [
                'semua' => ['label' => 'Semua', 'count' => $counts['semua'] ?? 0],
                'produk' => ['label' => 'Produk', 'count' => $counts['produk'] ?? 0],
                'ulasan' => ['label' => 'Ulasan', 'count' => $counts['ulasan'] ?? 0],
            ];
        @endphp
        @foreach($tabs as $key => $tab)
            <a href="{{ route('admin.notifications.index', array_filter(['kategori' => $key === 'semua' ? null : $key, 'per_page' => request('per_page')])) }}"
               class="admin-notification-tab {{ $activeCategory === $key ? 'is-active' : '' }}">
                <span>{{ $tab['label'] }}</span>
                @if($tab['count'] > 0 || $key === 'semua')
                    <span class="admin-notification-tab__count">{{ $tab['count'] }}</span>
                @endif
            </a>
        @endforeach
    </div>

    <div class="p-4 sm:p-5 flex flex-col gap-2.5">
        @forelse($notifications as $item)
            @include('admin.notifikasi.partials.item', ['item' => $item])
        @empty
            <div class="px-4 py-10 text-center flex flex-col items-center justify-center">
                <div class="w-11 h-11 rounded-full bg-[var(--sm-primary-soft)] text-[var(--sm-primary)] flex items-center justify-center mb-2.5">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6 6 0 10-12 0v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                    </svg>
                </div>
                <p class="text-sm font-bold text-[var(--sm-ink)]">Belum ada notifikasi</p>
                <p class="text-xs text-[var(--sm-text-muted)] mt-0.5">Notifikasi aktivitas produk dan ulasan akan tampil di sini.</p>
            </div>
        @endforelse
    </div>

    @include('components.admin.table-pagination', ['paginator' => $notifications])
</div>
@endsection
