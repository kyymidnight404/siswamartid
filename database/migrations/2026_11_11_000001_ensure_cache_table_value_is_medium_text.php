<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Ensure the cache table value column uses MEDIUMTEXT (16MB) instead of TEXT (64KB)
     * to prevent SQLSTATE[22001] data truncation when storing large caches such as bad words dictionary.
     */
    public function up(): void
    {
        if (Schema::hasTable('cache')) {
            $driver = Schema::getConnection()->getDriverName();
            if ($driver === 'mysql') {
                DB::statement('ALTER TABLE `cache` MODIFY `value` MEDIUMTEXT NOT NULL');
            } elseif ($driver === 'pgsql') {
                DB::statement('ALTER TABLE "cache" ALTER COLUMN "value" TYPE TEXT');
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No need to revert to smaller capacity TEXT
    }
};
