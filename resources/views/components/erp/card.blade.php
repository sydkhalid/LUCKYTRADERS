@props([
    'title' => null,
    'description' => null,
])

<section {{ $attributes->merge(['class' => 'erp-panel card custom-card']) }}>
    @if ($title || $description || isset($actions))
        <div class="card-header d-flex flex-column flex-sm-row align-items-sm-start justify-content-between gap-3">
            <div>
                @if ($title)
                    <h3 class="card-title mb-1">{{ $title }}</h3>
                @endif
                @if ($description)
                    <p class="card-subtitle text-muted mb-0">{{ $description }}</p>
                @endif
            </div>
            @isset($actions)
                <div class="d-flex flex-wrap align-items-center gap-2">
                    {{ $actions }}
                </div>
            @endisset
        </div>
    @endif

    <div class="card-body">
        {{ $slot }}
    </div>
</section>
