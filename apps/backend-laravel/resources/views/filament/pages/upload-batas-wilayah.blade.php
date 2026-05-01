<x-filament-panels::page>
    <div style="display: grid; gap: 1.5rem;">
        <section style="display: grid; gap: 0.75rem;">
            <div>
                <h2 style="font-size: 1.125rem; font-weight: 700;">Upload Boundary GeoJSON</h2>
                <p style="color: #6b7280;">Unggah batas kecamatan atau desa ke layer resmi. Parser KML dan SHP sudah disiapkan di service, tetapi belum diaktifkan pada project ini.</p>
            </div>

            <form wire:submit="upload" style="display: grid; gap: 1rem;">
                <label style="display: grid; gap: 0.35rem;">
                    <span>Layer</span>
                    <select wire:model.live="lapisanPetaId" style="padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 0.5rem;">
                        <option value="">Pilih layer</option>
                        @foreach ($this->layerOptions as $id => $label)
                            <option value="{{ $id }}">{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('lapisanPetaId') <small style="color: #dc2626;">{{ $message }}</small> @enderror
                </label>

                <label style="display: grid; gap: 0.35rem;">
                    <span>Kecamatan</span>
                    <select wire:model.live="kecamatanId" style="padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 0.5rem;">
                        <option value="">Pilih kecamatan</option>
                        @foreach ($this->kecamatanOptions as $id => $label)
                            <option value="{{ $id }}">{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('kecamatanId') <small style="color: #dc2626;">{{ $message }}</small> @enderror
                </label>

                <label style="display: grid; gap: 0.35rem;">
                    <span>Desa</span>
                    <select wire:model.live="desaId" style="padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 0.5rem;">
                        <option value="">Pilih desa</option>
                        @foreach ($this->desaOptions as $id => $label)
                            <option value="{{ $id }}">{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('desaId') <small style="color: #dc2626;">{{ $message }}</small> @enderror
                </label>

                <label style="display: grid; gap: 0.35rem;">
                    <span>File GeoJSON</span>
                    <input type="file" wire:model="geojsonFile" accept=".json,.geojson,application/geo+json,application/json" style="padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 0.5rem;">
                    @error('geojsonFile') <small style="color: #dc2626;">{{ $message }}</small> @enderror
                </label>

                <div style="display: flex; gap: 0.75rem; align-items: center;">
                    <button type="submit" style="padding: 0.75rem 1rem; background: #0f766e; color: white; border-radius: 0.5rem; border: none;">
                        Proses Upload
                    </button>
                    <span style="color: #6b7280;">
                        @if ($this->boundaryContext === 'desa')
                            File akan disimpan sebagai boundary desa.
                        @elseif ($this->boundaryContext === 'kecamatan')
                            File akan disimpan sebagai boundary kecamatan.
                        @else
                            Pilih layer boundary resmi terlebih dahulu.
                        @endif
                    </span>
                </div>
            </form>
        </section>
    </div>
</x-filament-panels::page>
