<?php

namespace App\Exports\Concerns;

use Carbon\Carbon;
use DateTimeInterface;

trait StylesSheet
{
    /** Warna Desain SiswaMart */
    public const COLOR_TITLE_BG   = '1F3550';
    public const COLOR_HEADER_BG  = '3F6A96';
    public const COLOR_ALT_BG     = 'F3F6FB';
    public const COLOR_SECTION_BG = 'E3ECF5';
    public const COLOR_AMBER_BG   = 'FFF0D4';
    public const COLOR_BORDER     = 'D1D5DB';
    public const COLOR_TEXT_MAIN  = '1F3550';
    public const COLOR_TEXT_MUTED = '6B7280';
    public const COLOR_WHITE      = 'FFFFFF';
    public const COLOR_TAB_ACTIVE = '1F3550';
    public const COLOR_TAB_OTHER  = '5B8AB5';

    /**
     * Sanitasi teks pengguna untuk mencegah formula injection (CSV/XLSX).
     */
    public function sanitizeText(?string $value): string
    {
        if ($value === null || $value === '') {
            return '-';
        }

        // Teks yang diawali karakter formula di-escape
        $firstChar = $value[0] ?? '';
        if (in_array($firstChar, ['=', '+', '-', '@', "\t", "\r", "\n"], true)) {
            // Dalam OpenXML inlineStr, nilai tetap teks murni, namun menambahkan prefix kutip ' mencegah formula bila diekspor ke CSV
            return "'" . $value;
        }

        return $value;
    }

    /**
     * Konversi tanggal ke nilai serial Excel asli (sejak 1899-12-30).
     */
    public function toExcelDate(Carbon|DateTimeInterface|string|null $date): ?int
    {
        if ($date === null || $date === '') {
            return null;
        }

        if (is_string($date)) {
            $date = Carbon::parse($date, 'Asia/Jakarta');
        }

        $timestamp = $date->getTimestamp() + $date->getOffset();

        return (int) (floor($timestamp / 86400) + 25569);
    }

    /**
     * Format angka mata uang Rupiah.
     */
    public function formatRupiah(int|float $amount): array
    {
        return [
            'type' => 'currency',
            'value' => (float) $amount,
        ];
    }

    /**
     * Format angka bulat.
     */
    public function formatNumber(int|float $number): array
    {
        return [
            'type' => 'number',
            'value' => (float) $number,
        ];
    }

    /**
     * Format persentase.
     */
    public function formatPercent(float $percent): array
    {
        return [
            'type' => 'percent',
            'value' => round($percent / 100, 4),
        ];
    }

    /**
     * Format tanggal serial Excel.
     */
    public function formatDate(Carbon|DateTimeInterface|string|null $date): array
    {
        $serial = $this->toExcelDate($date);

        return [
            'type' => 'date',
            'value' => $serial,
            'label' => $date instanceof Carbon ? $date->format('d/m/Y') : (string) $date,
        ];
    }
}
