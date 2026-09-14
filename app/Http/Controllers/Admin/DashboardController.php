<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Blog;
use App\Models\Category;
use App\Models\Offer;
use App\Models\Region;
use App\Models\Store;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        /** @var Region $region */
        $region = $request->attributes->get('activeRegion');

        $metrics = [
            'total_stores' => Store::where('region_id', $region->id)->count(),
            'total_coupons' => Offer::whereHas('store', fn ($q) => $q->where('region_id', $region->id))
                ->where('offer_type', 'coupon')->count(),
            'total_categories' => Category::where('region_id', $region->id)->where('type', 'store')->count(),
            'total_blogs' => Blog::where('region_id', $region->id)->count(),
            'total_admin_users' => User::count(),
        ];

        return view('admin.dashboard', ['metrics' => $metrics]);
    }
}
