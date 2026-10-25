@if ($paginator->hasPages())
    <nav role="navigation" aria-label="{{ __('Pagination Navigation') }}" class="flex items-center justify-between">
        <div class="flex justify-between flex-1 sm:hidden">
            @if ($paginator->onFirstPage())
                <span class="relative inline-flex items-center px-4 py-2 text-sm font-bold text-[var(--ink-soft)] bg-[var(--white-warm)] border-2 border-[var(--ink)]/30 rounded-full cursor-not-allowed opacity-50">
                    {!! __('pagination.previous') !!}
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" class="relative inline-flex items-center px-4 py-2 text-sm font-bold text-[var(--ink)] bg-[var(--white-warm)] border-2 border-[var(--ink)] rounded-full shadow-[2px_2px_0_var(--ink)] hover:-translate-y-0.5 transition-all">
                    {!! __('pagination.previous') !!}
                </a>
            @endif

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" class="relative inline-flex items-center px-4 py-2 ml-3 text-sm font-bold text-[var(--ink)] bg-[var(--white-warm)] border-2 border-[var(--ink)] rounded-full shadow-[2px_2px_0_var(--ink)] hover:-translate-y-0.5 transition-all">
                    {!! __('pagination.next') !!}
                </a>
            @else
                <span class="relative inline-flex items-center px-4 py-2 ml-3 text-sm font-bold text-[var(--ink-soft)] bg-[var(--white-warm)] border-2 border-[var(--ink)]/30 rounded-full cursor-not-allowed opacity-50">
                    {!! __('pagination.next') !!}
                </span>
            @endif
        </div>

        <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
            <div>
                <p class="text-sm font-bold text-[var(--ink-soft)]">
                    {!! __('Showing') !!}
                    @if ($paginator->firstItem())
                        <span class="font-extrabold text-[var(--ink)]">{{ $paginator->firstItem() }}</span>
                        {!! __('to') !!}
                        <span class="font-extrabold text-[var(--ink)]">{{ $paginator->lastItem() }}</span>
                    @else
                        {{ $paginator->count() }}
                    @endif
                    {!! __('of') !!}
                    <span class="font-extrabold text-[var(--ink)]">{{ $paginator->total() }}</span>
                    {!! __('results') !!}
                </p>
            </div>

            <div>
                <div class="inline-flex items-center gap-2">
                    {{-- Previous Page Link --}}
                    @if ($paginator->onFirstPage())
                        <span aria-disabled="true" aria-label="{{ __('pagination.previous') }}" class="w-11 h-11 inline-flex items-center justify-center rounded-full border-2 border-[var(--ink)]/30 bg-neutral-100 text-neutral-400 cursor-not-allowed opacity-50">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                            </svg>
                        </span>
                    @else
                        <a href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="{{ __('pagination.previous') }}" class="w-11 h-11 inline-flex items-center justify-center rounded-full border-2 border-[var(--ink)] bg-[var(--white-warm)] text-[var(--ink)] shadow-[2px_2px_0_var(--ink)] hover:-translate-y-0.5 active:translate-y-0 transition-all font-bold">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                            </svg>
                        </a>
                    @endif

                    {{-- Pagination Elements --}}
                    @foreach ($elements as $element)
                        {{-- "Three Dots" Separator --}}
                        @if (is_string($element))
                            <span aria-disabled="true" class="w-8 h-11 inline-flex items-center justify-center text-[var(--ink-soft)] font-black text-sm select-none">
                                {{ $element }}
                            </span>
                        @endif

                        {{-- Array Of Links --}}
                        @if (is_array($element))
                            @foreach ($element as $page => $url)
                                @if ($page == $paginator->currentPage())
                                    <span aria-current="page" class="w-11 h-11 inline-flex items-center justify-center rounded-full border-2 border-[var(--ink)] bg-[var(--brand)] text-[var(--ink)] shadow-[2px_2px_0_var(--ink)] font-black text-sm select-none">
                                        {{ $page }}
                                    </span>
                                @else
                                    <a href="{{ $url }}" class="w-11 h-11 inline-flex items-center justify-center rounded-full border-2 border-[var(--ink)] bg-[var(--white-warm)] text-[var(--ink)] shadow-[2px_2px_0_var(--ink)] hover:-translate-y-0.5 active:translate-y-0 transition-all font-bold text-sm" aria-label="{{ __('Go to page :page', ['page' => $page]) }}">
                                        {{ $page }}
                                    </a>
                                @endif
                            @endforeach
                        @endif
                    @endforeach

                    {{-- Next Page Link --}}
                    @if ($paginator->hasMorePages())
                        <a href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="{{ __('pagination.next') }}" class="w-11 h-11 inline-flex items-center justify-center rounded-full border-2 border-[var(--ink)] bg-[var(--white-warm)] text-[var(--ink)] shadow-[2px_2px_0_var(--ink)] hover:-translate-y-0.5 active:translate-y-0 transition-all font-bold">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                            </svg>
                        </a>
                    @else
                        <span aria-disabled="true" aria-label="{{ __('pagination.next') }}" class="w-11 h-11 inline-flex items-center justify-center rounded-full border-2 border-[var(--ink)]/30 bg-neutral-100 text-neutral-400 cursor-not-allowed opacity-50">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                            </svg>
                        </span>
                    @endif
                </div>
            </div>
        </div>
    </nav>
@endif
