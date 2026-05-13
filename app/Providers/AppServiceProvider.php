<?php

namespace App\Providers;

use App\Models\User;
use App\Services\ActivityLogger;
use App\Services\SystemSettingService;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password as PasswordRule;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        PasswordRule::defaults(fn () => PasswordRule::min(12)->mixedCase()->numbers()->symbols());

        RateLimiter::for('api', function (Request $request): Limit {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        RateLimiter::for('auth', function (Request $request): Limit {
            return Limit::perMinute(10)->by($request->ip());
        });

        RateLimiter::for('erp', function (Request $request): Limit {
            return Limit::perMinute(240)->by($request->user()?->id ?: $request->ip());
        });

        View::composer('*', function ($view): void {
            static $shared = null;

            if ($shared === null) {
                $service = app(SystemSettingService::class);
                $settings = $service->settings();
                $company = $service->company();
                $invoice = $service->invoiceSettings();
                $currency = strtoupper((string) ($settings->currency ?: 'INR'));
                $symbols = [
                    'INR' => 'Rs.',
                    'USD' => '$',
                    'EUR' => '€',
                    'GBP' => '£',
                    'AED' => 'AED',
                    'SGD' => 'S$',
                ];

                $company['logo_url'] = $company['logo']
                    ? Storage::disk('public')->url($company['logo'])
                    : null;

                $shared = [
                    'erpCompany' => $company,
                    'erpSystemSettings' => $settings,
                    'erpInvoiceSettings' => $invoice,
                    'erpTheme' => [
                        'mode' => in_array($settings->theme_mode, ['light', 'dark'], true) ? $settings->theme_mode : 'light',
                        'color' => $settings->theme_color ?: '#2563eb',
                        'sidebar_style' => $settings->sidebar_style ?: 'dark',
                        'header_style' => $settings->header_style ?: 'light',
                    ],
                    'erpCurrency' => [
                        'code' => $currency,
                        'symbol' => $symbols[$currency] ?? $currency,
                    ],
                    'erpBusinessType' => 'Steel Trading ERP',
                ];
            }

            $view->with($shared);
        });

        Gate::before(function (User $user, string $ability): ?bool {
            $roleTablesReady = Schema::hasTable('roles') && Schema::hasTable('model_has_roles');

            if ($roleTablesReady && $user->hasRole('Super Admin')) {
                return true;
            }

            if ((! $roleTablesReady || ! $user->roles()->exists()) && (bool) $user->is_admin) {
                return true;
            }

            return null;
        });

        Event::listen(Login::class, function (Login $event): void {
            app(ActivityLogger::class)->log('login', 'auth', 'User logged in', causer: $event->user);
        });

        Event::listen(Logout::class, function (Logout $event): void {
            if ($event->user) {
                app(ActivityLogger::class)->log('logout', 'auth', 'User logged out', causer: $event->user);
            }
        });
    }
}
