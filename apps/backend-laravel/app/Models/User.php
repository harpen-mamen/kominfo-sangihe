<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Support\AdminScope;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements FilamentUser
{
    use HasApiTokens;
    use HasRoles;
    use Notifiable;

    protected $table = 'pengguna';

    protected $fillable = [
        'nama',
        'email',
        'kata_sandi',
        'role',
        'kecamatan_id',
        'opd_id',
        'aktif',
        'login_terakhir_pada',
    ];

    protected $hidden = [
        'kata_sandi',
        'token_pengingat',
    ];

    protected $appends = [
        'name',
    ];

    protected string $guard_name = 'web';

    protected function casts(): array
    {
        return [
            'aktif' => 'boolean',
            'login_terakhir_pada' => 'datetime',
            'kata_sandi' => 'hashed',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (self $user): void {
            $role = AdminScope::primaryRole($user);

            if ($role === 'admin_kecamatan' && blank($user->kecamatan_id)) {
                throw ValidationException::withMessages([
                    'kecamatan_id' => 'Admin kecamatan wajib memiliki kecamatan.',
                ]);
            }

            if ($role === 'admin_opd' && blank($user->opd_id)) {
                throw ValidationException::withMessages([
                    'opd_id' => 'Admin OPD wajib memiliki OPD.',
                ]);
            }
        });

        static::saved(function (self $user): void {
            if (! method_exists($user, 'syncRoles') || ! Schema::hasTable('roles')) {
                return;
            }

            $currentRoles = collect($user->getRoleNames()->all())
                ->filter()
                ->values()
                ->all();

            if (! count($currentRoles) && filled($user->role)) {
                $user->syncRoles([$user->role]);
                $currentRoles = [$user->role];
            }

            $primaryRole = AdminScope::primaryRole($user);

            if (filled($primaryRole) && $user->role !== $primaryRole) {
                $user->forceFill(['role' => $primaryRole])->saveQuietly();
            }
        });
    }

    public function kecamatan(): BelongsTo
    {
        return $this->belongsTo(Kecamatan::class);
    }

    public function opd(): BelongsTo
    {
        return $this->belongsTo(Opd::class);
    }

    public function pengajuanData(): HasMany
    {
        return $this->hasMany(PengajuanData::class, 'dikirim_oleh');
    }

    public function berita(): HasMany
    {
        return $this->hasMany(Berita::class, 'penulis_id');
    }

    public function kegiatan(): HasMany
    {
        return $this->hasMany(Kegiatan::class, 'pembuat_id');
    }

    public function primaryRole(): string
    {
        return AdminScope::primaryRole($this);
    }

    public function isAdminKominfo(): bool
    {
        return AdminScope::isKominfo($this);
    }

    public function isAdminKecamatan(): bool
    {
        return AdminScope::isSubdistrict($this);
    }

    public function isAdminOpd(): bool
    {
        return AdminScope::isDepartment($this);
    }

    public function isDukcapil(): bool
    {
        $this->loadMissing('opd');

        $kode = strtolower((string) $this->opd?->kode);
        $nama = strtolower((string) $this->opd?->nama);

        return $kode === 'dukcapil'
            || str_contains($nama, 'dukcapil')
            || str_contains($nama, 'kependudukan');
    }

    public function workspaceKey(): string
    {
        return AdminScope::workspaceKey($this);
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return $panel->getId() === 'admin'
            && $this->aktif
            && AdminScope::hasRole($this, [
                'super_admin',
                'admin_kominfo',
                'admin_kecamatan',
                'admin_opd',
            ]);
    }

    public function getAuthPassword(): string
    {
        return (string) $this->kata_sandi;
    }

    public function getRememberTokenName(): string
    {
        return 'token_pengingat';
    }

    public function getFilamentName(): string
    {
        return $this->nama;
    }

    public function getNameAttribute(): string
    {
        return (string) ($this->attributes['nama'] ?? '');
    }

    public function getPasswordAttribute(): string
    {
        return (string) ($this->attributes['kata_sandi'] ?? '');
    }

    public function setPasswordAttribute(string $value): void
    {
        $this->attributes['kata_sandi'] = $value;
    }
}
