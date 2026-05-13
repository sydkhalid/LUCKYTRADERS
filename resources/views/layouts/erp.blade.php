<!DOCTYPE html>
<html lang="en" class="h-full scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'ERP') | Lucky Traders ERP</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-100 font-sans text-slate-900 antialiased">
@php
    $erpCompanyName = app(\App\Services\SystemSettingService::class)->company()['name'] ?? 'LUCKY TRADERS';
    $erpUser = auth()->user();
    $erpTitle = trim($__env->yieldContent('title')) ?: 'Dashboard';
    $erpInitials = collect(explode(' ', $erpCompanyName))
        ->filter()
        ->map(fn ($word) => mb_substr($word, 0, 1))
        ->take(2)
        ->implode('') ?: 'LT';
    $erpBreadcrumbs = collect(request()->segments())
        ->reject(fn ($segment) => is_numeric($segment))
        ->map(fn ($segment) => \Illuminate\Support\Str::headline($segment))
        ->values();
    $erpCan = fn (?string $permission) => $permission === null || ($erpUser && $erpUser->can($permission));
    $erpIsSuperAdmin = $erpUser && method_exists($erpUser, 'hasRole') && $erpUser->hasRole('Super Admin');
    $erpNavSections = [
        'Overview' => [
            ['label' => 'Dashboard', 'route' => 'dashboard', 'active' => ['dashboard'], 'permission' => 'view_dashboard'],
            ['label' => 'Notifications', 'route' => 'notifications.index', 'active' => ['notifications.*'], 'permission' => null],
        ],
        'Masters' => [
            ['label' => 'Product Categories', 'route' => 'product-categories.index', 'active' => ['product-categories.*'], 'permission' => 'manage_products'],
            ['label' => 'Products', 'route' => 'products.index', 'active' => ['products.*'], 'permission' => 'manage_products'],
            ['label' => 'Customers', 'route' => 'customers.index', 'active' => ['customers.*'], 'permission' => 'manage_customers'],
            ['label' => 'Suppliers', 'route' => 'suppliers.index', 'active' => ['suppliers.*'], 'permission' => 'manage_suppliers'],
        ],
        'Trading' => [
            ['label' => 'Purchases', 'route' => 'purchases.index', 'active' => ['purchases.*'], 'permission' => 'manage_purchases'],
            ['label' => 'Purchase Returns', 'route' => 'purchase-returns.index', 'active' => ['purchase-returns.*'], 'permission' => 'manage_returns'],
            ['label' => 'Quotations', 'route' => 'quotations.index', 'active' => ['quotations.*'], 'permission' => 'manage_quotations'],
            ['label' => 'Sales / Billing', 'route' => 'sales.index', 'active' => ['sales.*'], 'permission' => 'manage_sales'],
            ['label' => 'Sales Returns', 'route' => 'sales-returns.index', 'active' => ['sales-returns.*'], 'permission' => 'manage_returns'],
            ['label' => 'Stock Adjustments', 'route' => 'stock-adjustments.index', 'active' => ['stock-adjustments.*'], 'permission' => 'manage_stock_adjustments'],
        ],
        'Accounts' => [
            ['label' => 'Receipts', 'route' => 'receipts.index', 'active' => ['receipts.*'], 'permission' => 'manage_receipts'],
            ['label' => 'Payments', 'route' => 'payments.index', 'active' => ['payments.*', 'supplier-payments.*'], 'permission' => 'manage_payments'],
            ['label' => 'Ledgers', 'route' => 'ledgers.index', 'active' => ['ledgers.*'], 'permission' => 'manage_ledgers'],
            ['label' => 'Cashbook', 'route' => 'cashbook.index', 'active' => ['cashbook.*', 'bankbook.*'], 'permission' => 'manage_ledgers'],
            ['label' => 'Expenses', 'route' => 'expenses.index', 'active' => ['expenses.*', 'expense-categories.*'], 'permission' => 'manage_expenses'],
        ],
        'Business' => [
            ['label' => 'Loans', 'route' => 'loans.index', 'active' => ['loans.*'], 'permission' => 'manage_loans'],
            ['label' => 'Partners', 'route' => 'partners.index', 'active' => ['partners.*'], 'permission' => 'manage_partners'],
        ],
        'Reports' => [
            ['label' => 'GST Reports', 'route' => 'gst-reports.index', 'active' => ['gst-reports.*'], 'permission' => 'view_gst_reports'],
            ['label' => 'Reports', 'route' => 'reports.index', 'active' => ['reports.*'], 'permission' => 'view_reports'],
        ],
        'Admin' => [
            ['label' => 'Users & Roles', 'route' => 'users.index', 'active' => ['users.*'], 'permission' => 'manage_users'],
            ['label' => 'Activity Logs', 'route' => 'activity-logs.index', 'active' => ['activity-logs.*'], 'permission' => 'view_activity_logs'],
            ['label' => 'Settings', 'route' => 'settings.company', 'active' => ['settings.company', 'settings.invoice', 'settings.bank', 'settings.terms', 'settings.media'], 'permission' => 'manage_settings'],
            ['label' => 'Testing Checklist', 'route' => 'settings.testing-checklist', 'active' => ['settings.testing-checklist'], 'permission' => 'manage_settings'],
            ['label' => 'Backup System', 'route' => 'settings.backups.index', 'active' => ['settings.backups.*'], 'permission' => null, 'super_admin' => true],
        ],
    ];
@endphp

<div x-data="{ sidebarOpen: false }" class="erp-shell min-h-screen lg:flex">
    <div
        x-cloak
        x-show="sidebarOpen"
        x-transition.opacity
        class="fixed inset-0 z-40 bg-slate-950/50 backdrop-blur-sm lg:hidden"
        @click="sidebarOpen = false"
    ></div>

    <aside
        class="erp-sidebar fixed inset-y-0 left-0 z-50 flex w-72 -translate-x-full flex-col border-r border-white/10 bg-slate-950 text-white shadow-2xl transition-transform duration-200 lg:static lg:z-auto lg:translate-x-0 lg:shadow-none"
        :class="{ 'translate-x-0': sidebarOpen }"
        aria-label="Primary"
    >
        <div class="flex h-20 items-center gap-3 border-b border-white/10 px-5">
            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-cyan-400 text-sm font-black tracking-wide text-slate-950">
                {{ $erpInitials }}
            </div>
            <div class="min-w-0">
                <p class="truncate text-base font-black tracking-wide">{{ $erpCompanyName }}</p>
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">Steel ERP</p>
            </div>
        </div>

        <nav class="flex-1 space-y-5 overflow-y-auto px-3 py-5">
            @foreach ($erpNavSections as $section => $items)
                @php
                    $visibleItems = collect($items)->filter(function ($item) use ($erpCan, $erpIsSuperAdmin) {
                        return ($item['super_admin'] ?? false) ? $erpIsSuperAdmin : $erpCan($item['permission'] ?? null);
                    });
                @endphp

                @if ($visibleItems->isNotEmpty())
                    <div>
                        <p class="px-3 pb-2 text-[11px] font-bold uppercase tracking-[0.22em] text-slate-500">{{ $section }}</p>
                        <div class="space-y-1">
                            @foreach ($visibleItems as $item)
                                @php $isActive = request()->routeIs(...$item['active']); @endphp
                                <a
                                    href="{{ route($item['route']) }}"
                                    class="erp-nav-link {{ $isActive ? 'erp-nav-link-active' : '' }}"
                                    @if ($isActive) aria-current="page" @endif
                                >
                                    <span class="erp-nav-dot"></span>
                                    <span class="truncate">{{ $item['label'] }}</span>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif
            @endforeach
        </nav>

        <div class="border-t border-white/10 p-4">
            <div class="rounded-2xl bg-white/5 p-3">
                <p class="truncate text-sm font-semibold">{{ $erpUser?->name }}</p>
                <p class="mt-1 truncate text-xs text-slate-400">{{ $erpUser?->role ?: 'ERP User' }}</p>
            </div>
        </div>
    </aside>

    <div class="flex min-w-0 flex-1 flex-col">
        <header class="erp-topbar sticky top-0 z-30 border-b border-slate-200/80 bg-white/90 px-4 py-3 shadow-sm backdrop-blur-xl sm:px-6 lg:px-8">
            <div class="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
                <div class="flex min-w-0 items-start gap-3">
                    <button
                        type="button"
                        class="mt-1 inline-flex h-10 w-10 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-700 shadow-sm hover:bg-slate-50 lg:hidden"
                        @click="sidebarOpen = true"
                        aria-label="Open sidebar"
                    >
                        <span class="block h-0.5 w-5 rounded bg-current before:mt-[-6px] before:block before:h-0.5 before:w-5 before:rounded before:bg-current after:mt-[10px] after:block after:h-0.5 after:w-5 after:rounded after:bg-current"></span>
                    </button>

                    <div class="min-w-0">
                        <nav class="mb-1 flex flex-wrap items-center gap-1 text-xs font-semibold uppercase tracking-wide text-slate-500" aria-label="Breadcrumb">
                            @if ($erpBreadcrumbs->isEmpty() || request()->routeIs('dashboard'))
                                <span class="text-slate-700">Dashboard</span>
                            @else
                                <a href="{{ route('dashboard') }}" class="hover:text-slate-900">Dashboard</a>
                                @foreach ($erpBreadcrumbs as $breadcrumb)
                                    <span class="text-slate-300">/</span>
                                    <span class="{{ $loop->last ? 'text-slate-700' : '' }}">{{ $breadcrumb }}</span>
                                @endforeach
                            @endif
                        </nav>
                        <h1 class="truncate text-2xl font-black tracking-tight text-slate-950">@yield('title')</h1>
                    </div>
                </div>

                <div class="flex flex-col gap-3 sm:flex-row sm:items-center xl:justify-end">
                    <x-global-search />

                    <div class="flex items-center gap-2 sm:justify-end">
                        <x-notification-bell />

                        <form method="POST" action="{{ route('logout') }}" class="no-print">
                            @csrf
                            <button class="rounded-xl border border-red-200 bg-red-50 px-3 py-2 text-sm font-bold text-red-700 shadow-sm hover:bg-red-100">
                                Logout
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </header>

        <main class="erp-content flex-1 px-4 py-6 sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="mb-5 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800 shadow-sm">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="mb-5 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-semibold text-red-700 shadow-sm">
                    {{ session('error') }}
                </div>
            @endif

            @yield('content')
        </main>
    </div>
</div>

</body>
</html>
