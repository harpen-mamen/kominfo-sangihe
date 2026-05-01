@php
    $rows = collect($rows ?? [])->values();
    $max = max(1, (float) ($rows->max('value') ?? 1));
@endphp

<section class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900">
    <div class="mb-4">
        <h2 class="text-base font-semibold text-gray-950 dark:text-white">{{ $title ?? 'Ranking Wilayah' }}</h2>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $description ?? 'Daftar ranking berdasarkan filter aktif.' }}</p>
    </div>
    <div class="space-y-3">
        @forelse ($rows as $index => $row)
            @php $width = max(4, min(100, ((float) $row['value'] / $max) * 100)); @endphp
            <div class="rounded-lg border border-gray-100 bg-gray-50 p-3 dark:border-gray-800 dark:bg-gray-950">
                <div class="mb-2 flex items-center justify-between gap-3">
                    <span class="min-w-0 truncate text-sm font-semibold text-gray-800 dark:text-gray-100">{{ $index + 1 }}. {{ $row['label'] }}</span>
                    <span class="shrink-0 rounded-full bg-white px-2.5 py-1 text-xs font-semibold tabular-nums text-gray-700 shadow-sm dark:bg-gray-900 dark:text-gray-200">{{ $this->formatNumber($row['value']) }}</span>
                </div>
                <div class="h-2 overflow-hidden rounded-full bg-gray-200 dark:bg-gray-800">
                    <div class="h-full rounded-full bg-gradient-to-r from-[#0f3b57] via-[#0ea5a8] to-[#22c55e]" style="width: {{ $width }}%"></div>
                </div>
            </div>
        @empty
            <div class="rounded-lg border border-dashed border-gray-200 p-6 text-center text-sm text-gray-500 dark:border-gray-700">
                Belum ada ranking untuk filter ini.
            </div>
        @endforelse
    </div>
</section>
