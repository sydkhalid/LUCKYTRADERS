<?php

namespace App\Providers;

use App\Models\User;
use App\Services\ActivityLogger;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Schema;
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
