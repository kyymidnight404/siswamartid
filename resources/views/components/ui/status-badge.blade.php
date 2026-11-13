@props(['status'])
@php($badge = match(strtolower((string) $status)) {
    'aktif' => ['Aktif', 'active', 'check'],
    'pending', 'menunggu moderasi', 'menunggu' => ['Menunggu', 'pending', 'clock'],
    'ditolak' => ['Ditolak', 'rejected', 'x'],
    'nonaktif' => ['Nonaktif', 'muted', 'minus'],
    default => [ucfirst((string) $status), 'muted', 'info'],
})
<span {{ $attributes->class(['ui-status-badge', 'ui-status-badge--'.$badge[1]]) }}>
    <svg width="14" height="14" class="flex-shrink-0 inline-block" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
        @switch($badge[2])
            @case('check')
                <path stroke-linecap="round" stroke-linejoin="round" d="m5 12 4 4L19 6" />
                @break
            @case('clock')
                <circle cx="12" cy="12" r="9" />
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 7v5l3 2" />
                @break
            @case('x')
                <path stroke-linecap="round" stroke-linejoin="round" d="m6 6 12 12M18 6 6 18" />
                @break
            @case('minus')
                <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14" />
                @break
            @default
                <circle cx="12" cy="12" r="9" />
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 11v5m0-8h.01" />
        @endswitch
    </svg>
    {{ $badge[0] }}
</span>
