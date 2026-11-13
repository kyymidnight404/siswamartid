<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Kategori;
use App\Models\Produk;
use Illuminate\View\View;

class HomeController extends Controller
{
    /**
     * Tampilkan halaman utama (storefront promosi).
     */
    public function index(): View
    {
        // 1. Etalase hero membutuhkan tiga produk terbaru yang tampil publik.
        $produkHero = Produk::publicVisible()
            ->select(['id', 'penjual_id', 'kategori_id', 'nama', 'slug', 'harga', 'foto_utama'])
            ->with([
                'kategori:id,nama,slug',
                'penjual:id,nama_toko,nomor_whatsapp,status_toko',
            ])
            ->withAvg(['ulasan' => fn ($query) => $query->publicVisible()], 'rating')
            ->latest('id')
            ->limit(3)
            ->get();

        $hargaMinimumAktif = $produkHero->isNotEmpty()
            ? Produk::publicVisible()->min('harga')
            : null;

        // 2. Kategori untuk marquee dan grid kategori
        $kategoriList = Kategori::withCount([
            'produk as produk_aktif_count' => fn ($query) => $query->publicVisible(),
        ])->orderBy('nama')->get();

        // 3. Rekomendasi Produk Populer (4-5 item untuk grid beranda)
        $produkRekomendasi = Produk::publicVisible()
            ->with(['kategori', 'penjual'])
            ->withAvg(['ulasan' => fn($q) => $q->publicVisible()], 'rating')
            ->withCount(['ulasan' => fn($q) => $q->publicVisible()])
            ->orderByDesc('ulasan_avg_rating')
            ->orderByDesc('views')
            ->latest()
            ->limit(5)
            ->get();

        return view('public.home.index', compact(
            'kategoriList',
            'produkRekomendasi',
            'produkHero',
            'hargaMinimumAktif'
        ));
    }
}
