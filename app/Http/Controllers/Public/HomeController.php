<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\HomepageSection;
use App\Models\PageSetting;
use App\Models\Region;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(Region $region): View
    {
        $pageSetting = PageSetting::forPage($region->id, 'home');
        abort_if($pageSetting && ! $pageSetting->is_active, 404);

        // A store/offer picked into a section can be moved to Pending (or
        // deactivated/expired) afterwards without anyone touching the
        // section itself — these eager loads re-check current visibility
        // every time the homepage renders, rather than trusting a pick made
        // whenever the admin last saved the section.
        $sections = HomepageSection::where('region_id', $region->id)->where('is_active', true)
            ->with([
                'offers' => fn ($q) => $q->where('is_active', true)
                    ->where(fn ($eq) => $eq->whereNull('expiry_date')->orWhere('expiry_date', '>', now()))
                    ->whereHas('store', fn ($sq) => $sq->visible()),
                'offers.store', 'offers.badges',
                'stores' => fn ($q) => $q->visible()->withCount([
                    'coupons as active_coupons_count' => fn ($cq) => $cq->where('is_active', true),
                    'deals as active_deals_count' => fn ($dq) => $dq->where('is_active', true),
                ]),
                'stores.storeSlugPrefix', 'stores.storeSlugSuffix',
            ])
            ->orderBy('sort_order')
            ->get()
            ->map(function (HomepageSection $section) use ($region) {
                // CTA links are stored as region-agnostic relative paths
                // (e.g. "/coupons") so admins don't have to think about the
                // region prefix — it's applied here at render time.
                if ($section->cta_url && ! str_starts_with($section->cta_url, 'http')) {
                    $section->display_cta_url = $region->publicUrl($section->cta_url);
                } else {
                    $section->display_cta_url = $section->cta_url;
                }

                return $section;
            });

        return view('public.home', [
            'region' => $region,
            'pageType' => 'home',
            'currentPageScripts' => $pageSetting,
            'sections' => $sections,
            'heading' => $pageSetting?->heading ?: $region->name.' Coupons, Promo Codes & Deals',
            'subheading' => $pageSetting?->subheading ?: 'Save today with verified coupon codes, promo codes and deals for top stores in '.$region->name.'.',
            'heroSearchPlaceholder' => $pageSetting?->hero_search_placeholder ?: 'Search for a store or brand...',
            'heroSearchButtonText' => $pageSetting?->hero_search_button_text ?: 'Search',
            'heroBadgeText' => $pageSetting?->hero_badge_text,
            'seoTitle' => $pageSetting?->meta_title ?: ($region->name.' Coupons, Promo Codes & Deals — '.now()->format('F Y')),
            'seoDescription' => $pageSetting?->meta_description ?: ('Save today with verified coupon codes, promo codes and deals for top stores in '.$region->name.'.'),
            'ogTitle' => $pageSetting?->og_title,
            'ogImage' => $pageSetting?->og_image,
            'robotsIndex' => $pageSetting?->robots_index ?? true,
            'robotsFollow' => $pageSetting?->robots_follow ?? true,
        ]);
    }
}
