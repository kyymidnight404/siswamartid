<div class="dashboard-sidebar-brand">
    <x-brand-logo :href="route('public.home')" label="SiswaMart — Beranda" />
    @if (filled($role ?? null))
        <span class="dashboard-role-badge">{{ $role }}</span>
    @endif
</div>
