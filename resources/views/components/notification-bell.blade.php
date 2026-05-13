@php
    $unreadCount = auth()->user()
        ? app(\App\Services\NotificationAlertService::class)->unreadCount(auth()->user())
        : 0;
@endphp

<div
    class="relative shrink-0 nav-item navbar-dropdown dropdown-notifications"
    data-notification-bell
    data-dropdown-url="{{ route('notifications.dropdown') }}"
    data-index-url="{{ route('notifications.index') }}"
>
    <button
        type="button"
        data-notification-toggle
        class="lt-header-control nav-link relative"
        aria-label="Notifications"
        aria-expanded="false"
    >
        <svg width="19" height="19" viewBox="0 0 24 24" aria-hidden="true">
            <path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9Z" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
            <path d="M13.73 21a2 2 0 0 1-3.46 0" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
        </svg>
        <span
            data-notification-count
            class="{{ $unreadCount > 0 ? '' : 'hidden' }} badge bg-danger rounded-pill badge-notifications position-absolute top-0 start-100 translate-middle"
        >{{ $unreadCount }}</span>
    </button>

    <div
        data-notification-panel
        class="lt-notification-panel dropdown-menu dropdown-menu-end p-0 absolute right-0 top-full z-50 mt-2 hidden w-[min(24rem,calc(100vw-2rem))] overflow-hidden"
    ></div>
</div>
