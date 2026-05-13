@props([
    'title' => 'No records found',
    'description' => null,
])

<div {{ $attributes->merge(['class' => 'erp-empty-state']) }}>
    <div class="text-base font-black text-slate-700">{{ $title }}</div>
    @if ($description)
        <p class="mt-2 max-w-md text-sm font-semibold text-slate-500">{{ $description }}</p>
    @endif
    @isset($actions)
        <div class="mt-4 flex flex-wrap items-center justify-center gap-2">
            {{ $actions }}
        </div>
    @endisset
</div>
