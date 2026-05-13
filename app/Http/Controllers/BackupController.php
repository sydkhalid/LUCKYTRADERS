<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use RuntimeException;
use Throwable;

class BackupController extends Controller
{
    public function index(): View
    {
        return view('settings.backups', [
            'backups' => $this->backupFiles(),
        ]);
    }

    public function store(): RedirectResponse
    {
        try {
            $exitCode = Artisan::call('backup:run', [
                '--only-db' => true,
                '--disable-notifications' => true,
            ]);

            if ($exitCode !== 0) {
                throw new RuntimeException(trim(Artisan::output()) ?: 'The backup command did not complete successfully.');
            }

            return back()->with('success', 'Database backup created successfully.');
        } catch (Throwable $exception) {
            return back()->with('error', 'Backup failed: '.$exception->getMessage());
        }
    }

    public function download(string $file)
    {
        $path = $this->decodePath($file);
        abort_unless($this->isKnownBackup($path), 404);

        return Storage::disk('local')->download($path);
    }

    public function destroy(string $file): RedirectResponse
    {
        $path = $this->decodePath($file);
        abort_unless($this->isKnownBackup($path), 404);

        Storage::disk('local')->delete($path);

        return back()->with('success', 'Backup deleted successfully.');
    }

    /**
     * @return list<array{name:string,path:string,encoded:string,size:int,size_label:string,created_at:\Carbon\CarbonInterface|null}>
     */
    private function backupFiles(): array
    {
        return collect(Storage::disk('local')->allFiles())
            ->filter(fn (string $path): bool => Str::endsWith(strtolower($path), '.zip'))
            ->map(function (string $path): array {
                $lastModified = Storage::disk('local')->lastModified($path);
                $size = Storage::disk('local')->size($path);

                return [
                    'name' => basename($path),
                    'path' => $path,
                    'encoded' => $this->encodePath($path),
                    'size' => $size,
                    'size_label' => $this->formatBytes($size),
                    'created_at' => $lastModified ? now()->setTimestamp($lastModified) : null,
                ];
            })
            ->sortByDesc('created_at')
            ->values()
            ->all();
    }

    private function isKnownBackup(?string $path): bool
    {
        if (! $path || str_contains($path, '..') || ! Str::endsWith(strtolower($path), '.zip')) {
            return false;
        }

        return Storage::disk('local')->exists($path);
    }

    private function decodePath(string $encoded): ?string
    {
        $encoded = strtr($encoded, '-_', '+/');
        $encoded .= str_repeat('=', (4 - strlen($encoded) % 4) % 4);
        $decoded = base64_decode($encoded, true);

        return is_string($decoded) ? $decoded : null;
    }

    private function encodePath(string $path): string
    {
        return rtrim(strtr(base64_encode($path), '+/', '-_'), '=');
    }

    private function formatBytes(int $bytes): string
    {
        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2).' MB';
        }

        if ($bytes >= 1024) {
            return number_format($bytes / 1024, 2).' KB';
        }

        return $bytes.' B';
    }
}
