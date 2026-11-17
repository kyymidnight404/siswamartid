<?php

use App\Models\Penjual;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasColumn('penjual', 'slug')) {
            Schema::table('penjual', function (Blueprint $table) {
                $table->string('slug')->nullable()->after('nama_toko');
            });
        }

        $usedSlugs = [];
        $sellers = DB::table('penjual')->orderBy('id')->get(['id', 'nama_toko', 'slug']);

        foreach ($sellers as $seller) {
            $base = Str::slug((string) $seller->nama_toko) ?: 'toko';
            if (in_array($base, Penjual::RESERVED_SLUGS, true)) {
                $base .= '-toko';
            }

            $slug = filled($seller->slug) ? (string) $seller->slug : $base;
            if (in_array($slug, Penjual::RESERVED_SLUGS, true)) {
                $slug .= '-toko';
            }

            $candidate = $slug;
            $suffix = 1;
            while (in_array($candidate, $usedSlugs, true) || in_array($candidate, Penjual::RESERVED_SLUGS, true)) {
                $candidate = $base . '-' . $suffix++;
            }

            $usedSlugs[] = $candidate;

            if ($seller->slug !== $candidate) {
                DB::table('penjual')->where('id', $seller->id)->update(['slug' => $candidate]);
            }
        }

        Schema::table('penjual', function (Blueprint $table) {
            $table->string('slug')->nullable(false)->unique('penjual_slug_unique')->change();
        });

        Schema::table('produk', function (Blueprint $table) {
            $table->dropUnique('produk_slug_unique');
            $table->unique(['penjual_id', 'slug'], 'produk_penjual_slug_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('produk', function (Blueprint $table) {
            $table->dropUnique('produk_penjual_slug_unique');
            $table->unique('slug', 'produk_slug_unique');
        });

        Schema::table('penjual', function (Blueprint $table) {
            $table->dropUnique('penjual_slug_unique');
            $table->dropColumn('slug');
        });
    }
};
