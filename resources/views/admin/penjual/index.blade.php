@extends('layouts.admin')

@section('title', 'Manajemen Penjual')
@section('page-title', 'Manajemen Penjual')

@section('content')

    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-[var(--text-main)]">Daftar Penjual</h2>
            <p class="text-sm text-[var(--text-muted)] mt-1">Atur akun siswa yang berjualan di SiswaMart.</p>
        </div>
        <a href="{{ route('admin.penjual.create') }}" class="btn-primary">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Tambah Penjual Baru
        </a>
    </div>

    {{-- Search & Status Filter Toolbar --}}
    <div class="mb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <form method="GET" action="{{ route('admin.penjual.index') }}" class="flex flex-wrap items-center gap-3 w-full md:w-auto">
            <input type="hidden" name="per_page" value="{{ request('per_page', 10) }}">

            {{-- Search Input --}}
            <div class="relative min-w-[240px] flex-1 sm:flex-initial">
                <svg class="w-4 h-4 absolute left-3.5 top-1/2 -translate-y-1/2 text-[var(--text-muted)]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <input type="text"
                       name="search"
                       value="{{ request('search') }}"
                       placeholder="Cari toko, nama, email, kelas..."
                       class="form-input !h-10 !text-xs !pl-10 !py-2 w-full">
            </div>

            {{-- Status Filter --}}
            <select name="status" onchange="this.form.submit()" class="form-input !h-10 !text-xs !py-2 !w-auto font-bold bg-[var(--bg-surface)]">
                <option value="">Semua Status</option>
                <option value="aktif" {{ request('status') === 'aktif' ? 'selected' : '' }}>Akun Aktif</option>
                <option value="nonaktif" {{ request('status') === 'nonaktif' ? 'selected' : '' }}>Akun Nonaktif</option>
                <option value="buka" {{ request('status') === 'buka' ? 'selected' : '' }}>Toko Buka</option>
                <option value="tutup" {{ request('status') === 'tutup' ? 'selected' : '' }}>Toko Tutup</option>
            </select>

            <button type="submit" class="btn-primary !h-10 !text-xs !py-2 !px-4">
                Cari
            </button>

            @if(request('search') || request('status'))
                <a href="{{ route('admin.penjual.index', ['per_page' => request('per_page', 10)]) }}" class="btn-outline !h-10 !text-xs !py-2 !px-3" title="Reset Filter">
                    Reset
                </a>
            @endif
        </form>
    </div>

    <div class="admin-card">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-[var(--bg-hover)] border-b border-[var(--border-color)]">
                    <tr>
                        <th class="px-5 py-4 text-xs font-semibold text-[var(--text-muted)] uppercase tracking-wider">Toko / Penjual</th>
                        <th class="px-5 py-4 text-xs font-semibold text-[var(--text-muted)] uppercase tracking-wider">Lokasi / Kontak</th>
                        <th class="px-5 py-4 text-xs font-semibold text-[var(--text-muted)] uppercase tracking-wider">Status Akun</th>
                        <th class="px-5 py-4 text-xs font-semibold text-[var(--text-muted)] uppercase tracking-wider text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[var(--border-color)]">
                    @forelse($penjual as $p)
                    <tr class="hover:bg-[var(--bg-hover)] transition-colors">
                        <td class="px-5 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-[#e8f0fe] text-[#1a73e8] font-bold text-sm flex items-center justify-center flex-shrink-0">
                                    {{ strtoupper(substr($p->name, 0, 1)) }}
                                </div>
                                <div>
                                    <div class="font-semibold text-[var(--text-main)]">{{ $p->penjual?->nama_toko ?? 'Toko Belum Diatur' }}</div>
                                    <div class="text-xs text-[var(--text-muted)] mt-0.5">{{ $p->name }}</div>
                                    <div class="text-[10px] text-[var(--text-muted)]">{{ $p->email }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-5 py-4">
                            <div class="text-[var(--text-main)] font-medium text-sm">{{ $p->penjual?->lokasi_kelas ?? '-' }}</div>
                            <div class="text-xs text-[var(--text-muted)] mt-0.5 flex items-center gap-1">
                                <svg class="w-3 h-3 text-[#34a853]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                                {{ $p->penjual?->nomor_whatsapp ?? '-' }}
                            </div>
                        </td>
                        <td class="px-5 py-4">
                            @if($p->is_active)
                                <span class="badge badge-aktif">Aktif</span>
                            @else
                                <span class="badge badge-ditolak">Nonaktif</span>
                            @endif
                        </td>
                        <td class="px-5 py-4">
                            <div class="flex items-center justify-end gap-2">
                                <form method="POST" action="{{ route('admin.penjual.toggleStatus', $p) }}"
                                      class="seller-confirm-form" data-confirm-kind="status"
                                      data-seller-label="{{ $p->penjual?->nama_toko ?? $p->name }}">
                                    @csrf @method('PATCH')
                                    <button type="submit"
                                            class="btn-outline text-xs px-2.5 py-1 {{ $p->is_active ? 'text-[#ea4335] border-[#ea4335] hover:bg-[#fce8e6]' : 'text-[#34a853] border-[#34a853] hover:bg-[#e6f4ea]' }}">
                                        {{ $p->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                    </button>
                                </form>
                                <a href="{{ route('admin.penjual.edit', $p) }}"
                                   class="btn-outline text-xs px-2.5 py-1">
                                    Edit
                                </a>
                                <a href="{{ route('admin.penjual.show', $p) }}"
                                   class="btn-outline text-xs px-2.5 py-1">
                                    Detail
                                </a>
                                <form method="POST" action="{{ route('admin.penjual.destroy', $p) }}"
                                      class="seller-confirm-form" data-confirm-kind="delete"
                                      data-seller-label="{{ $p->penjual?->nama_toko ?? $p->name }}">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn-outline text-xs px-2.5 py-1 text-[#ea4335] border-[#ea4335] hover:bg-[#fce8e6]">
                                        Hapus
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-5 py-12 text-center text-[var(--text-muted)]">
                            Belum ada akun penjual yang sesuai pencarian / filter.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <x-admin.table-pagination :paginator="$penjual" />
    </div>

    <style>
        .seller-confirm-overlay {
            position: fixed;
            inset: 0;
            z-index: 10001;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
            background: rgba(17, 24, 39, 0.62);
            backdrop-filter: blur(3px);
        }
        .seller-confirm-overlay[hidden] { display: none; }
        .seller-confirm-dialog {
            width: min(100%, 28rem);
            max-height: min(90vh, 38rem);
            overflow-y: auto;
            padding: 1.5rem;
            border: 2px solid var(--border-color);
            border-radius: 1.5rem;
            background: var(--bg-surface);
            color: var(--text-main);
            box-shadow: 6px 6px 0 var(--border-color);
            animation: sellerConfirmIn 180ms ease-out both;
        }
        @keyframes sellerConfirmIn {
            from { opacity: 0; transform: translateY(8px) scale(.97); }
            to { opacity: 1; transform: translateY(0) scale(1); }
        }
        .seller-confirm-icon {
            display: grid;
            width: 3.5rem;
            height: 3.5rem;
            margin: 0 auto 1rem;
            place-items: center;
            border: 2px solid #b42318;
            border-radius: 1rem;
            background: #fef3f2;
            color: #b42318;
        }
        .seller-confirm-actions { display: flex; justify-content: flex-end; gap: .75rem; margin-top: 1.5rem; }
        .seller-confirm-cancel, .seller-confirm-submit {
            min-height: 2.75rem;
            padding: .625rem 1rem;
            border: 2px solid var(--border-color);
            border-radius: 9999px;
            font-weight: 800;
            box-shadow: 3px 3px 0 var(--border-color);
            transition: transform 150ms ease, box-shadow 150ms ease, background 150ms ease;
        }
        .seller-confirm-cancel { background: var(--bg-surface); color: var(--text-main); }
        .seller-confirm-submit { border-color: #7f1d1d; background: #dc2626; color: #fff; }
        .seller-confirm-cancel:hover, .seller-confirm-submit:hover { transform: translate(-1px, -1px); box-shadow: 4px 4px 0 var(--border-color); }
        .seller-confirm-submit:disabled { cursor: wait; opacity: .75; transform: none; }
        @media (max-width: 480px) {
            .seller-confirm-dialog { padding: 1.25rem; border-radius: 1.25rem; }
            .seller-confirm-actions { flex-direction: column-reverse; }
            .seller-confirm-cancel, .seller-confirm-submit { width: 100%; }
        }
        @media (prefers-reduced-motion: reduce) {
            .seller-confirm-dialog { animation: none; }
            .seller-confirm-cancel, .seller-confirm-submit { transition: none; }
        }
    </style>

    <div id="sellerConfirmOverlay" class="seller-confirm-overlay" hidden>
        <section id="sellerConfirmDialog" class="seller-confirm-dialog" role="dialog" aria-modal="true"
                 aria-labelledby="sellerConfirmTitle" aria-describedby="sellerConfirmMessage" tabindex="-1">
            <div class="seller-confirm-icon" aria-hidden="true">
                <svg class="w-7 h-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v4m0 4h.01M10.3 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.7 3.86a2 2 0 0 0-3.4 0Z" />
                </svg>
            </div>
            <h2 id="sellerConfirmTitle" class="text-xl font-black text-center">Hapus Penjual?</h2>
            <p id="sellerConfirmMessage" class="mt-3 text-sm leading-relaxed text-center text-[var(--text-muted)]">
                Tindakan ini akan menghapus akun penjual beserta produk yang terkait secara permanen. Tindakan ini tidak dapat dibatalkan.
            </p>
            <p id="sellerConfirmSeller" class="mt-3 text-sm font-extrabold text-center text-[var(--text-main)]" hidden></p>
            <div class="seller-confirm-actions">
                <button id="sellerConfirmCancel" type="button" class="seller-confirm-cancel">Batal</button>
                <button id="sellerConfirmSubmit" type="button" class="seller-confirm-submit">Ya, Hapus Penjual</button>
            </div>
        </section>
    </div>

    <script>
        (() => {
            const overlay = document.getElementById('sellerConfirmOverlay');
            const title = document.getElementById('sellerConfirmTitle');
            const message = document.getElementById('sellerConfirmMessage');
            const sellerName = document.getElementById('sellerConfirmSeller');
            const cancelButton = document.getElementById('sellerConfirmCancel');
            const submitButton = document.getElementById('sellerConfirmSubmit');
            let pendingForm = null;
            let returnFocusTo = null;
            let submitting = false;

            const closeModal = () => {
                if (overlay.hidden || submitting) return;
                overlay.hidden = true;
                document.body.style.overflow = '';
                pendingForm = null;
                if (returnFocusTo?.isConnected) returnFocusTo.focus();
            };

            document.querySelectorAll('.seller-confirm-form').forEach((form) => {
                form.addEventListener('submit', (event) => {
                    event.preventDefault();
                    if (submitting) return;

                    pendingForm = form;
                    returnFocusTo = document.activeElement;
                    const isDelete = form.dataset.confirmKind === 'delete';
                    title.textContent = isDelete ? 'Hapus Penjual?' : 'Ubah Status Akun?';
                    message.textContent = isDelete
                        ? 'Tindakan ini akan menghapus akun penjual beserta produk yang terkait secara permanen. Tindakan ini tidak dapat dibatalkan.'
                        : 'Ubah status akun penjual ini?';
                    sellerName.textContent = form.dataset.sellerLabel || '';
                    sellerName.hidden = !sellerName.textContent;
                    submitButton.textContent = isDelete ? 'Ya, Hapus Penjual' : 'Ya, Lanjutkan';
                    submitButton.dataset.loadingText = isDelete ? 'Menghapus…' : 'Memproses…';
                    submitButton.disabled = false;
                    submitting = false;
                    overlay.hidden = false;
                    document.body.style.overflow = 'hidden';
                    cancelButton.focus();
                });
            });

            cancelButton.addEventListener('click', closeModal);
            overlay.addEventListener('click', (event) => {
                if (event.target === overlay) closeModal();
            });
            document.addEventListener('keydown', (event) => {
                if (overlay.hidden) return;
                if (event.key === 'Escape') {
                    event.preventDefault();
                    closeModal();
                    return;
                }
                if (event.key === 'Tab') {
                    const focusable = [cancelButton, submitButton].filter((button) => !button.disabled);
                    const first = focusable[0];
                    const last = focusable[focusable.length - 1];
                    if (event.shiftKey && document.activeElement === first) {
                        event.preventDefault();
                        last.focus();
                    } else if (!event.shiftKey && document.activeElement === last) {
                        event.preventDefault();
                        first.focus();
                    }
                }
            });
            submitButton.addEventListener('click', () => {
                if (!pendingForm || submitting) return;
                submitting = true;
                submitButton.disabled = true;
                submitButton.textContent = submitButton.dataset.loadingText;
                pendingForm.submit();
            });
        })();
    </script>

@endsection

