@props([
    'title' => null,
    'description' => null,
])

<section {{ $attributes->merge(['class' => 'erp-panel']) }}>
    @if ($title || $description || isset($actions))
        <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
            <div>
                @if ($title)
                    <h3 class="text-sm font-black uppercase tracking-[0.16em] text-slate-600">{{ $title }}</h3>
                @endif
                @if ($description)
                    <p class="mt-1 text-sm font-semibold text-slate-500">{{ $description }}</p>
                @endif
            </div>
            @isset($actions)
                <div class="flex flex-wrap items-center gap-2">
                    {{ $actions }}
                </div>
            @endisset
        </div>
    @endif

    {{ $slot }}
</section>
