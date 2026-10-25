@extends('layouts.public')

@section('title', 'SiswaMart')
@section('meta_description', 'Jelajahi SiswaMart, katalog makanan, minuman, dan jajanan buatan siswa SMKN 11 Bandung. Temukan produk favorit dan hubungi penjual langsung via WhatsApp.')

@section('content')
    @php
        $categoryColors = [
            'dessert' => 'var(--sm-primary-soft)',
            'makanan' => 'var(--sm-primary-soft)',
            'makanan-berat' => 'var(--sm-primary-soft)',
            'minuman' => 'var(--sm-primary-soft)',
            'snack' => 'var(--sm-primary-soft)',
        ];
        $categoryIcons = [
            'dessert' => 'cake',
            'makanan' => 'burger',
            'makanan-berat' => 'burger',
            'minuman' => 'glass-full',
            'snack' => 'cookie',
        ];
    @endphp

    <div class="home-page-content">
        <section class="home-hero home-container" aria-labelledby="home-hero-title">
            <div class="home-hero__copy">
                <p class="home-hero__area">
                    <span class="home-hero__area-icon" aria-hidden="true"><x-public.home-icon name="map-pin" /></span>
                    <span class="home-hero__area-desktop">Untuk saat ini, SiswaMart melayani penjualan di area SMKN 11 Bandung.</span>
                    <span class="home-hero__area-mobile">Melayani area SMKN 11 Bandung</span>
                </p>

                <h1 id="home-hero-title" class="home-hero__title">
                    <span class="home-hero__title-first">MAKAN <span class="home-hero__accent">ENAK.</span></span>
                    <span class="home-hero__title-second">BUATAN TEMAN SENDIRI.</span>
                </h1>

                <p class="home-hero__description">
                    Temukan produk makanan dan minuman karya siswa, lalu hubungi penjual langsung lewat WhatsApp.
                </p>

                <div class="home-hero__actions">
                    <a href="{{ route('public.produk.index') }}" class="home-button home-button--primary">Jelajahi katalog</a>
                    <a href="{{ route('public.cara_pemesanan') }}" class="home-button home-button--outline">Cara memesan</a>
                </div>

                <ul class="home-hero__benefits" aria-label="Keunggulan SiswaMart">
                    <li>
                        <span class="home-hero__benefit-icon" aria-hidden="true">
                            <x-public.home-icon name="brand-whatsapp" />
                        </span>
                        <span>Pesan langsung via WhatsApp</span>
                    </li>
                    <li>
                        <span class="home-hero__benefit-icon" aria-hidden="true">
                            <x-public.home-icon name="school" />
                        </span>
                        <span>Dari siswa SMKN 11</span>
                    </li>
                    <li>
                        <span class="home-hero__benefit-icon" aria-hidden="true">
                            <x-public.home-icon name="coin" />
                        </span>
                        <span>Harga ramah kantong</span>
                    </li>
                </ul>
            </div>

            <div class="home-hero__visual" aria-label="Etalase produk SiswaMart"
                 x-data="{ activeSlide: 0, init() { const track = this.$refs.track; if (!track || !('IntersectionObserver' in window)) return; this.slideObserver = new IntersectionObserver((entries) => { const current = entries.filter(entry => entry.isIntersecting).sort((a, b) => b.intersectionRatio - a.intersectionRatio)[0]; if (current) this.activeSlide = Number(current.target.dataset.slide); }, { root: track, threshold: 0.6 }); this.$nextTick(() => [...track.children].forEach(card => this.slideObserver.observe(card))); }, goToSlide(index) { const card = this.$refs.track?.children[index]; card?.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'center' }); } }">
                @if($produkHero->isEmpty())
                    <div class="home-hero__fallback">
                        <img src="{{ asset('images/food.png') }}"
                             alt="Ilustrasi pixel-art makanan dan minuman SiswaMart"
                             width="380" height="380" fetchpriority="high">
                    </div>
                @else
                    <p class="home-showcase__mobile-title">Produk pilihan, mulai Rp {{ number_format((float) $hargaMinimumAktif, 0, ',', '.') }}</p>
                    <div class="home-showcase__track" x-ref="track" aria-label="Produk pilihan">
                    @foreach($produkHero as $index => $item)
                        @php
                            $productUrl = route('public.produk.show', $item->slug);
                            $photoUrl = $item->foto_utama ? asset('storage/' . $item->foto_utama) : null;
                            $waNumber = preg_replace('/[^0-9]/', '', $item->penjual?->nomor_whatsapp ?? '');
                            if (str_starts_with($waNumber, '0')) {
                                $waNumber = '62' . substr($waNumber, 1);
                            }
                            $waMessage = "Halo, saya tertarik dengan produk:\n*" . $item->nama . "*\n\nApakah produk ini masih tersedia?";
                            $waLink = $waNumber ? 'https://wa.me/' . $waNumber . '?text=' . rawurlencode($waMessage) : null;
                        @endphp
                        @if($index === 0)
                            <article class="home-showcase-card home-showcase-card--main" data-slide="{{ $index }}">
                                <a class="home-showcase-card__details" href="{{ $productUrl }}" aria-label="Lihat {{ $item->nama }}">
                                    <span class="home-showcase-card__image">
                                        @if($photoUrl)
                                            <img src="{{ $photoUrl }}" alt="{{ $item->nama }}" width="300" height="190" loading="eager" fetchpriority="high" onerror="this.hidden=true;this.nextElementSibling.hidden=false">
                                            <span class="home-showcase-card__placeholder" hidden aria-hidden="true"><x-public.home-icon name="image" /></span>
                                        @else
                                            <span class="home-showcase-card__placeholder" aria-hidden="true"><x-public.home-icon name="image" /></span>
                                        @endif
                                    </span>
                                    <span class="home-showcase-card__body">
                                        <span class="home-showcase-card__meta">
                                            @if($item->kategori)
                                                <span class="home-showcase-card__category">{{ $item->kategori->nama }}</span>
                                            @endif
                                            @if($item->ulasan_avg_rating !== null)
                                                <span class="home-showcase-card__rating" aria-label="Rating {{ number_format($item->ulasan_avg_rating, 1) }} dari 5"><x-public.home-icon name="star" /> {{ number_format($item->ulasan_avg_rating, 1) }}</span>
                                            @endif
                                        </span>
                                        <span class="home-showcase-card__name">{{ $item->nama }}</span>
                                        <span class="home-showcase-card__store">{{ $item->penjual?->nama_toko }}</span>
                                        <span class="home-showcase-card__price">Rp {{ number_format((float) $item->harga, 0, ',', '.') }}</span>
                                    </span>
                                </a>
                                @if($waLink)
                                    <a class="home-showcase-card__whatsapp" href="{{ $waLink }}" target="_blank" rel="noopener noreferrer">
                                        <x-public.home-icon name="brand-whatsapp" />
                                        <span>Pesan via WhatsApp</span>
                                    </a>
                                @endif
                            </article>
                        @else
                            <article class="home-showcase-card home-showcase-card--back home-showcase-card--back-{{ $index }}" data-slide="{{ $index }}">
                                <a class="home-showcase-card__details" href="{{ $productUrl }}" aria-label="Lihat {{ $item->nama }}">
                                    <span class="home-showcase-card__image">
                                        @if($photoUrl)
                                            <img src="{{ $photoUrl }}" alt="{{ $item->nama }}" width="220" height="130" loading="lazy" onerror="this.hidden=true;this.nextElementSibling.hidden=false">
                                            <span class="home-showcase-card__placeholder" hidden aria-hidden="true"><x-public.home-icon name="image" /></span>
                                        @else
                                            <span class="home-showcase-card__placeholder" aria-hidden="true"><x-public.home-icon name="image" /></span>
                                        @endif
                                    </span>
                                    <span class="home-showcase-card__back-body">
                                        <span class="home-showcase-card__back-name">{{ $item->nama }}</span>
                                        <span class="home-showcase-card__back-price">Rp {{ number_format((float) $item->harga, 0, ',', '.') }}</span>
                                    </span>
                                </a>
                                <span class="home-showcase-card__mobile-body">
                                    <span class="home-showcase-card__meta">
                                        @if($item->kategori)
                                            <span class="home-showcase-card__category">{{ $item->kategori->nama }}</span>
                                        @endif
                                        @if($item->ulasan_avg_rating !== null)
                                            <span class="home-showcase-card__rating" aria-label="Rating {{ number_format($item->ulasan_avg_rating, 1) }} dari 5"><x-public.home-icon name="star" /> {{ number_format($item->ulasan_avg_rating, 1) }}</span>
                                        @endif
                                    </span>
                                    <span class="home-showcase-card__mobile-name">{{ $item->nama }}</span>
                                    <span class="home-showcase-card__store">{{ $item->penjual?->nama_toko }}</span>
                                    <span class="home-showcase-card__price">Rp {{ number_format((float) $item->harga, 0, ',', '.') }}</span>
                                    @if($waLink)
                                        <a class="home-showcase-card__whatsapp" href="{{ $waLink }}" target="_blank" rel="noopener noreferrer">
                                            <x-public.home-icon name="brand-whatsapp" />
                                            <span>Pesan via WhatsApp</span>
                                        </a>
                                    @endif
                                </span>
                            </article>
                        @endif
                    @endforeach
                    </div>
                    @if($produkHero->count() > 1)
                        <div class="home-showcase__dots" role="group" aria-label="Pilih produk">
                            @foreach($produkHero as $dot => $item)
                                <button type="button" class="home-showcase__dot" @click="goToSlide({{ $dot }})" :class="activeSlide === {{ $dot }} ? 'is-active' : ''" :aria-current="activeSlide === {{ $dot }} ? 'true' : 'false'" aria-label="Lihat produk {{ $dot + 1 }}"></button>
                            @endforeach
                        </div>
                    @endif
                    <span class="home-showcase__minimum">Mulai Rp {{ number_format((float) $hargaMinimumAktif, 0, ',', '.') }}</span>
                @endif
            </div>
        </section>

        @if($kategoriList->isNotEmpty())
            <div class="home-marquee" aria-label="Kategori produk SiswaMart">
                <div class="home-marquee__track">
                    @foreach(range(1, 2) as $copy)
                        <div class="home-marquee__group" @if($copy === 2) aria-hidden="true" @endif>
                            @foreach($kategoriList as $kat)
                                <span class="home-marquee__item" style="--marquee-color: {{ $categoryColors[$kat->slug] ?? 'var(--sm-bg)' }}">
                                    <span class="home-marquee__icon" aria-hidden="true"><x-public.home-icon :name="$categoryIcons[$kat->slug] ?? 'burger'" /></span>
                                    <span>{{ mb_strtoupper($kat->nama) }}</span>
                                </span>
                            @endforeach
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        @if($kategoriList->isNotEmpty())
            <section id="home-categories" class="home-container home-section" aria-labelledby="home-category-title">
                <div class="home-section__heading">
                    <h2 id="home-category-title" class="home-section__title">Pilih kategorimu</h2>
                    <a href="{{ route('public.kategori.index') }}" class="home-text-link">Lihat semua <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg></a>
                </div>

                <div class="home-category-grid">
                    @foreach($kategoriList->take(4) as $kat)
                        @php
                            $icon = $categoryIcons[$kat->slug] ?? 'burger';
                            $color = $categoryColors[$kat->slug] ?? 'var(--sm-bg)';
                        @endphp
                        <a href="{{ route('public.kategori.show', $kat->slug) }}" class="home-category-card" style="--category-color: {{ $color }}">
                            <span class="home-category-card__icon" aria-hidden="true" style="--icon-fill: {{ $color }}">
                                <x-public.home-icon :name="$icon" />
                            </span>
                            <span class="home-category-card__name">{{ $kat->nama }}</span>
                            <span class="home-category-card__arrow" aria-hidden="true"><x-public.home-icon name="arrow-right" /></span>
                        </a>
                    @endforeach
                </div>
            </section>
        @endif

        <section id="home-products" class="home-container home-section" aria-labelledby="home-products-title">
            <div class="home-section__heading">
                <h2 id="home-products-title" class="home-section__title">Yang lagi populer.</h2>
                <a href="{{ route('public.produk.index') }}" class="home-button home-button--small">Lihat semua produk</a>
            </div>

            @if($produkRekomendasi->isNotEmpty())
                <div class="home-products-grid">
                    @foreach($produkRekomendasi->take(4) as $item)
                        <x-public.product-card :produk="$item" homepage />
                    @endforeach
                </div>
            @else
                <p class="home-products-empty">Belum ada produk untuk ditampilkan saat ini.</p>
            @endif
        </section>

        <section id="home-how-to-order" class="home-container home-section" aria-labelledby="home-how-to-title">
            <div class="home-how-to">
                <div class="home-how-to__intro">
                    <div>
                        <p class="home-how-to__eyebrow">Belanja langsung dari pembuatnya</p>
                        <h2 id="home-how-to-title" class="home-how-to__title">Cara memesan.<br>Temukan produk siswa.</h2>
                        <p class="home-how-to__description">Pilih produk, lalu atur pesanan langsung dengan penjual melalui WhatsApp.</p>
                    </div>
                    <a href="{{ route('public.cara_pemesanan') }}" class="home-button home-button--light">Lihat caranya</a>
                </div>

                <ol class="home-how-to__steps">
                    <li class="home-how-to__step">
                        <span class="home-how-to__step-icon" aria-hidden="true">
                            <x-public.home-icon name="search" />
                        </span>
                        <span class="home-how-to__number">01</span>
                        <span class="home-how-to__step-title">Pilih Produk</span>
                    </li>
                    <li class="home-how-to__step">
                        <span class="home-how-to__step-icon" aria-hidden="true">
                            <x-public.home-icon name="star" />
                        </span>
                        <span class="home-how-to__number">02</span>
                        <span class="home-how-to__step-title">Baca Ulasan</span>
                    </li>
                    <li class="home-how-to__step">
                        <span class="home-how-to__step-icon" aria-hidden="true">
                            <x-public.home-icon name="brand-whatsapp" />
                        </span>
                        <span class="home-how-to__number">03</span>
                        <span class="home-how-to__step-title">Hubungi Penjual</span>
                    </li>
                    <li class="home-how-to__step">
                        <span class="home-how-to__step-icon" aria-hidden="true">
                            <x-public.home-icon name="circle-check" />
                        </span>
                        <span class="home-how-to__number">04</span>
                        <span class="home-how-to__step-title">Atur Langsung</span>
                    </li>
                </ol>
            </div>
        </section>
    </div>
@endsection

