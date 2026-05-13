@props([
    'title' => null,
    'description' => null,
])

<section {{ $attributes->merge(['class' => 'erp-form-card card custom-card']) }}>
    @if ($title || $description)
        <div class="card-header">
            @if ($title)
                <h3 class="card-title mb-1">{{ $title }}</h3>
            @endif
            @if ($description)
                <p class="card-subtitle text-muted mb-0">{{ $description }}</p>
            @endif
        </div>
    @endif

    <div class="card-body">
        {{ $slot }}
    </div>
</section>
