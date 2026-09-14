<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\PageSetting;
use App\Models\Region;
use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StoreDirectoryController extends Controller
{
    public function suggest(Request $request, Region $region): \Illuminate\Http\JsonResponse
    {
        $q = $request->string('q')->value();

        $stores = Store::where('region_id', $region->id)->visible()
            ->where('name', 'like', "%{$q}%")
            ->when($request->filled('category_id'), fn ($sq) => $sq->where('category_id', $request->integer('category_id')))
            ->orderBy('name')->limit(8)->get(['slug', 'name', 'route_prefix', 'route_suffix']);

        return response()->json($stores->map(fn ($store) => [
            'label' => $store->name,
            'url' => $store->urlFor($region),
        ]));
    }

    public function index(Request $request, Region $region): View
    {
        $query = Store::where('region_id', $region->id)->visible()
            ->withCount([
                'coupons as active_coupons_count' => fn ($q) => $q->where('is_active', true),
                'deals as active_deals_count' => fn ($q) => $q->where('is_active', true),
            ]);

        if ($request->filled('q')) {
            $query->where('name', 'like', '%'.$request->string('q').'%');
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->integer('category_id'));
        }

        // Featured/Popular stores get priority in filter and search results.
        $stores = $query->orderByDesc('is_featured')->orderByDesc('is_popular')->orderBy('name')->paginate(20)->withQueryString();

        $categories = Category::where('region_id', $region->id)->where('type', 'store')->where('is_active', true)
            ->orderBy('sort_order')->get(['id', 'name']);

        // Row 3: A-Z directory of every active store's name, independent of
        // Row 2's paginated/filtered grid — grouped by first letter, with
        // letters that have zero stores omitted entirely.
        $directory = Store::where('region_id', $region->id)->visible()
            ->orderBy('name')->get(['name', 'slug', 'route_prefix', 'route_suffix'])
            ->groupBy(fn ($store) => mb_strtoupper(mb_substr($store->name, 0, 1)));

        $settings = PageSetting::forPage($region->id, 'stores');

        $viewData = [
            'region' => $region,
            'pageType' => 'stores_directory',
            'stores' => $stores,
            'categories' => $categories,
            'selectedCategoryId' => $request->integer('category_id') ?: null,
            'directory' => $directory,
            'heading' => $settings?->heading ?: 'Find Coupons by Store',
            'subheading' => $settings?->subheading,
            'seoTitle' => $settings?->meta_title ?: 'Find Coupons by Store — '.$region->name,
            'seoDescription' => $settings?->meta_description ?: 'Browse all stores with active coupon codes and deals in '.$region->name.'.',
            'ogTitle' => $settings?->og_title,
            'ogImage' => $settings?->og_image,
            'robotsIndex' => $settings?->robots_index ?? true,
            'robotsFollow' => $settings?->robots_follow ?? true,
        ];

        if ($request->header('X-Ajax-Filter')) {
            return view('public.partials.stores-results', $viewData);
        }

        return view('public.stores', $viewData);
    }
}
