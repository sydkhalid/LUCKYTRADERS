@extends('layouts.erp')

@section('title', 'Testing Checklist')

@section('content')
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <div>
            <h2 class="text-lg font-semibold text-gray-900">Production Testing Checklist</h2>
            <p class="text-sm text-gray-500">Use this page before live billing to verify each ERP module and deployment step.</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('settings.company') }}" class="rounded border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">Company Settings</a>
            <a href="{{ route('settings.invoice') }}" class="rounded border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">Invoice Settings</a>
            @role('Super Admin')
                <a href="{{ route('settings.backups.index') }}" class="rounded border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">Backups</a>
            @endrole
        </div>
    </div>

    <div class="grid gap-6 xl:grid-cols-[1fr_360px]">
        <div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
            <div class="border-b border-gray-200 px-5 py-4">
                <h3 class="font-semibold text-gray-900">Module Verification</h3>
            </div>
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                    <tr>
                        <th class="px-4 py-3">Done</th>
                        <th class="px-4 py-3">Module</th>
                        <th class="px-4 py-3">Verify</th>
                        <th class="px-4 py-3">Automated Coverage</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach ($testingItems as $item)
                        <tr>
                            <td class="px-4 py-3 align-top">
                                <input type="checkbox" class="h-4 w-4 rounded border-gray-300 text-slate-900 focus:ring-slate-700">
                            </td>
                            <td class="px-4 py-3 align-top font-medium text-gray-900">{{ $item['module'] }}</td>
                            <td class="px-4 py-3 align-top text-gray-700">{{ $item['coverage'] }}</td>
                            <td class="px-4 py-3 align-top text-gray-600">{{ $item['test'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="space-y-6">
            <div class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                <h3 class="font-semibold text-gray-900">Security Readiness</h3>
                <div class="mt-4 space-y-3">
                    @foreach ($securityItems as $item)
                        <label class="flex gap-3 text-sm text-gray-700">
                            <input type="checkbox" class="mt-0.5 h-4 w-4 rounded border-gray-300 text-slate-900 focus:ring-slate-700">
                            <span>{{ $item }}</span>
                        </label>
                    @endforeach
                </div>
            </div>

            <div class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                <h3 class="font-semibold text-gray-900">Production Commands</h3>
                <div class="mt-4 space-y-2">
                    @foreach ($deploymentCommands as $command)
                        <div class="rounded bg-slate-950 px-3 py-2 font-mono text-xs text-slate-50">{{ $command }}</div>
                    @endforeach
                </div>
            </div>

            <div class="rounded-lg border border-amber-200 bg-amber-50 p-5 text-sm text-amber-900">
                Set <span class="font-mono font-semibold">APP_ENV=production</span>, <span class="font-mono font-semibold">APP_DEBUG=false</span>, and production database credentials before running cache commands.
            </div>
        </div>
    </div>
@endsection
