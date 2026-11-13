<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Kategori;
use App\Models\Produk;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    /**
     * Hasilkan XML Sitemap dinamis untuk seluruh halaman publik SiswaMart.
     */
    public function index(): Response
    {
        $categories = Kategori::orderBy('nama')->get();
        $products = Produk::publicVisible()
            ->select(['id', 'slug', 'updated_at'])
            ->latest('updated_at')
            ->get();

        $content = view('public.sitemap', compact('categories', 'products'))->render();

        return response($content, 200)
            ->header('Content-Type', 'application/xml; charset=utf-8');
    }
}
