<!DOCTYPE html>
<html lang="en" class="h-full scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'ERP') | Lucky Traders ERP</title>
    @php
        $erpFlash = [
            'success' => session('success'),
            'error' => session('error'),
            'warning' => session('warning'),
        ];
    @endphp
    <script>
        window.erpFlash = {{ \Illuminate\Support\Js::from($erpFlash) }};
    </script>
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
    $erpCan = function (?string $permission) use ($erpUser): bool {
        if ($permission === null) {
            return true;
        }

        if (! $erpUser) {
            return false;
        }

        return collect(explode('|', $permission))
            ->map(fn ($value) => trim($value))
            ->filter()
            ->contains(fn ($value) => $erpUser->can($value));
    };
    $erpIsSuperAdmin = $erpUser && method_exists($erpUser, 'hasRole') && $erpUser->hasRole('Super Admin');
    $erpRouteExists = fn (array $item): bool => empty($item['route']) || \Illuminate\Support\Facades\Route::has($item['route']);
    $erpItemVisible = function (array $item) use ($erpCan, $erpIsSuperAdmin, $erpRouteExists): bool {
        if (($item['super_admin'] ?? false) && ! $erpIsSuperAdmin) {
            return false;
        }

        return $erpRouteExists($item) && $erpCan($item['permission'] ?? null);
    };
    $erpItemActive = fn (array $item): bool => request()->routeIs(...($item['active'] ?? [$item['route'] ?? '']));
    $erpNavItems = collect(config('erp_menu', []))
        ->map(function (array $item) use ($erpItemVisible) {
            if (! empty($item['children'])) {
                $item['children'] = collect($item['children'])
                    ->filter(fn (array $child) => $erpItemVisible($child))
                    ->values()
                    ->all();

                return empty($item['children']) ? null : $item;
            }

            return $erpItemVisible($item) ? $item : null;
        })
        ->filter()
        ->values();
@endphp

<div
    x-data="{
        sidebarOpen: false,
        sidebarCollapsed: localStorage.getItem('erp-sidebar-collapsed') === 'true',
        setSidebarCollapsed(value) {
            this.sidebarCollapsed = value;
            localStorage.setItem('erp-sidebar-collapsed', this.sidebarCollapsed ? 'true' : 'false');
        },
        toggleSidebarCollapse() {
            this.setSidebarCollapsed(! this.sidebarCollapsed);
        }
    }"
    class="erp-shell min-h-screen lg:flex"
>
    <div
        x-cloak
        x-show="sidebarOpen"
        x-transition.opacity
        class="fixed inset-0 z-40 bg-slate-950/50 backdrop-blur-sm lg:hidden"
        @click="sidebarOpen = false"
    ></div>

    <aside
        class="erp-sidebar fixed inset-y-0 left-0 z-50 flex w-72 -translate-x-full flex-col border-r border-white/10 bg-slate-950 text-white shadow-2xl transition-all duration-200 lg:static lg:z-auto lg:w-72 lg:translate-x-0 lg:shadow-none"
        :class="{ 'translate-x-0': sidebarOpen, 'lg:!w-20': sidebarCollapsed, 'lg:!w-72': !sidebarCollapsed }"
        aria-label="Primary"
    >
        <div class="flex h-20 items-center gap-3 border-b border-white/10 px-5">
            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-cyan-400 text-sm font-black tracking-wide text-slate-950">
                {{ $erpInitials }}
            </div>
            <div class="min-w-0" x-show="!sidebarCollapsed" x-transition.opacity>
                <p class="truncate text-base font-black tracking-wide">{{ $erpCompanyName }}</p>
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">Steel ERP</p>
            </div>
            <button
                type="button"
                class="ml-auto hidden h-9 w-9 items-center justify-center rounded-xl border border-white/10 bg-white/5 text-slate-300 hover:bg-white/10 hover:text-white lg:inline-flex"
                @click="toggleSidebarCollapse()"
                :aria-label="sidebarCollapsed ? 'Expand sidebar' : 'Collapse sidebar'"
            >
                <span x-text="sidebarCollapsed ? '>' : '<'" class="text-sm font-black"></span>
            </button>
        </div>

        <nav class="flex-1 space-y-2 overflow-y-auto px-3 py-5">
            @foreach ($erpNavItems as $item)
                @php
                    $children = collect($item['children'] ?? []);
                    $hasChildren = $children->isNotEmpty();
                    $isParentActive = $erpItemActive($item) || $children->contains(fn (array $child) => $erpItemActive($child));
                @endphp

                @if ($hasChildren)
                    <div
                        x-data="{ open: {{ $isParentActive ? 'true' : 'false' }} }"
                        class="space-y-1"
                    >
                        <button
                            type="button"
                            class="erp-nav-parent {{ $isParentActive ? 'erp-nav-parent-active' : '' }}"
                            @click="if (sidebarCollapsed) { setSidebarCollapsed(false); open = true } else { open = ! open }"
                            :aria-expanded="open.toString()"
                        >
                            <span class="erp-nav-icon">
                                <svg viewBox="0 0 24 24" aria-hidden="true">
                                    <path d="{{ $item['icon'] }}" />
                                </svg>
                            </span>
                            <span class="min-w-0 flex-1 truncate text-left" x-show="!sidebarCollapsed" x-transition.opacity>{{ $item['label'] }}</span>
                            <svg class="h-4 w-4 shrink-0 transition-transform duration-150" :class="{ 'rotate-90': open }" x-show="!sidebarCollapsed" x-transition.opacity viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd" d="M7.22 4.22a.75.75 0 0 1 1.06 0l5.25 5.25a.75.75 0 0 1 0 1.06l-5.25 5.25a.75.75 0 0 1-1.06-1.06L11.94 10 7.22 5.28a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd" />
                            </svg>
                        </button>

                        <div
                            x-cloak
                            x-show="open && !sidebarCollapsed"
                            x-transition.opacity
                            class="space-y-1 pl-11 pr-1"
                        >
                            @foreach ($children as $child)
                                @php $isActive = $erpItemActive($child); @endphp
                                <a
                                    href="{{ route($child['route']) }}"
                                    class="erp-nav-child-link {{ $isActive ? 'erp-nav-child-link-active' : '' }}"
                                    @click="sidebarOpen = false"
                                    @if ($isActive) aria-current="page" @endif
                                >
                                    <span class="erp-nav-child-dot"></span>
                                    <span class="truncate">{{ $child['label'] }}</span>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @else
                    @php $isActive = $erpItemActive($item); @endphp
                    <a
                        href="{{ route($item['route']) }}"
                        class="erp-nav-parent {{ $isActive ? 'erp-nav-parent-active' : '' }}"
                        @click="sidebarOpen = false"
                        @if ($isActive) aria-current="page" @endif
                    >
                        <span class="erp-nav-icon">
                            <svg viewBox="0 0 24 24" aria-hidden="true">
                                <path d="{{ $item['icon'] }}" />
                            </svg>
                        </span>
                        <span class="min-w-0 flex-1 truncate" x-show="!sidebarCollapsed" x-transition.opacity>{{ $item['label'] }}</span>
                    </a>
                @endif
            @endforeach
        </nav>

        <div class="border-t border-white/10 p-4">
            <div class="rounded-2xl bg-white/5 p-3" x-show="!sidebarCollapsed" x-transition.opacity>
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

                        <div x-data="{ userMenuOpen: false }" class="relative no-print">
                            <button
                                type="button"
                                class="flex items-center gap-3 rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-bold text-slate-700 shadow-sm hover:bg-slate-50"
                                @click="userMenuOpen = ! userMenuOpen"
                                aria-haspopup="true"
                                :aria-expanded="userMenuOpen"
                            >
                                <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-slate-950 text-xs font-black text-white">
                                    {{ mb_substr($erpUser?->name ?: 'U', 0, 1) }}
                                </span>
                                <span class="hidden max-w-36 truncate sm:inline">{{ $erpUser?->name }}</span>
                            </button>

                            <div
                                x-cloak
                                x-show="userMenuOpen"
                                x-transition
                                @click.outside="userMenuOpen = false"
                                class="absolute right-0 z-50 mt-2 w-64 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-xl"
                            >
                                <div class="border-b border-slate-100 px-4 py-3">
                                    <p class="truncate text-sm font-black text-slate-950">{{ $erpUser?->name }}</p>
                                    <p class="mt-1 truncate text-xs font-semibold text-slate-500">{{ $erpUser?->email }}</p>
                                </div>
                                <a href="{{ route('profile.edit') }}" class="block px-4 py-3 text-sm font-bold text-slate-700 hover:bg-slate-50">Profile Settings</a>
                                <form method="POST" action="{{ route('logout') }}" data-confirm-action data-confirm-title="Logout from ERP?" data-confirm-text="Your current screen will close after logout.">
                                    @csrf
                                    <button class="block w-full px-4 py-3 text-left text-sm font-bold text-red-700 hover:bg-red-50">
                                        Logout
                                    </button>
                                </form>
                            </div>
                        </div>
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
