<x-guest-layout>
    <div class="mb-5 text-center">
        <x-brand-logo :href="route('public.home')" class="auth-wordmark mb-2.5" label="SiswaMart, kembali ke beranda" />
        <h1 class="font-display text-2xl font-bold text-[var(--sm-ink)]">Reset Password</h1>
        <p class="mt-1 text-xs text-[var(--sm-text-muted)]">Buat password baru untuk akun SiswaMart kamu.</p>
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

    <form method="POST" action="{{ route('password.store') }}" class="space-y-3.5" x-data="{ showPassword: false, showPasswordConfirmation: false }">
        @csrf

        <!-- Password Reset Token -->
        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <!-- Email Address -->
        <div>
            <label for="email" class="mb-1.5 block text-xs font-bold text-[var(--sm-ink)]">Email Terdaftar</label>
            <div id="email" class="form-input flex items-center bg-[var(--sm-bg)] text-[var(--sm-text-muted)]" aria-label="Email akun yang akan direset" aria-readonly="true">
                {{ old('email', $request->email) }}
            </div>
            <input type="hidden" name="email" value="{{ old('email', $request->email) }}">
            <x-input-error :messages="$errors->get('email')" class="mt-1.5 text-xs font-medium text-[var(--sm-ink)]" />
        </div>

        <!-- Password -->
        <div>
            <label for="password" class="mb-1.5 block text-xs font-bold text-[var(--sm-ink)]">Password Baru</label>
            <div class="auth-password-wrap relative">
                <input id="password" x-bind:type="showPassword ? 'text' : 'password'" type="password" name="password" required autocomplete="new-password"
                       class="form-input pr-14" placeholder="Minimal 8 karakter">
                <button type="button" class="auth-password-toggle absolute right-1 top-1 grid h-12 w-12 place-items-center rounded-xl text-[var(--sm-ink)] hover:bg-[var(--sm-primary-soft)] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[var(--sm-primary)]" @click="showPassword = !showPassword"
                        :aria-label="showPassword ? 'Sembunyikan password baru' : 'Tampilkan password baru'" :aria-pressed="showPassword.toString()">
                    <svg class="h-5 w-5" x-show="!showPassword" aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                    <svg class="h-5 w-5" x-show="showPassword" x-cloak aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="m3 3 18 18M10.6 10.6a2 2 0 0 0 2.8 2.8"/><path d="M9.9 5.2A10 10 0 0 1 12 5c6.4 0 10 7 10 7a15 15 0 0 1-3 3.8M6.2 6.2C3.5 8 2 12 2 12s3.6 7 10 7a9.8 9.8 0 0 0 4-.8"/></svg>
                </button>
            </div>
            <x-input-error :messages="$errors->get('password')" class="mt-1.5 text-xs font-medium text-[var(--sm-ink)]" />
        </div>

        <!-- Confirm Password -->
        <div>
            <label for="password_confirmation" class="mb-1.5 block text-xs font-bold text-[var(--sm-ink)]">Konfirmasi Password Baru</label>
            <div class="auth-password-wrap relative">
                <input id="password_confirmation" x-bind:type="showPasswordConfirmation ? 'text' : 'password'" type="password" name="password_confirmation" required autocomplete="new-password"
                       class="form-input pr-14" placeholder="Ulangi password baru">
                <button type="button" class="auth-password-toggle absolute right-1 top-1 grid h-12 w-12 place-items-center rounded-xl text-[var(--sm-ink)] hover:bg-[var(--sm-primary-soft)] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[var(--sm-primary)]" @click="showPasswordConfirmation = !showPasswordConfirmation"
                        :aria-label="showPasswordConfirmation ? 'Sembunyikan konfirmasi password baru' : 'Tampilkan konfirmasi password baru'" :aria-pressed="showPasswordConfirmation.toString()">
                    <svg class="h-5 w-5" x-show="!showPasswordConfirmation" aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                    <svg class="h-5 w-5" x-show="showPasswordConfirmation" x-cloak aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="m3 3 18 18M10.6 10.6a2 2 0 0 0 2.8 2.8"/><path d="M9.9 5.2A10 10 0 0 1 12 5c6.4 0 10 7 10 7a15 15 0 0 1-3 3.8M6.2 6.2C3.5 8 2 12 2 12s3.6 7 10 7a9.8 9.8 0 0 0 4-.8"/></svg>
                </button>
            </div>
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
