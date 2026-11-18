<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Kategori;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

class KategoriController extends Controller
{
    private const ICON_OPTIONS = [
        'images/category/dessert.png' => 'Dessert',
        'images/category/makanan_berat.png' => 'Makanan Berat',
        'images/category/minuman.png' => 'Minuman',
        'images/category/snack.png' => 'Snack',
    ];

    public function index(Request $request)
    {
        $search = $request->get('search');
        $perPage = $request->query('per_page', 10);
        
        $query = Kategori::withCount('produk')
            ->when($search, function ($query, $search) {
                return $query->where('nama', 'like', "%{$search}%");
            })
            ->orderBy('created_at', 'desc');

        if ($perPage === 'all' || $perPage === 'semua') {
            $perPageCount = max(1, (clone $query)->count());
        } else {
            $perPageCount = in_array((int) $perPage, [10, 25, 50], true) ? (int) $perPage : 10;
        }

        $kategoris = $query->paginate($perPageCount)->withQueryString();
        $iconOptions = self::ICON_OPTIONS;

        return view('admin.kategori.index', compact('kategoris', 'search', 'iconOptions', 'perPage'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:100', Rule::unique('kategori', 'nama')],
            'icon' => ['required', 'string', Rule::in(array_keys(self::ICON_OPTIONS))],
        ]);

        $nama = trim($validated['nama']);

        Kategori::create([
            'nama' => $nama,
            'slug' => $this->generateUniqueSlug($nama),
            'icon' => $validated['icon'],
        ]);

        return redirect()->route('admin.categories.index')->with('success', 'Kategori berhasil ditambahkan.');
    }

    public function update(Request $request, Kategori $category)
    {
        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:100', Rule::unique('kategori', 'nama')->ignore($category->id)],
            'icon' => ['required', 'string', Rule::in(array_keys(self::ICON_OPTIONS))],
        ]);

        $nama = trim($validated['nama']);

        $category->update([
            'nama' => $nama,
            'slug' => $this->generateUniqueSlug($nama, $category->id),
            'icon' => $validated['icon'],
        ]);

        return redirect()->route('admin.categories.index')->with('success', 'Kategori berhasil diperbarui.');
    }

    private function generateUniqueSlug(string $nama, ?int $ignoreId = null): string
    {
        $base = Str::limit(Str::slug($nama) ?: 'kategori', 90, '');
        $slug = $base;
        $suffix = 1;

        while (
            Kategori::where('slug', $slug)
                ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
                ->exists()
        ) {
            $slug = $base.'-'.$suffix;
            $suffix++;
        }

        return $slug;
    }

    public function destroy(Kategori $category)
    {
        if ($category->produk()->count() > 0) {
            return redirect()->route('admin.categories.index')->with('error', 'Kategori belum bisa dihapus karena masih dipakai produk.');
        }

        $category->delete();

        return redirect()->route('admin.categories.index')->with('success', 'Kategori berhasil dihapus.');
    }
}
