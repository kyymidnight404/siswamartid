{!! '<' . '?xml version="1.0" encoding="UTF-8"?' . '>' !!}
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    {{-- Halaman Utama --}}
    <url>
        <loc>{{ route('public.home') }}</loc>
        <changefreq>daily</changefreq>
        <priority>1.0</priority>
    </url>

    {{-- Katalog Utama --}}
    <url>
        <loc>{{ route('public.kategori.index') }}</loc>
        <changefreq>daily</changefreq>
        <priority>0.9</priority>
    </url>

    {{-- Kategori Publik --}}
    @foreach($categories as $category)
    <url>
        <loc>{{ route('public.kategori.show', $category->slug) }}</loc>
        <changefreq>weekly</changefreq>
        <priority>0.8</priority>
    </url>
    @endforeach

    {{-- Detail Produk Aktif --}}
    @foreach($products as $product)
    <url>
        <loc>{{ route('public.produk.show', $product->slug) }}</loc>
        <lastmod>{{ $product->updated_at?->tz('UTC')->toAtomString() ?? now()->tz('UTC')->toAtomString() }}</lastmod>
        <changefreq>weekly</changefreq>
        <priority>0.7</priority>
    </url>
    @endforeach

    {{-- Halaman Informasi Statis --}}
    <url>
        <loc>{{ route('public.cara_pemesanan') }}</loc>
        <changefreq>monthly</changefreq>
        <priority>0.5</priority>
    </url>
    <url>
        <loc>{{ route('public.tentang_kami') }}</loc>
        <changefreq>monthly</changefreq>
        <priority>0.5</priority>
    </url>
</urlset>
