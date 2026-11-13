@extends('exports.pdf.layout')

@section('title', 'Rekap Ulasan — SiswaMart')
@section('page-orientation', 'portrait')

@section('report-title', 'Rekap Kepuasan Ulasan · ' . $periodLabel)
@section('report-subtitle')
    Laporan Agregat Admin SiswaMart &middot; Periode {{ $start->format('d/m/Y') }} – {{ $end->format('d/m/Y') }}
@endsection

@section('period-label', $periodLabel)

@section('content')
    {{-- Summary Grid --}}
    <table class="summary-grid">
        <tr>
            <td style="width: 33.3%;">
                <div class="summary-label">Total Ulasan Diterbitkan</div>
                <div class="summary-value">{{ number_format($reviews->count()) }}</div>
                <div class="summary-note">Ulasan disetujui pada periode ini</div>
            </td>
            <td style="width: 33.3%;">
                <div class="summary-label">Rating Rata-rata</div>
                <div class="summary-value">{{ number_format($averageRating, 2) }} / 5</div>
                <div class="summary-note">Skala kepuasan 1 sampai 5</div>
            </td>
            <td style="width: 33.3%;">
                <div class="summary-label">Distribusi Bintang</div>
                <div style="font-size: 10pt; font-weight: bold; margin-top: 4px;">
                    @foreach($ratingDistribution as $stars => $count)
                        <span style="display: inline-block; margin-right: 12px;">{{ $stars }}★ {{ number_format($count) }}</span>
                    @endforeach
                </div>
            </td>
        </tr>
    </table>

    {{-- Tabel Ulasan Publik --}}
    <div class="section-bar">Daftar Seluruh Ulasan Publik ({{ number_format($reviews->count()) }} Ulasan)</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 14%;">Waktu</th>
                <th style="width: 14%;">Pengulas</th>
                <th style="width: 9%;" class="text-center">Rating</th>
                <th style="width: 22%;">Produk</th>
                <th style="width: 18%;">Penjual / Toko</th>
                <th style="width: 23%;">Komentar</th>
            </tr>
        </thead>
        <tbody>
            @forelse($reviews as $review)
                <tr>
                    <td>{{ $review->created_at?->format('d/m/Y H:i') ?? '—' }}</td>
                    <td><strong>{{ $review->guest_username ?: 'Pengunjung' }}</strong></td>
                    <td class="text-center font-bold">{{ $review->rating }} / 5</td>
                    <td>{{ $review->produk?->nama ?? 'Produk telah dihapus' }}</td>
                    <td class="text-secondary">{{ $review->produk?->penjual?->nama_toko ?? 'Penjual tidak tersedia' }}</td>
                    <td>{{ $review->public_content ?: 'Tidak ada teks ulasan.' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center text-secondary" style="padding: 12px;">
                        Belum ada data pada periode ini
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
@endsection
