@extends('layouts.admin')

@section('title', 'Cek Produk')
@section('page-title', 'Cek Produk')

@section('content')

    <div class="mb-6 flex items-center gap-3">
        <a href="{{ route('admin.produk.index') }}" class="p-2 rounded-full hover:bg-[var(--sm-primary-soft)] text-[var(--sm-text-muted)] transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        </a>
        <h2 class="text-xl font-bold text-[var(--sm-ink)]">Detail Produk</h2>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        {{-- Preview --}}
        <div class="lg:col-span-2 space-y-6">
            <div class="admin-card p-6">
                <div class="flex flex-col md:flex-row gap-6">
                    <div class="w-full md:w-1/3 flex-shrink-0">
                        @if($produk->foto_utama)
                            <img src="{{ asset('storage/' . $produk->foto_utama) }}" alt="{{ $produk->nama }}" class="w-full aspect-square object-cover rounded-xl border border-[var(--sm-border)]">
                        @else
                            <div class="w-full aspect-square rounded-xl bg-[var(--sm-primary-soft)] border border-[var(--sm-border)] flex items-center justify-center text-[var(--sm-text-muted)]">
                                Belum ada foto
                            </div>
                        @endif
                    </div>
                    
                    <div class="flex-1 min-w-0">
                        <div class="flex items-start justify-between gap-4 mb-2">
                            <h3 class="text-2xl font-bold text-[var(--sm-ink)]">{{ $produk->nama }}</h3>
                            <span class="badge badge-{{ $produk->status === 'aktif' ? 'aktif' : ($produk->status === 'pending' ? 'pending' : ($produk->status === 'ditolak' ? 'ditolak' : 'draft')) }}">
                                {{ ucfirst($produk->status) }}
                            </span>
                        </div>
                        
                        <p class="text-2xl font-bold text-[var(--sm-primary)] mb-4">Rp {{ number_format($produk->harga, 0, ',', '.') }}</p>
                        
                        <div class="flex flex-wrap gap-2 mb-6">
                            <span class="text-xs font-medium px-2.5 py-1 bg-[var(--sm-primary-soft)] border border-[var(--sm-border)] text-[var(--sm-text-muted)] rounded-md">Kategori: {{ $produk->kategori->nama }}</span>
                            <span class="text-xs font-medium px-2.5 py-1 bg-[var(--sm-primary-soft)] border border-[var(--sm-border)] text-[var(--sm-text-muted)] rounded-md">Stok: {{ $produk->stok === 'po' ? 'Pre-Order' : ucfirst($produk->stok) }}</span>
                        </div>
                        
                        <div>
                            <h4 class="text-sm font-semibold text-[var(--sm-ink)] mb-2">Tentang Produk</h4>
                            <div class="text-sm text-[var(--sm-text-muted)] leading-relaxed bg-[var(--sm-primary-soft)] p-4 rounded-xl border border-[var(--sm-border)] whitespace-pre-wrap">{{ $produk->deskripsi }}</div>
                        </div>
                        @if($produk->status === 'ditolak' && $produk->alasan_ditolak)
                            <div class="mt-4 text-sm text-[var(--sm-ink)] bg-[var(--sm-primary-soft)] p-4 rounded-xl border border-[var(--sm-primary)]">
                                <strong>Alasan penolakan:</strong> {{ $produk->alasan_ditolak }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            
            {{-- Review seller request or legacy status moderation --}}
            <div class="admin-card">
                <div class="admin-card-header bg-[var(--sm-primary-soft)]">
                    <h3 class="text-sm font-bold text-[var(--sm-ink)]">Keputusan Produk</h3>
                </div>
                <div class="p-6">
                    @if($produk->pending_action)
                        <div class="mb-5 rounded-2xl border-2 border-[var(--sm-ink)] bg-[var(--sm-primary-soft)] p-4">
                            <p class="font-bold text-[var(--sm-ink)]">
                                @if($produk->pending_action === 'create') Pengajuan produk baru
                                @elseif($produk->pending_action === 'update') Permintaan perubahan produk
                                @else Permintaan penghapusan produk @endif
                            </p>
                            @if($produk->pending_action === 'update')
                                <dl class="mt-3 grid gap-2 text-sm sm:grid-cols-2">
                                    <div><dt class="font-bold">Nama</dt><dd>{{ $pendingChanges['nama'] ?? $produk->nama }}</dd></div>
                                    <div><dt class="font-bold">Harga</dt><dd>Rp {{ number_format($pendingChanges['harga'] ?? $produk->harga, 0, ',', '.') }}</dd></div>
                                    <div><dt class="font-bold">Stok</dt><dd>{{ ($pendingChanges['stok'] ?? $produk->stok) === 'po' ? 'Pre-Order' : ucfirst($pendingChanges['stok'] ?? $produk->stok) }}</dd></div>
                                    <div><dt class="font-bold">Kategori</dt><dd>{{ $pendingCategory?->nama ?? $produk->kategori->nama }}</dd></div>
                                    <div class="sm:col-span-2"><dt class="font-bold">Deskripsi</dt><dd class="whitespace-pre-wrap">{{ $pendingChanges['deskripsi'] ?? $produk->deskripsi }}</dd></div>
                                </dl>
                                @if(!empty($pendingChanges['foto_utama']))
                                    <img src="{{ asset('storage/' . $pendingChanges['foto_utama']) }}" alt="Foto usulan produk" class="mt-3 h-40 w-40 rounded-2xl border-2 border-[var(--sm-ink)] object-cover">
                                @endif
                            @endif
                        </div>
                        <form action="{{ route('admin.produk.updateStatus', $produk) }}" method="POST">
                            @csrf
                            @method('PATCH')
                            <div class="mb-5">
                                <label class="mb-1.5 block text-sm font-medium text-[var(--sm-ink)]">Catatan untuk penjual</label>
                                <textarea name="alasan_ditolak" rows="3" class="form-input" placeholder="Isi jika pengajuan ditolak atau perlu diperbaiki...">{{ old('alasan_ditolak') }}</textarea>
                                @error('alasan_ditolak') <p class="mt-1 text-xs text-[var(--sm-primary)]">{{ $message }}</p> @enderror
                            </div>
                            <div class="flex flex-wrap gap-3">
                                <button type="submit" name="decision" value="approve" class="btn-primary">{{ $produk->pending_action === 'delete' ? 'Setujui & Hapus' : 'Setujui Pengajuan' }}</button>
                                <button type="submit" name="decision" value="reject" class="btn-outline">Tolak Pengajuan</button>
                            </div>
                        </form>
                    @else
                        <form action="{{ route('admin.produk.updateStatus', $produk) }}" method="POST">
                            @csrf
                            @method('PATCH')
                            <div class="mb-5">
                                <label class="mb-1.5 block text-sm font-medium text-[var(--sm-ink)]">Pilih Status</label>
                                <select name="status" class="form-input" required>
                                    <option value="pending" {{ $produk->status == 'pending' ? 'selected' : '' }}>Pending (Menunggu)</option>
                                    <option value="aktif" {{ $produk->status == 'aktif' ? 'selected' : '' }}>Aktif (Setujui & Tampilkan)</option>
                                    <option value="ditolak" {{ $produk->status == 'ditolak' ? 'selected' : '' }}>Ditolak</option>
                                </select>
                            </div>
                            <div class="mb-6">
                                <label class="mb-1.5 block text-sm font-medium text-[var(--sm-ink)]">Catatan untuk Penjual</label>
                                <textarea name="alasan_ditolak" rows="3" class="form-input" placeholder="Tambahkan catatan jika produk perlu diperbaiki...">{{ old('alasan_ditolak', $produk->alasan_ditolak) }}</textarea>
                                @error('alasan_ditolak') <p class="mt-1 text-xs text-[var(--sm-primary)]">{{ $message }}</p> @enderror
                            </div>
                            <button type="submit" class="btn-primary">Simpan Status</button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
        
        {{-- Info Penjual --}}
        <div class="space-y-6">
            <div class="admin-card">
                <div class="admin-card-header bg-[var(--sm-primary-soft)]">
                    <h3 class="text-sm font-bold text-[var(--sm-ink)]">Tentang Penjual</h3>
                </div>
                <div class="p-5">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-12 h-12 rounded-full bg-[var(--sm-primary-soft)] text-[var(--sm-ink)] font-bold text-lg flex items-center justify-center">
                            {{ strtoupper(substr($produk->penjual->nama_toko, 0, 1)) }}
                        </div>
                        <div>
                            <h4 class="font-bold text-[var(--sm-ink)]">{{ $produk->penjual->nama_toko }}</h4>
                            <p class="text-xs text-[var(--sm-text-muted)]">{{ $produk->penjual->user->name }}</p>
                        </div>
                    </div>
                    
                    <div class="space-y-3 pt-3 border-t border-[var(--sm-border)]">
                        <div>
                            <p class="text-xs text-[var(--sm-text-muted)]">Lokasi / Kelas</p>
                            <p class="text-sm font-medium text-[var(--sm-ink)] mt-0.5">{{ $produk->penjual->lokasi_kelas }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-[var(--sm-text-muted)]">Kontak WhatsApp</p>
                            <a href="https://wa.me/{{ $produk->penjual->nomor_whatsapp }}" target="_blank" class="text-sm font-medium text-[var(--sm-primary)] hover:underline flex items-center gap-1 mt-0.5">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                                {{ $produk->penjual->nomor_whatsapp }}
                            </a>
                        </div>
                        <div>
                            <p class="text-xs text-[var(--sm-text-muted)]">Tanggal Pengajuan</p>
                            <p class="text-sm font-medium text-[var(--sm-ink)] mt-0.5">{{ $produk->created_at->format('d M Y, H:i') }}</p>
                        </div>
                    </div>
                </div>
            </div>
            
                    <form id="deleteProductForm" method="POST" action="{{ route('admin.produk.destroy', $produk) }}">
                @csrf @method('DELETE')
                <button type="submit" class="w-full btn-outline justify-center text-[var(--sm-primary)] border-[var(--sm-primary)] hover:bg-[var(--sm-primary-soft)]">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    Hapus Produk
                </button>
            </form>
        </div>
    </div>

    <dialog id="deleteProductDialog" class="product-delete-dialog" aria-labelledby="deleteProductTitle" aria-describedby="deleteProductMessage">
        <div class="product-delete-dialog__icon" aria-hidden="true">
            <svg class="w-7 h-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v4m0 4h.01M10.3 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.7 3.86a2 2 0 0 0-3.4 0Z" />
            </svg>
        </div>
        <h2 id="deleteProductTitle">Hapus produk?</h2>
        <p id="deleteProductMessage">Produk <strong>{{ $produk->nama }}</strong> akan dihapus secara permanen. Tindakan ini tidak dapat dibatalkan.</p>
        <div class="product-delete-dialog__actions">
            <button type="button" id="cancelDeleteProduct" class="product-delete-dialog__cancel">Batal</button>
            <button type="button" id="confirmDeleteProduct" class="product-delete-dialog__confirm">Ya, Hapus Produk</button>
        </div>
    </dialog>

    <style>
        .product-delete-dialog {
            width: min(calc(100% - 2rem), 28rem);
            padding: 1.5rem;
            border: 3px solid var(--sm-ink);
            border-radius: 1.5rem;
            color: var(--sm-ink);
            background: var(--sm-surface);
            box-shadow: 0 5px 0 var(--sm-ink);
        }
        .product-delete-dialog::backdrop { background: rgb(31 53 80 / 62%); }
        .product-delete-dialog__icon {
            display: grid;
            width: 3rem;
            height: 3rem;
            margin: 0 auto 1rem;
            place-items: center;
            border: 2px solid var(--sm-ink);
            border-radius: 50%;
            background: var(--sm-primary-soft);
        }
        .product-delete-dialog h2 { text-align: center; font-size: 1.25rem; font-weight: 800; }
        .product-delete-dialog p { margin-top: .75rem; text-align: center; line-height: 1.55; color: var(--sm-text-muted); }
        .product-delete-dialog__actions { display: flex; justify-content: flex-end; gap: .75rem; margin-top: 1.5rem; }
        .product-delete-dialog__actions button {
            min-height: 2.75rem;
            padding: .625rem 1rem;
            border: 2px solid var(--sm-ink);
            border-radius: 9999px;
            font-weight: 700;
            box-shadow: 0 3px 0 var(--sm-ink);
        }
        .product-delete-dialog__cancel { color: var(--sm-ink); background: var(--sm-surface); }
        .product-delete-dialog__confirm { color: var(--sm-on-primary); background: #DC2626; }
        .product-delete-dialog__actions button:focus-visible { outline: 3px solid var(--sm-primary); outline-offset: 3px; }
        @media (max-width: 480px) {
            .product-delete-dialog__actions { flex-direction: column-reverse; }
            .product-delete-dialog__actions button { width: 100%; }
        }
    </style>

    <script>
        (() => {
            const form = document.getElementById('deleteProductForm');
            const dialog = document.getElementById('deleteProductDialog');
            const cancel = document.getElementById('cancelDeleteProduct');
            const confirm = document.getElementById('confirmDeleteProduct');
            if (!form || !dialog || !cancel || !confirm) return;

            form.addEventListener('submit', (event) => {
                if (form.dataset.confirmed === 'true') return;
                event.preventDefault();
                dialog.showModal();
                cancel.focus();
            });

            cancel.addEventListener('click', () => dialog.close());
            confirm.addEventListener('click', () => {
                form.dataset.confirmed = 'true';
                form.requestSubmit();
            });
        })();
    </script>

@endsection
