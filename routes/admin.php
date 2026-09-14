<?php

use App\Http\Controllers\Admin\AdminSettingController;
use App\Http\Controllers\Admin\AffiliateNetworkController;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\BadgeController;
use App\Http\Controllers\Admin\BlogCategoryController;
use App\Http\Controllers\Admin\BlogController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\ContactMessageController;
use App\Http\Controllers\Admin\ContactPageController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\GeneralSettingController;
use App\Http\Controllers\Admin\HomepageSectionController;
use App\Http\Controllers\Admin\MenuController;
use App\Http\Controllers\Admin\OfferController;
use App\Http\Controllers\Admin\PagesOverviewController;
use App\Http\Controllers\Admin\PageSettingController;
use App\Http\Controllers\Admin\ProfileController;
use App\Http\Controllers\Admin\RegionController;
use App\Http\Controllers\Admin\RegionSwitchController;
use App\Http\Controllers\Admin\ScriptInjectionController;
use App\Http\Controllers\Admin\StaticPageController;
use App\Http\Controllers\Admin\StoreController;
use App\Http\Controllers\Admin\StoreSuffixController;
use App\Http\Controllers\Admin\UserController;
use App\Models\AdminSetting;
use Illuminate\Support\Facades\Route;

// The prefix itself is DB-backed (Superadmin-editable, see AdminSettingController)
// but always falls back to config('admin.default_path') — see AdminSetting::panelPath().
// Must stay a static/eagerly-resolved string here (not a {wildcard} route
// parameter) and registered before the {region} catch-all in web.php, or a
// request like GET /admin matches {region}="admin" first and never reaches
// the panel. Changing the stored path clears the route cache immediately
// (AdminSettingController::update()) so it takes effect on the very next
// uncached request; a production route:cache still needs re-running (part
// of the normal deploy flow) to bake the new value into a cached route file.
Route::prefix(AdminSetting::panelPath())->name('admin.')->group(function () {
    Route::middleware('guest')->group(function () {
        Route::get('login', [AuthController::class, 'showLogin'])->name('login');
        Route::post('login', [AuthController::class, 'login'])->middleware('throttle:admin-login')->name('login.attempt');
    });

    Route::middleware(['auth', 'admin.active', 'admin.region'])->group(function () {
        Route::post('logout', [AuthController::class, 'logout'])->name('logout');
        Route::post('switch-region', [RegionSwitchController::class, 'switch'])->name('region.switch');

        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

        Route::get('profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::put('profile', [ProfileController::class, 'update'])->name('profile.update');

        Route::middleware('can:manage-users')->group(function () {
            Route::post('users/{user}/suspend', [UserController::class, 'suspend'])->name('users.suspend');
            Route::post('users/{user}/reactivate', [UserController::class, 'reactivate'])->name('users.reactivate');
            Route::resource('users', UserController::class)->except('show');

            Route::get('admin-settings', [AdminSettingController::class, 'edit'])->name('admin-settings.edit');
            Route::put('admin-settings', [AdminSettingController::class, 'update'])->name('admin-settings.update');
        });

        // Stores + Coupons/Offers — the "Store & Coupon Manager" role's
        // entire scope, also reachable by Superadmin/Manager as before.
        Route::middleware('can:manage-stores-coupons')->group(function () {
            Route::get('stores/suggest', [StoreController::class, 'suggest'])->name('stores.suggest');
            Route::get('stores/classification', [StoreController::class, 'classification'])->name('stores.classification');
            Route::post('stores/reorder-featured', [StoreController::class, 'reorderFeatured'])->name('stores.reorder-featured');
            Route::post('stores/reorder-popular', [StoreController::class, 'reorderPopular'])->name('stores.reorder-popular');
            Route::post('stores/reorder-pending', [StoreController::class, 'reorderPending'])->name('stores.reorder-pending');
            Route::post('stores/reorder-featured-offers', [StoreController::class, 'reorderFeaturedOffers'])->name('stores.reorder-featured-offers');
            Route::post('stores/{store}/toggle-active', [StoreController::class, 'toggleActive'])->name('stores.toggle-active');
            Route::resource('stores', StoreController::class)->except('show');

            Route::get('offers/suggest', [OfferController::class, 'suggest'])->name('offers.suggest');
            Route::post('offers/store/{store}/reorder', [OfferController::class, 'reorder'])->name('offers.reorder');
            Route::resource('offers', OfferController::class)->except('show');
        });

        // Blogs + Blog Categories — the "Blog Manager" role's entire scope.
        Route::middleware('can:manage-blogs')->group(function () {
            Route::post('blog-categories/reorder', [BlogCategoryController::class, 'reorder'])->name('blog-categories.reorder');
            Route::resource('blog-categories', BlogCategoryController::class)->except('show');

            Route::post('blogs/reorder', [BlogController::class, 'reorder'])->name('blogs.reorder');
            Route::resource('blogs', BlogController::class)->except('show');
        });

        // Everything else — unreachable by the two granular roles above,
        // same as it always was for Superadmin/Manager.
        Route::middleware('can:full-admin-access')->group(function () {
            Route::get('pages', [PagesOverviewController::class, 'index'])->name('pages-overview.index');

            Route::post('regions/{region}/make-default', [RegionController::class, 'makeDefault'])->name('regions.make-default');
            Route::post('regions/{region}/toggle-active', [RegionController::class, 'toggleActive'])->name('regions.toggle-active');
            Route::resource('regions', RegionController::class)->except('show');

            Route::get('menus', [MenuController::class, 'index'])->name('menus.index');
            Route::get('menus/{menu}', [MenuController::class, 'edit'])->name('menus.edit');
            Route::post('menus/{menu}/items', [MenuController::class, 'storeItem'])->name('menus.items.store');
            Route::post('menus/{menu}/items/reorder', [MenuController::class, 'reorderItems'])->name('menus.items.reorder');
            Route::delete('menus/{menu}/items/{item}', [MenuController::class, 'destroyItem'])->name('menus.items.destroy');

            Route::get('general-settings', [GeneralSettingController::class, 'edit'])->name('general-settings.edit');
            Route::put('general-settings', [GeneralSettingController::class, 'update'])->name('general-settings.update');

            Route::resource('badges', BadgeController::class)->except('show');
            Route::resource('store-suffixes', StoreSuffixController::class)->except('show');

            Route::get('categories/suggest', [CategoryController::class, 'suggest'])->name('categories.suggest');
            Route::post('categories/reorder', [CategoryController::class, 'reorder'])->name('categories.reorder');
            Route::resource('categories', CategoryController::class)->except('show');

            Route::resource('script-injections', ScriptInjectionController::class)->except('show');

            Route::resource('affiliate-networks', AffiliateNetworkController::class)->except('show');

            Route::get('contact-messages', [ContactMessageController::class, 'index'])->name('contact-messages.index');
            Route::post('contact-messages/{contactMessage}/toggle-read', [ContactMessageController::class, 'markRead'])->name('contact-messages.toggle-read');
            Route::delete('contact-messages/{contactMessage}', [ContactMessageController::class, 'destroy'])->name('contact-messages.destroy');

            Route::resource('static-pages', StaticPageController::class)->except('show');

            Route::post('homepage-sections/reorder', [HomepageSectionController::class, 'reorder'])->name('homepage-sections.reorder');
            Route::get('homepage-sections/picker-results', [HomepageSectionController::class, 'pickerResults'])->name('homepage-sections.picker-results');
            Route::resource('homepage-sections', HomepageSectionController::class)->except('show');

            Route::get('page-settings/{pageKey}', [PageSettingController::class, 'edit'])->name('page-settings.edit');
            Route::put('page-settings/{pageKey}', [PageSettingController::class, 'update'])->name('page-settings.update');

            Route::get('contact-page', [ContactPageController::class, 'edit'])->name('contact-page.edit');
            Route::post('contact-page/agendas', [ContactPageController::class, 'storeAgenda'])->name('contact-page.agendas.store');
            Route::put('contact-page/agendas/{agenda}', [ContactPageController::class, 'updateAgenda'])->name('contact-page.agendas.update');
            Route::delete('contact-page/agendas/{agenda}', [ContactPageController::class, 'destroyAgenda'])->name('contact-page.agendas.destroy');
            Route::post('contact-page/agendas/reorder', [ContactPageController::class, 'reorderAgendas'])->name('contact-page.agendas.reorder');
        });
    });
});
