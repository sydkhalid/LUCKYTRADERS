@extends('layouts.erp')

@section('title', 'Backup System')

@section('content')
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <div>
            <h2 class="text-lg font-semibold text-gray-900">Backup System</h2>
            <p class="text-sm text-gray-500">Manual and scheduled backups are stored on the private local disk.</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('settings.backups.settings') }}" class="rounded border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">Backup Settings</a>
            <a href="{{ route('settings.company') }}" class="rounded border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">Company Settings</a>
            <form
                method="POST"
                action="{{ route('settings.backups.store') }}"
                data-confirm-action
                data-confirm-title="Create database backup?"
                data-confirm-text="The backup task may take a few moments to complete."
                data-confirm-button="Create backup"
            >
                @csrf
                <input type="hidden" name="backup_type" value="database">
                <button class="rounded bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-800">Create Database Backup</button>
            </form>
            <form
                method="POST"
                action="{{ route('settings.backups.store') }}"
                data-confirm-action
                data-confirm-title="Create full backup?"
                data-confirm-text="This includes files and database and may take longer than a database-only backup."
                data-confirm-button="Create full backup"
            >
                @csrf
                <input type="hidden" name="backup_type" value="full">
                <button class="rounded bg-emerald-700 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-800">Create Full Backup</button>
            </form>
        </div>
    </div>

    <div class="mb-5 grid gap-4 md:grid-cols-3">
        <div class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
            <p class="text-sm text-gray-500">Last Backup Status</p>
            <h3 class="mt-1 text-lg font-bold {{ ($status['status'] ?? '') === 'failed' ? 'text-red-700' : 'text-gray-900' }}">
                {{ ucfirst(str_replace('_', ' ', $status['status'] ?? 'unknown')) }}
            </h3>
            <p class="mt-2 text-sm text-gray-600">{{ $status['message'] ?? '-' }}</p>
        </div>
        <div class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
            <p class="text-sm text-gray-500">Last Run</p>
            <h3 class="mt-1 text-lg font-bold text-gray-900">{{ ! empty($status['ran_at']) ? \Carbon\Carbon::parse($status['ran_at'])->format('d M Y h:i A') : '-' }}</h3>
            <p class="mt-2 text-sm text-gray-600">Type: {{ ucfirst($status['type'] ?? '-') }}</p>
        </div>
        <div class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
            <p class="text-sm text-gray-500">Backup Storage</p>
            <h3 class="mt-1 text-lg font-bold text-gray-900">{{ $settings['storage'] }}</h3>
            <p class="mt-2 text-sm text-gray-600">Disk: {{ implode(', ', $settings['disks']) }}</p>
        </div>
    </div>

    <div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                <tr>
                    <th class="px-4 py-3">File</th>
                    <th class="px-4 py-3">Type</th>
                    <th class="px-4 py-3">Size</th>
                    <th class="px-4 py-3">Created</th>
                    <th class="px-4 py-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($backups as $backup)
                    <tr>
                        <td class="px-4 py-3">
                            <p class="font-medium text-gray-900">{{ $backup['name'] }}</p>
                            <p class="mt-1 text-xs text-gray-500">{{ $backup['path'] }}</p>
                        </td>
                        <td class="px-4 py-3 text-gray-700">{{ $backup['type'] }}</td>
                        <td class="px-4 py-3 text-gray-700">{{ $backup['size_label'] }}</td>
                        <td class="px-4 py-3 text-gray-700">{{ $backup['created_at']?->format('d M Y h:i A') ?: '-' }}</td>
                        <td class="px-4 py-3">
                            <div class="flex justify-end gap-2">
                                <a href="{{ route('settings.backups.download', $backup['encoded']) }}" class="rounded border border-gray-300 px-3 py-1.5 text-xs font-semibold text-gray-700 hover:bg-gray-50">Download</a>
                                <form
                                    method="POST"
                                    action="{{ route('settings.backups.destroy', $backup['encoded']) }}"
                                    data-confirm-delete
                                    data-confirm-title="Delete this backup file?"
                                    data-confirm-text="This backup file will be permanently removed from private storage."
                                >
                                    @csrf
                                    @method('DELETE')
                                    <button class="rounded bg-red-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-red-700">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-8 text-center text-gray-500">No backup files found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
