<?php

namespace App\Filament\Widgets;

use App\Models\Berita;
use App\Models\Kegiatan;
use App\Models\PengajuanData;
use App\Support\FilamentWorkspace;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Widgets\Widget;
use Illuminate\Support\Collection;

class VerificationInbox extends Widget
{
    use HasWidgetShield {
        canView as shieldCanView;
    }

    protected string $view = 'filament.widgets.verification-inbox';

    protected int|string|array $columnSpan = 'full';

    public static function canView(): bool
    {
        return static::shieldCanView() && FilamentWorkspace::isKominfo();
    }

    /**
     * @return array<string, mixed>
     */
    protected function getViewData(): array
    {
        $statistics = PengajuanData::query()
            ->with(['kecamatan', 'periodeData'])
            ->where('status', 'diajukan')
            ->latest('tanggal_kirim')
            ->limit(5)
            ->get()
            ->map(fn (PengajuanData $submission): array => [
                'type' => 'Statistik',
                'title' => 'Pengajuan data mentah',
                'scope' => trim(implode(' | ', array_filter([
                    $submission->kecamatan?->nama,
                    $submission->periodeData?->label,
                ]))),
                'status' => 'Menunggu verifikasi',
                'sort_at' => optional($submission->tanggal_kirim ?? $submission->updated_at)?->timestamp ?? 0,
                'updated_at' => optional($submission->tanggal_kirim ?? $submission->updated_at)?->format('d M Y H:i'),
            ]);

        $news = Berita::query()
            ->with(['kecamatan', 'opd'])
            ->where('status', 'diajukan')
            ->latest('updated_at')
            ->limit(5)
            ->get()
            ->map(fn (Berita $post): array => [
                'type' => 'Berita',
                'title' => $post->judul,
                'scope' => $post->kecamatan?->nama ?? $post->opd?->nama ?? 'Konten publik',
                'status' => 'Menunggu verifikasi',
                'sort_at' => optional($post->updated_at)?->timestamp ?? 0,
                'updated_at' => optional($post->updated_at)?->format('d M Y H:i'),
            ]);

        $events = Kegiatan::query()
            ->with(['kecamatan', 'opd'])
            ->where('status', 'diajukan')
            ->latest('updated_at')
            ->limit(5)
            ->get()
            ->map(fn (Kegiatan $event): array => [
                'type' => 'Agenda',
                'title' => $event->judul,
                'scope' => $event->kecamatan?->nama ?? $event->opd?->nama ?? 'Agenda daerah',
                'status' => 'Menunggu verifikasi',
                'sort_at' => optional($event->updated_at)?->timestamp ?? 0,
                'updated_at' => optional($event->updated_at)?->format('d M Y H:i'),
            ]);

        $items = Collection::make()
            ->merge($statistics)
            ->merge($news)
            ->merge($events)
            ->sortByDesc('sort_at')
            ->take(8)
            ->values();

        return [
            'items' => $items,
            'counts' => [
                'statistics' => $statistics->count(),
                'news' => $news->count(),
                'events' => $events->count(),
            ],
        ];
    }
}
