<?php

namespace App\Services;

use Carbon\CarbonInterface;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;
use ZipArchive;

class BackupManager
{
    private const STATUS_PATH = 'backups/status.json';

    /**
     * @return list<array{name:string,path:string,encoded:string,size:int,size_label:string,created_at:CarbonInterface|null,type:string}>
     */
    public function files(): array
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
                    'type' => $this->classifyBackup($path),
                ];
            })
            ->sortByDesc('created_at')
            ->values()
            ->all();
    }

    public function status(): array
    {
        if (! Storage::disk('local')->exists(self::STATUS_PATH)) {
            return [
                'status' => 'not_run',
                'message' => 'No backup has been run yet.',
                'type' => null,
                'ran_at' => null,
                'output' => null,
            ];
        }

        $status = json_decode(Storage::disk('local')->get(self::STATUS_PATH), true);

        return is_array($status) ? $status : [
            'status' => 'unknown',
            'message' => 'Backup status file could not be read.',
            'type' => null,
            'ran_at' => null,
            'output' => null,
        ];
    }

    public function settings(): array
    {
        return [
            'name' => config('backup.backup.name'),
            'disks' => config('backup.backup.destination.disks', []),
            'storage' => 'storage/app/private',
            'include' => config('backup.backup.source.files.include', []),
            'include_env' => filter_var(env('BACKUP_INCLUDE_ENV', false), FILTER_VALIDATE_BOOLEAN),
            'cleanup' => config('backup.cleanup.default_strategy', []),
            'schedule' => [
                'Daily database backup' => 'php artisan erp:backup-database',
                'Weekly full backup' => 'php artisan erp:backup-full',
                'Monthly cleanup' => 'php artisan erp:backup-clean',
            ],
        ];
    }

    public function runDatabaseBackup(bool $notify = false): void
    {
        $this->runBackupCommand('database', [
            '--only-db' => true,
            '--disable-notifications' => ! $notify,
        ]);
    }

    public function runFullBackup(bool $notify = false): void
    {
        $this->runBackupCommand('full', [
            '--disable-notifications' => ! $notify,
        ]);
    }

    public function cleanup(bool $notify = false): void
    {
        $this->runCommand('cleanup', 'backup:clean', [
            '--disable-notifications' => ! $notify,
        ]);
    }

    public function download(string $encoded)
    {
        $path = $this->decodePath($encoded);
        abort_unless($this->isKnownBackup($path), 404);

        return Storage::disk('local')->download($path);
    }

    public function delete(string $encoded): void
    {
        $path = $this->decodePath($encoded);
        abort_unless($this->isKnownBackup($path), 404);

        Storage::disk('local')->delete($path);
    }

    private function runBackupCommand(string $type, array $parameters): void
    {
        $this->runCommand($type, 'backup:run', $parameters);
    }

    private function runCommand(string $type, string $command, array $parameters): void
    {
        try {
            $exitCode = Artisan::call($command, $parameters);
            $output = trim(Artisan::output());

            if ($exitCode !== 0) {
                throw new RuntimeException($output ?: 'The backup command did not complete successfully.');
            }

            $message = $type === 'cleanup'
                ? 'Backup cleanup completed successfully.'
                : ucfirst($type).' backup created successfully.';

            $this->writeStatus('success', $type, $message, $output);
            Log::info('ERP backup command completed.', [
                'type' => $type,
                'command' => $command,
                'output' => $output,
            ]);
        } catch (Throwable $exception) {
            $message = ucfirst($type).' backup failed: '.$exception->getMessage();
            $this->writeStatus('failed', $type, $message, trim(Artisan::output()));
            Log::error('ERP backup command failed.', [
                'type' => $type,
                'command' => $command,
                'message' => $exception->getMessage(),
            ]);

            throw new RuntimeException($message, previous: $exception);
        }
    }

    private function writeStatus(string $status, string $type, string $message, ?string $output): void
    {
        Storage::disk('local')->put(self::STATUS_PATH, json_encode([
            'status' => $status,
            'type' => $type,
            'message' => $message,
            'ran_at' => now()->toDateTimeString(),
            'output' => $output,
        ], JSON_PRETTY_PRINT));
    }

    private function classifyBackup(string $path): string
    {
        $fullPath = Storage::disk('local')->path($path);

        if (! is_file($fullPath)) {
            return 'Backup';
        }

        $zip = new ZipArchive();

        if ($zip->open($fullPath) !== true) {
            return 'Backup';
        }

        $hasDatabaseDump = false;
        $hasFilePayload = false;

        for ($index = 0; $index < $zip->numFiles; $index++) {
            $name = strtolower((string) $zip->getNameIndex($index));

            if (Str::endsWith($name, ['.sql', '.dump', '.sqlite', '.sqlite3'])) {
                $hasDatabaseDump = true;
            }

            if (
                str_contains($name, 'storage/app/public')
                || str_contains($name, '.env')
                || (! Str::endsWith($name, ['.sql', '.dump', '.sqlite', '.sqlite3']) && ! str_ends_with($name, '/'))
            ) {
                $hasFilePayload = true;
            }
        }

        $zip->close();

        return $hasDatabaseDump && ! $hasFilePayload
            ? 'Database'
            : ($hasDatabaseDump ? 'Full' : 'Files');
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
