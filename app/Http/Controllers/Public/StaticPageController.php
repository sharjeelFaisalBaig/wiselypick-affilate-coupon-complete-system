<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\ContactPageAgenda;
use App\Models\Region;
use App\Models\StaticPage;
use Illuminate\View\View;

class StaticPageController extends Controller
{
    /**
     * $staticPage is already resolved by PageRouterController (via
     * StaticPage::resolveByPath(), which is what actually matches its
     * possibly-prefixed/suffixed path) — see Public\StoreController::show()'s
     * equivalent docblock for why this takes the resolved model directly
     * rather than a slug.
     */
    public function show(Region $region, StaticPage $staticPage): View
    {
        $page = $staticPage;

        $viewData = [
            'region' => $region,
            'pageType' => 'static_page',
            'page' => $page,
            'currentPageScripts' => $page,
            'seoTitle' => $page->meta_title ?: $page->title,
            'seoDescription' => $page->meta_description,
            'ogTitle' => $page->og_title,
            'ogImage' => $page->og_image,
            'robotsIndex' => $page->robots_index,
            'robotsFollow' => $page->robots_follow,
        ];

        if ($page->page_type === 'contact') {
            $viewData['agendas'] = ContactPageAgenda::where('region_id', $region->id)->where('is_active', true)
                ->orderBy('sort_order')->get();

            return view('public.contact', $viewData);
        }

        return view('public.static-page', $viewData);
    }
}
