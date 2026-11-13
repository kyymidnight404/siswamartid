<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Produk;
use App\Models\Ulasan;
use App\Services\CommentModerationService;
use App\Services\AdminNotificationService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;

class UlasanController extends Controller
{
    /**
     * Simpan ulasan guest baru dengan auto-moderation filter.
     */
    public function store(Request $request, Produk $produk, CommentModerationService $moderationService, AdminNotificationService $notificationService): RedirectResponse
    {
        abort_if($produk->status !== 'aktif' || ! ($produk->penjual?->user?->is_active), 404);

        $validated = $request->validate([
            'guest_username' => ['required', 'string', 'max:100'],
            'rating'         => ['required', 'integer', 'min:1', 'max:5'],
            'komentar'       => ['nullable', 'string', 'max:1000'],
        ]);

        $originalText = $validated['komentar'] ?? '';

        // Tolak sebelum penyimpanan jika nama pengguna atau isi ulasan mengandung kata terlarang.
        $usernameMod = $moderationService->moderate($validated['guest_username']);
        $commentMod  = $moderationService->moderate($originalText);

        if ($usernameMod['detected'] || $commentMod['detected']) {
            $warningMsg = $usernameMod['detected']
                ? 'Nama pengguna ulasan mengandung kata yang tidak pantas. Silakan perbaiki lalu kirim kembali.'
                : 'Ulasan tidak terkirim karena mengandung kata yang tidak pantas. Silakan perbaiki isi ulasan lalu kirim kembali.';

            return redirect()->to(route('public.produk.show', $produk->slug) . '#ulasan')
                ->withInput()
                ->with('review_warning', $warningMsg);
        }

        $ulasan = Ulasan::create([
            'produk_id'         => $produk->id,
            'guest_username'    => $validated['guest_username'],
            'rating'            => $validated['rating'],
            'komentar'          => $originalText,
            'original_komentar' => $originalText,
            'display_content'   => null,
            'is_censored'       => false,
            'moderation_status' => 'approved',
            'status'            => 'approved',
            'detected_words'    => null,
        ]);

        $notificationService->notifyAdmins([
            'title' => 'Ulasan baru masuk',
            'description' => $validated['guest_username'] . ' mengirim ulasan untuk produk "' . $produk->nama . '".',
            'activity' => 'ulasan.ditambahkan',
            'actor' => $validated['guest_username'],
            'entity_type' => 'ulasan',
            'entity_id' => $ulasan->id,
            'url' => route('admin.komentar.index', ['status' => 'semua', 'tab' => 'komentar']),
        ]);

        return redirect()->to(route('public.produk.show', $produk->slug) . '#ulasan')
            ->with('success', 'Ulasanmu berhasil dikirim.');
    }
}
