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
            <a href="{{ route('customers.create') }}" class="btn btn-primary btn-wave">
                <span class="me-1">+</span> Add Customer
            </a>
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
            <button class="btn btn-primary w-100">Apply</button>
        </div>
        <div class="d-flex align-items-end">
            <button type="button" data-reset-filters class="btn btn-light border w-100">Reset</button>
        </div>
    </form>

    <div class="card custom-card customer-rz-card">
        <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2">
            <div>
                <h5 class="card-title mb-1">Customers List</h5>
                <p class="text-muted mb-0 small">Search, export, and manage receivable master records.</p>
            </div>
            <span class="badge bg-primary-transparent text-primary">Bootstrap 5 Table</span>
        </div>
        <div class="card-body p-0">
            <div class="customer-table-wrap">
                <table
                    id="customersTable"
                    data-erp-datatable
                    data-ajax-url="{{ route('erp.datatables', 'customers') }}"
                    data-filter-form="#customerFilters"
                    data-empty="No customers found."
                    data-search-placeholder="Search customer, phone, GST..."
                    data-export="true"
                    data-page-length="15"
                    class="table table-hover table-nowrap align-middle mb-0 customer-rz-table"
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
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .customers-index {
        --rz-card-border: #e9edf6;
        --rz-soft-bg: #f7f8fc;
        --rz-head-bg: #f8f9fd;
        --rz-text: #252b3b;
        --rz-muted: #7c8493;
        --rz-primary: #7367f0;
    }

    .customers-index .card.custom-card,
    .customers-index .erp-advanced-search {
        border: 1px solid var(--rz-card-border);
        border-radius: .65rem;
        background: #fff;
        box-shadow: 0 .125rem .625rem rgba(20, 20, 43, .04);
    }

    .customers-index .card-header {
        min-height: 4.25rem;
        padding: 1rem 1.25rem;
        border-bottom: 1px solid var(--rz-card-border);
        background: #fff;
    }

    .customers-index .card-title {
        color: var(--rz-text);
        font-size: .98rem;
        font-weight: 800;
    }

    .customers-index .bg-primary-transparent {
        background: rgba(115, 103, 240, .12);
    }

    .customers-index .text-primary {
        color: var(--rz-primary) !important;
    }

    .customers-index .btn {
        border-radius: .45rem;
        font-weight: 700;
        box-shadow: none !important;
    }

    .customers-index .btn-primary {
        border-color: var(--rz-primary);
        background: var(--rz-primary);
    }

    .customers-index .btn-primary:hover {
        border-color: #6154df;
        background: #6154df;
    }

    .customers-index .erp-advanced-search {
        margin-bottom: 1rem;
        overflow: hidden;
    }

    .customers-index .erp-advanced-search-toggle {
        display: flex;
        width: 100%;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        padding: .9rem 1.25rem;
        border: 0;
        background: #fff;
        color: var(--rz-text);
        text-align: left;
    }

    .customers-index .erp-advanced-search-toggle:hover {
        background: var(--rz-soft-bg);
    }

    .customers-index .erp-advanced-search-title {
        display: flex;
        align-items: center;
        gap: .5rem;
        color: var(--rz-text);
        font-size: .9rem;
        font-weight: 800;
    }

    .customers-index .erp-advanced-search-subtitle {
        display: block;
        margin-top: .125rem;
        color: var(--rz-muted);
        font-size: .78rem;
        font-weight: 500;
    }

    .customers-index .erp-advanced-search-meta {
        display: inline-flex;
        align-items: center;
        gap: .45rem;
        flex-shrink: 0;
    }

    .customers-index .erp-advanced-search-badge {
        border-radius: 999px;
        background: rgba(115, 103, 240, .12);
        color: var(--rz-primary);
        padding: .25rem .55rem;
        font-size: .72rem;
        font-weight: 800;
    }

    .customers-index .erp-advanced-search-chevron {
        display: inline-flex;
        width: 1.85rem;
        height: 1.85rem;
        align-items: center;
        justify-content: center;
        border: 1px solid var(--rz-card-border);
        border-radius: .45rem;
        color: var(--rz-muted);
        transition: .15s ease;
    }

    .customers-index .erp-advanced-search.is-open .erp-advanced-search-chevron {
        transform: rotate(180deg);
        background: var(--rz-primary);
        color: #fff;
    }

    .customers-index .erp-filter-form {
        display: grid !important;
        grid-template-columns: repeat(auto-fit, minmax(210px, 1fr));
        gap: .9rem;
        align-items: end;
        margin: 0 !important;
        padding: 1rem 1.25rem !important;
        border-top: 1px solid var(--rz-card-border) !important;
        background: var(--rz-soft-bg) !important;
        box-shadow: none !important;
    }

    .customers-index .erp-filter-form[hidden] {
        display: none !important;
    }

    .customers-index .erp-filter-field-wide {
        grid-column: 1 / -1;
    }

    .customers-index .form-label,
    .customers-index .erp-filter-field > label {
        margin-bottom: .35rem;
        color: #56606f;
        font-size: .7rem;
        font-weight: 800;
        letter-spacing: .055em;
        text-transform: uppercase;
    }

    .customers-index .form-control,
    .customers-index .form-select,
    .customers-index .erp-filter-search-input input,
    .customers-index .dt-length select {
        min-height: 2.25rem;
        border-color: #dbe0ea;
        border-radius: .45rem;
        color: var(--rz-text);
        font-size: .84rem;
    }

    .customers-index .erp-filter-search-input {
        position: relative;
    }

    .customers-index .erp-filter-search-input .erp-icon {
        position: absolute;
        left: .8rem;
        top: 50%;
        transform: translateY(-50%);
        color: var(--rz-muted);
        pointer-events: none;
    }

    .customers-index .erp-filter-search-input input {
        width: 100%;
        padding-left: 2.35rem;
    }

    .customers-index .customer-table-wrap {
        overflow-x: auto;
    }

    .customers-index .dt-container,
    .customers-index .dataTables_wrapper {
        border: 0 !important;
        border-radius: 0 !important;
        background: #fff;
        box-shadow: none !important;
    }

    .customers-index .erp-dt-toolbar,
    .customers-index .dt-layout-row:first-child {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: .75rem;
        padding: .9rem 1.25rem;
        border-bottom: 1px solid var(--rz-card-border);
        background: #fff;
    }

    .customers-index .erp-dt-footer,
    .customers-index .dt-layout-row:last-child {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: .75rem;
        padding: .85rem 1.25rem;
        border-top: 1px solid var(--rz-card-border);
        background: var(--rz-soft-bg);
    }

    .customers-index .erp-dt-tools-left,
    .customers-index .erp-dt-tools-right,
    .customers-index .dt-buttons,
    .customers-index .dt-length {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: .45rem;
    }

    .customers-index .dt-search,
    .customers-index .erp-has-advanced-search .dt-search {
        display: none !important;
    }

    .customers-index .dt-length label {
        display: inline-flex;
        align-items: center;
        gap: .45rem;
        margin: 0;
        color: var(--rz-muted);
        font-size: .8rem;
        font-weight: 700;
    }

    .customers-index .dt-length select {
        width: 4.75rem;
        font-weight: 800;
    }

    .customers-index .dt-button,
    .customers-index button.dt-button,
    .customers-index .erp-dt-refresh-button {
        display: inline-flex !important;
        height: 2.2rem;
        align-items: center;
        justify-content: center;
        gap: .35rem;
        padding: .4rem .65rem !important;
        border: 1px solid #dfe4ef !important;
        border-radius: .45rem !important;
        background: #fff !important;
        color: #56606f !important;
        box-shadow: none !important;
        font-size: .7rem !important;
        font-weight: 800 !important;
        line-height: 1 !important;
        text-transform: uppercase;
    }

    .customers-index .dt-button:hover,
    .customers-index button.dt-button:hover,
    .customers-index .erp-dt-refresh-button:hover {
        border-color: rgba(115, 103, 240, .35) !important;
        background: rgba(115, 103, 240, .08) !important;
        color: var(--rz-primary) !important;
    }

    .customers-index .erp-icon {
        display: inline-block;
        width: .95rem !important;
        height: .95rem !important;
        max-width: .95rem !important;
        max-height: .95rem !important;
        flex-shrink: 0;
    }

    .customers-index .customer-rz-table {
        width: 100% !important;
        margin: 0 !important;
        color: var(--rz-text);
        border-color: var(--rz-card-border);
    }

    .customers-index .customer-rz-table thead th {
        padding: .85rem 1.25rem !important;
        border-bottom: 1px solid var(--rz-card-border) !important;
        background: var(--rz-head-bg) !important;
        color: #596273 !important;
        font-size: .72rem !important;
        font-weight: 800 !important;
        letter-spacing: .045em !important;
        text-transform: uppercase;
        vertical-align: middle;
        white-space: nowrap;
    }

    .customers-index .customer-rz-table tbody td {
        padding: .95rem 1.25rem !important;
        border-bottom: 1px solid #eef1f7 !important;
        color: #293043;
        font-size: .86rem;
        font-weight: 500;
        vertical-align: middle;
    }

    .customers-index .customer-rz-table tbody tr:hover td {
        background: #fbfbff !important;
    }

    .customers-index .customer-rz-table th:last-child,
    .customers-index .customer-rz-table td:last-child {
        width: 1%;
        text-align: right !important;
        white-space: nowrap;
    }

    .customers-index .erp-badge {
        display: inline-flex;
        align-items: center;
        border-radius: .35rem;
        padding: .25rem .55rem;
        font-size: .68rem;
        font-weight: 800;
        letter-spacing: .025em;
        text-transform: uppercase;
    }

    .customers-index .erp-badge-success {
        background: rgba(40, 199, 111, .14);
        color: #198754;
    }

    .customers-index .erp-badge-warning {
        background: rgba(255, 159, 67, .16);
        color: #b76e00;
    }

    .customers-index .erp-badge-danger {
        background: rgba(234, 84, 85, .14);
        color: #dc3545;
    }

    .customers-index .erp-row-actions {
        display: inline-flex;
        align-items: center;
        justify-content: flex-end;
        gap: .35rem;
        flex-wrap: nowrap;
    }

    .customers-index .erp-row-actions form {
        display: inline-flex;
        margin: 0;
    }

    .customers-index .erp-action-button {
        display: inline-flex;
        width: 1.85rem;
        height: 1.85rem;
        align-items: center;
        justify-content: center;
        border: 0;
        border-radius: .35rem;
        background: transparent;
        color: #7b8496;
        text-decoration: none;
        transition: .15s ease;
    }

    .customers-index .erp-action-button:hover {
        transform: translateY(-1px);
    }

    .customers-index .erp-action-view:hover,
    .customers-index .erp-action-view {
        color: #7367f0;
    }

    .customers-index .erp-action-edit:hover,
    .customers-index .erp-action-edit {
        color: #00bad1;
    }

    .customers-index .erp-action-delete:hover,
    .customers-index .erp-action-delete {
        color: #ea5455;
    }

    .customers-index .dt-info {
        color: var(--rz-muted);
        font-size: .8rem;
        font-weight: 700;
    }

    .customers-index .dt-paging,
    .customers-index .dt-paging nav {
        display: flex;
        align-items: center;
        gap: .25rem;
        margin: 0;
        padding: 0;
        border: 0;
        background: transparent;
        box-shadow: none;
    }

    .customers-index .dt-paging .dt-paging-button {
        min-width: 1.95rem;
        height: 1.95rem;
        border: 1px solid #dfe4ef !important;
        border-radius: .35rem !important;
        background: #fff !important;
        color: #667085 !important;
        font-size: .78rem;
        font-weight: 800;
    }

    .customers-index .dt-paging .dt-paging-button.current,
    .customers-index .dt-paging .dt-paging-button.current:hover {
        border-color: var(--rz-primary) !important;
        background: var(--rz-primary) !important;
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
