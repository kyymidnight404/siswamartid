<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Produk;
use App\Models\Kategori;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use App\Services\AdminNotificationService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class ProdukController extends Controller
{
    /**
     * Tampilkan daftar produk untuk dimoderasi.
     */
    public function index(Request $request): View
    {
        $search = $request->query('search');
        $status = $request->query('status');
        $kategoriId = $request->query('kategori_id');
        $perPage = $request->query('per_page', 10);

        $query = Produk::with(['penjual.user', 'kategori']);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                  ->orWhere('deskripsi', 'like', "%{$search}%")
                  ->orWhereHas('penjual', function ($p) use ($search) {
                      $p->where('nama_toko', 'like', "%{$search}%")
                        ->orWhereHas('user', fn ($u) => $u->where('name', 'like', "%{$search}%"));
                  });
            });
        }

        if ($kategoriId) {
            $query->where('kategori_id', $kategoriId);
        }

        $validStatuses = ['pending', 'aktif', 'ditolak', 'draft'];
        $hasPendingAction = Schema::hasColumn('produk', 'pending_action');

        if ($status === 'pending') {
            $query->where(function ($pending) use ($hasPendingAction) {
                $pending->where('status', 'pending');
                if ($hasPendingAction) {
                    $pending->orWhereNotNull('pending_action');
                }
            });
        } elseif ($status && in_array($status, $validStatuses, true)) {
            $query->where('status', $status);
            if ($hasPendingAction && in_array($status, ['aktif', 'ditolak'], true)) {
                $query->whereNull('pending_action');
            }
        }

        $query->latest();

        if ($perPage === 'all' || $perPage === 'semua') {
            $perPageCount = max(1, (clone $query)->count());
        } else {
            $perPageCount = in_array((int) $perPage, [10, 25, 50], true) ? (int) $perPage : 10;
        }

        $produk = $query->paginate($perPageCount)->withQueryString();
        $pendingCount = Produk::where(function ($pending) use ($hasPendingAction) {
            $pending->where('status', 'pending');
            if ($hasPendingAction) {
                $pending->orWhereNotNull('pending_action');
            }
        })->count();

        $kategoris = Kategori::orderBy('nama')->get();

        return view('admin.produk.index', compact('produk', 'status', 'pendingCount', 'search', 'kategoriId', 'kategoris', 'perPage'));
    }

    /**
     * Tampilkan detail produk.
     */
    public function show(Produk $produk): View
    {
        $produk->load(['penjual.user', 'kategori']);
        $pendingChanges = $produk->pending_changes ?? [];
        $pendingCategory = isset($pendingChanges['kategori_id'])
            ? Kategori::find($pendingChanges['kategori_id'])
            : null;

        return view('admin.produk.show', compact('produk', 'pendingChanges', 'pendingCategory'));
    }

    /**
     * Update status produk.
     */
    public function updateStatus(Request $request, Produk $produk, AdminNotificationService $notificationService): RedirectResponse
    {
        if ($produk->pending_action) {
            $validated = $request->validate([
                'decision' => 'required|in:approve,reject',
                'alasan_ditolak' => 'nullable|string|max:500',
            ]);

            $action = $produk->pending_action;
            $reason = trim((string) ($validated['alasan_ditolak'] ?? ''));

            if ($validated['decision'] === 'approve' && $action === 'delete') {
                $photoToDelete = $produk->foto_utama;
                $produkNama = $produk->nama;
                $sellerId = $produk->penjual_id;
                $sellerName = $produk->penjual?->user?->name ?? 'Penjual';
                $produkId = $produk->id;

                DB::transaction(function () use ($produk) {
                    $produk->delete();
                });
                if ($photoToDelete) {
                    Storage::disk('public')->delete($photoToDelete);
                }

                try {
                    $notificationService->notifyAdmins([
                        'title' => 'Produk dihapus',
                        'description' => 'Produk "' . $produkNama . '" disetujui untuk dihapus dari katalog.',
                        'activity' => 'produk.dihapus',
                        'actor' => $sellerName,
                        'seller_id' => $sellerId,
                        'entity_type' => 'produk',
                        'entity_id' => $produkId,
                        'url' => route('admin.produk.index'),
                    ]);
                } catch (\Throwable $e) {
                    // Logged silently
                }

                return redirect()->route('admin.produk.index')
                    ->with('success', 'Permintaan penghapusan produk disetujui. Produk telah dihapus.');
            }

            if ($validated['decision'] === 'approve' && $action === 'update') {
                $changes = $produk->pending_changes ?? [];
                $oldPhoto = $produk->foto_utama;
                $newPhoto = $changes['foto_utama'] ?? null;

                if (isset($changes['slug'])) {
                    $baseSlug = $changes['slug'];
                    $slug = $baseSlug;
                    $suffix = 1;
                    while (Produk::where('slug', $slug)->where('id', '!=', $produk->id)->exists()) {
                        $slug = $baseSlug . '-' . $suffix++;
                    }
                    $changes['slug'] = $slug;
                }

                DB::transaction(function () use ($produk, $changes) {
                    $produk->update(array_merge($changes, [
                        'status' => 'aktif',
                        'approved_at' => $produk->approved_at ?? now(),
                        'pending_action' => null,
                        'pending_changes' => null,
                        'alasan_ditolak' => null,
                    ]));
                });

                if ($newPhoto && $oldPhoto && $newPhoto !== $oldPhoto) {
                    Storage::disk('public')->delete($oldPhoto);
                }
            } elseif ($validated['decision'] === 'approve') {
                $produk->update([
                    'status' => 'aktif',
                    'approved_at' => $produk->approved_at ?? now(),
                    'pending_action' => null,
                    'pending_changes' => null,
                    'alasan_ditolak' => null,
                ]);
            } else {
                if ($action === 'update') {
                    $pendingPhoto = data_get($produk->pending_changes, 'foto_utama');
                    if ($pendingPhoto && $pendingPhoto !== $produk->foto_utama) {
                        Storage::disk('public')->delete($pendingPhoto);
                    }
                }

                $rejectionReason = $reason !== '' ? $reason : 'Pengajuan produk ditolak oleh Admin.';
                $produk->update([
                    'status' => $action === 'create' ? 'ditolak' : $produk->status,
                    'pending_action' => null,
                    'pending_changes' => null,
                    'alasan_ditolak' => $rejectionReason,
                ]);
            }

            return redirect()->route('admin.produk.index')
                ->with('success', $validated['decision'] === 'approve' ? 'Pengajuan produk disetujui.' : 'Pengajuan produk ditolak.');
        }

        $validated = $request->validate([
            'status' => 'required|in:aktif,pending,ditolak',
            'alasan_ditolak' => 'nullable|string|max:500'
        ]);

        $alasan = $validated['status'] === 'ditolak' ? ($validated['alasan_ditolak'] ?? null) : null;

        $updateData = [
            'status' => $validated['status'],
            'alasan_ditolak' => $alasan,
        ];

        if ($validated['status'] === 'aktif') {
            $updateData['approved_at'] = $produk->approved_at ?? now();
        }

        $produk->update($updateData);

        $notificationService->notifyAdmins([
            'title' => 'Status produk diperbarui',
            'description' => 'Status produk "' . $produk->nama . '" diubah menjadi ' . $validated['status'] . '.',
            'activity' => 'produk.status_diubah',
            'actor' => auth()->user()->name,
            'entity_type' => 'produk',
            'entity_id' => $produk->id,
            'url' => route('admin.produk.show', $produk),
        ], auth()->id());

        return redirect()->route('admin.produk.index')->with('success', 'Status produk berhasil diperbarui.');
    }

    /**
     * Hapus produk.
     */
    public function destroy(Produk $produk, AdminNotificationService $notificationService): RedirectResponse
    {
        $sellerId = $produk->penjual_id;
        $sellerName = $produk->penjual?->user?->name ?? 'Penjual';
        $produkNama = $produk->nama;
        $produkId = $produk->id;

        // Delete image if exists
        if ($produk->foto_utama && \Illuminate\Support\Facades\Storage::disk('public')->exists($produk->foto_utama)) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($produk->foto_utama);
        }
        
        $produk->delete();

        $notificationService->notifyAdmins([
            'title' => 'Produk dihapus',
            'description' => 'Produk "' . $produkNama . '" dihapus oleh ' . auth()->user()->name . '.',
            'activity' => 'produk.dihapus',
            'actor' => $sellerName,
            'seller_id' => $sellerId,
            'entity_type' => 'produk',
            'entity_id' => $produkId,
            'url' => route('admin.produk.index'),
        ], auth()->id());

        return redirect()->route('admin.produk.index')->with('success', 'Produk berhasil dihapus.');
    }
}

