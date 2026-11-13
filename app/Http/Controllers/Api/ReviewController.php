<?php

namespace App\Http\Controllers\Api;

use App\Models\Produk;
use App\Models\Ulasan;
use App\Services\AdminNotificationService;
use App\Services\CommentModerationService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ReviewController extends ApiController
{
    /**
     * Cari produk aktif berdasarkan id atau slug.
     */
    protected function findPublicProduct(mixed $identifier): Produk
    {
        if ($identifier instanceof Produk) {
            $product = $identifier;
        } else {
            $product = Produk::where('slug', (string) $identifier)
                ->orWhere('id', is_numeric($identifier) ? (int) $identifier : 0)
                ->firstOrFail();
        }

        abort_if(
            $product->status !== 'aktif' || ! ($product->penjual?->user?->is_active),
            404,
            'Produk tidak ditemukan atau tidak aktif'
        );

        return $product;
    }

    /**
     * Menampilkan daftar ulasan publik untuk sebuah produk (dengan paginasi).
     */
    public function index(Request $request, mixed $product)
    {
        $productModel = $this->findPublicProduct($product);
        $perPage = min(max((int) $request->input('per_page', 10), 1), 50);

        $reviews = $productModel->ulasan()
            ->publicVisible()
            ->latest()
            ->paginate($perPage)
            ->withQueryString();

        $reviews->through(fn (Ulasan $review) => $this->reviewData($review));

        return $this->success(
            $reviews,
            'Daftar ulasan berhasil diambil'
        );
    }

    /**
     * Menambahkan ulasan dari pengunjung / guest.
     */
    public function store(
        Request $request,
        mixed $product,
        CommentModerationService $moderation,
        AdminNotificationService $notificationService
    ) {
        $productModel = $this->findPublicProduct($product);

        $validated = $request->validate([
            'guest_username' => ['required', 'string', 'max:100'],
            'rating'         => ['required', 'integer', 'min:1', 'max:5'],
            'comment'        => ['nullable', 'string', 'max:1000'],
            'komentar'       => ['nullable', 'string', 'max:1000'],
        ]);

        $comment = $validated['comment'] ?? $validated['komentar'] ?? '';
        $guestUsername = trim($validated['guest_username']);
        $comment = trim($comment);

        // Validasi kata terlarang dengan pesan yang SAMA persis dengan web
        $usernameMod = $moderation->moderate($guestUsername);
        if ($usernameMod['detected']) {
            throw ValidationException::withMessages([
                'guest_username' => ['Nama pengguna ulasan mengandung kata yang tidak pantas. Silakan perbaiki lalu kirim kembali.'],
            ]);
        }

        $commentMod = $moderation->moderate($comment);
        if ($commentMod['detected']) {
            $commentField = array_key_exists('comment', $request->all()) ? 'comment' : 'komentar';

            throw ValidationException::withMessages([
                $commentField => ['Ulasan tidak terkirim karena mengandung kata yang tidak pantas. Silakan perbaiki isi ulasan lalu kirim kembali.'],
            ]);
        }

        // Simpan ulasan berstatus approved (sama seperti web)
        $review = $productModel->ulasan()->create([
            'guest_username'    => $guestUsername,
            'rating'            => $validated['rating'],
            'komentar'          => $comment,
            'original_komentar' => $comment,
            'display_content'   => null,
            'is_censored'       => false,
            'moderation_status' => 'approved',
            'status'            => 'approved',
            'detected_words'    => null,
        ]);

        // Kirim notifikasi admin seperti web
        try {
            $notificationService->notifyAdmins([
                'title'       => 'Ulasan baru masuk',
                'description' => $guestUsername . ' mengirim ulasan untuk produk "' . $productModel->nama . '".',
                'activity'    => 'ulasan.ditambahkan',
                'actor'       => $guestUsername,
                'entity_type' => 'ulasan',
                'entity_id'   => $review->id,
                'url'         => route('admin.komentar.index', ['status' => 'semua', 'tab' => 'komentar']),
            ]);
        } catch (\Throwable $e) {
            // Safe fallback jika notifikasi gagal
        }

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
        $content = $review->public_content ?? $review->display_content ?? $review->komentar;

        return [
            'id'              => $review->id,
            'guest_username'  => $review->guest_username,
            'name'            => $review->guest_username,
            'rating'          => (int) $review->rating,
            'komentar'        => $content,
            'comment'         => $content,
            'display_content' => $content,
            'date'            => $review->created_at?->format('d M Y') ?? $review->created_at?->toDateString(),
            'created_at'      => $review->created_at?->toISOString(),
        ];
    }
}
