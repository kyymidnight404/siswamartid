@extends('layouts.penjual')

@section('title', 'Laporan — SiswaMart')
@section('page-title', 'Laporan')

@section('content')
<style>
    .seller-report {
        width: 100%;
        max-width: 1240px;
        margin: 0;
        color: var(--sm-ink);
        font-size: 16px;
    }
    .seller-report__header {
        display: flex;
        align-items: flex-end;
        justify-content: space-between;
        gap: 24px;
        margin-bottom: 24px;
    }
    .summary-card__top {
        display: flex;
        align-items: center;
        gap: 12px;
    }
    .summary-card__bottom {
        display: flex;
        align-items: flex-end;
        justify-content: space-between;
        gap: 10px;
        margin-top: 14px;
    }
    .summary-card__reading {
        min-width: 0;
    }
    .seller-report__store {
        margin: 0 0 6px;
        color: var(--sm-text-muted);
        font-size: 14px;
        font-weight: 600;
    }
    .seller-report__title {
        margin: 0;
        color: var(--sm-ink);
        font-size: clamp(28px, 3vw, 36px);
        font-weight: 800;
        line-height: 1.15;
    }
    .seller-report__period {
        margin: 8px 0 0;
        color: var(--sm-text-muted);
        font-size: 16px;
        font-weight: 600;
    }
    .seller-report__actions {
        display: flex;
        flex-wrap: wrap;
        justify-content: flex-end;
        gap: 12px;
    }
    .seller-report__actions .ui-button {
        min-height: 48px;
        font-size: 14px;
    }
    .seller-report__actions button:disabled {
        opacity: .5;
        box-shadow: none;
        transform: none;
        cursor: not-allowed;
    }
    .seller-report__stats {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 16px;
        margin-bottom: 24px;
    }
    .seller-report .summary-card {
        min-width: 0;
        padding: 22px;
        border-width: 3px;
        border-color: var(--sm-ink);
        border-radius: 24px;
        box-shadow: 0 5px 0 var(--sm-ink);
    }
    .seller-report .summary-card--popular {
        background: var(--sm-primary-soft);
    }
    .summary-card__icon {
        display: grid;
        width: 44px;
        height: 44px;
        flex: 0 0 44px;
        place-items: center;
        border: 2px solid var(--sm-ink);
        border-radius: 50%;
        background: var(--sm-primary-soft);
        color: var(--sm-ink);
    }
    .summary-card--popular .summary-card__icon {
        background: var(--sm-surface);
    }
    .summary-card__label {
        margin: 0;
        color: var(--sm-text-muted);
        font-size: 14px;
        font-weight: 600;
        line-height: 1.35;
    }
    .summary-card__value {
        margin: 0;
        color: var(--sm-ink);
        font-size: 42px;
        font-weight: 800;
        line-height: 1.1;
    }
    .summary-card__note {
        margin: 8px 0 0;
        color: var(--sm-text-muted);
        font-size: 13px;
        font-weight: 600;
        line-height: 1.45;
    }
    .summary-card__chart {
        display: block;
        width: 100px;
        height: 44px;
        flex: 0 0 100px;
    }
    .seller-report .popular-card {
        padding: 22px;
    }
    .popular-card__heading {
        margin-bottom: 18px;
    }
    .popular-card__title {
        margin: 0;
        color: var(--sm-ink);
        font-size: 20px;
        font-weight: 800;
    }
    .popular-card__description {
        margin: 5px 0 0;
        color: var(--sm-text-muted);
        font-size: 14px;
        font-weight: 600;
    }
    .popular-list {
        display: grid;
        gap: 14px;
        margin: 0;
        padding: 0;
        list-style: none;
    }
    .popular-list__item {
        display: grid;
        grid-template-columns: 36px 52px minmax(140px, 1fr) minmax(120px, 0.8fr) 84px;
        align-items: center;
        gap: 14px;
        padding: 12px 0;
        border-top: 2px solid var(--sm-primary-soft);
    }
    .popular-list__rank {
        display: grid;
        width: 36px;
        height: 36px;
        place-items: center;
        border: 2px solid var(--sm-ink);
        border-radius: 50%;
        background: var(--sm-primary-soft);
        color: var(--sm-ink);
        font-size: 16px;
        font-weight: 800;
    }
    .popular-list__rank--first {
        background: var(--sm-accent);
    }
    .popular-list__image {
        display: grid;
        width: 52px;
        height: 52px;
        overflow: hidden;
        place-items: center;
        border: 2px solid var(--sm-ink);
        border-radius: 14px;
        background: var(--sm-primary-soft);
        color: var(--sm-ink);
    }
    .popular-list__image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    .popular-list__product {
        min-width: 0;
    }
    .popular-list__name {
        overflow: hidden;
        color: var(--sm-ink);
        font-size: 16px;
        font-weight: 700;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    .popular-list__category {
        display: block;
        width: fit-content;
        max-width: 100%;
        margin-top: 5px;
        padding: 3px 9px;
        overflow: hidden;
        border: 1px solid var(--sm-ink);
        border-radius: 999px;
        background: var(--sm-primary-soft);
        color: var(--sm-ink);
        font-size: 13px;
        font-weight: 600;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    .popular-list__track {
        height: 18px;
        overflow: hidden;
        border: 2px solid var(--sm-ink);
        border-radius: 999px;
        background: var(--sm-primary-soft);
        box-sizing: border-box;
    }
    .popular-list__bar {
        display: block;
        height: 100%;
        border-radius: inherit;
        background: var(--sm-primary);
    }
    .popular-list__views {
        color: var(--sm-ink);
        font-size: 16px;
        font-weight: 800;
        text-align: right;
        white-space: nowrap;
    }
    .popular-card__empty,
    .popular-card__note {
        margin: 16px 0 0;
        color: var(--sm-text-muted);
        font-size: 14px;
        font-weight: 600;
        line-height: 1.5;
    }
    .seller-report :is(a, button):focus-visible {
        outline: 3px solid var(--sm-primary);
        outline-offset: 3px;
    }
    @media (max-width: 1100px) {
        .seller-report__stats {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }
    @media (max-width: 700px) {
        .seller-report__header {
            align-items: stretch;
            flex-direction: column;
            gap: 18px;
        }
        .seller-report__actions {
            justify-content: flex-start;
        }
        .popular-list__item {
            grid-template-columns: 36px 52px minmax(0, 1fr) 84px;
            gap: 10px;
        }
        .popular-list__track {
            grid-column: 1 / -1;
            grid-row: 2;
            width: 100%;
        }
        .popular-list__views {
            grid-column: 4;
            grid-row: 1;
        }
    }
    @media (max-width: 560px) {
        .seller-report__stats {
            grid-template-columns: minmax(0, 1fr);
        }
        .seller-report__actions {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
        .seller-report__actions .ui-button {
            width: 100%;
            padding-right: 10px;
            padding-left: 10px;
        }
    }
</style>

<section class="seller-report" aria-labelledby="seller-report-title">
    <header class="seller-report__header">
        <div>
            <p class="seller-report__store">{{ str_replace("'", '’', $penjual->nama_toko) }}</p>
            <h1 id="seller-report-title" class="seller-report__title">Ringkasan 7 hari terakhir</h1>
            <p class="seller-report__period">{{ $periodLabel }}</p>
        </div>

        <nav class="seller-report__actions" aria-label="Navigasi periode laporan">
            <a
                href="{{ route('penjual.laporan', ['week_offset' => $weekOffset - 1]) }}"
                class="ui-button ui-button--secondary"
            >
                7 hari sebelumnya
            </a>
            @if($canGoNext)
                <a href="{{ route('penjual.laporan', ['week_offset' => $weekOffset + 1]) }}" class="ui-button ui-button--secondary">
                    7 hari berikutnya
                </a>
            @else
                <button type="button" class="ui-button ui-button--secondary" disabled aria-disabled="true">
                    7 hari berikutnya
                </button>
            @endif
            <x-ui.download-menu
                :pdf-url="route('penjual.laporan.pdf', ['week_offset' => $weekOffset])"
                :xlsx-url="route('penjual.laporan.sheet', ['week_offset' => $weekOffset])"
                label="Unduh Laporan"
            />
        </nav>
    </header>

    <div class="seller-report__stats">
        <x-ui.card class="summary-card">
            <div class="summary-card__top">
                <div class="summary-card__icon"><x-public.home-icon name="package" :size="21" aria-hidden="true" /></div>
                <p class="summary-card__label">Produk diunggah</p>
            </div>
            <div class="summary-card__bottom">
                <div class="summary-card__reading">
                    <p class="summary-card__value">{{ number_format($uploadedProductsCount) }}</p>
                    <p class="summary-card__note">7 hari terakhir</p>
                </div>
            </div>
        </x-ui.card>

        <x-ui.card class="summary-card">
            <div class="summary-card__top">
                <div class="summary-card__icon"><x-public.home-icon name="message-circle" :size="21" aria-hidden="true" /></div>
                <p class="summary-card__label">Ulasan baru</p>
            </div>
            <div class="summary-card__bottom">
                <div class="summary-card__reading">
                    <p class="summary-card__value">{{ number_format($newReviewsCount) }}</p>
                    <p class="summary-card__note">7 hari terakhir</p>
                </div>
            </div>
        </x-ui.card>

        <x-ui.card class="summary-card">
            <div class="summary-card__top">
                <div class="summary-card__icon"><x-public.home-icon name="eye" :size="21" aria-hidden="true" /></div>
                <p class="summary-card__label">Total views semua produk</p>
            </div>
            @php
                $maxDailyViews = max(1, (int) $dailyViews->max('count'));
                $dailyViewDescription = $dailyViews
                    ->map(fn ($day) => $day['date']->translatedFormat('l') . ' ' . $day['count'] . ' views')
                    ->implode(', ');
                $weekdayInitials = [1 => 'S', 2 => 'S', 3 => 'R', 4 => 'K', 5 => 'J', 6 => 'S', 7 => 'M'];
            @endphp
            <div class="summary-card__bottom">
                <div class="summary-card__reading">
                    <p class="summary-card__value">{{ number_format($totalViews) }}</p>
                    <p class="summary-card__note">{{ $viewsComparison }}</p>
                </div>
                <svg class="summary-card__chart" viewBox="0 0 100 46" role="img" aria-label="Views per hari: {{ $dailyViewDescription }}">
                    @foreach($dailyViews as $day)
                        @php
                            $index = $loop->index;
                            $barHeight = $day['count'] > 0 ? max(3, (int) round($day['count'] / $maxDailyViews * 25)) : 3;
                            $barY = 29 - $barHeight;
                            $weekday = (int) $day['date']->format('N');
                        @endphp
                        <rect
                            x="{{ 3 + ($index * 14) }}"
                            y="{{ $barY }}"
                            width="8"
                            height="{{ $barHeight }}"
                            rx="2"
                            fill="{{ $day['count'] > 0 ? 'var(--sm-primary)' : '#B9C7D8' }}"
                            @if($day['date']->isToday()) stroke="var(--sm-ink)" stroke-width="2" @endif
                        />
                        <text x="{{ 7 + ($index * 14) }}" y="44" text-anchor="middle" font-size="12" fill="var(--sm-text-muted)" aria-hidden="true">{{ $weekdayInitials[$weekday] }}</text>
                    @endforeach
                </svg>
            </div>
        </x-ui.card>

        <x-ui.card class="summary-card summary-card--popular">
            <div class="summary-card__top">
                <div class="summary-card__icon"><x-public.home-icon name="trend-up" :size="21" aria-hidden="true" /></div>
                <p class="summary-card__label">Views produk populer</p>
            </div>
            <div class="summary-card__bottom">
                <div class="summary-card__reading">
                    <p class="summary-card__value">{{ number_format($popularViews) }}</p>
                    <p class="summary-card__note">
                        @if($totalViews > 0)
                            {{ $viewsFromPopularPercent }}% dari semua views
                        @else
                            0
                        @endif
                    </p>
                </div>
            </div>
        </x-ui.card>
    </div>

    <x-ui.card class="popular-card">
        <div class="popular-card__heading">
            <h3 class="popular-card__title">Top 5 produk terpopuler</h3>
            <p class="popular-card__description">Berdasarkan views, 7 hari terakhir</p>
        </div>

        @if($popularProducts->isEmpty())
            <p class="popular-card__empty">Belum ada views pada periode ini.</p>
        @else
            <ol class="popular-list">
                @foreach($popularProducts as $index => $product)
                    <li class="popular-list__item">
                        <span class="popular-list__rank {{ $index === 0 ? 'popular-list__rank--first' : '' }}" aria-label="Peringkat {{ $index + 1 }}">
                            {{ $index + 1 }}
                        </span>
                        <span class="popular-list__image">
                            @if($product->foto_utama)
                                <img src="{{ asset('storage/' . $product->foto_utama) }}" alt="" loading="lazy">
                            @else
                                <x-public.home-icon name="package" :size="22" aria-hidden="true" />
                            @endif
                        </span>
                        <span class="popular-list__product">
                            <span class="popular-list__name">{{ $product->nama }}</span>
                            <span class="popular-list__category">{{ $product->kategori?->nama ?? 'Tanpa kategori' }}</span>
                        </span>
                        <span class="popular-list__track" role="img" aria-label="{{ $product->period_views }} views">
                            <span class="popular-list__bar" style="width: {{ $product->bar_width }}%"></span>
                        </span>
                        <span class="popular-list__views">{{ number_format($product->period_views) }} views</span>
                    </li>
                @endforeach
            </ol>

            @if($popularProducts->count() < 5)
                <p class="popular-card__note">
                    Baru {{ $popularProducts->count() }} produk yang punya views. Produk lain akan muncul di sini setelah dilihat pengunjung.
                </p>
            @endif
        @endif
    </x-ui.card>
</section>
@endsection
