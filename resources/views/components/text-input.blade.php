@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'border-2 border-[var(--sm-ink)] focus:border-[var(--sm-primary)] focus:ring-[var(--sm-primary)] rounded-[var(--r-input)] shadow-[2px_2px_0_var(--sm-ink)]']) }}>

