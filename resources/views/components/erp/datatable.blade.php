@props([
    'id',
    'ajaxUrl' => null,
    'filterForm' => null,
    'empty' => 'No records found.',
    'searchPlaceholder' => 'Search records...',
    'export' => true,
    'pageLength' => 15,
    'title' => null,
    'subtitle' => 'Search, export, refresh, and manage records.',
])

@php
    $baseTitle = preg_replace('/Table$/', '', (string) $id);
    $baseTitle = trim(preg_replace('/(?<!^)[A-Z]/', ' $0', $baseTitle));
    $tableTitle = $title ?: \Illuminate\Support\Str::headline($baseTitle ?: 'Records');
@endphp

<div class="card custom-card erp-datatable-shell">
    <div class="card-header erp-datatable-card-header d-flex flex-wrap align-items-center justify-content-between gap-2">
        <div class="min-w-0">
            <h5 class="card-title mb-1">{{ $tableTitle }}</h5>
            <p class="card-subtitle text-muted mb-0">{{ $subtitle }}</p>
        </div>
        <span class="badge erp-table-badge bg-label-primary">Live Table</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive erp-datatable-table" tabindex="0" role="region" aria-label="{{ $tableTitle }} table">
            <table
                id="{{ $id }}"
                data-erp-datatable
                @if ($ajaxUrl) data-ajax-url="{{ $ajaxUrl }}" @endif
                @if ($filterForm) data-filter-form="{{ $filterForm }}" @endif
                data-empty="{{ $empty }}"
                data-search-placeholder="{{ $searchPlaceholder }}"
                data-export="{{ $export ? 'true' : 'false' }}"
                data-page-length="{{ $pageLength }}"
                {{ $attributes->merge(['class' => 'table table-hover table-nowrap align-middle mb-0 erp-bootstrap-table']) }}
            >
                {{ $slot }}
            </table>
        </div>
    </div>
</div>
