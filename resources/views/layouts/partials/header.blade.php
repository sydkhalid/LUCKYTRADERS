@php
    $erpUser = auth()->user();
    $erpTitle = trim($__env->yieldContent('title')) ?: 'Dashboard';
    $erpBreadcrumbs = collect(request()->segments())
        ->reject(fn ($segment) => is_numeric($segment))
        ->map(fn ($segment) => \Illuminate\Support\Str::headline($segment))
        ->values();
@endphp

<header class="lt-header">
    <div class="lt-header-grid">
        <div class="lt-title-row">
            <button type="button" class="btn lt-icon-button d-lg-none" data-lt-sidebar-open aria-label="Open sidebar">
                <span class="d-block rounded" style="width: 20px; height: 2px; background: currentColor; box-shadow: 0 -6px 0 currentColor, 0 6px 0 currentColor;"></span>
            </button>

            <button type="button" class="btn lt-icon-button d-none d-lg-inline-flex" data-lt-sidebar-collapse aria-label="Toggle sidebar">
                <span class="d-block rounded" style="width: 18px; height: 2px; background: currentColor; box-shadow: 0 -6px 0 currentColor, 0 6px 0 currentColor;"></span>
            </button>

            <div class="min-w-0">
                <div class="lt-breadcrumb text-truncate">
                    @if ($erpBreadcrumbs->isEmpty() || request()->routeIs('dashboard'))
                        <span>Dashboard</span>
                    @else
                        <a href="{{ route('dashboard') }}" class="text-decoration-none text-reset">Dashboard</a>
                        @foreach ($erpBreadcrumbs as $breadcrumb)
                            <span class="mx-1 text-secondary">/</span>
                            <span class="{{ $loop->last ? '' : 'lt-breadcrumb-extra' }}">{{ $breadcrumb }}</span>
                        @endforeach
                    @endif
                </div>
                <h1 class="lt-page-title text-truncate">{{ $erpTitle }}</h1>
            </div>
        </div>

        <div class="lt-header-actions">
            <x-global-search />

            <div class="lt-header-action-row">
                <x-notification-bell />

                <div class="dropdown no-print">
                    <button
                        type="button"
                        class="lt-user-button"
                        data-bs-toggle="dropdown"
                        data-bs-auto-close="outside"
                        aria-expanded="false"
                    >
                        <span class="lt-avatar">{{ mb_substr($erpUser?->name ?: 'U', 0, 1) }}</span>
                        <span class="lt-user-name text-truncate">{{ $erpUser?->name }}</span>
                        <span class="lt-user-name text-secondary">▾</span>
                    </button>

                    <div class="dropdown-menu dropdown-menu-end mt-2 shadow border-0 rounded-3 overflow-hidden" style="width: 17rem;">
                        <div class="px-3 py-3 border-bottom">
                            <div class="fw-black text-truncate">{{ $erpUser?->name }}</div>
                            <div class="small text-secondary text-truncate">{{ $erpUser?->email }}</div>
                        </div>
                        <a href="{{ route('profile.edit') }}" class="dropdown-item fw-semibold py-2">Profile Settings</a>
                        <form method="POST" action="{{ route('logout') }}" data-confirm-action data-confirm-title="Logout from ERP?" data-confirm-text="Your current screen will close after logout.">
                            @csrf
                            <button class="dropdown-item fw-semibold text-danger py-2">Logout</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>
