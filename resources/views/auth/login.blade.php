<x-guest-layout>
    <div class="auth-login-content">
        <x-brand-logo static class="auth-wordmark" label="SiswaMart" />
        <h1>Masuk ke akunmu</h1>
        <p class="auth-login-subtitle">Untuk penjual terdaftar dan admin SiswaMart.</p>

        @if ($errors->any())
            <div class="auth-error-summary" role="alert">
                <span class="auth-error-summary__icon" aria-hidden="true">!</span>
                <div><strong>Periksa kembali data masukmu.</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
            </div>
        @endif
        <x-auth-session-status class="auth-session-status" :status="session('status')" />

        <form method="POST" action="{{ route('login') }}" class="auth-login-form" x-data="{ submitting: false, showPassword: false }" @submit="submitting = true">
            @csrf
            <div class="auth-login-field">
                <label for="email">Username atau email</label>
                <input id="email" type="text" name="email" value="{{ old('email') }}" required autocomplete="username" @if(!$errors->any()) autofocus @endif placeholder="Masukkan username atau email">
                <x-input-error :messages="$errors->get('email')" class="auth-field-error" />
            </div>
            <div class="auth-login-field">
                <label for="password">Password</label>
                <div class="auth-password-wrap">
                    <input id="password" x-bind:type="showPassword ? 'text' : 'password'" type="password" name="password" required autocomplete="current-password" placeholder="Masukkan password">
                    <button type="button" class="auth-password-toggle" @click="showPassword = !showPassword" :aria-label="showPassword ? 'Sembunyikan password' : 'Tampilkan password'" :aria-pressed="showPassword.toString()">
                        <svg x-show="!showPassword" aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                        <svg x-show="showPassword" x-cloak aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="m3 3 18 18M10.6 10.6a2 2 0 0 0 2.8 2.8"/><path d="M9.9 5.2A10 10 0 0 1 12 5c6.4 0 10 7 10 7a15 15 0 0 1-3 3.8M6.2 6.2C3.5 8 2 12 2 12s3.6 7 10 7a9.8 9.8 0 0 0 4-.8"/></svg>
                    </button>
                </div>
                <x-input-error :messages="$errors->get('password')" class="auth-field-error" />
            </div>
            <div class="auth-login-options">
                <label for="remember_me" class="auth-remember-label"><input id="remember_me" type="checkbox" name="remember"><span>Ingat saya di perangkat ini</span></label>
                @if(Route::has('password.request'))<a href="{{ route('password.request') }}">Lupa password?</a>@endif
            </div>
            <button type="submit" class="auth-login-submit" x-bind:disabled="submitting"><span x-show="!submitting">Masuk</span><span x-show="submitting" x-cloak>Memproses...</span></button>
        </form>

        <div class="auth-login-divider" aria-hidden="true"><span>atau</span></div>
        <a href="{{ route('google.redirect') }}" class="auth-google-button" aria-label="Lanjutkan dengan Google">
            <svg aria-hidden="true" class="auth-google-logo" viewBox="0 0 48 48"><path fill="#4285F4" d="M44 24c0-1.4-.1-2.7-.4-4H24v8h11.3c-.8 2.2-2.2 4-4.1 5.3l6.2 5.2C41.3 34.8 44 29.8 44 24Z"/><path fill="#34A853" d="M24 44c5.2 0 9.9-2 13.4-5.2l-6.2-5.2c-1.9 1.3-4.3 2.4-7.2 2.4-5.2 0-9.6-3.3-11.3-7.9l-6.5 5C9.5 39.6 16.2 44 24 44Z"/><path fill="#FBBC05" d="M12.7 28.1c-.4-1.3-.7-2.7-.7-4.1s.3-2.8.7-4.1l-6.6-5C4.8 17.4 4 20.6 4 24s.8 6.6 2.1 9.1l6.6-5Z"/><path fill="#EA4335" d="M24 12c3.1 0 5.8 1.2 8 3l5.7-5.7C34 5.8 29.3 4 24 4 16.3 4 9.7 8.3 6.3 14.7l6.6 5C14.5 16.1 18.8 12 24 12Z"/></svg>
            <span>Lanjutkan dengan Google</span>
        </a>
    </div>
</x-guest-layout>
