@extends('layouts.admin')

@section('title', 'Moderasi Produk')
@section('page-title', 'Moderasi Produk')

@section('content')

    <div class="mb-6">
        <h2 class="text-xl font-bold text-[var(--sm-ink)]">Moderasi Produk</h2>
        <p class="text-sm text-[var(--sm-text-muted)] mt-1">Cek dan setujui produk sebelum tampil di katalog.</p>
    </div>

    @php
        $moderationTabs = [
            ['label' => 'Semua', 'status' => null, 'count' => null],
            ['label' => 'Menunggu Moderasi', 'status' => 'pending', 'count' => (int) $pendingCount],
            ['label' => 'Aktif', 'status' => 'aktif', 'count' => null],
            ['label' => 'Ditolak', 'status' => 'ditolak', 'count' => null],
        ];
    @endphp
    <nav aria-label="Filter moderasi produk" class="mb-4 flex flex-wrap gap-2 rounded-[24px] border-2 border-[var(--sm-ink)] bg-[var(--sm-surface)] p-2 shadow-[3px_3px_0_var(--sm-ink)]">
        @foreach($moderationTabs as $tab)
            @php($isActive = $status === $tab['status'])
            <a href="{{ route('admin.produk.index', array_filter(['status' => $tab['status'], 'search' => request('search'), 'kategori_id' => request('kategori_id'), 'per_page' => request('per_page')])) }}"
               @if($isActive) aria-current="page" @endif
               class="inline-flex min-h-11 items-center justify-center gap-3 rounded-full border-2 px-4 py-2 text-sm font-bold transition duration-150 hover:-translate-y-0.5 focus-visible:outline-none {{ $isActive ? 'border-[var(--sm-ink)] bg-[var(--sm-primary)] text-[var(--sm-ink)]' : 'border-transparent bg-transparent text-[var(--sm-text-muted)] hover:border-[var(--sm-ink)] hover:bg-[var(--sm-primary-soft)]' }}">
                <span>{{ $tab['label'] }}</span>
                @if(($tab['count'] ?? 0) > 0)
                    <span class="inline-flex h-7 min-w-7 items-center justify-center rounded-full border border-[var(--sm-ink)] bg-[var(--sm-surface)] px-2 text-xs text-[var(--sm-ink)]">{{ $tab['count'] }}</span>
                @endif
            </a>
        @endforeach
    </nav>

    {{-- Search & Kategori Filter Toolbar --}}
    <div class="mb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <form method="GET" action="{{ route('admin.produk.index') }}" class="flex flex-wrap items-center gap-3 w-full md:w-auto">
            @if($status)
                <input type="hidden" name="status" value="{{ $status }}">
            @endif
            <input type="hidden" name="per_page" value="{{ request('per_page', 10) }}">

            {{-- Search Input --}}
            <div class="admin-search-field relative min-w-[240px] flex-1 sm:flex-initial">
                <svg class="w-4 h-4 absolute left-3.5 top-1/2 -translate-y-1/2 text-[var(--sm-text-muted)] pointer-events-none flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <input type="text"
                       name="search"
                       value="{{ request('search') }}"
                       placeholder="Cari produk, deskripsi, toko..."
                       class="form-input !h-10 !text-xs pl-12 !py-2 w-full">
            </div>

            {{-- Kategori Filter --}}
            <select name="kategori_id" onchange="this.form.submit()" class="form-input !h-10 !text-xs !py-2 !w-auto font-bold bg-[var(--sm-surface)]">
                <option value="">Semua Kategori</option>
                @foreach($kategoris as $kat)
                    <option value="{{ $kat->id }}" {{ (string) request('kategori_id') === (string) $kat->id ? 'selected' : '' }}>
                        {{ $kat->nama }}
                    </option>
                @endforeach
            </select>

            <button type="submit" class="btn-primary !h-10 !text-xs !py-2 !px-4">
                Cari
            </button>

            @if(request('search') || request('kategori_id'))
                <a href="{{ route('admin.produk.index', array_filter(['status' => $status, 'per_page' => request('per_page', 10)])) }}" class="btn-outline !h-10 !text-xs !py-2 !px-3" title="Reset Filter">
                    Reset
                </a>
            @endif
        </form>
    </div>

    <div class="admin-card">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-[var(--sm-primary-soft)] border-b border-[var(--sm-border)]">
                    <tr>
                        <th class="px-5 py-4 text-xs font-semibold text-[var(--sm-text-muted)] uppercase tracking-wider">Info Produk</th>
                        <th class="px-5 py-4 text-xs font-semibold text-[var(--sm-text-muted)] uppercase tracking-wider">Penjual</th>
                        <th class="px-5 py-4 text-xs font-semibold text-[var(--sm-text-muted)] uppercase tracking-wider">Harga</th>
                        <th class="px-5 py-4 text-xs font-semibold text-[var(--sm-text-muted)] uppercase tracking-wider">Status</th>
                        <th class="px-5 py-4 text-xs font-semibold text-[var(--sm-text-muted)] uppercase tracking-wider text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[var(--sm-border)]">
                    @forelse($produk as $p)
                    <tr class="hover:bg-[var(--sm-primary-soft)] transition-colors">
                        <td class="px-5 py-4">
                            <div class="flex items-center gap-4">
                                @if($p->foto_utama)
                                    <img src="{{ asset('storage/' . $p->foto_utama) }}" class="w-12 h-12 rounded-lg object-cover border border-[var(--sm-border)]" alt="{{ $p->nama }}">
                                @else
                                    <div class="w-12 h-12 rounded-lg bg-[var(--sm-primary-soft)] border border-[var(--sm-border)] flex items-center justify-center text-[var(--sm-text-muted)] flex-shrink-0">
                                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                    </div>
                                @endif
                                <div>
                                    <div class="font-semibold text-[var(--sm-ink)]">{{ $p->nama }}</div>
                                    <div class="text-[10px] bg-[var(--sm-primary)] text-[var(--sm-border)] border border-[var(--sm-border)] shadow-[1px_1px_0px_0px_var(--sm-border)] font-bold px-2 py-0.5 rounded-full inline-block mt-1">{{ $p->kategori->nama }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-5 py-4">
                            <div class="text-[var(--sm-ink)] font-medium text-sm">{{ $p->penjual->nama_toko }}</div>
                            <div class="text-xs text-[var(--sm-text-muted)]">{{ $p->penjual->lokasi_kelas }}</div>
                        </td>
                        <td class="px-5 py-4">
                            <div class="font-medium text-[var(--sm-ink)]">Rp {{ number_format($p->harga, 0, ',', '.') }}</div>
                            <div class="text-xs text-[var(--sm-text-muted)] mt-0.5">Stok: {{ ucfirst($p->stok) }}</div>
                        </td>
                        <td class="px-5 py-4">
                            <span class="badge badge-{{ $p->pending_action ? 'pending' : ($p->status === 'aktif' ? 'aktif' : ($p->status === 'pending' ? 'pending' : ($p->status === 'ditolak' ? 'ditolak' : 'draft'))) }}">
                                @if($p->pending_action === 'create') Pengajuan produk baru
                                @elseif($p->pending_action === 'update') Pengajuan perubahan
                                @elseif($p->pending_action === 'delete') Pengajuan penghapusan
                                @else {{ ucfirst($p->status) }} @endif
                            </span>
                        </td>
                        <td class="px-5 py-4">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('admin.produk.show', $p) }}" class="btn-outline text-xs px-3 py-1.5">Review</a>

                                @if($p->status === 'pending' && !$p->pending_action)
                                <form method="POST" action="{{ route('admin.produk.updateStatus', $p) }}" class="inline-block">
                                    @csrf @method('PATCH')
                                    <input type="hidden" name="status" value="aktif">
                                    <button type="submit" class="btn-primary text-xs px-3 py-1.5" style="background:var(--sm-primary)">Setujui</button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-5 py-12 text-center text-[var(--sm-text-muted)]">
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
