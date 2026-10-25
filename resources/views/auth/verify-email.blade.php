<x-guest-layout>
    <div class="mb-5 text-center">
        <x-brand-logo :href="route('public.home')" class="auth-wordmark mb-2.5" label="SiswaMart, kembali ke beranda" />
        <h1 class="font-display text-2xl font-bold text-[var(--sm-ink)]">Verifikasi Email</h1>
        <p class="mt-1 text-xs text-[var(--sm-text-muted)]">Terima kasih sudah bergabung! Silakan cek inbox emailmu dan klik link verifikasi untuk melanjutkan.</p>
    </div>

    @if (session('status') == 'verification-link-sent')
        <div role="status" class="mb-4 flex items-start gap-2.5 rounded-xl border-2 border-[var(--sm-primary)] bg-[var(--sm-primary-soft)] px-3.5 py-2.5 text-xs font-semibold text-[var(--sm-ink)] shadow-[2px_2px_0_var(--sm-ink)]">
            <svg aria-hidden="true" class="mt-0.5 h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                <polyline points="22 4 12 14.01 9 11.01"></polyline>
            </svg>
            <span>Link verifikasi baru telah dikirimkan ke alamat emailmu.</span>
        </div>
    @endif

    <div class="space-y-3 pt-2">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <button type="submit" class="btn-primary w-full justify-center py-3 text-sm font-bold">
                Kirim Ulang Link Verifikasi
            </button>
        </form>

        <form method="POST" action="{{ route('logout') }}" class="text-center">
            @csrf
            <button type="submit" class="inline-flex items-center gap-1.5 text-xs font-bold text-[var(--sm-text-muted)] hover:text-[var(--sm-ink)] hover:underline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[var(--sm-ink)]">
                Keluar dari akun
            </button>
        </form>
    </div>
</x-guest-layout>
