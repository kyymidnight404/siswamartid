<?php

namespace App\Http\Controllers\Penjual;

use App\Http\Controllers\Controller;
use App\Models\Kategori;
use App\Models\Produk;
use App\Services\AdminNotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ProdukController extends Controller
{
    /**
     * Pastikan User login memiliki profil Penjual.
     */
    private function getPenjualId()
    {
        $penjual = auth()->user()->penjual;
        abort_unless($penjual, 403, 'Profil penjual tidak ditemukan.');
        return $penjual->id;
    }

    private function recordActivity(string $type): void
    {
        auth()->user()->penjual?->update([
            'last_activity_at' => now(),
            'last_activity_type' => $type,
        ]);
    }

    /**
     * Tampilkan daftar produk milik penjual.
     */
    public function index(Request $request): View
    {
        $penjualId = $this->getPenjualId();
        $status = $request->query('status');
        $search = $request->query('search');
        $kategoriId = $request->query('kategori_id');
        $stok = $request->query('stok');
        $perPage = $request->query('per_page', 10);
        $hasPendingAction = Schema::hasColumn('produk', 'pending_action');

        $allowedStatuses = ['aktif', 'pending', 'ditolak'];
        if (!in_array($status, $allowedStatuses, true)) {
            $status = null;
        }

        $sellerProducts = Produk::where('penjual_id', $penjualId);
        $statusCounts = collect([
            'all' => (clone $sellerProducts)->count(),
            'aktif' => (clone $sellerProducts)->where('status', 'aktif')->when($hasPendingAction, fn ($q) => $q->whereNull('pending_action'))->count(),
            'pending' => (clone $sellerProducts)->where(function ($query) use ($hasPendingAction) {
                $query->where('status', 'pending');
                if ($hasPendingAction) {
                    $query->orWhereNotNull('pending_action');
                }
            })->count(),
            'ditolak' => (clone $sellerProducts)->when($hasPendingAction, fn ($q) => $q->whereNull('pending_action'))->where(function ($query) {
                $query->where('status', 'ditolak')->orWhereNotNull('alasan_ditolak');
            })->count(),
        ]);

        $query = Produk::with('kategori')
            ->where('penjual_id', $penjualId)
            ->when($status === 'pending', fn ($q) => $q->where(function ($inner) use ($hasPendingAction) {
                $inner->where('status', 'pending');
                if ($hasPendingAction) {
                    $inner->orWhereNotNull('pending_action');
                }
            }))
            ->when($status === 'aktif', fn ($q) => $q->where('status', 'aktif')->when($hasPendingAction, fn ($inner) => $inner->whereNull('pending_action')))
            ->when($status === 'ditolak', fn ($q) => $q->when($hasPendingAction, fn ($inner) => $inner->whereNull('pending_action'))->where(function ($inner) {
                $inner->where('status', 'ditolak')->orWhereNotNull('alasan_ditolak');
            }))
            ->when($search, fn ($q) => $q->where(function ($inner) use ($search) {
                $inner->where('nama', 'like', "%{$search}%")
                      ->orWhere('deskripsi', 'like', "%{$search}%");
            }))
            ->when($kategoriId, fn ($q) => $q->where('kategori_id', $kategoriId))
            ->when($stok, function ($q) use ($stok) {
                $val = ($stok === 'ready') ? 'tersedia' : $stok;
                $q->where('stok', $val);
            })
            ->latest();

        if ($perPage === 'all' || $perPage === 'semua') {
            $perPageCount = max(1, (clone $query)->count());
        } else {
            $perPageCount = in_array((int) $perPage, [10, 25, 50], true) ? (int) $perPage : 10;
        }

        $produk = $query->paginate($perPageCount)->withQueryString();
        $kategoris = Kategori::orderBy('nama')->get();

        return view('penjual.produk.index', compact('produk', 'status', 'statusCounts', 'search', 'kategoriId', 'stok', 'kategoris', 'perPage'));
    }

    /**
     * Tampilkan form tambah produk.
     */
    public function create(): View
    {
        $this->getPenjualId();

        $kategori = Kategori::orderBy('nama')->get();

        return view('penjual.produk.create', compact('kategori'));
    }

    /**
     * Hasilkan slug unik.
     */
    private function generateUniqueSlug(string $nama, ?int $ignoreId = null): string
    {
        $slug = Str::slug($nama);
        $originalSlug = $slug;
        $count = 1;

        while (Produk::where('slug', $slug)->when($ignoreId, fn($q) => $q->where('id', '!=', $ignoreId))->exists()) {
            $slug = $originalSlug . '-' . $count;
            $count++;
        }

        return $slug;
    }

    /**
     * Simpan produk baru.
     * Produk baru menunggu persetujuan Admin sebelum tampil di katalog.
     */
    public function store(Request $request, AdminNotificationService $notificationService): RedirectResponse
    {
        $penjualId = $this->getPenjualId();

        $validated = $request->validate([
            'nama'          => ['required', 'string', 'max:255'],
            'kategori_id'   => ['required', 'exists:kategori,id'],
            'deskripsi'     => ['required', 'string'],
            'harga'         => ['required', 'numeric', 'min:0'],
            'stok'          => ['required', 'in:tersedia,po,habis'],
            'label_promosi' => ['nullable', 'string', 'max:100'],
            'foto_utama'    => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        $validated['penjual_id'] = $penjualId;
        $validated['slug']       = $this->generateUniqueSlug($request->nama);
        $validated['status'] = 'pending';
        $validated['pending_action'] = 'create';

        $validated['stok'] = $request->stok;

        if ($request->hasFile('foto_utama')) {
            $validated['foto_utama'] = $request->file('foto_utama')->store('produk', 'public');
        }

        $produk = Produk::create($validated);
        $this->recordActivity('produk.ditambahkan');

        try {
            $notificationService->notifyAdmins([
                'title' => 'Produk baru ditambahkan',
                'description' => auth()->user()->name . ' menambahkan produk "' . $produk->nama . '".',
                'activity' => 'produk.ditambahkan',
                'actor' => auth()->user()->name,
                'seller_id' => $penjualId,
                'entity_type' => 'produk',
                'entity_id' => $produk->id,
                'url' => route('admin.produk.show', $produk),
            ]);
        } catch (\Throwable $e) {
            // Logged silently, product remains safely saved
        }

        return redirect()->route('penjual.produk.index')
                         ->with('success', 'Produk dikirim dan menunggu persetujuan Admin sebelum tampil di katalog.');
    }

    /**
     * Tampilkan form edit produk.
     */
    public function edit(Produk $produk): View
    {
        $penjualId = $this->getPenjualId();
        abort_unless($produk->penjual_id === $penjualId, 403, 'Akses ditolak.');

        $kategori = Kategori::orderBy('nama')->get();

        return view('penjual.produk.edit', compact('produk', 'kategori'));
    }

    /**
     * Update produk.
     * Simpan perubahan sebagai pengajuan, tanpa mengubah data publik sebelum disetujui.
     */
    public function update(Request $request, Produk $produk, AdminNotificationService $notificationService): RedirectResponse
    {
        $penjualId = $this->getPenjualId();
        abort_unless($produk->penjual_id === $penjualId, 403, 'Akses ditolak.');
        if ($produk->pending_action) {
            return back()->with('error', 'Produk ini masih menunggu keputusan Admin. Tunggu sampai pengajuan sebelumnya selesai.');
        }

        $validated = $request->validate([
            'nama'          => ['required', 'string', 'max:255'],
            'kategori_id'   => ['required', 'exists:kategori,id'],
            'deskripsi'     => ['required', 'string'],
            'harga'         => ['required', 'numeric', 'min:0'],
            'stok'          => ['required', 'in:tersedia,po,habis'],
            'label_promosi' => ['nullable', 'string', 'max:100'],
            'foto_utama'    => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        if ($request->nama !== $produk->nama) {
            $validated['slug'] = $this->generateUniqueSlug($request->nama, $produk->id);
        }

        $validated['stok'] = $request->stok;
        if ($request->hasFile('foto_utama')) {
            $validated['foto_utama'] = $request->file('foto_utama')->store('produk', 'public');
        }

        $produk->update([
            'pending_action' => 'update',
            'pending_changes' => $validated,
            'alasan_ditolak' => null,
        ]);
        $this->recordActivity('produk.edit_diminta');

        try {
            $notificationService->notifyAdmins([
                'title' => 'Permintaan perubahan produk',
                'description' => auth()->user()->name . ' meminta perubahan untuk produk "' . $produk->nama . '".',
                'activity' => 'produk.edit_diminta',
                'actor' => auth()->user()->name,
                'entity_type' => 'produk',
                'entity_id' => $produk->id,
                'url' => route('admin.produk.show', $produk),
            ]);
        } catch (\Throwable $e) {
            // Logged silently
        }

        return redirect()->route('penjual.produk.index')
                         ->with('success', 'Perubahan produk dikirim dan menunggu persetujuan Admin.');
    }

    /**
     * Hapus produk.
     */
    public function destroy(Produk $produk, AdminNotificationService $notificationService): RedirectResponse
    {
        $penjualId = $this->getPenjualId();
        abort_unless($produk->penjual_id === $penjualId, 403, 'Akses ditolak.');
        if ($produk->pending_action) {
            return back()->with('error', 'Produk ini masih menunggu keputusan Admin. Tunggu sampai pengajuan sebelumnya selesai.');
        }

        $produkName = $produk->nama;
        $produk->update([
            'pending_action' => 'delete',
            'pending_changes' => null,
            'alasan_ditolak' => null,
        ]);
        $this->recordActivity('produk.hapus_diminta');

        try {
            $notificationService->notifyAdmins([
                'title' => 'Permintaan penghapusan produk',
                'description' => auth()->user()->name . ' meminta penghapusan produk "' . $produkName . '".',
                'activity' => 'produk.hapus_diminta',
                'actor' => auth()->user()->name,
                'seller_id' => $penjualId,
                'entity_type' => 'produk',
                'entity_id' => $produk->id,
                'url' => route('admin.produk.show', $produk),
            ]);
        } catch (\Throwable $e) {
            // Logged silently
        }

        return redirect()->route('penjual.produk.index')
                         ->with('success', 'Permintaan penghapusan dikirim dan menunggu persetujuan Admin.');
    }
}
