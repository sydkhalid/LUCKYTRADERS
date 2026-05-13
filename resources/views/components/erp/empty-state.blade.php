@props([
    'title' => 'No records found',
    'description' => null,
])

<div {{ $attributes->merge(['class' => 'erp-empty-state card border-0 text-center']) }}>
    <div class="card-body py-5">
    <div class="h6 fw-bold text-heading">{{ $title }}</div>
    @if ($description)
        <p class="mt-2 mx-auto mb-0 text-muted fw-semibold" style="max-width: 32rem;">{{ $description }}</p>
    @endif
    @isset($actions)
        <div class="mt-4 d-flex flex-wrap align-items-center justify-content-center gap-2">
            {{ $actions }}
        </div>
    @endisset
    </div>
</div>
