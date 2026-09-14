<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\ContactPageAgenda;
use App\Models\Region;
use App\Models\StaticPage;
use Illuminate\View\View;

class StaticPageController extends Controller
{
    public function show(Region $region, string $pageSlug): View
    {
        $page = StaticPage::where('region_id', $region->id)->where('slug', $pageSlug)->where('is_active', true)->firstOrFail();

        $viewData = [
            'region' => $region,
            'pageType' => 'static_page',
            'page' => $page,
            'seoTitle' => $page->meta_title ?: $page->title,
            'seoDescription' => $page->meta_description,
            'ogTitle' => $page->og_title,
            'ogImage' => $page->og_image,
            'robotsIndex' => $page->robots_index,
            'robotsFollow' => $page->robots_follow,
        ];

        if ($pageSlug === 'contact') {
            $viewData['agendas'] = ContactPageAgenda::where('region_id', $region->id)->where('is_active', true)
                ->orderBy('sort_order')->get();

            return view('public.contact', $viewData);
        }

        return view('public.static-page', $viewData);
    }
}
