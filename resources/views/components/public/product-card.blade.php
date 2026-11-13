{{--
    Product Card — Public Catalog
    Storefront-style: foto dominan, typography bold, organic playful vibe.
--}}
@props(['produk', 'homepage' => false])

@if($homepage)
    @php
        $categorySlug = $produk->kategori->slug ?? '';
        $categoryIcons = [
            'makanan' => 'burger',
            'makanan-berat' => 'burger',
            'minuman' => 'glass-full',
            'snack' => 'cookie',
            'dessert' => 'cake',
        ];
        $categoryColors = [
            'makanan' => 'var(--sm-primary-soft)',
            'makanan-berat' => 'var(--sm-primary-soft)',
            'minuman' => 'var(--sm-primary-soft)',
            'snack' => 'var(--sm-primary-soft)',
            'dessert' => 'var(--sm-primary-soft)',
        ];
        $categoryIcon = $categoryIcons[$categorySlug] ?? 'burger';
        $categoryColor = $categoryColors[$categorySlug] ?? 'var(--sm-bg)';
        $homeAverageRating = $produk->ulasan_avg_rating ?? $produk->average_rating ?? 0;
        $homeBadges = [];
        if ($produk->stok === 'po') {
            $homeBadges[] = ['label' => 'PRE-ORDER', 'class' => 'home-product-card__badge--preorder'];
        } elseif ($produk->stok === 'tersedia') {
            $homeBadges[] = ['label' => 'TERSEDIA', 'class' => 'home-product-card__badge--available'];
        } elseif ($produk->stok === 'habis') {
            $homeBadges[] = ['label' => 'HABIS', 'class' => 'home-product-card__badge--unavailable'];
        }

        $promotionLabel = trim((string) $produk->label_promosi);
        $stockLabel = $homeBadges[0]['label'] ?? null;
        $normalizedPromotionLabel = strtoupper(str_replace([' ', '-'], '', $promotionLabel));
        $normalizedStockLabel = strtoupper(str_replace([' ', '-'], '', (string) $stockLabel));
        if ($promotionLabel !== '' && $normalizedPromotionLabel !== $normalizedStockLabel && count($homeBadges) < 2) {
            $promotionClass = in_array(strtoupper($promotionLabel), ['BEST SELLER', 'TERLARIS', 'HITS', 'FAVORIT'], true)
                ? 'home-product-card__badge--favorite'
                : 'home-product-card__badge--promo';
            $homeBadges[] = ['label' => $promotionLabel, 'class' => $promotionClass];
        } elseif (count($homeBadges) < 2) {
            if ($produk->views >= 500) {
                $homeBadges[] = ['label' => 'BEST SELLER', 'class' => 'home-product-card__badge--favorite'];
            } elseif ($produk->views >= 100) {
                $homeBadges[] = ['label' => 'HITS', 'class' => 'home-product-card__badge--promo'];
            } elseif ($produk->views >= 50) {
                $homeBadges[] = ['label' => 'FAVORIT', 'class' => 'home-product-card__badge--favorite'];
            } elseif ($produk->created_at && $produk->created_at->diffInDays(now()) <= 1) {
                $homeBadges[] = ['label' => 'BARU', 'class' => 'home-product-card__badge--promo'];
            }
        }
    @endphp

    <a href="{{ route('public.produk.show', $produk->slug) }}"
       id="produk-card-{{ $produk->id }}"
       class="product-card home-product-card group"
       aria-label="Lihat detail {{ $produk->nama }}">
        <div class="home-product-card__media">
            @if($produk->foto_utama)
                <img src="{{ asset('storage/' . $produk->foto_utama) }}"
                     alt="{{ $produk->nama }}"
                     width="640"
                     height="480"
                     loading="lazy"
                     onerror="this.onerror=null;this.hidden=true;this.parentElement.querySelector('[data-product-fallback]').hidden=false;">
                <div data-product-fallback hidden class="home-product-card__fallback" style="--product-placeholder: {{ $categoryColor }}" role="img" aria-label="Ilustrasi kategori {{ $produk->kategori->nama ?? 'produk' }}">
                    <span class="home-product-card__fallback-icon" aria-hidden="true" style="--icon-fill: {{ $categoryColor }}"><x-public.home-icon :name="$categoryIcon" :size="60" :stroke-width="1.5" /></span>
                </div>
            @else
                <div data-product-fallback class="home-product-card__fallback" style="--product-placeholder: {{ $categoryColor }}" role="img" aria-label="Ilustrasi kategori {{ $produk->kategori->nama ?? 'produk' }}">
                    <span class="home-product-card__fallback-icon" aria-hidden="true" style="--icon-fill: {{ $categoryColor }}"><x-public.home-icon :name="$categoryIcon" :size="60" :stroke-width="1.5" /></span>
                </div>
            @endif

            @if($homeBadges)
                <div class="home-product-card__badges" aria-label="Status produk">
                    @foreach(array_slice($homeBadges, 0, 2) as $badge)
                        <span class="home-product-card__badge {{ $badge['class'] }}">{{ $badge['label'] }}</span>
                    @endforeach
                </div>
            @endif
        </div>

        <div class="home-product-card__info">
            <h3 class="home-product-card__title">{{ $produk->nama }}</h3>
            <p class="home-product-card__seller">
                <x-public.home-icon name="building-store" class="home-product-card__seller-icon" aria-hidden="true" />
                <span>{{ $produk->penjual->nama_toko ?? 'Penjual' }}</span>
                @if(($produk->penjual->status_toko ?? 'buka') === 'tutup')
                    <span class="inline-block text-[9px] font-black text-rose-700 bg-rose-50 border border-rose-200 px-1.5 py-0.5 rounded-full uppercase tracking-wider ml-1">Tutup</span>
                @endif
            </p>

            @if($produk->kategori)
                <span class="home-product-card__category">{{ $produk->kategori->nama }}</span>
            @endif

            <div class="home-product-card__meta">
                <span class="home-product-card__price">Rp {{ number_format($produk->harga, 0, ',', '.') }}</span>
                <div class="home-product-card__stats">
                    @if((float) $homeAverageRating > 0)
                        <span class="home-product-card__rating" aria-label="Rating {{ number_format($homeAverageRating, 1) }} dari 5">
                            <x-public.home-icon name="star" class="home-product-card__rating-icon" :filled="true" aria-hidden="true" />
                            {{ number_format($homeAverageRating, 1) }}
                        </span>
                    @endif
                    <span class="home-product-card__views" aria-label="{{ number_format($produk->views ?? 0, 0, ',', '.') }} kali dilihat">
                        <x-public.home-icon name="eye" class="home-product-card__views-icon" aria-hidden="true" />
                        {{ number_format($produk->views ?? 0, 0, ',', '.') }}
                    </span>
                </div>
            </div>
        </div>
    </a>
@else

@once
    <style>
        .product-card-grid,
        .category-product-grid {
            grid-auto-rows: 27.5rem;
        }

        .product-card-grid > .product-card,
        .category-product-grid > .product-card {
            display: flex;
            flex-direction: column;
            height: 100%;
            min-height: 0;
        }

        .product-card-grid .product-card__media,
        .category-product-grid .product-card__media {
            width: 100%;
            flex: 0 0 52%;
            aspect-ratio: auto;
            border-right: 0;
            border-bottom: 2px solid var(--sm-border);
        }

        .product-card-grid .product-card__info,
        .category-product-grid .product-card__info {
            flex: 1;
            min-height: 0;
        }

        @media (min-width: 1024px) {
            .category-product-grid {
                grid-template-columns: repeat(3, minmax(0, 1fr));
            }
        }

        @media (max-width: 767px) {
            .product-card-grid,
            .category-product-grid {
                grid-auto-rows: 12.25rem;
            }

            .product-card-grid > .product-card,
            .category-product-grid > .product-card {
                flex-direction: row;
            }

            .product-card-grid .product-card__media,
            .category-product-grid .product-card__media {
                width: 8rem;
                flex: 0 0 8rem;
                height: 100%;
                aspect-ratio: 1;
                border-right: 2px solid var(--sm-border);
                border-bottom: 0;
            }
        }
    </style>
@endonce

<a href="{{ route('public.produk.show', $produk->slug) }}"
   id="produk-card-{{ $produk->id }}"
   class="product-card group flex h-full min-h-36 flex-row focus:outline-none bg-[var(--sm-surface)] border-2 border-[var(--sm-border)] shadow-solid-sm hover:shadow-solid-md hover:-translate-y-1 transition-all duration-200 rounded-[24px] overflow-hidden md:flex-col"
   aria-label="Lihat detail {{ $produk->nama }}">

    {{-- ── Foto Produk ──────────────────────────────────────── --}}
    @php
        $slug = $produk->kategori->slug ?? '';
        $fallbackIcons = ['makanan' => 'burger', 'makanan-berat' => 'burger', 'minuman' => 'glass-full', 'snack' => 'cookie', 'dessert' => 'cake'];
        $fallbackTints = ['makanan' => 'var(--sm-primary-soft)', 'makanan-berat' => 'var(--sm-primary-soft)', 'minuman' => 'var(--sm-primary-soft)', 'snack' => 'var(--sm-primary-soft)', 'dessert' => 'var(--sm-primary-soft)'];
        $fallbackIcon = $fallbackIcons[$slug] ?? 'package';
        $fallbackTint = $fallbackTints[$slug] ?? 'var(--sm-primary-soft)';
    @endphp
    <div class="product-card__media relative aspect-square w-32 shrink-0 overflow-hidden bg-white border-r-2 border-[var(--sm-border)] md:w-full md:border-r-0 md:border-b-2">
        @if($produk->foto_utama)
            <img src="{{ asset('storage/' . $produk->foto_utama) }}"
                 alt="{{ $produk->nama }}"
                 class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300 ease-out"
                 loading="lazy"
                 onerror="this.onerror=null;this.hidden=true;this.parentElement.querySelector('[data-product-fallback]').hidden=false;">
        @endif
        <div data-product-fallback @if($produk->foto_utama) hidden @endif class="product-card__fallback img-placeholder w-full h-full flex items-center justify-center" style="--product-placeholder: {{ $fallbackTint }}" role="img" aria-label="Ilustrasi kategori {{ $produk->kategori->nama ?? 'produk' }}">
            <span class="product-card__fallback-icon" aria-hidden="true"><x-public.home-icon :name="$fallbackIcon" :size="60" :stroke-width="1.5" /></span>
        </div>

        {{-- Status / Promo Badges (Top Left) --}}
        <div class="absolute top-3 left-3 flex flex-col gap-1.5 z-10">
            @php
                $badge = null;
                $labelPromosi = $produk->label_promosi ? strtoupper(trim($produk->label_promosi)) : '';
                $isLabelPromoPerformance = in_array($labelPromosi, ['BEST SELLER', 'TERLARIS', 'HITS', 'FAVORIT', 'BARU', 'PRE-ORDER', 'PRE ORDER']);

                if ($produk->stok === 'habis') {
                    $badge = ['text' => 'HABIS', 'bg' => 'bg-[var(--sm-ink)]', 'textCol' => 'text-white'];
                } elseif ($produk->stok === 'po') {
                    $badge = ['text' => 'PRE-ORDER', 'bg' => 'bg-[var(--sm-primary-soft)]', 'textCol' => 'text-[var(--sm-ink)]'];
                } elseif ($produk->views >= 500) {
                    $badge = ['text' => 'BEST SELLER', 'bg' => 'bg-[var(--sm-ink)]', 'textCol' => 'text-white'];
                } elseif ($produk->views >= 100) {
                    $badge = ['text' => 'HITS', 'bg' => 'bg-[var(--sm-primary-soft)]', 'textCol' => 'text-[var(--sm-ink)]'];
                } elseif ($produk->views >= 50) {
                    $badge = ['text' => 'FAVORIT', 'bg' => 'bg-[var(--sm-primary)]', 'textCol' => 'text-white'];
                } elseif ($produk->created_at && $produk->created_at->diffInDays(now()) <= 1) {
                    $badge = ['text' => 'BARU', 'bg' => 'bg-[var(--sm-primary)]', 'textCol' => 'text-white'];
                } elseif ($produk->label_promosi && !$isLabelPromoPerformance) {
                    $badge = ['text' => $produk->label_promosi, 'bg' => 'bg-white', 'textCol' => 'text-[var(--sm-ink)]'];
                }
            @endphp

            @if($badge)
                <span class="inline-block {{ $badge['bg'] }} {{ $badge['textCol'] }} border-2 border-[var(--sm-border)] text-[10px] font-black px-2.5 py-0.5 rounded-full uppercase tracking-wider shadow-solid-sm transform -rotate-1">
                    {{ $badge['text'] }}
                </span>
            @endif
            @if($produk->stok === 'tersedia')
                <span class="inline-block bg-[var(--sm-primary-soft)] text-[var(--sm-ink)] border border-[var(--sm-ink)] text-[9px] font-black px-2 py-0.5 rounded-full uppercase tracking-wider">Tersedia</span>
            @endif
        </div>

    </div>

    {{-- ── Info Produk ───────────────────────────────────────── --}}
    <div class="product-card__info flex min-w-0 flex-1 flex-col justify-between bg-[var(--sm-surface)] p-3 sm:p-5">
        <div>
            {{-- Nama Produk --}}
            <h3 class="text-base sm:text-lg font-black text-[var(--sm-ink)] leading-snug line-clamp-2 mb-1.5 group-hover:text-[var(--sm-primary)] transition-colors duration-150">
                {{ $produk->nama }}
            </h3>

            {{-- Toko Penjual --}}
            <div class="flex items-center gap-1.5 text-xs font-bold text-[var(--sm-text-muted)] uppercase tracking-wider mb-2">
                <svg class="w-3.5 h-3.5 text-[var(--sm-primary)] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                </svg>
                <span class="truncate">{{ $produk->penjual->nama_toko ?? '-' }}</span>
                @if(($produk->penjual->status_toko ?? 'buka') === 'tutup')
                    <span class="shrink-0 text-[9px] font-black text-rose-700 bg-rose-50 border border-rose-200 px-1.5 py-0.5 rounded-full uppercase tracking-wider">Tutup</span>
                @endif
            </div>
            @if($produk->kategori)
                <p class="mb-2 truncate text-[10px] font-bold text-[var(--sm-text-muted)]">{{ $produk->kategori->nama }}</p>
            @endif
        </div>

        <div>
            {{-- Dashed Divider --}}
            <div class="pt-2 border-t-2 border-dashed border-[var(--sm-border)]/20 mb-3"></div>

            {{-- Harga & Stat/Rating --}}
            <div class="flex items-center justify-between gap-2">
                {{-- Harga --}}
                <span class="text-base sm:text-lg font-black text-[var(--sm-primary)] tracking-tight">
                    Rp {{ number_format($produk->harga, 0, ',', '.') }}
                </span>

                {{-- Rating and views --}}
                <div class="flex items-center gap-1 shrink-0">
                    @php
                        $avgRating = $produk->ulasan_avg_rating ?? $produk->average_rating ?? 0;
                        $revCount  = $produk->ulasan_count ?? $produk->review_count ?? 0;
                    @endphp
                    @if($revCount > 0)
                        <div class="flex items-center gap-1 bg-[var(--sm-surface)] border border-[var(--sm-ink)] px-2 py-0.5 rounded-full text-[11px] font-black text-[var(--sm-ink)]">
                            <svg class="w-3 h-3 text-[var(--sm-primary)] fill-current" viewBox="0 0 20 20">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                            </svg>
                            <span>{{ number_format($avgRating, 1) }}</span>
                        </div>
                    @endif

                    <div class="flex items-center gap-0.5 text-[11px] font-bold text-[var(--sm-text-muted)]">
                        <x-public.click-icon class="h-3.5 w-3.5 shrink-0" />
                        <span>{{ number_format($produk->views, 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</a>
@endif

