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
                <svg width="20" height="20" viewBox="0 0 24 24" aria-hidden="true">
                    <path d="M4 7h16M4 12h16M4 17h16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
                </svg>
            </button>

            <button type="button" class="btn lt-icon-button d-none d-lg-inline-flex" data-lt-sidebar-collapse aria-label="Toggle sidebar">
                <svg width="20" height="20" viewBox="0 0 24 24" aria-hidden="true">
                    <path d="M4 7h16M4 12h16M4 17h16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
                </svg>
            </button>

            <div class="lt-header-title-block min-w-0">
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
            <div class="lt-header-action-row">
                <x-global-search />
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
                        <span class="lt-user-name text-truncate">{{ $erpUser?->name ?: 'User' }}</span>
                        <svg class="lt-user-chevron" width="16" height="16" viewBox="0 0 24 24" aria-hidden="true">
                            <path d="m6 9 6 6 6-6" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    </button>

                    <div class="dropdown-menu dropdown-menu-end mt-2 shadow border-0 rounded-3 overflow-hidden lt-profile-dropdown" style="width: 17rem;">
                        <div class="px-3 py-3 border-bottom">
                            <div class="fw-black text-truncate">{{ $erpUser?->name ?: 'User' }}</div>
                            <div class="small text-secondary text-truncate">{{ $erpUser?->email }}</div>
                        </div>
                        <a href="{{ route('profile.edit') }}" class="dropdown-item fw-semibold py-2 lt-dropdown-action">
                            <svg width="17" height="17" viewBox="0 0 24 24" aria-hidden="true">
                                <path d="M20 21a8 8 0 0 0-16 0M12 13a5 5 0 1 0 0-10 5 5 0 0 0 0 10Z" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
                            </svg>
                            <span>Profile Settings</span>
                        </a>
                        <form method="POST" action="{{ route('logout') }}" data-confirm-action data-confirm-title="Logout from ERP?" data-confirm-text="Your current screen will close after logout.">
                            @csrf
                            <button class="dropdown-item fw-semibold text-danger py-2 lt-dropdown-action lt-logout-action">
                                <svg width="17" height="17" viewBox="0 0 24 24" aria-hidden="true">
                                    <path d="M10 17l5-5-5-5M15 12H3M21 19V5a2 2 0 0 0-2-2h-5M14 21h5a2 2 0 0 0 2-2" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                                <span>Logout</span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>
