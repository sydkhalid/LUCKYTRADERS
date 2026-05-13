<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Lucky Traders ERP</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100">
@php
    $erpCompanyName = app(\App\Services\SystemSettingService::class)->company()['name'] ?? 'LUCKY TRADERS';
@endphp

<div class="flex min-h-screen">

    {{-- Sidebar --}}
    <aside class="w-64 bg-slate-900 text-white">
        <div class="p-5 text-2xl font-bold border-b border-slate-700">
            {{ $erpCompanyName }}
        </div>

        <nav class="p-4 space-y-2">
            @can('view_dashboard')
                <a href="{{ route('dashboard') }}" class="block px-4 py-2 rounded hover:bg-slate-700 {{ request()->routeIs('dashboard') ? 'bg-slate-700' : '' }}">Dashboard</a>
            @endcan
            @can('manage_products')
                <a href="{{ route('product-categories.index') }}" class="block px-4 py-2 rounded hover:bg-slate-700 {{ request()->routeIs('product-categories.*') ? 'bg-slate-700' : '' }}">Product Categories</a>
                <a href="{{ route('products.index') }}" class="block px-4 py-2 rounded hover:bg-slate-700 {{ request()->routeIs('products.*') ? 'bg-slate-700' : '' }}">Products</a>
            @endcan
            @can('manage_customers')
                <a href="{{ route('customers.index') }}" class="block px-4 py-2 rounded hover:bg-slate-700 {{ request()->routeIs('customers.*') ? 'bg-slate-700' : '' }}">Customers</a>
            @endcan
            @can('manage_suppliers')
                <a href="{{ route('suppliers.index') }}" class="block px-4 py-2 rounded hover:bg-slate-700 {{ request()->routeIs('suppliers.*') ? 'bg-slate-700' : '' }}">Suppliers</a>
            @endcan
            @can('manage_purchases')
                <a href="{{ route('purchases.index') }}" class="block px-4 py-2 rounded hover:bg-slate-700 {{ request()->routeIs('purchases.*') ? 'bg-slate-700' : '' }}">Purchases</a>
                <a href="{{ route('purchase-returns.index') }}" class="block px-4 py-2 rounded hover:bg-slate-700 {{ request()->routeIs('purchase-returns.*') ? 'bg-slate-700' : '' }}">Purchase Returns</a>
            @endcan
            @can('manage_sales')
                <a href="{{ route('quotations.index') }}" class="block px-4 py-2 rounded hover:bg-slate-700 {{ request()->routeIs('quotations.*') ? 'bg-slate-700' : '' }}">Quotations</a>
                <a href="{{ route('sales.index') }}" class="block px-4 py-2 rounded hover:bg-slate-700 {{ request()->routeIs('sales.*') ? 'bg-slate-700' : '' }}">Sales / Billing</a>
                <a href="{{ route('sales-returns.index') }}" class="block px-4 py-2 rounded hover:bg-slate-700 {{ request()->routeIs('sales-returns.*') ? 'bg-slate-700' : '' }}">Sales Returns</a>
            @endcan
            @canany(['manage_sales', 'manage_payments'])
                <a href="{{ route('receipts.create') }}" class="block px-4 py-2 rounded hover:bg-slate-700 {{ request()->routeIs('receipts.*') ? 'bg-slate-700' : '' }}">Receipts</a>
            @endcanany
            @can('manage_payments')
                <a href="{{ route('payments.index') }}" class="block px-4 py-2 rounded hover:bg-slate-700 {{ request()->routeIs('payments.*', 'supplier-payments.*') ? 'bg-slate-700' : '' }}">Payments</a>
                <a href="{{ route('ledgers.index') }}" class="block px-4 py-2 rounded hover:bg-slate-700 {{ request()->routeIs('ledgers.*') ? 'bg-slate-700' : '' }}">Ledgers</a>
                <a href="{{ route('cashbook.index') }}" class="block px-4 py-2 rounded hover:bg-slate-700 {{ request()->routeIs('cashbook.*', 'bankbook.*') ? 'bg-slate-700' : '' }}">Cashbook</a>
            @endcan
            @can('manage_expenses')
                <a href="{{ route('expenses.index') }}" class="block px-4 py-2 rounded hover:bg-slate-700 {{ request()->routeIs('expenses.*', 'expense-categories.*') ? 'bg-slate-700' : '' }}">Expenses</a>
            @endcan
            @can('manage_stock_adjustments')
                <a href="{{ route('stock-adjustments.index') }}" class="block px-4 py-2 rounded hover:bg-slate-700 {{ request()->routeIs('stock-adjustments.*') ? 'bg-slate-700' : '' }}">Stock Adjustments</a>
            @endcan
            @can('manage_loans')
                <a href="{{ route('loans.index') }}" class="block px-4 py-2 rounded hover:bg-slate-700 {{ request()->routeIs('loans.*') ? 'bg-slate-700' : '' }}">Loans</a>
            @endcan
            @can('manage_partners')
                <a href="{{ route('partners.index') }}" class="block px-4 py-2 rounded hover:bg-slate-700 {{ request()->routeIs('partners.*') ? 'bg-slate-700' : '' }}">Partners</a>
            @endcan
            @can('view_gst_reports')
                <a href="{{ route('gst-reports.index') }}" class="block px-4 py-2 rounded hover:bg-slate-700 {{ request()->routeIs('gst-reports.*') ? 'bg-slate-700' : '' }}">GST Reports</a>
            @endcan
            @can('manage_users')
                <a href="{{ route('users.index') }}" class="block px-4 py-2 rounded hover:bg-slate-700 {{ request()->routeIs('users.*') ? 'bg-slate-700' : '' }}">Users & Roles</a>
            @endcan
            @can('manage_settings')
                <a href="{{ route('settings.company') }}" class="block px-4 py-2 rounded hover:bg-slate-700 {{ request()->routeIs('settings.*') ? 'bg-slate-700' : '' }}">Settings</a>
            @endcan
        </nav>
    </aside>

    {{-- Main Content --}}
    <main class="flex-1">
        <header class="bg-white shadow px-6 py-4 flex justify-between">
            <h1 class="text-xl font-semibold">@yield('title')</h1>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button class="text-red-600 font-semibold">Logout</button>
            </form>
        </header>

        <section class="p-6">
            @if (session('success'))
                <div class="mb-5 rounded border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="mb-5 rounded border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-700">
                    {{ session('error') }}
                </div>
            @endif

            @yield('content')
        </section>
    </main>

</div>

</body>
</html>
