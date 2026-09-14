<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Region;
use App\Models\Store;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function show(Region $region, string $categorySlug): View
    {
        $category = Category::where('region_id', $region->id)->where('type', 'store')->where('slug', $categorySlug)->where('is_active', true)
            ->firstOrFail();

        $storesQuery = Store::where('region_id', $region->id)->where('category_id', $category->id)->visible();

        $stores = (clone $storesQuery)->withCount('offers')->orderBy('name')->paginate(20)->withQueryString();

        // Full alphabetical index of every store in this category, grouped by first letter.
        $allStoresGrouped = (clone $storesQuery)->orderBy('name')->get()
            ->groupBy(fn (Store $store) => strtoupper(substr($store->name, 0, 1)))
            ->sortKeys();

        $allCategories = Category::where('region_id', $region->id)->where('type', 'store')->where('is_active', true)
            ->orderBy('sort_order')->get();

        return view('public.category', [
            'region' => $region,
            'pageType' => 'category',
            'category' => $category,
            'stores' => $stores,
            'allStoresGrouped' => $allStoresGrouped,
            'allCategories' => $allCategories,
            'seoTitle' => $category->meta_title ?: "Coupons for {$category->name} Stores ".now()->format('Y'),
            'seoDescription' => $category->meta_description ?: "Verified coupon codes and deals for {$category->name} stores in {$region->name}.",
        ]);
    }
}
