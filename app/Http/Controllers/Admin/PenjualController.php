<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PenjualController extends Controller
{
    /**
     * Tampilkan daftar seluruh Penjual.
     */
    public function index(Request $request): View
    {
        $search = $request->query('search');
        $status = $request->query('status');
        $perPage = $request->query('per_page', 10);

        $query = User::with('penjual')
            ->where('role', 'penjual');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhereHas('penjual', function ($p) use ($search) {
                      $p->where('nama_toko', 'like', "%{$search}%")
                        ->orWhere('nomor_whatsapp', 'like', "%{$search}%")
                        ->orWhere('lokasi_kelas', 'like', "%{$search}%")
                        ->orWhere('nisn', 'like', "%{$search}%");
                  });
            });
        }

        if ($status === 'aktif') {
            $query->where('is_active', true);
        } elseif ($status === 'nonaktif') {
            $query->where('is_active', false);
        } elseif ($status === 'buka') {
            $query->whereHas('penjual', fn ($p) => $p->where('status_toko', 'buka'));
        } elseif ($status === 'tutup') {
            $query->whereHas('penjual', fn ($p) => $p->where('status_toko', 'tutup'));
        }

        $query->latest();

        if ($perPage === 'all' || $perPage === 'semua') {
            $perPageCount = max(1, (clone $query)->count());
        } else {
            $perPageCount = in_array((int) $perPage, [10, 25, 50], true) ? (int) $perPage : 10;
        }

        $penjual = $query->paginate($perPageCount)->withQueryString();

        return view('admin.penjual.index', compact('penjual', 'search', 'status', 'perPage'));
    }

    /**
     * Tampilkan form tambah Penjual.
     */
    public function create(): View
    {
        return view('admin.penjual.create');
    }

    /**
     * Simpan akun Penjual baru menggunakan DB transaction.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name'           => ['required', 'string', 'max:255'],
            'email'          => ['required', 'email', 'unique:users,email'],
            'password'       => ['required', 'string', 'min:8'],
            'nama_toko'      => ['required', 'string', 'max:255'],
            'deskripsi_toko' => ['nullable', 'string'],
            'nomor_whatsapp' => ['required', 'string', 'max:20'],
            'lokasi_kelas'   => ['required', 'string', 'max:100'],
            'nisn'           => ['required', 'digits:10', 'unique:penjual,nisn'],
            'foto_toko'      => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'alamat'         => ['required', 'string', 'max:1000'],
        ]);

        DB::transaction(function () use ($request) {
            $user = User::create([
                'name'      => $request->name,
                'email'     => $request->email,
                'password'  => Hash::make($request->password),
                'role'      => 'penjual',
                'is_active' => true,
            ]);

            $profile = [
                'nama_toko'      => $request->nama_toko,
                'deskripsi_toko' => $request->deskripsi_toko,
                'nomor_whatsapp' => $request->nomor_whatsapp,
                'lokasi_kelas'   => $request->lokasi_kelas,
                'nisn'           => $request->nisn,
                'alamat'         => $request->alamat,
            ];
            if ($request->hasFile('foto_toko')) {
                $profile['foto_toko'] = $request->file('foto_toko')->store('penjual', 'public');
            }
            $user->penjual()->create($profile);
        });

        return redirect()
            ->route('admin.penjual.index')
            ->with('success', 'Akun penjual berhasil dibuat.');
    }

    /**
     * Tampilkan form edit Penjual.
     */
    public function edit(User $penjual): View
    {
        abort_unless($penjual->role === 'penjual', 404);

        $penjual->load('penjual');

        return view('admin.penjual.edit', compact('penjual'));
    }

    /**
     * Perbarui data Penjual.
     */
    public function update(Request $request, User $penjual): RedirectResponse
    {
        abort_unless($penjual->role === 'penjual', 404);

        $request->validate([
            'name'           => ['required', 'string', 'max:255'],
            'email'          => ['required', 'email', Rule::unique('users', 'email')->ignore($penjual->id)],
            'password'       => ['nullable', 'string', 'min:8'],
            'nama_toko'      => ['required', 'string', 'max:255'],
            'deskripsi_toko' => ['nullable', 'string'],
            'nomor_whatsapp' => ['required', 'string', 'max:20'],
            'lokasi_kelas'   => ['required', 'string', 'max:100'],
            'nisn'           => ['required', 'digits:10', Rule::unique('penjual', 'nisn')->ignore($penjual->penjual?->id)],
            'foto_toko'      => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'alamat'         => ['required', 'string', 'max:1000'],
        ]);

        DB::transaction(function () use ($request, $penjual) {
            $userData = [
                'name'  => $request->name,
                'email' => $request->email,
            ];

            // Update password hanya jika diisi
            if ($request->filled('password')) {
                $userData['password'] = Hash::make($request->password);
            }

            $penjual->update($userData);

            $profile = [
                'nama_toko'      => $request->nama_toko,
                'deskripsi_toko' => $request->deskripsi_toko,
                'nomor_whatsapp' => $request->nomor_whatsapp,
                'lokasi_kelas'   => $request->lokasi_kelas,
                'nisn'           => $request->nisn,
                'alamat'         => $request->alamat,
            ];
            $existingProfile = $penjual->penjual;
            if ($request->hasFile('foto_toko')) {
                if ($existingProfile?->foto_toko) {
                    Storage::disk('public')->delete($existingProfile->foto_toko);
                }
                $profile['foto_toko'] = $request->file('foto_toko')->store('penjual', 'public');
            }

            $penjual->penjual()->updateOrCreate(
                ['user_id' => $penjual->id],
                $profile
            );
        });

        return redirect()
            ->route('admin.penjual.index')
            ->with('success', 'Data penjual berhasil diperbarui.');
    }

    public function show(User $penjual): View
    {
        abort_unless($penjual->role === 'penjual', 404);
        $penjual->load('penjual');

        if ($penjual->penjual) {
            $hasPendingAction = Schema::hasColumn('produk', 'pending_action');
            $penjual->penjual->loadCount([
                'produk',
                'produk as produk_aktif_count' => fn ($query) => $query->where('status', 'aktif'),
                'produk as produk_pending_count' => fn ($query) => $query->where(function ($pending) use ($hasPendingAction) {
                    $pending->where('status', 'pending');
                    if ($hasPendingAction) {
                        $pending->orWhereNotNull('pending_action');
                    }
                }),
                'produk as produk_ditolak_count' => fn ($query) => $query->where('status', 'ditolak'),
            ]);
            $penjual->penjual->loadSum('produk', 'views');
        }

        return view('admin.penjual.show', compact('penjual'));
    }

    /**
     * Toggle status aktif/nonaktif Penjual.
     * Hanya berlaku untuk user dengan role penjual.
     */
    public function toggleStatus(User $penjual): RedirectResponse
    {
        abort_unless($penjual->role === 'penjual', 404);

        $penjual->update(['is_active' => ! $penjual->is_active]);

        // Cabut seluruh Sanctum token aktif jika akun dinonaktifkan
        if (! $penjual->is_active) {
            $penjual->tokens()->delete();
        }

        $status = $penjual->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return redirect()
            ->route('admin.penjual.index')
            ->with('success', "Akun penjual berhasil {$status}.");
    }

    /**
     * Hapus akun Penjual beserta profil dan produknya (CASCADE).
     * Aman karena penjual.user_id ON DELETE CASCADE dan
     * produk.penjual_id ON DELETE CASCADE sudah dikonfigurasi di migration.
     */
    public function destroy(User $penjual): RedirectResponse
    {
        abort_unless($penjual->role === 'penjual', 404);

        if ($penjual->penjual && $penjual->penjual->produk()->exists()) {
            return redirect()
                ->route('admin.penjual.index')
                ->with('error', 'Penjual tidak dapat dihapus karena masih memiliki produk di katalog. Silakan nonaktifkan akun sebagai gantinya.');
        }

        $penjual->delete();

        return redirect()
            ->route('admin.penjual.index')
            ->with('success', 'Akun penjual dan data terkait berhasil dihapus.');
    }
}
