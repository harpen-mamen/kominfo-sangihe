<section class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900">
    <div class="mb-4 flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <h2 class="text-base font-semibold text-gray-950 dark:text-white">Filter Statistik</h2>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Persempit data berdasarkan periode, OPD, indikator, dan wilayah.</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <button type="button" wire:click="exportCsv" class="rounded-lg border border-cyan-200 bg-cyan-50 px-3 py-2 text-xs font-semibold text-cyan-800 transition hover:bg-cyan-100 dark:border-cyan-800 dark:bg-cyan-950 dark:text-cyan-200">
                Export CSV
            </button>
            <button type="button" disabled title="Plugin export Excel belum tersedia" class="rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-xs font-semibold text-gray-400 dark:border-gray-700 dark:bg-gray-950">
                Export Excel
            </button>
            <button type="button" disabled title="Plugin export PDF belum tersedia" class="rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-xs font-semibold text-gray-400 dark:border-gray-700 dark:bg-gray-950">
                Export PDF
            </button>
            <div wire:loading class="rounded-lg bg-cyan-50 px-3 py-2 text-xs font-semibold text-cyan-700 dark:bg-cyan-950 dark:text-cyan-200">
                Memuat data...
            </div>
        </div>
    </div>

    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
        <label class="space-y-2">
            <span class="text-sm font-medium text-gray-700 dark:text-gray-200">Mode Laporan</span>
            <select wire:model.live="modePeriode" class="w-full rounded-lg border-gray-300 bg-white text-sm shadow-sm dark:border-gray-700 dark:bg-gray-950">
                <option value="bulanan">Bulanan</option>
                <option value="tahunan">Tahunan</option>
            </select>
        </label>
        <label class="space-y-2">
            <span class="text-sm font-medium text-gray-700 dark:text-gray-200">Periode</span>
            <select wire:model.live="periodeDataId" class="w-full rounded-lg border-gray-300 bg-white text-sm shadow-sm dark:border-gray-700 dark:bg-gray-950">
                <option value="">Custom tahun/bulan</option>
                @foreach ($this->periodeOptions as $id => $label)
                    <option value="{{ $id }}">{{ $label }}</option>
                @endforeach
            </select>
        </label>
        <label class="space-y-2">
            <span class="text-sm font-medium text-gray-700 dark:text-gray-200">Tahun</span>
            <select wire:model.live="tahun" @disabled($periodeDataId) class="w-full rounded-lg border-gray-300 bg-white text-sm shadow-sm disabled:opacity-50 dark:border-gray-700 dark:bg-gray-950">
                <option value="">Semua tahun</option>
                @foreach ($this->tahunOptions as $id => $label)
                    <option value="{{ $id }}">{{ $label }}</option>
                @endforeach
            </select>
        </label>
        <label class="space-y-2">
            <span class="text-sm font-medium text-gray-700 dark:text-gray-200">Bulan</span>
            <select wire:model.live="bulan" @disabled($periodeDataId || $modePeriode === 'tahunan') class="w-full rounded-lg border-gray-300 bg-white text-sm shadow-sm disabled:opacity-50 dark:border-gray-700 dark:bg-gray-950">
                <option value="">Semua bulan</option>
                @foreach ($this->bulanOptions as $id => $label)
                    <option value="{{ $id }}">{{ $label }}</option>
                @endforeach
            </select>
        </label>
        <label class="space-y-2">
            <span class="text-sm font-medium text-gray-700 dark:text-gray-200">OPD</span>
            <select wire:model.live="opdId" class="w-full rounded-lg border-gray-300 bg-white text-sm shadow-sm dark:border-gray-700 dark:bg-gray-950">
                <option value="">Semua OPD</option>
                @foreach ($this->opdOptions as $id => $label)
                    <option value="{{ $id }}">{{ $label }}</option>
                @endforeach
            </select>
        </label>
        <label class="space-y-2">
            <span class="text-sm font-medium text-gray-700 dark:text-gray-200">Kelompok Indikator</span>
            <select wire:model.live="kelompok" class="w-full rounded-lg border-gray-300 bg-white text-sm shadow-sm dark:border-gray-700 dark:bg-gray-950">
                <option value="">Semua kelompok</option>
                @foreach ($this->kelompokOptions as $id => $label)
                    <option value="{{ $id }}">{{ $label }}</option>
                @endforeach
            </select>
        </label>
        <label class="space-y-2">
            <span class="text-sm font-medium text-gray-700 dark:text-gray-200">Indikator</span>
            <select wire:model.live="indikatorId" class="w-full rounded-lg border-gray-300 bg-white text-sm shadow-sm dark:border-gray-700 dark:bg-gray-950">
                <option value="">Semua indikator</option>
                @foreach ($this->indikatorOptions as $id => $label)
                    <option value="{{ $id }}">{{ $label }}</option>
                @endforeach
            </select>
        </label>
        <label class="space-y-2">
            <span class="text-sm font-medium text-gray-700 dark:text-gray-200">Kecamatan</span>
            <select wire:model.live="kecamatanId" class="w-full rounded-lg border-gray-300 bg-white text-sm shadow-sm dark:border-gray-700 dark:bg-gray-950">
                <option value="">Semua kecamatan</option>
                @foreach ($this->kecamatanOptions as $id => $label)
                    <option value="{{ $id }}">{{ $label }}</option>
                @endforeach
            </select>
        </label>
        <label class="space-y-2">
            <span class="text-sm font-medium text-gray-700 dark:text-gray-200">Desa</span>
            <select wire:model.live="desaId" class="w-full rounded-lg border-gray-300 bg-white text-sm shadow-sm dark:border-gray-700 dark:bg-gray-950">
                <option value="">Semua desa</option>
                @foreach ($this->desaOptions as $id => $label)
                    <option value="{{ $id }}">{{ $label }}</option>
                @endforeach
            </select>
        </label>
    </div>
</section>
