<x-guest-layout>
    <div class="mb-5 text-center">
        <x-brand-logo :href="route('public.home')" class="auth-wordmark mb-2.5" label="SiswaMart, kembali ke beranda" />
        <h1 class="font-display text-2xl font-bold text-[var(--sm-ink)]">Reset Password</h1>
        <p class="mt-1 text-xs text-[var(--sm-text-muted)]">Buat password baru untuk akun SiswaMart kamu.</p>
    </div>

    <form method="POST" action="{{ route('password.store') }}" class="space-y-3.5">
        @csrf

        <!-- Password Reset Token -->
        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <!-- Email Address -->
        <div>
            <label for="email" class="mb-1.5 block text-xs font-bold text-[var(--sm-ink)]">Email Terdaftar</label>
            <input id="email" type="email" name="email" value="{{ old('email', $request->email) }}" required autofocus autocomplete="username"
                   class="form-input" placeholder="email@gmail.com">
            <x-input-error :messages="$errors->get('email')" class="mt-1.5 text-xs font-medium text-[var(--sm-ink)]" />
        </div>

        <!-- Password -->
        <div>
            <label for="password" class="mb-1.5 block text-xs font-bold text-[var(--sm-ink)]">Password Baru</label>
            <input id="password" type="password" name="password" required autocomplete="new-password"
                   class="form-input" placeholder="Minimal 8 karakter">
            <x-input-error :messages="$errors->get('password')" class="mt-1.5 text-xs font-medium text-[var(--sm-ink)]" />
        </div>

        <!-- Confirm Password -->
        <div>
            <label for="password_confirmation" class="mb-1.5 block text-xs font-bold text-[var(--sm-ink)]">Konfirmasi Password Baru</label>
            <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password"
                   class="form-input" placeholder="Ulangi password baru">
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-1.5 text-xs font-medium text-[var(--sm-ink)]" />
        </div>

        <button type="submit" class="btn-primary w-full justify-center py-3 text-sm font-bold">
            Simpan Password Baru
        </button>
    </form>

    <div class="mt-4 text-center">
        <a href="{{ route('login') }}" class="inline-flex items-center gap-1.5 text-xs font-bold text-[var(--sm-ink)] hover:text-[var(--sm-primary-hover)] hover:underline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[var(--sm-ink)]">
            <svg aria-hidden="true" class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <line x1="19" y1="12" x2="5" y2="12"></line>
                <polyline points="12 19 5 12 12 5"></polyline>
            </svg>
            Batal & kembali ke login
        </a>
    </div>
</x-guest-layout>
