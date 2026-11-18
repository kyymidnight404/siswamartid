@extends('layouts.admin')

@section('title', 'Kelola Kategori')
@section('page-title', 'Kelola Kategori')

@section('content')
    @php
        $categoryIcons = [
            'dessert' => 'cake',
            'makanan' => 'burger',
            'makanan-berat' => 'burger',
            'minuman' => 'glass-full',
            'snack' => 'cookie',
        ];
        $fallbackCategoryImages = [
            'dessert' => 'images/category/dessert.png',
            'makanan' => 'images/category/makanan_berat.png',
            'makanan-berat' => 'images/category/makanan_berat.png',
            'minuman' => 'images/category/minuman.png',
            'snack' => 'images/category/snack.png',
        ];
        $iconToHomeIcon = [
            'images/category/dessert.png' => 'cake',
            'images/category/makanan_berat.png' => 'burger',
            'images/category/minuman.png' => 'glass-full',
            'images/category/snack.png' => 'cookie',
        ];
    @endphp

    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-[var(--sm-ink)]">Kelola Kategori</h2>
            <p class="text-sm text-[var(--sm-text-muted)] mt-1">Kelola kategori produk SiswaMart.</p>
        </div>
        <button type="button" onclick="openModal('createModal')" class="btn-primary">
            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Tambah Kategori
        </button>
    </div>

    {{-- Search Toolbar --}}
    <div class="mb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <form action="{{ route('admin.categories.index') }}" method="GET" class="flex flex-wrap items-center gap-3 w-full md:w-auto">
            <input type="hidden" name="per_page" value="{{ request('per_page', 10) }}">

            <div class="admin-search-field relative min-w-[240px] flex-1 sm:flex-initial">
                <svg class="w-4 h-4 absolute left-3.5 top-1/2 -translate-y-1/2 text-[var(--sm-text-muted)] pointer-events-none flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <input type="text"
                       name="search"
                       value="{{ request('search') }}"
                       aria-label="Cari kategori"
                       placeholder="Cari kategori..."
                       class="form-input !h-10 !text-xs pl-12 !py-2 w-full">
            </div>

            <button type="submit" class="btn-primary !h-10 !text-xs !py-2 !px-4">
                Cari
            </button>

            @if(request('search'))
                <a href="{{ route('admin.categories.index', ['per_page' => request('per_page', 10)]) }}" class="btn-outline !h-10 !text-xs !py-2 !px-3" title="Reset Pencarian">
                    Reset
                </a>
            @endif
        </form>
    </div>

    {{-- Validasi Error Flash --}}
    @if ($errors->any())
        <div class="mb-6 admin-flash" style="position: relative; transform: none; bottom: auto; left: auto; animation: none; border-color: var(--sm-primary);">
            <svg class="w-4 h-4 text-[var(--sm-primary)] flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
            <div>
                <strong class="font-bold">Terjadi kesalahan!</strong>
                <ul class="list-disc list-inside text-xs mt-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    <div class="admin-card">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[640px] text-sm text-left">
                <thead class="bg-[var(--sm-primary-soft)] border-b border-[var(--sm-border)]">
                    <tr>
                        <th class="px-5 py-4 text-xs font-semibold text-[var(--sm-text-muted)] uppercase tracking-wider w-16 text-center">No</th>
                        <th class="px-5 py-4 text-xs font-semibold text-[var(--sm-text-muted)] uppercase tracking-wider text-center">Ikon</th>
                        <th class="px-5 py-4 text-xs font-semibold text-[var(--sm-text-muted)] uppercase tracking-wider">Nama Kategori</th>
                        <th class="px-5 py-4 text-xs font-semibold text-[var(--sm-text-muted)] uppercase tracking-wider text-center">Jumlah Produk</th>
                        <th class="px-5 py-4 text-xs font-semibold text-[var(--sm-text-muted)] uppercase tracking-wider">Dibuat</th>
                        <th class="px-5 py-4 text-xs font-semibold text-[var(--sm-text-muted)] uppercase tracking-wider text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[var(--sm-border)]">
                    @forelse($kategoris as $index => $kategori)
                    <tr class="hover:bg-[var(--sm-primary-soft)] transition-colors">
                        <td class="px-5 py-4 text-center font-medium">{{ $kategoris->firstItem() + $index }}</td>
                        @php($categoryIcon = array_key_exists($kategori->icon ?? '', $iconOptions) ? $kategori->icon : ($fallbackCategoryImages[$kategori->slug] ?? 'images/category/snack.png'))
                        @php($resolvedHomeIcon = $categoryIcons[$kategori->slug] ?? ($iconToHomeIcon[$kategori->icon ?? ''] ?? 'package'))
                        <td class="px-5 py-4 text-center">
                            <span class="mx-auto flex h-11 w-11 items-center justify-center rounded-full border-2 border-[var(--sm-border)] bg-[var(--sm-primary-soft)] p-1.5 flex-shrink-0">
                                <x-public.home-icon :name="$resolvedHomeIcon" :size="24" class="text-[var(--sm-ink)]" aria-hidden="true" />
                            </span>
                        </td>
                        <td class="px-5 py-4">
                            <div class="font-semibold text-[var(--sm-ink)]">{{ $kategori->nama }}</div>
                        </td>
                        <td class="px-5 py-4 text-center">
                            <span class="inline-flex items-center justify-center bg-[var(--sm-primary-soft)] text-[var(--sm-ink)] border border-[var(--sm-border)] font-bold px-2 py-0.5 rounded-full text-xs">
                                {{ $kategori->produk_count }}
                            </span>
                        </td>
                        <td class="px-5 py-4">
                            <div class="text-xs text-[var(--sm-text-muted)]">{{ $kategori->created_at->format('d M Y') }}</div>
                        </td>
                        <td class="px-5 py-4">
                            <div class="flex items-center justify-end gap-2">
                                <button type="button" data-kategori-id="{{ $kategori->id }}" data-kategori-nama="{{ $kategori->nama }}" data-kategori-icon="{{ $categoryIcon }}" onclick="openEditModalFromButton(this)"
                                   class="btn-outline text-xs px-2.5 py-1">
                                    Edit
                                </button>
                                <form method="POST" action="{{ route('admin.categories.destroy', $kategori) }}"
                                      onsubmit="return confirm('Yakin ingin menghapus kategori ini?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn-outline text-xs px-2.5 py-1 text-[var(--sm-primary)] border-[var(--sm-primary)] hover:bg-[var(--sm-primary-soft)]">
                                        Hapus
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-5 py-12">
                            <div class="flex flex-col items-center justify-center text-[var(--sm-text-muted)]">
                                <svg class="w-16 h-16 mb-4 text-[var(--sm-primary)] opacity-50 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                                <p class="font-bold text-lg text-[var(--sm-ink)] mb-1">Belum ada kategori</p>
                                <p class="text-sm">Tambahkan kategori untuk mengelompokkan produk.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <x-admin.table-pagination :paginator="$kategoris" />
    </div>

    {{-- Modal Create --}}
    <div id="createModal" class="fixed inset-0 z-[70] hidden overflow-y-auto" aria-labelledby="create-modal-title" role="dialog" aria-modal="true">
        <div class="flex min-h-full items-center justify-center p-4">
            <div class="fixed inset-0 bg-black/60 transition-opacity" aria-hidden="true" onclick="closeModal('createModal')"></div>
            <div class="relative z-10 w-full max-w-lg bg-[var(--sm-surface)] border-2 border-[var(--sm-border)] rounded-2xl text-left overflow-hidden shadow-[8px_8px_0px_0px_var(--sm-border)] transition-all">
                <form action="{{ route('admin.categories.store') }}" method="POST">
                    @csrf
                    <div class="bg-[var(--sm-surface)] px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="flex items-center justify-between gap-3 mb-4">
                            <h3 class="text-lg leading-6 font-bold text-[var(--sm-ink)]" id="create-modal-title">Tambah Kategori Baru</h3>
                            <button type="button" onclick="closeModal('createModal')" aria-label="Tutup modal" class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-full border-2 border-[var(--sm-border)] bg-[var(--sm-surface)] text-[var(--sm-ink)] hover:bg-[var(--sm-primary-soft)]">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>
                        <div class="mb-4">
                            <label for="nama" class="block text-sm font-bold text-[var(--sm-ink)] mb-1">Nama Kategori <span class="text-[var(--sm-primary)]">*</span></label>
                            <input type="text" name="nama" id="nama" class="form-input" placeholder="Contoh: Makanan & Minuman" required>
                        </div>
                        <div class="mb-4">
                            <label for="icon" class="block text-sm font-bold text-[var(--sm-ink)] mb-1">Ikon Kategori <span class="text-[var(--sm-primary)]">*</span></label>
                            <div class="flex items-center gap-3">
                                <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full border-2 border-[var(--sm-border)] bg-[var(--sm-primary-soft)] p-2">
                                    <img id="create_icon_preview" src="{{ asset('images/category/dessert.png') }}" alt="Pratinjau ikon kategori" class="h-full w-full object-contain">
                                </span>
                                <select name="icon" id="icon" class="form-input" required onchange="previewCategoryIcon(this, 'create_icon_preview')">
                                    <option value="" disabled selected>Pilih ikon kategori</option>
                                    @foreach($iconOptions as $iconPath => $iconLabel)
                                        <option value="{{ $iconPath }}">{{ $iconLabel }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <p class="mt-1 text-xs text-[var(--sm-text-muted)]">Pilih ikon yang tersedia di katalog SiswaMart.</p>
                        </div>
                    </div>
                    <div class="bg-[var(--sm-primary-soft)] px-4 py-3 sm:px-6 flex flex-col-reverse sm:flex-row sm:justify-end gap-3 border-t-2 border-[var(--sm-border)]">
                        <button type="button" onclick="closeModal('createModal')" class="btn-outline w-full justify-center sm:w-auto">
                            Batal
                        </button>
                        <button type="submit" class="btn-primary w-full justify-center sm:w-auto">
                            Simpan Kategori
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Modal Edit --}}
    <div id="editModal" class="fixed inset-0 z-[70] hidden overflow-y-auto" aria-labelledby="edit-modal-title" role="dialog" aria-modal="true">
        <div class="flex min-h-full items-center justify-center p-4">
            <div class="fixed inset-0 bg-black/60 transition-opacity" aria-hidden="true" onclick="closeModal('editModal')"></div>
            <div class="relative z-10 w-full max-w-lg bg-[var(--sm-surface)] border-2 border-[var(--sm-border)] rounded-2xl text-left overflow-hidden shadow-[8px_8px_0px_0px_var(--sm-border)] transition-all">
                <form id="editForm" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="bg-[var(--sm-surface)] px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="flex items-center justify-between gap-3 mb-4">
                            <h3 class="text-lg leading-6 font-bold text-[var(--sm-ink)]" id="edit-modal-title">Edit Kategori</h3>
                            <button type="button" onclick="closeModal('editModal')" aria-label="Tutup modal" class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-full border-2 border-[var(--sm-border)] bg-[var(--sm-surface)] text-[var(--sm-ink)] hover:bg-[var(--sm-primary-soft)]">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>
                        <div class="mb-4">
                            <label for="edit_nama" class="block text-sm font-bold text-[var(--sm-ink)] mb-1">Nama Kategori <span class="text-[var(--sm-primary)]">*</span></label>
                            <input type="text" name="nama" id="edit_nama" class="form-input" required>
                        </div>
                        <div class="mb-4">
                            <label for="edit_icon" class="block text-sm font-bold text-[var(--sm-ink)] mb-1">Ikon Kategori <span class="text-[var(--sm-primary)]">*</span></label>
                            <div class="flex items-center gap-3">
                                <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full border-2 border-[var(--sm-border)] bg-[var(--sm-primary-soft)] p-2">
                                    <img id="edit_icon_preview" src="{{ asset('images/category/dessert.png') }}" alt="Pratinjau ikon kategori" class="h-full w-full object-contain">
                                </span>
                                <select name="icon" id="edit_icon" class="form-input" required onchange="previewCategoryIcon(this, 'edit_icon_preview')">
                                    @foreach($iconOptions as $iconPath => $iconLabel)
                                        <option value="{{ $iconPath }}">{{ $iconLabel }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="bg-[var(--sm-primary-soft)] px-4 py-3 sm:px-6 flex flex-col-reverse sm:flex-row sm:justify-end gap-3 border-t-2 border-[var(--sm-border)]">
                        <button type="button" onclick="closeModal('editModal')" class="btn-outline w-full justify-center sm:w-auto">
                            Batal
                        </button>
                        <button type="submit" class="btn-primary w-full justify-center sm:w-auto">
                            Perbarui Kategori
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        const categoryAssetBase = @json(rtrim(asset(''), '/'));
        const categoryUpdateUrlTemplate = @json(route('admin.categories.update', ['category' => '__ID__']));

        function syncModalScrollLock() {
            const hasOpenModal = ['createModal', 'editModal'].some((id) => {
                const el = document.getElementById(id);
                return el && !el.classList.contains('hidden');
            });
            document.body.classList.toggle('overflow-hidden', hasOpenModal);
        }

        function openModal(modalId) {
            const modal = document.getElementById(modalId);
            if (!modal) return;
            modal.classList.remove('hidden');
            syncModalScrollLock();

            const focusTarget = modal.querySelector('input[type="text"]');
            if (focusTarget) {
                requestAnimationFrame(() => focusTarget.focus());
            }
        }

        function closeModal(modalId) {
            const modal = document.getElementById(modalId);
            if (!modal) return;
            modal.classList.add('hidden');
            syncModalScrollLock();
        }

        function openEditModal(id, nama, icon) {
            const form = document.getElementById('editForm');
            form.action = categoryUpdateUrlTemplate.replace('__ID__', encodeURIComponent(id));
            document.getElementById('edit_nama').value = nama;
            const iconSelect = document.getElementById('edit_icon');
            iconSelect.value = icon;
            previewCategoryIcon(iconSelect, 'edit_icon_preview');
            openModal('editModal');
        }

        function openEditModalFromButton(button) {
            openEditModal(button.dataset.kategoriId, button.dataset.kategoriNama, button.dataset.kategoriIcon);
        }

        function previewCategoryIcon(select, previewId) {
            const preview = document.getElementById(previewId);
            if (!preview || !select || !select.value) return;
            preview.src = `${categoryAssetBase}/${select.value.replace(/^\/+/, '')}`;
        }

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') {
                closeModal('createModal');
                closeModal('editModal');
            }
        });
    </script>

@endsection

