{{-- resources/views/filament/pages/input-data/input-cepat.blade.php --}}

<x-filament-panels::page>
    @include('filament.pages.input-data._styles')

    @php
        $vars = get_defined_vars();

        $getProp = function (string $name, $default = null) use ($vars) {
            if (array_key_exists($name, $vars)) {
                return $vars[$name];
            }

            try {
                return $this->{$name} ?? $default;
            } catch (\Throwable $e) {
                return $default;
            }
        };

        $periodeList = collect($getProp('periodes', []));
        $indikatorList = collect($getProp('indikators', []));
        $desaList = collect($getProp('desas', []));

        $periodeId = $getProp('periodeId');
        $submitMethod = collect(['simpan', 'save', 'store'])
            ->first(fn ($method) => method_exists($this, $method)) ?? 'simpan';

        $bulanMap = [
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember',
        ];

        $nama = function ($item, $fallback = '-') {
            return data_get($item, 'nama')
                ?? data_get($item, 'name')
                ?? data_get($item, 'judul')
                ?? $fallback;
        };

        $labelPeriode = function ($periode) use ($bulanMap, $nama) {
            if (data_get($periode, 'nama')) {
                return data_get($periode, 'nama');
            }

            $bulan = data_get($periode, 'bulan');
            $tahun = data_get($periode, 'tahun');

            if ($bulan && $tahun) {
                $bulanLabel = is_numeric($bulan)
                    ? ($bulanMap[(int) $bulan] ?? $bulan)
                    : $bulan;

                return trim($bulanLabel . ' ' . $tahun);
            }

            return $nama($periode);
        };

        $periodeAktif = $periodeId
            ? $periodeList->first(fn ($item) => (string) data_get($item, 'id') === (string) $periodeId)
            : null;
    @endphp

    <div class="kid-page">
        <div class="kid-stack">
            <div class="kid-header">
                <div>
                    <p class="kid-eyebrow">Input & Verifikasi</p>
                    <h1 class="kid-title">Input Cepat</h1>
                    <p class="kid-subtitle">
                        Masukkan banyak indikator sekaligus dalam satu tabel. Cocok untuk operator kecamatan yang mengisi data bulanan per desa.
                    </p>
                </div>

                <div class="kid-summary">
                    <div class="kid-summary-item">
                        <span class="kid-summary-label">Periode</span>
                        <span class="kid-summary-value">
                            {{ $periodeAktif ? $labelPeriode($periodeAktif) : 'Belum dipilih' }}
                        </span>
                    </div>

                    <div class="kid-summary-item">
                        <span class="kid-summary-label">Indikator Aktif</span>
                        <span class="kid-summary-value">
                            {{ number_format($indikatorList->count(), 0, ',', '.') }}
                        </span>
                    </div>
                </div>
            </div>

            @if (session()->has('success'))
                <div class="kid-alert kid-alert-success">
                    {{ session('success') }}
                </div>
            @endif

            @if (session()->has('status'))
                <div class="kid-alert kid-alert-success">
                    {{ session('status') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="kid-alert kid-alert-danger">
                    Terdapat data yang belum sesuai.
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form wire:submit.prevent="{{ $submitMethod }}">
                <div class="kid-card">
                    <div class="kid-card-header">
                        <div>
                            <h2 class="kid-card-title">Periode Input</h2>
                            <p class="kid-card-desc">
                                Pilih periode pelaporan sebelum mengisi tabel data.
                            </p>
                        </div>
                    </div>

                    <div class="kid-card-body">
                        <div class="kid-grid">
                            <div class="kid-field">
                                <label class="kid-label" for="periodeId">Periode</label>
                                <select id="periodeId" class="kid-control" wire:model.live="periodeId">
                                    <option value="">Pilih periode</option>
                                    @foreach ($periodeList as $periode)
                                        <option value="{{ data_get($periode, 'id') }}">
                                            {{ $labelPeriode($periode) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="kid-help" style="margin-top: 0;">
                                Tabel akan menyimpan nilai berdasarkan kombinasi desa dan indikator. Nilai <strong>0</strong> tetap valid.
                            </div>
                        </div>
                    </div>
                </div>

                <div class="kid-card">
                    <div class="kid-card-header">
                        <div>
                            <h2 class="kid-card-title">Tabel Input Cepat</h2>
                            <p class="kid-card-desc">
                                Baris adalah desa, kolom adalah indikator. Geser tabel ke kanan jika indikator banyak.
                            </p>
                        </div>

                        <span class="kid-badge">
                            {{ number_format($desaList->count(), 0, ',', '.') }} Desa
                        </span>
                    </div>

                    <div class="kid-card-body">
                        @if ($desaList->isEmpty() || $indikatorList->isEmpty())
                            <div class="kid-empty">
                                <p class="kid-empty-title">Data belum lengkap.</p>
                                <p class="kid-empty-text">
                                    Pastikan master desa dan indikator sudah tersedia. Setelah itu pilih periode pelaporan.
                                </p>
                            </div>
                        @else
                            <div class="kid-table-wrap">
                                <table class="kid-table" style="min-width: {{ 360 + ($indikatorList->count() * 220) }}px;">
                                    <thead>
                                        <tr>
                                            <th style="width: 60px;">No</th>
                                            <th style="width: 260px;">Desa</th>

                                            @foreach ($indikatorList as $indikator)
                                                <th style="width: 220px;">
                                                    {{ $nama($indikator) }}
                                                    @if (data_get($indikator, 'satuan'))
                                                        <div class="kid-muted">{{ data_get($indikator, 'satuan') }}</div>
                                                    @endif
                                                </th>
                                            @endforeach
                                        </tr>
                                    </thead>

                                    <tbody>
                                        @foreach ($desaList as $desa)
                                            @php
                                                $desaId = data_get($desa, 'id');
                                                $kodeDesa = data_get($desa, 'kode') ?? data_get($desa, 'kode_desa');
                                            @endphp

                                            <tr>
                                                <td class="kid-row-number">{{ $loop->iteration }}</td>

                                                <td>
                                                    <div class="kid-strong">{{ $nama($desa) }}</div>
                                                    @if ($kodeDesa)
                                                        <div class="kid-muted">Kode: {{ $kodeDesa }}</div>
                                                    @endif
                                                </td>

                                                @foreach ($indikatorList as $indikator)
                                                    @php
                                                        $indikatorId = data_get($indikator, 'id');
                                                        $tipeNilai = data_get($indikator, 'tipe_nilai', 'decimal');
                                                        $inputType = $tipeNilai === 'text' ? 'text' : 'number';
                                                        $step = $tipeNilai === 'integer' ? '1' : 'any';
                                                        $min = data_get($indikator, 'batas_min');
                                                        $max = data_get($indikator, 'batas_max');
                                                    @endphp

                                                    <td>
                                                        <input
                                                            class="kid-control"
                                                            type="{{ $inputType }}"
                                                            step="{{ $step }}"
                                                            @if ($min !== null) min="{{ $min }}" @endif
                                                            @if ($max !== null) max="{{ $max }}" @endif
                                                            placeholder="Nilai"
                                                            wire:model.blur="nilai.{{ $desaId }}.{{ $indikatorId }}"
                                                            @disabled(! $periodeId)
                                                        />
                                                    </td>
                                                @endforeach
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>

                    <div class="kid-actions">
                        <a href="{{ url('/admin') }}" class="kid-button kid-button-secondary">
                            Kembali
                        </a>

                        <button
                            type="submit"
                            class="kid-button kid-button-primary"
                            wire:loading.attr="disabled"
                            wire:target="{{ $submitMethod }}"
                            @disabled(! $periodeId || $desaList->isEmpty() || $indikatorList->isEmpty())
                        >
                            <span wire:loading.remove wire:target="{{ $submitMethod }}">
                                Simpan Data
                            </span>

                            <span wire:loading wire:target="{{ $submitMethod }}">
                                Menyimpan...
                            </span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</x-filament-panels::page>