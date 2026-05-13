@props([
    'title',
    'description' => null,
    'kicker' => null,
])

<div {{ $attributes->merge(['class' => 'erp-page-header card mb-4']) }}>
    <div class="card-body d-flex flex-column flex-sm-row align-items-sm-center justify-content-between gap-3">
    <div class="min-w-0">
        @if ($kicker)
            <p class="erp-page-kicker mb-1">{{ $kicker }}</p>
        @endif
        <h2 class="h4 mb-1 fw-bold text-heading">{{ $title }}</h2>
        @if ($description)
            <p class="mb-0 text-muted fw-semibold">{{ $description }}</p>
        @endif
    </div>

    @isset($actions)
        <div class="d-flex shrink-0 flex-wrap align-items-center gap-2">
            {{ $actions }}
        </div>
    @endisset
    </div>
</div>
