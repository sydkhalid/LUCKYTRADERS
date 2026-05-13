@extends('layouts.erp')

@section('title', 'Backups')

@section('content')
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <div>
            <h2 class="text-lg font-semibold text-gray-900">Database Backups</h2>
            <p class="text-sm text-gray-500">Backup access is restricted to Super Admin users.</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('settings.company') }}" class="rounded border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">Company Settings</a>
            <a href="{{ route('settings.invoice') }}" class="rounded border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">Invoice Settings</a>
            <a href="{{ route('settings.testing-checklist') }}" class="rounded border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">Testing Checklist</a>
            <form method="POST" action="{{ route('settings.backups.store') }}">
                @csrf
                <button class="rounded bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-800">Create Backup</button>
            </form>
        </div>
    </div>

    <div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                <tr>
                    <th class="px-4 py-3">File</th>
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
                        <td class="px-4 py-3 text-gray-700">{{ $backup['size_label'] }}</td>
                        <td class="px-4 py-3 text-gray-700">{{ $backup['created_at']?->format('d M Y h:i A') ?: '-' }}</td>
                        <td class="px-4 py-3">
                            <div class="flex justify-end gap-2">
                                <a href="{{ route('settings.backups.download', $backup['encoded']) }}" class="rounded border border-gray-300 px-3 py-1.5 text-xs font-semibold text-gray-700 hover:bg-gray-50">Download</a>
                                <form method="POST" action="{{ route('settings.backups.destroy', $backup['encoded']) }}" onsubmit="return confirm('Delete this backup file?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="rounded bg-red-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-red-700">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-4 py-8 text-center text-gray-500">No backup files found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
