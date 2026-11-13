<?php

namespace App\Services;

use App\Models\Penjual;
use Carbon\Carbon;
use Illuminate\Support\Str;

class PdfFilenameService
{
    /**
     * Instansiasi cepat / factory method.
     */
    public static function make(): self
    {
        return app(self::class);
    }

    /**
     * Konversi nama toko atau instance Penjual menjadi slug yang aman, bersih, dan ringkas.
     */
    public function slugifyStore(string|Penjual|null $store): string
    {
        $name = $store instanceof Penjual ? $store->nama_toko : (string) $store;
        $slug = Str::slug($name ?: 'toko');

        if ($slug === '') {
            $slug = 'toko';
        }

        // Batasi panjang slug agar nama file tidak terlalu panjang (maksimal 50 karakter)
        $trimmed = substr($slug, 0, 50);

        return rtrim($trimmed, '-');
    }

    /**
     * Standarisasi Carbon datetime ke timezone Asia/Jakarta (default waktu saat ini).
     */
    public function resolveDate(?Carbon $date = null): Carbon
    {
        return ($date ?? Carbon::now('Asia/Jakarta'))->copy()->setTimezone('Asia/Jakarta');
    }

    /**
     * A. Laporan Detail Penjual
     * Format: detail-penjual-{slug-toko}-{tanggal}.pdf
     * Contoh: detail-penjual-edmunds-hearth-20261106.pdf
     */
    public function detailPenjual(string|Penjual|null $store, ?Carbon $date = null): string
    {
        $slug = $this->slugifyStore($store);
        $dateStr = $this->resolveDate($date)->format('Ymd');

        return "detail-penjual-{$slug}-{$dateStr}.pdf";
    }

    /**
     * B. Rekap Penjual
     * Format: rekap-penjual-{slug-toko}-{tanggal}.pdf
     * Contoh: rekap-penjual-the-noble-brewer-20261106.pdf
     */
    public function rekapPenjual(string|Penjual|null $store, ?Carbon $date = null): string
    {
        $slug = $this->slugifyStore($store);
        $dateStr = $this->resolveDate($date)->format('Ymd');

        return "rekap-penjual-{$slug}-{$dateStr}.pdf";
    }

    /**
     * C. Rekap Ulasan
     * Format: rekap-ulasan-{periode}-{tanggal}.pdf
     * Contoh: rekap-ulasan-weekly-20261106.pdf
     */
    public function rekapUlasan(string $period = 'weekly', ?Carbon $date = null): string
    {
        $periodSlug = strtolower(trim($period)) === 'monthly' ? 'monthly' : 'weekly';
        $dateStr = $this->resolveDate($date)->format('Ymd');

        return "rekap-ulasan-{$periodSlug}-{$dateStr}.pdf";
    }

    /**
     * D & E. Rekap Kinerja (Mingguan / Bulanan)
     * D. Rekap Kinerja Mingguan: rekap-kinerja-weekly-{tanggal}.pdf (Contoh: rekap-kinerja-weekly-20261106.pdf)
     * E. Rekap Kinerja Bulanan:  rekap-kinerja-monthly-{tahunbulan}.pdf (Contoh: rekap-kinerja-monthly-202611.pdf)
     */
    public function rekapKinerja(string $period = 'weekly', ?Carbon $date = null): string
    {
        $resolvedDate = $this->resolveDate($date);
        $periodLower = strtolower(trim($period));

        if ($periodLower === 'monthly') {
            $yearMonth = $resolvedDate->format('Ym');

            return "rekap-kinerja-monthly-{$yearMonth}.pdf";
        }

        $dateStr = $resolvedDate->format('Ymd');

        return "rekap-kinerja-weekly-{$dateStr}.pdf";
    }

    /**
     * F. Rekap Produk
     * Format: rekap-produk-{slug-toko}-{tanggal}.pdf
     * Contoh: rekap-produk-edmunds-hearth-20261106.pdf
     */
    public function rekapProduk(string|Penjual|null $store, ?Carbon $date = null): string
    {
        $slug = $this->slugifyStore($store);
        $dateStr = $this->resolveDate($date)->format('Ymd');

        return "rekap-produk-{$slug}-{$dateStr}.pdf";
    }
}
