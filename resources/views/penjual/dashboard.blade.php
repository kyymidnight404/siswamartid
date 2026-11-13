@extends('layouts.penjual')

@section('title', 'Dashboard penjual')
@section('page-title', 'Dashboard penjual')

@section('content')
<div class="seller-dashboard-page">
    <header class="seller-dashboard-welcome">
        <div>
            <h2>Selamat datang, {{ Auth::user()->name }}</h2>
            <p>Kelola produk dan pantau performa tokomu.</p>
        </div>
        <a href="{{ route('penjual.produk.create') }}" class="ui-button ui-button--primary seller-dashboard-add">
            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
            Tambah produk
        </a>
    </header>

    <section class="seller-dashboard-summary" aria-labelledby="seller-dashboard-summary-title">
        <header class="seller-dashboard-section-heading">
            <div>
                <h2 id="seller-dashboard-summary-title">Rekapitulasi toko</h2>
                <p>Ringkasan produk, kunjungan, dan ulasan yang tercatat.</p>
            </div>
            <a href="{{ route('penjual.laporan') }}" class="seller-dashboard-text-link">
                Lihat rekapitulasi lengkap
                <x-public.home-icon name="arrow-right" :size="16" aria-hidden="true" />
            </a>
        </header>

        <div class="seller-dashboard-stats">
            <x-ui.card class="seller-dashboard-stat">
                <div class="seller-dashboard-stat__heading">
                    <span class="seller-dashboard-stat__icon"><x-public.home-icon name="package" :size="20" aria-hidden="true" /></span>
                    <span class="seller-dashboard-stat__label">Total produk</span>
                </div>
                <p class="seller-dashboard-stat__value">{{ number_format($totalProduk) }}</p>
            </x-ui.card>

            <x-ui.card class="seller-dashboard-stat">
                <div class="seller-dashboard-stat__heading">
                    <span class="seller-dashboard-stat__icon"><x-public.home-icon name="check" :size="20" aria-hidden="true" /></span>
                    <span class="seller-dashboard-stat__label">Produk aktif</span>
                </div>
                <p class="seller-dashboard-stat__value">{{ number_format($produkAktif) }}</p>
            </x-ui.card>

            <x-ui.card @class(['seller-dashboard-stat', 'seller-dashboard-stat--pending', 'has-pending' => $produkPending > 0])>
                <div class="seller-dashboard-stat__heading">
                    <span class="seller-dashboard-stat__icon"><x-public.home-icon name="clock" :size="20" aria-hidden="true" /></span>
                    <span class="seller-dashboard-stat__label">Menunggu</span>
                </div>
                <p class="seller-dashboard-stat__value">{{ number_format($produkPending) }}</p>
            </x-ui.card>

            <x-ui.card class="seller-dashboard-stat">
                <div class="seller-dashboard-stat__heading">
                    <span class="seller-dashboard-stat__icon"><x-public.home-icon name="eye" :size="20" aria-hidden="true" /></span>
                    <span class="seller-dashboard-stat__label">Total dilihat</span>
                </div>
                <p class="seller-dashboard-stat__value">{{ number_format($totalViews) }}</p>
            </x-ui.card>

            <x-ui.card class="seller-dashboard-stat">
                <div class="seller-dashboard-stat__heading">
                    <span class="seller-dashboard-stat__icon"><x-public.home-icon name="message-circle" :size="20" aria-hidden="true" /></span>
                    <span class="seller-dashboard-stat__label">Total ulasan</span>
                </div>
                <p class="seller-dashboard-stat__value">{{ number_format($totalUlasan) }}</p>
            </x-ui.card>
        </div>
    </section>

    <div class="seller-dashboard-content">
        <x-ui.card class="seller-dashboard-panel seller-dashboard-products">
            <header class="seller-dashboard-panel__header">
                <h2>Produk terbaru</h2>
                <a href="{{ route('penjual.produk.index') }}" class="seller-dashboard-text-link">
                    Lihat semua produk
                    <x-public.home-icon name="arrow-right" :size="16" aria-hidden="true" />
                </a>
            </header>
            <div class="seller-dashboard-table-wrap">
                <table class="seller-dashboard-table">
                    <colgroup>
                        <col>
                        <col class="seller-dashboard-table__status">
                        <col class="seller-dashboard-table__views">
                        <col class="seller-dashboard-table__actions">
                    </colgroup>
                    <thead>
                        <tr>
                            <th scope="col">Produk</th>
                            <th scope="col">Status</th>
                            <th scope="col" class="is-numeric">Views</th>
                            <th scope="col" class="is-numeric">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($terbaru as $item)
                            <tr>
                                <td class="seller-dashboard-table__product">
                                    <span class="seller-dashboard-product-name" title="{{ $item->nama }}">{{ $item->nama }}</span>
                                    <span class="seller-dashboard-product-price">Rp {{ number_format($item->harga, 0, ',', '.') }}</span>
                                </td>
                                <td><x-ui.status-badge :status="$item->status" /></td>
                                <td class="seller-dashboard-table__views-cell">{{ number_format($item->views) }}</td>
                                <td class="seller-dashboard-table__action-cell">
                                    <a href="{{ route('penjual.produk.edit', $item) }}" class="seller-dashboard-edit" aria-label="Edit {{ $item->nama }}">
                                        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 20h9M16.5 3.5a2.12 2.12 0 0 1 3 3L8 18l-4 1 1-4Z"/></svg>
                                        Edit
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="seller-dashboard-table__empty">Belum ada produk. Tambahkan produk pertamamu.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-ui.card>

        <x-ui.card class="seller-dashboard-panel seller-dashboard-popular">
            <header class="seller-dashboard-panel__header">
                <h2>Produk terpopuler</h2>
            </header>
            <ol class="seller-dashboard-popular__list">
                @forelse($topProduk as $idx => $populer)
                    <li class="seller-dashboard-popular__item">
                        <span class="seller-dashboard-popular__rank {{ $idx === 0 ? 'is-first' : '' }}" aria-label="Peringkat {{ $idx + 1 }}">{{ $idx + 1 }}</span>
                        <span class="seller-dashboard-popular__copy">
                            <span class="seller-dashboard-popular__name" title="{{ $populer->nama }}">{{ $populer->nama }}</span>
                            <span class="seller-dashboard-popular__meta">{{ number_format($populer->views) }} dilihat · {{ number_format($populer->ulasan_count) }} ulasan</span>
                        </span>
                    </li>
                @empty
                    <li class="seller-dashboard-popular__empty">Belum ada data performa.</li>
                @endforelse
            </ol>
            <a href="{{ route('penjual.laporan') }}" class="seller-dashboard-text-link seller-dashboard-popular__link">
                Lihat laporan lengkap
                <x-public.home-icon name="arrow-right" :size="16" aria-hidden="true" />
            </a>
        </x-ui.card>
    </div>

    <x-ui.card class="seller-dashboard-promo">
        <span class="seller-dashboard-promo__icon">
            <x-public.home-icon name="info-circle" :size="24" aria-hidden="true" />
        </span>
        <div>
            <h2>Media promosi</h2>
            <p>SiswaMart adalah katalog promosi. Pembeli menghubungimu langsung lewat WhatsApp.</p>
        </div>
    </x-ui.card>
</div>
@endsection
