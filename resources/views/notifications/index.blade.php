@extends('layouts.erp')

@section('title', 'Notifications')

@section('content')
@php
    $severityClasses = [
        'danger' => 'bg-red-50 text-red-700 border-red-200',
        'warning' => 'bg-amber-50 text-amber-700 border-amber-200',
        'success' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
        'info' => 'bg-slate-50 text-slate-700 border-slate-200',
    ];
@endphp

<div class="space-y-6">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h2 class="text-lg font-semibold text-gray-900">Notification & Alert Center</h2>
            <p class="text-sm text-gray-500">Low stock, payments, supplier dues, loans, GST, backups, and daily summary alerts.</p>
        </div>
        <form method="POST" action="{{ route('notifications.read-all') }}">
            @csrf
            @method('PATCH')
            <button class="rounded bg-slate-900 px-4 py-2 text-sm font-semibold text-white">Mark All Read</button>
        </form>
    </div>

    <div class="flex flex-wrap gap-2">
        @foreach (['all' => 'All', 'unread' => 'Unread', 'read' => 'Read'] as $status => $label)
            <a href="{{ route('notifications.index', ['status' => $status]) }}"
               class="rounded px-4 py-2 text-sm font-semibold {{ $filter === $status ? 'bg-slate-900 text-white' : 'bg-white text-slate-700 shadow-sm hover:bg-slate-50' }}">
                {{ $label }}
            </a>
        @endforeach
    </div>

    <div class="space-y-3">
        @forelse ($notifications as $notification)
            <div class="border bg-white p-4 shadow-sm {{ $notification->read_at ? 'border-slate-200' : ($severityClasses[$notification->severity] ?? $severityClasses['info']) }}">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <div class="flex flex-wrap items-center gap-2">
                            <h3 class="font-semibold text-slate-900">{{ $notification->title }}</h3>
                            @unless($notification->read_at)
                                <span class="rounded bg-red-100 px-2 py-0.5 text-xs font-semibold text-red-700">Unread</span>
                            @endunless
                            <span class="rounded bg-slate-100 px-2 py-0.5 text-xs font-semibold text-slate-600">{{ \Illuminate\Support\Str::headline($notification->type) }}</span>
                        </div>
                        <p class="mt-1 text-sm text-slate-600">{{ $notification->message }}</p>
                        <p class="mt-2 text-xs text-slate-500">{{ $notification->created_at?->format('d M Y h:i A') }} | {{ $notification->created_at?->diffForHumans() }}</p>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        @if ($notification->action_url)
                            <a href="{{ $notification->action_url }}" class="rounded border border-slate-300 px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50">Open</a>
                        @endif
                        <form method="POST" action="{{ $notification->read_at ? route('notifications.unread', $notification) : route('notifications.read', $notification) }}">
                            @csrf
                            @method('PATCH')
                            <button class="rounded border border-slate-300 px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50">
                                {{ $notification->read_at ? 'Mark Unread' : 'Mark Read' }}
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @empty
            <div class="bg-white p-8 text-center text-sm text-slate-500 shadow-sm">
                No notifications found.
            </div>
        @endforelse
    </div>

    {{ $notifications->links() }}
</div>
@endsection
