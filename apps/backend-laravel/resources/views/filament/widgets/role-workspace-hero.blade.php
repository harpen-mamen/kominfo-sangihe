<x-filament-widgets::widget>
    <section class="role-workspace-hero" aria-label="Ringkasan ruang kerja admin">
        <div class="role-workspace-hero__main">
            <div class="role-workspace-hero__mark" aria-hidden="true">KS</div>

            <div class="role-workspace-hero__copy">
                <span class="role-workspace-hero__eyebrow">{{ $eyebrow }}</span>
                <h2>{{ $title }}</h2>
                <p>{{ $description }}</p>

                @if (count($highlights))
                    <div class="role-workspace-hero__chips">
                        @foreach ($highlights as $highlight)
                            <span>{{ $highlight }}</span>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        @if (count($links))
            <div class="role-workspace-hero__actions" aria-label="Aksi cepat admin">
                @foreach ($links as $link)
                    <a class="role-workspace-hero__action" href="{{ $link['url'] }}">
                        <span>
                            <strong>{{ $link['label'] }}</strong>
                            <small>{{ $link['description'] }}</small>
                        </span>
                        <em>Buka</em>
                    </a>
                @endforeach
            </div>
        @endif
    </section>
</x-filament-widgets::widget>
