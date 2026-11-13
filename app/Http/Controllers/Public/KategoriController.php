<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Kategori;
use App\Models\Produk;
use Illuminate\Http\Request;
use Illuminate\View\View;

class KategoriController extends Controller
{
    /**
     * Tampilkan daftar semua kategori (Semua Produk) dengan layout katalog.
     */
    public function index(Request $request): View
    {
        $search = $request->input('q');
        $minPrice = $request->input('min_price');
        $maxPrice = $request->input('max_price');
        $sort = $request->input('sort', 'terbaru');

        $kategoriList = Kategori::withCount(['produk' => function ($q) {
            $q->publicVisible();
        }])->orderBy('nama')->get();

        $produkQuery = Produk::publicVisible()
            ->with(['kategori', 'penjual'])
            ->withAvg(['ulasan' => fn ($q) => $q->publicVisible()], 'rating')
            ->withCount(['ulasan' => fn ($q) => $q->publicVisible()]);

        if ($search) {
            $escapedSearch = addcslashes($search, '%_\\');
            $produkQuery->where(function ($q) use ($escapedSearch) {
                $q->where('nama', 'like', "%{$escapedSearch}%")
                  ->orWhere('deskripsi', 'like', "%{$escapedSearch}%")
                  ->orWhereHas('penjual', fn ($s) => $s->where('nama_toko', 'like', "%{$escapedSearch}%"));
            });
        }

        if (is_numeric($minPrice)) {
            $produkQuery->where('harga', '>=', $minPrice);
        }
        if (is_numeric($maxPrice)) {
            $produkQuery->where('harga', '<=', $maxPrice);
        }

        switch ($sort) {
            case 'termurah':
                $produkQuery->orderBy('harga', 'asc');
                break;
            case 'termahal':
                $produkQuery->orderBy('harga', 'desc');
                break;
            case 'terpopuler':
                $produkQuery->orderBy('views', 'desc');
                break;
            case 'terbaru':
            default:
                $produkQuery->latest();
                break;
        }

        $produk = $produkQuery->paginate(12)->withQueryString();

        return view('public.kategori.index', compact('kategoriList', 'produk', 'search', 'minPrice', 'maxPrice', 'sort'));
    }

    /**
     * Tampilkan produk untuk kategori tertentu dengan layout katalog.
     */
    public function show(Kategori $kategori, Request $request): View
    {
        $search = $request->input('q');
        $minPrice = $request->input('min_price');
        $maxPrice = $request->input('max_price');
        $sort = $request->input('sort', 'terbaru');

        $kategoriList = Kategori::withCount(['produk' => function ($q) {
            $q->publicVisible();
        }])->orderBy('nama')->get();

        $produkQuery = Produk::publicVisible()
            ->with(['kategori', 'penjual'])
            ->withAvg(['ulasan' => fn ($q) => $q->publicVisible()], 'rating')
            ->withCount(['ulasan' => fn ($q) => $q->publicVisible()])
            ->where('kategori_id', $kategori->id);

        if ($search) {
            $escapedSearch = addcslashes($search, '%_\\');
            $produkQuery->where(function ($q) use ($escapedSearch) {
                $q->where('nama', 'like', "%{$escapedSearch}%")
                  ->orWhere('deskripsi', 'like', "%{$escapedSearch}%")
                  ->orWhereHas('penjual', fn ($s) => $s->where('nama_toko', 'like', "%{$escapedSearch}%"));
            });
        }

        if (is_numeric($minPrice)) {
            $produkQuery->where('harga', '>=', $minPrice);
        }
        if (is_numeric($maxPrice)) {
            $produkQuery->where('harga', '<=', $maxPrice);
        }

        switch ($sort) {
            case 'termurah':
                $produkQuery->orderBy('harga', 'asc');
                break;
            case 'termahal':
                $produkQuery->orderBy('harga', 'desc');
                break;
            case 'terpopuler':
                $produkQuery->orderBy('views', 'desc');
                break;
            case 'terbaru':
            default:
                $produkQuery->latest();
                break;
        }

        $produk = $produkQuery->paginate(12)->withQueryString();

        return view('public.kategori.show', compact('kategoriList', 'kategori', 'produk', 'search', 'minPrice', 'maxPrice', 'sort'));
    }
}
