<?php

namespace App\Http\Controllers\Api;

use App\Models\Produk;
use App\Models\Ulasan;
use App\Services\CommentModerationService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ReviewController extends ApiController
{
    /**
     * Menampilkan daftar ulasan publik untuk sebuah produk.
     */
    public function index(Produk $product)
    {
        abort_if($product->status !== 'aktif' || ! ($product->penjual?->user?->is_active), 404);

        $reviews = $product->ulasan()
            ->publicVisible()
            ->latest()
            ->get()
            ->map(fn (Ulasan $review) => $this->reviewData($review));

        return $this->success(
            $reviews,
            'Daftar ulasan berhasil diambil'
        );
    }

    /**
     * Menambahkan ulasan.
     *
     * Endpoint ini dapat digunakan oleh guest.
     */
    public function store(
        Request $request,
        Produk $product,
        CommentModerationService $moderation
    ) {
        abort_if($product->status !== 'aktif' || ! ($product->penjual?->user?->is_active), 404);

        $validated = $request->validate([
            'guest_username' => ['required', 'string', 'max:100'],
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['nullable', 'string', 'max:1000'],
            'komentar' => ['nullable', 'string', 'max:1000'],
        ]);

        /*
         * Ambil komentar dari field "comment".
         * Tetap mendukung "komentar" untuk kompatibilitas dengan
         * request lama.
         */
        $comment = $validated['comment']
            ?? $validated['komentar']
            ?? '';

        /*
         * Bersihkan username dan komentar.
         */
        $guestUsername = trim($validated['guest_username']);
        $comment = trim($comment);

        /*
         * Moderasi nama pengguna dan komentar.
         */
        $userModerated = $moderation->moderate($guestUsername);
        if ($userModerated['detected']) {
            throw ValidationException::withMessages([
                'guest_username' => 'Nama pengguna ulasan mengandung kata yang tidak pantas.',
            ]);
        }

        $moderated = $moderation->moderate($comment);

        if ($moderated['detected']) {
            $commentField = array_key_exists('comment', $request->all()) ? 'comment' : 'komentar';

            throw ValidationException::withMessages([
                $commentField => 'Ulasan ditolak karena terdeteksi kata yang tidak pantas. Silakan ubah isi ulasan lalu coba lagi.',
            ]);
        }

        /*
         * Simpan ulasan.
         */
        $review = $product->ulasan()->create([
            'guest_username' => $guestUsername,
            'rating' => $validated['rating'],
            'komentar' => $comment,
            'original_komentar' => $comment,
            'display_content' => null,
            'is_censored' => false,
            'moderation_status' => 'approved',
            'status' => 'approved',
            'detected_words' => null,
        ]);

        return $this->success(
            $this->reviewData($review),
            'Ulasan berhasil dikirim.',
            201
        );
    }

    /**
     * Format data ulasan untuk API.
     */
    private function reviewData(Ulasan $review): array
    {
        return [
            'id' => $review->id,
            'name' => $review->guest_username,
            'rating' => $review->rating,
            'comment' => $review->public_content,
            'date' => $review->created_at?->toDateString(),
        ];
    }
}
