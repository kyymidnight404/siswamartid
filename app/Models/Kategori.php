<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Kategori extends Model
{
    use HasFactory;

    /**
     * Nama tabel eksplisit — mencegah Laravel auto-pluralize ke 'kategoris'.
     */
    protected $table = 'kategori';

    /**
     * Kolom yang boleh diisi secara massal.
     *
     * @var list<string>
     */
    protected $fillable = [
        'nama',
        'slug',
        'icon',
    ];

    // ─── Route Binding ───────────────────────────────────────────────────────

    /**
     * Selesaikan model binding baik melalui ID numerik maupun slug teks.
     */
    public function resolveRouteBinding($value, $field = null)
    {
        if ($field) {
            return parent::resolveRouteBinding($value, $field);
        }

        return $this->where('slug', (string) $value)
            ->orWhere('id', is_numeric($value) ? (int) $value : 0)
            ->first();
    }

    // ─── Relasi ───────────────────────────────────────────────────────────────

    /**
     * Satu Kategori memiliki banyak Produk.
     */
    public function produk(): HasMany
    {
        return $this->hasMany(Produk::class);
    }
}
