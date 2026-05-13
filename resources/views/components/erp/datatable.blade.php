@props([
    'id',
    'ajaxUrl' => null,
    'filterForm' => null,
    'empty' => 'No records found.',
    'searchPlaceholder' => 'Search records...',
    'export' => true,
    'pageLength' => 15,
])

<div class="erp-datatable-shell">
    <div class="erp-datatable-table overflow-x-auto">
    <table
        id="{{ $id }}"
        data-erp-datatable
        @if ($ajaxUrl) data-ajax-url="{{ $ajaxUrl }}" @endif
        @if ($filterForm) data-filter-form="{{ $filterForm }}" @endif
        data-empty="{{ $empty }}"
        data-search-placeholder="{{ $searchPlaceholder }}"
        data-export="{{ $export ? 'true' : 'false' }}"
        data-page-length="{{ $pageLength }}"
        {{ $attributes->merge(['class' => 'min-w-full divide-y divide-slate-200 text-sm']) }}
    >
        {{ $slot }}
    </table>
    </div>
</div>
