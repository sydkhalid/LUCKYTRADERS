@php
    $active = $active ?? 'company';
    $kicker = $kicker ?? 'System Settings';
    $title = $title ?? 'Settings';
    $description = $description ?? 'Manage Lucky Traders configuration.';
    $icon = $icon ?? 'settings';

    $icons = [
        'company' => '<path d="M3 21h18"/><path d="M5 21V7l8-4v18"/><path d="M19 21V11l-6-4"/><path d="M9 9h1"/><path d="M9 13h1"/><path d="M9 17h1"/>',
        'invoice' => '<path d="M14 2H6a2 2 0 0 0-2 2v16l4-2 4 2 4-2 4 2V8z"/><path d="M14 2v6h6"/><path d="M8 13h8"/><path d="M8 17h5"/>',
        'bank' => '<path d="M3 10h18"/><path d="M5 10V8l7-5 7 5v2"/><path d="M6 10v8"/><path d="M10 10v8"/><path d="M14 10v8"/><path d="M18 10v8"/><path d="M4 18h16"/><path d="M3 22h18"/>',
        'terms' => '<path d="M16 3h5v5"/><path d="M4 20 21 3"/><path d="M21 16v5h-5"/><path d="M15 15l6 6"/><path d="M4 4l5 5"/>',
        'media' => '<rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="9" cy="9" r="2"/><path d="m21 15-3.1-3.1a2 2 0 0 0-2.8 0L6 21"/>',
        'testing' => '<path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/>',
        'backup' => '<path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><path d="M7 10l5 5 5-5"/><path d="M12 15V3"/>',
        'settings' => '<path d="M12 15.5A3.5 3.5 0 1 0 12 8a3.5 3.5 0 0 0 0 7.5z"/><path d="M19.4 15a1.8 1.8 0 0 0 .4 2l.1.1a2 2 0 1 1-2.8 2.8l-.1-.1a1.8 1.8 0 0 0-2-.4 1.8 1.8 0 0 0-1 1.6V21a2 2 0 1 1-4 0v-.1a1.8 1.8 0 0 0-1-1.6 1.8 1.8 0 0 0-2 .4l-.1.1a2 2 0 1 1-2.8-2.8l.1-.1a1.8 1.8 0 0 0 .4-2 1.8 1.8 0 0 0-1.6-1H3a2 2 0 1 1 0-4h.1a1.8 1.8 0 0 0 1.6-1 1.8 1.8 0 0 0-.4-2l-.1-.1A2 2 0 1 1 7 4.1l.1.1a1.8 1.8 0 0 0 2 .4 1.8 1.8 0 0 0 1-1.6V3a2 2 0 1 1 4 0v.1a1.8 1.8 0 0 0 1 1.6 1.8 1.8 0 0 0 2-.4l.1-.1A2 2 0 1 1 20 7l-.1.1a1.8 1.8 0 0 0-.4 2 1.8 1.8 0 0 0 1.6 1H21a2 2 0 1 1 0 4h-.1a1.8 1.8 0 0 0-1.5 1z"/>',
    ];

    $links = [
        ['key' => 'company', 'label' => 'Company', 'route' => 'settings.company', 'icon' => 'company'],
        ['key' => 'invoice', 'label' => 'Invoice', 'route' => 'settings.invoice', 'icon' => 'invoice'],
        ['key' => 'bank', 'label' => 'Bank', 'route' => 'settings.bank', 'icon' => 'bank'],
        ['key' => 'terms', 'label' => 'Terms', 'route' => 'settings.terms', 'icon' => 'terms'],
        ['key' => 'media', 'label' => 'Media', 'route' => 'settings.media', 'icon' => 'media'],
        ['key' => 'testing', 'label' => 'Testing', 'route' => 'settings.testing-checklist', 'icon' => 'testing'],
    ];
@endphp

<div class="settings-hero card mb-4">
    <div class="card-body">
        <div class="d-flex flex-column flex-xl-row align-items-xl-center justify-content-between gap-4">
            <div class="d-flex align-items-start gap-3">
                <span class="settings-hero-icon" aria-hidden="true">
                    <svg class="erp-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        {!! $icons[$icon] ?? $icons['settings'] !!}
                    </svg>
                </span>
                <div>
                    <p class="settings-kicker mb-1">{{ $kicker }}</p>
                    <h1 class="settings-title mb-2">{{ $title }}</h1>
                    <p class="settings-copy mb-0">{{ $description }}</p>
                </div>
            </div>
            <div class="settings-hero-status">
                <span class="settings-status-dot"></span>
                Dynamic settings connected
            </div>
        </div>

        <nav class="settings-nav mt-4" aria-label="Settings navigation">
            @foreach ($links as $link)
                <a href="{{ route($link['route']) }}" class="settings-nav-link {{ $active === $link['key'] ? 'active' : '' }}">
                    <svg class="erp-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        {!! $icons[$link['icon']] !!}
                    </svg>
                    <span>{{ $link['label'] }}</span>
                </a>
            @endforeach
            @role('Super Admin')
                <a href="{{ route('settings.backups.index') }}" class="settings-nav-link {{ in_array($active, ['backups', 'backup-settings'], true) ? 'active' : '' }}">
                    <svg class="erp-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        {!! $icons['backup'] !!}
                    </svg>
                    <span>Backups</span>
                </a>
            @endrole
        </nav>
    </div>
</div>
