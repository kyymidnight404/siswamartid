<!DOCTYPE html>
<html lang="id">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Rekapitulasi Mingguan — {{ $penjual->nama_toko }}</title>
    <style>
        @page {
            margin: 24px 28px;
        }
        body {
            font-family: DejaVu Sans, sans-serif;
            color: var(--sm-ink);
            font-size: 9.5px;
            line-height: 1.35;
        }
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }
        .header-table td {
            border: none;
            padding: 0;
            vertical-align: top;
        }
        .brand-title {
            font-size: 18px;
            font-weight: bold;
            color: var(--sm-primary);
            margin: 0;
            letter-spacing: -0.5px;
        }
        .brand-title span {
            color: var(--sm-ink);
        }
        .report-title {
            font-size: 13px;
            font-weight: bold;
            color: var(--sm-ink);
            margin-top: 3px;
            text-transform: uppercase;
        }
        .store-info {
            font-size: 9.5px;
            color: var(--sm-text-muted);
            margin-top: 2px;
        }
        .period-box {
            text-align: right;
            font-size: 9px;
            color: var(--sm-text-muted);
        }
        .period-label {
            font-size: 10px;
            font-weight: bold;
            color: var(--sm-ink);
            margin-bottom: 2px;
        }
        .divider {
            border-bottom: 2px solid var(--sm-primary);
            margin: 8px 0 14px 0;
        }
        .section-title {
            font-size: 10.5px;
            font-weight: bold;
            text-transform: uppercase;
            color: var(--sm-ink);
            border-left: 3px solid var(--sm-primary);
            padding-left: 6px;
            margin: 14px 0 6px 0;
        }
        .stats-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 6px 0;
            margin-bottom: 12px;
        }
        .stat-card {
            background: var(--sm-surface);
            border: 1px solid var(--sm-border);
            padding: 8px 10px;
            vertical-align: top;
        }
        .stat-label {
            font-size: 8px;
            font-weight: bold;
            text-transform: uppercase;
            color: var(--sm-text-muted);
            margin-bottom: 3px;
        }
        .stat-value {
            font-size: 16px;
            font-weight: bold;
            color: var(--sm-ink);
        }
        .stat-comp {
            font-size: 8px;
            font-weight: bold;
            margin-top: 3px;
        }
        .text-up { color: var(--sm-primary); }
        .text-down { color: var(--sm-ink); }
        .text-neutral { color: var(--sm-text-muted); }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            margin-bottom: 12px;
        }
        .data-table th,
        .data-table td {
            border: 1px solid var(--sm-border);
            padding: 5px 6px;
            text-align: left;
            vertical-align: top;
            word-wrap: break-word;
            font-size: 8.5px;
        }
        .data-table th {
            background: var(--sm-primary-soft);
            font-weight: bold;
            font-size: 8px;
            text-transform: uppercase;
            color: var(--sm-ink);
        }
        .text-center { text-align: center !important; }
        .text-right { text-align: right !important; }
        .font-bold { font-weight: bold; }
        .text-muted { color: var(--sm-text-muted); }
        .badge {
            display: inline-block;
            padding: 1px 5px;
            font-size: 7.5px;
            font-weight: bold;
            border-radius: 3px;
        }
        .badge-up { background: var(--sm-primary-soft); color: var(--sm-ink); }
        .badge-down { background: var(--sm-primary-soft); color: var(--sm-ink); }
        .badge-neutral { background: var(--sm-primary-soft); color: var(--sm-text-muted); }

        .footer {
            margin-top: 16px;
            padding-top: 8px;
            border-top: 1px solid var(--sm-border);
            font-size: 7.5px;
            color: var(--sm-text-muted);
            text-align: justify;
        }
    </style>
</head>
<body>
    {{-- Header --}}
    <table class="header-table">
        <tr>
            <td style="width: 60%;">
                <div class="brand-title">Siswa<span>Mart</span></div>
                <div class="report-title">Rekapitulasi Mingguan Penjual</div>
                <div class="store-info">
                    Toko: <strong>{{ $penjual->nama_toko }}</strong>
                    @if($penjual->user?->name)
                        &middot; Pemilik: {{ $penjual->user->name }}
                    @endif
                </div>
            </td>
            <td style="width: 40%;" class="period-box">
                <div class="period-label">Periode Rekapitulasi</div>
                <div>{{ $periodLabel }}</div>
                <div style="margin-top: 3px;">Dicetak: {{ now()->translatedFormat('d F Y, H:i') }} WIB</div>
            </td>
        </tr>
    </table>

    <div class="divider"></div>

    {{-- Ringkasan Kartu Statistik --}}
    <table class="stats-table">
        <tr>
            <td class="stat-card" style="width: 33.33%;">
                <div class="stat-label">Total Produk Katalog</div>
                <div class="stat-value">{{ number_format($totalProdukCount) }}</div>
                <div class="stat-comp text-neutral">Produk terdaftar di toko</div>
            </td>
            <td class="stat-card" style="width: 33.33%;">
                <div class="stat-label">Dilihat Minggu Ini (Views)</div>
                <div class="stat-value" style="color: var(--sm-primary);">{{ number_format($viewsThisWeek) }}</div>
                <div class="stat-comp {{ $viewsComp['type'] === 'up' ? 'text-up' : ($viewsComp['type'] === 'down' ? 'text-down' : 'text-neutral') }}">
                    {{ $viewsComp['text'] }}
                </div>
            </td>
            <td class="stat-card" style="width: 33.33%;">
                <div class="stat-label">Ulasan Baru Masuk</div>
                <div class="stat-value" style="color: var(--sm-on-accent);">{{ number_format($ulasanThisWeek) }}</div>
                <div class="stat-comp {{ $ulasanComp['type'] === 'up' ? 'text-up' : ($ulasanComp['type'] === 'down' ? 'text-down' : 'text-neutral') }}">
                    {{ $ulasanComp['text'] }}
                </div>
            </td>
        </tr>
    </table>

    {{-- Produk Terpopuler --}}
    <div class="section-title">Produk Terpopuler (Top 5 Periode Ini)</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 7%;" class="text-center">Rank</th>
                <th style="width: 45%;">Nama Produk</th>
                <th style="width: 18%;">Kategori</th>
                <th style="width: 12%;" class="text-right">Views</th>
                <th style="width: 9%;" class="text-center">Rating</th>
                <th style="width: 9%;" class="text-center">Ulasan</th>
            </tr>
        </thead>
        <tbody>
            @forelse($produkTerpopuler as $index => $item)
                <tr>
                    <td class="text-center font-bold">#{{ $index + 1 }}</td>
                    <td>
                        <strong style="color: var(--sm-ink);">{{ $item->nama }}</strong>
                    </td>
                    <td class="text-muted">{{ $item->kategori->nama ?? '—' }}</td>
                    <td class="text-right font-bold" style="color: var(--sm-primary);">
                        {{ number_format($item->cur_views ?? $item->period_views) }}
                    </td>
                    <td class="text-center font-bold" style="color: var(--sm-on-accent);">
                        {{ number_format($item->average_rating, 1) }}
                    </td>
                    <td class="text-center">{{ number_format($item->review_count) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center text-muted" style="padding: 10px;">
                        Belum ada data produk populer pada periode ini.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{-- Performa Semua Produk --}}
    <div class="section-title">Performa Semua Produk ({{ count($performaProduk) }} Produk)</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 34%;">Nama Produk</th>
                <th style="width: 16%;">Kategori</th>
                <th style="width: 13%;" class="text-right">Views Ini</th>
                <th style="width: 13%;" class="text-right">Views Lalu</th>
                <th style="width: 10%;" class="text-center">Tren</th>
                <th style="width: 7%;" class="text-center">Ulasan</th>
                <th style="width: 7%;" class="text-center">Rating</th>
            </tr>
        </thead>
        <tbody>
            @forelse($performaProduk as $p)
                <tr>
                    <td>
                        <strong>{{ $p->nama }}</strong>
                    </td>
                    <td class="text-muted">{{ $p->kategori->nama ?? '—' }}</td>
                    <td class="text-right font-bold">{{ number_format($p->cur_views) }}</td>
                    <td class="text-right text-muted">{{ number_format($p->prev_views) }}</td>
                    <td class="text-center">
                        @if($p->trend === 'up')
                            <span class="badge badge-up">+{{ $p->views_diff }}</span>
                        @elseif($p->trend === 'down')
                            <span class="badge badge-down">{{ $p->views_diff }}</span>
                        @else
                            <span class="badge badge-neutral">—</span>
                        @endif
                    </td>
                    <td class="text-center">{{ $p->new_reviews }}</td>
                    <td class="text-center font-bold" style="color: var(--sm-on-accent);">
                        {{ number_format($p->average_rating, 1) }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center text-muted" style="padding: 10px;">
                        Belum ada produk di katalog toko Anda.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{-- Ulasan Masuk Minggu Ini --}}
    <div class="section-title">Ulasan Masuk Minggu Ini ({{ count($ulasanMingguan) }} Ulasan)</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 15%;">Waktu</th>
                <th style="width: 18%;">Pengulas</th>
                <th style="width: 25%;">Produk</th>
                <th style="width: 10%;" class="text-center">Rating</th>
                <th style="width: 32%;">Komentar</th>
            </tr>
        </thead>
        <tbody>
            @forelse($ulasanMingguan as $rev)
                <tr>
                    <td>{{ $rev->created_at?->format('d M Y, H:i') ?? '—' }}</td>
                    <td><strong>{{ $rev->guest_username ?: 'Pengunjung' }}</strong></td>
                    <td>{{ $rev->produk?->nama ?? 'Produk' }}</td>
                    <td class="text-center font-bold" style="color: var(--sm-on-accent);">
                        {{ $rev->rating }} / 5
                    </td>
                    <td>{{ $rev->komentar ?: 'Tidak ada teks ulasan.' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center text-muted" style="padding: 10px;">
                        Belum ada ulasan baru pada periode minggu ini.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        Rekapitulasi performa resmi toko SiswaMart. Laporan ini dibuat secara otomatis oleh sistem SiswaMart untuk mendukung evaluasi dan perkembangan usaha siswa.
    </div>
</body>
</html>
