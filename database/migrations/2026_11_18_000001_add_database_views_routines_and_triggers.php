<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Tambahkan SQL View, Stored Function, Stored Procedure, dan Trigger MySQL/MariaDB.
     */
    public function up(): void
    {
        if (! in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
            return;
        }

        $this->dropObjects();

        DB::unprepared(<<<'ENDSQL'
CREATE OR REPLACE SQL SECURITY INVOKER VIEW v_katalog_produk_aktif AS
SELECT
    p.id,
    p.penjual_id,
    p.kategori_id,
    p.nama,
    p.slug,
    p.harga,
    p.stok,
    p.label_promosi,
    p.views,
    p.approved_at,
    pj.nama_toko,
    pj.slug AS toko_slug,
    pj.status_toko,
    k.nama AS nama_kategori,
    k.slug AS kategori_slug,
    COALESCE(u_stats.jumlah_ulasan, 0) AS jumlah_ulasan,
    COALESCE(u_stats.rata_rata_rating, 0.00) AS rata_rata_rating,
    p.created_at,
    p.updated_at
FROM produk p
INNER JOIN penjual pj ON pj.id = p.penjual_id
INNER JOIN users usr ON usr.id = pj.user_id
INNER JOIN kategori k ON k.id = p.kategori_id
LEFT JOIN (
    SELECT
        u.produk_id,
        COUNT(*) AS jumlah_ulasan,
        ROUND(AVG(u.rating), 2) AS rata_rata_rating
    FROM ulasan u
    WHERE (
        u.moderation_status = 'approved'
        AND (u.status IS NULL OR u.status NOT IN ('disembunyikan', 'ditolak', 'dihapus', 'rejected'))
    ) OR (
        u.moderation_status IS NULL
        AND u.status IN ('aktif', 'disetujui', 'disensor')
    )
    GROUP BY u.produk_id
) u_stats ON u_stats.produk_id = p.id
WHERE p.status = 'aktif'
  AND usr.is_active = 1
ENDSQL
        );

        DB::unprepared(<<<'ENDSQL'
CREATE FUNCTION fn_hitung_rating_produk(p_produk_id BIGINT UNSIGNED)
RETURNS DECIMAL(3,2)
READS SQL DATA
SQL SECURITY INVOKER
BEGIN
    DECLARE v_avg DECIMAL(3,2) DEFAULT 0.00;

    SELECT COALESCE(ROUND(AVG(u.rating), 2), 0.00)
    INTO v_avg
    FROM ulasan u
    WHERE u.produk_id = p_produk_id
      AND (
          (
              u.moderation_status = 'approved'
              AND (u.status IS NULL OR u.status NOT IN ('disembunyikan', 'ditolak', 'dihapus', 'rejected'))
          )
          OR (
              u.moderation_status IS NULL
              AND u.status IN ('aktif', 'disetujui', 'disensor')
          )
      );

    RETURN v_avg;
END
ENDSQL
        );

        DB::unprepared(<<<'ENDSQL'
CREATE PROCEDURE sp_rekap_toko(IN p_penjual_id BIGINT UNSIGNED)
READS SQL DATA
SQL SECURITY INVOKER
BEGIN
    SELECT
        pj.id AS penjual_id,
        pj.nama_toko,
        pj.slug AS toko_slug,
        COALESCE(ps.total_produk, 0) AS total_produk,
        COALESCE(ps.total_produk_aktif, 0) AS total_produk_aktif,
        COALESCE(ps.total_views, 0) AS total_views,
        COALESCE(us.rata_rata_rating, 0.00) AS rata_rata_rating
    FROM penjual pj
    LEFT JOIN (
        SELECT
            penjual_id,
            COUNT(*) AS total_produk,
            SUM(CASE WHEN status = 'aktif' THEN 1 ELSE 0 END) AS total_produk_aktif,
            SUM(views) AS total_views
        FROM produk
        WHERE penjual_id = p_penjual_id
        GROUP BY penjual_id
    ) ps ON ps.penjual_id = pj.id
    LEFT JOIN (
        SELECT
            pr.penjual_id,
            ROUND(AVG(u.rating), 2) AS rata_rata_rating
        FROM ulasan u
        INNER JOIN produk pr ON pr.id = u.produk_id
        WHERE pr.penjual_id = p_penjual_id
          AND (
              (
                  u.moderation_status = 'approved'
                  AND (u.status IS NULL OR u.status NOT IN ('disembunyikan', 'ditolak', 'dihapus', 'rejected'))
              )
              OR (
                  u.moderation_status IS NULL
                  AND u.status IN ('aktif', 'disetujui', 'disensor')
              )
          )
        GROUP BY pr.penjual_id
    ) us ON us.penjual_id = pj.id
    WHERE pj.id = p_penjual_id;
END
ENDSQL
        );

        DB::unprepared(<<<'ENDSQL'
CREATE TRIGGER trg_produk_set_approved_at
BEFORE UPDATE ON produk
FOR EACH ROW
BEGIN
    IF NEW.status = 'aktif' AND NEW.approved_at IS NULL THEN
        SET NEW.approved_at = CURRENT_TIMESTAMP;
    END IF;
END
ENDSQL
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
            return;
        }

        $this->dropObjects();
    }

    private function dropObjects(): void
    {
        DB::unprepared('DROP TRIGGER IF EXISTS trg_produk_set_approved_at');
        DB::unprepared('DROP PROCEDURE IF EXISTS sp_rekap_toko');
        DB::unprepared('DROP FUNCTION IF EXISTS fn_hitung_rating_produk');
        DB::unprepared('DROP VIEW IF EXISTS v_katalog_produk_aktif');
    }
};
