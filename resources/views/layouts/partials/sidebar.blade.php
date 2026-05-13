@php
    $erpUser = auth()->user();
    $erpCompanyName = $erpCompany['name'] ?? 'LUCKY TRADERS';
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

<aside class="lt-sidebar" data-lt-sidebar data-sidebar-style="{{ $erpTheme['sidebar_style'] ?? 'dark' }}" aria-label="Primary navigation">
    <div class="lt-sidebar-inner">
        <div class="lt-sidebar-brand">
            @if (! empty($erpCompany['logo_url']))
                <img src="{{ $erpCompany['logo_url'] }}" alt="{{ $erpCompanyName }}" class="lt-brand-logo">
            @else
                <span class="lt-brand-mark">{{ $erpInitials }}</span>
            @endif

            <div class="lt-brand-text min-w-0">
                <span class="lt-brand-title text-truncate">{{ $erpCompanyName }}</span>
                <span class="lt-brand-subtitle">{{ $erpBusinessType ?? 'Steel Trading ERP' }}</span>
            </div>

            <button type="button" class="btn btn-sm lt-icon-button ms-auto d-lg-none" data-lt-sidebar-close aria-label="Close sidebar">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>

        <nav class="lt-sidebar-menu" data-simplebar>
            @foreach ($erpNavItems as $item)
                @php
                    $children = collect($item['children'] ?? []);
                    $hasChildren = $children->isNotEmpty();
                    $isParentActive = $erpItemActive($item) || $children->contains(fn (array $child) => $erpItemActive($child));
                    $submenuId = 'lt-menu-'.\Illuminate\Support\Str::slug($item['label']);
                @endphp

                <div class="lt-menu-section">
                    @if ($hasChildren)
                        <button
                            type="button"
                            class="lt-menu-toggle {{ $isParentActive ? 'active' : '' }}"
                            data-lt-menu-toggle="{{ $submenuId }}"
                            aria-expanded="{{ $isParentActive ? 'true' : 'false' }}"
                            title="{{ $item['label'] }}"
                        >
                            <span class="lt-icon">
                                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="{{ $item['icon'] }}" /></svg>
                            </span>
                            <span class="lt-menu-text text-truncate">{{ $item['label'] }}</span>
                            <span class="lt-menu-arrow">›</span>
                        </button>

                        <div id="{{ $submenuId }}" class="lt-submenu {{ $isParentActive ? 'show' : '' }}">
                            @foreach ($children as $child)
                                @php $isActive = $erpItemActive($child); @endphp
                                <a
                                    href="{{ route($child['route']) }}"
                                    class="lt-menu-link {{ $isActive ? 'active' : '' }}"
                                    @if ($isActive) aria-current="page" @endif
                                >
                                    <span class="lt-menu-text text-truncate">{{ $child['label'] }}</span>
                                </a>
                            @endforeach
                        </div>
                    @else
                        @php $isActive = $erpItemActive($item); @endphp
                        <a
                            href="{{ route($item['route']) }}"
                            class="lt-menu-link {{ $isActive ? 'active' : '' }}"
                            title="{{ $item['label'] }}"
                            @if ($isActive) aria-current="page" @endif
                        >
                            <span class="lt-icon">
                                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="{{ $item['icon'] }}" /></svg>
                            </span>
                            <span class="lt-menu-text text-truncate">{{ $item['label'] }}</span>
                        </a>
                    @endif
                </div>
            @endforeach
        </nav>

        <div class="lt-sidebar-user border-top border-white border-opacity-10 p-3">
            <div class="rounded-3 border border-white border-opacity-10 bg-white bg-opacity-10 p-3">
                <div class="fw-bold text-truncate">{{ $erpUser?->name }}</div>
                <div class="small opacity-75 text-truncate">{{ $erpUser?->role ?: 'ERP User' }}</div>
            </div>
        </div>
    </div>
</aside>

<div class="lt-sidebar-backdrop" data-lt-sidebar-backdrop></div>
