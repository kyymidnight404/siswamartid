<x-guest-layout>
    <div class="mb-5 text-center">
        <x-brand-logo :href="route('public.home')" class="auth-wordmark mb-2.5" label="SiswaMart, kembali ke beranda" />
        <h1 class="font-display text-2xl font-bold text-[var(--sm-ink)]">Konfirmasi Password</h1>
        <p class="mt-1 text-xs text-[var(--sm-text-muted)]">Halaman ini membutuhkan konfirmasi. Masukkan password akunmu untuk melanjutkan.</p>
    </div>

    <form method="POST" action="{{ route('password.confirm') }}" class="space-y-4">
        @csrf

        <!-- Password -->
        <div>
            <label for="password" class="mb-1.5 block text-xs font-bold text-[var(--sm-ink)]">Password</label>
            <input id="password" type="password" name="password" required autocomplete="current-password" autofocus
                   class="form-input" placeholder="Masukkan password akunmu">
            <x-input-error :messages="$errors->get('password')" class="mt-1.5 text-xs font-medium text-[var(--sm-ink)]" />
        </div>

        <button type="submit" class="btn-primary w-full justify-center py-3 text-sm font-bold">
            Konfirmasi
        </button>
    </form>
</x-guest-layout>
