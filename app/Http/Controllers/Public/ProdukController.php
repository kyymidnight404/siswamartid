<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Produk;
use App\Services\WhatsappUrlService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class ProdukController extends Controller
{
    /**
     * Tampilkan detail produk.
     * Hanya produk aktif dari penjual aktif yang dapat diakses; increment views untuk produk aktif.
     */
    public function show(Request $request, Produk $produk, WhatsappUrlService $waService): View
    {
        abort_if($produk->status !== 'aktif' || ! ($produk->penjual?->user?->is_active), 404);

        $viewSessionKey = 'viewed_produk_' . $produk->id;
        if (! $request->session()->has($viewSessionKey)) {
            $produk->increment('views');
            try {
                \App\Models\ProdukViewLog::create([
                    'produk_id' => $produk->id,
                    'type'      => 'view',
                ]);
            } catch (\Throwable $e) {
                // Safe fallback
            }
            $request->session()->put($viewSessionKey, now()->timestamp);
        }

        $produk->load(['kategori', 'penjual.user']);

        // Ulasan publik (aktif/disetujui/disensor)
        $ulasan = $produk->ulasan()
            ->publicVisible()
            ->latest()
            ->get();

        // Produk Terkait (kategori sama, aktif & penjual aktif, tidak termasuk diri sendiri)
        $produkTerkait = Produk::publicVisible()
            ->with(['kategori', 'penjual'])
            ->withAvg(['ulasan' => fn($q) => $q->publicVisible()], 'rating')
            ->withCount(['ulasan' => fn($q) => $q->publicVisible()])
            ->where('kategori_id', $produk->kategori_id)
            ->where('id', '!=', $produk->id)
            ->latest()
            ->limit(4)
            ->get();

        $waData = $waService->generateForProduct($produk, false);

        return view('public.produk.show', compact('produk', 'ulasan', 'produkTerkait', 'waData'));
    }

    /**
     * Catat interaksi klik WhatsApp dan alihkan ke WhatsApp penjual.
     * Jika request AJAX / JSON, kembalikan JSON agar tidak memicu CORS error redirect ke wa.me.
     */
    public function trackWhatsapp(Request $request, Produk $produk, WhatsappUrlService $waService): RedirectResponse|JsonResponse
    {
        abort_if($produk->status !== 'aktif' || ! ($produk->penjual?->user?->is_active), 404);

        $result = $waService->generateForProduct($produk, true);
        if (! $result['success']) {
            if ($request->expectsJson() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $result['message']], 400);
            }
            return back()->with('error', $result['message']);
        }

        if ($request->expectsJson() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'whatsapp_url' => $result['whatsapp_url'],
            ]);
        }

        return redirect()->away($result['whatsapp_url']);
    }
}
