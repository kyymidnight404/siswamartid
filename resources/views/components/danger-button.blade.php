<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center px-4 py-2 bg-[var(--sm-primary)] border-2 border-[var(--sm-ink)] rounded-full font-semibold text-xs text-[var(--sm-on-primary)] uppercase tracking-widest shadow-[4px_4px_0_var(--sm-ink)] hover:bg-[var(--sm-primary-hover)] focus:outline-none focus:ring-2 focus:ring-[var(--sm-ink)] focus:ring-offset-2 transition ease-in-out duration-150']) }}>
    {{ $slot }}
</button>

