@extends('layouts.penjual')

@section('title', 'Edit Produk')
@section('page-title', 'Edit Produk')

@section('content')

    <div class="mb-6 flex items-center gap-3">
        <a href="{{ route('penjual.produk.index') }}" class="p-2 rounded-full hover:bg-[var(--bg-hover)] text-[var(--text-muted)] transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        </a>
        <h2 class="text-xl font-bold text-[var(--text-main)]">Edit Produk</h2>
    </div>

    @if($produk->pending_action)
        <div class="mb-6 rounded-2xl border-2 border-[var(--ink)] bg-[var(--warning-bg)] p-4 shadow-[3px_3px_0_var(--ink)]">
            <p class="text-sm font-bold text-[var(--ink)]">Pengajuan menunggu persetujuan Admin.</p>
            <p class="mt-1 text-xs text-[var(--ink-soft)]">Kamu belum bisa mengirim perubahan lain sebelum Admin memutuskan pengajuan ini.</p>
        </div>
    @elseif($produk->alasan_ditolak && $produk->status !== 'ditolak')
        <div class="mb-6 rounded-2xl border-2 border-[var(--ink)] bg-[var(--warning-bg)] p-4 shadow-[3px_3px_0_var(--ink)]">
            <p class="text-sm font-bold text-[var(--ink)]">Pengajuan sebelumnya ditolak.</p>
            <p class="mt-1 text-xs text-[var(--ink-soft)]">Catatan Admin: {{ $produk->alasan_ditolak }}</p>
        </div>
    @elseif($produk->status === 'ditolak')
        <div class="bg-[#fce8e6] border border-[#ea4335] rounded-2xl p-4 mb-6 shadow-sm">
            <div class="flex items-start gap-3">
                <svg class="w-5 h-5 text-[#ea4335] flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <div>
                    <p class="text-sm font-bold text-[#c5221f]">Produk Ditolak</p>
                    <p class="text-xs text-[#c5221f]/90 mt-1 mb-2">Produkmu ditolak Admin. Perbaiki sesuai catatan berikut, lalu kirim lagi untuk ditinjau.</p>
                    <div class="text-sm bg-white/50 px-3 py-2 rounded-lg border border-[#ea4335]/30 text-[#c5221f] italic">
                        "{{ $produk->alasan_ditolak ?? 'Tidak ada alasan spesifik yang diberikan.' }}"
                    </div>
                </div>
            </div>
        </div>
    @elseif($produk->status === 'pending')
        <div class="bg-[#fef7e0] border border-[#fbbc04] rounded-2xl p-4 mb-6 shadow-sm">
            <div class="flex items-start gap-3">
                <svg class="w-5 h-5 text-[#b06000] flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <div>
                    <p class="text-sm font-bold text-[#b06000]">Menunggu Moderasi</p>
                    <p class="text-xs text-[#b06000]/90 mt-0.5">Produkmu sedang ditinjau. Setiap perubahan yang kamu ajukan akan menunggu persetujuan Admin.</p>
                </div>
            </div>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <div class="admin-card p-6">
                <form method="POST" action="{{ route('penjual.produk.update', $produk) }}" enctype="multipart/form-data" class="space-y-6" id="produkForm">
                    @csrf @method('PUT')

                    <h3 class="text-sm font-bold text-[var(--text-main)] border-b border-[var(--border-color)] pb-2 mb-4 uppercase tracking-widest">Informasi Utama</h3>

                    <div>
                        <label class="block text-sm font-medium text-[var(--text-main)] mb-1.5">Nama Produk</label>
                        <input type="text" name="nama" value="{{ old('nama', $produk->nama) }}" required class="form-input">
                        @error('nama') <p class="text-[#ea4335] text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <div>
                            <label class="block text-sm font-medium text-[var(--text-main)] mb-1.5">Kategori</label>
                            <select name="kategori_id" required class="form-input">
                                @foreach($kategori as $k)
                                    <option value="{{ $k->id }}" {{ (old('kategori_id', $produk->kategori_id) == $k->id) ? 'selected' : '' }}>{{ $k->nama }}</option>
                                @endforeach
                            </select>
                            @error('kategori_id') <p class="text-[#ea4335] text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-[var(--text-main)] mb-1.5">Harga (Rp)</label>
                            <input type="number" name="harga" value="{{ old('harga', $produk->harga) }}" required min="0" class="form-input">
                            @error('harga') <p class="text-[#ea4335] text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-[var(--text-main)] mb-1.5">Deskripsi Produk</label>
                        <textarea name="deskripsi" rows="5" required class="form-input">{{ old('deskripsi', $produk->deskripsi) }}</textarea>
                        @error('deskripsi') <p class="text-[#ea4335] text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <h3 class="text-sm font-bold text-[var(--text-main)] border-b border-[var(--border-color)] pb-2 mt-8 mb-4 uppercase tracking-widest">Media & Status</h3>

                    <div class="flex flex-col sm:flex-row gap-6 items-start">
                        @if($produk->foto_utama)
                            <div class="w-24 h-24 rounded-lg overflow-hidden border border-[var(--border-color)] flex-shrink-0">
                                <img src="{{ asset('storage/' . $produk->foto_utama) }}" alt="Preview" class="w-full h-full object-cover">
                            </div>
                        @else
                            <div class="w-24 h-24 rounded-lg bg-[var(--bg-hover)] border border-[var(--border-color)] flex-shrink-0 flex items-center justify-center text-xs text-[var(--text-muted)] text-center p-2">
                                Tidak ada foto
                            </div>
                        @endif

                        <div class="flex-1 w-full">
                            <label for="foto_utama" class="block text-sm font-medium text-[var(--text-main)] mb-1.5">Ganti Foto <span class="text-xs text-[var(--text-muted)] font-normal">(Opsional)</span></label>

                            <div class="relative">
                                <label for="foto_utama" class="form-input flex items-center justify-between cursor-pointer select-none" id="foto_upload_box">
                                    <div class="flex items-center gap-3 overflow-hidden min-w-0 pr-2">
                                        <span class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full text-xs font-black bg-[var(--color-primary)] text-[var(--border-color)] border-2 border-[var(--border-color)] flex-shrink-0">
                                            <svg class="w-4 h-4 text-[var(--border-color)]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                            Pilih Foto Baru
                                        </span>
                                        <span id="foto_nama_label" class="text-sm font-medium text-[var(--text-muted)] truncate">
                                            Pilih file gambar jika ingin mengganti
                                        </span>
                                    </div>
                                    <div class="flex items-center pr-1 text-[var(--text-muted)] opacity-60 flex-shrink-0">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" /></svg>
                                    </div>
                                </label>
                                <input type="file" name="foto_utama" id="foto_utama" accept="image/png,image/jpeg,image/jpg,image/webp" class="sr-only" onchange="previewFotoUtama(this)">
                            </div>

                            <!-- Preview Container (New Selection) -->
                            <div id="foto_preview_wrapper" class="hidden mt-3 p-3 bg-[var(--bg-surface)] border-2 border-[var(--border-color)] rounded-2xl shadow-[2px_2px_0px_0px_var(--border-color)] flex items-center justify-between">
                                <div class="flex items-center gap-3 min-w-0">
                                    <img id="foto_preview_img" src="#" alt="Preview Baru" class="w-12 h-12 rounded-xl object-cover border-2 border-[var(--border-color)] flex-shrink-0 shadow-[2px_2px_0px_0px_var(--border-color)]">
                                    <div class="min-w-0">
                                        <p id="foto_preview_name" class="text-xs font-bold text-[var(--text-main)] truncate"></p>
                                        <p id="foto_preview_size" class="text-[10px] text-[var(--text-muted)]"></p>
                                    </div>
                                </div>
                                <button type="button" onclick="hapusFotoUtama()" class="text-xs font-extrabold text-[#DC2626] hover:bg-[#fee2e2] px-3 py-1.5 rounded-full border border-[#DC2626] transition-colors">
                                    Batal Ganti
                                </button>
                            </div>

                            <p class="text-xs text-[var(--text-muted)] mt-1.5">Kosongkan jika fotonya tetap. Format: JPG, PNG, atau WEBP. Maksimal 2MB.</p>
                            @error('foto_utama') <p class="text-[#ea4335] text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-[var(--text-main)] mb-1.5">Status Ketersediaan</label>
                        <div class="flex gap-4">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" name="stok" value="tersedia" {{ old('stok', $produk->stok) === 'tersedia' ? 'checked' : '' }} class="w-4 h-4 accent-[var(--color-primary)] border-[var(--border-color)]">
                                <span class="text-sm text-[var(--text-main)]">Ready (Tersedia)</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" name="stok" value="po" {{ old('stok', $produk->stok) === 'po' ? 'checked' : '' }} class="w-4 h-4 accent-[var(--color-primary)] border-[var(--border-color)]">
                                <span class="text-sm text-[var(--text-main)]">Pre-Order</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" name="stok" value="habis" {{ old('stok', $produk->stok) === 'habis' ? 'checked' : '' }} class="w-4 h-4 accent-[var(--color-primary)] border-[var(--border-color)]">
                                <span class="text-sm text-[var(--text-main)]">Habis</span>
                            </label>
                        </div>
                        @error('stok') <p class="text-[#ea4335] text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </form>
            </div>
        </div>

        <div class="space-y-6">
            <div class="admin-card">
                <div class="admin-card-header bg-[var(--cream-2)] border-b-[var(--border-color)]">
                    <h3 class="text-sm font-extrabold text-[var(--ink)]">Aksi Perubahan</h3>
                </div>
                <div class="p-5">
                    <p class="text-xs text-[var(--text-muted)] mb-5 leading-relaxed">
                        Perubahan akan diperiksa Admin terlebih dahulu. Informasi produk yang tampil tetap seperti saat ini sampai perubahan disetujui.
                    </p>
                    <button type="button" onclick="document.getElementById('produkForm').submit()" class="w-full btn-primary justify-center py-2.5">
                        Ajukan Perubahan
                    </button>
                    <div class="mt-3 text-center">
                        <a href="{{ route('penjual.produk.index') }}" class="text-xs text-[var(--ink-soft)] hover:text-[var(--brand)] hover:underline font-bold">Batal</a>
                    </div>
                </div>
            </div>

            <div class="admin-card">
                <div class="admin-card-header bg-[var(--cream-2)] border-b-[var(--border-color)]">
                    <h3 class="text-sm font-extrabold text-[var(--ink)]">Performa Produk</h3>
                </div>
                <div class="p-5 text-center">
                    <div class="w-16 h-16 bg-[var(--cream-2)] border-2 border-[var(--border-color)] shadow-[2px_2px_0px_0px_var(--border-color)] rounded-full mx-auto flex items-center justify-center text-[var(--color-primary)] mb-3">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                    </div>
                    <p class="text-sm text-[var(--text-muted)]">Produk ini sudah dilihat</p>
                    <p class="text-3xl font-display font-black text-[var(--text-main)] my-1">{{ number_format($produk->views) }}</p>
                    <p class="text-xs text-[var(--text-muted)]">kali oleh pengunjung</p>

                    <a href="{{ route('penjual.laporan') }}" class="inline-block mt-4 text-xs font-bold text-[var(--ink)] hover:text-[var(--brand)] hover:underline">
                        Lihat Laporan Toko →
                    </a>
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
                label.classList.remove('text-[var(--text-muted)]');
                label.classList.add('text-[var(--text-main)]', 'font-bold');

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
            label.textContent = 'Pilih file gambar jika ingin mengganti';
            label.classList.add('text-[var(--text-muted)]');
            label.classList.remove('text-[var(--text-main)]', 'font-bold');

            if (previewWrapper) {
                previewWrapper.classList.add('hidden');
            }
            if (previewImg) {
                previewImg.src = '#';
            }
        }
    </script>
@endsection

