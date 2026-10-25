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
        $request->validate([
            'nama' => 'required|string|max:100|unique:kategori,nama',
            'icon' => ['required', 'string', Rule::in(array_keys(self::ICON_OPTIONS))],
        ]);

        Kategori::create([
            'nama' => $request->nama,
            'slug' => Str::slug($request->nama),
            'icon' => $request->icon,
        ]);

        return redirect()->route('admin.categories.index')->with('success', 'Kategori berhasil ditambahkan.');
    }

    public function update(Request $request, Kategori $category)
    {
        $request->validate([
            'nama' => 'required|string|max:100|unique:kategori,nama,' . $category->id,
            'icon' => ['required', 'string', Rule::in(array_keys(self::ICON_OPTIONS))],
        ]);

        $category->update([
            'nama' => $request->nama,
            'slug' => Str::slug($request->nama),
            'icon' => $request->icon,
        ]);

        return redirect()->route('admin.categories.index')->with('success', 'Kategori berhasil diperbarui.');
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
