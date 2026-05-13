<?php

use App\Services\BackupManager;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('erp:backup-database {--notify : Send configured backup notifications}', function () {
    app(BackupManager::class)->runDatabaseBackup((bool) $this->option('notify'));
    $this->info('LUCKY TRADERS database backup completed.');

    return 0;
})->purpose('Create a LUCKY TRADERS database backup');

Artisan::command('erp:backup-full {--notify : Send configured backup notifications}', function () {
    app(BackupManager::class)->runFullBackup((bool) $this->option('notify'));
    $this->info('LUCKY TRADERS full backup completed.');

    return 0;
})->purpose('Create a LUCKY TRADERS full files and database backup');

Artisan::command('erp:backup-clean {--notify : Send configured backup notifications}', function () {
    app(BackupManager::class)->cleanup((bool) $this->option('notify'));
    $this->info('LUCKY TRADERS backup cleanup completed.');

    return 0;
})->purpose('Clean old LUCKY TRADERS backups using configured retention rules');

Schedule::command('erp:backup-database')->dailyAt('01:00')->withoutOverlapping();
Schedule::command('erp:backup-full')->weeklyOn(0, '02:00')->withoutOverlapping();
Schedule::command('erp:backup-clean')->monthlyOn(1, '03:00')->withoutOverlapping();
