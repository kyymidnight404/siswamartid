<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Support\Str;

class Penjual extends Model
{
    use HasFactory;

    /**
     * Prefix URL root yang dicadangkan agar tidak bentrok dengan rute /{toko}/{produk}.
     *
     * @var list<string>
     */
    public const RESERVED_SLUGS = [
        'admin',
        'penjual',
        'kategori',
        'produk',
        'cara-pemesanan',
        'tentang-kami',
        'sitemap.xml',
        'login',
        'logout',
        'register',
        'forgot-password',
        'reset-password',
        'verify-email',
        'confirm-password',
        'password',
        'email',
        'auth',
        'profile',
        'preview',
        'api',
        'storage',
        'build',
        'css',
        'fonts',
        'images',
        'mobile',
    ];

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
        'slug',
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

    protected static function booted(): void
    {
        static::creating(function (Penjual $penjual) {
            if (blank($penjual->slug)) {
                $penjual->slug = static::generateUniqueSlug((string) $penjual->nama_toko);
            }
        });

        static::updating(function (Penjual $penjual) {
            if ($penjual->isDirty('nama_toko') && ! $penjual->isDirty('slug')) {
                $penjual->slug = static::generateUniqueSlug((string) $penjual->nama_toko, $penjual->id);
            } elseif (blank($penjual->slug)) {
                $penjual->slug = static::generateUniqueSlug((string) $penjual->nama_toko, $penjual->id);
            }
        });
    }

    /**
     * Hasilkan slug toko unik dan aman dari tabrakan rute sistem.
     */
    public static function generateUniqueSlug(string $namaToko, ?int $ignoreId = null): string
    {
        $base = Str::slug($namaToko) ?: 'toko';
        if (in_array($base, self::RESERVED_SLUGS, true)) {
            $base .= '-toko';
        }

        $slug = $base;
        $suffix = 1;

        while (
            in_array($slug, self::RESERVED_SLUGS, true)
            || static::where('slug', $slug)
                ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
                ->exists()
        ) {
            $slug = $base . '-' . $suffix++;
        }

        return $slug;
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
     * Petakan child route binding `{produk}` ke relasi singular `produk()`.
     */
    public function childRouteBindingRelationshipName($childType): string
    {
        return $childType === 'produk'
            ? 'produk'
            : parent::childRouteBindingRelationshipName($childType);
    }

    /**
     * Ulasan dari seluruh produk milik penjual ini.
     */
    public function ulasan(): HasManyThrough
    {
        return $this->hasManyThrough(Ulasan::class, Produk::class, 'penjual_id', 'produk_id');
    }
}
