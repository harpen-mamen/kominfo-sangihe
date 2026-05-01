<?php

namespace App\Services;

use App\Models\Desa;
use App\Models\IndikatorData;
use App\Models\NilaiDataMentah;
use App\Models\SumberData;
use App\Models\User;
use App\Support\AdminScope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class SumberDataFilterService
{
    public const INVALID_MESSAGE = 'Sumber data tidak sesuai dengan indikator atau wilayah kewenangan Anda.';

    /**
     * @var array<string, array<int, string>>
     */
    private array $jenisByKelompok = [
        'kesehatan' => ['puskesmas', 'desa', 'kecamatan'],
        'penyakit' => ['puskesmas', 'desa', 'kecamatan'],
        'pendidikan' => ['sekolah', 'desa', 'kecamatan'],
        'kependudukan' => ['desa', 'kecamatan', 'opd'],
        'demografi' => ['desa', 'kecamatan', 'opd'],
        'penduduk' => ['desa', 'kecamatan', 'opd'],
        'fasilitas' => ['desa', 'kecamatan', 'fasilitas_publik'],
        'infrastruktur' => ['desa', 'kecamatan', 'fasilitas_publik'],
    ];

    /**
     * @var array<int, string>
     */
    private array $fallbackJenis = ['desa', 'kecamatan', 'opd', 'lainnya'];

    /**
     * @return array<int, string>
     */
    public function getAllowedJenisForIndikator(IndikatorData $indikator): array
    {
        $kelompok = $this->normalizeKelompok($indikator->kelompok);

        foreach ($this->jenisByKelompok as $key => $jenis) {
            if ($kelompok === $key || str_contains($kelompok, $key)) {
                return array_values(array_unique($jenis));
            }
        }

        return $this->fallbackJenis;
    }

    public function queryForIndikator(?IndikatorData $indikator, ?User $user = null, ?int $kecamatanId = null): Builder
    {
        $query = SumberData::query()->where('aktif', true);

        if (! $indikator) {
            return $query->whereRaw('1 = 0');
        }

        $allowedJenis = $this->getAllowedJenisForIndikator($indikator);

        if (! ($user instanceof User && AdminScope::isDepartment($user))) {
            $query->whereIn('jenis', $allowedJenis);
        }

        if ($user instanceof User && AdminScope::isSubdistrict($user)) {
            if (blank($user->kecamatan_id)) {
                return $query->whereRaw('1 = 0');
            }

            $query->where(function (Builder $builder) use ($allowedJenis, $user): void {
                $builder
                    ->whereIn('jenis', $allowedJenis)
                    ->where('kecamatan_id', $user->kecamatan_id);

                if (in_array('opd', $allowedJenis, true)) {
                    $builder->orWhere(function (Builder $opdSource) use ($user): void {
                        $opdSource
                            ->where('jenis', 'opd')
                            ->where(function (Builder $scope) use ($user): void {
                                $scope->whereNull('kecamatan_id')
                                    ->orWhere('kecamatan_id', $user->kecamatan_id);
                            });
                    });
                }
            });
        }

        if ($user instanceof User && AdminScope::isDepartment($user)) {
            if (blank($user->opd_id)) {
                return $query->whereRaw('1 = 0');
            }

            $query->where(function (Builder $builder) use ($allowedJenis, $user): void {
                $builder
                    ->whereIn('jenis', $allowedJenis)
                    ->orWhere('opd_id', $user->opd_id);
            });

            if ($kecamatanId) {
                $query->where(function (Builder $builder) use ($kecamatanId): void {
                    $builder
                        ->where('kecamatan_id', $kecamatanId)
                        ->orWhereNull('kecamatan_id');
                });
            }
        }

        return $this->orderByJenisPriority($query, $allowedJenis);
    }

    /**
     * @return array<int|string, string>
     */
    public function optionsForIndikatorId(mixed $indikatorId, ?User $user = null, mixed $desaId = null, ?int $kecamatanId = null): array
    {
        $indikator = $this->resolveIndikator($indikatorId);

        if (! $indikator) {
            return [];
        }

        $kecamatanId ??= $this->kecamatanIdFromDesa($desaId);

        if (! $kecamatanId && $user instanceof User && AdminScope::isSubdistrict($user)) {
            $kecamatanId = $user->kecamatan_id;
        }

        return $this
            ->queryForIndikator($indikator, $user, $kecamatanId)
            ->orderBy('nama')
            ->pluck('nama', 'id')
            ->all();
    }

    public function helperTextForIndikatorId(mixed $indikatorId): ?string
    {
        $indikator = $this->resolveIndikator($indikatorId);

        if (! $indikator) {
            return null;
        }

        $kelompok = $this->normalizeKelompok($indikator->kelompok);

        if (str_contains($kelompok, 'kesehatan') || str_contains($kelompok, 'penyakit')) {
            return 'Untuk indikator kesehatan, pilih Puskesmas/Desa/Kecamatan yang menjadi asal laporan.';
        }

        if (str_contains($kelompok, 'pendidikan')) {
            return 'Untuk indikator pendidikan, pilih Sekolah/Desa/Kecamatan.';
        }

        if (str_contains($kelompok, 'kependudukan') || str_contains($kelompok, 'demografi') || str_contains($kelompok, 'penduduk')) {
            return 'Untuk indikator kependudukan, pilih Desa/Kecamatan/OPD terkait.';
        }

        return 'Pilih sumber data yang paling sesuai dengan asal laporan indikator.';
    }

    public function validateNilaiDataMentah(NilaiDataMentah $nilai, User $user): void
    {
        $nilai->loadMissing(['desa', 'indikatorData', 'pengajuanData', 'sumberData.desa']);

        if (! $nilai->sumber_data_id) {
            return;
        }

        $sumberData = $nilai->sumberData;
        $indikator = $nilai->indikatorData;

        if (! $sumberData || ! $indikator || ! $sumberData->aktif) {
            $this->throwInvalid();
        }

        if (AdminScope::isKominfo($user)) {
            return;
        }

        $allowedJenis = $this->getAllowedJenisForIndikator($indikator);

        if (! in_array($sumberData->jenis, $allowedJenis, true)) {
            if (! (AdminScope::isDepartment($user) && $sumberData->opd_id && (int) $sumberData->opd_id === (int) $user->opd_id)) {
                $this->throwInvalid();
            }
        }

        if ($sumberData->desa_id && $nilai->desa_id && (int) $sumberData->desa_id !== (int) $nilai->desa_id) {
            $this->throwInvalid();
        }

        $contextKecamatanId = $nilai->pengajuanData?->kecamatan_id ?: $nilai->desa?->kecamatan_id;

        if ($sumberData->kecamatan_id && $contextKecamatanId && (int) $sumberData->kecamatan_id !== (int) $contextKecamatanId) {
            $this->throwInvalid();
        }

        if (AdminScope::isSubdistrict($user)) {
            $this->validateForSubdistrict($sumberData, $user);
        }

        if (AdminScope::isDepartment($user)) {
            $this->validateForDepartment($sumberData, $user);
        }
    }

    private function validateForSubdistrict(SumberData $sumberData, User $user): void
    {
        if (blank($user->kecamatan_id)) {
            $this->throwInvalid();
        }

        if ($sumberData->kecamatan_id && (int) $sumberData->kecamatan_id !== (int) $user->kecamatan_id) {
            $this->throwInvalid();
        }

        if ($sumberData->desa?->kecamatan_id && (int) $sumberData->desa->kecamatan_id !== (int) $user->kecamatan_id) {
            $this->throwInvalid();
        }

        if ($sumberData->jenis !== 'opd' && (int) $sumberData->kecamatan_id !== (int) $user->kecamatan_id) {
            $this->throwInvalid();
        }
    }

    private function validateForDepartment(SumberData $sumberData, User $user): void
    {
        if (blank($user->opd_id)) {
            $this->throwInvalid();
        }

        if ($sumberData->opd_id && (int) $sumberData->opd_id !== (int) $user->opd_id) {
            $this->throwInvalid();
        }
    }

    private function orderByJenisPriority(Builder $query, array $allowedJenis): Builder
    {
        $case = collect(array_values($allowedJenis))
            ->map(fn (string $jenis, int $index): string => "WHEN ? THEN {$index}")
            ->implode(' ');

        return $query->orderByRaw("CASE jenis {$case} ELSE 99 END", array_values($allowedJenis));
    }

    private function resolveIndikator(mixed $indikatorId): ?IndikatorData
    {
        if ($indikatorId instanceof IndikatorData) {
            return $indikatorId;
        }

        if (blank($indikatorId)) {
            return null;
        }

        return IndikatorData::query()->find($indikatorId);
    }

    private function kecamatanIdFromDesa(mixed $desaId): ?int
    {
        if (blank($desaId)) {
            return null;
        }

        $kecamatanId = Desa::query()->whereKey($desaId)->value('kecamatan_id');

        return $kecamatanId ? (int) $kecamatanId : null;
    }

    private function normalizeKelompok(?string $kelompok): string
    {
        return Str::of((string) $kelompok)
            ->lower()
            ->ascii()
            ->replaceMatches('/[^a-z0-9]+/', '_')
            ->trim('_')
            ->value();
    }

    private function throwInvalid(): never
    {
        throw ValidationException::withMessages([
            'sumber_data_id' => self::INVALID_MESSAGE,
        ]);
    }
}
