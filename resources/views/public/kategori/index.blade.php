@extends('layouts.public')

@section('title', 'Semua Produk | SiswaMart')
@section('meta_description', 'Jelajahi seluruh kategori produk kreatif karya siswa di SiswaMart.')

@section('content')

<style>
.range-slider input[type="range"]::-webkit-slider-thumb {
    pointer-events: auto;
    width: 20px;
    height: 20px;
    border-radius: 50%;
    background: var(--sm-primary);
    border: 2px solid var(--sm-border);
    cursor: pointer;
    -webkit-appearance: none;
}
.range-slider input[type="range"]::-moz-range-thumb {
    pointer-events: auto;
    width: 20px;
    height: 20px;
    border-radius: 50%;
    background: var(--sm-primary);
    border: 2px solid var(--sm-border);
    cursor: pointer;
}
</style>

<div class="bg-[var(--sm-bg)] min-h-screen pb-20 pt-8" x-data="{
    q: '{{ $search ?? '' }}',
    sort: '{{ $sort ?? 'terbaru' }}',
    min_price: '{{ $minPrice ?? '' }}',
    max_price: '{{ $maxPrice ?? '' }}',
    get sliderMin() {
        return this.min_price === '' ? 0 : parseInt(this.min_price);
    },
    set sliderMin(val) {
        this.min_price = val;
    },
    get sliderMax() {
        return this.max_price === '' ? 50000 : parseInt(this.max_price);
    },
    set sliderMax(val) {
        this.max_price = val;
    },
    validateInput() {
        let min = parseInt(this.min_price);
        let max = parseInt(this.max_price);
        if (isNaN(min) || min < 0) min = 0;
        if (isNaN(max) || max > 50000) max = 50000;
        if (min > max) {
            let tmp = min;
            min = max;
            max = tmp;
        }
        this.min_price = min;
        this.max_price = max;
    },
    submitForm() {
        this.validateInput();
        if (this.min_price == 0) this.min_price = '';
        if (this.max_price == 50000) this.max_price = '';
        const form = this.$refs.mainForm;
        this.$refs.searchInput.value = this.q;
        this.$refs.sortInput.value = this.sort;
        this.$refs.minPriceInput.value = this.min_price;
        this.$refs.maxPriceInput.value = this.max_price;
        form.submit();
    },
    resetFilter() {
        this.min_price = '';
        this.max_price = '';
        this.submitForm();
    }
}">
    
    <form x-ref="mainForm" action="{{ route('public.kategori.index') }}" method="GET" class="hidden">
        <input type="hidden" name="q" x-ref="searchInput" x-model="q">
        <input type="hidden" name="sort" x-ref="sortInput" x-model="sort">
        <input type="hidden" name="min_price" x-ref="minPriceInput" x-model="min_price">
        <input type="hidden" name="max_price" x-ref="maxPriceInput" x-model="max_price">
    </form>

    <div class="max-w-[1400px] mx-auto px-6 lg:px-[60px]">
        
        {{-- ─── Breadcrumb ────────────────────────────────────────────── --}}
        <nav class="flex items-center gap-2 text-sm text-[var(--sm-text-muted)] font-bold mb-8 uppercase tracking-widest" aria-label="Breadcrumb">
            <a href="{{ route('public.home') }}" class="hover:text-[var(--sm-primary)] transition-colors">BERANDA</a>
            <span class="text-[var(--sm-border)]">/</span>
            <a href="{{ route('public.kategori.index') }}" class="hover:text-[var(--sm-primary)] transition-colors">KATEGORI</a>
            <span class="text-[var(--sm-border)]">/</span>
            <span class="text-[var(--sm-ink)]">SEMUA PRODUK</span>
        </nav>

        {{-- ─── Header Section ────────────────────────────────────────── --}}
        <div class="flex flex-col lg:flex-row lg:items-end justify-between gap-6 mb-12 border-b-4 border-[var(--sm-border)] pb-8">
            <div>
                <h1 class="text-5xl font-black text-[var(--sm-ink)] mb-3 uppercase tracking-tighter">Semua Produk</h1>
                <p class="text-lg font-bold text-[var(--sm-text-muted)]">Temukan jajanan dan minuman favorit buatan teman-temanmu.</p>
            </div>
            
            <div class="hidden lg:flex items-center gap-3 w-full lg:w-auto">
                    <span class="text-sm font-bold text-[var(--sm-text-muted)] uppercase tracking-wider">URUTKAN</span>
                    <select x-model="sort" @change="submitForm()"
                            class="w-full sm:w-auto pl-4 pr-10 py-3 text-sm font-bold uppercase tracking-wider border-2 border-[var(--sm-border)] bg-white rounded-xl shadow-solid-sm focus:outline-none focus:ring-0 appearance-none cursor-pointer hover:-translate-y-0.5 transition-transform">
                        <option value="terbaru">Terbaru</option>
                        <option value="terpopuler">Terpopuler</option>
                        <option value="termurah">Termurah</option>
                        <option value="termahal">Termahal</option>
                    </select>
            </div>
        </div>

        <div class="flex flex-col lg:flex-row gap-10">

            {{-- ─── Sidebar (Desktop) ──────────────────────── --}}
            <aside class="w-full lg:w-[320px] flex-shrink-0">
                <div class="space-y-8 lg:sticky lg:top-24">
                    
                    <x-public.category-select :kategori-list="$kategoriList" />

                    {{-- Card Filter Harga --}}
                    <div class="hidden lg:block bg-[var(--sm-surface)] rounded-3xl border-2 border-[var(--sm-border)] shadow-solid-md p-6">
                        <h2 class="text-xl font-black text-[var(--sm-ink)] mb-6 uppercase tracking-wider">Atur Harga</h2>
                        
                        {{-- Range visualization --}}
                        <div class="mb-6">
                            <div class="flex justify-between text-xs font-bold text-[var(--sm-text-muted)] uppercase tracking-wider mb-4">
                                <span>Min</span>
                                <span>Max</span>
                            </div>
                            <div class="relative h-3 bg-white border-2 border-[var(--sm-border)] rounded-full range-slider">
                                <div class="absolute top-0 h-full bg-[var(--sm-primary)] border-y-2 border-[var(--sm-border)]"
                                     x-bind:style="`left: ${(sliderMin / 50000) * 100}%; right: ${100 - (sliderMax / 50000) * 100}%;`"></div>
                                
                                <input type="range" min="0" max="50000" step="1000" 
                                       x-model="sliderMin" 
                                       @input="if(sliderMin > sliderMax) sliderMin = sliderMax"
                                       class="absolute w-full h-3 opacity-0 cursor-pointer pointer-events-none appearance-none z-20">
                                       
                                <input type="range" min="0" max="50000" step="1000" 
                                       x-model="sliderMax" 
                                       @input="if(sliderMax < sliderMin) sliderMax = sliderMin"
                                       class="absolute w-full h-3 opacity-0 cursor-pointer pointer-events-none appearance-none z-20">
                            </div>
                        </div>

                        <div class="flex items-center gap-3 mb-6">
                            <div class="flex-1">
                                <label class="text-xs font-bold text-[var(--sm-text-muted)] block mb-2 uppercase tracking-wider">Min (Rp)</label>
                                <input type="text" x-model="min_price" @input="min_price = min_price.toString().replace(/[^0-9]/g, '')" @change="validateInput()" placeholder="0" class="w-full px-3 py-2 text-sm font-bold border-2 border-[var(--sm-border)] rounded-xl focus:outline-none focus:ring-0">
                            </div>
                            <div class="flex-1">
                                <label class="text-xs font-bold text-[var(--sm-text-muted)] block mb-2 uppercase tracking-wider">Max (Rp)</label>
                                <input type="text" x-model="max_price" @input="max_price = max_price.toString().replace(/[^0-9]/g, '')" @change="validateInput()" placeholder="50000" class="w-full px-3 py-2 text-sm font-bold border-2 border-[var(--sm-border)] rounded-xl focus:outline-none focus:ring-0">
                            </div>
                        </div>

                        <button @click="submitForm()" class="btn-primary w-full bg-[var(--sm-primary)] text-white text-sm py-3 mb-3 hover:bg-[var(--sm-primary-hover)]">
                            TAMPILKAN PRODUK
                        </button>
                        <button @click="resetFilter()" class="w-full text-center text-xs font-bold text-[var(--sm-text-muted)] hover:text-[var(--sm-ink)] transition-colors uppercase tracking-wider">
                            Atur Ulang Filter
                        </button>
                    </div>

                </div>
            </aside>

            {{-- ─── Main Content ──────────────────────────────────────── --}}
            <main class="flex-1 min-w-0">
                
                {{-- Product Grid --}}
                @if($produk->isEmpty())
                    <div class="empty-state">
                        <div class="text-[80px] mb-6 grayscale opacity-40">
                            <x-public.home-icon name="package" :size="72" aria-hidden="true" />
                        </div>
                        <h3 class="text-3xl font-black text-[var(--sm-ink)] mb-4 uppercase">Kosong Melompong!</h3>
                        <p class="text-lg font-bold text-[var(--sm-text-muted)] mb-8 max-w-md mx-auto">
                            @if($search || $minPrice || $maxPrice)
                                Filter yang kamu pilih terlalu ketat. Coba ubah atau reset filter.
                            @else
                                Belum ada produk aktif yang tersedia di kategori ini.
                            @endif
                        </p>
                        <a href="{{ route('public.kategori.index') }}" class="btn-primary inline-flex px-8 py-3 bg-[var(--sm-primary)] text-white hover:bg-[var(--sm-primary-hover)]">
                            LIHAT SEMUA PRODUK
                        </a>
                    </div>
                @else
                    <div class="category-product-grid grid grid-cols-1 gap-4 sm:grid-cols-2 md:gap-6 lg:grid-cols-4">
                        @foreach($produk as $item)
                            <x-public.product-card :produk="$item" />
                        @endforeach
                    </div>

                    {{-- Pagination --}}
                    <div class="mt-16">
                        {{ $produk->links() }}
                    </div>
                @endif

            </main>
        </div>
    </div>
</div>

@endsection
