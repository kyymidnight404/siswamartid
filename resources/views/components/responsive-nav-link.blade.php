@props(['active'])

@php
$classes = ($active ?? false)
            ? 'block w-full ps-3 pe-4 py-2 border-l-4 border-[var(--sm-border)] text-start text-base font-medium text-[var(--sm-ink)] bg-[var(--sm-primary-soft)] focus:outline-none focus:text-[var(--sm-ink)] focus:bg-[var(--sm-primary-soft)] focus:border-[var(--sm-border)] transition duration-150 ease-in-out'
            : 'block w-full ps-3 pe-4 py-2 border-l-4 border-transparent text-start text-base font-medium text-[var(--sm-text-muted)] hover:text-[var(--sm-ink)] hover:bg-[var(--sm-primary-soft)] hover:border-[var(--sm-border)] focus:outline-none focus:text-[var(--sm-ink)] focus:bg-[var(--sm-primary-soft)] focus:border-[var(--sm-border)] transition duration-150 ease-in-out';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>

