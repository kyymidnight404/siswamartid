@extends('layouts.penjual')

@section('title', 'Katalog Saya')
@section('page-title', 'Katalog Saya')

@section('content')

    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-[var(--text-main)]">Produk Saya</h2>
            <p class="text-sm text-[var(--text-muted)] mt-1">Atur produk yang kamu jual di SiswaMart.</p>
        </div>
        <a href="{{ route('penjual.produk.create') }}" class="btn-primary shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Tambah Produk
        </a>
    </div>

    {{-- Product status filters --}}
    @php
        $productTabs = [
            ['label' => 'Semua', 'status' => null, 'count' => (int) $statusCounts->get('all', 0)],
            ['label' => 'Aktif', 'status' => 'aktif', 'count' => (int) $statusCounts->get('aktif', 0)],
            ['label' => 'Menunggu Moderasi', 'status' => 'pending', 'count' => (int) $statusCounts->get('pending', 0)],
            ['label' => 'Ditolak', 'status' => 'ditolak', 'count' => (int) $statusCounts->get('ditolak', 0)],
        ];
    @endphp
    <nav aria-label="Filter status produk" class="seller-product-filter mb-4 flex flex-wrap gap-2 rounded-[24px] border-2 border-[var(--ink)] bg-[var(--white-warm)] p-2 shadow-[3px_3px_0_var(--ink)]">
        @foreach($productTabs as $tab)
            @php($isActive = $status === $tab['status'])
            <a href="{{ route('penjual.produk.index', array_filter(['status' => $tab['status'], 'search' => request('search'), 'kategori_id' => request('kategori_id'), 'stok' => request('stok'), 'per_page' => request('per_page')])) }}"
               @if($isActive) aria-current="page" @endif
               class="seller-product-filter__link inline-flex min-h-11 items-center justify-center gap-3 rounded-full border-2 px-4 py-2 text-sm font-bold transition duration-150 hover:-translate-y-0.5 focus-visible:outline-none {{ $isActive ? 'border-[var(--ink)] bg-[var(--brand)] text-[var(--ink)]' : 'border-transparent bg-transparent text-[var(--ink-soft)] hover:border-[var(--ink)] hover:bg-[var(--cream-2)]' }}">
                <span>{{ $tab['label'] }}</span>
                @if($tab['count'] > 0 || in_array($tab['status'], [null, 'aktif'], true))
                    <span class="inline-flex h-7 min-w-7 items-center justify-center rounded-full border border-[var(--ink)] px-2 text-xs {{ $isActive ? 'bg-[var(--white-warm)] text-[var(--ink)]' : 'bg-[var(--cream-2)] text-[var(--ink)]' }}">{{ $tab['count'] }}</span>
                @endif
            </a>
        @endforeach
    </nav>

    {{-- Search & Filters Toolbar --}}
    <div class="mb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <form method="GET" action="{{ route('penjual.produk.index') }}" class="flex flex-wrap items-center gap-3 w-full md:w-auto">
            @if($status)
                <input type="hidden" name="status" value="{{ $status }}">
            @endif
            <input type="hidden" name="per_page" value="{{ request('per_page', 10) }}">

            {{-- Search Input --}}
            <div class="relative min-w-[240px] flex-1 sm:flex-initial">
                <svg class="w-4 h-4 absolute left-3.5 top-1/2 -translate-y-1/2 text-[var(--text-muted)]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <input type="text"
                       name="search"
                       value="{{ request('search') }}"
                       placeholder="Cari produk saya..."
                       class="form-input !h-10 !text-xs !pl-10 !py-2 w-full">
            </div>

            {{-- Kategori Filter --}}
            <select name="kategori_id" onchange="this.form.submit()" class="form-input !h-10 !text-xs !py-2 !w-auto font-bold bg-[var(--bg-surface)]">
                <option value="">Semua Kategori</option>
                @foreach($kategoris as $kat)
                    <option value="{{ $kat->id }}" {{ (string) request('kategori_id') === (string) $kat->id ? 'selected' : '' }}>
                        {{ $kat->nama }}
                    </option>
                @endforeach
            </select>

            {{-- Stok Filter --}}
            <select name="stok" onchange="this.form.submit()" class="form-input !h-10 !text-xs !py-2 !w-auto font-bold bg-[var(--bg-surface)]">
                <option value="">Semua Stok</option>
                <option value="ready" {{ in_array(request('stok'), ['ready', 'tersedia'], true) ? 'selected' : '' }}>Ready (Tersedia)</option>
                <option value="habis" {{ request('stok') === 'habis' ? 'selected' : '' }}>Habis</option>
                <option value="po" {{ request('stok') === 'po' ? 'selected' : '' }}>Pre-Order</option>
            </select>

            <button type="submit" class="btn-primary !h-10 !text-xs !py-2 !px-4">
                Cari
            </button>

            @if(request('search') || request('kategori_id') || request('stok'))
                <a href="{{ route('penjual.produk.index', array_filter(['status' => $status, 'per_page' => request('per_page', 10)])) }}" class="btn-outline !h-10 !text-xs !py-2 !px-3" title="Reset Filter">
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
                        <th class="px-5 py-4 text-xs font-semibold text-[var(--text-muted)] uppercase tracking-wider">Info Produk</th>
                        <th class="px-5 py-4 text-xs font-semibold text-[var(--text-muted)] uppercase tracking-wider">Harga & Stok</th>
                        <th class="px-5 py-4 text-xs font-semibold text-[var(--text-muted)] uppercase tracking-wider">Dilihat</th>
                        <th class="px-5 py-4 text-xs font-semibold text-[var(--text-muted)] uppercase tracking-wider">Status</th>
                        <th class="px-5 py-4 text-xs font-semibold text-[var(--text-muted)] uppercase tracking-wider text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[var(--border-color)]">
                    @forelse($produk as $p)
                    <tr class="hover:bg-[var(--bg-hover)] transition-colors">
                        <td class="px-5 py-4">
                            <div class="flex items-center gap-4">
                                @if($p->foto_utama)
                                    <img src="{{ asset('storage/' . $p->foto_utama) }}" class="w-12 h-12 rounded-lg object-cover border border-[var(--border-color)]" alt="{{ $p->nama }}">
                                @else
                                    <div class="w-12 h-12 rounded-lg bg-[var(--bg-hover)] border border-[var(--border-color)] flex items-center justify-center text-[var(--text-muted)]">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                    </div>
                                @endif
                                <div>
                                    <div class="font-semibold text-[var(--text-main)]">{{ $p->nama }}</div>
                                    <div class="text-[10px] bg-[var(--color-primary)] text-[var(--border-color)] border border-[var(--border-color)] shadow-[1px_1px_0px_0px_var(--border-color)] font-bold px-2 py-0.5 rounded-full inline-block mt-1">{{ $p->kategori->nama }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-5 py-4">
                            <div class="font-medium text-[var(--text-main)]">Rp {{ number_format($p->harga, 0, ',', '.') }}</div>
                            <div class="text-xs text-[var(--text-muted)] mt-0.5">{{ $p->stok === 'po' ? 'Pre-Order' : ucfirst($p->stok) }}</div>
                        </td>
                        <td class="px-5 py-4">
                            <div class="font-bold text-[var(--text-main)]">{{ number_format($p->views) }} <span class="font-normal text-[var(--text-muted)] text-[10px]">kali</span></div>
                        </td>
                        <td class="px-5 py-4">
                            <span class="badge badge-{{ $p->pending_action ? 'pending' : ($p->status === 'aktif' ? 'aktif' : ($p->status === 'pending' ? 'pending' : ($p->status === 'ditolak' ? 'ditolak' : 'draft'))) }}">
                                @if($p->pending_action === 'create') Menunggu persetujuan
                                @elseif($p->pending_action === 'update') Menunggu persetujuan perubahan
                                @elseif($p->pending_action === 'delete') Menunggu persetujuan hapus
                                @else {{ ucfirst($p->status) }} @endif
                            </span>
                            @if($p->alasan_ditolak && !$p->pending_action)
                                <div class="text-[10px] text-[#ea4335] mt-1 line-clamp-1" title="{{ $p->alasan_ditolak }}">Alasan: {{ $p->alasan_ditolak }}</div>
                            @endif
                        </td>
                        <td class="px-5 py-4">
                            <div class="flex items-center justify-end gap-2">
                                @if(!$p->pending_action)
                                    <a href="{{ route('penjual.produk.edit', $p) }}" class="btn-outline text-xs px-3 py-1.5">Edit</a>

                                    <form method="POST" action="{{ route('penjual.produk.destroy', $p) }}" onsubmit="return confirm('Kirim permintaan penghapusan produk ini kepada Admin? Produk baru dihapus setelah disetujui.')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn-outline text-xs px-3 py-1.5 text-[#ea4335] border-[#ea4335] hover:bg-[#fce8e6]">Minta Hapus</button>
                                    </form>
                                @else
                                    <span class="text-xs font-semibold text-[var(--ink-soft)]">Menunggu Admin</span>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-5 py-12 text-center text-[var(--text-muted)]">
                            Belum ada produk yang sesuai pencarian / filter.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <x-admin.table-pagination :paginator="$produk" />
    </div>

@endsection
