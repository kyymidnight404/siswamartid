@props(['active'])

@php
$classes = ($active ?? false)
            ? 'inline-flex items-center px-1 pt-1 border-b-2 border-[var(--sm-border)] text-sm font-medium leading-5 text-[var(--sm-ink)] focus:outline-none focus:border-[var(--sm-border)] transition duration-150 ease-in-out'
            : 'inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-medium leading-5 text-[var(--sm-text-muted)] hover:text-[var(--sm-text-muted)] hover:border-[var(--sm-border)] focus:outline-none focus:text-[var(--sm-text-muted)] focus:border-[var(--sm-border)] transition duration-150 ease-in-out';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>

