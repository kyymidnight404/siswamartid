{{--
    Komponen: Kategori dropdown untuk navbar.
    Mengambil data sendiri agar bisa digunakan di semua halaman via layout,
    tanpa perlu memodifikasi setiap controller.
--}}
@php
    $navKategori = \App\Models\Kategori::select('id', 'nama', 'slug', 'icon')
        ->orderBy('nama')
        ->limit(10)
        ->get();
@endphp

<div x-data="{ catOpen: false }" class="relative" @keydown.escape.window="catOpen = false">
    <button @click="catOpen = !catOpen"
            @click.outside="catOpen = false"
            id="nav-kategori-btn"
            class="inline-flex items-center gap-1 text-sm font-medium text-[var(--sm-text-muted)] hover:text-[var(--sm-primary)] px-3 py-2 rounded-lg hover:bg-[var(--sm-primary-soft)] transition-all"
            :aria-expanded="catOpen.toString()"
            aria-haspopup="true">
        Kategori
        <svg class="w-3.5 h-3.5 transition-transform duration-150"
             :class="{ 'rotate-180': catOpen }"
             fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
        </svg>
    </button>

    <div x-show="catOpen"
         @click.outside="catOpen = false"
         x-transition:enter="transition ease-out duration-100"
         x-transition:enter-start="opacity-0 -translate-y-1 scale-95"
         x-transition:enter-end="opacity-100 translate-y-0 scale-100"
         x-transition:leave="transition ease-in duration-75"
         x-transition:leave-start="opacity-100 translate-y-0 scale-100"
         x-transition:leave-end="opacity-0 -translate-y-1 scale-95"
         class="absolute top-full left-0 mt-1.5 w-52 bg-[var(--sm-surface)] border border-[var(--sm-border)] rounded-xl shadow-lg py-1 z-50 origin-top-left"
         style="display:none;"
         role="menu">

        @forelse($navKategori as $item)
            @php
                $catIcons = ['makanan' => 'burger', 'makanan-berat' => 'burger', 'minuman' => 'glass-full', 'snack' => 'cookie', 'dessert' => 'cake'];
                $iconName = $catIcons[$item->slug] ?? 'package';
            @endphp
            <a href="{{ route('public.kategori.show', $item->slug) }}"
               @click="catOpen = false"
               class="flex items-center gap-3 px-4 py-2.5 text-sm text-[var(--sm-ink)] hover:bg-[var(--sm-primary-soft)] hover:text-[var(--sm-primary)] transition-colors"
               role="menuitem">
                <x-public.home-icon :name="$iconName" class="h-5 w-5 flex-shrink-0" aria-hidden="true" />
                <span>{{ $item->nama }}</span>
            </a>
        @empty
            <p class="px-4 py-3 text-sm text-[var(--sm-text-muted)]">Belum ada kategori.</p>
        @endforelse

        <div class="border-t border-[var(--sm-border)]/20 mt-1 pt-1">
            <a href="{{ route('public.kategori.index') }}"
               @click="catOpen = false"
               class="flex items-center gap-2 px-4 py-2.5 text-sm font-semibold text-[var(--sm-primary)] hover:bg-[var(--sm-primary-soft)] transition-colors"
               role="menuitem">
                Lihat semua produk
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </a>
        </div>
    </div>
</div>

