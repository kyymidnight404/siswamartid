<?php

namespace App\Services;

use App\Models\Produk;
use App\Models\ProdukViewLog;

class WhatsappUrlService
{
    /**
     * Buat URL WhatsApp dan catat interaksi log untuk produk tertentu.
     *
     * @return array{
     *     success: bool,
     *     whatsapp_url?: string,
     *     phone?: string,
     *     message?: string,
     *     store_closed?: bool
     * }
     */
    public function generateForProduct(Produk $produk, bool $recordLog = true): array
    {
        $rawNumber = $produk->penjual?->nomor_whatsapp ?? '';
        $cleanNumber = preg_replace('/[^0-9]/', '', (string) $rawNumber);

        if (empty($cleanNumber)) {
            return [
                'success' => false,
                'message' => 'Nomor kontak WhatsApp penjual belum tersedia.',
            ];
        }

        if (str_starts_with($cleanNumber, '0')) {
            $cleanNumber = '62' . substr($cleanNumber, 1);
        }

        if ($recordLog) {
            try {
                ProdukViewLog::create([
                    'produk_id' => $produk->id,
                    'type'      => 'whatsapp',
                ]);
            } catch (\Throwable $e) {
                // Safe fallback
            }
        }

        $isClosed = ($produk->penjual?->status_toko === 'tutup');
        $prefixNotice = $isClosed 
            ? "[Pemberitahuan: Toko sedang tutup, balasan mungkin tertunda]\n\n" 
            : '';

        if ($produk->stok === 'po') {
            $message = $prefixNotice . "Halo, saya tertarik untuk Pre-Order produk:\n*" . $produk->nama . "*\n\nBisa diinformasikan perkiraan jadwal dan cara pemesanannya?";
        } elseif ($produk->stok === 'habis') {
            $message = $prefixNotice . "Halo, saya tertarik dengan produk:\n*" . $produk->nama . "*\n\nApakah ada informasi kapan produk ini akan tersedia kembali?";
        } else {
            $message = $prefixNotice . "Halo, saya tertarik dengan produk:\n*" . $produk->nama . "*\n\nApakah produk ini masih tersedia?";
        }

        $url = 'https://wa.me/' . $cleanNumber . '?text=' . rawurlencode($message);

        return [
            'success'      => true,
            'whatsapp_url' => $url,
            'phone'        => $cleanNumber,
            'message'      => $message,
            'store_closed' => $isClosed,
        ];
    }
}
