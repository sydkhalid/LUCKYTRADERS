<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Spatie\Activitylog\Models\Activity;

class ActivityLogger
{
    /**
     * @param array<string, mixed> $old
     * @param array<string, mixed> $new
     */
    public function log(
        string $action,
        string $module,
        string $description,
        ?Model $subject = null,
        array $old = [],
        array $new = [],
        ?Model $causer = null
    ): Activity {
        $user = $causer ?: Auth::user();
        $logger = activity($module)
            ->event($action)
            ->withProperties(array_filter([
                'module' => $module,
                'action' => $action,
                'user_name' => $user?->name,
                'role' => $user && method_exists($user, 'primaryRoleName') ? $user->primaryRoleName() : null,
                'ip_address' => request()?->ip(),
                'user_agent' => request()?->userAgent(),
                'device' => $this->deviceLabel(request()?->userAgent()),
                'old' => $old,
                'attributes' => $new,
            ], fn ($value) => $value !== null && $value !== []));

        if ($user) {
            $logger->causedBy($user);
        }

        if ($subject) {
            $logger->performedOn($subject);
        }

        return $logger->log($description);
    }

    private function deviceLabel(?string $userAgent): string
    {
        $userAgent = strtolower((string) $userAgent);

        if ($userAgent === '') {
            return 'Unknown';
        }

        $device = str_contains($userAgent, 'mobile') || str_contains($userAgent, 'android') || str_contains($userAgent, 'iphone')
            ? 'Mobile'
            : 'Desktop';

        $browser = match (true) {
            str_contains($userAgent, 'edg/') => 'Edge',
            str_contains($userAgent, 'chrome/') => 'Chrome',
            str_contains($userAgent, 'firefox/') => 'Firefox',
            str_contains($userAgent, 'safari/') => 'Safari',
            default => 'Browser',
        };

        return $device.' '.$browser;
    }
}
