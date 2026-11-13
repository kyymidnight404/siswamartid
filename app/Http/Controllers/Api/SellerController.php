<?php

namespace App\Http\Controllers\Api;

use App\Models\Penjual;
use App\Models\Produk;
use App\Models\Ulasan;
use Illuminate\Http\Request;

class SellerController extends ApiController
{
    public function index()
    {
        $sellers = Penjual::query()
            ->whereHas('user', fn ($q) => $q->where('is_active', true))
            ->withCount(['produk as active_product_count' => fn ($q) => $q->publicVisible()])
            ->orderBy('nama_toko')
            ->get()
            ->map(fn (Penjual $seller) => $this->sellerData($seller, false));

        return $this->success($sellers, 'Daftar penjual berhasil diambil');
    }

    public function show(mixed $seller, ProductController $productController)
    {
        if ($seller instanceof Penjual) {
            $sellerModel = $seller;
        } else {
            $sellerModel = Penjual::where('id', is_numeric($seller) ? (int) $seller : 0)
                ->orWhere('nama_toko', (string) $seller)
                ->firstOrFail();
        }

        abort_unless($sellerModel->user?->is_active, 404);

        $products = $sellerModel->produk()
            ->publicVisible()
            ->with(['kategori', 'penjual.user'])
            ->withAvg(['ulasan' => fn ($reviews) => $reviews->publicVisible()], 'rating')
            ->withCount(['ulasan' => fn ($reviews) => $reviews->publicVisible()])
            ->latest()
            ->get()
            ->map(fn (Produk $product) => $productController->productData($product));

        $data = $this->sellerData($sellerModel, false);
        $data['products'] = $products;
        $data['produk'] = $products;

        return $this->success($data, 'Profil penjual berhasil diambil');
    }

    public function dashboard(Request $request, ProductController $productController)
    {
        $user = $request->user();
        $penjual = $user?->penjual;

        if (! $penjual) {
            return $this->error('Profil penjual tidak ditemukan untuk akun ini.', [], 403);
        }

        $produkQuery = Produk::where('penjual_id', $penjual->id);
        $totalProduk = (clone $produkQuery)->count();
        $produkAktif = (clone $produkQuery)->where('status', 'aktif')->count();
        $produkPending = (clone $produkQuery)->where('status', 'pending')->count();
        $produkDitolak = (clone $produkQuery)->where('status', 'ditolak')->count();
        $totalViews = (int) (clone $produkQuery)->sum('views');
        $totalUlasan = Ulasan::whereIn('produk_id', (clone $produkQuery)->select('id'))
            ->publicVisible()
            ->count();

        $recentProducts = Produk::with(['kategori', 'penjual.user'])
            ->withAvg(['ulasan' => fn ($reviews) => $reviews->publicVisible()], 'rating')
            ->withCount(['ulasan' => fn ($reviews) => $reviews->publicVisible()])
            ->where('penjual_id', $penjual->id)
            ->latest()
            ->take(10)
            ->get()
            ->map(fn (Produk $p) => array_merge($productController->productData($p), [
                'moderation_status' => $p->status,
                'pending_action' => $p->pending_action ?? null,
                'rejection_reason' => $p->alasan_ditolak,
            ]));

        return $this->success([
            'seller' => $this->sellerData($penjual, true),
            'stats' => [
                'total_products' => $totalProduk,
                'active_products' => $produkAktif,
                'pending_products' => $produkPending,
                'rejected_products' => $produkDitolak,
                'total_views' => $totalViews,
                'total_reviews' => $totalUlasan,
            ],
            'products' => $recentProducts,
        ], 'Dashboard penjual berhasil diambil');
    }

    public function toggleStoreStatus(Request $request)
    {
        $penjual = $request->user()?->penjual;
        if (! $penjual) {
            return $this->error('Profil penjual tidak ditemukan.', [], 403);
        }

        $newStatus = ($penjual->status_toko === 'tutup') ? 'buka' : 'tutup';
        $penjual->update(['status_toko' => $newStatus]);

        return $this->success(
            $this->sellerData($penjual->fresh(), true),
            "Status toko berhasil diubah menjadi {$newStatus}."
        );
    }

    public function sellerData(Penjual $seller, bool $isOwner = false): array
    {
        $imageUrl = $seller->foto_toko ? url('storage/'.$seller->foto_toko) : null;

        return [
            'id' => $seller->id,
            'name' => $seller->nama_toko,
            'nama_toko' => $seller->nama_toko,
            'description' => $seller->deskripsi_toko,
            'deskripsi_toko' => $seller->deskripsi_toko,
            'class_location' => $seller->lokasi_kelas,
            'lokasi_kelas' => $seller->lokasi_kelas,
            'location' => null,
            'store_status' => $seller->status_toko ?? 'buka',
            'status_toko' => $seller->status_toko ?? 'buka',
            // Nomor telepon disembunyikan untuk publik, hanya tampil bagi pemilik toko di dashboard
            'phone' => $isOwner ? $seller->nomor_whatsapp : null,
            'nomor_whatsapp' => $isOwner ? $seller->nomor_whatsapp : null,
            'image' => $imageUrl,
            'foto_toko' => $imageUrl,
            'active_product_count' => $seller->active_product_count ?? $seller->produk()->publicVisible()->count(),
        ];
    }
}
