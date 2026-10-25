<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\InvalidStateException;
use Throwable;

class GoogleAuthController extends Controller
{
    public function redirect(Request $request): RedirectResponse
    {
        if (! config('services.google.client_id') || ! config('services.google.client_secret')) {
            return redirect()->route('login')->withErrors([
                'google' => 'Login Google belum dikonfigurasi. Hubungi Admin SiswaMart.',
            ]);
        }

        $this->syncRedirectUrl($request);

        return $this->getGoogleDriver()->scopes(['openid', 'profile', 'email'])->redirect();
    }

    public function callback(Request $request): RedirectResponse
    {
        if ($request->filled('error')) {
            return redirect()->route('login')->withErrors([
                'google' => 'Login Google dibatalkan. Silakan coba lagi.',
            ]);
        }

        $this->syncRedirectUrl($request);

        $driver = $this->getGoogleDriver();

        try {
            $googleUser = $driver->user();
        } catch (InvalidStateException $e) {
            // Fallback ke stateless jika session state/cookie terputus saat cross-site redirect (umum terjadi pada browser mobile)
            try {
                $googleUser = $this->getGoogleDriver()->stateless()->user();
            } catch (Throwable $statelessException) {
                report($statelessException);

                return redirect()->route('login')->withErrors([
                    'google' => 'Google tidak dapat memverifikasi login saat ini. Silakan coba lagi.',
                ]);
            }
        } catch (Throwable $exception) {
            report($exception);

            return redirect()->route('login')->withErrors([
                'google' => 'Google tidak dapat memverifikasi login saat ini. Silakan coba lagi.',
            ]);
        }

        $googleId = (string) $googleUser->getId();
        $email = Str::lower(trim((string) $googleUser->getEmail()));
        $rawUser = (array) $googleUser->getRaw();
        $emailVerified = filter_var(
            $rawUser['email_verified'] ?? $rawUser['verified_email'] ?? false,
            FILTER_VALIDATE_BOOLEAN
        );

        if ($googleId === '' || $email === '' || ! $emailVerified) {
            return redirect()->route('login')->withErrors([
                'google' => 'Gunakan akun Google dengan alamat email yang sudah terverifikasi.',
            ]);
        }

        // 1. Cari pengguna berdasarkan google_id terlebih dahulu
        $user = User::query()->where('google_id', $googleId)->first();

        // 2. Jika belum ditemukan via google_id, cari berdasarkan email terverifikasi
        if (! $user) {
            $user = User::query()->whereRaw('LOWER(email) = ?', [$email])->first();

            if ($user) {
                // Keamanan penautan: cegah penautan jika akun sudah terikat dengan Google ID lain
                if ($user->google_id && $user->google_id !== $googleId) {
                    return redirect()->route('login')->withErrors([
                        'google' => 'Akun SiswaMart ini sudah tertaut dengan akun Google yang berbeda.',
                    ]);
                }

                // Tautkan Google ID ke akun yang sudah terdaftar
                $user->google_id = $googleId;
                if (! $user->email_verified_at) {
                    $user->email_verified_at = now();
                }
                $user->save();
            }
        }

        // 3. Google login hanya memberikan akses ke akun yang sudah terdaftar di SiswaMart
        if (! $user) {
            return redirect()->route('login')->withErrors([
                'google' => 'Email Google ini belum terdaftar di SiswaMart. Hubungi Admin untuk mendapatkan akun.',
            ]);
        }

        // 4. Periksa apakah akun aktif
        if (! $user->is_active) {
            return redirect()->route('login')->withErrors([
                'google' => 'Akun SiswaMart ini sedang dinonaktifkan. Hubungi Admin.',
            ]);
        }

        // 5. Periksa profil toko penjual jika akun adalah penjual
        if ($user->role === 'penjual' && ! $user->penjual) {
            return redirect()->route('login')->withErrors([
                'google' => 'Akun penjual belum memiliki profil toko. Hubungi Admin SiswaMart.',
            ]);
        }

        if (! $user->email_verified_at) {
            $user->forceFill(['email_verified_at' => now()])->save();
        }

        Auth::login($user);
        $request->session()->regenerate();

        if ($user->role === 'penjual') {
            $user->penjual->update([
                'last_login_at' => now(),
                'last_activity_at' => now(),
                'last_activity_type' => 'login',
            ]);
        }

        $destination = $user->role === 'admin'
            ? route('admin.dashboard')
            : route('penjual.dashboard');

        return redirect()->intended($destination);
    }

    /**
     * Dapatkan instance Socialite Google driver dengan dukungan CA bundle lokal jika tersedia (mis. Laragon di Windows).
     */
    private function getGoogleDriver()
    {
        $driver = Socialite::driver('google');

        $caPath = 'C:/laragon/etc/ssl/cacert.pem';
        if (! app()->runningUnitTests() && file_exists($caPath) && method_exists($driver, 'setHttpClient')) {
            $driver->setHttpClient(new \GuzzleHttp\Client([
                'verify' => $caPath,
            ]));
        }

        return $driver;
    }

    /**
     * Menyesuaikan URL redirect callback secara dinamis jika lingkungan eksekusi
     * (misal server hosting produksi vs lokal) tidak sesuai dengan konfigurasi statis .env.
     */
    private function syncRedirectUrl(Request $request): void
    {
        $configured = config('services.google.redirect');
        if (! $configured) {
            return;
        }

        $currentHost = $request->getHost();
        $configuredHost = parse_url($configured, PHP_URL_HOST);

        if ($configuredHost && $currentHost && strcasecmp($configuredHost, $currentHost) !== 0) {
            $isConfiguredLocal = in_array(strtolower($configuredHost), ['localhost', '127.0.0.1'], true);
            $isCurrentLocal = in_array(strtolower($currentHost), ['localhost', '127.0.0.1'], true) || str_ends_with(strtolower($currentHost), '.test');

            if ($isConfiguredLocal !== $isCurrentLocal) {
                config(['services.google.redirect' => route('google.callback')]);
            }
        }
    }
}
