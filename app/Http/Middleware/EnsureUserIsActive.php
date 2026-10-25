<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsActive
{
    /**
     * Pastikan user yang sedang terautentikasi memiliki status is_active = true.
     * Jika akun dinonaktifkan, cabut token (untuk API) atau bersihkan sesi (untuk web)
     * lalu kembalikan respon error yang sesuai.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && ! $user->is_active) {
            // Cabut token API jika request terautentikasi melalui personal access token
            if (method_exists($user, 'currentAccessToken') && $user->currentAccessToken()) {
                $user->tokens()->delete();
            }

            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Akun Anda sedang dinonaktifkan oleh pihak sekolah. Silakan hubungi administrator.',
                ], 403);
            }

            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->withErrors([
                'email' => 'Akun Anda sedang dinonaktifkan oleh pihak sekolah. Silakan hubungi administrator.',
            ]);
        }

        return $next($request);
    }
}
