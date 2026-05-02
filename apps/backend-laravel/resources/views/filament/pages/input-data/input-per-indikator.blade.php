{{-- resources/views/filament/pages/input-data/input-per-indikator.blade.php --}}

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
        $indikatorId = $getProp('indikatorId');

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

        $indikatorAktif = $indikatorId
            ? $indikatorList->first(fn ($item) => (string) data_get($item, 'id') === (string) $indikatorId)
            : null;

        $tipeNilai = data_get($indikatorAktif, 'tipe_nilai', 'decimal');
        $satuan = data_get($indikatorAktif, 'satuan');
        $kategori = data_get($indikatorAktif, 'kategori') ?? data_get($indikatorAktif, 'kelompok_indikator');
        $metodeAgregasi = data_get($indikatorAktif, 'metode_agregasi');
        $petunjuk = data_get($indikatorAktif, 'petunjuk_pengisian');
        $batasMin = data_get($indikatorAktif, 'batas_min');
        $batasMax = data_get($indikatorAktif, 'batas_max');
        $wajibDiisi = (bool) data_get($indikatorAktif, 'wajib_diisi', false);

        $inputType = $tipeNilai === 'text' ? 'text' : 'number';
        $inputStep = $tipeNilai === 'integer' ? '1' : 'any';
    @endphp

    <div class="kid-page">
        <div class="kid-stack">
            <div class="kid-header">
                <div>
                    <p class="kid-eyebrow">Input & Verifikasi</p>
                    <h1 class="kid-title">Input Per Indikator</h1>
                    <p class="kid-subtitle">
                        Isi nilai data mentah per desa berdasarkan periode dan indikator yang dibuat oleh Admin Kominfo.
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
                        <span class="kid-summary-label">Jumlah Desa</span>
                        <span class="kid-summary-value">
                            {{ number_format($desaList->count(), 0, ',', '.') }}
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
                            <h2 class="kid-card-title">Filter Data</h2>
                            <p class="kid-card-desc">
                                Pilih periode dan indikator sebelum mengisi nilai.
                            </p>
                        </div>

                        @if ($indikatorAktif)
                            <span class="kid-badge">
                                {{ $wajibDiisi ? 'Wajib diisi' : 'Opsional' }}
                            </span>
                        @endif
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

                            <div class="kid-field">
                                <label class="kid-label" for="indikatorId">Indikator</label>
                                <select id="indikatorId" class="kid-control" wire:model.live="indikatorId">
                                    <option value="">Pilih indikator</option>
                                    @foreach ($indikatorList as $indikator)
                                        <option value="{{ data_get($indikator, 'id') }}">
                                            {{ $nama($indikator) }}
                                            @if (data_get($indikator, 'satuan'))
                                                — {{ data_get($indikator, 'satuan') }}
                                            @endif
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        @if ($indikatorAktif)
                            <div class="kid-info-grid">
                                <div class="kid-info-item">
                                    <span class="kid-info-label">Kategori</span>
                                    <span class="kid-info-value">{{ $kategori ?: '-' }}</span>
                                </div>

                                <div class="kid-info-item">
                                    <span class="kid-info-label">Satuan</span>
                                    <span class="kid-info-value">{{ $satuan ?: '-' }}</span>
                                </div>

                                <div class="kid-info-item">
                                    <span class="kid-info-label">Tipe Nilai</span>
                                    <span class="kid-info-value">{{ $tipeNilai ?: '-' }}</span>
                                </div>

                                <div class="kid-info-item">
                                    <span class="kid-info-label">Agregasi</span>
                                    <span class="kid-info-value">{{ $metodeAgregasi ?: '-' }}</span>
                                </div>
                            </div>

                            @if ($petunjuk || $batasMin !== null || $batasMax !== null)
                                <div class="kid-help">
                                    @if ($petunjuk)
                                        <div>
                                            <strong>Petunjuk:</strong> {{ $petunjuk }}
                                        </div>
                                    @endif

                                    @if ($batasMin !== null || $batasMax !== null)
                                        <div style="margin-top: 6px;">
                                            <strong>Batas nilai:</strong>
                                            {{ $batasMin !== null ? 'Minimal ' . $batasMin : '' }}
                                            {{ $batasMin !== null && $batasMax !== null ? ' · ' : '' }}
                                            {{ $batasMax !== null ? 'Maksimal ' . $batasMax : '' }}
                                        </div>
                                    @endif
                                </div>
                            @endif
                        @else
                            <div class="kid-help">
                                Pilih indikator terlebih dahulu. Setelah indikator dipilih, kolom nilai akan aktif.
                            </div>
                        @endif
                    </div>
                </div>

                <div class="kid-card">
                    <div class="kid-card-header">
                        <div>
                            <h2 class="kid-card-title">Daftar Desa dan Nilai</h2>
                            <p class="kid-card-desc">
                                Masukkan nilai untuk setiap desa. Nilai <strong>0</strong> tetap dianggap valid.
                            </p>
                        </div>
                    </div>

                    <div class="kid-card-body">
                        @if ($desaList->isEmpty())
                            <div class="kid-empty">
                                <p class="kid-empty-title">Belum ada data desa.</p>
                                <p class="kid-empty-text">
                                    Data desa tidak ditemukan. Periksa master data desa atau scope kecamatan pengguna.
                                </p>
                            </div>
                        @else
                            <div class="kid-table-wrap">
                                <table class="kid-table">
                                    <thead>
                                        <tr>
                                            <th style="width: 60px;">No</th>
                                            <th>Desa</th>
                                            <th style="width: 280px;">Nilai</th>
                                            <th style="width: 140px;">Satuan</th>
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

                                                <td>
                                                    <input
                                                        class="kid-control"
                                                        type="{{ $inputType }}"
                                                        step="{{ $inputStep }}"
                                                        @if ($batasMin !== null) min="{{ $batasMin }}" @endif
                                                        @if ($batasMax !== null) max="{{ $batasMax }}" @endif
                                                        placeholder="{{ $indikatorAktif ? 'Masukkan nilai' : 'Pilih indikator dahulu' }}"
                                                        wire:model.blur="nilai.{{ $desaId }}"
                                                        @disabled(! $indikatorAktif)
                                                    />
                                                </td>

                                                <td class="kid-unit">
                                                    {{ $satuan ?: '-' }}
                                                </td>
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
                            @disabled(! $periodeId || ! $indikatorId || $desaList->isEmpty())
                        >
                            <span wire:loading.remove wire:target="{{ $submitMethod }}">
                                Simpan
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