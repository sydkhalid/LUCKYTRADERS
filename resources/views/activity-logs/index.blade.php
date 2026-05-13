@extends('layouts.erp')

@section('title', $title)

@section('content')
@php
    $reportLinks = [
        'activity-logs.index' => 'Activity Log List',
        'activity-logs.users' => 'User Activity',
        'activity-logs.modules' => 'Module Activity',
        'activity-logs.dates' => 'Date-wise Activity',
    ];

    $formatPayload = function ($payload) {
        if ($payload instanceof \Illuminate\Support\Collection) {
            $payload = $payload->all();
        }

        if (empty($payload)) {
            return '-';
        }

        return \Illuminate\Support\Str::limit(json_encode($payload, JSON_PRETTY_PRINT), 500);
    };
@endphp

<div class="space-y-6">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <p class="text-sm font-medium text-slate-500">Audit Trail</p>
            <h2 class="text-2xl font-bold text-slate-900">{{ $title }}</h2>
        </div>
        <div class="rounded bg-slate-900 px-4 py-2 text-sm font-semibold text-white">
            {{ number_format($totalCount) }} records
        </div>
    </div>

    <div class="flex flex-wrap gap-2">
        @foreach ($reportLinks as $routeName => $label)
            <a href="{{ route($routeName, request()->query()) }}"
               class="rounded px-4 py-2 text-sm font-semibold {{ request()->routeIs($routeName) ? 'bg-slate-900 text-white' : 'bg-white text-slate-700 shadow-sm hover:bg-slate-50' }}">
                {{ $label }}
            </a>
        @endforeach
    </div>

    <div class="bg-white p-5 shadow-sm">
        <form method="GET" class="grid gap-4 md:grid-cols-6">
            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700">User</label>
                <select name="user_id" class="w-full rounded border-slate-300 text-sm">
                    <option value="">All Users</option>
                    @foreach ($users as $user)
                        <option value="{{ $user->id }}" @selected((string) $filters['user_id'] === (string) $user->id)>
                            {{ $user->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700">Module</label>
                <select name="module" class="w-full rounded border-slate-300 text-sm">
                    <option value="">All Modules</option>
                    @foreach ($modules as $module)
                        <option value="{{ $module }}" @selected($filters['module'] === $module)>
                            {{ \Illuminate\Support\Str::headline($module) }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700">Action</label>
                <select name="action" class="w-full rounded border-slate-300 text-sm">
                    <option value="">All Actions</option>
                    @foreach ($actions as $action)
                        <option value="{{ $action }}" @selected($filters['action'] === $action)>
                            {{ \Illuminate\Support\Str::headline($action) }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700">From Date</label>
                <input type="date" name="from_date" value="{{ $filters['from_date'] }}" class="w-full rounded border-slate-300 text-sm">
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700">To Date</label>
                <input type="date" name="to_date" value="{{ $filters['to_date'] }}" class="w-full rounded border-slate-300 text-sm">
            </div>

            <div class="flex items-end gap-2">
                <button class="rounded bg-slate-900 px-4 py-2 text-sm font-semibold text-white">Filter</button>
                <a href="{{ url()->current() }}" class="rounded border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700">Reset</a>
            </div>
        </form>
    </div>

    @if ($reportMode === 'user')
        <div class="grid gap-4 md:grid-cols-3">
            @forelse ($userSummary as $row)
                <div class="bg-white p-4 shadow-sm">
                    <p class="text-sm text-slate-500">{{ $row->causer?->email ?? 'Unknown user' }}</p>
                    <p class="mt-1 text-lg font-bold text-slate-900">{{ $row->causer?->name ?? 'Unknown user' }}</p>
                    <p class="mt-2 text-sm font-semibold text-slate-700">{{ number_format($row->total) }} actions</p>
                </div>
            @empty
                <div class="bg-white p-4 text-sm text-slate-500 shadow-sm md:col-span-3">No user activity found for the selected filters.</div>
            @endforelse
        </div>
    @elseif ($reportMode === 'module')
        <div class="grid gap-4 md:grid-cols-4">
            @forelse ($moduleSummary as $row)
                <div class="bg-white p-4 shadow-sm">
                    <p class="text-sm text-slate-500">Module</p>
                    <p class="mt-1 text-lg font-bold text-slate-900">{{ \Illuminate\Support\Str::headline($row->log_name) }}</p>
                    <p class="mt-2 text-sm font-semibold text-slate-700">{{ number_format($row->total) }} actions</p>
                </div>
            @empty
                <div class="bg-white p-4 text-sm text-slate-500 shadow-sm md:col-span-4">No module activity found for the selected filters.</div>
            @endforelse
        </div>
    @elseif ($reportMode === 'date')
        <div class="grid gap-4 md:grid-cols-4">
            @forelse ($dateSummary as $row)
                <div class="bg-white p-4 shadow-sm">
                    <p class="text-sm text-slate-500">Date</p>
                    <p class="mt-1 text-lg font-bold text-slate-900">{{ \Carbon\Carbon::parse($row->activity_date)->format('d M Y') }}</p>
                    <p class="mt-2 text-sm font-semibold text-slate-700">{{ number_format($row->total) }} actions</p>
                </div>
            @empty
                <div class="bg-white p-4 text-sm text-slate-500 shadow-sm md:col-span-4">No date-wise activity found for the selected filters.</div>
            @endforelse
        </div>
    @endif

    <div class="overflow-hidden bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-4 py-3">Date / Time</th>
                        <th class="px-4 py-3">User</th>
                        <th class="px-4 py-3">Action</th>
                        <th class="px-4 py-3">Module / Record</th>
                        <th class="px-4 py-3">IP / Device</th>
                        <th class="px-4 py-3">Old Values</th>
                        <th class="px-4 py-3">New Values</th>
                        @role('Super Admin')
                            <th class="px-4 py-3"></th>
                        @endrole
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($activities as $activity)
                        @php
                            $properties = $activity->properties;
                            $role = $properties->get('role') ?? ($activity->causer && method_exists($activity->causer, 'primaryRoleName') ? $activity->causer->primaryRoleName() : null);
                            $recordName = $properties->get('record_name')
                                ?? ($activity->subject?->name ?? $activity->subject?->sale_no ?? $activity->subject?->purchase_no ?? $activity->subject?->payment_no ?? $activity->subject?->loan_no ?? $activity->subject?->quotation_no ?? null)
                                ?? ($activity->subject_type ? class_basename($activity->subject_type).' #'.$activity->subject_id : '-');
                        @endphp
                        <tr class="align-top">
                            <td class="whitespace-nowrap px-4 py-3 text-slate-700">
                                {{ $activity->created_at?->format('d M Y h:i A') }}
                            </td>
                            <td class="px-4 py-3">
                                <div class="font-semibold text-slate-900">{{ $properties->get('user_name') ?? $activity->causer?->name ?? 'System' }}</div>
                                <div class="text-xs text-slate-500">{{ $role ?? 'No role' }}</div>
                            </td>
                            <td class="px-4 py-3">
                                <span class="rounded bg-slate-100 px-2 py-1 text-xs font-semibold text-slate-700">
                                    {{ \Illuminate\Support\Str::headline($activity->event ?? $properties->get('action') ?? $activity->description) }}
                                </span>
                                <div class="mt-1 text-xs text-slate-500">{{ $activity->description }}</div>
                            </td>
                            <td class="px-4 py-3">
                                <div class="font-semibold text-slate-900">{{ \Illuminate\Support\Str::headline($properties->get('module') ?? $activity->log_name ?? '-') }}</div>
                                <div class="text-xs text-slate-500">{{ $recordName }}</div>
                            </td>
                            <td class="px-4 py-3 text-slate-700">
                                <div>{{ $properties->get('ip_address') ?? '-' }}</div>
                                <div class="text-xs text-slate-500">{{ $properties->get('device') ?? '-' }}</div>
                            </td>
                            <td class="px-4 py-3">
                                <pre class="max-w-xs whitespace-pre-wrap rounded bg-slate-50 p-2 text-xs text-slate-600">{{ $formatPayload($properties->get('old', [])) }}</pre>
                            </td>
                            <td class="px-4 py-3">
                                <pre class="max-w-xs whitespace-pre-wrap rounded bg-slate-50 p-2 text-xs text-slate-600">{{ $formatPayload($properties->get('attributes', [])) }}</pre>
                            </td>
                            @role('Super Admin')
                                <td class="px-4 py-3 text-right">
                                    <form method="POST" action="{{ route('activity-logs.destroy', $activity) }}" onsubmit="return confirm('Delete this activity log?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="text-sm font-semibold text-red-600">Delete</button>
                                    </form>
                                </td>
                            @endrole
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-10 text-center text-slate-500">No activity logs found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="border-t border-slate-200 px-4 py-3">
            {{ $activities->links() }}
        </div>
    </div>
</div>
@endsection
