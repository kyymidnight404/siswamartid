<!DOCTYPE html>
<html lang="id">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Rekap Ulasan SiswaMart</title>
    <style>
        @page { margin: 28px 30px; }
        body { font-family: DejaVu Sans, sans-serif; color: var(--sm-ink); font-size: 10px; }
        h1 { margin: 0 0 5px; font-size: 20px; }
        .muted { color: var(--sm-text-muted); }
        .summary { margin: 18px 0; padding: 10px; border: 1px solid var(--sm-text-muted); background: var(--sm-primary-soft); }
        .summary span { display: inline-block; margin-right: 28px; }
        table { width: 100%; border-collapse: collapse; table-layout: fixed; }
        th, td { border: 1px solid var(--sm-text-muted); padding: 7px; text-align: left; vertical-align: top; word-wrap: break-word; }
        th { background: var(--sm-primary-soft); color: var(--sm-ink); }
        .rating { white-space: nowrap; }
        .comment { white-space: pre-wrap; }
    </style>
</head>
<body>
    <h1>Rekap Ulasan SiswaMart · {{ $periodLabel }}</h1>
    <div class="muted">Periode {{ $start->format('d M Y') }} sampai {{ $end->format('d M Y') }}</div>

    <div class="summary">
        <span><strong>Total ulasan:</strong> {{ number_format($reviews->count()) }}</span>
        <span><strong>Rating rata-rata:</strong> {{ number_format($averageRating, 2) }} / 5</span>
        <span><strong>Distribusi:</strong>
            @foreach($ratingDistribution as $stars => $count)
                {{ $stars }}★ {{ $count }}@if(!$loop->last), @endif
            @endforeach
        </span>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 13%">Waktu</th>
                <th style="width: 12%">Pengulas</th>
                <th style="width: 7%">Rating</th>
                <th style="width: 24%">Produk</th>
                <th style="width: 18%">Penjual</th>
                <th style="width: 26%">Ulasan</th>
            </tr>
        </thead>
        <tbody>
            @forelse($reviews as $review)
                <tr>
                    <td>{{ $review->created_at?->format('d M Y, H:i') ?? '—' }}</td>
                    <td>{{ $review->guest_username ?: 'Pengunjung' }}</td>
                    <td class="rating">{{ $review->rating }} / 5</td>
                    <td>{{ $review->produk?->nama ?? 'Produk telah dihapus' }}</td>
                    <td>{{ $review->produk?->penjual?->nama_toko ?? 'Penjual tidak tersedia' }}</td>
                    <td class="comment">{{ $review->public_content ?: 'Tidak ada teks ulasan.' }}</td>
                </tr>
            @empty
                <tr><td colspan="6">Belum ada ulasan yang diterbitkan pada periode ini.</td></tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
