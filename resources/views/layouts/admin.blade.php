<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>@yield('title', config('app.name')) | {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100">

<div class="min-h-screen flex">

    {{-- Sidebar --}}
    <aside class="w-64 bg-slate-900 text-white min-h-screen">
        <div class="border-b border-slate-700 p-5">
            <div class="text-xl font-bold">Lucky Traders</div>
            <div class="mt-1 text-xs font-medium uppercase text-slate-400">Steel ERP</div>
        </div>

        <nav class="p-4 space-y-2">
            <a href="{{ route('dashboard') }}" class="block px-4 py-2 rounded hover:bg-slate-700">Dashboard</a>
            <a href="#" class="block px-4 py-2 rounded hover:bg-slate-700">Product Master</a>
            <a href="#" class="block px-4 py-2 rounded hover:bg-slate-700">Customers</a>
            <a href="#" class="block px-4 py-2 rounded hover:bg-slate-700">Suppliers</a>
            <a href="#" class="block px-4 py-2 rounded hover:bg-slate-700">Purchases</a>
            <a href="#" class="block px-4 py-2 rounded hover:bg-slate-700">Sales / Billing</a>
            <a href="#" class="block px-4 py-2 rounded hover:bg-slate-700">Cashbook</a>
            <a href="#" class="block px-4 py-2 rounded hover:bg-slate-700">Loans & Payables</a>
            <a href="#" class="block px-4 py-2 rounded hover:bg-slate-700">Partner Capital</a>
            <a href="#" class="block px-4 py-2 rounded hover:bg-slate-700">Reports</a>
        </nav>
    </aside>

    {{-- Main Content --}}
    <main class="flex-1">

        {{-- Topbar --}}
        <header class="bg-white shadow px-6 py-4 flex justify-between items-center">
            <h1 class="text-xl font-semibold text-gray-800">
                @yield('title', 'Dashboard')
            </h1>

            <div class="flex items-center gap-4">
                <span class="text-sm text-gray-600">{{ auth()->user()->name }}</span>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="text-sm text-red-600 hover:underline">
                        Logout
                    </button>
                </form>
            </div>
        </header>

        {{-- Page Content --}}
        <section class="p-6">
            @yield('content')
        </section>

    </main>

</div>

</body>
</html>
