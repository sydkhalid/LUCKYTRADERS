@extends('layouts.app')

@section('title', 'Testing Checklist')

@section('content')
    @php
        $statusClasses = [
            'pending' => 'bg-slate-100 text-slate-700 border-slate-200',
            'pass' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
            'fail' => 'bg-rose-50 text-rose-700 border-rose-200',
        ];
        $bugStatusClasses = [
            'open' => 'bg-rose-50 text-rose-700 border-rose-200',
            'in_progress' => 'bg-amber-50 text-amber-700 border-amber-200',
            'resolved' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
            'closed' => 'bg-slate-100 text-slate-700 border-slate-200',
        ];
        $severityClasses = [
            'low' => 'bg-slate-100 text-slate-700 border-slate-200',
            'medium' => 'bg-blue-50 text-blue-700 border-blue-200',
            'high' => 'bg-amber-50 text-amber-700 border-amber-200',
            'critical' => 'bg-rose-50 text-rose-700 border-rose-200',
        ];
    @endphp

    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <div>
            <p class="text-xs font-black uppercase tracking-[0.22em] text-cyan-700">Go-Live Verification</p>
            <h2 class="mt-1 text-2xl font-black tracking-tight text-slate-950">Production Testing Checklist</h2>
            <p class="mt-1 text-sm text-slate-500">Use this page before live billing to verify each ERP module and track unresolved bugs.</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('settings.company') }}" class="rounded-xl border border-slate-300 bg-white px-4 py-2 text-sm font-bold text-slate-700 hover:bg-slate-50">Company Settings</a>
            <a href="{{ route('settings.invoice') }}" class="rounded-xl border border-slate-300 bg-white px-4 py-2 text-sm font-bold text-slate-700 hover:bg-slate-50">Invoice Settings</a>
            @role('Super Admin')
                <a href="{{ route('settings.backups.index') }}" class="rounded-xl border border-slate-300 bg-white px-4 py-2 text-sm font-bold text-slate-700 hover:bg-slate-50">Backups</a>
            @endrole
        </div>
    </div>

    <div class="mb-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-5">
        <div class="erp-summary-card">
            <p class="text-xs font-black uppercase tracking-wide text-slate-500">Progress</p>
            <h3 class="mt-2 text-3xl font-black text-slate-950">{{ $summary['progress'] }}%</h3>
            <div class="mt-4 h-2 rounded-full bg-slate-100">
                <div class="h-2 rounded-full bg-emerald-500" style="width: {{ $summary['progress'] }}%"></div>
            </div>
        </div>
        <div class="erp-summary-card">
            <p class="text-xs font-black uppercase tracking-wide text-slate-500">Total Tests</p>
            <h3 class="mt-2 text-3xl font-black text-slate-950">{{ $summary['total'] }}</h3>
        </div>
        <div class="erp-summary-card">
            <p class="text-xs font-black uppercase tracking-wide text-slate-500">Passed</p>
            <h3 class="mt-2 text-3xl font-black text-emerald-700">{{ $summary['passed'] }}</h3>
        </div>
        <div class="erp-summary-card">
            <p class="text-xs font-black uppercase tracking-wide text-slate-500">Failed</p>
            <h3 class="mt-2 text-3xl font-black text-rose-700">{{ $summary['failed'] }}</h3>
        </div>
        <div class="erp-summary-card">
            <p class="text-xs font-black uppercase tracking-wide text-slate-500">Open Bugs</p>
            <h3 class="mt-2 text-3xl font-black text-amber-700">{{ $summary['open_bugs'] }}</h3>
        </div>
    </div>

    <div class="grid gap-6 2xl:grid-cols-[1fr_420px]">
        <div class="space-y-6">
            <form method="POST" action="{{ route('settings.testing-checklist.update') }}" class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                @csrf
                @method('PATCH')

                <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-200 px-5 py-4">
                    <div>
                        <h3 class="font-black text-slate-950">Module Verification</h3>
                        <p class="mt-1 text-sm text-slate-500">Mark each workflow as pass or fail after manual verification.</p>
                    </div>
                    <button class="rounded-xl bg-slate-950 px-4 py-2 text-sm font-black text-white hover:bg-slate-800">Save Checklist</button>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-sm">
                        <thead>
                            <tr>
                                <th class="w-44">Status</th>
                                <th>Module</th>
                                <th>Manual Test</th>
                                <th>Expected Result</th>
                                <th>Automated Coverage</th>
                                <th class="min-w-72">Notes / Comments</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach ($testingItems as $item)
                                <tr>
                                    <td class="px-4 py-4 align-top">
                                        <select name="items[{{ $item->id }}][status]" class="w-full text-sm font-bold">
                                            @foreach ($statuses as $value => $label)
                                                <option value="{{ $value }}" @selected($item->status === $value)>{{ $label }}</option>
                                            @endforeach
                                        </select>
                                        <div class="mt-2 inline-flex rounded-full border px-2 py-1 text-xs font-black {{ $statusClasses[$item->status] ?? $statusClasses['pending'] }}">
                                            {{ $statuses[$item->status] ?? 'Pending' }}
                                        </div>
                                        @if ($item->tester || $item->tested_at)
                                            <p class="mt-2 text-xs text-slate-500">
                                                {{ $item->tester?->name ?: 'Tester' }}
                                                @if ($item->tested_at)
                                                    <br>{{ $item->tested_at->format('d M Y, h:i A') }}
                                                @endif
                                            </p>
                                        @endif
                                    </td>
                                    <td class="px-4 py-4 align-top">
                                        <p class="font-black text-slate-950">{{ $item->module }}</p>
                                        @if ($item->bugs->isNotEmpty())
                                            <p class="mt-2 text-xs font-bold text-rose-700">{{ $item->bugs->count() }} open bug(s)</p>
                                        @endif
                                    </td>
                                    <td class="px-4 py-4 align-top text-slate-700">{{ $item->scenario }}</td>
                                    <td class="px-4 py-4 align-top text-slate-700">{{ $item->expected_result }}</td>
                                    <td class="px-4 py-4 align-top font-mono text-xs text-slate-600">{{ $item->automated_test ?: '-' }}</td>
                                    <td class="px-4 py-4 align-top">
                                        <textarea name="items[{{ $item->id }}][notes]" rows="3" class="w-full min-w-72" placeholder="Testing notes, comments, or blocker details">{{ old("items.{$item->id}.notes", $item->notes) }}</textarea>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="flex justify-end border-t border-slate-200 bg-slate-50 px-5 py-4">
                    <button class="rounded-xl bg-slate-950 px-5 py-2 text-sm font-black text-white hover:bg-slate-800">Save Checklist</button>
                </div>
            </form>

            <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-200 px-5 py-4">
                    <h3 class="font-black text-slate-950">Bug Tracking Section</h3>
                    <p class="mt-1 text-sm text-slate-500">Record issues found during ERP testing and update their resolution status.</p>
                </div>

                <form method="POST" action="{{ route('settings.testing-bugs.store') }}" class="border-b border-slate-200 bg-slate-50 p-5">
                    @csrf
                    <div class="grid gap-4 lg:grid-cols-4">
                        <div>
                            <label class="block text-sm font-bold text-slate-700">Linked Test</label>
                            <select name="testing_checklist_id" class="mt-1 w-full">
                                <option value="">No specific test</option>
                                @foreach ($testingItems as $item)
                                    <option value="{{ $item->id }}" @selected(old('testing_checklist_id') == $item->id)>{{ $item->module }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-slate-700">Module</label>
                            <select name="module" class="mt-1 w-full" required>
                                @foreach ($testingItems as $item)
                                    <option value="{{ $item->module }}" @selected(old('module') === $item->module)>{{ $item->module }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-slate-700">Severity</label>
                            <select name="severity" class="mt-1 w-full" required>
                                @foreach ($severities as $value => $label)
                                    <option value="{{ $value }}" @selected(old('severity', 'medium') === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-slate-700">Bug Title</label>
                            <input name="title" value="{{ old('title') }}" class="mt-1 w-full" required placeholder="Short bug summary">
                        </div>
                        <div class="lg:col-span-4">
                            <label class="block text-sm font-bold text-slate-700">Description</label>
                            <textarea name="description" rows="3" class="mt-1 w-full" placeholder="Steps to reproduce, expected result, actual result">{{ old('description') }}</textarea>
                        </div>
                    </div>
                    <div class="mt-4 flex justify-end">
                        <button class="rounded-xl bg-slate-950 px-5 py-2 text-sm font-black text-white hover:bg-slate-800">Add Bug</button>
                    </div>
                </form>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-sm">
                        <thead>
                            <tr>
                                <th>Bug</th>
                                <th>Module</th>
                                <th>Severity</th>
                                <th>Status</th>
                                <th>Reported</th>
                                <th class="min-w-72">Resolution Notes</th>
                                <th class="text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse ($bugs as $bug)
                                <tr>
                                    <td class="px-4 py-4 align-top">
                                        <p class="font-black text-slate-950">{{ $bug->title }}</p>
                                        @if ($bug->description)
                                            <p class="mt-1 max-w-lg text-xs text-slate-500">{{ $bug->description }}</p>
                                        @endif
                                        @if ($bug->checklist)
                                            <p class="mt-2 text-xs font-bold text-cyan-700">Linked: {{ $bug->checklist->module }}</p>
                                        @endif
                                    </td>
                                    <td class="px-4 py-4 align-top text-slate-700">{{ $bug->module }}</td>
                                    <td class="px-4 py-4 align-top">
                                        <span class="inline-flex rounded-full border px-2 py-1 text-xs font-black {{ $severityClasses[$bug->severity] ?? $severityClasses['medium'] }}">
                                            {{ $severities[$bug->severity] ?? ucfirst($bug->severity) }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-4 align-top">
                                        <span class="inline-flex rounded-full border px-2 py-1 text-xs font-black {{ $bugStatusClasses[$bug->status] ?? $bugStatusClasses['open'] }}">
                                            {{ $bugStatuses[$bug->status] ?? ucfirst($bug->status) }}
                                        </span>
                                        @if ($bug->resolved_at)
                                            <p class="mt-2 text-xs text-slate-500">Resolved {{ $bug->resolved_at->format('d M Y') }}</p>
                                        @endif
                                    </td>
                                    <td class="px-4 py-4 align-top text-xs text-slate-500">
                                        {{ $bug->reporter?->name ?: 'Admin' }}<br>
                                        {{ $bug->created_at->format('d M Y, h:i A') }}
                                    </td>
                                    <td class="px-4 py-4 align-top" colspan="2">
                                        <form method="POST" action="{{ route('settings.testing-bugs.update', $bug) }}" class="grid min-w-[420px] gap-3 lg:grid-cols-[140px_140px_1fr_auto]">
                                            @csrf
                                            @method('PATCH')
                                            <select name="severity" class="w-full text-sm">
                                                @foreach ($severities as $value => $label)
                                                    <option value="{{ $value }}" @selected($bug->severity === $value)>{{ $label }}</option>
                                                @endforeach
                                            </select>
                                            <select name="status" class="w-full text-sm">
                                                @foreach ($bugStatuses as $value => $label)
                                                    <option value="{{ $value }}" @selected($bug->status === $value)>{{ $label }}</option>
                                                @endforeach
                                            </select>
                                            <textarea name="resolution_notes" rows="2" class="w-full" placeholder="Resolution or follow-up notes">{{ $bug->resolution_notes }}</textarea>
                                            <button class="rounded-xl border border-slate-300 bg-white px-4 py-2 text-sm font-black text-slate-700 hover:bg-slate-50">Update</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7">No bugs tracked yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="border-t border-slate-200 px-5 py-4">
                    {{ $bugs->withQueryString()->links() }}
                </div>
            </div>
        </div>

        <aside class="space-y-6">
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <h3 class="font-black text-slate-950">Security Readiness</h3>
                <div class="mt-4 space-y-3">
                    @foreach ($securityItems as $item)
                        <label class="flex gap-3 text-sm text-slate-700">
                            <input type="checkbox" class="mt-0.5 h-4 w-4 rounded border-slate-300 text-slate-900 focus:ring-slate-700">
                            <span>{{ $item }}</span>
                        </label>
                    @endforeach
                </div>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <h3 class="font-black text-slate-950">Production Commands</h3>
                <div class="mt-4 space-y-2">
                    @foreach ($deploymentCommands as $command)
                        <div class="rounded-xl bg-slate-950 px-3 py-2 font-mono text-xs text-slate-50">{{ $command }}</div>
                    @endforeach
                </div>
            </div>

            <div class="rounded-2xl border border-amber-200 bg-amber-50 p-5 text-sm text-amber-900">
                Set <span class="font-mono font-bold">APP_ENV=production</span>, <span class="font-mono font-bold">APP_DEBUG=false</span>, and production database credentials before running cache commands.
            </div>
        </aside>
    </div>
@endsection
