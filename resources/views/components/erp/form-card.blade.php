@props([
    'title' => null,
    'description' => null,
])

<section {{ $attributes->merge(['class' => 'erp-form-card']) }}>
    @if ($title || $description)
        <div class="mb-5 border-b border-slate-100 pb-4">
            @if ($title)
                <h3 class="text-sm font-black uppercase tracking-[0.16em] text-slate-600">{{ $title }}</h3>
            @endif
            @if ($description)
                <p class="mt-1 text-sm font-semibold text-slate-500">{{ $description }}</p>
            @endif
        </div>
    @endif

    {{ $slot }}
</section>
