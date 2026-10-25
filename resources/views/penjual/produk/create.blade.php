@extends('layouts.penjual')

@section('title', 'Tambah Produk Baru')
@section('page-title', 'Tambah Produk Baru')

@section('content')

    <div class="mb-6">
        <h2 class="text-xl font-bold text-[var(--sm-ink)]">Tambah Produk</h2>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <div class="admin-card p-6">
                <form method="POST" action="{{ route('penjual.produk.store') }}" enctype="multipart/form-data" class="space-y-6" id="produkForm">
                    @csrf

                    <h3 class="text-sm font-bold text-[var(--sm-ink)] border-b border-[var(--sm-border)] pb-2 mb-4 uppercase tracking-widest">Detail Produk</h3>

                    <div>
                        <label class="block text-sm font-medium text-[var(--sm-ink)] mb-1.5">Nama Produk</label>
                        <input type="text" name="nama" value="{{ old('nama') }}" required class="form-input" placeholder="Masukkan nama produk">
                        @error('nama') <p class="text-[var(--sm-primary)] text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <div>
                            <label class="block text-sm font-medium text-[var(--sm-ink)] mb-1.5">Kategori</label>
                            <select name="kategori_id" required class="form-input">
                                <option value="" disabled {{ old('kategori_id') ? '' : 'selected' }}>Pilih Kategori</option>
                                @foreach($kategori as $k)
                                    <option value="{{ $k->id }}" {{ old('kategori_id') == $k->id ? 'selected' : '' }}>{{ $k->nama }}</option>
                                @endforeach
                            </select>
                            @error('kategori_id') <p class="text-[var(--sm-primary)] text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-[var(--sm-ink)] mb-1.5">Harga (Rp)</label>
                            <input type="number" name="harga" value="{{ old('harga') }}" required min="0" class="form-input" placeholder="Contoh: 15000">
                            @error('harga') <p class="text-[var(--sm-primary)] text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-[var(--sm-ink)] mb-1.5">Deskripsi Produk</label>
                        <textarea name="deskripsi" rows="5" required class="form-input" placeholder="Ceritakan rasa, bahan, varian, atau porsinya...">{{ old('deskripsi') }}</textarea>
                        @error('deskripsi') <p class="text-[var(--sm-primary)] text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <h3 class="text-sm font-bold text-[var(--sm-ink)] border-b border-[var(--sm-border)] pb-2 mt-8 mb-4 uppercase tracking-widest">Foto & Ketersediaan</h3>

                    <div>
                        <label for="foto_utama" class="block text-sm font-medium text-[var(--sm-ink)] mb-1.5">Foto Produk <span class="text-xs text-[var(--sm-text-muted)] font-normal">(Opsional)</span></label>

                        <div class="relative">
                            <label for="foto_utama" class="form-input flex items-center justify-between cursor-pointer select-none" id="foto_upload_box">
                                <div class="flex items-center gap-3 overflow-hidden min-w-0 pr-2">
                                    <span class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full text-xs font-black bg-[var(--sm-primary)] text-[var(--sm-border)] border-2 border-[var(--sm-border)] flex-shrink-0">
                                        <svg class="w-4 h-4 text-[var(--sm-border)]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                        Pilih Foto
                                    </span>
                                    <span id="foto_nama_label" class="text-sm font-medium text-[var(--sm-text-muted)] truncate">
                                        Belum ada file dipilih
                                    </span>
                                </div>
                                <div class="flex items-center pr-1 text-[var(--sm-text-muted)] opacity-60 flex-shrink-0">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" /></svg>
                                </div>
                            </label>
                            <input type="file" name="foto_utama" id="foto_utama" accept="image/png,image/jpeg,image/jpg,image/webp" class="sr-only" onchange="previewFotoUtama(this)">
                        </div>

                        <!-- Preview Container -->
                        <div id="foto_preview_wrapper" class="hidden mt-3 p-3 bg-[var(--sm-surface)] border-2 border-[var(--sm-border)] rounded-2xl shadow-[2px_2px_0px_0px_var(--sm-border)] flex items-center justify-between">
                            <div class="flex items-center gap-3 min-w-0">
                                <img id="foto_preview_img" src="#" alt="Preview Foto" class="w-12 h-12 rounded-xl object-cover border-2 border-[var(--sm-border)] flex-shrink-0 shadow-[2px_2px_0px_0px_var(--sm-border)]">
                                <div class="min-w-0">
                                    <p id="foto_preview_name" class="text-xs font-bold text-[var(--sm-ink)] truncate"></p>
                                    <p id="foto_preview_size" class="text-[10px] text-[var(--sm-text-muted)]"></p>
                                </div>
                            </div>
                            <button type="button" onclick="hapusFotoUtama()" class="text-xs font-extrabold text-[var(--sm-ink)] hover:bg-[var(--sm-primary-soft)] px-3 py-1.5 rounded-full border border-[var(--sm-ink)] transition-colors">
                                Hapus
                            </button>
                        </div>

                        <p class="text-xs text-[var(--sm-text-muted)] mt-1.5">JPG, PNG, atau WEBP. Maksimal 2MB.</p>
                        @error('foto_utama') <p class="text-[var(--sm-primary)] text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-[var(--sm-ink)] mb-1.5">Status Ketersediaan</label>
                        <div class="flex gap-4">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" name="stok" value="tersedia" {{ old('stok', 'tersedia') === 'tersedia' ? 'checked' : '' }} class="w-4 h-4 accent-[var(--sm-primary)] border-[var(--sm-border)]">
                                <span class="text-sm text-[var(--sm-ink)]">Ready (Tersedia)</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" name="stok" value="po" {{ old('stok') === 'po' ? 'checked' : '' }} class="w-4 h-4 accent-[var(--sm-primary)] border-[var(--sm-border)]">
                                <span class="text-sm text-[var(--sm-ink)]">Pre-Order</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" name="stok" value="habis" {{ old('stok') === 'habis' ? 'checked' : '' }} class="w-4 h-4 accent-[var(--sm-primary)] border-[var(--sm-border)]">
                                <span class="text-sm text-[var(--sm-ink)]">Habis</span>
                            </label>
                        </div>
                        @error('stok') <p class="text-[var(--sm-primary)] text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </form>
            </div>
        </div>

        <div class="space-y-6">
            <div class="admin-card">
                <div class="admin-card-header bg-[var(--sm-primary-soft)] border-b-[var(--sm-border)]">
                    <h3 class="text-sm font-extrabold text-[var(--sm-ink)]">Publikasi</h3>
                </div>
                <div class="p-5">
                    <p class="text-xs text-[var(--sm-text-muted)] mb-5 leading-relaxed">Produkmu akan masuk status <span class="font-bold">Pending (Menunggu Moderasi)</span>. Admin akan mengeceknya sebelum produk tampil di katalog.</p>
                    <button type="button" onclick="document.getElementById('produkForm').submit()" class="w-full btn-primary justify-center py-2.5">
                        Kirim Produk
                    </button>
                    <div class="mt-3 text-center">
                        <a href="{{ route('penjual.produk.index') }}" class="text-xs text-[var(--sm-text-muted)] hover:text-[var(--sm-primary)] hover:underline font-bold">Batal</a>
                    </div>
                </div>
            </div>

            <div class="bg-[var(--sm-bg)] border-2 border-[var(--sm-border)] rounded-2xl p-4 shadow-[2px_2px_0px_0px_var(--sm-border)]">
                <div class="flex items-start gap-3">
                    <svg class="w-5 h-5 text-[var(--sm-primary)] flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <div>
                        <p class="text-sm font-bold text-[var(--sm-ink)]">Catatan</p>
                        <p class="text-xs text-[var(--sm-text-muted)] mt-0.5">Transaksi dilakukan langsung dengan pembeli. Pastikan nomor WhatsApp di profilmu sudah benar.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function previewFotoUtama(input) {
            const file = input.files && input.files[0];
            const label = document.getElementById('foto_nama_label');
            const previewWrapper = document.getElementById('foto_preview_wrapper');
            const previewImg = document.getElementById('foto_preview_img');
            const previewName = document.getElementById('foto_preview_name');
            const previewSize = document.getElementById('foto_preview_size');

            if (file) {
                label.textContent = file.name;
                label.classList.remove('text-[var(--sm-text-muted)]');
                label.classList.add('text-[var(--sm-ink)]', 'font-bold');

                if (previewWrapper && previewImg) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        previewImg.src = e.target.result;
                        previewName.textContent = file.name;
                        previewSize.textContent = (file.size / (1024 * 1024)).toFixed(2) + ' MB';
                        previewWrapper.classList.remove('hidden');
                    }
                    reader.readAsDataURL(file);
                }
            }
        }

        function hapusFotoUtama() {
            const input = document.getElementById('foto_utama');
            const label = document.getElementById('foto_nama_label');
            const previewWrapper = document.getElementById('foto_preview_wrapper');
            const previewImg = document.getElementById('foto_preview_img');

            input.value = '';
            label.textContent = 'Belum ada file dipilih';
            label.classList.add('text-[var(--sm-text-muted)]');
            label.classList.remove('text-[var(--sm-ink)]', 'font-bold');

            if (previewWrapper) {
                previewWrapper.classList.add('hidden');
            }
            if (previewImg) {
                previewImg.src = '#';
            }
        }
    </script>
@endsection
