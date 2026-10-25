<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Kategori;
use App\Models\Produk;
use App\Models\Penjual;
use App\Models\Ulasan;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HomeController extends Controller
{
    /**
     * Tampilkan halaman utama (storefront promosi).
     */
    public function index(): View
    {
        $search = request()->input('q');
        $selectedKategori = request()->input('kategori');

        // Etalase hero hanya membutuhkan tiga produk terbaru yang tampil publik.
        $produkHero = Produk::publicVisible()
            ->select(['id', 'penjual_id', 'kategori_id', 'nama', 'slug', 'harga', 'foto_utama'])
            ->with([
                'kategori:id,nama,slug',
                'penjual:id,nama_toko,nomor_whatsapp',
            ])
            ->withAvg(['ulasan' => fn ($query) => $query->publicVisible()], 'rating')
            ->latest('id')
            ->limit(3)
            ->get();
        $hargaMinimumAktif = $produkHero->isNotEmpty()
            ? Produk::publicVisible()->min('harga')
            : null;

        // 1. Kategori
        $kategoriList = Kategori::withCount([
            'produk as produk_aktif_count' => fn ($query) => $query->publicVisible(),
        ])->orderBy('nama')->get();

        // Katalog khusus beranda mobile, memakai produk aktif dari penjual aktif
        $produkMobile = Produk::publicVisible()
            ->with(['kategori', 'penjual'])
            ->when($selectedKategori, fn ($query) => $query->whereHas('kategori', fn ($category) => $category->where('slug', $selectedKategori)))
            ->when($search, function ($query) use ($search) {
                $query->where(function ($searchQuery) use ($search) {
                    $searchQuery->where('nama', 'like', "%{$search}%")
                        ->orWhere('deskripsi', 'like', "%{$search}%")
                        ->orWhereHas('penjual', fn ($seller) => $seller->where('nama_toko', 'like', "%{$search}%"));
                });
            })
            ->latest()
            ->paginate(12)
            ->withQueryString();

        // 2. Rekomendasi Produk (5 item)
        $produkRekomendasi = Produk::publicVisible()
            ->with(['kategori', 'penjual'])
            ->withAvg(['ulasan' => fn($q) => $q->publicVisible()], 'rating')
            ->orderByDesc('ulasan_avg_rating')
            ->orderByDesc('views')
            ->latest()
            ->limit(5)
            ->get();
            
        // 3. Produk Populer (Top 3 views)
        $produkPopuler = Produk::publicVisible()
            ->with(['kategori', 'penjual'])
            ->orderByDesc('views')
            ->limit(3)
            ->get();

        // Tampilkan hanya ulasan yang memang sudah dapat dilihat publik dari produk aktif penjual aktif.
        $ulasanPilihan = Ulasan::with('produk')
            ->whereHas('produk', fn ($query) => $query->publicVisible())
            ->publicVisible()
            ->latest()
            ->limit(3)
            ->get();
            
        // 4. Statistik Dashboard
        $totalProduk = Produk::publicVisible()->count();
        $totalPenjual = Penjual::whereHas('user', fn($q) => $q->where('is_active', true))->count();
        $totalKategori = Kategori::count();

        return view('public.home.index', compact(
            'kategoriList', 
            'produkRekomendasi', 
            'produkPopuler',
            'ulasanPilihan',
            'totalProduk',
            'totalPenjual',
            'totalKategori',
            'produkMobile',
            'search',
            'selectedKategori',
            'produkHero',
            'hargaMinimumAktif'
        ));
    }
}
