<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::table('produk')->whereNotIn('stok', ['ready', 'tersedia', 'po', 'habis'])->exists()) {
            throw new RuntimeException('Nilai stok produk di luar status yang dikenal; data perlu diperiksa sebelum normalisasi.');
        }

        if (DB::table('produk')->whereNotIn('status', ['draft', 'pending', 'aktif', 'ditolak'])->exists()) {
            throw new RuntimeException('Nilai status produk di luar status yang dikenal; data perlu diperiksa sebelum normalisasi.');
        }

        if (DB::table('penjual')->whereRaw("LOWER(status_toko) NOT IN ('buka', 'tutup')")->exists()) {
            throw new RuntimeException('Nilai status toko di luar Buka/Tutup; data perlu diperiksa sebelum normalisasi.');
        }

        if (DB::table('ulasan')->where('rating', '<', 1)->orWhere('rating', '>', 5)->exists()) {
            throw new RuntimeException('Rating ulasan di luar rentang 1–5; data perlu diperiksa sebelum normalisasi.');
        }

        // Perluas enum terlebih dahulu agar nilai baru diizinkan
        Schema::table('produk', function (Blueprint $table) {
            $table->enum('stok', ['ready', 'tersedia', 'po', 'habis'])->default('tersedia')->change();
            $table->enum('status', ['draft', 'pending', 'aktif', 'ditolak'])->default('pending')->change();
        });

        DB::table('produk')->where('stok', 'ready')->update(['stok' => 'tersedia']);
        DB::table('penjual')->update(['status_toko' => DB::raw('LOWER(status_toko)')]);

        Schema::table('produk', function (Blueprint $table) {
            $table->enum('stok', ['tersedia', 'po', 'habis'])->default('tersedia')->change();
        });

        Schema::table('penjual', function (Blueprint $table) {
            $table->enum('status_toko', ['buka', 'tutup'])->default('buka')->change();
        });

        Schema::table('ulasan', function (Blueprint $table) {
            $table->unsignedTinyInteger('rating')->change();
        });
    }

    public function down(): void
    {
        // Perluas enum terlebih dahulu agar nilai lama diizinkan sebelum update data
        Schema::table('produk', function (Blueprint $table) {
            $table->enum('stok', ['ready', 'tersedia', 'po', 'habis'])->default('ready')->change();
            $table->enum('status', ['draft', 'pending', 'aktif', 'ditolak'])->default('pending')->change();
        });

        DB::table('produk')->where('stok', 'tersedia')->update(['stok' => 'ready']);
        DB::table('produk')->where('status', 'draft')->update(['status' => 'pending']);
        DB::table('penjual')->update([
            'status_toko' => DB::raw("CASE WHEN status_toko = 'tutup' THEN 'Tutup' ELSE 'Buka' END"),
        ]);

        Schema::table('produk', function (Blueprint $table) {
            $table->enum('stok', ['ready', 'po', 'habis'])->default('ready')->change();
            $table->enum('status', ['pending', 'aktif', 'ditolak'])->default('pending')->change();
        });

        Schema::table('penjual', function (Blueprint $table) {
            $table->string('status_toko', 20)->default('Buka')->change();
        });

        Schema::table('ulasan', function (Blueprint $table) {
            $table->unsignedBigInteger('rating')->change();
        });
    }
};
