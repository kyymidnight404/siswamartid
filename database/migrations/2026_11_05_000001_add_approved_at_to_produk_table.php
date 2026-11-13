<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('produk') && !Schema::hasColumn('produk', 'approved_at')) {
            Schema::table('produk', function (Blueprint $table) {
                $table->timestamp('approved_at')->nullable()->after('status');
            });

            // Backfill untuk produk yang sudah berstatus 'aktif':
            // set approved_at = updated_at (atau created_at jika updated_at kosong)
            DB::table('produk')
                ->where('status', 'aktif')
                ->whereNull('approved_at')
                ->update([
                    'approved_at' => DB::raw('COALESCE(updated_at, created_at, NOW())')
                ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('produk') && Schema::hasColumn('produk', 'approved_at')) {
            Schema::table('produk', function (Blueprint $table) {
                $table->dropColumn('approved_at');
            });
        }
    }
};
