<?php

namespace App\Filament\Pages;

use App\Models\Desa;
use App\Models\Kecamatan;
use App\Models\LapisanPeta;
use App\Services\BoundaryUploadService;
use App\Support\FilamentWorkspace;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use BackedEnum;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Livewire\WithFileUploads;

class UploadBatasWilayah extends Page
{
    use HasPageShield {
        canAccess as shieldCanAccess;
    }
    use WithFileUploads;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowUpTray;

    protected static ?string $navigationLabel = 'Upload Batas Wilayah';

    protected static string|\UnitEnum|null $navigationGroup = 'Peta';

    protected static ?int $navigationSort = 30;

    protected string $view = 'filament.pages.upload-batas-wilayah';

    public ?int $lapisanPetaId = null;

    public ?int $kecamatanId = null;

    public ?int $desaId = null;

    public mixed $geojsonFile = null;

    public static function canAccess(): bool
    {
        return static::shieldCanAccess() && FilamentWorkspace::isKominfo();
    }

    /**
     * @return array<int|string, string>
     */
    public function getLayerOptionsProperty(): array
    {
        return LapisanPeta::query()
            ->where('aktif', true)
            ->where(function ($query): void {
                $query
                    ->where('hanya_admin_kominfo', true)
                    ->orWhereIn('slug', ['batas-kecamatan', 'batas-desa', 'batas-kecamatan-sangihe', 'batas-desa-sangihe']);
            })
            ->orderBy('urutan')
            ->pluck('nama', 'id')
            ->all();
    }

    /**
     * @return array<int|string, string>
     */
    public function getKecamatanOptionsProperty(): array
    {
        return Kecamatan::query()->where('aktif', true)->orderBy('nama')->pluck('nama', 'id')->all();
    }

    /**
     * @return array<int|string, string>
     */
    public function getDesaOptionsProperty(): array
    {
        return Desa::query()
            ->where('aktif', true)
            ->when($this->kecamatanId, fn ($query) => $query->where('kecamatan_id', $this->kecamatanId))
            ->orderBy('nama')
            ->pluck('nama', 'id')
            ->all();
    }

    public function getBoundaryContextProperty(): ?string
    {
        $slug = LapisanPeta::query()->whereKey($this->lapisanPetaId)->value('slug');

        return match (true) {
            is_string($slug) && str_contains($slug, 'desa') => 'desa',
            is_string($slug) && str_contains($slug, 'kecamatan') => 'kecamatan',
            default => null,
        };
    }

    public function updatedDesaId(?int $value): void
    {
        if (! $value) {
            return;
        }

        $this->kecamatanId = Desa::query()->whereKey($value)->value('kecamatan_id');
    }

    public function upload(BoundaryUploadService $service): void
    {
        $actor = FilamentWorkspace::user();

        abort_unless($actor, 403);

        $rules = [
            'lapisanPetaId' => ['required', 'integer', 'exists:lapisan_peta,id'],
            'geojsonFile' => ['required', 'file', 'mimes:json,geojson'],
        ];

        if ($this->boundaryContext === 'desa') {
            $rules['desaId'] = ['required', 'integer', 'exists:desa,id'];
        } else {
            $rules['kecamatanId'] = ['required', 'integer', 'exists:kecamatan,id'];
        }

        $this->validate($rules);

        $result = $service->uploadGeoJson(
            uploadedFile: $this->geojsonFile,
            lapisanPetaId: (int) $this->lapisanPetaId,
            kecamatanId: $this->kecamatanId,
            desaId: $this->desaId,
            actor: $actor,
        );

        Notification::make()
            ->title('Boundary GeoJSON berhasil diproses.')
            ->body("{$result['count']} fitur tersimpan pada layer {$result['layer']}.")
            ->success()
            ->send();

        $this->reset('geojsonFile', 'desaId');
    }
}
