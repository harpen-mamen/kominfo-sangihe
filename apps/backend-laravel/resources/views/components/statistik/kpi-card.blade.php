<div class="rounded-2xl border bg-white p-5 shadow-sm">
    <p class="text-sm text-gray-500">{{ $title }}</p>
    <h3 class="text-2xl font-bold text-gray-900">
        {{ $value }}
    </h3>
    @if($desc ?? false)
        <p class="text-xs text-gray-500 mt-1">{{ $desc }}</p>
    @endif
</div>