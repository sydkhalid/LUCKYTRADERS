@extends('layouts.app')

@section('title', 'Customers')

@section('content')
<div class="customers-index">
    <x-erp.page-header
        title="Customers"
        description="GST, contact details, and opening balances for billing."
        kicker="Receivables"
    >
        <x-slot:actions>
            <a href="{{ route('customers.create') }}" class="erp-primary-button">Add Customer</a>
        </x-slot:actions>
    </x-erp.page-header>

    <form id="customerFilters" class="customer-filter-form">
        <div>
            <label class="form-label">Status</label>
            <select name="status" data-searchable class="form-select">
                <option value="">All Status</option>
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
            </select>
        </div>
        <div class="d-flex align-items-end">
            <button class="btn btn-dark w-100">Apply</button>
        </div>
        <div class="d-flex align-items-end">
            <button type="button" data-reset-filters class="btn btn-outline-secondary w-100">Reset</button>
        </div>
    </form>

    <x-erp.datatable
        id="customersTable"
        :ajax-url="route('erp.datatables', 'customers')"
        filter-form="#customerFilters"
        search-placeholder="Search customer, phone, GST..."
        empty="No customers found."
        class="table table-hover align-middle mb-0 customer-bootstrap-table"
    >
        <thead>
            <tr>
                <th data-column="name">Name</th>
                <th data-column="phone">Phone</th>
                <th data-column="gst_number">GST Number</th>
                <th class="text-end" data-column="opening_balance">Opening Balance</th>
                <th data-column="status">Status</th>
                <th class="text-end" data-column="actions" data-orderable="false" data-searchable="false">Action</th>
            </tr>
        </thead>
        <tbody></tbody>
    </x-erp.datatable>
</div>
@endsection

@push('styles')
<style>
    .customers-index {
        --lt-table-border: #dee2e6;
        --lt-table-muted: #6c757d;
        --lt-table-soft: #f8f9fa;
        --lt-table-ink: #1f2937;
    }

    .customers-index .erp-advanced-search,
    .customers-index .erp-datatable-shell {
        background: #fff;
        border: 1px solid var(--lt-table-border);
        border-radius: 1rem;
        box-shadow: 0 .5rem 1.25rem rgba(15, 23, 42, .06);
        overflow: hidden;
    }

    .customers-index .erp-advanced-search {
        margin-bottom: 1rem;
    }

    .customers-index .erp-advanced-search-toggle {
        width: 100%;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        padding: 1rem 1.25rem;
        border: 0;
        background: #fff;
        color: var(--lt-table-ink);
        text-align: left;
    }

    .customers-index .erp-advanced-search-toggle:hover {
        background: var(--lt-table-soft);
    }

    .customers-index .erp-advanced-search-copy {
        min-width: 0;
    }

    .customers-index .erp-advanced-search-title {
        display: flex;
        align-items: center;
        gap: .5rem;
        font-size: .95rem;
        font-weight: 800;
        color: var(--lt-table-ink);
    }

    .customers-index .erp-advanced-search-subtitle {
        display: block;
        margin-top: .15rem;
        color: var(--lt-table-muted);
        font-size: .85rem;
        font-weight: 500;
    }

    .customers-index .erp-advanced-search-meta {
        display: inline-flex;
        align-items: center;
        gap: .5rem;
        flex-shrink: 0;
    }

    .customers-index .erp-advanced-search-badge {
        display: inline-flex;
        align-items: center;
        border-radius: 50rem;
        background: #cff4fc;
        color: #055160;
        padding: .25rem .6rem;
        font-size: .75rem;
        font-weight: 800;
    }

    .customers-index .erp-advanced-search-chevron {
        display: inline-flex;
        width: 2rem;
        height: 2rem;
        align-items: center;
        justify-content: center;
        border: 1px solid var(--lt-table-border);
        border-radius: .5rem;
        color: #495057;
        transition: transform .15s ease, background-color .15s ease, color .15s ease;
    }

    .customers-index .erp-advanced-search.is-open .erp-advanced-search-chevron {
        transform: rotate(180deg);
        background: #212529;
        color: #fff;
    }

    .customers-index .erp-filter-form {
        display: grid !important;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 1rem;
        align-items: end;
        margin: 0 !important;
        padding: 1rem 1.25rem !important;
        border-top: 1px solid var(--lt-table-border) !important;
        background: var(--lt-table-soft) !important;
        box-shadow: none !important;
    }

    .customers-index .erp-filter-form[hidden] {
        display: none !important;
    }

    .customers-index .erp-filter-field-wide {
        grid-column: 1 / -1;
    }

    .customers-index .erp-filter-search-input {
        position: relative;
    }

    .customers-index .erp-filter-search-input .erp-icon {
        position: absolute;
        left: .875rem;
        top: 50%;
        transform: translateY(-50%);
        color: #6c757d;
        pointer-events: none;
    }

    .customers-index .erp-filter-search-input input {
        padding-left: 2.5rem;
    }

    .customers-index .form-label,
    .customers-index .erp-filter-field > label {
        margin-bottom: .4rem;
        color: #495057;
        font-size: .75rem;
        font-weight: 800;
        letter-spacing: .08em;
        text-transform: uppercase;
    }

    .customers-index .erp-datatable-table,
    .customers-index .dataTables_wrapper,
    .customers-index .dt-container {
        border: 0 !important;
        border-radius: 0 !important;
        box-shadow: none !important;
        background: #fff;
    }

    .customers-index .erp-dt-toolbar,
    .customers-index .dt-layout-row:first-child {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: .75rem;
        padding: 1rem 1.25rem;
        border-bottom: 1px solid #e9ecef;
        background: #fff;
    }

    .customers-index .erp-dt-footer,
    .customers-index .dt-layout-row:last-child {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: .75rem;
        padding: .9rem 1.25rem;
        border-top: 1px solid #e9ecef;
        background: var(--lt-table-soft);
    }

    .customers-index .erp-dt-tools-left,
    .customers-index .erp-dt-tools-right,
    .customers-index .dt-buttons,
    .customers-index .dt-length {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: .5rem;
    }

    .customers-index .dt-search,
    .customers-index .erp-has-advanced-search .dt-search {
        display: none !important;
    }

    .customers-index .dt-length label {
        display: inline-flex;
        align-items: center;
        gap: .5rem;
        margin: 0;
        color: #495057;
        font-size: .875rem;
        font-weight: 700;
    }

    .customers-index .dt-length select {
        width: 5.25rem;
        height: 2.375rem;
        border: 1px solid var(--lt-table-border);
        border-radius: .5rem;
        background-color: #fff;
        font-weight: 800;
    }

    .customers-index .dt-button,
    .customers-index button.dt-button,
    .customers-index .erp-dt-refresh-button {
        display: inline-flex !important;
        height: 2.375rem;
        align-items: center;
        justify-content: center;
        gap: .4rem;
        padding: .45rem .75rem !important;
        border: 1px solid var(--lt-table-border) !important;
        border-radius: .5rem !important;
        background: #fff !important;
        color: #495057 !important;
        box-shadow: 0 .125rem .25rem rgba(15, 23, 42, .04) !important;
        font-size: .75rem !important;
        font-weight: 800 !important;
        line-height: 1 !important;
        text-transform: uppercase;
    }

    .customers-index .dt-button:hover,
    .customers-index button.dt-button:hover,
    .customers-index .erp-dt-refresh-button:hover {
        border-color: #86b7fe !important;
        background: #f8fbff !important;
        color: #0d6efd !important;
    }

    .customers-index .erp-icon {
        display: inline-block;
        width: 1rem !important;
        height: 1rem !important;
        max-width: 1rem !important;
        max-height: 1rem !important;
        flex-shrink: 0;
    }

    .customers-index .customer-bootstrap-table {
        width: 100% !important;
        margin: 0 !important;
        border-collapse: separate !important;
        border-spacing: 0 !important;
        color: var(--lt-table-ink);
    }

    .customers-index .customer-bootstrap-table thead th {
        padding: .95rem 1.25rem !important;
        border-bottom: 1px solid var(--lt-table-border) !important;
        background: var(--lt-table-soft) !important;
        color: #495057 !important;
        font-size: .75rem !important;
        font-weight: 800 !important;
        letter-spacing: .08em !important;
        text-transform: uppercase;
        vertical-align: middle;
        white-space: nowrap;
    }

    .customers-index .customer-bootstrap-table tbody td {
        padding: 1rem 1.25rem !important;
        border-bottom: 1px solid #f1f3f5 !important;
        color: #212529;
        font-size: .925rem;
        font-weight: 500;
        vertical-align: middle;
    }

    .customers-index .customer-bootstrap-table tbody tr:nth-child(even) td {
        background: #fcfcfd;
    }

    .customers-index .customer-bootstrap-table tbody tr:hover td {
        background: #f8fbff !important;
    }

    .customers-index .customer-bootstrap-table th:last-child,
    .customers-index .customer-bootstrap-table td:last-child {
        width: 1%;
        text-align: right !important;
        white-space: nowrap;
    }

    .customers-index .erp-badge {
        display: inline-flex;
        align-items: center;
        border-radius: 50rem;
        padding: .35rem .65rem;
        font-size: .72rem;
        font-weight: 800;
        letter-spacing: .04em;
        text-transform: uppercase;
    }

    .customers-index .erp-badge-success {
        background: #d1e7dd;
        color: #0f5132;
    }

    .customers-index .erp-row-actions {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: .4rem;
        flex-wrap: nowrap;
    }

    .customers-index .erp-row-actions form {
        display: inline-flex;
        margin: 0;
    }

    .customers-index .erp-action-button {
        display: inline-flex;
        width: 2.125rem;
        height: 2.125rem;
        align-items: center;
        justify-content: center;
        border: 1px solid var(--lt-table-border);
        border-radius: .5rem;
        background: #fff;
        color: #495057;
        box-shadow: 0 .125rem .25rem rgba(15, 23, 42, .04);
        text-decoration: none;
    }

    .customers-index .erp-action-button:hover {
        transform: translateY(-1px);
    }

    .customers-index .erp-action-label {
        position: absolute !important;
        width: 1px !important;
        height: 1px !important;
        padding: 0 !important;
        margin: -1px !important;
        overflow: hidden !important;
        clip: rect(0, 0, 0, 0) !important;
        white-space: nowrap !important;
        border: 0 !important;
    }

    .customers-index .erp-action-view {
        color: #0d6efd;
        border-color: #b6d4fe;
    }

    .customers-index .erp-action-edit {
        color: #997404;
        border-color: #ffda6a;
    }

    .customers-index .erp-action-delete {
        color: #dc3545;
        border-color: #f5c2c7;
    }

    .customers-index .dt-info {
        color: var(--lt-table-muted);
        font-size: .875rem;
        font-weight: 700;
    }

    .customers-index .dt-paging,
    .customers-index .dt-paging nav {
        display: flex;
        align-items: center;
        gap: .35rem;
        margin: 0;
        padding: 0;
        border: 0;
        background: transparent;
        box-shadow: none;
    }

    .customers-index .dt-paging .dt-paging-button {
        min-width: 2.125rem;
        height: 2.125rem;
        border: 1px solid var(--lt-table-border) !important;
        border-radius: .5rem !important;
        background: #fff !important;
        color: #495057 !important;
        font-weight: 800;
    }

    .customers-index .dt-paging .dt-paging-button.current,
    .customers-index .dt-paging .dt-paging-button.current:hover {
        border-color: #212529 !important;
        background: #212529 !important;
        color: #fff !important;
    }

    .customers-index div.dt-processing,
    .customers-index .dt-processing,
    .customers-index .dataTables_processing {
        display: none !important;
        visibility: hidden !important;
    }

    @media (max-width: 767.98px) {
        .customers-index .erp-dt-toolbar,
        .customers-index .dt-layout-row:first-child,
        .customers-index .erp-dt-footer,
        .customers-index .dt-layout-row:last-child {
            align-items: stretch;
        }

        .customers-index .erp-dt-tools-left,
        .customers-index .erp-dt-tools-right,
        .customers-index .dt-buttons {
            width: 100%;
        }

        .customers-index .dt-button,
        .customers-index button.dt-button,
        .customers-index .erp-dt-refresh-button {
            flex: 1 1 auto;
        }
    }
</style>
@endpush
