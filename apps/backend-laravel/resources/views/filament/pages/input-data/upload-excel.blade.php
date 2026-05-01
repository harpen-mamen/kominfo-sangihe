<x-filament-panels::page>
    <div class="space-y-6">
        <div class="grid gap-4 md:grid-cols-2">
            <label class="space-y-2">
                <span class="text-sm font-medium text-gray-700 dark:text-gray-200">Periode</span>
                <select wire:model.live="periodeDataId" class="w-full rounded-lg border-gray-300 bg-white text-sm shadow-sm dark:border-gray-700 dark:bg-gray-900">
                    @foreach ($this->periodeOptions as $id => $label)
                        <option value="{{ $id }}">{{ $label }}</option>
                    @endforeach
                </select>
            </label>
            <div class="flex items-end">
                <x-filament::button wire:click="downloadTemplate" color="gray">Download Template</x-filament::button>
            </div>
        </div>

        <label class="space-y-2 block">
            <span class="text-sm font-medium text-gray-700 dark:text-gray-200">Isi template CSV dari Excel</span>
            <textarea wire:model.defer="csvContent" rows="8" class="w-full rounded-lg border-gray-300 bg-white font-mono text-sm shadow-sm dark:border-gray-700 dark:bg-gray-900" placeholder="kode_desa,kode_indikator,sumber_data,nilai"></textarea>
        </label>

        <div class="flex gap-3">
            <x-filament::button wire:click="preview" color="gray">Preview</x-filament::button>
            <x-filament::button wire:click="save">Simpan Baris Valid</x-filament::button>
        </div>

        <div class="overflow-x-auto rounded-lg border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <table class="min-w-full divide-y divide-gray-200 text-sm dark:divide-gray-800">
                <thead class="bg-gray-50 text-left text-xs font-semibold uppercase text-gray-500 dark:bg-gray-950 dark:text-gray-400">
                    <tr>
                        <th class="px-4 py-3">Baris</th>
                        <th class="px-4 py-3">Desa</th>
                        <th class="px-4 py-3">Indikator</th>
                        <th class="px-4 py-3">Sumber Data</th>
                        <th class="px-4 py-3">Nilai</th>
                        <th class="px-4 py-3">Validasi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse ($previewRows as $row)
                        <tr>
                            <td class="px-4 py-3">{{ $row['line'] }}</td>
                            <td class="px-4 py-3">{{ $row['kode_desa'] }}</td>
                            <td class="px-4 py-3">{{ $row['kode_indikator'] }}</td>
                            <td class="px-4 py-3">{{ $row['sumber_data'] }}</td>
                            <td class="px-4 py-3">{{ number_format((float) $row['nilai'], 2, ',', '.') }}</td>
                            <td class="px-4 py-3">{{ $row['error'] ?: 'Valid' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-4 py-8 text-center text-gray-500">Preview belum dibuat.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-filament-panels::page>
