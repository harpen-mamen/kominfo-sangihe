<?php

namespace App\Filament\Resources\RingkasanStatistik\Widgets\Concerns;

use App\Models\IndikatorData;
use App\Models\RingkasanStatistik;
use App\Support\FilamentWorkspace;
use Illuminate\Database\Eloquent\Builder;

trait InteractsWithRingkasanStatistik
{
    /**
     * @return array<int, string>
     */
    protected function chartPalette(): array
    {
        return [
            '#0d6efd',
            '#20c997',
            '#f59e0b',
            '#dc3545',
            '#8b5cf6',
            '#06b6d4',
            '#198754',
            '#f97316',
            '#6366f1',
            '#14b8a6',
            '#ef4444',
            '#84cc16',
            '#a855f7',
            '#0ea5e9',
            '#6b7280',
        ];
    }

    protected function scopedRingkasanQuery(): Builder
    {
        $query = RingkasanStatistik::query();
        $user = FilamentWorkspace::user();

        if ($user && FilamentWorkspace::isSubdistrict() && $user->kecamatan_id) {
            $query->where('kecamatan_id', $user->kecamatan_id);
        }

        if ($user && FilamentWorkspace::isDepartment() && $user->opd_id) {
            $query->where('opd_id', $user->opd_id);
        }

        return $query;
    }

    protected function aggregationLevel(): string
    {
        return match (FilamentWorkspace::key()) {
            'kecamatan' => 'kecamatan',
            'opd' => 'opd',
            default => 'kabupaten',
        };
    }

    protected function comparisonLevel(): string
    {
        return in_array(FilamentWorkspace::key(), ['kecamatan', 'opd'], true) ? 'desa' : 'kecamatan';
    }

    /**
     * @return array<int, string>
     */
    protected function indicatorFilters(): array
    {
        $indicatorIds = $this->scopedRingkasanQuery()
            ->distinct()
            ->pluck('indikator_data_id')
            ->all();

        if (blank($indicatorIds)) {
            return [];
        }

        return IndikatorData::query()
            ->whereIn('id', $indicatorIds)
            ->orderBy('urutan')
            ->orderBy('nama')
            ->pluck('nama', 'id')
            ->all();
    }

    protected function defaultIndicatorId(): ?int
    {
        $filters = $this->indicatorFilters();

        if (blank($filters)) {
            return null;
        }

        return (int) array_key_first($filters);
    }

    protected function selectedIndicatorId(): ?int
    {
        if (property_exists($this, 'filter') && filled($this->filter)) {
            return (int) $this->filter;
        }

        return $this->defaultIndicatorId();
    }

    protected function latestPeriodId(?int $indicatorId = null, ?string $level = null): ?int
    {
        $query = $this->scopedRingkasanQuery()
            ->when($indicatorId, fn (Builder $query): Builder => $query->where('indikator_data_id', $indicatorId))
            ->when($level, fn (Builder $query): Builder => $query->where('tingkat_rekap', $level))
            ->orderByDesc('periode_data_id');

        return $query->value('periode_data_id');
    }

    /**
     * @return array<int, string>|string
     */
    protected function wrapChartLabel(?string $label, int $charactersPerLine = 16): array|string
    {
        $label = trim((string) $label);

        if ($label === '' || mb_strlen($label) <= $charactersPerLine) {
            return $label === '' ? '-' : $label;
        }

        $words = preg_split('/\s+/', $label) ?: [];
        $lines = [];
        $currentLine = '';

        foreach ($words as $word) {
            $candidate = $currentLine === '' ? $word : "{$currentLine} {$word}";

            if (mb_strlen($candidate) <= $charactersPerLine) {
                $currentLine = $candidate;

                continue;
            }

            if ($currentLine !== '') {
                $lines[] = $currentLine;
            }

            $currentLine = $word;
        }

        if ($currentLine !== '') {
            $lines[] = $currentLine;
        }

        return $lines;
    }
}
