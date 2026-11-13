@extends('layouts.admin')

@section('title', 'Kelola Komentar')
@section('page-title', 'Kelola Komentar')

@section('content')
<div class="space-y-5">
    <section class="admin-card p-5 sm:p-6">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <h2 class="text-xl font-black text-[var(--sm-ink)]">Ulasan Pengunjung</h2>
                <p class="mt-1 text-sm text-[var(--sm-text-muted)]">Lihat isi ulasan beserta produk dan penjualnya. Ulasan yang melanggar aturan ditolak otomatis saat dikirim.</p>
            </div>
            <span class="inline-flex w-fit items-center rounded-full border-2 border-[var(--sm-border)] bg-[var(--sm-surface)] px-4 py-2 text-sm font-bold">
                {{ number_format($komentarList->total()) }} ulasan
            </span>
        </div>
    </section>

    {{-- Search & Filter Toolbar --}}
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <form method="GET" action="{{ route('admin.komentar.index') }}" class="flex flex-wrap items-center gap-3 w-full md:w-auto">
            <input type="hidden" name="per_page" value="{{ request('per_page', 10) }}">

            {{-- Search Input --}}
            <div class="admin-search-field relative min-w-[240px] flex-1 sm:flex-initial">
                <svg class="w-4 h-4 absolute left-3.5 top-1/2 -translate-y-1/2 text-[var(--sm-text-muted)] pointer-events-none flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <input type="text"
                       name="search"
                       value="{{ request('search') }}"
                       placeholder="Cari ulasan, pengunjung, produk, toko..."
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

            {{-- Rating Filter --}}
            <select name="rating" onchange="this.form.submit()" class="form-input !h-10 !text-xs !py-2 !w-auto font-bold bg-[var(--sm-surface)]">
                <option value="">Semua Rating</option>
                @for($r = 5; $r >= 1; $r--)
                    <option value="{{ $r }}" {{ (string) request('rating') === (string) $r ? 'selected' : '' }}>
                        {{ $r }} Bintang
                    </option>
                @endfor
            </select>

            <button type="submit" class="btn-primary !h-10 !text-xs !py-2 !px-4">
                Cari
            </button>

            @if(request('search') || request('kategori_id') || request('rating'))
                <a href="{{ route('admin.komentar.index', ['per_page' => request('per_page', 10)]) }}" class="btn-outline !h-10 !text-xs !py-2 !px-3" title="Reset Filter">
                    Reset
                </a>
            @endif
        </form>
    </div>

    @if(session('success'))
        <div class="admin-card border-2 border-[var(--sm-border)] bg-[var(--sm-primary-soft)] px-4 py-3 text-sm font-semibold text-[var(--sm-ink)]" role="status">
            {{ session('success') }}
        </div>
    @endif

    <section class="space-y-4" aria-label="Daftar ulasan">
        @forelse($komentarList as $ulasan)
            <article class="admin-card p-4 sm:p-5">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                    <div class="min-w-0 flex-1 space-y-3">
                        <div class="flex flex-wrap items-center gap-x-3 gap-y-1">
                            <h3 class="font-black text-[var(--sm-ink)]">{{ $ulasan->guest_username ?: 'Pengunjung' }}</h3>
                            <span class="inline-flex items-center gap-1 rounded-full border border-[var(--sm-border)] bg-[var(--sm-primary-soft)] px-2.5 py-1 text-xs font-bold text-[var(--sm-ink)]" aria-label="Rating {{ $ulasan->rating }} dari 5">
                                <svg class="w-3.5 h-3.5 text-amber-500 fill-current inline-block flex-shrink-0" viewBox="0 0 20 20" aria-hidden="true"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                <span>{{ $ulasan->rating }}/5</span>
                            </span>
                            @if($ulasan->produk?->kategori)
                                <span class="text-[10px] bg-[var(--sm-primary)] text-[var(--sm-border)] border border-[var(--sm-border)] font-bold px-2 py-0.5 rounded-full inline-block">
                                    {{ $ulasan->produk->kategori->nama }}
                                </span>
                            @endif
                            <time class="text-xs text-[var(--sm-text-muted)]" datetime="{{ $ulasan->created_at?->toIso8601String() }}">
                                {{ $ulasan->created_at?->timezone(config('app.timezone'))->format('d M Y, H:i') ?? 'Waktu tidak tersedia' }}
                            </time>
                        </div>

                        <p class="whitespace-pre-line break-words text-sm leading-relaxed text-[var(--sm-ink)]">{{ $ulasan->public_content ?: 'Tidak ada teks ulasan.' }}</p>

                        <div class="flex flex-wrap gap-x-5 gap-y-1 border-t border-[var(--sm-border)]/20 pt-3 text-xs text-[var(--sm-text-muted)]">
                            <span><strong class="text-[var(--sm-ink)]">Produk:</strong> {{ $ulasan->produk?->nama ?? 'Produk telah dihapus' }}</span>
                            <span><strong class="text-[var(--sm-ink)]">Penjual:</strong> {{ $ulasan->produk?->penjual?->nama_toko ?? 'Penjual tidak tersedia' }}</span>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('admin.komentar.destroy', $ulasan) }}" onsubmit="return confirm('Hapus ulasan ini?')" class="shrink-0">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="inline-flex min-h-11 w-full items-center justify-center rounded-full border-2 border-[var(--sm-border)] bg-[var(--sm-primary-soft)] px-4 py-2 text-sm font-extrabold text-[var(--sm-ink)] shadow-[3px_3px_0_var(--sm-border)] transition hover:-translate-y-0.5 hover:shadow-[5px_5px_0_var(--sm-border)] sm:w-auto" aria-label="Hapus ulasan dari {{ $ulasan->guest_username ?: 'Pengunjung' }}">
                            Hapus ulasan
                        </button>
                    </form>
                </div>
            </article>
        @empty
            <div class="admin-card px-5 py-12 text-center">
                <h3 class="font-bold text-[var(--sm-ink)]">Belum ada ulasan yang sesuai pencarian / filter</h3>
                <p class="mt-1 text-sm text-[var(--sm-text-muted)]">Ulasan yang lolos pemeriksaan otomatis akan tampil di sini.</p>
            </div>
        @endforelse
    </section>

    <div class="admin-card overflow-hidden">
        <x-admin.table-pagination :paginator="$komentarList" />
    </div>
</div>
@endsection
