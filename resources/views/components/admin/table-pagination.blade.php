@props(['paginator'])

@if($paginator && $paginator->total() > 0)
    <div class="p-4 border-t-2 border-[var(--sm-border)] bg-[var(--sm-surface)] flex flex-col md:flex-row md:items-center md:justify-between gap-4 text-xs font-semibold text-[var(--sm-text-muted)]">
        {{-- Total info --}}
        <div class="flex items-center gap-2">
            <span>
                Menampilkan <strong class="text-[var(--sm-ink)] font-black">{{ $paginator->firstItem() ?? 0 }}</strong> – <strong class="text-[var(--sm-ink)] font-black">{{ $paginator->lastItem() ?? 0 }}</strong> dari <strong class="text-[var(--sm-ink)] font-black">{{ number_format($paginator->total()) }}</strong> data
            </span>
        </div>

        {{-- Controls --}}
        <div class="flex flex-wrap items-center gap-3 sm:gap-4">
            {{-- Paging 10 - 25 - 50 - Semua --}}
            <div class="flex items-center gap-1.5">
                <span class="text-[11px] uppercase tracking-wider font-bold">Baris:</span>
                <select onchange="window.location.href = this.value" 
                        aria-label="Pilih jumlah baris per halaman"
                        class="form-input !h-8 !py-1 !px-2.5 !text-xs !w-auto font-bold bg-[var(--sm-bg)] border-2 border-[var(--sm-border)] rounded-xl cursor-pointer">
                    <option value="{{ request()->fullUrlWithQuery(['per_page' => 10, 'page' => 1]) }}" {{ request('per_page', 10) == 10 ? 'selected' : '' }}>10</option>
                    <option value="{{ request()->fullUrlWithQuery(['per_page' => 25, 'page' => 1]) }}" {{ request('per_page') == 25 ? 'selected' : '' }}>25</option>
                    <option value="{{ request()->fullUrlWithQuery(['per_page' => 50, 'page' => 1]) }}" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                    <option value="{{ request()->fullUrlWithQuery(['per_page' => 'all', 'page' => 1]) }}" {{ in_array(request('per_page'), ['all', 'semua'], true) ? 'selected' : '' }}>Semua</option>
                </select>
            </div>

            {{-- Dropdown Lompat Halaman --}}
            <div class="flex items-center gap-1.5">
                <span class="text-[11px] uppercase tracking-wider font-bold">Halaman:</span>
                <select onchange="window.location.href = this.value"
                        aria-label="Lompat ke halaman"
                        class="form-input !h-8 !py-1 !px-2.5 !text-xs !w-auto font-bold bg-[var(--sm-bg)] border-2 border-[var(--sm-border)] rounded-xl cursor-pointer"
                        {{ $paginator->lastPage() <= 1 ? 'disabled' : '' }}>
                    @for($i = 1; $i <= max(1, $paginator->lastPage()); $i++)
                        <option value="{{ $paginator->url($i) }}" {{ $paginator->currentPage() == $i ? 'selected' : '' }}>
                            {{ $i }} dari {{ max(1, $paginator->lastPage()) }}
                        </option>
                    @endfor
                </select>
            </div>

            {{-- Prev / Next Nav Buttons --}}
            <div class="flex items-center gap-1">
                @if($paginator->onFirstPage())
                    <span class="pagination-icon-btn disabled" aria-disabled="true" title="Halaman sebelumnya">
                        <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/></svg>
                    </span>
                @else
                    <a href="{{ $paginator->previousPageUrl() }}" class="pagination-icon-btn" title="Halaman sebelumnya">
                        <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/></svg>
                    </a>
                @endif

                @if($paginator->hasMorePages())
                    <a href="{{ $paginator->nextPageUrl() }}" class="pagination-icon-btn" title="Halaman selanjutnya">
                        <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                    </a>
                @else
                    <span class="pagination-icon-btn disabled" aria-disabled="true" title="Halaman selanjutnya">
                        <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                    </span>
                @endif
            </div>
        </div>
    </div>
@endif
