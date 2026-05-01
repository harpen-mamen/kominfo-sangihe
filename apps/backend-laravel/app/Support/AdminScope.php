<?php

namespace App\Support;

use App\Models\Berita;
use App\Models\Desa;
use App\Models\DokumenPublik;
use App\Models\FiturPeta;
use App\Models\IndikatorData;
use App\Models\Kegiatan;
use App\Models\NilaiDataMentah;
use App\Models\PengajuanData;
use App\Models\RingkasanStatistik;
use App\Models\SumberData;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class AdminScope
{
    /**
     * @return array<int, string>
     */
    public static function roleNames(User $user): array
    {
        $roles = [];

        if (method_exists($user, 'getRoleNames')) {
            try {
                $roles = array_values(array_filter($user->getRoleNames()->all()));
            } catch (\Throwable) {
                $roles = [];
            }
        }

        if (filled($user->role)) {
            $roles[] = (string) $user->role;
        }

        return array_values(array_unique(array_map('strval', array_filter($roles))));
    }

    public static function hasRole(User $user, string|array $roles): bool
    {
        $expected = array_map('strval', (array) $roles);
        $actual = self::roleNames($user);

        return count(array_intersect($expected, $actual)) > 0;
    }

    public static function primaryRole(User $user): string
    {
        $roles = self::roleNames($user);

        foreach (['super_admin', 'admin_kominfo', 'admin_kecamatan', 'admin_opd'] as $role) {
            if (in_array($role, $roles, true)) {
                return $role;
            }
        }

        return $roles[0] ?? 'admin_kecamatan';
    }

    public static function workspaceKey(User $user): string
    {
        return match (self::primaryRole($user)) {
            'super_admin', 'admin_kominfo' => 'kominfo',
            'admin_kecamatan' => 'kecamatan',
            'admin_opd' => 'opd',
            default => 'operator',
        };
    }

    public static function scopeLabel(User $user): string
    {
        $user->loadMissing(['kecamatan', 'opd']);

        return match (self::workspaceKey($user)) {
            'opd' => $user->opd?->nama ?? 'OPD',
            'kecamatan' => $user->kecamatan?->nama ?? 'Kecamatan',
            'kominfo' => 'Kabupaten Kepulauan Sangihe',
            default => 'Unit Pengguna',
        };
    }

    public static function isKominfo(User $user): bool
    {
        return self::workspaceKey($user) === 'kominfo';
    }

    public static function isSubdistrict(User $user): bool
    {
        return self::workspaceKey($user) === 'kecamatan';
    }

    public static function isDepartment(User $user): bool
    {
        return self::workspaceKey($user) === 'opd';
    }

    public static function applyScope(Builder $query, User $user, ?string $kecamatanColumn = 'kecamatan_id', ?string $opdColumn = 'opd_id'): Builder
    {
        return match (self::workspaceKey($user)) {
            'kecamatan' => filled($kecamatanColumn) && $user->kecamatan_id
                ? $query->where($kecamatanColumn, $user->kecamatan_id)
                : $query->whereRaw('1 = 0'),
            'opd' => filled($opdColumn) && $user->opd_id
                ? $query->where($opdColumn, $user->opd_id)
                : $query->whereRaw('1 = 0'),
            default => $query,
        };
    }

    public static function pengajuanDataQuery(User $user): Builder
    {
        $query = PengajuanData::query();

        return match (self::workspaceKey($user)) {
            'kecamatan' => $user->kecamatan_id
                ? $query->where('kecamatan_id', $user->kecamatan_id)
                : $query->whereRaw('1 = 0'),
            'opd' => $user->opd_id
                ? $query->where('opd_id', $user->opd_id)
                : $query->whereRaw('1 = 0'),
            default => $query,
        };
    }

    public static function beritaQuery(User $user): Builder
    {
        return self::applyScope(Berita::query(), $user, 'kecamatan_id', 'opd_id');
    }

    public static function kegiatanQuery(User $user): Builder
    {
        return self::applyScope(Kegiatan::query(), $user, 'kecamatan_id', 'opd_id');
    }

    public static function desaQuery(User $user): Builder
    {
        $query = Desa::query();

        return self::isSubdistrict($user) && $user->kecamatan_id
            ? $query->where('kecamatan_id', $user->kecamatan_id)
            : $query;
    }

    public static function sumberDataQuery(User $user): Builder
    {
        return self::applyScope(SumberData::query(), $user, 'kecamatan_id', 'opd_id');
    }

    public static function indikatorDataQuery(User $user, bool $forInput = false): Builder
    {
        $query = IndikatorData::query();

        return match (self::workspaceKey($user)) {
            'kecamatan' => $query
                ->where('aktif', true)
                ->when($forInput, fn (Builder $builder): Builder => $builder->where('boleh_diinput_kecamatan', true)),
            'opd' => $user->opd_id
                ? $query
                    ->where('opd_id', $user->opd_id)
                    ->when($forInput, fn (Builder $builder): Builder => $builder
                        ->where('aktif', true)
                        ->where('boleh_diinput_opd', true))
                : $query->whereRaw('1 = 0'),
            default => $query,
        };
    }

    public static function canInputIndicator(User $user, IndikatorData $indikator): bool
    {
        return match (self::workspaceKey($user)) {
            'kecamatan' => (bool) $indikator->aktif && (bool) $indikator->boleh_diinput_kecamatan,
            'opd' => (bool) $indikator->aktif
                && (bool) $indikator->boleh_diinput_opd
                && filled($user->opd_id)
                && (int) $indikator->opd_id === (int) $user->opd_id,
            default => true,
        };
    }

    public static function fiturPetaQuery(User $user): Builder
    {
        return self::applyScope(FiturPeta::query(), $user, 'kecamatan_id', 'opd_id');
    }

    public static function ringkasanStatistikQuery(User $user): Builder
    {
        $query = RingkasanStatistik::query()->with(['indikatorData', 'periodeData', 'kecamatan', 'desa', 'opd']);

        return match (self::workspaceKey($user)) {
            'kecamatan' => $user->kecamatan_id
                ? $query->where('kecamatan_id', $user->kecamatan_id)
                : $query->whereRaw('1 = 0'),
            'opd' => $user->opd_id
                ? $query->where('opd_id', $user->opd_id)
                : $query->whereRaw('1 = 0'),
            default => $query,
        };
    }

    public static function dokumenPublikQuery(User $user): Builder
    {
        $query = DokumenPublik::query()->with(['kecamatan', 'desa', 'opd', 'pengirim', 'peninjau']);

        return match (self::workspaceKey($user)) {
            'kecamatan' => $user->kecamatan_id
                ? $query->where('kecamatan_id', $user->kecamatan_id)
                : $query->whereRaw('1 = 0'),
            'opd' => $user->opd_id
                ? $query->where('opd_id', $user->opd_id)
                : $query->whereRaw('1 = 0'),
            default => $query,
        };
    }

    public static function nilaiDataMentahQuery(User $user): Builder
    {
        $query = NilaiDataMentah::query()
            ->with(['pengajuanData', 'desa', 'indikatorData', 'sumberData']);

        return match (self::workspaceKey($user)) {
            'kecamatan' => $user->kecamatan_id
                ? $query
                    ->whereHas('pengajuanData', fn (Builder $builder): Builder => $builder->where('kecamatan_id', $user->kecamatan_id))
                    ->whereHas('indikatorData', fn (Builder $builder): Builder => $builder
                        ->where('aktif', true)
                        ->where('boleh_diinput_kecamatan', true))
                : $query->whereRaw('1 = 0'),
            'opd' => $user->opd_id
                ? $query
                    ->whereHas('pengajuanData', fn (Builder $builder): Builder => $builder->where('opd_id', $user->opd_id))
                    ->whereHas('indikatorData', fn (Builder $builder): Builder => $builder
                        ->where('opd_id', $user->opd_id)
                        ->where('boleh_diinput_opd', true))
                : $query->whereRaw('1 = 0'),
            default => $query,
        };
    }
}
