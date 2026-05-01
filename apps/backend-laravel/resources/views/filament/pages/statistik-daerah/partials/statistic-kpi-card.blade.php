@php
    $tone = $card['tone'] ?? 'blue';
    $gradientClass = match ($tone) {
        'navy' => 'from-[#0f3b57] to-[#075985]',
        'teal' => 'from-[#0f766e] to-[#0ea5a8]',
        'green' => 'from-[#15803d] to-[#22c55e]',
        default => 'from-[#1d4ed8] to-[#06b6d4]',
    };
    $iconClass = match ($tone) {
        'navy' => 'text-cyan-700 dark:text-cyan-300',
        'teal' => 'text-teal-700 dark:text-teal-300',
        'green' => 'text-emerald-700 dark:text-emerald-300',
        default => 'text-blue-700 dark:text-blue-300',
    };
@endphp

<article class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
    <div class="h-1 bg-gradient-to-r {{ $gradientClass }}"></div>
    <div class="p-5">
        <div class="flex items-start justify-between gap-4">
            <div class="min-w-0">
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ $card['label'] }}</p>
                <strong class="mt-2 block truncate text-2xl font-bold tracking-tight text-gray-950 dark:text-white">{{ $card['value'] }}</strong>
            </div>
            <span class="shrink-0 rounded-xl bg-gray-50 p-2 {{ $iconClass }} dark:bg-gray-800">
                <x-filament::icon :icon="$card['icon'] ?? 'heroicon-o-sparkles'" class="h-5 w-5" />
            </span>
        </div>
        <p class="mt-3 text-xs text-gray-500 dark:text-gray-400">{{ $card['caption'] }}</p>
    </div>
</article>
