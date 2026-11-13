<!DOCTYPE html>
<html lang="id">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>@yield('title', 'Laporan Rekapitulasi SiswaMart')</title>
    <style>
        @page {
            margin: 14mm 15mm 18mm 15mm;
            size: a4 @yield('page-orientation', 'portrait');
        }

        body {
            font-family: "DejaVu Sans", sans-serif;
            font-size: 10pt;
            line-height: 1.4;
            color: #1F3550;
            background: #ffffff;
            margin: 0;
            padding: 0;
        }

        /* ── Header Dokumen ─────────────────────────────────── */
        .pdf-header {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
            border-bottom: 2px solid #1F3550;
            padding-bottom: 8px;
        }
        .pdf-header td {
            border: none;
            padding: 0;
            vertical-align: top;
        }
        .brand-logo {
            font-size: 20pt;
            font-weight: bold;
            line-height: 1.1;
            margin: 0;
        }
        .brand-siswa {
            color: #1F3550;
        }
        .brand-mart {
            color: #3F6A96;
        }
        .report-heading {
            font-size: 13pt;
            font-weight: bold;
            color: #1F3550;
            margin-top: 3px;
        }
        .report-subheading {
            font-size: 10pt;
            color: #4C6078;
            margin-top: 2px;
        }
        .meta-table {
            text-align: right;
            font-size: 10pt;
            color: #4C6078;
        }
        .meta-period {
            font-size: 10pt;
            font-weight: bold;
            color: #1F3550;
            margin-bottom: 2px;
        }

        /* ── Section Header ─────────────────────────────────── */
        .section-bar {
            background-color: #E3ECF5;
            color: #1F3550;
            font-size: 10pt;
            font-weight: bold;
            padding: 6px 10px;
            margin: 14px 0 8px 0;
            border-left: 4px solid #3F6A96;
        }

        /* ── Summary Block (2x2 / Cards) ────────────────────── */
        .summary-grid {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 14px;
        }
        .summary-grid td {
            border: 1px solid #B9C7D8;
            padding: 9px 12px;
            vertical-align: top;
            background-color: #ffffff;
        }
        .summary-label {
            font-size: 10pt;
            color: #4C6078;
            font-weight: normal;
            margin-bottom: 4px;
        }
        .summary-value {
            font-size: 16pt;
            font-weight: bold;
            color: #1F3550;
            line-height: 1.1;
        }
        .summary-note {
            font-size: 10pt;
            color: #4C6078;
            margin-top: 4px;
        }

        /* ── Tabel Standar ──────────────────────────────────── */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 14px;
        }
        .data-table thead {
            display: table-header-group;
        }
        .data-table tr {
            page-break-inside: avoid;
        }
        .data-table th {
            background-color: #3F6A96;
            color: #ffffff;
            font-weight: bold;
            font-size: 10pt;
            text-align: left;
            padding: 7px 8px;
            border: 1px solid #B9C7D8;
        }
        .data-table td {
            font-size: 10pt;
            padding: 6px 8px;
            border: 1px solid #B9C7D8;
            vertical-align: middle;
            color: #1F3550;
        }
        .data-table tbody tr:nth-child(even) {
            background-color: #F5F8FC;
        }
        .data-table tbody tr:nth-child(odd) {
            background-color: #ffffff;
        }
        .data-table tfoot td {
            background-color: #E3ECF5;
            font-weight: bold;
            color: #1F3550;
            border: 1px solid #B9C7D8;
            padding: 7px 8px;
        }

        /* Rank 1 highlight */
        .rank-first {
            background-color: #F2B84B !important;
            color: #1F3550 !important;
            font-weight: bold;
        }

        /* ── Alignment Utilities ────────────────────────────── */
        .text-left { text-align: left !important; }
        .text-center { text-align: center !important; }
        .text-right { text-align: right !important; }
        .font-bold { font-weight: bold; }
        .text-secondary { color: #4C6078; }

        /* ── Bar Chart (CSS / Table) ────────────────────────── */
        .chart-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 6px;
            margin-bottom: 12px;
        }
        .chart-table td {
            border: 1px solid #B9C7D8;
            padding: 6px 8px;
            font-size: 10pt;
        }
        .bar-container {
            width: 100%;
            background-color: #E3ECF5;
            height: 14px;
            border-radius: 4px;
            overflow: hidden;
        }
        .bar-fill {
            background-color: #3F6A96;
            height: 14px;
            border-radius: 4px;
        }

        @yield('extra-styles')
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

    {{-- Header Dokumen --}}
    <table class="pdf-header">
        <tr>
            <td style="width: 60%;">
                <div class="brand-logo">
                    <span class="brand-siswa">Siswa</span><span class="brand-mart">Mart</span>
                </div>
                <div class="report-heading">@yield('report-title', 'Rekapitulasi Laporan')</div>
                <div class="report-subheading">
                    @yield('report-subtitle')
                </div>
            </td>
            <td style="width: 40%;" class="meta-table">
                <div class="meta-period">Periode: @yield('period-label')</div>
                <div>Waktu Ekspor: {{ ($exportedAt ?? now('Asia/Jakarta'))->format('d/m/Y H:i') }} WIB</div>
                @yield('extra-meta')
            </td>
        </tr>
    </table>

    {{-- Konten Utama --}}
    <div class="pdf-content">
        @yield('content')
    </div>
</body>
</html>
