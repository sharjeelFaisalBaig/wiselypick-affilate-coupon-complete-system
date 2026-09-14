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

        $sections = HomepageSection::where('region_id', $region->id)->where('is_active', true)
            ->with(['offers.store', 'offers.badges', 'categories', 'stores' => fn ($q) => $q->withCount([
                'coupons as active_coupons_count' => fn ($cq) => $cq->where('is_active', true),
                'deals as active_deals_count' => fn ($dq) => $dq->where('is_active', true),
            ])])
            ->orderBy('sort_order')
            ->get()
            ->map(function (HomepageSection $section) use ($region) {
                // CTA links are stored as region-agnostic relative paths
                // (e.g. "/coupons") so admins don't have to think about the
                // region prefix — it's applied here at render time.
                if ($section->cta_url && ! str_starts_with($section->cta_url, 'http')) {
                    $section->display_cta_url = '/'.$region->code.'/'.ltrim($section->cta_url, '/');
                } else {
                    $section->display_cta_url = $section->cta_url;
                }

                return $section;
            });

        return view('public.home', [
            'region' => $region,
            'pageType' => 'home',
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
