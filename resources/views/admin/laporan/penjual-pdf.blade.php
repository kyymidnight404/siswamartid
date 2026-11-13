@extends('exports.pdf.layout')

@section('title', 'Rekap Kinerja Penjual — SiswaMart')
@section('page-orientation', 'portrait')

@section('extra-styles')
    .data-table th, .data-table td {
        font-size: 8pt;
        padding: 5px 6px;
    }
@endsection

@section('report-title', 'Rekap Kinerja Penjual · ' . $periodLabel)
@section('report-subtitle')
    Laporan Agregat Admin SiswaMart &middot; Periode {{ $start->format('d/m/Y') }} – {{ $end->format('d/m/Y') }}
@endsection

@section('period-label', $periodLabel)

@section('content')
    {{-- Summary Grid: 2 Baris x 3 Kolom agar rapi di format Portrait --}}
    <table class="summary-grid">
        <tr>
            <td style="width: 33.3%;">
                <div class="summary-label">Total Penjual</div>
                <div class="summary-value">{{ number_format($sellerReport['total_sellers']) }}</div>
            </td>
            <td style="width: 33.3%;">
                <div class="summary-label">Produk Ditambah</div>
                <div class="summary-value">{{ number_format($sellerReport['products_added']) }}</div>
            </td>
            <td style="width: 33.3%;">
                <div class="summary-label">Produk Dihapus</div>
                <div class="summary-value">{{ number_format($sellerReport['products_deleted']) }}</div>
            </td>
        </tr>
        <tr>
            <td style="width: 33.3%;">
                <div class="summary-label">Permintaan Edit</div>
                <div class="summary-value">{{ number_format($sellerReport['edits_requested']) }}</div>
            </td>
            <td style="width: 33.3%;">
                <div class="summary-label">Total Dilihat</div>
                <div class="summary-value">{{ number_format($sellerReport['views']) }}</div>
            </td>
            <td style="width: 33.3%;">
                <div class="summary-label">Ulasan Baru</div>
                <div class="summary-value">{{ number_format($sellerReport['reviews']) }}</div>
            </td>
        </tr>
    </table>

    {{-- Tabel Utama Performa Penjual (9 kolom - disesuaikan untuk Portrait) --}}
    <div class="section-bar">Performa Setiap Penjual (Diurutkan berdasarkan kunjungan)</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 5%;" class="text-center">No</th>
                <th style="width: 25%;">Penjual &amp; Toko</th>
                <th style="width: 14%;" class="text-center">Produk Aktif</th>
                <th style="width: 10%;" class="text-right">Dilihat</th>
                <th style="width: 8%;" class="text-center">Ulasan</th>
                <th style="width: 10%;" class="text-center">Rating</th>
                <th style="width: 8%;" class="text-center">Tambah</th>
                <th style="width: 8%;" class="text-center">Hapus</th>
                <th style="width: 12%;" class="text-center">Minta Edit</th>
            </tr>
        </thead>
        <tbody>
            @forelse($sellerReport['sellers'] as $index => $seller)
                <tr>
                    <td class="text-center font-bold">{{ $index + 1 }}</td>
                    <td>
                        <strong>{{ $seller->nama_toko }}</strong><br>
                        <span class="text-secondary">{{ $seller->user?->name ?? 'Akun Penjual' }}</span>
                    </td>
                    <td class="text-center">{{ $seller->produk_aktif_count ?? 0 }} / {{ $seller->produk_count }}</td>
                    <td class="text-right font-bold">{{ number_format($seller->period_views) }}</td>
                    <td class="text-center">{{ number_format($seller->period_reviews) }}</td>
                    <td class="text-center font-bold">
                        {{ $seller->period_rating !== null ? number_format($seller->period_rating, 1) . ' / 5' : '—' }}
                    </td>
                    <td class="text-center">+{{ $seller->period_added }}</td>
                    <td class="text-center">-{{ $seller->period_deleted }}</td>
                    <td class="text-center font-bold">{{ number_format($seller->period_edits_requested) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" class="text-center text-secondary" style="padding: 12px;">
                        Belum ada data pada periode ini
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{-- Tabel Pendukung Vertikal: Top 10 Produk & Aktivitas Terakhir --}}
    <div class="section-bar">Top 10 Produk Paling Banyak Dilihat</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 6%;" class="text-center">#</th>
                <th style="width: 54%;">Nama Produk &amp; Toko</th>
                <th style="width: 20%;" class="text-right">Dilihat</th>
                <th style="width: 20%;" class="text-center">Ulasan</th>
            </tr>
        </thead>
        <tbody>
            @forelse($sellerReport['top_products'] as $idx => $prod)
                <tr>
                    <td class="text-center {{ $idx === 0 ? 'rank-first' : '' }} font-bold">#{{ $idx + 1 }}</td>
                    <td>
                        <strong>{{ $prod->nama }}</strong><br>
                        <span class="text-secondary">{{ $prod->penjual?->nama_toko ?? 'Penjual tidak tersedia' }}</span>
                    </td>
                    <td class="text-right font-bold">{{ number_format($prod->period_views) }}</td>
                    <td class="text-center">{{ number_format($prod->period_reviews) }}</td>
                </tr>
            @empty
                <tr><td colspan="4" class="text-center text-secondary">Belum ada data pada periode ini</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="section-bar">Aktivitas Terakhir</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 20%;">Waktu</th>
                <th style="width: 30%;">Penjual</th>
                <th style="width: 50%;">Deskripsi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($sellerReport['events'] as $event)
                <tr>
                    <td>{{ $event['created_at']->format('d/m H:i') }}</td>
                    <td><strong>{{ $event['seller_name'] }}</strong></td>
                    <td>{{ $event['description'] }}</td>
                </tr>
            @empty
                <tr><td colspan="3" class="text-center text-secondary">Belum ada data pada periode ini</td></tr>
            @endforelse
        </tbody>
    </table>
@endsection
