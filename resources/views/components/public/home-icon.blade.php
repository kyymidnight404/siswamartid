{{-- Tabler Icons, MIT License. Copyright (c) 2020 Paweł Kuna. https://github.com/tabler/tabler-icons --}}
@props([
    'name' => 'burger',
    'size' => 24,
    'strokeWidth' => 2,
    'filled' => false,
])

<svg
    {{ $attributes->merge(['class' => 'home-icon-svg flex-shrink-0', 'aria-hidden' => 'true']) }}
    xmlns="http://www.w3.org/2000/svg"
    width="{{ $size }}"
    height="{{ $size }}"
    viewBox="0 0 24 24"
    fill="none"
    stroke="currentColor"
    stroke-width="{{ $strokeWidth }}"
    stroke-linecap="round"
    stroke-linejoin="round"
>
    @switch($name)
        @case('dessert')
        @case('cake')
            <path d="M3 20h18v-8a3 3 0 0 0-3-3H6a3 3 0 0 0-3 3v8" />
            <path d="M3 14.8c.3.14.65.2 1 .2a2.4 2.4 0 0 0 2-1 2.4 2.4 0 0 1 2-1 2.4 2.4 0 0 1 2 1 2.4 2.4 0 0 0 2 1 2.4 2.4 0 0 0 2-1 2.4 2.4 0 0 1 2-1 2.4 2.4 0 0 1 2 1 2.4 2.4 0 0 0 2 1c.35 0 .7-.06 1-.2" />
            <path d="M12 4l1.47 1.64a2 2 0 1 1-3.02.1L12 4Z" />
            @break
        @case('makanan')
        @case('makanan-berat')
        @case('food')
        @case('burger')
            <path d="M4 15h16a4 4 0 0 1-4 4H8a4 4 0 0 1-4-4Z" />
            <path d="M12 4c3.78 0 6.95 2.13 7.79 5H4.21C5.05 6.13 8.22 4 12 4Z" />
            <path d="M5 12h14" />
            @break
        @case('minuman')
        @case('drink')
        @case('glass-full')
            <path d="M8 21h8M12 15v6" />
            <path d="m17 3 1 7c0 3.01-2.69 5-6 5s-6-1.99-6-5l1-7h10Z" />
            <path d="M6 10a5 5 0 0 1 6 0 5 5 0 0 0 6 0" />
            @break
        @case('snack')
        @case('cookie')
            <path d="M8 13v.01M12 17v.01M12 12v.01M16 14v.01M11 8v.01" />
            <path d="M13.15 3.48 15.8 4.6a4 4 0 0 0 4.66 6.14l.05.13a3 3 0 0 1 0 2.3c-.5.79-.84 1.4-1.02 1.85-.19.46-.41 1.2-.66 2.22a3 3 0 0 1-1.63 1.62c-1.05.27-1.79.49-2.22.66-.47.2-1.08.54-1.85 1.03a3 3 0 0 1-2.3 0c-.8-.5-1.41-.85-1.85-1.03-.47-.19-1.2-.41-2.22-.66a3 3 0 0 1-1.63-1.62c-.27-1.06-.49-1.8-.66-2.22-.2-.48-.54-1.1-1.03-1.86a3 3 0 0 1 0-2.3c.48-.74.83-1.36 1.03-1.85.16-.42.38-1.16.66-2.22A3 3 0 0 1 6.75 5.2c1.03-.26 1.77-.48 2.22-.66.46-.2 1.08-.54 1.85-1.03a3 3 0 0 1 2.3-.03Z" />
            @break
        @case('location')
        @case('map-pin')
            <path d="M9 11a3 3 0 1 0 6 0 3 3 0 0 0-6 0Z" />
            <path d="m17.66 16.66-4.24 4.24a2 2 0 0 1-2.83 0l-4.24-4.24a8 8 0 1 1 11.31 0Z" />
            @break
        @case('whatsapp')
        @case('brand-whatsapp')
            <path d="m3 21 1.65-3.8a9 9 0 1 1 3.4 2.9L3 21Z" />
            <path d="M9 10a.5.5 0 0 0 1 0V9a.5.5 0 0 0-1 0v1a5 5 0 0 0 5 5h1a.5.5 0 0 0 0-1h-1a.5.5 0 0 0 0 1" />
            @break
        @case('school')
            <path d="m22 9-10-4L2 9l10 4 10-4v6" />
            <path d="M6 10.6V16a6 3 0 0 0 12 0v-5.4" />
            @break
        @case('price')
        @case('coin')
            <circle cx="12" cy="12" r="9" />
            <path d="M14.8 9a2 2 0 0 0-1.8-1h-2a2 2 0 1 0 0 4h2a2 2 0 1 1 0 4h-2a2 2 0 0 1-1.8-1M12 7v10" />
            @break
        @case('seller')
        @case('building-store')
            <path d="M3 21h18M3 7v1a3 3 0 0 0 6 0V7m0 1a3 3 0 0 0 6 0V7m0 1a3 3 0 0 0 6 0V7H3l2-4h14l2 4M5 21V10.85M19 21V10.85M9 21v-4a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v4" />
            @break
        @case('star')
            <path d="m12 17.75-6.17 3.24 1.18-6.87-5-4.87 6.9-1L12 2l3.09 6.25 6.9 1-5 4.87 1.18 6.87L12 17.75Z" @if($filled) fill="currentColor" @endif />
            @break
        @case('eye')
        @case('views')
            <path d="M10 12a2 2 0 1 0 4 0 2 2 0 0 0-4 0Z" />
            <path d="M21 12c-2.4 4-5.4 6-9 6s-6.6-2-9-6c2.4-4 5.4-6 9-6s6.6 2 9 6Z" />
            @break
        @case('arrow-right')
            <path d="M5 12h14m-6-6 6 6-6 6" />
            @break
        @case('search')
            <circle cx="10" cy="10" r="7" />
            <path d="m21 21-6-6" />
            @break
        @case('filter')
        @case('funnel')
            <path d="M4 5h16l-6.5 7.5V19l-3 1v-7.5L4 5Z" />
            @break
        @case('click')
            <path d="m5 3 12 12-5 .5 3 5-2.5 1.5-3-5L7 21 5 3Z" />
            @break
        @case('check')
        @case('circle-check')
            <circle cx="12" cy="12" r="9" />
            <path d="m9 12 2 2 4-4" />
            @break
        @case('chat')
        @case('message-circle')
            <path d="m3 20 1.3-3.9c-2.32-3.44-1.43-7.87 2.1-10.37 3.53-2.5 8.59-2.3 11.85.48 3.25 2.78 3.7 7.27 1.03 10.5-2.67 3.24-7.62 4.22-11.57 2.3L3 20Z" />
            @break
        @case('instagram')
        @case('brand-instagram')
            <path d="M7 3h10a4 4 0 0 1 4 4v10a4 4 0 0 1-4 4H7a4 4 0 0 1-4-4V7a4 4 0 0 1 4-4Z" />
            <circle cx="12" cy="12" r="3.5" />
            <path d="M17 7v.01" />
            @break
        @case('tiktok')
        @case('brand-tiktok')
            <path d="M21 7.92v4.03a9.95 9.95 0 0 1-5-1.95v4.5a6.5 6.5 0 1 1-8-6.33v4.33a2.5 2.5 0 1 0 4 2V3h4.08a6 6 0 0 0 4.92 4.92Z" />
            @break
        @case('menu-2')
            <path d="M4 6h16M4 12h16M4 18h16" />
            @break
        @case('plus')
            <path d="M12 5v14M5 12h14" />
            @break
        @case('package')
            <path d="m12 3 9 4.5v9L12 21l-9-4.5v-9L12 3Z" /><path d="m3.3 7.7 8.7 4.4 8.7-4.4M12 21v-9" />
            @break
        @case('bell')
            <path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9M10 21h4" />
            @break
        @case('chart-bar')
        @case('dashboard')
        @case('chart')
            <path d="M3 3v18h18M8 16v-5m5 5V6m5 10v-8" />
            @break
        @case('category')
            <rect x="4" y="4" width="6" height="6" rx="1" /><rect x="14" y="4" width="6" height="6" rx="1" /><rect x="4" y="14" width="6" height="6" rx="1" /><rect x="14" y="14" width="6" height="6" rx="1" />
            @break
        @case('users')
        @case('user')
            <path d="M16 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2M10 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8ZM20 8v6m3-3h-6" />
            @break
        @case('logout')
            <path d="M10 17l5-5-5-5m5 5H3m9-9h6a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-6" />
            @break
        @case('clock')
            <circle cx="12" cy="12" r="9" /><path d="M12 7v5l3 2" />
            @break
        @case('activity')
            <path d="M3 12h4l3-8 4 16 3-8h4" />
            @break
        @case('edit')
            <path d="M12 20h9M16.5 3.5a2.12 2.12 0 0 1 3 3L8 18l-4 1 1-4Z" />
            @break
        @case('info-circle')
            <circle cx="12" cy="12" r="9" /><path d="M12 11v5m0-8h.01" />
            @break
        @case('shield')
            <path d="M12 3 20 6v5c0 5-3.4 8.2-8 10-4.6-1.8-8-5-8-10V6l8-3Z" /><path d="m9 12 2 2 4-4" />
            @break
        @case('sparkles')
            <path d="m12 3 1.5 5.5L19 10l-5.5 1.5L12 17l-1.5-5.5L5 10l5.5-1.5L12 3Z" /><path d="m19 16 .8 2.2L22 19l-2.2.8L19 22l-.8-2.2L16 19l2.2-.8L19 16Z" />
            @break
        @case('alert-triangle')
            <path d="m10.3 3.9-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.7-3.1l-8-14a2 2 0 0 0-3.4 0ZM12 9v4m0 4h.01" />
            @break
        @case('calendar')
            <rect x="3" y="5" width="18" height="16" rx="2" /><path d="M16 3v4M8 3v4M3 11h18" />
            @break
        @case('printer')
            <path d="M6 9V3h12v6M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2M6 14h12v7H6z" />
            @break
        @case('download')
            <path d="M4 17v2a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-2" />
            <path d="m7 11 5 5 5-5" />
            <path d="M12 4v12" />
            @break
        @case('trend-up')
            <path d="m3 17 6-6 4 4 8-8M15 7h6v6" />
            @break
        @case('trend-down')
            <path d="m3 7 6 6 4-4 8 8m-6 0h6v-6" />
            @break
        @case('trash')
            <path d="M4 7h16M10 11v6m4-6v6M5 7l1 14h12l1-14M9 7V4h6v3" />
            @break
        @case('photo')
        @case('image')
            <rect x="3" y="3" width="18" height="18" rx="2" /><circle cx="8.5" cy="8.5" r="1.5" /><path d="m21 15-5-5L5 21" />
            @break
        @case('phone')
            <path d="M5 4h4l2 5-3 2a16 16 0 0 0 5 5l2-3 5 2v4a2 2 0 0 1-2 2A16 16 0 0 1 3 6a2 2 0 0 1 2-2Z" />
            @break
        @case('arrow-left')
            <path d="M19 12H5m6 6-6-6 6-6" />
            @break
        @case('circle-x')
            <circle cx="12" cy="12" r="9" /><path d="m9 9 6 6m0-6-6 6" />
            @break
        @case('settings')
            <circle cx="12" cy="12" r="3" /><path d="m19.4 15 .1.1 1.4 1.1-1.4 2.4-1.7-.7a8 8 0 0 1-1.8 1l-.3 1.8h-2.8l-.3-1.8a8 8 0 0 1-1.8-1l-1.7.7-1.4-2.4 1.4-1.1a8 8 0 0 1 0-2l-1.4-1.1 1.4-2.4 1.7.7a8 8 0 0 1 1.8-1l.3-1.8h2.8l.3 1.8a8 8 0 0 1 1.8 1l1.7-.7 1.4 2.4-1.4 1.1a8 8 0 0 1 0 2Z" />
            @break
        @case('x')
            <path d="m18 6-12 12M6 6l12 12" />
            @break
        @default
            <path d="M4 15h16a4 4 0 0 1-4 4H8a4 4 0 0 1-4-4ZM12 4c3.78 0 6.95 2.13 7.79 5H4.21C5.05 6.13 8.22 4 12 4ZM5 12h14" />
    @endswitch
</svg>

