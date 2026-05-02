{{-- resources/views/filament/pages/statistik-daerah/partials/statistic-filter-card.blade.php --}}

@php
    $vars = get_defined_vars();

    $modePeriode = $vars['modePeriode'] ?? 'bulanan';
    $tahunOptions = $vars['tahunOptions'] ?? [];
    $bulanOptions = $vars['bulanOptions'] ?? [];
    $opdOptions = $vars['opdOptions'] ?? [];
    $kelompokOptions = $vars['kelompokOptions'] ?? [];
    $indikatorOptions = $vars['indikatorOptions'] ?? [];
    $kecamatanOptions = $vars['kecamatanOptions'] ?? [];
    $desaOptions = $vars['desaOptions'] ?? [];

    $hasModePeriode = (bool) ($vars['hasModePeriode'] ?? true);
    $hasTahun = (bool) ($vars['hasTahun'] ?? true);
    $hasBulan = (bool) ($vars['hasBulan'] ?? true);
    $hasOpd = (bool) ($vars['hasOpd'] ?? false);
    $hasKelompok = (bool) ($vars['hasKelompok'] ?? false);
    $hasIndikator = (bool) ($vars['hasIndikator'] ?? false);
    $hasKecamatan = (bool) ($vars['hasKecamatan'] ?? false);
    $hasDesa = (bool) ($vars['hasDesa'] ?? false);

    $resetMethod = null;

    foreach (['resetFilters', 'resetFilter', 'clearFilters'] as $method) {
        if (method_exists($this, $method)) {
            $resetMethod = $method;
            break;
        }
    }
@endphp

<div class="kom-card">
    <div class="kom-card-header">
        <div style="display: flex; align-items: flex-start; gap: 12px;">
            <span class="kom-badge" style="height: 32px; width: 32px; justify-content: center; padding: 0;">
                <x-filament::icon icon="heroicon-o-funnel" style="width: 18px; height: 18px;" />
            </span>

            <div>
                <h2 class="kom-card-title">Filter Data</h2>
                <p class="kom-card-desc">
                    Pilih periode, indikator, OPD, dan wilayah untuk memperbarui grafik serta tabel statistik.
                </p>
            </div>
        </div>

        <span class="kom-badge">Live Filter</span>
    </div>

    <div class="kom-card-body">
        <div class="kom-grid-3">
            @if ($hasModePeriode)
                <label>
                    <span>Periode</span>
                    <select wire:model.live="modePeriode">
                        <option value="bulanan">Bulanan</option>
                        <option value="tahunan">Tahunan</option>
                    </select>
                </label>
            @endif

            @if ($hasTahun)
                <label>
                    <span>Tahun</span>
                    <select wire:model.live="tahun">
                        @foreach ($tahunOptions as $id => $label)
                            <option value="{{ $id }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </label>
            @endif

            @if ($hasBulan)
                <label>
                    <span>Bulan</span>
                    <select wire:model.live="bulan" @disabled($modePeriode === 'tahunan')>
                        @foreach ($bulanOptions as $id => $label)
                            <option value="{{ $id }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </label>
            @endif

            @if ($hasOpd || count($opdOptions) > 0)
                <label>
                    <span>OPD</span>
                    <select wire:model.live="opdId">
                        <option value="">Semua OPD</option>
                        @foreach ($opdOptions as $id => $label)
                            <option value="{{ $id }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </label>
            @endif

            @if ($hasKelompok || count($kelompokOptions) > 0)
                <label>
                    <span>Kelompok Indikator</span>
                    <select wire:model.live="kelompok">
                        <option value="">Semua kelompok</option>
                        @foreach ($kelompokOptions as $id => $label)
                            <option value="{{ $id }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </label>
            @endif

            @if ($hasIndikator || count($indikatorOptions) > 0)
                <label>
                    <span>Indikator</span>
                    <select wire:model.live="indikatorId">
                        <option value="">Semua indikator</option>
                        @foreach ($indikatorOptions as $id => $label)
                            <option value="{{ $id }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </label>
            @endif

            @if ($hasKecamatan || count($kecamatanOptions) > 0)
                <label>
                    <span>Kecamatan</span>
                    <select wire:model.live="kecamatanId">
                        <option value="">Semua kecamatan</option>
                        @foreach ($kecamatanOptions as $id => $label)
                            <option value="{{ $id }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </label>
            @endif

            @if ($hasDesa || count($desaOptions) > 0)
                <label>
                    <span>Desa</span>
                    <select wire:model.live="desaId">
                        <option value="">Semua desa</option>
                        @foreach ($desaOptions as $id => $label)
                            <option value="{{ $id }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </label>
            @endif
        </div>

        @if ($resetMethod)
            <div class="kom-actions">
                <button
                    type="button"
                    class="kom-button kom-button-secondary"
                    wire:click="{{ $resetMethod }}"
                    wire:loading.attr="disabled"
                    wire:target="{{ $resetMethod }}"
                >
                    Reset Filter
                </button>
            </div>
        @endif
    </div>
</div>
