@props(['eyebrow' => null])

<header {{ $attributes->class(['page-header']) }}>
    @if($eyebrow)<p class="mb-2 text-sm font-bold uppercase tracking-wide text-[var(--sm-text-muted)]">{{ $eyebrow }}</p>@endif
    <h1 class="font-display font-bold uppercase">{{ $title ?? $slot }}</h1>
    @isset($description)<p class="mt-3 max-w-3xl text-base leading-relaxed text-[var(--sm-text-muted)]">{{ $description }}</p>@endisset
</header>

