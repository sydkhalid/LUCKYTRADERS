<?php

namespace App\Http\Controllers;

use App\Services\BackupManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Throwable;

class BackupController extends Controller
{
    public function __construct(private BackupManager $backups)
    {
        //
    }

    public function index(): View
    {
        return view('settings.backups', [
            'backups' => $this->backups->files(),
            'status' => $this->backups->status(),
            'settings' => $this->backups->settings(),
        ]);
    }

    public function settings(): View
    {
        return view('settings.backup-settings', [
            'settings' => $this->backups->settings(),
            'status' => $this->backups->status(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'backup_type' => ['nullable', 'in:database,full'],
        ]);

        $type = $validated['backup_type'] ?? 'database';

        try {
            $type === 'full'
                ? $this->backups->runFullBackup()
                : $this->backups->runDatabaseBackup();

            return back()->with('success', ucfirst($type).' backup created successfully.');
        } catch (Throwable $exception) {
            return back()->with('error', 'Backup failed: '.$exception->getMessage());
        }
    }

    public function cleanup(): RedirectResponse
    {
        try {
            $this->backups->cleanup();

            return back()->with('success', 'Backup cleanup completed successfully.');
        } catch (Throwable $exception) {
            return back()->with('error', 'Backup cleanup failed: '.$exception->getMessage());
        }
    }

    public function download(string $file)
    {
        return $this->backups->download($file);
    }

    public function destroy(string $file): RedirectResponse
    {
        $this->backups->delete($file);

        return back()->with('success', 'Backup deleted successfully.');
    }
}
