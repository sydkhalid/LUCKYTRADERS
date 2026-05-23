@php
    $erpUser = auth()->user();
    $erpCompanyName = $erpCompany['name'] ?? 'LUCKY TRADERS';
    $erpSidebarStyle = in_array($erpTheme['sidebar_style'] ?? 'dark', ['dark', 'light'], true)
        ? $erpTheme['sidebar_style']
        : 'dark';
    $erpInitials = collect(explode(' ', $erpCompanyName))
        ->filter()
        ->map(fn ($word) => mb_substr($word, 0, 1))
        ->take(2)
        ->implode('') ?: 'LT';
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
    $erpRouteUrl = fn (array $item): string => route($item['route'], $item['params'] ?? []);
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

<aside class="lt-sidebar layout-menu menu-vertical menu bg-menu-theme" data-lt-sidebar data-sidebar-style="{{ $erpSidebarStyle }}" aria-label="Primary navigation">
    <div class="lt-sidebar-inner menu-inner-shadow">
        <div class="lt-sidebar-brand app-brand demo">
            @if (! empty($erpCompany['logo_url']))
                <img src="{{ $erpCompany['logo_url'] }}" alt="{{ $erpCompanyName }}" class="lt-brand-logo app-brand-logo demo">
            @else
                <span class="lt-brand-mark app-brand-logo demo">{{ $erpInitials }}</span>
            @endif

            <div class="lt-brand-text app-brand-text demo menu-text fw-bold ms-2 min-w-0">
                <span class="lt-brand-title text-truncate">{{ $erpCompanyName }}</span>
                <span class="lt-brand-subtitle">{{ $erpBusinessType ?? 'Steel Trading ERP' }}</span>
            </div>

            <button type="button" class="btn btn-sm lt-icon-button layout-menu-toggle menu-link text-large ms-auto d-lg-none" data-lt-sidebar-close aria-label="Close sidebar">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>

        <nav class="lt-sidebar-menu menu-inner py-1" data-simplebar>
            @foreach ($erpNavItems as $item)
                @php
                    $children = collect($item['children'] ?? []);
                    $hasChildren = $children->isNotEmpty();
                    $isParentActive = $erpItemActive($item) || $children->contains(fn (array $child) => $erpItemActive($child));
                    $submenuId = 'lt-menu-'.\Illuminate\Support\Str::slug($item['label']);
                @endphp

                <div class="lt-menu-section menu-item {{ $isParentActive ? 'active open' : '' }}" style="--lt-menu-order: {{ $loop->index }};">
                    @if ($hasChildren)
                        <button
                            type="button"
                            class="lt-menu-toggle menu-link menu-toggle {{ $isParentActive ? 'active' : '' }}"
                            data-lt-menu-toggle="{{ $submenuId }}"
                            aria-expanded="{{ $isParentActive ? 'true' : 'false' }}"
                            title="{{ $item['label'] }}"
                        >
                            <span class="lt-icon menu-icon tf-icons">
                                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="{{ $item['icon'] }}" /></svg>
                            </span>
                            <span class="lt-menu-text text-truncate">{{ $item['label'] }}</span>
                            <span class="lt-menu-count">{{ $children->count() }}</span>
                            <span class="lt-menu-arrow" aria-hidden="true">
                                <svg viewBox="0 0 24 24" focusable="false">
                                    <path d="m9 6 6 6-6 6" />
                                </svg>
                            </span>
                        </button>

                        <div id="{{ $submenuId }}" class="lt-submenu menu-sub {{ $isParentActive ? 'show' : '' }}">
                            @foreach ($children as $child)
                                @php $isActive = $erpItemActive($child); @endphp
                                <a
                                    href="{{ $erpRouteUrl($child) }}"
                                    class="lt-menu-link menu-link {{ $isActive ? 'active' : '' }}"
                                    title="{{ $child['label'] }}"
                                    style="--lt-child-order: {{ $loop->index }};"
                                    @if ($isActive) aria-current="page" @endif
                                >
                                    <span class="lt-menu-text text-truncate">{{ $child['label'] }}</span>
                                </a>
                            @endforeach
                        </div>
                    @else
                        @php $isActive = $erpItemActive($item); @endphp
                        <a
                            href="{{ $erpRouteUrl($item) }}"
                            class="lt-menu-link menu-link {{ $isActive ? 'active' : '' }}"
                            title="{{ $item['label'] }}"
                            @if ($isActive) aria-current="page" @endif
                        >
                            <span class="lt-icon menu-icon tf-icons">
                                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="{{ $item['icon'] }}" /></svg>
                            </span>
                            <span class="lt-menu-text text-truncate">{{ $item['label'] }}</span>
                        </a>
                    @endif
                </div>
            @endforeach
        </nav>

        <div class="lt-sidebar-user menu-user border-top p-3">
            <div class="rounded-3 border bg-white p-3">
                <div class="fw-bold text-truncate">{{ $erpUser?->name }}</div>
                <div class="small opacity-75 text-truncate">{{ $erpUser?->role ?: 'ERP User' }}</div>
            </div>
        </div>
    </div>
</aside>

<div class="lt-sidebar-backdrop" data-lt-sidebar-backdrop></div>
