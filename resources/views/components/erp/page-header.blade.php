@props([
    'title',
    'description' => null,
    'kicker' => null,
])

<div {{ $attributes->merge(['class' => 'erp-page-header']) }}>
    <div class="min-w-0">
        @if ($kicker)
            <p class="erp-page-kicker">{{ $kicker }}</p>
        @endif
        <h2 class="mt-1 text-xl font-black tracking-tight text-slate-950">{{ $title }}</h2>
        @if ($description)
            <p class="mt-1 text-sm font-semibold text-slate-500">{{ $description }}</p>
        @endif
    </div>

    @isset($actions)
        <div class="flex shrink-0 flex-wrap items-center gap-2">
            {{ $actions }}
        </div>
    @endisset
</div>
