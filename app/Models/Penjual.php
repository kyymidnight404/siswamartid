<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Penjual extends Model
{
    use HasFactory;

    /**
     * Nama tabel eksplisit — mencegah Laravel auto-pluralize ke 'penjuals'.
     */
    protected $table = 'penjual';

    /**
     * Kolom yang boleh diisi secara massal.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'nama_toko',
        'deskripsi_toko',
        'nomor_whatsapp',
        'lokasi_kelas',
        'foto_toko',
        'status_toko',
        'nisn',
        'alamat',
        'last_login_at',
        'last_activity_at',
        'last_activity_type',
    ];

    protected function casts(): array
    {
        return [
            'last_login_at' => 'datetime',
            'last_activity_at' => 'datetime',
        ];
    }

    // ─── Relasi ───────────────────────────────────────────────────────────────

    /**
     * Penjual dimiliki oleh satu User.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Satu Penjual memiliki banyak Produk.
     */
    public function produk(): HasMany
    {
        return $this->hasMany(Produk::class);
    }

    /**
     * Ulasan dari seluruh produk milik penjual ini.
     */
    public function ulasan(): HasManyThrough
    {
        return $this->hasManyThrough(Ulasan::class, Produk::class, 'penjual_id', 'produk_id');
    }
}
