<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

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
        Gate::define('manage-users', fn (User $user) => $user->isSuperadmin());

        // Superadmin and Manager keep full access to everything this gate
        // guards (every admin area except Stores/Coupons, Blogs, and Users —
        // those have their own narrower gates below); the two granular
        // roles are deliberately excluded here.
        Gate::define('full-admin-access', fn (User $user) => $user->isSuperadmin() || $user->isManager());

        Gate::define('manage-stores-coupons', fn (User $user) => $user->isSuperadmin() || $user->isManager() || $user->isStoreCouponManager());

        Gate::define('manage-blogs', fn (User $user) => $user->isSuperadmin() || $user->isManager() || $user->isBlogManager());

        RateLimiter::for('admin-login', fn ($request) => Limit::perMinutes(5, 5)->by($request->ip()));
        RateLimiter::for('contact-form', fn ($request) => Limit::perMinutes(5, 5)->by($request->ip()));
    }
}
