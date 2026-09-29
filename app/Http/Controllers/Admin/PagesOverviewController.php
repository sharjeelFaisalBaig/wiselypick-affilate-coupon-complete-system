<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PageSetting;
use App\Models\Region;
use App\Models\StaticPage;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * A thin, read-only aggregating screen over the 3 existing storage models
 * (StaticPage, PageSetting, HomepageSection) — per the SRS's "Pages module"
 * (fixed set of pages, cannot add/delete, tabular view), without merging
 * them into one polymorphic table. Each row deep-links to whichever
 * specialized screen actually manages that page.
 */
class PagesOverviewController extends Controller
{
    public function index(Request $request): View
    {
        /** @var Region $region */
        $region = $request->attributes->get('activeRegion');

        // Keyed by page_type (not slug — that's freely admin-editable, see
        // StaticPage::SEED_DEFAULTS) so this screen keeps finding "the"
        // contact/terms/privacy page after any of them gets renamed.
        $staticPagesByType = StaticPage::where('region_id', $region->id)
            ->whereIn('page_type', ['contact', 'terms', 'privacy'])
            ->get()->keyBy('page_type');
        $staticPathFor = fn (string $type) => $staticPagesByType->has($type)
            ? '/'.$staticPagesByType[$type]->path()
            : null;

        $pageSettingsByKey = PageSetting::where('region_id', $region->id)->get()->keyBy('page_key');
        $pathFor = fn (string $pageKey) => '/'.($pageSettingsByKey->get($pageKey)?->slug ?? PageSetting::DEFAULT_SLUGS[$pageKey]);

        $rows = [
            [
                'name' => 'Homepage',
                'path' => $pathFor('home'),
                'is_active' => $pageSettingsByKey->get('home')?->is_active ?? true,
                'content_edit_route' => route('admin.homepage-sections.index'),
                'seo_edit_route' => route('admin.page-settings.edit', 'home'),
            ],
            [
                'name' => 'Contact',
                'path' => $staticPathFor('contact'),
                'is_active' => $staticPagesByType->get('contact')?->is_active ?? true,
                'content_edit_route' => $staticPagesByType->has('contact') ? route('admin.static-pages.edit', $staticPagesByType['contact']) : null,
                'seo_edit_route' => route('admin.contact-page.edit'),
                'seo_edit_label' => 'Question Agendas',
            ],
            [
                'name' => 'Terms of Use',
                'path' => $staticPathFor('terms'),
                'is_active' => $staticPagesByType->get('terms')?->is_active ?? true,
                'content_edit_route' => $staticPagesByType->has('terms') ? route('admin.static-pages.edit', $staticPagesByType['terms']) : null,
            ],
            [
                'name' => 'Privacy Policy',
                'path' => $staticPathFor('privacy'),
                'is_active' => $staticPagesByType->get('privacy')?->is_active ?? true,
                'content_edit_route' => $staticPagesByType->has('privacy') ? route('admin.static-pages.edit', $staticPagesByType['privacy']) : null,
            ],
            [
                'name' => 'Promo Codes',
                'path' => $pathFor('coupons'),
                'is_active' => $pageSettingsByKey->get('coupons')?->is_active ?? true,
                'content_edit_route' => route('admin.page-settings.edit', 'coupons'),
                'content_edit_label' => 'Settings',
            ],
            [
                'name' => 'Stores Listing',
                'path' => $pathFor('stores'),
                'is_active' => $pageSettingsByKey->get('stores')?->is_active ?? true,
                'content_edit_route' => route('admin.page-settings.edit', 'stores'),
                'content_edit_label' => 'Settings',
            ],
            [
                'name' => 'Blogs',
                'path' => $pathFor('blogs'),
                'is_active' => $pageSettingsByKey->get('blogs')?->is_active ?? true,
                'content_edit_route' => route('admin.page-settings.edit', 'blogs'),
                'content_edit_label' => 'Settings',
            ],
        ];

        return view('admin.pages-overview.index', ['rows' => $rows]);
    }
}
