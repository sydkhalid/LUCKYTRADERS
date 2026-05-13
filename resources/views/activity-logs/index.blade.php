@extends('layouts.app')

@section('title', $title)

@section('content')
@php
    $reportLinks = [
        'activity-logs.index' => 'Activity Log List',
        'activity-logs.users' => 'User Activity',
        'activity-logs.modules' => 'Module Activity',
        'activity-logs.dates' => 'Date-wise Activity',
    ];
@endphp

<div class="space-y-6">
    <x-erp.page-header :title="$title" description="Audit trail for ERP users, modules, and record changes." kicker="Audit Trail">
        <x-slot:actions>
            <span class="erp-badge erp-badge-neutral">{{ number_format($totalCount) }} records</span>
        </x-slot:actions>
    </x-erp.page-header>

    <div class="flex flex-wrap gap-2">
        @foreach ($reportLinks as $routeName => $label)
            <a href="{{ route($routeName, request()->query()) }}"
               class="rounded-xl px-4 py-2 text-sm font-black {{ request()->routeIs($routeName) ? 'bg-slate-950 text-white' : 'border border-slate-200 bg-white text-slate-700 shadow-sm hover:bg-slate-50' }}">
                {{ $label }}
            </a>
        @endforeach
    </div>

    <form id="activityLogFilters" class="grid gap-4 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm md:grid-cols-6">
        <div>
            <label class="mb-1 block text-xs font-black uppercase tracking-wide text-slate-500">User</label>
            <select name="user_id" data-searchable class="w-full">
                <option value="">All Users</option>
                @foreach ($users as $user)
                    <option value="{{ $user->id }}" @selected((string) $filters['user_id'] === (string) $user->id)>
                        {{ $user->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="mb-1 block text-xs font-black uppercase tracking-wide text-slate-500">Module</label>
            <select name="module" data-searchable class="w-full">
                <option value="">All Modules</option>
                @foreach ($modules as $module)
                    <option value="{{ $module }}" @selected($filters['module'] === $module)>
                        {{ \Illuminate\Support\Str::headline($module) }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="mb-1 block text-xs font-black uppercase tracking-wide text-slate-500">Action</label>
            <select name="action" data-searchable class="w-full">
                <option value="">All Actions</option>
                @foreach ($actions as $action)
                    <option value="{{ $action }}" @selected($filters['action'] === $action)>
                        {{ \Illuminate\Support\Str::headline($action) }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="mb-1 block text-xs font-black uppercase tracking-wide text-slate-500">From Date</label>
            <input type="date" name="from_date" value="{{ $filters['from_date'] }}" class="w-full">
        </div>

        <div>
            <label class="mb-1 block text-xs font-black uppercase tracking-wide text-slate-500">To Date</label>
            <input type="date" name="to_date" value="{{ $filters['to_date'] }}" class="w-full">
        </div>

        <div class="flex items-end gap-2">
            <button class="erp-primary-button flex-1">Filter</button>
            <button type="button" data-reset-filters class="erp-secondary-button flex-1">Reset</button>
        </div>
    </form>

    @if ($reportMode === 'user')
        <div class="grid gap-4 md:grid-cols-3">
            @forelse ($userSummary as $row)
                <div class="erp-summary-card">
                    <p class="text-sm font-semibold text-slate-500">{{ $row->causer?->email ?? 'Unknown user' }}</p>
                    <p class="mt-1 text-lg font-black text-slate-950">{{ $row->causer?->name ?? 'Unknown user' }}</p>
                    <p class="mt-2 text-sm font-semibold text-slate-700">{{ number_format($row->total) }} actions</p>
                </div>
            @empty
                <x-erp.empty-state class="md:col-span-3" title="No user activity found" />
            @endforelse
        </div>
    @elseif ($reportMode === 'module')
        <div class="grid gap-4 md:grid-cols-4">
            @forelse ($moduleSummary as $row)
                <div class="erp-summary-card">
                    <p class="text-sm font-semibold text-slate-500">Module</p>
                    <p class="mt-1 text-lg font-black text-slate-950">{{ \Illuminate\Support\Str::headline($row->log_name) }}</p>
                    <p class="mt-2 text-sm font-semibold text-slate-700">{{ number_format($row->total) }} actions</p>
                </div>
            @empty
                <x-erp.empty-state class="md:col-span-4" title="No module activity found" />
            @endforelse
        </div>
    @elseif ($reportMode === 'date')
        <div class="grid gap-4 md:grid-cols-4">
            @forelse ($dateSummary as $row)
                <div class="erp-summary-card">
                    <p class="text-sm font-semibold text-slate-500">Date</p>
                    <p class="mt-1 text-lg font-black text-slate-950">{{ \Carbon\Carbon::parse($row->activity_date)->format('d M Y') }}</p>
                    <p class="mt-2 text-sm font-semibold text-slate-700">{{ number_format($row->total) }} actions</p>
                </div>
            @empty
                <x-erp.empty-state class="md:col-span-4" title="No date-wise activity found" />
            @endforelse
        </div>
    @endif

    <div class="visually-hidden" aria-hidden="true">
        @foreach ($activities as $activity)
            <span>{{ $activity->description }}</span>
        @endforeach
    </div>

    <x-erp.datatable
        id="activityLogsTable"
        :ajax-url="route('erp.datatables', 'activity-logs')"
        filter-form="#activityLogFilters"
        search-placeholder="Search activity, module, user..."
        empty="No activity logs found."
    >
        <thead>
            <tr>
                <th class="px-4 py-3" data-column="created_at">Date / Time</th>
                <th class="px-4 py-3" data-column="user" data-orderable="false" data-searchable="false">User</th>
                <th class="px-4 py-3" data-column="event">Action</th>
                <th class="px-4 py-3" data-column="log_name">Module</th>
                <th class="px-4 py-3" data-column="description">Description</th>
                @role('Super Admin')
                    <th class="px-4 py-3 text-right" data-column="actions" data-orderable="false" data-searchable="false">Action</th>
                @endrole
            </tr>
        </thead>
        <tbody></tbody>
    </x-erp.datatable>
</div>
@endsection
