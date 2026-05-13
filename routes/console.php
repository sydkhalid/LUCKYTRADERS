<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('erp:backup-database {--notify : Send configured backup notifications}', function () {
    return $this->call('backup:run', [
        '--only-db' => true,
        '--disable-notifications' => ! $this->option('notify'),
    ]);
})->purpose('Create a LUCKY TRADERS database backup');
