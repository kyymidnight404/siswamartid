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
    @endphp

    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-[var(--text-main)]">Kelola Kategori</h2>
            <p class="text-sm text-[var(--text-muted)] mt-1">Kelola kategori produk SiswaMart.</p>
        </div>
        <button onclick="openModal('createModal')" class="btn-primary">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Tambah Kategori
        </button>
    </div>

    {{-- Search Toolbar --}}
    <div class="mb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <form action="{{ route('admin.categories.index') }}" method="GET" class="flex flex-wrap items-center gap-3 w-full md:w-auto">
            <input type="hidden" name="per_page" value="{{ request('per_page', 10) }}">

            <div class="relative min-w-[240px] flex-1 sm:flex-initial">
                <svg class="w-4 h-4 absolute left-3.5 top-1/2 -translate-y-1/2 text-[var(--text-muted)]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <input type="text"
                       name="search"
                       value="{{ request('search') }}"
                       placeholder="Cari kategori..."
                       class="form-input !h-10 !text-xs !pl-10 !py-2 w-full">
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
        <div class="mb-6 admin-flash" style="position: relative; transform: none; bottom: auto; left: auto; animation: none; border-color: #ea4335;">
            <svg class="w-4 h-4 text-[#ea4335] flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
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
            <table class="w-full text-sm text-left">
                <thead class="bg-[var(--bg-hover)] border-b border-[var(--border-color)]">
                    <tr>
                        <th class="px-5 py-4 text-xs font-semibold text-[var(--text-muted)] uppercase tracking-wider w-16 text-center">No</th>
                        <th class="px-5 py-4 text-xs font-semibold text-[var(--text-muted)] uppercase tracking-wider text-center">Ikon</th>
                        <th class="px-5 py-4 text-xs font-semibold text-[var(--text-muted)] uppercase tracking-wider">Nama Kategori</th>
                        <th class="px-5 py-4 text-xs font-semibold text-[var(--text-muted)] uppercase tracking-wider text-center">Jumlah Produk</th>
                        <th class="px-5 py-4 text-xs font-semibold text-[var(--text-muted)] uppercase tracking-wider">Dibuat</th>
                        <th class="px-5 py-4 text-xs font-semibold text-[var(--text-muted)] uppercase tracking-wider text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[var(--border-color)]">
                    @forelse($kategoris as $index => $kategori)
                    <tr class="hover:bg-[var(--bg-hover)] transition-colors">
                        <td class="px-5 py-4 text-center font-medium">{{ $kategoris->firstItem() + $index }}</td>
                        @php($categoryIcon = array_key_exists($kategori->icon, $iconOptions) ? $kategori->icon : ($fallbackCategoryImages[$kategori->slug] ?? 'images/category/snack.png'))
                        <td class="px-5 py-4 text-center">
                            <span class="mx-auto flex h-11 w-11 items-center justify-center rounded-full border-2 border-[var(--border-color)] bg-[var(--cream-2)] p-1.5">
                                <x-public.home-icon :name="$categoryIcons[$kategori->slug] ?? 'package'" :size="24" class="text-[var(--text-main)]" aria-hidden="true" />
                            </span>
                        </td>
                        <td class="px-5 py-4">
                            <div class="font-semibold text-[var(--text-main)]">{{ $kategori->nama }}</div>
                        </td>
                        <td class="px-5 py-4 text-center">
                            <span class="inline-flex items-center justify-center bg-[var(--color-primary-light)] text-[var(--text-main)] border border-[var(--border-color)] font-bold px-2 py-0.5 rounded-full text-xs">
                                {{ $kategori->produk_count }}
                            </span>
                        </td>
                        <td class="px-5 py-4">
                            <div class="text-xs text-[var(--text-muted)]">{{ $kategori->created_at->format('d M Y') }}</div>
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
                                    <button type="submit" class="btn-outline text-xs px-2.5 py-1 text-[#ea4335] border-[#ea4335] hover:bg-[#fce8e6]">
                                        Hapus
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-5 py-12">
                            <div class="flex flex-col items-center justify-center text-[var(--text-muted)]">
                                <svg class="w-16 h-16 mb-4 text-[var(--color-primary)] opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                                <p class="font-bold text-lg text-[var(--text-main)] mb-1">Belum ada kategori</p>
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
    <div id="createModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-900 bg-opacity-75 transition-opacity" aria-hidden="true" onclick="closeModal('createModal')"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block align-bottom bg-[var(--bg-surface)] border-2 border-[var(--border-color)] rounded-2xl text-left overflow-hidden shadow-[8px_8px_0px_0px_var(--border-color)] transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <form action="{{ route('admin.categories.store') }}" method="POST">
                    @csrf
                    <div class="bg-[var(--bg-surface)] px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <h3 class="text-lg leading-6 font-bold text-[var(--text-main)] mb-4" id="modal-title">Tambah Kategori Baru</h3>
                        <div class="mb-4">
                            <label for="nama" class="block text-sm font-bold text-[var(--text-main)] mb-1">Nama Kategori <span class="text-[#ea4335]">*</span></label>
                            <input type="text" name="nama" id="nama" class="form-input" placeholder="Contoh: Makanan & Minuman" required>
                        </div>
                        <div class="mb-4">
                            <label for="icon" class="block text-sm font-bold text-[var(--text-main)] mb-1">Ikon Kategori <span class="text-[#ea4335]">*</span></label>
                            <div class="flex items-center gap-3">
                                <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full border-2 border-[var(--border-color)] bg-[var(--cream-2)] p-2">
                                    <img id="create_icon_preview" src="{{ asset('images/category/dessert.png') }}" alt="Pratinjau ikon kategori" class="h-full w-full object-contain">
                                </span>
                                <select name="icon" id="icon" class="form-input" required onchange="previewCategoryIcon(this, 'create_icon_preview')">
                                    <option value="" disabled selected>Pilih ikon kategori</option>
                                    @foreach($iconOptions as $iconPath => $iconLabel)
                                        <option value="{{ $iconPath }}">{{ $iconLabel }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <p class="mt-1 text-xs text-[var(--text-muted)]">Pilih ikon yang tersedia di katalog SiswaMart.</p>
                        </div>
                    </div>
                    <div class="bg-[var(--bg-hover)] px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse border-t-2 border-[var(--border-color)]">
                        <button type="submit" class="w-full inline-flex justify-center rounded-full border-2 border-[var(--border-color)] shadow-[2px_2px_0px_0px_var(--border-color)] px-4 py-2 bg-[var(--color-primary)] text-base font-bold text-[var(--border-color)] hover:bg-[var(--color-primary-dark)] sm:ml-3 sm:w-auto sm:text-sm">
                            Simpan Kategori
                        </button>
                        <button type="button" onclick="closeModal('createModal')" class="mt-3 w-full inline-flex justify-center rounded-full border-2 border-[var(--border-color)] shadow-[2px_2px_0px_0px_var(--border-color)] px-4 py-2 bg-[var(--bg-surface)] text-base font-bold text-[var(--text-main)] hover:bg-[var(--bg-hover)] sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Batal
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Modal Edit --}}
    <div id="editModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-900 bg-opacity-75 transition-opacity" aria-hidden="true" onclick="closeModal('editModal')"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block align-bottom bg-[var(--bg-surface)] border-2 border-[var(--border-color)] rounded-2xl text-left overflow-hidden shadow-[8px_8px_0px_0px_var(--border-color)] transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <form id="editForm" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="bg-[var(--bg-surface)] px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <h3 class="text-lg leading-6 font-bold text-[var(--text-main)] mb-4" id="modal-title">Edit Kategori</h3>
                        <div class="mb-4">
                            <label for="edit_nama" class="block text-sm font-bold text-[var(--text-main)] mb-1">Nama Kategori <span class="text-[#ea4335]">*</span></label>
                            <input type="text" name="nama" id="edit_nama" class="form-input" required>
                        </div>
                        <div class="mb-4">
                            <label for="edit_icon" class="block text-sm font-bold text-[var(--text-main)] mb-1">Ikon Kategori <span class="text-[#ea4335]">*</span></label>
                            <div class="flex items-center gap-3">
                                <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full border-2 border-[var(--border-color)] bg-[var(--cream-2)] p-2">
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
                    <div class="bg-[var(--bg-hover)] px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse border-t-2 border-[var(--border-color)]">
                        <button type="submit" class="w-full inline-flex justify-center rounded-full border-2 border-[var(--border-color)] shadow-[2px_2px_0px_0px_var(--border-color)] px-4 py-2 bg-[var(--color-primary)] text-base font-bold text-[var(--border-color)] hover:bg-[var(--color-primary-dark)] sm:ml-3 sm:w-auto sm:text-sm">
                            Perbarui Kategori
                        </button>
                        <button type="button" onclick="closeModal('editModal')" class="mt-3 w-full inline-flex justify-center rounded-full border-2 border-[var(--border-color)] shadow-[2px_2px_0px_0px_var(--border-color)] px-4 py-2 bg-[var(--bg-surface)] text-base font-bold text-[var(--text-main)] hover:bg-[var(--bg-hover)] sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Batal
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function openModal(modalId) {
            document.getElementById(modalId).classList.remove('hidden');
        }

        function closeModal(modalId) {
            document.getElementById(modalId).classList.add('hidden');
        }

        function openEditModal(id, nama, icon) {
            const form = document.getElementById('editForm');
            form.action = `/admin/categories/${id}`;
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
            document.getElementById(previewId).src = `/${select.value}`;
        }
    </script>

@endsection

