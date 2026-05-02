<x-filament-panels::page>
    <div class="space-y-6">
        <div class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <div class="grid gap-4 md:grid-cols-4">
                <div>
                    <div class="text-xs font-semibold uppercase tracking-wide text-gray-500">Periode</div>
                    <div class="mt-1 font-semibold text-gray-950 dark:text-white">{{ $this->record->periodeData?->label }}</div>
                </div>
                <div>
                    <div class="text-xs font-semibold uppercase tracking-wide text-gray-500">Kecamatan</div>
                    <div class="mt-1 font-semibold text-gray-950 dark:text-white">{{ $this->record->kecamatan?->nama }}</div>
                </div>
                <div>
                    <div class="text-xs font-semibold uppercase tracking-wide text-gray-500">Status</div>
                    <div class="mt-1 font-semibold text-gray-950 dark:text-white">{{ \App\Support\ResourceOptions::statusData()[$this->record->status] ?? $this->record->status }}</div>
                </div>
                <div>
                    <div class="text-xs font-semibold uppercase tracking-wide text-gray-500">Kelengkapan Wajib</div>
                    <div class="mt-1 font-semibold text-gray-950 dark:text-white">
                        {{ $this->progress['filled'] }}/{{ $this->progress['total'] }} ({{ $this->progress['percent'] }}%)
                    </div>
                </div>
            </div>

            @if ($this->record->status === 'revisi' && filled($this->record->catatan_revisi))
                <div class="mt-4 rounded-lg border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900">
                    <strong>Catatan revisi Kominfo:</strong>
                    <div class="mt-1">{{ $this->record->catatan_revisi }}</div>
                </div>
            @endif
        </div>

        @if ($this->kecamatanIndicators->isNotEmpty())
            <div class="rounded-lg border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <div class="border-b border-gray-200 px-5 py-4 dark:border-gray-800">
                    <h2 class="text-base font-semibold text-gray-950 dark:text-white">Indikator Level Kecamatan</h2>
                </div>
                <div class="divide-y divide-gray-100 dark:divide-gray-800">
                    @foreach ($this->kecamatanIndicators as $indikator)
                        <div class="grid gap-3 p-5 lg:grid-cols-[minmax(220px,1fr)_220px_180px_minmax(220px,1fr)]">
                            <div>
                                <div class="font-medium text-gray-950 dark:text-white">{{ $indikator->nama }}</div>
                                <div class="mt-1 text-xs text-gray-500">
                                    {{ $indikator->satuan }} · {{ $indikator->metode_agregasi }} @if ($indikator->wajib_diisi) · wajib @endif
                                </div>
                                @if (filled($indikator->petunjuk_pengisian))
                                    <div class="mt-2 text-xs text-gray-600 dark:text-gray-300">{{ $indikator->petunjuk_pengisian }}</div>
                                @endif
                            </div>
                            <input
                                @disabled($this->isReadOnly)
                                class="rounded-lg border-gray-300 bg-white text-sm shadow-sm dark:border-gray-700 dark:bg-gray-950"
                                step="0.01"
                                type="{{ $indikator->tipe_nilai === 'text' ? 'text' : 'number' }}"
                                wire:model.defer="nilai.kecamatan.{{ $this->record->kecamatan_id }}.{{ $indikator->id }}"
                            >
                            <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-200">
                                <input @disabled($this->isReadOnly) type="checkbox" wire:model.defer="tidakTersedia.kecamatan.{{ $this->record->kecamatan_id }}.{{ $indikator->id }}">
                                Tidak tersedia
                            </label>
                            <textarea
                                @disabled($this->isReadOnly)
                                class="rounded-lg border-gray-300 bg-white text-sm shadow-sm dark:border-gray-700 dark:bg-gray-950"
                                placeholder="Catatan"
                                rows="2"
                                wire:model.defer="catatanNilai.kecamatan.{{ $this->record->kecamatan_id }}.{{ $indikator->id }}"
                            ></textarea>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        @if ($this->desaIndicators->isNotEmpty())
            <div class="overflow-x-auto rounded-lg border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <table class="min-w-full divide-y divide-gray-200 text-sm dark:divide-gray-800">
                    <thead class="bg-gray-50 text-left text-xs font-semibold uppercase text-gray-500 dark:bg-gray-950 dark:text-gray-400">
                        <tr>
                            <th class="sticky left-0 z-10 min-w-48 bg-gray-50 px-4 py-3 dark:bg-gray-950">Desa</th>
                            @foreach ($this->desaIndicators as $indikator)
                                <th class="min-w-80 px-4 py-3">
                                    <div>{{ $indikator->nama }}</div>
                                    <div class="mt-1 normal-case text-gray-400">{{ $indikator->satuan }} · {{ $indikator->metode_agregasi }} @if ($indikator->wajib_diisi) · wajib @endif</div>
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @foreach ($this->desaRows as $desa)
                            <tr>
                                <td class="sticky left-0 z-10 bg-white px-4 py-4 font-medium text-gray-950 dark:bg-gray-900 dark:text-white">{{ $desa->nama }}</td>
                                @foreach ($this->desaIndicators as $indikator)
                                    <td class="px-4 py-4 align-top">
                                        @if (filled($indikator->petunjuk_pengisian))
                                            <div class="mb-2 text-xs text-gray-500">{{ $indikator->petunjuk_pengisian }}</div>
                                        @endif
                                        <div class="space-y-2">
                                            <input
                                                @disabled($this->isReadOnly)
                                                class="w-full rounded-lg border-gray-300 bg-white text-sm shadow-sm dark:border-gray-700 dark:bg-gray-950"
                                                step="0.01"
                                                type="{{ $indikator->tipe_nilai === 'text' ? 'text' : 'number' }}"
                                                wire:model.defer="nilai.desa.{{ $desa->id }}.{{ $indikator->id }}"
                                            >
                                            <label class="flex items-center gap-2 text-xs text-gray-700 dark:text-gray-200">
                                                <input @disabled($this->isReadOnly) type="checkbox" wire:model.defer="tidakTersedia.desa.{{ $desa->id }}.{{ $indikator->id }}">
                                                Tidak tersedia
                                            </label>
                                            <textarea
                                                @disabled($this->isReadOnly)
                                                class="w-full rounded-lg border-gray-300 bg-white text-sm shadow-sm dark:border-gray-700 dark:bg-gray-950"
                                                placeholder="Catatan"
                                                rows="2"
                                                wire:model.defer="catatanNilai.desa.{{ $desa->id }}.{{ $indikator->id }}"
                                            ></textarea>
                                        </div>
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        @if ($this->indicators->isEmpty())
            <div class="rounded-lg border border-gray-200 bg-white p-6 text-sm text-gray-600 shadow-sm dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300">
                Belum ada indikator aktif yang dibuka untuk input kecamatan pada pengajuan ini.
            </div>
        @endif

        <div class="flex flex-wrap gap-3">
            <x-filament::button :disabled="$this->isReadOnly" wire:click="saveDraft">
                Simpan Draft
            </x-filament::button>
            <x-filament::button :disabled="$this->isReadOnly" color="success" wire:click="submit">
                Ajukan ke Kominfo
            </x-filament::button>
        </div>
    </div>
</x-filament-panels::page>
