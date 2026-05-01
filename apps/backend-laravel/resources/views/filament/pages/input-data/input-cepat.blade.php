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
            <label class="space-y-2">
                <span class="text-sm font-medium text-gray-700 dark:text-gray-200">Kelompok Indikator</span>
                <select wire:model.live="kelompok" class="w-full rounded-lg border-gray-300 bg-white text-sm shadow-sm dark:border-gray-700 dark:bg-gray-900">
                    @foreach ($this->kelompokOptions as $id => $label)
                        <option value="{{ $id }}">{{ $label }}</option>
                    @endforeach
                </select>
            </label>
        </div>

        <div class="overflow-x-auto rounded-lg border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <table class="min-w-full divide-y divide-gray-200 text-sm dark:divide-gray-800">
                <thead class="bg-gray-50 text-left text-xs font-semibold uppercase text-gray-500 dark:bg-gray-950 dark:text-gray-400">
                    <tr>
                        <th class="px-4 py-3">Desa</th>
                        @foreach ($this->indikatorColumns as $indikator)
                            <th class="px-4 py-3">{{ $indikator->nama }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @foreach ($this->desaRows as $desa)
                        <tr>
                            <td class="px-4 py-3 font-medium text-gray-950 dark:text-white">{{ $desa->nama }}</td>
                            @foreach ($this->indikatorColumns as $indikator)
                                <td class="px-4 py-3">
                                    <input type="number" step="0.01" wire:model.defer="nilai.{{ $desa->id }}.{{ $indikator->id }}" class="w-36 rounded-lg border-gray-300 bg-white text-sm shadow-sm dark:border-gray-700 dark:bg-gray-900">
                                </td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <x-filament::button wire:click="save">Simpan</x-filament::button>
    </div>
</x-filament-panels::page>
