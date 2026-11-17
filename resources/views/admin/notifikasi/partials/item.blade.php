@php
    $data = $item['data'] ?? [];
    $act = strtolower((string) ($data['activity'] ?? ''));
    $tit = strtolower((string) ($data['title'] ?? ''));
    $isUnread = empty($item['read_at']);
    $category = $item['category'] ?? 'produk';
@endphp
<form method="POST" action="{{ route('admin.notifications.read', $item['id']) }}" class="block w-full m-0 p-0">
    @csrf
    <button type="submit" class="notification-item-btn notification-item-card {{ $isUnread ? 'is-unread' : 'is-read' }}">
        <div class="flex items-start gap-3">
            <div class="admin-notif-item-icon {{ str_contains($act, 'hapus') || str_contains($tit, 'hapus') ? 'is-warning' : '' }}">
                @if(str_contains($act, 'edit') || str_contains($tit, 'perubahan'))
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.9"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                @elseif(str_contains($act, 'hapus') || str_contains($tit, 'hapus'))
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.9"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                @elseif($category === 'ulasan')
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.9"><path stroke-linecap="round" stroke-linejoin="round" d="M7 8h10M7 12h6m-5 8l-4 2V6a2 2 0 012-2h12a2 2 0 012 2v10a2 2 0 01-2 2H8z"/></svg>
                @else
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.9"><path stroke-linecap="round" stroke-linejoin="round" d="M21 16V8a2 2 0 00-1-1.73l-7-4a2 2 0 00-2 0l-7 4A2 2 0 003 8v8a2 2 0 001 1.73l7 4a2 2 0 002 0l7-4A2 2 0 0021 16z"/><path stroke-linecap="round" stroke-linejoin="round" d="M3.27 6.96L12 12.01l8.73-5.05M12 22.08V12"/></svg>
                @endif
            </div>
            <div class="min-w-0 flex-1">
                <div class="flex items-start justify-between gap-2">
                    <h3 class="text-[13px] font-bold text-[var(--sm-ink)] leading-snug">
                        {{ $data['title'] ?? 'Aktivitas baru' }}
                    </h3>
                    @if($isUnread)
                        <span class="admin-notif-unread-dot" title="Belum dibaca"></span>
                    @endif
                </div>
                <p class="text-[12px] text-[var(--sm-text-muted)] mt-0.5 leading-snug line-clamp-2 break-words">
                    {{ $data['description'] ?? '' }}
                </p>
                <div class="flex items-center gap-1 mt-1.5 text-[11px] font-medium text-[var(--sm-text-muted)]">
                    <svg class="w-3.5 h-3.5 opacity-75 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span>{{ $item['created_at'] ?? '' }}</span>
                </div>
            </div>
        </div>
    </button>
</form>
