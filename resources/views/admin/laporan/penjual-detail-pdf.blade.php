<!DOCTYPE html>
<html lang="id">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Laporan Rekapitulasi Produk — {{ $penjual->nama_toko }}</title>
    <style>
        @page {
            margin: 14mm 15mm 16mm 15mm;
            size: a4 portrait;
        }

        body {
            font-family: "DejaVu Sans", sans-serif;
            font-size: 8.5pt;
            line-height: 1.35;
            color: #1F3550;
            background: #ffffff;
            margin: 0;
            padding: 0;
        }

        /* ── Header Dokumen (Sesuai Gambar 2, Tema SiswaMart) ── */
        .header-container {
            text-align: center;
            margin-bottom: 12px;
        }

        .header-title {
            font-size: 13pt;
            font-weight: bold;
            color: #1F3550;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin: 0 0 4px 0;
        }

        .header-subtitle {
            font-size: 8.5pt;
            color: #4C6078;
            font-style: italic;
            margin-bottom: 2px;
        }

        .header-divider {
            border-bottom: 2px solid #1F3550;
            margin-top: 10px;
            margin-bottom: 14px;
        }

        /* ── Section Header ─────────────────────────────────── */
        .section-title {
            font-size: 9.5pt;
            font-weight: bold;
            color: #1F3550;
            margin-bottom: 6px;
            letter-spacing: 0.2px;
        }

        /* ── Tabel Standar SiswaMart ────────────────────────── */
        .report-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 14px;
        }

        .report-table thead {
            display: table-header-group;
        }

        .report-table tr {
            page-break-inside: avoid;
        }

        .report-table th {
            background-color: #3F6A96;
            color: #ffffff;
            font-weight: bold;
            font-size: 8pt;
            padding: 6px 7px;
            border: 1px solid #1F3550;
            text-align: left;
            text-transform: uppercase;
            letter-spacing: 0.2px;
        }

        .report-table td {
            font-size: 8pt;
            padding: 5px 7px;
            border: 1px solid #B9C7D8;
            vertical-align: middle;
            color: #1F3550;
        }

        .report-table tbody tr:nth-child(even) {
            background-color: #F5F8FC;
        }

        .report-table tbody tr:nth-child(odd) {
            background-color: #ffffff;
        }

        /* Alignment Utilities */
        .text-center { text-align: center !important; }
        .text-left { text-align: left !important; }
        .text-right { text-align: right !important; }
        .font-bold { font-weight: bold; }
        .text-muted { color: #4C6078; }

        /* Badge status */
        .badge-pending {
            color: #B45309;
            font-weight: bold;
        }
        .badge-ditolak {
            color: #B91C1C;
            font-weight: bold;
        }
        .badge-aktif {
            color: #1F3550;
        }
        .star-icon {
            color: #D97706;
            font-size: 8.5pt;
        }
    </style>
</head>
<body>
    {{-- Script PHP DomPDF: Tunggal & Dinamis (SiswaMart di kiri, Halaman X dari Y di kanan) --}}
    <script type="text/php">
        if (isset($pdf)) {
            $font = $fontMetrics->getFont("DejaVu Sans", "normal");
            $size = 9;
            $y = $pdf->get_height() - 32;

            // Garis pembatas footer (warna #B9C7D8)
            $pdf->line(42, $y - 8, $pdf->get_width() - 42, $y - 8, array(0.725, 0.78, 0.847), 0.75);

            // Tulisan SiswaMart satu kali di kiri bawah
            $pdf->page_text(42, $y, "SiswaMart", $font, $size, array(0.3, 0.38, 0.47));

            // Nomor halaman dinamis dan akurat di kanan bawah
            $pdf->page_text($pdf->get_width() - 145, $y, "Halaman " . $PAGE_NUM . " dari " . $PAGE_COUNT, $font, $size, array(0.3, 0.38, 0.47));
        }
    </script>

    {{-- Header Dokumen Sesuai Gambar 2 --}}
    <div class="header-container">
        <h1 class="header-title">LAPORAN REKAPITULASI PRODUK — {{ strtoupper($penjual->nama_toko) }}</h1>
        <div class="header-subtitle">
            SiswaMart — Tanggal Rekap: {{ $rekapDate }}
        </div>
        <div class="header-subtitle">
            Pemilik: {{ $penjual->user?->name ?? '—' }} (&#64;{{ $penjual->user?->username ?? '—' }}) | Kelas: {{ $penjual->lokasi_kelas ?? '—' }}
        </div>
        <div class="header-divider"></div>
    </div>

    {{-- I. INDIKATOR REKAPITULASI AUDIT (LIFETIME) --}}
    <div class="section-title">I. INDIKATOR REKAPITULASI AUDIT (LIFETIME)</div>
    <table class="report-table">
        <thead>
            <tr>
                <th style="width: 8%;" class="text-center">NO.</th>
                <th style="width: 56%;" class="text-left">INDIKATOR REKAPITULASI</th>
                <th style="width: 36%;" class="text-center">JUMLAH / TOTAL DATA</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="text-center font-bold">1</td>
                <td>Total Produk Diunggah (Lifetime)</td>
                <td class="text-center font-bold">{{ number_format($totalUploaded) }} Produk</td>
            </tr>
            <tr>
                <td class="text-center font-bold">2</td>
                <td>Produk Disetujui (Tayang di Katalog Website)</td>
                <td class="text-center font-bold">{{ number_format($totalApproved) }} Produk ({{ $approvedPercentage }}%)</td>
            </tr>
            <tr>
                <td class="text-center font-bold">3</td>
                <td>Produk Pending / Ditolak Moderasi Admin</td>
                <td class="text-center font-bold">{{ number_format($totalPending) }} Pending / {{ number_format($totalRejected) }} Ditolak</td>
            </tr>
            <tr>
                <td class="text-center font-bold">4</td>
                <td>Total Ulasan &amp; Rata-Rata Rating Toko</td>
                <td class="text-center font-bold">
                    <span class="star-icon">★</span> {{ $avgRating !== null ? number_format($avgRating, 1) : '0.0' }} ({{ number_format($totalUlasan) }} Ulasan)
                </td>
            </tr>
        </tbody>
    </table>

    {{-- II. RINCIAN KATALOG PRODUK & RATING --}}
    <div class="section-title">II. RINCIAN KATALOG PRODUK &amp; RATING {{ $subSectionTitle ?? '(SELURUH DATA)' }}</div>
    <table class="report-table">
        <thead>
            <tr>
                <th style="width: 6%;" class="text-center">NO.</th>
                <th style="width: 27%;" class="text-left">NAMA PRODUK</th>
                <th style="width: 15%;" class="text-left">KATEGORI</th>
                <th style="width: 17%;" class="text-center">TANGGAL UPLOAD</th>
                <th style="width: 17%;" class="text-center">TGL MASUK KATALOG</th>
                <th style="width: 9%;" class="text-center">RATING</th>
                <th style="width: 9%;" class="text-center">TOTAL ULASAN</th>
            </tr>
        </thead>
        <tbody>
            @forelse($products as $index => $prod)
                <tr>
                    <td class="text-center font-bold">{{ $index + 1 }}</td>
                    <td class="font-bold">{{ $prod->nama }}</td>
                    <td class="text-muted">{{ $prod->kategori?->nama ?? '—' }}</td>
                    <td class="text-center">
                        {{ $prod->created_at ? $prod->created_at->locale('id')->translatedFormat('d M Y, H.i') : '—' }}
                    </td>
                    <td class="text-center">
                        @if($prod->status === 'aktif')
                            <span class="badge-aktif">
                                {{ $prod->approved_at ? $prod->approved_at->locale('id')->translatedFormat('d M Y, H.i') : ($prod->updated_at ? $prod->updated_at->locale('id')->translatedFormat('d M Y, H.i') : 'AKTIF') }}
                            </span>
                        @elseif($prod->status === 'pending')
                            <span class="badge-pending">PENDING</span>
                        @elseif($prod->status === 'ditolak')
                            <span class="badge-ditolak">DITOLAK</span>
                        @else
                            <span>{{ strtoupper($prod->status) }}</span>
                        @endif
                    </td>
                    <td class="text-center font-bold">
                        <span class="star-icon">★</span> {{ number_format($prod->avg_rating ?? 0, 1) }}
                    </td>
                    <td class="text-center font-bold">
                        {{ number_format($prod->total_ulasan ?? 0) }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center text-muted" style="padding: 14px;">
                        Belum ada data produk untuk toko ini
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
