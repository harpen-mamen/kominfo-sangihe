<x-filament-widgets::widget>
    <section class="verification-inbox" aria-label="Antrean verifikasi Kominfo">
        <div class="verification-inbox__header">
            <div>
                <span class="verification-inbox__eyebrow">Meja Verifikasi</span>
                <h3>Antrean yang Perlu Dicek Kominfo</h3>
            </div>

            <div class="verification-inbox__badges">
                <span>Statistik: {{ $counts['statistics'] }}</span>
                <span>Berita: {{ $counts['news'] }}</span>
                <span>Agenda: {{ $counts['events'] }}</span>
            </div>
        </div>

        <div class="verification-inbox__list">
            @forelse ($items as $item)
                <article class="verification-inbox__card">
                    <div class="verification-inbox__type" aria-hidden="true">
                        {{ $item['type'] }}
                    </div>

                    <div class="verification-inbox__content">
                        <div class="verification-inbox__meta">
                            <strong>{{ $item['status'] }}</strong>
                            <span>Diperbarui {{ $item['updated_at'] }}</span>
                        </div>

                        <h4>{{ $item['title'] }}</h4>
                        <p>{{ $item['scope'] ?: 'Kabupaten Kepulauan Sangihe' }}</p>
                    </div>
                </article>
            @empty
                <div class="verification-inbox__empty">
                    Tidak ada data yang menunggu verifikasi saat ini.
                </div>
            @endforelse
        </div>
    </section>
</x-filament-widgets::widget>
