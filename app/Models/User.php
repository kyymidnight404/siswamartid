<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;

#[Fillable(['name', 'username', 'email', 'google_id', 'password', 'role', 'is_active', 'must_change_password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    protected static function booted(): void
    {
        static::creating(function (User $user): void {
            if (! \Illuminate\Support\Facades\Schema::hasColumn('users', 'username')) {
                return;
            }

            if (filled($user->username)) {
                return;
            }

            $identity = Str::before((string) ($user->email ?: $user->name), '@');
            $base = Str::limit(Str::slug($identity, '_') ?: 'user', 70, '');
            $username = $base;
            $suffix = 1;

            while (static::query()->where('username', $username)->exists()) {
                $tail = '_'.$suffix++;
                $username = Str::limit($base, 80 - strlen($tail), '').$tail;
            }

            $user->username = $username;
        });
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'must_change_password' => 'boolean',
        ];
    }

    // ─── Relasi ───────────────────────────────────────────────────────────────

    /**
     * Satu User (role: penjual) memiliki satu profil Penjual.
     */
    public function penjual(): HasOne
    {
        return $this->hasOne(Penjual::class);
    }

    /**
     * Kirim notifikasi pengaturan ulang kata sandi (SiswaMart branded).
     */
    public function sendPasswordResetNotification(#[\SensitiveParameter] $token): void
    {
        $this->notify(new \App\Notifications\ResetPasswordNotification($token));
    }
}
