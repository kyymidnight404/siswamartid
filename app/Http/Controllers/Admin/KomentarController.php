<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Kategori;
use App\Models\Ulasan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class KomentarController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->query('search');
        $rating = $request->query('rating');
        $kategoriId = $request->query('kategori_id');
        $perPage = $request->query('per_page', 10);

        $query = Ulasan::with(['produk.penjual', 'produk.kategori']);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('guest_username', 'like', "%{$search}%")
                  ->orWhere('komentar', 'like', "%{$search}%")
                  ->orWhereHas('produk', function ($p) use ($search) {
                      $p->where('nama', 'like', "%{$search}%")
                        ->orWhereHas('penjual', fn ($pen) => $pen->where('nama_toko', 'like', "%{$search}%"));
                  });
            });
        }

        if ($rating) {
            $query->where('rating', (int) $rating);
        }

        if ($kategoriId) {
            $query->whereHas('produk', fn ($p) => $p->where('kategori_id', $kategoriId));
        }

        $query->latest();

        if ($perPage === 'all' || $perPage === 'semua') {
            $perPageCount = max(1, (clone $query)->count());
        } else {
            $perPageCount = in_array((int) $perPage, [10, 25, 50], true) ? (int) $perPage : 10;
        }

        $komentarList = $query->paginate($perPageCount)->withQueryString();
        $kategoris = Kategori::orderBy('nama')->get();

        return view('admin.komentar.index', compact('komentarList', 'search', 'rating', 'kategoriId', 'kategoris', 'perPage'));
    }

    public function destroy(Ulasan $ulasan): RedirectResponse
    {
        $ulasan->delete();

        return redirect()
            ->route('admin.komentar.index')
            ->with('success', 'Ulasan berhasil dihapus.');
    }
}
