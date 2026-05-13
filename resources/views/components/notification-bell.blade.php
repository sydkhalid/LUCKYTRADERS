@php
    $unreadCount = auth()->user()
        ? app(\App\Services\NotificationAlertService::class)->unreadCount(auth()->user())
        : 0;
@endphp

<div
    class="relative shrink-0"
    data-notification-bell
    data-dropdown-url="{{ route('notifications.dropdown') }}"
    data-index-url="{{ route('notifications.index') }}"
>
    <button
        type="button"
        data-notification-toggle
        class="relative inline-flex h-11 items-center gap-2 rounded-xl border border-slate-200 bg-white px-3 text-sm font-bold text-slate-700 shadow-sm hover:border-cyan-200 hover:bg-cyan-50/70 hover:text-slate-950"
        aria-label="Notifications"
    >
        <span class="relative h-4 w-4 rounded-full border-2 border-slate-500 before:absolute before:-bottom-1 before:left-1/2 before:h-1 before:w-2 before:-translate-x-1/2 before:rounded-b-full before:border-x-2 before:border-b-2 before:border-slate-500"></span>
        <span class="hidden sm:inline">Alerts</span>
        <span
            data-notification-count
            class="{{ $unreadCount > 0 ? '' : 'hidden' }} absolute -right-2 -top-2 min-w-5 rounded-full bg-red-600 px-1.5 py-0.5 text-xs font-black leading-none text-white ring-2 ring-white"
        >{{ $unreadCount }}</span>
    </button>

    <div
        data-notification-panel
        class="absolute right-0 top-full z-50 mt-2 hidden w-[min(24rem,calc(100vw-2rem))] overflow-hidden rounded-xl border border-slate-200 bg-white shadow-xl shadow-slate-300/30"
    ></div>
</div>
