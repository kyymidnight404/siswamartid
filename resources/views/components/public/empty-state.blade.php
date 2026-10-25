@props(['title', 'icon' => 'package'])

<section {{ $attributes->class(['empty-state']) }} role="status">
    <span class="empty-state__icon" aria-hidden="true"><x-public.home-icon :name="$icon" :size="48" /></span>
    <h2 class="mt-5 text-2xl font-bold">{{ $title }}</h2>
    <div class="mt-3 text-[var(--sm-text-muted)]">{{ $slot }}</div>
</section>

