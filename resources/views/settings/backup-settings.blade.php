@extends('layouts.erp')

@section('title', 'Backup Settings')

@section('content')
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <div>
            <h2 class="text-lg font-semibold text-gray-900">Backup Settings</h2>
            <p class="text-sm text-gray-500">Scheduler, source, retention, and cleanup configuration.</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('settings.backups.index') }}" class="rounded border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">Backup List</a>
            <form method="POST" action="{{ route('settings.backups.cleanup') }}" onsubmit="return confirm('Run backup cleanup now?')">
                @csrf
                <button class="rounded bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-800">Run Cleanup</button>
            </form>
        </div>
    </div>

    <div class="grid gap-5 lg:grid-cols-2">
        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
            <h3 class="text-sm font-semibold uppercase tracking-wide text-gray-500">Config Setup</h3>
            <dl class="mt-4 space-y-3 text-sm">
                <div class="flex justify-between gap-4"><dt class="text-gray-500">Backup name</dt><dd class="font-semibold text-gray-900">{{ $settings['name'] }}</dd></div>
                <div class="flex justify-between gap-4"><dt class="text-gray-500">Private storage</dt><dd class="font-semibold text-gray-900">{{ $settings['storage'] }}</dd></div>
                <div class="flex justify-between gap-4"><dt class="text-gray-500">Backup disks</dt><dd class="font-semibold text-gray-900">{{ implode(', ', $settings['disks']) }}</dd></div>
                <div class="flex justify-between gap-4"><dt class="text-gray-500">Include .env</dt><dd class="font-semibold text-gray-900">{{ $settings['include_env'] ? 'Enabled' : 'Disabled' }}</dd></div>
            </dl>
        </div>

        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
            <h3 class="text-sm font-semibold uppercase tracking-wide text-gray-500">Last Backup Status</h3>
            <dl class="mt-4 space-y-3 text-sm">
                <div class="flex justify-between gap-4"><dt class="text-gray-500">Status</dt><dd class="font-semibold text-gray-900">{{ ucfirst(str_replace('_', ' ', $status['status'] ?? 'unknown')) }}</dd></div>
                <div class="flex justify-between gap-4"><dt class="text-gray-500">Type</dt><dd class="font-semibold text-gray-900">{{ ucfirst($status['type'] ?? '-') }}</dd></div>
                <div class="flex justify-between gap-4"><dt class="text-gray-500">Last run</dt><dd class="font-semibold text-gray-900">{{ ! empty($status['ran_at']) ? \Carbon\Carbon::parse($status['ran_at'])->format('d M Y h:i A') : '-' }}</dd></div>
            </dl>
            <p class="mt-4 text-sm text-gray-600">{{ $status['message'] ?? '-' }}</p>
        </div>

        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
            <h3 class="text-sm font-semibold uppercase tracking-wide text-gray-500">Scheduled Auto Backup</h3>
            <div class="mt-4 overflow-hidden rounded border border-gray-200">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <tbody class="divide-y divide-gray-100">
                        @foreach ($settings['schedule'] as $label => $command)
                            <tr>
                                <td class="px-4 py-3 font-medium text-gray-900">{{ $label }}</td>
                                <td class="px-4 py-3 font-mono text-xs text-gray-700">{{ $command }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <p class="mt-4 text-sm text-gray-600">Server cron should run <span class="font-mono">php artisan schedule:run</span> every minute.</p>
        </div>

        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
            <h3 class="text-sm font-semibold uppercase tracking-wide text-gray-500">Cleanup Rules</h3>
            <dl class="mt-4 space-y-3 text-sm">
                <div class="flex justify-between gap-4"><dt class="text-gray-500">Keep all backups</dt><dd class="font-semibold text-gray-900">{{ $settings['cleanup']['keep_all_backups_for_days'] ?? '-' }} day</dd></div>
                <div class="flex justify-between gap-4"><dt class="text-gray-500">Daily backups</dt><dd class="font-semibold text-gray-900">{{ $settings['cleanup']['keep_daily_backups_for_days'] ?? '-' }} days</dd></div>
                <div class="flex justify-between gap-4"><dt class="text-gray-500">Weekly backups</dt><dd class="font-semibold text-gray-900">{{ $settings['cleanup']['keep_weekly_backups_for_weeks'] ?? '-' }} weeks</dd></div>
                <div class="flex justify-between gap-4"><dt class="text-gray-500">Monthly backups</dt><dd class="font-semibold text-gray-900">{{ $settings['cleanup']['keep_monthly_backups_for_months'] ?? '-' }} months</dd></div>
                <div class="flex justify-between gap-4"><dt class="text-gray-500">Storage limit</dt><dd class="font-semibold text-gray-900">{{ $settings['cleanup']['delete_oldest_backups_when_using_more_megabytes_than'] ?? '-' }} MB</dd></div>
            </dl>
        </div>
    </div>

    <div class="mt-5 rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
        <h3 class="text-sm font-semibold uppercase tracking-wide text-gray-500">Files Included In Full Backup</h3>
        <ul class="mt-4 space-y-2 text-sm text-gray-700">
            @foreach ($settings['include'] as $path)
                <li class="font-mono">{{ $path }}</li>
            @endforeach
        </ul>
    </div>
@endsection
