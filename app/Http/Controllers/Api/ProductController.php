<?php

namespace App\Http\Controllers\Api;

use App\Models\Produk;
use App\Models\ProdukViewLog;
use App\Services\WhatsappUrlService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ProductController extends ApiController
{
    /**
     * Cari produk aktif berdasarkan id atau slug.
     */
    public function findPublicProduct(mixed $identifier): Produk
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
     * Daftar produk publik dengan paginasi, pencarian, filter kategori, dan pengurutan.
     */
    public function index(Request $request)
    {
        $query = Produk::query()
            ->publicVisible()
            ->with(['kategori', 'penjual.user'])
            ->withAvg(['ulasan' => fn ($reviews) => $reviews->publicVisible()], 'rating')
            ->withCount(['ulasan' => fn ($reviews) => $reviews->publicVisible()]);

        $search = $request->string('search', $request->string('q'))->trim()->toString();
        if ($search !== '') {
            $escapedSearch = addcslashes($search, '%_\\');
            $query->where(function ($products) use ($escapedSearch) {
                $products->where('nama', 'like', "%{$escapedSearch}%")
                    ->orWhere('deskripsi', 'like', "%{$escapedSearch}%")
                    ->orWhereHas('penjual', fn ($seller) => $seller->where('nama_toko', 'like', "%{$escapedSearch}%"));
            });
        }

        if ($category = $request->input('category', $request->input('category_id', $request->input('kategori')))) {
            $query->whereHas('kategori', fn ($categories) => $categories->where('id', $category)->orWhere('slug', $category));
        }

        if ($sellerId = $request->input('seller_id', $request->input('penjual_id'))) {
            $query->where('penjual_id', $sellerId);
        }

        if (is_numeric($request->input('min_price'))) {
            $query->where('harga', '>=', (float) $request->input('min_price'));
        }

        if (is_numeric($request->input('max_price'))) {
            $query->where('harga', '<=', (float) $request->input('max_price'));
        }

        match ($request->input('sort')) {
            'price_asc', 'termurah' => $query->orderBy('harga'),
            'price_desc', 'termahal' => $query->orderByDesc('harga'),
            'popular', 'terpopuler' => $query->orderByDesc('views'),
            'rating' => $query->orderByDesc('ulasan_avg_rating'),
            default => $query->latest(),
        };

        $perPage = min(max((int) $request->input('per_page', 12), 1), 50);
        $products = $query->paginate($perPage)->withQueryString();
        $products->through(fn (Produk $product) => $this->productData($product));

        return $this->success($products, 'Daftar produk berhasil diambil');
    }

    /**
     * Detail produk by slug atau id.
     */
    public function show(Request $request, mixed $product)
    {
        $productModel = $this->findPublicProduct($product);

        $deviceId = $request->header('X-Device-Id');
        $cacheKey = $deviceId ? "view_product_{$productModel->id}_{$deviceId}" : null;

        // Dedupe view jika request menyertakan X-Device-Id
        if (! $cacheKey || ! Cache::has($cacheKey)) {
            $productModel->increment('views');
            try {
                ProdukViewLog::create([
                    'produk_id' => $productModel->id,
                    'type'      => 'view',
                ]);
            } catch (\Throwable $e) {
                // Safe fallback
            }

            if ($cacheKey) {
                Cache::put($cacheKey, true, now()->addHours(24));
            }
        }

        $productModel->load(['kategori', 'penjual.user'])
            ->loadAvg(['ulasan' => fn ($reviews) => $reviews->publicVisible()], 'rating')
            ->loadCount(['ulasan' => fn ($reviews) => $reviews->publicVisible()]);

        return $this->success($this->productData($productModel, true), 'Detail produk berhasil diambil');
    }

    /**
     * Endpoint untuk mencatat view secara eksplisit dengan deduplikasi berbasis device_id.
     */
    public function trackView(Request $request, mixed $product)
    {
        $productModel = $this->findPublicProduct($product);

        $deviceId = $request->header('X-Device-Id');
        $cacheKey = $deviceId ? "view_product_{$productModel->id}_{$deviceId}" : null;
        $incremented = false;

        if (! $cacheKey || ! Cache::has($cacheKey)) {
            $productModel->increment('views');
            $incremented = true;
            try {
                ProdukViewLog::create([
                    'produk_id' => $productModel->id,
                    'type'      => 'view',
                ]);
            } catch (\Throwable $e) {
                // Safe fallback
            }

            if ($cacheKey) {
                Cache::put($cacheKey, true, now()->addHours(24));
            }
        }

        $productModel->refresh();

        return $this->success([
            'id' => $productModel->id,
            'views' => (int) $productModel->views,
            'incremented' => $incremented,
        ], 'View produk berhasil dicatat');
    }

    /**
     * Catat interaksi WhatsApp dan kembalikan URL wa.me.
     */
    public function trackWhatsapp(mixed $product, WhatsappUrlService $waService)
    {
        $productModel = $this->findPublicProduct($product);
        $result = $waService->generateForProduct($productModel);

        if (! $result['success']) {
            return $this->error($result['message'], [], 422);
        }

        return $this->success([
            'whatsapp_url' => $result['whatsapp_url'],
            'phone'        => $result['phone'],
            'message'      => $result['message'],
            'store_closed' => $result['store_closed'] ?? false,
        ], 'Tautan WhatsApp berhasil dibuat');
    }

    /**
     * Format representasi data produk untuk respons API.
     */
    public function productData(Produk $product, bool $detail = false): array
    {
        $imageUrl = $product->foto_utama ? url('storage/' . $product->foto_utama) : null;
        $sellerImage = $product->penjual?->foto_toko ? url('storage/' . $product->penjual->foto_toko) : null;
        $rating = round((float) ($product->ulasan_avg_rating ?? $product->average_rating), 1);
        $reviewCount = (int) ($product->ulasan_count ?? $product->review_count);

        $data = [
            'id'               => $product->id,
            'slug'             => $product->slug,
            'nama'             => $product->nama,
            'name'             => $product->nama,
            'harga'            => (float) $product->harga,
            'price'            => (float) $product->harga,
            'deskripsi'        => $product->deskripsi,
            'description'      => $product->deskripsi,
            'stok'             => $product->stok,
            'stock_status'     => $product->stok,
            'available'        => $product->stok !== 'habis',
            'label_promosi'    => $product->label_promosi,
            'promotion_label'  => $product->label_promosi,
            'foto_utama'       => $imageUrl,
            'image'            => $imageUrl,
            'views'            => (int) ($product->views ?? 0),
            'rating'           => $rating,
            'average_rating'   => $rating,
            'review_count'     => $reviewCount,
            'jumlah_ulasan'    => $reviewCount,
            'nama_toko'        => $product->penjual?->nama_toko ?? '',
            'status_toko'      => $product->penjual?->status_toko ?? 'buka',
            'kategori'         => $product->kategori ? [
                'id'   => $product->kategori->id,
                'nama' => $product->kategori->nama,
                'name' => $product->kategori->nama,
                'slug' => $product->kategori->slug,
                'icon' => $product->kategori->icon,
            ] : null,
            'category'         => $product->kategori ? [
                'id'   => $product->kategori->id,
                'nama' => $product->kategori->nama,
                'name' => $product->kategori->nama,
                'slug' => $product->kategori->slug,
                'icon' => $product->kategori->icon,
            ] : null,
            'penjual'          => $product->penjual ? [
                'id'             => $product->penjual->id,
                'nama_toko'      => $product->penjual->nama_toko,
                'name'           => $product->penjual->nama_toko,
                'deskripsi_toko' => $product->penjual->deskripsi_toko,
                'lokasi_kelas'   => $product->penjual->lokasi_kelas,
                'class_location' => $product->penjual->lokasi_kelas,
                'status_toko'    => $product->penjual->status_toko ?? 'buka',
                'store_status'   => $product->penjual->status_toko ?? 'buka',
                'foto_toko'      => $sellerImage,
                'image'          => $sellerImage,
            ] : null,
            'seller'           => $product->penjual ? [
                'id'             => $product->penjual->id,
                'nama_toko'      => $product->penjual->nama_toko,
                'name'           => $product->penjual->nama_toko,
                'deskripsi_toko' => $product->penjual->deskripsi_toko,
                'lokasi_kelas'   => $product->penjual->lokasi_kelas,
                'class_location' => $product->penjual->lokasi_kelas,
                'status_toko'    => $product->penjual->status_toko ?? 'buka',
                'store_status'   => $product->penjual->status_toko ?? 'buka',
                'foto_toko'      => $sellerImage,
                'image'          => $sellerImage,
            ] : null,
            'created_at'       => $product->created_at?->toISOString(),
        ];

        if ($detail) {
            $related = Produk::publicVisible()
                ->with(['kategori', 'penjual.user'])
                ->withAvg(['ulasan' => fn ($q) => $q->publicVisible()], 'rating')
                ->withCount(['ulasan' => fn ($q) => $q->publicVisible()])
                ->where('kategori_id', $product->kategori_id)
                ->where('id', '!=', $product->id)
                ->latest()
                ->limit(4)
                ->get()
                ->map(fn (Produk $item) => $this->productData($item, false));

            $data['related_products'] = $related;
            $data['produk_terkait'] = $related;
        }

        return $data;
    }
}
