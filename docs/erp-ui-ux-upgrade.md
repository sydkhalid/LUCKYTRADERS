# Lucky Traders ERP UI/UX Upgrade

## Package Installation Commands

```bash
composer require yajra/laravel-datatables-oracle
npm install sweetalert2 chart.js datatables.net datatables.net-dt datatables.net-buttons datatables.net-buttons-dt datatables.net-responsive datatables.net-responsive-dt jquery select2 jszip pdfmake
npm run build
```

The current checkout already includes these packages in `composer.json` and `package.json`.

## Updated Layout Blade

The enterprise shell is implemented in `resources/views/layouts/erp.blade.php`.

It includes:
- Collapsible desktop sidebar and mobile drawer.
- Active menu highlighting.
- Top navbar with breadcrumbs, page title, global search, notification bell, and user dropdown.
- CSRF meta tag and Vite assets.
- SweetAlert-ready flash payload.

Use it from ERP pages:

```blade
@extends('layouts.erp')

@section('title', 'Products')

@section('content')
    <x-erp.page-header
        title="Products"
        description="Steel items, rates, GST, HSN, and opening stock."
        kicker="Inventory Master"
    >
        <x-slot:actions>
            <a href="{{ route('products.create') }}" class="erp-primary-button">Add Product</a>
        </x-slot:actions>
    </x-erp.page-header>
@endsection
```

## DataTable Setup Example

Server route:

```php
Route::get('/erp/datatables/{module}', ErpDataTableController::class)
    ->middleware('auth')
    ->name('erp.datatables');
```

Blade:

```blade
<x-erp.datatable
    id="productsTable"
    :ajax-url="route('erp.datatables', 'products')"
    filter-form="#productFilters"
    search-placeholder="Search product, code, category, HSN..."
    empty="No products found."
>
    <thead>
        <tr>
            <th data-column="code">Code</th>
            <th data-column="name">Product</th>
            <th data-column="category" data-orderable="false" data-searchable="false">Category</th>
            <th data-column="status">Status</th>
            <th data-column="actions" data-orderable="false" data-searchable="false">Action</th>
        </tr>
    </thead>
    <tbody></tbody>
</x-erp.datatable>
```

Controller:

```php
return DataTables::eloquent(
    Product::query()->with('category')->select('products.*')
)
    ->addColumn('category', fn (Product $product) => $product->category?->name ?: '-')
    ->editColumn('status', fn (Product $product) => $this->badge($product->status))
    ->addColumn('actions', fn (Product $product) => $this->actions([
        ['View', route('products.show', $product)],
        ['Edit', route('products.edit', $product)],
    ], route('products.destroy', $product), 'Delete this product?'))
    ->rawColumns(['status', 'actions'])
    ->toJson();
```

## SweetAlert Setup

Global setup is in `resources/js/erp-ux.js`.

```js
toast('success', payload.message || 'Saved successfully.');
modal('error', payload.message || 'Unable to save this record.');
```

Delete confirmation is automatic for:

```blade
<form method="POST" action="{{ route('products.destroy', $product) }}" data-confirm-delete data-confirm-title="Delete this product?">
    @csrf
    @method('DELETE')
    <button>Delete</button>
</form>
```

## AJAX Form Example

```blade
<form method="POST" action="{{ route('products.store') }}" data-ajax-form>
    @csrf
    <div data-ajax-errors class="hidden rounded-xl border border-red-200 bg-red-50 p-3 text-sm text-red-700"></div>
    <input name="name" required>
    <p data-error-for="name" class="hidden text-xs font-semibold text-red-600"></p>
    <button data-loading-text="Saving product...">Save Product</button>
</form>
```

Expected JSON response:

```json
{
  "success": true,
  "message": "Product created successfully.",
  "redirect": "https://example.test/products"
}
```

## Dashboard Chart.js Example

Dashboard data endpoint:

```php
Route::get('/dashboard/charts', [DashboardController::class, 'chartData'])
    ->middleware('permission:view_dashboard')
    ->name('dashboard.charts');
```

Blade root:

```blade
<div data-dashboard-charts="{{ route('dashboard.charts') }}">
    <canvas id="monthlySalesChart"></canvas>
</div>
```

JavaScript dispatches AJAX-loaded data:

```js
fetch(url.toString(), { headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' } })
    .then((response) => response.json())
    .then((charts) => {
        window.dispatchEvent(new CustomEvent('erp:dashboard-charts', { detail: charts }));
    });
```

## Reusable Blade Components

- `resources/views/components/erp/page-header.blade.php`
- `resources/views/components/erp/datatable.blade.php`
- `resources/views/components/erp/status-badge.blade.php`
- `resources/views/components/erp/form-card.blade.php`
- `resources/views/components/erp/empty-state.blade.php`
- `resources/views/components/erp/ajax-errors.blade.php`

## Testing Checklist

- Login as Super Admin and verify all sidebar modules are visible.
- Open dashboard and confirm KPI cards, charts, recent tables, and notifications load.
- Verify `/dashboard/charts` returns JSON.
- Check DataTables search, pagination, sorting, export, and filters for products, categories, customers, suppliers, purchases, sales, quotations, receipts, payments, loans, partners, expenses, stock adjustments, returns, GST reports, users, and activity logs.
- Create and update product, customer, supplier, purchase, sale, receipt, payment, loan transaction, partner transaction, expense, stock adjustment, and settings records using AJAX forms.
- Confirm validation errors render inline without page reload.
- Confirm delete and cancel actions show SweetAlert warning before submitting.
- Verify customer/product/supplier searchable dropdowns work on billing, purchase, receipt, and payment pages.
- Check billing and purchase live totals, GST/non-GST behavior, paid amount, balance, and stock/rate lookup.
- Check mobile layout: sidebar drawer, stacked cards, scrollable tables, and usable forms.
- Run `php artisan test`.
- Run `npm run build`.
