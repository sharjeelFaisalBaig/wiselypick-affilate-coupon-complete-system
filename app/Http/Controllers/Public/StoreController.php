<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Region;
use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StoreController extends Controller
{
    /**
     * $store is already resolved by PageRouterController (its path — a
     * per-store admin-editable {prefix}/{slug}[/{suffix}] — is no longer a
     * fixed "store/{slug}" route, so this can't rely on route-model-binding
     * from a URL segment anymore).
     */
    public function show(Request $request, Region $region, Store $store): View
    {
        $store->load(['category', 'storeSuffix']);

        $query = $store->offers()->with(['store', 'badges'])->where('is_active', true);

        match ($request->string('filter')->value()) {
            'coupon' => $query->where('offer_type', 'coupon'),
            'deal' => $query->where('offer_type', 'deal'),
            default => null,
        };

        if ($request->filled('q')) {
            $search = $request->string('q')->value();
            $query->where(fn ($q) => $q->where('title', 'like', "%{$search}%")->orWhere('code', 'like', "%{$search}%"));
        }

        $offers = $query->orderBy('sort_order')->paginate(24)->withQueryString();

        $couponCount = $store->offers()->where('is_active', true)->where('offer_type', 'coupon')->count();
        $dealCount = $store->offers()->where('is_active', true)->where('offer_type', 'deal')->count();

        $suffix = $store->storeSuffix?->name ?: 'Promo Codes, Coupons & Deals';
        $h1 = "{$store->name} {$suffix} ".now()->format('F Y');

        $viewData = [
            'region' => $region,
            'pageType' => 'store_detail',
            'storeId' => $store->id,
            'store' => $store,
            'offers' => $offers,
            'couponCount' => $couponCount,
            'dealCount' => $dealCount,
            'savingsStats' => $store->savingsStats(),
            'h1' => $h1,
            'seoTitle' => $store->meta_title ?: $h1,
            'seoDescription' => $store->meta_description ?: "Save with the latest verified {$store->name} coupon codes and deals in {$region->name}.",
            'canonicalUrl' => $region->canonicalUrlFor($request->path()),
            'robotsIndex' => $store->robots_index,
            'robotsFollow' => $store->robots_follow,
            'ogTitle' => $store->og_title,
            'ogImage' => $store->og_image,
        ];

        if ($request->header('X-Ajax-Filter')) {
            return view('public.partials.store-offers-results', $viewData);
        }

        return view('public.store', $viewData);
    }
}
