<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Menambahkan composite index untuk optimasi query katalog dan agregasi ulasan.
     */
    public function up(): void
    {
        Schema::table('produk', function (Blueprint $table) {
            $table->index(['status', 'created_at'], 'idx_produk_status_created');
            $table->index(['status', 'views'], 'idx_produk_status_views');
            $table->index(['status', 'harga'], 'idx_produk_status_harga');
            $table->index(['kategori_id', 'status'], 'idx_produk_kategori_status');
        });

        Schema::table('ulasan', function (Blueprint $table) {
            $table->index(['produk_id', 'moderation_status'], 'idx_ulasan_produk_moderation');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('produk', function (Blueprint $table) {
            $table->dropIndex('idx_produk_status_created');
            $table->dropIndex('idx_produk_status_views');
            $table->dropIndex('idx_produk_status_harga');
            $table->dropIndex('idx_produk_kategori_status');
        });

        Schema::table('ulasan', function (Blueprint $table) {
            $table->dropIndex('idx_ulasan_produk_moderation');
        });
    }
};
