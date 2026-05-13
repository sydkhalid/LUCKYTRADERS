<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Lucky Traders ERP</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100">

<div class="flex min-h-screen">

    {{-- Sidebar --}}
    <aside class="w-64 bg-slate-900 text-white">
        <div class="p-5 text-2xl font-bold border-b border-slate-700">
            LUCKY TRADERS
        </div>

        <nav class="p-4 space-y-2">
            <a href="{{ route('dashboard') }}" class="block px-4 py-2 rounded hover:bg-slate-700 {{ request()->routeIs('dashboard') ? 'bg-slate-700' : '' }}">Dashboard</a>
            <a href="{{ route('products.index') }}" class="block px-4 py-2 rounded hover:bg-slate-700 {{ request()->routeIs('products.*') ? 'bg-slate-700' : '' }}">Products</a>
            <a href="{{ route('product-categories.index') }}" class="block px-4 py-2 rounded hover:bg-slate-700 {{ request()->routeIs('product-categories.*') ? 'bg-slate-700' : '' }}">Categories</a>
            <a href="{{ route('customers.index') }}" class="block px-4 py-2 rounded hover:bg-slate-700 {{ request()->routeIs('customers.*') ? 'bg-slate-700' : '' }}">Customers</a>
            <a href="{{ route('suppliers.index') }}" class="block px-4 py-2 rounded hover:bg-slate-700 {{ request()->routeIs('suppliers.*') ? 'bg-slate-700' : '' }}">Suppliers</a>
            <a href="{{ route('purchases.index') }}" class="block px-4 py-2 rounded hover:bg-slate-700 {{ request()->routeIs('purchases.*') ? 'bg-slate-700' : '' }}">Purchases</a>
            <a href="{{ route('sales.index') }}" class="block px-4 py-2 rounded hover:bg-slate-700 {{ request()->routeIs('sales.*') ? 'bg-slate-700' : '' }}">Sales / Billing</a>
            <a href="{{ route('receipts.create') }}" class="block px-4 py-2 rounded hover:bg-slate-700 {{ request()->routeIs('receipts.*') ? 'bg-slate-700' : '' }}">Receipts</a>
            <a href="{{ route('payments.index') }}" class="block px-4 py-2 rounded hover:bg-slate-700 {{ request()->routeIs('payments.*', 'supplier-payments.*') ? 'bg-slate-700' : '' }}">Payments</a>
            <a href="{{ route('ledgers.index') }}" class="block px-4 py-2 rounded hover:bg-slate-700 {{ request()->routeIs('ledgers.*') ? 'bg-slate-700' : '' }}">Ledgers</a>
            <a href="{{ route('cashbook.index') }}" class="block px-4 py-2 rounded hover:bg-slate-700 {{ request()->routeIs('cashbook.*', 'bankbook.*') ? 'bg-slate-700' : '' }}">Cashbook</a>
            <a href="#" class="block px-4 py-2 rounded hover:bg-slate-700">Stock</a>
            <a href="#" class="block px-4 py-2 rounded hover:bg-slate-700">Loans</a>
            <a href="#" class="block px-4 py-2 rounded hover:bg-slate-700">Partners</a>
            <a href="#" class="block px-4 py-2 rounded hover:bg-slate-700">GST Reports</a>
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
