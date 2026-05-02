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
use Illuminate\Support\Facades\Schema;

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

        foreach (['super_admin', 'admin_kominfo', 'verifikator_kominfo', 'admin_kecamatan', 'admin_opd'] as $role) {
            if (in_array($role, $roles, true)) {
                return $role;
            }
        }

        return $roles[0] ?? 'admin_kecamatan';
    }

    public static function workspaceKey(User $user): string
    {
        return match (self::primaryRole($user)) {
            'super_admin', 'admin_kominfo', 'verifikator_kominfo' => 'kominfo',
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

    public static function indikatorDataQuery(User $user, bool $forInput = false, ?string $kelompokIndikator = null): Builder
    {
        $query = IndikatorData::query();

        $query = match (self::workspaceKey($user)) {
            'kecamatan' => self::applyKecamatanIndikatorInputScope($query, $forInput),
            'opd' => self::applyOpdIndikatorInputScope($query, $user, $forInput),
            default => self::applyDefaultIndikatorInputScope($query, $forInput),
        };

        if ($forInput) {
            self::applyKelompokIndikatorFilter($query, $kelompokIndikator);
        }

        return $query;
    }

    public static function orderIndikatorQuery(Builder $query): Builder
    {
        if (self::indikatorHasColumn('urutan_tampil')) {
            $query->orderBy('urutan_tampil');
        } elseif (self::indikatorHasColumn('urutan')) {
            $query->orderBy('urutan');
        }

        return $query->orderBy('nama');
    }

    public static function canInputIndicator(User $user, IndikatorData $indikator): bool
    {
        return match (self::workspaceKey($user)) {
            'kecamatan' => self::indicatorBoolean($indikator, 'aktif', true)
                && self::indicatorAllowsKecamatanInput($indikator)
                && self::indicatorAllowsDesaLevel($indikator),
            'opd' => self::indicatorBoolean($indikator, 'aktif', true)
                && self::indicatorAllowsOpdInput($indikator)
                && filled($user->opd_id)
                && self::indicatorBelongsToOpd($indikator, (int) $user->opd_id),
            default => true,
        };
    }

    protected static function applyKecamatanIndikatorInputScope(Builder $query, bool $forInput): Builder
    {
        if (self::indikatorHasColumn('aktif')) {
            $query->where('aktif', true);
        }

        if (! $forInput) {
            return $query;
        }

        if (self::indikatorHasColumn('input_kecamatan')) {
            $query->where('input_kecamatan', true);
        } elseif (self::indikatorHasColumn('boleh_diinput_kecamatan')) {
            $query->where('boleh_diinput_kecamatan', true);
        }

        if (self::indikatorHasColumn('level_input')) {
            $query->where(function (Builder $builder): void {
                $builder
                    ->whereNull('level_input')
                    ->orWhere('level_input', 'desa');
            });
        }

        return $query;
    }

    protected static function applyOpdIndikatorInputScope(Builder $query, User $user, bool $forInput): Builder
    {
        if (blank($user->opd_id)) {
            return $query->whereRaw('1 = 0');
        }

        if (self::indikatorHasColumn('opd_id') || self::indikatorHasColumn('opd_pembina_id')) {
            $query->where(function (Builder $builder) use ($user): void {
                $hasFilter = false;

                if (self::indikatorHasColumn('opd_id')) {
                    $builder->where('opd_id', $user->opd_id);
                    $hasFilter = true;
                }

                if (self::indikatorHasColumn('opd_pembina_id')) {
                    $hasFilter
                        ? $builder->orWhere('opd_pembina_id', $user->opd_id)
                        : $builder->where('opd_pembina_id', $user->opd_id);
                }
            });
        }

        if (! $forInput) {
            return $query;
        }

        if (self::indikatorHasColumn('aktif')) {
            $query->where('aktif', true);
        }

        if (self::indikatorHasColumn('input_opd')) {
            $query->where('input_opd', true);
        } elseif (self::indikatorHasColumn('boleh_diinput_opd')) {
            $query->where('boleh_diinput_opd', true);
        }

        return $query;
    }

    protected static function applyDefaultIndikatorInputScope(Builder $query, bool $forInput): Builder
    {
        if ($forInput && self::indikatorHasColumn('aktif')) {
            $query->where('aktif', true);
        }

        return $query;
    }

    public static function applyKelompokIndikatorFilter(Builder $query, ?string $kelompokIndikator): Builder
    {
        if (blank($kelompokIndikator)) {
            return $query;
        }

        $columns = array_values(array_filter(
            ['kategori', 'kelompok', 'kelompok_indikator'],
            fn (string $column): bool => self::indikatorHasColumn($column)
        ));

        if ($columns === []) {
            return $query;
        }

        return $query->where(function (Builder $builder) use ($columns, $kelompokIndikator): void {
            foreach ($columns as $index => $column) {
                $method = $index === 0 ? 'where' : 'orWhere';
                $builder->{$method}($column, $kelompokIndikator);
            }
        });
    }

    protected static function indikatorHasColumn(string $column): bool
    {
        return Schema::hasColumn((new IndikatorData())->getTable(), $column);
    }

    protected static function indicatorBoolean(IndikatorData $indikator, string $column, bool $default = false): bool
    {
        return self::indikatorHasColumn($column)
            ? (bool) $indikator->getAttribute($column)
            : $default;
    }

    protected static function indicatorAllowsKecamatanInput(IndikatorData $indikator): bool
    {
        if (self::indikatorHasColumn('input_kecamatan')) {
            return (bool) $indikator->getAttribute('input_kecamatan');
        }

        if (self::indikatorHasColumn('boleh_diinput_kecamatan')) {
            return (bool) $indikator->getAttribute('boleh_diinput_kecamatan');
        }

        return true;
    }

    protected static function indicatorAllowsOpdInput(IndikatorData $indikator): bool
    {
        if (self::indikatorHasColumn('input_opd')) {
            return (bool) $indikator->getAttribute('input_opd');
        }

        if (self::indikatorHasColumn('boleh_diinput_opd')) {
            return (bool) $indikator->getAttribute('boleh_diinput_opd');
        }

        return true;
    }

    protected static function indicatorBelongsToOpd(IndikatorData $indikator, int $opdId): bool
    {
        $hasScopeColumn = false;

        if (self::indikatorHasColumn('opd_id')) {
            $hasScopeColumn = true;

            if ((int) $indikator->getAttribute('opd_id') === $opdId) {
                return true;
            }
        }

        if (self::indikatorHasColumn('opd_pembina_id')) {
            $hasScopeColumn = true;

            if ((int) $indikator->getAttribute('opd_pembina_id') === $opdId) {
                return true;
            }
        }

        return ! $hasScopeColumn;
    }

    protected static function indicatorAllowsDesaLevel(IndikatorData $indikator): bool
    {
        if (! self::indikatorHasColumn('level_input')) {
            return true;
        }

        return blank($indikator->getAttribute('level_input'))
            || $indikator->getAttribute('level_input') === 'desa';
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
                    ->whereHas('indikatorData', fn (Builder $builder): Builder => self::applyKecamatanIndikatorInputScope($builder, true))
                : $query->whereRaw('1 = 0'),
            'opd' => $user->opd_id
                ? $query
                    ->whereHas('pengajuanData', fn (Builder $builder): Builder => $builder->where('opd_id', $user->opd_id))
                    ->whereHas('indikatorData', fn (Builder $builder): Builder => self::applyOpdIndikatorInputScope($builder, $user, true))
                : $query->whereRaw('1 = 0'),
            default => $query,
        };
    }
}
