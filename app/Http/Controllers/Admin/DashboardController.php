<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Penjual;
use App\Models\Produk;
use App\Models\Ulasan;
use App\Notifications\AdminActivityNotification;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Tampilkan dashboard Admin.
     */
    public function index(): View
    {
        $totalPenjual = Penjual::count();
        $produkAktif = Produk::where('status', 'aktif')->count();
        $hasPendingAction = Schema::hasColumn('produk', 'pending_action');
        $perluModerasi = Produk::where(function ($q) use ($hasPendingAction) {
            $q->where('status', 'pending');
            if ($hasPendingAction) {
                $q->orWhereNotNull('pending_action');
            }
        })->count();

        return view('admin.dashboard', compact(
            'totalPenjual',
            'produkAktif',
            'perluModerasi'
        ));
    }
}
