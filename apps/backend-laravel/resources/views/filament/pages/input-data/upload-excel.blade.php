{{-- resources/views/filament/pages/input-data/upload-excel.blade.php --}}

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

        $hasProp = function (string $name) {
            try {
                return property_exists($this, $name) || isset($this->{$name});
            } catch (\Throwable $e) {
                return false;
            }
        };

        $periodeList = collect($getProp('periodes', []));
        $indikatorList = collect($getProp('indikators', []));
        $periodeId = $getProp('periodeId');

        $fileProperty = collect(['file', 'excelFile', 'importFile', 'uploadedFile'])
            ->first(fn ($property) => $hasProp($property)) ?? 'file';

        $submitMethod = collect(['import', 'upload', 'simpan', 'save'])
            ->first(fn ($method) => method_exists($this, $method)) ?? 'import';

        $downloadMethod = collect(['downloadTemplate', 'unduhTemplate', 'downloadFormat'])
            ->first(fn ($method) => method_exists($this, $method));

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
    @endphp

    <div class="kid-page">
        <div class="kid-stack">
            <div class="kid-header">
                <div>
                    <p class="kid-eyebrow">Input & Verifikasi</p>
                    <h1 class="kid-title">Upload Excel</h1>
                    <p class="kid-subtitle">
                        Unggah data mentah dalam format Excel agar operator tidak perlu mengisi satu per satu melalui tabel.
                    </p>
                </div>

                <div class="kid-summary">
                    <div class="kid-summary-item">
                        <span class="kid-summary-label">Format</span>
                        <span class="kid-summary-value">.xlsx / .xls</span>
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
                            <h2 class="kid-card-title">Pengaturan Upload</h2>
                            <p class="kid-card-desc">
                                Pilih periode, lalu unggah file Excel sesuai template.
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

                            <div class="kid-field">
                                <label class="kid-label" for="excelFile">File Excel</label>
                                <input
                                    id="excelFile"
                                    class="kid-control"
                                    type="file"
                                    accept=".xlsx,.xls,.csv"
                                    wire:model="{{ $fileProperty }}"
                                />
                            </div>
                        </div>

                        <div class="kid-help">
                            Pastikan kolom Excel mengikuti format template. Nilai <strong>0</strong> boleh diisi dan akan dianggap sebagai data valid,
                            sedangkan sel kosong dianggap belum diisi.
                        </div>
                    </div>
                </div>

                <div class="kid-card">
                    <div class="kid-card-header">
                        <div>
                            <h2 class="kid-card-title">Panduan Format Excel</h2>
                            <p class="kid-card-desc">
                                Gunakan kode desa dan kode indikator agar sistem dapat memetakan nilai dengan benar.
                            </p>
                        </div>
                    </div>

                    <div class="kid-card-body">
                        <div class="kid-dropzone">
                            <p class="kid-dropzone-title">Upload file data mentah kecamatan</p>
                            <p class="kid-dropzone-desc">
                                Setelah file diunggah, sistem akan membaca data, memvalidasi indikator, memvalidasi desa,
                                lalu menyimpan sebagai draft data mentah pada periode terpilih.
                            </p>

                            <div class="kid-alert kid-alert-warning" style="width: 100%; text-align: left;">
                                Kolom yang direkomendasikan:
                                <strong>kode_desa</strong>,
                                <strong>nama_desa</strong>,
                                <strong>kode_indikator</strong>,
                                <strong>nilai</strong>,
                                <strong>catatan</strong>.
                            </div>
                        </div>
                    </div>

                    <div class="kid-actions">
                        @if ($downloadMethod)
                            <button
                                type="button"
                                class="kid-button kid-button-secondary"
                                wire:click="{{ $downloadMethod }}"
                                wire:loading.attr="disabled"
                                wire:target="{{ $downloadMethod }}"
                            >
                                Unduh Template
                            </button>
                        @endif

                        <button
                            type="submit"
                            class="kid-button kid-button-primary"
                            wire:loading.attr="disabled"
                            wire:target="{{ $submitMethod }}"
                            @disabled(! $periodeId)
                        >
                            <span wire:loading.remove wire:target="{{ $submitMethod }}">
                                Upload & Proses
                            </span>

                            <span wire:loading wire:target="{{ $submitMethod }}">
                                Memproses...
                            </span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</x-filament-panels::page>