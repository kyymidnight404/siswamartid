<?php

use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Penjual\DashboardController as PenjualDashboardController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

// ─── Halaman Publik ───────────────────────────────────────────────────────────
Route::get('/', [\App\Http\Controllers\Public\HomeController::class, 'index'])->name('public.home');

Route::get('/kategori', [\App\Http\Controllers\Public\KategoriController::class, 'index'])->name('public.kategori.index');
Route::get('/kategori/{kategori:slug}', [\App\Http\Controllers\Public\KategoriController::class, 'show'])->name('public.kategori.show');

Route::get('/produk', [\App\Http\Controllers\Public\ProdukController::class, 'index'])->name('public.produk.index');
Route::get('/produk/{produk:slug}', [\App\Http\Controllers\Public\ProdukController::class, 'show'])
    ->name('public.produk.show')
    ->middleware('throttle:60,1');
Route::get('/produk/{produk:slug}/whatsapp', [\App\Http\Controllers\Public\ProdukController::class, 'trackWhatsapp'])
    ->name('public.produk.whatsapp')
    ->middleware('throttle:30,1');

Route::view('/cara-pemesanan', 'public.cara-pemesanan')->name('public.cara_pemesanan');
Route::view('/tentang-kami', 'public.tentang-kami')->name('public.tentang_kami');

// Submit ulasan dengan throttle (3 request / 1 menit)
Route::post('/produk/{produk:slug}/ulasan', [\App\Http\Controllers\Public\UlasanController::class, 'store'])
    ->name('public.ulasan.store')
    ->middleware('throttle:3,1');

// ─── Admin Area ───────────────────────────────────────────────────────────────
Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin'])->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
    Route::post('/notifikasi/baca-semua', [\App\Http\Controllers\Admin\NotificationController::class, 'markAllRead'])->name('notifications.read_all');
    Route::get('/notifikasi/poll', [\App\Http\Controllers\Admin\NotificationController::class, 'poll'])->name('notifications.poll');
    Route::post('/notifikasi/{notification}/baca', [\App\Http\Controllers\Admin\NotificationController::class, 'markRead'])->name('notifications.read');

    // Laporan Agregat Admin
    Route::get('/laporan', [\App\Http\Controllers\Admin\LaporanController::class, 'index'])->name('laporan.index');
    Route::get('/laporan/produk', [\App\Http\Controllers\Admin\LaporanController::class, 'produk'])->name('laporan.produk');
    Route::get('/laporan/ulasan', [\App\Http\Controllers\Admin\LaporanController::class, 'ulasan'])->name('laporan.ulasan');
    Route::get('/laporan/penjual/pdf', [\App\Http\Controllers\Admin\LaporanController::class, 'penjualPdf'])->name('laporan.penjual.pdf');
    Route::get('/laporan/ulasan/pdf', [\App\Http\Controllers\Admin\LaporanController::class, 'ulasanPdf'])->name('laporan.ulasan.pdf');
    Route::get('/laporan/penjual/sheet', [\App\Http\Controllers\Admin\LaporanController::class, 'penjualSheet'])->name('laporan.penjual.sheet');
    Route::get('/laporan/ulasan/sheet', [\App\Http\Controllers\Admin\LaporanController::class, 'ulasanSheet'])->name('laporan.ulasan.sheet');
    Route::get('/laporan/pdf', [\App\Http\Controllers\Admin\LaporanController::class, 'pdf'])->name('laporan.pdf');

    // Manajemen Penjual
    Route::resource('penjual', \App\Http\Controllers\Admin\PenjualController::class)
        ->parameters(['penjual' => 'penjual']);
    Route::patch('penjual/{penjual}/toggle-status', [\App\Http\Controllers\Admin\PenjualController::class, 'toggleStatus'])
        ->name('penjual.toggleStatus');

    // Manajemen Kategori
    Route::resource('categories', \App\Http\Controllers\Admin\KategoriController::class)->only(['index', 'store', 'update', 'destroy']);

    // Moderasi Produk
    Route::get('produk', [\App\Http\Controllers\Admin\ProdukController::class, 'index'])->name('produk.index');
    Route::get('produk/{produk}', [\App\Http\Controllers\Admin\ProdukController::class, 'show'])->name('produk.show');
    Route::patch('produk/{produk}/update-status', [\App\Http\Controllers\Admin\ProdukController::class, 'updateStatus'])->name('produk.updateStatus');
    Route::delete('produk/{produk}', [\App\Http\Controllers\Admin\ProdukController::class, 'destroy'])->name('produk.destroy');

    // Kelola Komentar & Auto Moderasi
    Route::get('komentar', [\App\Http\Controllers\Admin\KomentarController::class, 'index'])->name('komentar.index');
    Route::delete('komentar/{ulasan}', [\App\Http\Controllers\Admin\KomentarController::class, 'destroy'])->name('komentar.destroy');

    // Alias untuk ulasan.index
    Route::get('ulasan', [\App\Http\Controllers\Admin\KomentarController::class, 'index'])->name('ulasan.index');
});

// ─── Penjual Area ─────────────────────────────────────────────────────────────
Route::prefix('penjual')->name('penjual.')->middleware(['auth', 'penjual'])->group(function () {
    Route::get('/dashboard', [PenjualDashboardController::class, 'index'])->name('dashboard');
    Route::patch('/status-toko', [PenjualDashboardController::class, 'toggleStatusToko'])->name('status_toko.toggle');
    Route::get('/laporan', [\App\Http\Controllers\Penjual\LaporanController::class, 'index'])->name('laporan');
    Route::get('/laporan/pdf', [\App\Http\Controllers\Penjual\LaporanController::class, 'pdf'])->name('laporan.pdf');

    // Manajemen Produk
    Route::resource('produk', \App\Http\Controllers\Penjual\ProdukController::class)
        ->parameters(['produk' => 'produk']);
});

// ─── Profile (Breeze default, auth only) ─────────────────────────────────────
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
