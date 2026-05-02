<?php

namespace App\Filament\Pages;

use App\Services\PortalSettingsService;
use App\Support\AdminScope;
use BackedEnum;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Validation\Rule;
use Livewire\WithFileUploads;

class PengaturanPortal extends Page
{
    use HasPageShield {
        canAccess as shieldCanAccess;
    }
    use WithFileUploads;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    protected static ?string $navigationLabel = 'Pengaturan Portal';

    protected static string|\UnitEnum|null $navigationGroup = 'Pengaturan Portal';

    protected static ?int $navigationSort = 10;

    protected string $view = 'filament.pages.pengaturan-portal';

    public ?string $portalTitle = null;

    public ?string $portalLogoPath = null;

    public mixed $portalLogoUpload = null;

    public ?string $footerDescription = null;

    public ?string $contactAddress = null;

    public ?string $contactEmail = null;

    public ?string $contactPhone = null;

    public string $heroBackgroundType = 'image';

    public ?string $heroBackgroundImagePath = null;

    public mixed $heroBackgroundImageUpload = null;

    /** @var array<int, string> */
    public array $heroBackgroundImagePaths = [];

    public mixed $heroBackgroundImageUploads = null;

    public ?string $heroBackgroundVideoPath = null;

    public mixed $heroBackgroundVideoUpload = null;

    public ?string $heroVideoPosterPath = null;

    public mixed $heroVideoPosterUpload = null;

    public ?string $heroTitle = null;

    public ?string $heroSubtitle = null;

    public ?string $heroBadgeText = null;

    public ?string $heroPrimaryButtonText = null;

    public ?string $heroPrimaryButtonLink = null;

    public ?string $heroSecondaryButtonText = null;

    public ?string $heroSecondaryButtonLink = null;

    public ?string $aboutRegionTitle = null;

    public ?string $aboutRegionSubtitle = null;

    public ?string $aboutRegionContent = null;

    public ?string $aboutRegionImagePath = null;

    public mixed $aboutRegionImageUpload = null;

    public ?string $aboutRegionButtonText = null;

    public ?string $aboutRegionButtonLink = null;

    public ?string $mapHighlightTitle = null;

    public ?string $mapHighlightDescription = null;

    public ?string $mapHighlightButtonText = null;

    public ?string $mapHighlightButtonLink = null;

    public ?string $statisticsHighlightTitle = null;

    public ?string $statisticsHighlightDescription = null;

    public ?string $statisticsHighlightButtonText = null;

    public ?string $statisticsHighlightButtonLink = null;

    public ?string $openDataTitle = null;

    public ?string $openDataDescription = null;

    public ?string $openDataPrimaryButtonText = null;

    public ?string $openDataPrimaryButtonLink = null;

    public ?string $openDataSecondaryButtonText = null;

    public function mount(PortalSettingsService $settings): void
    {
        $defaults = $settings->defaults();

        $this->portalTitle = $settings->get('portal_title', $defaults['hero_title'] ?? null);
        $this->portalLogoPath = $settings->get('portal_logo');
        $this->footerDescription = $settings->get('footer_description');
        $this->contactAddress = $settings->get('contact_address');
        $this->contactEmail = $settings->get('contact_email');
        $this->contactPhone = $settings->get('contact_phone');

        $this->heroBackgroundType = $settings->get('hero_background_type', $defaults['hero_background_type'] ?? 'image') ?? 'image';
        $this->heroBackgroundImagePath = $settings->get('hero_background_image');
        $this->heroBackgroundImagePaths = $this->decodePathList($settings->get('hero_background_images'));
        if ($this->heroBackgroundImagePath && ! in_array($this->heroBackgroundImagePath, $this->heroBackgroundImagePaths, true)) {
            array_unshift($this->heroBackgroundImagePaths, $this->heroBackgroundImagePath);
        }
        $this->heroBackgroundVideoPath = $settings->get('hero_background_video');
        $this->heroVideoPosterPath = $settings->get('hero_video_poster');
        $this->heroTitle = $settings->get('hero_title', $defaults['hero_title'] ?? null);
        $this->heroSubtitle = $settings->get('hero_subtitle', $defaults['hero_subtitle'] ?? null);
        $this->heroBadgeText = $settings->get('hero_badge_text', $defaults['hero_badge_text'] ?? null);
        $this->heroPrimaryButtonText = $settings->get('hero_primary_button_text', $defaults['hero_primary_button_text'] ?? null);
        $this->heroPrimaryButtonLink = $settings->get('hero_primary_button_link', $defaults['hero_primary_button_link'] ?? null);
        $this->heroSecondaryButtonText = $settings->get('hero_secondary_button_text', $defaults['hero_secondary_button_text'] ?? null);
        $this->heroSecondaryButtonLink = $settings->get('hero_secondary_button_link', $defaults['hero_secondary_button_link'] ?? null);

        $this->aboutRegionTitle = $settings->get('about_region_title', $defaults['about_region_title'] ?? null);
        $this->aboutRegionSubtitle = $settings->get('about_region_subtitle', $defaults['about_region_subtitle'] ?? null);
        $this->aboutRegionContent = $settings->get('about_region_content', $defaults['about_region_content'] ?? null);
        $this->aboutRegionImagePath = $settings->get('about_region_image');
        $this->aboutRegionButtonText = $settings->get('about_region_button_text', $defaults['about_region_button_text'] ?? null);
        $this->aboutRegionButtonLink = $settings->get('about_region_button_link', $defaults['about_region_button_link'] ?? null);

        $this->mapHighlightTitle = $settings->get('map_highlight_title', $defaults['map_highlight_title'] ?? null);
        $this->mapHighlightDescription = $settings->get('map_highlight_description', $defaults['map_highlight_description'] ?? null);
        $this->mapHighlightButtonText = $settings->get('map_highlight_button_text', $defaults['map_highlight_button_text'] ?? null);
        $this->mapHighlightButtonLink = $settings->get('map_highlight_button_link', $defaults['map_highlight_button_link'] ?? null);

        $this->statisticsHighlightTitle = $settings->get('statistics_highlight_title', $defaults['statistics_highlight_title'] ?? null);
        $this->statisticsHighlightDescription = $settings->get('statistics_highlight_description', $defaults['statistics_highlight_description'] ?? null);
        $this->statisticsHighlightButtonText = $settings->get('statistics_highlight_button_text', $defaults['statistics_highlight_button_text'] ?? null);
        $this->statisticsHighlightButtonLink = $settings->get('statistics_highlight_button_link', $defaults['statistics_highlight_button_link'] ?? null);

        $this->openDataTitle = $settings->get('open_data_title', $defaults['open_data_title'] ?? null);
        $this->openDataDescription = $settings->get('open_data_description', $defaults['open_data_description'] ?? null);
        $this->openDataPrimaryButtonText = $settings->get('open_data_primary_button_text', $defaults['open_data_primary_button_text'] ?? null);
        $this->openDataPrimaryButtonLink = $settings->get('open_data_primary_button_link', $defaults['open_data_primary_button_link'] ?? null);
        $this->openDataSecondaryButtonText = $settings->get('open_data_secondary_button_text', $defaults['open_data_secondary_button_text'] ?? null);
    }

    public static function canAccess(): bool
    {
        $user = auth()->user();

        return $user
            && method_exists(AdminScope::class, 'hasRole')
            && AdminScope::hasRole($user, ['super_admin', 'admin_kominfo']);
    }

    public static function shouldRegisterNavigation(): bool
    {
        return static::canAccess();
    }

    public function save(PortalSettingsService $settings): void
    {
        $validated = $this->validate([
            'portalTitle' => ['nullable', 'string', 'max:120'],
            'footerDescription' => ['nullable', 'string', 'max:1000'],
            'contactAddress' => ['nullable', 'string', 'max:500'],
            'contactEmail' => ['nullable', 'email', 'max:120'],
            'contactPhone' => ['nullable', 'string', 'max:60'],

            'heroBackgroundType' => ['required', Rule::in(['image', 'video'])],
            'heroTitle' => ['nullable', 'string', 'max:140'],
            'heroSubtitle' => ['nullable', 'string', 'max:500'],
            'heroBadgeText' => ['nullable', 'string', 'max:80'],
            'heroPrimaryButtonText' => ['nullable', 'string', 'max:40'],
            'heroPrimaryButtonLink' => ['nullable', 'string', 'max:255'],
            'heroSecondaryButtonText' => ['nullable', 'string', 'max:40'],
            'heroSecondaryButtonLink' => ['nullable', 'string', 'max:255'],

            'portalLogoUpload' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'heroBackgroundImageUpload' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'heroBackgroundImageUploads' => ['nullable', 'array'],
            'heroBackgroundImageUploads.*' => ['file', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'heroBackgroundVideoUpload' => ['nullable', 'file', 'mimes:mp4,webm', 'max:30720'],
            'heroVideoPosterUpload' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp', 'max:5120'],

            'aboutRegionTitle' => ['nullable', 'string', 'max:140'],
            'aboutRegionSubtitle' => ['nullable', 'string', 'max:500'],
            'aboutRegionContent' => ['nullable', 'string', 'max:6000'],
            'aboutRegionButtonText' => ['nullable', 'string', 'max:40'],
            'aboutRegionButtonLink' => ['nullable', 'string', 'max:255'],
            'aboutRegionImageUpload' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp', 'max:5120'],

            'mapHighlightTitle' => ['nullable', 'string', 'max:140'],
            'mapHighlightDescription' => ['nullable', 'string', 'max:500'],
            'mapHighlightButtonText' => ['nullable', 'string', 'max:40'],
            'mapHighlightButtonLink' => ['nullable', 'string', 'max:255'],
            'statisticsHighlightTitle' => ['nullable', 'string', 'max:140'],
            'statisticsHighlightDescription' => ['nullable', 'string', 'max:500'],
            'statisticsHighlightButtonText' => ['nullable', 'string', 'max:40'],
            'statisticsHighlightButtonLink' => ['nullable', 'string', 'max:255'],
            'openDataTitle' => ['nullable', 'string', 'max:140'],
            'openDataDescription' => ['nullable', 'string', 'max:500'],
            'openDataPrimaryButtonText' => ['nullable', 'string', 'max:40'],
            'openDataPrimaryButtonLink' => ['nullable', 'string', 'max:255'],
            'openDataSecondaryButtonText' => ['nullable', 'string', 'max:40'],
        ]);

        $directory = 'portal';
        $disk = 'public';

        if ($this->portalLogoUpload) {
            $path = $this->portalLogoUpload->store($directory, $disk);
            $settings->set('portal_logo', $path, 'file');
            $this->portalLogoPath = $path;
        }

        if ($this->heroBackgroundImageUpload) {
            $path = $this->heroBackgroundImageUpload->store($directory, $disk);
            $settings->set('hero_background_image', $path, 'file');
            $this->heroBackgroundImagePath = $path;
            array_unshift($this->heroBackgroundImagePaths, $path);
        }

        if (is_array($this->heroBackgroundImageUploads)) {
            foreach ($this->heroBackgroundImageUploads as $upload) {
                $path = $upload->store($directory, $disk);
                $this->heroBackgroundImagePaths[] = $path;
            }
        }

        $this->heroBackgroundImagePaths = array_values(array_unique(array_filter($this->heroBackgroundImagePaths)));
        if ($this->heroBackgroundImagePaths !== []) {
            $settings->set('hero_background_images', json_encode($this->heroBackgroundImagePaths), 'json');
            $settings->set('hero_background_image', $this->heroBackgroundImagePaths[0], 'file');
            $this->heroBackgroundImagePath = $this->heroBackgroundImagePaths[0];
        }

        if ($this->heroBackgroundVideoUpload) {
            $path = $this->heroBackgroundVideoUpload->store($directory, $disk);
            $settings->set('hero_background_video', $path, 'file');
            $this->heroBackgroundVideoPath = $path;
        }

        if ($this->heroVideoPosterUpload) {
            $path = $this->heroVideoPosterUpload->store($directory, $disk);
            $settings->set('hero_video_poster', $path, 'file');
            $this->heroVideoPosterPath = $path;
        }

        if ($this->aboutRegionImageUpload) {
            $path = $this->aboutRegionImageUpload->store($directory, $disk);
            $settings->set('about_region_image', $path, 'file');
            $this->aboutRegionImagePath = $path;
        }

        $settings->set('portal_title', $validated['portalTitle'] ?? null, 'text');
        $settings->set('footer_description', $validated['footerDescription'] ?? null, 'text');
        $settings->set('contact_address', $validated['contactAddress'] ?? null, 'text');
        $settings->set('contact_email', $validated['contactEmail'] ?? null, 'text');
        $settings->set('contact_phone', $validated['contactPhone'] ?? null, 'text');

        $settings->set('hero_background_type', (string) $validated['heroBackgroundType'], 'text');
        $settings->set('hero_title', $validated['heroTitle'] ?? null, 'text');
        $settings->set('hero_subtitle', $validated['heroSubtitle'] ?? null, 'text');
        $settings->set('hero_badge_text', $validated['heroBadgeText'] ?? null, 'text');
        $settings->set('hero_primary_button_text', $validated['heroPrimaryButtonText'] ?? null, 'text');
        $settings->set('hero_primary_button_link', $validated['heroPrimaryButtonLink'] ?? null, 'text');
        $settings->set('hero_secondary_button_text', $validated['heroSecondaryButtonText'] ?? null, 'text');
        $settings->set('hero_secondary_button_link', $validated['heroSecondaryButtonLink'] ?? null, 'text');

        $settings->set('about_region_title', $validated['aboutRegionTitle'] ?? null, 'text');
        $settings->set('about_region_subtitle', $validated['aboutRegionSubtitle'] ?? null, 'text');
        $settings->set('about_region_content', $validated['aboutRegionContent'] ?? null, 'text');
        $settings->set('about_region_button_text', $validated['aboutRegionButtonText'] ?? null, 'text');
        $settings->set('about_region_button_link', $validated['aboutRegionButtonLink'] ?? null, 'text');

        $settings->set('map_highlight_title', $validated['mapHighlightTitle'] ?? null, 'text');
        $settings->set('map_highlight_description', $validated['mapHighlightDescription'] ?? null, 'text');
        $settings->set('map_highlight_button_text', $validated['mapHighlightButtonText'] ?? null, 'text');
        $settings->set('map_highlight_button_link', $validated['mapHighlightButtonLink'] ?? null, 'text');

        $settings->set('statistics_highlight_title', $validated['statisticsHighlightTitle'] ?? null, 'text');
        $settings->set('statistics_highlight_description', $validated['statisticsHighlightDescription'] ?? null, 'text');
        $settings->set('statistics_highlight_button_text', $validated['statisticsHighlightButtonText'] ?? null, 'text');
        $settings->set('statistics_highlight_button_link', $validated['statisticsHighlightButtonLink'] ?? null, 'text');

        $settings->set('open_data_title', $validated['openDataTitle'] ?? null, 'text');
        $settings->set('open_data_description', $validated['openDataDescription'] ?? null, 'text');
        $settings->set('open_data_primary_button_text', $validated['openDataPrimaryButtonText'] ?? null, 'text');
        $settings->set('open_data_primary_button_link', $validated['openDataPrimaryButtonLink'] ?? null, 'text');
        $settings->set('open_data_secondary_button_text', $validated['openDataSecondaryButtonText'] ?? null, 'text');

        $this->reset(
            'portalLogoUpload',
            'heroBackgroundImageUpload',
            'heroBackgroundImageUploads',
            'heroBackgroundVideoUpload',
            'heroVideoPosterUpload',
            'aboutRegionImageUpload',
        );

        Notification::make()
            ->title('Pengaturan Portal portal tersimpan.')
            ->success()
            ->send();
    }

    public function getPortalLogoUrlProperty(): ?string
    {
        return $this->publicStorageUrl($this->portalLogoPath);
    }

    public function getHeroBackgroundImageUrlProperty(): ?string
    {
        return $this->publicStorageUrl($this->heroBackgroundImagePath);
    }

    /**
     * @return array<int, string>
     */
    public function getHeroBackgroundImageUrlsProperty(): array
    {
        return collect($this->heroBackgroundImagePaths)
            ->filter()
            ->map(fn (string $path): ?string => $this->publicStorageUrl($path))
            ->filter()
            ->values()
            ->all();
    }

    public function getHeroBackgroundVideoUrlProperty(): ?string
    {
        return $this->publicStorageUrl($this->heroBackgroundVideoPath);
    }

    public function getHeroVideoPosterUrlProperty(): ?string
    {
        return $this->publicStorageUrl($this->heroVideoPosterPath);
    }

    public function getAboutRegionImageUrlProperty(): ?string
    {
        return $this->publicStorageUrl($this->aboutRegionImagePath);
    }

    /**
     * @return array<int, string>
     */
    private function decodePathList(?string $value): array
    {
        if (! $value) {
            return [];
        }

        $decoded = json_decode($value, true);

        if (! is_array($decoded)) {
            return [];
        }

        return array_values(array_filter($decoded, fn ($path): bool => is_string($path) && $path !== ''));
    }

    private function publicStorageUrl(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        return url('storage-files/' . ltrim($path, '/'));
    }
}
