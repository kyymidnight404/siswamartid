<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PasswordResetLinkController extends Controller
{
    /**
     * Display the password reset link request view.
     */
    public function create(): View
    {
        return view('auth.forgot-password');
    }

    /**
     * Handle an incoming password reset link request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'string', 'email'],
        ]);

        $email = strtolower(trim((string) $request->input('email')));

        // Validasi bahwa email milik akun admin atau penjual yang berstatus aktif
        $user = User::query()
            ->where('email', $email)
            ->where('is_active', true)
            ->whereIn('role', ['admin', 'penjual'])
            ->first();

        // Jika user tidak ditemukan atau dinonaktifkan, berikan pesan sukses generik
        // untuk mencegah user enumeration (membocorkan apakah email terdaftar)
        if (! $user) {
            return back()->with('status', __('passwords.sent'));
        }

        // Kirim tautan reset password melalui Password Broker bawaan Laravel
        $status = Password::sendResetLink(['email' => $email]);

        if ($status === Password::RESET_THROTTLED) {
            return back()->withInput($request->only('email'))
                ->withErrors(['email' => __('passwords.throttled')]);
        }

        return back()->with('status', __('passwords.sent'));
    }
}
