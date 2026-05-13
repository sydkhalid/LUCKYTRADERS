<?php

namespace App\Models\Concerns;

use Spatie\Activitylog\Contracts\Activity;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

trait LogsErpActivity
{
    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName($this->getTable())
            ->logFillable()
            ->logOnlyDirty()
            ->dontLogIfAttributesChangedOnly(['updated_at'])
            ->dontSubmitEmptyLogs();
    }

    public function getDescriptionForEvent(string $eventName): string
    {
        return class_basename($this).' '.$eventName;
    }

    public function tapActivity(Activity $activity, string $eventName): void
    {
        $user = auth()->user();

        $activity->properties = $activity->properties->merge(array_filter([
            'module' => $activity->log_name,
            'action' => $eventName,
            'user_name' => $user?->name,
            'role' => $user && method_exists($user, 'primaryRoleName') ? $user->primaryRoleName() : null,
            'ip_address' => request()?->ip(),
            'user_agent' => request()?->userAgent(),
            'device' => $this->activityDeviceLabel(request()?->userAgent()),
            'record_name' => method_exists($this, 'activityRecordName') ? $this->activityRecordName() : $this->getKey(),
        ], fn ($value) => $value !== null && $value !== ''));
    }

    private function activityDeviceLabel(?string $userAgent): string
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
