<x-guest-layout>
    @php
        $googleMessage = $errors->first('google');
        $googleAccountMissing = \Illuminate\Support\Str::contains($googleMessage, 'belum terdaftar');
    @endphp

    <div class="mb-5 text-center">
        <a href="{{ route('public.home') }}" class="auth-wordmark mb-2.5 inline-flex rounded-full px-4 py-1.5 font-display text-sm font-bold leading-none" aria-label="SiswaMart, kembali ke beranda">
            <span class="auth-wordmark__siswamart">Siswa</span><span class="auth-wordmark__mart">Mart</span>
        </a>
        <h1 class="font-display text-2xl font-bold text-[var(--text-main)]">Masuk ke akunmu</h1>
        <p class="mt-1 text-xs text-[var(--ink-soft)]">Pilih cara masuk ke akun SiswaMart</p>
    </div>

    <x-auth-session-status class="mb-3 text-xs font-medium text-green-600" :status="session('status')" />

    {{-- Form Login Username & Password --}}
    <form method="POST" action="{{ route('login') }}" class="space-y-3.5">
        @csrf

        <div>
            <label for="email" class="mb-1.5 block text-xs font-bold text-[var(--text-main)]">Username atau Email</label>
            <input id="email" type="text" name="email" value="{{ old('email') }}" required autofocus autocomplete="username"
                   class="form-input" placeholder="Masukkan username atau email">
            <x-input-error :messages="$errors->get('email')" class="mt-1.5 text-xs font-medium text-[#b42318]" />
        </div>

        <div>
            <label for="password" class="mb-1.5 block text-xs font-bold text-[var(--text-main)]">Password</label>
            <input id="password" type="password" name="password" required autocomplete="current-password"
                   class="form-input" placeholder="Masukkan password">
            <x-input-error :messages="$errors->get('password')" class="mt-1.5 text-xs font-medium text-[#b42318]" />
        </div>

        <div class="flex items-center justify-between pt-1">
            <label for="remember_me" class="inline-flex cursor-pointer items-center gap-2 text-xs font-medium text-[var(--text-muted)]">
                <input id="remember_me" type="checkbox" name="remember" class="h-4 w-4 rounded border-2 border-[var(--ink)] accent-[var(--brand)] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[var(--ink)]">
                <span>Ingat saya di perangkat ini</span>
            </label>
        </div>

        <button type="submit" class="btn-primary w-full justify-center py-3 text-sm font-bold">Masuk</button>
    </form>

    {{-- Divider --}}
    <div class="my-4 text-center text-xs font-semibold text-[var(--ink-soft)]">
        atau dengan Google
    </div>

    {{-- Google OAuth Button --}}
    <a href="{{ route('google.redirect') }}" class="auth-google-button btn-outline flex w-full items-center justify-center gap-3 rounded-full px-5 py-3 text-center text-sm font-bold shadow-[2px_2px_0_var(--ink)]" aria-label="Lanjutkan dengan Google">
        <span class="auth-google-icon inline-flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-white shadow-xs">
            <svg aria-hidden="true" class="h-4 w-4" viewBox="0 0 48 48">
                <path fill="#4285F4" d="M44 24c0-1.4-.1-2.7-.4-4H24v8h11.3c-.8 2.2-2.2 4-4.1 5.3l6.2 5.2C41.3 34.8 44 29.8 44 24Z"/>
                <path fill="#34A853" d="M24 44c5.2 0 9.9-2 13.4-5.2l-6.2-5.2c-1.9 1.3-4.3 2.4-7.2 2.4-5.2 0-9.6-3.3-11.3-7.9l-6.5 5C9.5 39.6 16.2 44 24 44Z"/>
                <path fill="#FBBC05" d="M12.7 28.1c-.4-1.3-.7-2.7-.7-4.1s.3-2.8.7-4.1l-6.6-5C4.8 17.4 4 20.6 4 24s.8 6.6 2.1 9.1l6.6-5Z"/>
                <path fill="#EA4335" d="M24 12c3.1 0 5.8 1.2 8 3l5.7-5.7C34 5.8 29.3 4 24 4 16.3 4 9.7 8.3 6.3 14.7l6.6 5C14.5 16.1 18.8 12 24 12Z"/>
            </svg>
        </span>
        <span class="leading-none">Lanjutkan dengan Google</span>
    </a>

    @if ($googleMessage)
        <div role="alert" class="mt-3.5 flex items-start gap-2.5 rounded-xl border-2 px-3.5 py-2.5 text-xs font-semibold shadow-[2px_2px_0_var(--ink)] {{ $googleAccountMissing ? 'border-[#b42318] bg-[#fff1f0] text-[#7a271a]' : 'border-[#9a6700] bg-[#fff7d6] text-[#6b4b00]' }}">
            <svg aria-hidden="true" class="mt-0.5 h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M12 9v4m0 4h.01M10.3 3.9 1.8 18.2A2 2 0 0 0 3.5 21h17a2 2 0 0 0 1.7-2.8L13.7 3.9a2 2 0 0 0-3.4 0Z"/>
            </svg>
            <span>{{ $googleMessage }}</span>
        </div>
    @endif

    <aside class="mt-4 text-center text-xs leading-normal text-[var(--ink-soft)]">
        Login khusus untuk <strong class="text-[var(--ink)]">penjual terdaftar</strong> & <strong class="text-[var(--ink)]">Admin</strong>.
    </aside>
</x-guest-layout>
