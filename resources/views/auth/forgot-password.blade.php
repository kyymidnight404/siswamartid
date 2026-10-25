<x-guest-layout>
    <div class="mb-5 text-center">
        <x-brand-logo :href="route('public.home')" class="auth-wordmark mb-2.5" label="SiswaMart, kembali ke beranda" />
        <h1 class="font-display text-2xl font-bold text-[var(--sm-ink)]">Lupa password?</h1>
        <p class="mt-1 text-xs text-[var(--sm-text-muted)]">Masukkan email akunmu untuk menerima link reset password.</p>
    </div>

    <!-- Session Status -->
    @if (session('status'))
        <div role="status" class="mb-4 flex items-start gap-2.5 rounded-xl border-2 border-[var(--sm-primary)] bg-[var(--sm-primary-soft)] px-3.5 py-2.5 text-xs font-semibold text-[var(--sm-ink)] shadow-[2px_2px_0_var(--sm-ink)]">
            <svg aria-hidden="true" class="mt-0.5 h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                <polyline points="22 4 12 14.01 9 11.01"></polyline>
            </svg>
            <span>{{ session('status') }}</span>
        </div>
    @endif

    <form method="POST" action="{{ route('password.email') }}" class="space-y-4">
        @csrf

        <!-- Email Address -->
        <div>
            <label for="email" class="mb-1.5 block text-xs font-bold text-[var(--sm-ink)]">Email Terdaftar</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username"
                   class="form-input" placeholder="email@gmail.com">
            <x-input-error :messages="$errors->get('email')" class="mt-1.5 text-xs font-medium text-[var(--sm-ink)]" />
        </div>

        <button type="submit" class="btn-primary w-full justify-center py-3 text-sm font-bold">
            Kirim Link Reset Password
        </button>
    </form>

    <div class="mt-5 text-center">
        <a href="{{ route('login') }}" class="inline-flex items-center gap-1.5 text-xs font-bold text-[var(--sm-ink)] hover:text-[var(--sm-primary-hover)] hover:underline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[var(--sm-ink)]">
            <svg aria-hidden="true" class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <line x1="19" y1="12" x2="5" y2="12"></line>
                <polyline points="12 19 5 12 12 5"></polyline>
            </svg>
            Kembali ke halaman login
        </a>
    </div>
</x-guest-layout>
