<x-filament-panels::page>
    <div class="space-y-6">
        <div class="grid gap-4 md:grid-cols-3">
            <label class="space-y-2">
                <span class="text-sm font-medium text-gray-700 dark:text-gray-200">Periode</span>
                <select wire:model.live="periodeDataId" class="w-full rounded-lg border-gray-300 bg-white text-sm shadow-sm dark:border-gray-700 dark:bg-gray-900">
                    <option value="">Semua periode</option>
                    @foreach ($this->periodeOptions as $id => $label)
                        <option value="{{ $id }}">{{ $label }}</option>
                    @endforeach
                </select>
            </label>

            <label class="space-y-2">
                <span class="text-sm font-medium text-gray-700 dark:text-gray-200">Kecamatan</span>
                <select wire:model.live="kecamatanId" class="w-full rounded-lg border-gray-300 bg-white text-sm shadow-sm dark:border-gray-700 dark:bg-gray-900">
                    <option value="">Semua kecamatan</option>
                    @foreach ($this->kecamatanOptions as $id => $label)
                        <option value="{{ $id }}">{{ $label }}</option>
                    @endforeach
                </select>
            </label>

            <label class="space-y-2">
                <span class="text-sm font-medium text-gray-700 dark:text-gray-200">Desa</span>
                <select wire:model.live="desaId" class="w-full rounded-lg border-gray-300 bg-white text-sm shadow-sm dark:border-gray-700 dark:bg-gray-900">
                    <option value="">Semua desa</option>
                    @foreach ($this->desaOptions as $id => $label)
                        <option value="{{ $id }}">{{ $label }}</option>
                    @endforeach
                </select>
            </label>
        </div>

        <div class="grid gap-4 md:grid-cols-4">
            <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <span class="text-sm text-gray-500 dark:text-gray-400">Jumlah Penduduk</span>
                <strong class="mt-2 block text-2xl font-semibold text-gray-950 dark:text-white">{{ number_format((float) $this->totals['jumlah_penduduk'], 0, ',', '.') }}</strong>
            </div>
            <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <span class="text-sm text-gray-500 dark:text-gray-400">Jumlah Kelahiran</span>
                <strong class="mt-2 block text-2xl font-semibold text-gray-950 dark:text-white">{{ number_format((float) $this->totals['jumlah_kelahiran'], 0, ',', '.') }}</strong>
            </div>
            <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <span class="text-sm text-gray-500 dark:text-gray-400">Jumlah Kematian</span>
                <strong class="mt-2 block text-2xl font-semibold text-gray-950 dark:text-white">{{ number_format((float) $this->totals['jumlah_kematian'], 0, ',', '.') }}</strong>
            </div>
            <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <span class="text-sm text-gray-500 dark:text-gray-400">Rasio Kematian</span>
                <strong class="mt-2 block text-2xl font-semibold text-gray-950 dark:text-white">
                    {{ is_null($this->totals['rasio_kematian']) ? '-' : number_format((float) $this->totals['rasio_kematian'], 2, ',', '.') }}
                </strong>
                <span class="text-xs text-gray-500 dark:text-gray-400">per 1.000 penduduk</span>
            </div>
        </div>

        <div class="overflow-x-auto rounded-lg border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <table class="min-w-full divide-y divide-gray-200 text-sm dark:divide-gray-800">
                <thead class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:bg-gray-950 dark:text-gray-400">
                    <tr>
                        <th class="px-4 py-3">Kecamatan</th>
                        <th class="px-4 py-3">Desa</th>
                        <th class="px-4 py-3 text-right">Penduduk</th>
                        <th class="px-4 py-3 text-right">Kelahiran</th>
                        <th class="px-4 py-3 text-right">Kematian</th>
                        <th class="px-4 py-3 text-right">Rasio</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse ($this->rows as $row)
                        <tr>
                            <td class="px-4 py-3 text-gray-700 dark:text-gray-200">{{ $row['kecamatan'] }}</td>
                            <td class="px-4 py-3 font-medium text-gray-950 dark:text-white">{{ $row['desa'] }}</td>
                            <td class="px-4 py-3 text-right text-gray-700 dark:text-gray-200">{{ number_format((float) $row['jumlah_penduduk'], 0, ',', '.') }}</td>
                            <td class="px-4 py-3 text-right text-gray-700 dark:text-gray-200">{{ number_format((float) $row['jumlah_kelahiran'], 0, ',', '.') }}</td>
                            <td class="px-4 py-3 text-right text-gray-700 dark:text-gray-200">{{ number_format((float) $row['jumlah_kematian'], 0, ',', '.') }}</td>
                            <td class="px-4 py-3 text-right text-gray-700 dark:text-gray-200">
                                {{ is_null($row['rasio_kematian']) ? '-' : number_format((float) $row['rasio_kematian'], 2, ',', '.') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
                                Belum ada data kependudukan Dukcapil untuk filter ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-filament-panels::page>
