<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
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
        // Some shared-hosting deployments (this app included, on Hostinger)
        // can't point the domain's document root at public/ directly, and
        // instead use a root .htaccess that internally rewrites every
        // request into public/index.php. That internal rewrite changes
        // SCRIPT_NAME to /public/index.php, which is what Laravel's
        // Request::getBaseUrl() normally derives the app's base path from —
        // so every generated URL (redirect(), url(), route()) silently
        // gained a leading /public segment (e.g. https://host/public/us
        // instead of https://host/us). Forcing the root URL from the
        // configured APP_URL sidesteps SCRIPT_NAME entirely, so this is
        // correct regardless of hosting quirks — but APP_URL in the live
        // .env must be the real bare domain (e.g. https://offerinos.com),
        // never .../public.
        URL::forceRootUrl(config('app.url'));

        Gate::define('manage-users', fn (User $user) => $user->isSuperadmin());

        // Superadmin and Manager keep full access to everything this gate
        // guards (every admin area except Stores, Coupons, Blogs, and Users —
        // those have their own narrower gates below); the granular roles are
        // deliberately excluded here.
        Gate::define('full-admin-access', fn (User $user) => $user->isSuperadmin() || $user->isManager());

        // Coupons is reachable by two different granular roles (Store &
        // Coupon Manager, and Blog + Coupons Manager) while Stores is only
        // reachable by the former — hence separate gates rather than one
        // combined "manage-stores-coupons" gate.
        Gate::define('manage-stores', fn (User $user) => $user->isSuperadmin() || $user->isManager() || $user->isStoreCouponManager());

        Gate::define('manage-coupons', fn (User $user) => $user->isSuperadmin() || $user->isManager() || $user->isStoreCouponManager() || $user->isBlogCouponManager());

        Gate::define('manage-blogs', fn (User $user) => $user->isSuperadmin() || $user->isManager() || $user->isBlogManager() || $user->isBlogCouponManager());

        RateLimiter::for('admin-login', fn ($request) => Limit::perMinutes(5, 5)->by($request->ip()));
        RateLimiter::for('contact-form', fn ($request) => Limit::perMinutes(5, 5)->by($request->ip()));
    }
}
