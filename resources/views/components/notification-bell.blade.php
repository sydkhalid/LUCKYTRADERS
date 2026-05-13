@php
    $unreadCount = auth()->user()
        ? app(\App\Services\NotificationAlertService::class)->unreadCount(auth()->user())
        : 0;
@endphp

<div
    class="relative"
    data-notification-bell
    data-dropdown-url="{{ route('notifications.dropdown') }}"
    data-index-url="{{ route('notifications.index') }}"
>
    <button
        type="button"
        data-notification-toggle
        class="relative rounded border border-slate-300 bg-white px-3 py-2 text-sm font-semibold text-slate-700 shadow-sm hover:bg-slate-50"
        aria-label="Notifications"
    >
        <span>Alerts</span>
        <span
            data-notification-count
            class="{{ $unreadCount > 0 ? '' : 'hidden' }} absolute -right-2 -top-2 min-w-5 rounded-full bg-red-600 px-1.5 py-0.5 text-xs font-bold leading-none text-white"
        >{{ $unreadCount }}</span>
    </button>

    <div
        data-notification-panel
        class="absolute right-0 top-full z-50 mt-2 hidden w-96 overflow-hidden rounded border border-slate-200 bg-white shadow-lg"
    ></div>
</div>
