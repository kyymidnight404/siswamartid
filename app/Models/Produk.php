<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Produk extends Model
{
    use HasFactory;

    /**
     * Nama tabel eksplisit — mencegah Laravel auto-pluralize ke 'produks'.
     */
    protected $table = 'produk';

    /**
     * Kolom yang boleh diisi secara massal.
     *
     * @var list<string>
     */
    protected $fillable = [
        'penjual_id',
        'kategori_id',
        'nama',
        'slug',
        'deskripsi',
        'harga',
        'stok',
        'label_promosi',
        'foto_utama',
        'status',
        'approved_at',
        'alasan_ditolak',
        'pending_action',
        'pending_changes',
        'views',
    ];

    /**
     * Cast tipe data kolom.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'harga' => 'decimal:2',
            'pending_changes' => 'array',
            'approved_at' => 'datetime',
        ];
    }

    // ─── Relasi ───────────────────────────────────────────────────────────────

    /**
     * Produk dimiliki oleh satu Penjual.
     */
    public function penjual(): BelongsTo
    {
        return $this->belongsTo(Penjual::class);
    }

    /**
     * Produk termasuk dalam satu Kategori.
     */
    public function kategori(): BelongsTo
    {
        return $this->belongsTo(Kategori::class);
    }

    /**
     * Satu Produk memiliki banyak Ulasan.
     */
    public function ulasan(): HasMany
    {
        return $this->hasMany(Ulasan::class);
    }

    /**
     * Satu Produk memiliki banyak log interaksi/views.
     */
    public function viewsLogs(): HasMany
    {
        return $this->hasMany(ProdukViewLog::class);
    }

    /**
     * Rata-rata rating dari ulasan publik (aktif/disetujui/disensor).
     */
    public function getAverageRatingAttribute(): float
    {
        return (float) $this->ulasan()->publicVisible()->avg('rating') ?: 0.0;
    }

    /**
     * Jumlah ulasan publik (aktif/disetujui/disensor).
     */
    public function getReviewCountAttribute(): int
    {
        return $this->ulasan()->publicVisible()->count();
    }

    /**
     * Scope produk yang boleh tampil di publik:
     * status = 'aktif' dan akun user penjualnya aktif (is_active = true).
     */
    public function scopePublicVisible($query)
    {
        return $query->where('status', 'aktif')
            ->whereHas('penjual.user', function ($q) {
                $q->where('is_active', true);
            });
    }
}
