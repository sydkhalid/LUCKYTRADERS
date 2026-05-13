@extends('layouts.app')

@section('title', 'Backup System')

@section('content')
    @include('settings.partials.header', [
        'active' => 'backups',
        'kicker' => 'Recovery Center',
        'title' => 'Backup System',
        'description' => 'Create, download, and remove database or full application backups from private storage.',
        'icon' => 'backup',
    ])

    <div class="settings-toolbar card mb-4">
        <div class="card-body d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
            <div>
                <h5 class="mb-1">Backup Actions</h5>
                <p class="mb-0 text-muted">Manual backup tasks use the existing confirmation and backup service flow.</p>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <a href="{{ route('settings.backups.settings') }}" class="btn btn-outline-secondary">
                    <svg class="erp-icon me-1" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 15.5A3.5 3.5 0 1 0 12 8a3.5 3.5 0 0 0 0 7.5z"/><path d="M19.4 15a1.8 1.8 0 0 0 .4 2"/></svg>
                    Backup Settings
                </a>
                <form method="POST" action="{{ route('settings.backups.store') }}" data-confirm-action data-confirm-title="Create database backup?" data-confirm-text="The backup task may take a few moments to complete." data-confirm-button="Create backup">
                    @csrf
                    <input type="hidden" name="backup_type" value="database">
                    <button class="btn btn-primary">Create Database Backup</button>
                </form>
                <form method="POST" action="{{ route('settings.backups.store') }}" data-confirm-action data-confirm-title="Create full backup?" data-confirm-text="This includes files and database and may take longer than a database-only backup." data-confirm-button="Create full backup">
                    @csrf
                    <input type="hidden" name="backup_type" value="full">
                    <button class="btn btn-success">Create Full Backup</button>
                </form>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="settings-mini-card h-100">
                <span>Last Backup Status</span>
                <strong class="{{ ($status['status'] ?? '') === 'failed' ? 'text-danger' : '' }}">{{ ucfirst(str_replace('_', ' ', $status['status'] ?? 'unknown')) }}</strong>
                <small>{{ $status['message'] ?? '-' }}</small>
            </div>
        </div>
        <div class="col-md-4">
            <div class="settings-mini-card h-100">
                <span>Last Run</span>
                <strong>{{ ! empty($status['ran_at']) ? \Carbon\Carbon::parse($status['ran_at'])->format('d M Y h:i A') : '-' }}</strong>
                <small>Type: {{ ucfirst($status['type'] ?? '-') }}</small>
            </div>
        </div>
        <div class="col-md-4">
            <div class="settings-mini-card h-100">
                <span>Backup Storage</span>
                <strong>{{ $settings['storage'] }}</strong>
                <small>Disk: {{ implode(', ', $settings['disks']) }}</small>
            </div>
        </div>
    </div>

    <div class="card settings-card">
        <div class="card-header d-flex align-items-center gap-3">
            <span class="settings-section-icon bg-label-primary">
                <svg class="erp-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><path d="M7 10l5 5 5-5"/><path d="M12 15V3"/></svg>
            </span>
            <div>
                <h5 class="mb-0">Backup Files</h5>
                <p class="mb-0 text-muted small">Private local backup inventory.</p>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table settings-table mb-0">
                <thead>
                    <tr>
                        <th>File</th>
                        <th>Type</th>
                        <th>Size</th>
                        <th>Created</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($backups as $backup)
                        <tr>
                            <td>
                                <p class="fw-semibold mb-1 text-heading">{{ $backup['name'] }}</p>
                                <p class="mb-0 small text-muted">{{ $backup['path'] }}</p>
                            </td>
                            <td>{{ $backup['type'] }}</td>
                            <td>{{ $backup['size_label'] }}</td>
                            <td>{{ $backup['created_at']?->format('d M Y h:i A') ?: '-' }}</td>
                            <td>
                                <div class="d-flex justify-content-end gap-2">
                                    <a href="{{ route('settings.backups.download', $backup['encoded']) }}" class="btn btn-sm btn-outline-secondary">Download</a>
                                    <form method="POST" action="{{ route('settings.backups.destroy', $backup['encoded']) }}" data-confirm-delete data-confirm-title="Delete this backup file?" data-confirm-text="This backup file will be permanently removed from private storage.">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-5">No backup files found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
