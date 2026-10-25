<button {{ $attributes->merge(['type' => 'button', 'class' => 'inline-flex items-center px-4 py-2 bg-[var(--sm-surface)] border-2 border-[var(--sm-ink)] rounded-full font-semibold text-xs text-[var(--sm-ink)] uppercase tracking-widest shadow-[4px_4px_0_var(--sm-ink)] hover:bg-[var(--sm-primary-soft)] focus:outline-none focus:ring-2 focus:ring-[var(--sm-ink)] focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150']) }}>
    {{ $slot }}
</button>

