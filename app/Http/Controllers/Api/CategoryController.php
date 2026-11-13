<?php

namespace App\Http\Controllers\Api;

use App\Models\Kategori;
use Illuminate\Http\Request;

class CategoryController extends ApiController
{
    public function index()
    {
        $categories = Kategori::query()
            ->withCount(['produk' => fn ($products) => $products->publicVisible()])
            ->orderBy('nama')
            ->get();

        return $this->success($categories->map(fn (Kategori $category) => $this->categoryData($category)), 'Daftar kategori berhasil diambil');
    }

    public function products(Kategori $category, Request $request, ProductController $products)
    {
        $query = $category->produk()
            ->publicVisible()
            ->with(['kategori', 'penjual.user'])
            ->withAvg(['ulasan' => fn ($reviews) => $reviews->publicVisible()], 'rating')
            ->withCount(['ulasan' => fn ($reviews) => $reviews->publicVisible()])
            ->latest();
        $perPage = min(max((int) $request->input('per_page', 12), 1), 50);
        $result = $query->paginate($perPage)->withQueryString();
        $result->through(fn ($product) => $products->productData($product));

        return $this->success($result, 'Produk kategori berhasil diambil');
    }

    public function show(Kategori $category)
    {
        return $this->success($this->categoryData($category), 'Detail kategori berhasil diambil');
    }

    private function categoryData(Kategori $category): array
    {
        return [
            'id' => $category->id,
            'name' => $category->nama,
            'slug' => $category->slug,
            'icon' => $category->icon,
            'product_count' => $category->produk_count ?? $category->produk()->publicVisible()->count(),
        ];
    }
}
