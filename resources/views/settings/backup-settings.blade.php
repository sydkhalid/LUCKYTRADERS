@extends('layouts.app')

@section('title', 'Backup Settings')

@section('content')
    @include('settings.partials.header', [
        'active' => 'backup-settings',
        'kicker' => 'Backup Rules',
        'title' => 'Backup Settings',
        'description' => 'Review scheduler, source folders, private storage, retention, and cleanup rules.',
        'icon' => 'backup',
    ])

    <div class="settings-toolbar card mb-4">
        <div class="card-body d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
            <div>
                <h5 class="mb-1">Maintenance Actions</h5>
                <p class="mb-0 text-muted">Configuration is read from the current backup config; cleanup uses the existing controller action.</p>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <a href="{{ route('settings.backups.index') }}" class="btn btn-outline-secondary">Backup List</a>
                <form method="POST" action="{{ route('settings.backups.cleanup') }}" data-confirm-action data-confirm-title="Run backup cleanup now?" data-confirm-text="Old backups matching the retention rules will be deleted." data-confirm-button="Run cleanup">
                    @csrf
                    <button class="btn btn-primary">Run Cleanup</button>
                </form>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-6">
            <div class="card settings-card h-100">
                <div class="card-header">
                    <h5 class="mb-0">Config Setup</h5>
                </div>
                <div class="card-body">
                    <dl class="settings-definition-list">
                        <div><dt>Backup name</dt><dd>{{ $settings['name'] }}</dd></div>
                        <div><dt>Private storage</dt><dd>{{ $settings['storage'] }}</dd></div>
                        <div><dt>Backup disks</dt><dd>{{ implode(', ', $settings['disks']) }}</dd></div>
                        <div><dt>Include .env</dt><dd>{{ $settings['include_env'] ? 'Enabled' : 'Disabled' }}</dd></div>
                    </dl>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card settings-card h-100">
                <div class="card-header">
                    <h5 class="mb-0">Last Backup Status</h5>
                </div>
                <div class="card-body">
                    <dl class="settings-definition-list">
                        <div><dt>Status</dt><dd>{{ ucfirst(str_replace('_', ' ', $status['status'] ?? 'unknown')) }}</dd></div>
                        <div><dt>Type</dt><dd>{{ ucfirst($status['type'] ?? '-') }}</dd></div>
                        <div><dt>Last run</dt><dd>{{ ! empty($status['ran_at']) ? \Carbon\Carbon::parse($status['ran_at'])->format('d M Y h:i A') : '-' }}</dd></div>
                    </dl>
                    <p class="mb-0 text-muted">{{ $status['message'] ?? '-' }}</p>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card settings-card h-100">
                <div class="card-header">
                    <h5 class="mb-0">Scheduled Auto Backup</h5>
                </div>
                <div class="table-responsive">
                    <table class="table settings-table mb-0">
                        <tbody>
                            @foreach ($settings['schedule'] as $label => $command)
                                <tr>
                                    <td class="fw-semibold text-heading">{{ $label }}</td>
                                    <td class="font-monospace small">{{ $command }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="card-footer bg-transparent text-muted small">Server cron should run <span class="font-monospace">php artisan schedule:run</span> every minute.</div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card settings-card h-100">
                <div class="card-header">
                    <h5 class="mb-0">Cleanup Rules</h5>
                </div>
                <div class="card-body">
                    <dl class="settings-definition-list">
                        <div><dt>Keep all backups</dt><dd>{{ $settings['cleanup']['keep_all_backups_for_days'] ?? '-' }} day</dd></div>
                        <div><dt>Daily backups</dt><dd>{{ $settings['cleanup']['keep_daily_backups_for_days'] ?? '-' }} days</dd></div>
                        <div><dt>Weekly backups</dt><dd>{{ $settings['cleanup']['keep_weekly_backups_for_weeks'] ?? '-' }} weeks</dd></div>
                        <div><dt>Monthly backups</dt><dd>{{ $settings['cleanup']['keep_monthly_backups_for_months'] ?? '-' }} months</dd></div>
                        <div><dt>Storage limit</dt><dd>{{ $settings['cleanup']['delete_oldest_backups_when_using_more_megabytes_than'] ?? '-' }} MB</dd></div>
                    </dl>
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="card settings-card">
                <div class="card-header">
                    <h5 class="mb-0">Files Included In Full Backup</h5>
                </div>
                <div class="card-body">
                    <div class="settings-code-grid">
                        @foreach ($settings['include'] as $path)
                            <code>{{ $path }}</code>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
