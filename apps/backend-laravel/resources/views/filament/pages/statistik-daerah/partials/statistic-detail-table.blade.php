{{-- resources/views/filament/pages/statistik-daerah/partials/statistic-detail-table.blade.php --}}

@php
    $rowsRaw = $rows ?? [];

    $rows = is_object($rowsRaw) && method_exists($rowsRaw, 'items')
        ? collect($rowsRaw->items())
        : collect($rowsRaw);

    $title = $title ?? 'Detail Data';
    $description = $description ?? 'Daftar data statistik sesuai filter aktif.';
    $emptyTitle = $emptyTitle ?? 'Belum ada data';
    $emptyDescription = $emptyDescription ?? 'Tidak ada data statistik untuk filter yang dipilih.';

    $columns = $columns ?? [
        'periode' => 'Periode',
        'opd' => 'OPD',
        'kelompok' => 'Kelompok',
        'indikator' => 'Indikator',
        'kecamatan' => 'Kecamatan',
        'desa' => 'Desa',
        'sumber_data' => 'Sumber Data',
        'nilai' => 'Nilai',
    ];
@endphp

<div class="kom-card">
    <div class="kom-card-header">
        <div style="display: flex; align-items: flex-start; gap: 12px;">
            <span class="kom-badge" style="height: 32px; width: 32px; justify-content: center; padding: 0;">
                <x-filament::icon icon="heroicon-o-table-cells" style="width: 18px; height: 18px;" />
            </span>

            <div>
                <h3 class="kom-card-title">{{ $title }}</h3>
                <p class="kom-card-desc">{{ $description }}</p>
            </div>
        </div>

        <span class="kom-badge">
            {{ number_format($rows->count(), 0, ',', '.') }} Baris
        </span>
    </div>

    <div class="kom-card-body">
        <div class="kom-table-wrap">
            <table>
                <thead>
                    <tr>
                        <th style="width: 56px;">No</th>

                        @foreach ($columns as $key => $label)
                            <th @if ($key === 'nilai') style="text-align: right;" @endif>
                                {{ $label }}
                            </th>
                        @endforeach
                    </tr>
                </thead>

                <tbody>
                    @forelse ($rows as $index => $row)
                        <tr>
                            <td>
                                <span class="kom-badge" style="min-width: 34px; justify-content: center;">
                                    {{ $index + 1 }}
                                </span>
                            </td>

                            @foreach ($columns as $key => $label)
                                @php
                                    $value = data_get($row, $key, '-');

                                    if ($key === 'nilai') {
                                        $numericValue = is_numeric($value) ? (float) $value : 0;
                                        $satuan = data_get($row, 'satuan', '');
                                    }
                                @endphp

                                @if ($key === 'kelompok')
                                    <td>
                                        <span class="kom-badge">
                                            {{ $value ?: '-' }}
                                        </span>
                                    </td>
                                @elseif ($key === 'indikator')
                                    <td class="kom-text-strong">
                                        {{ $value ?: '-' }}
                                    </td>
                                @elseif ($key === 'nilai')
                                    <td style="text-align: right;" class="kom-text-strong">
                                        {{ number_format($numericValue, 2, ',', '.') }}
                                        {{ $satuan }}
                                    </td>
                                @else
                                    <td>
                                        {{ $value ?: '-' }}
                                    </td>
                                @endif
                            @endforeach
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ count($columns) + 1 }}">
                                <div class="kom-empty">
                                    <p class="kom-empty-title">{{ $emptyTitle }}</p>
                                    <p class="kom-empty-text">{{ $emptyDescription }}</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
