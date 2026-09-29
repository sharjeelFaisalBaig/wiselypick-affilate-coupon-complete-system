<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\AdminSetting;
use App\Models\Blog;
use App\Models\Category;
use App\Models\PageSetting;
use App\Models\Region;
use App\Models\StaticPage;
use App\Models\Store;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Response;

class SitemapController extends Controller
{
    /**
     * Global /sitemap.xml — a sitemap INDEX (the standard technique for a
     * multi-section site) listing each active region's own sitemap.xml,
     * rather than one giant combined file. Crawlers only ever fetch a file
     * at this exact bare-domain path, so this stays the entry point even
     * though the real URLs now live one level down at {region}/sitemap.xml.
     */
    public function index()
    {
        $xml = Cache::remember('sitemap-index.xml', now()->addHour(), function () {
            $regionUrls = Region::where('is_active', true)->get()
                ->map(fn (Region $region) => $region->publicUrl('sitemap.xml'));

            return view('public.sitemap-index', ['regionUrls' => $regionUrls])->render();
        });

        return Response::make($xml, 200, ['Content-Type' => 'application/xml']);
    }

    /**
     * Per-region /{region}/sitemap.xml — the actual URL list for just this
     * region, cached independently so editing one region's content doesn't
     * invalidate every other region's cached sitemap.
     */
    public function regionSitemap(Region $region)
    {
        $xml = Cache::remember("sitemap.{$region->code}.xml", now()->addHour(), function () use ($region) {
            $urls = [];

            $pageSettings = PageSetting::where('region_id', $region->id)->get()->keyBy('page_key');

            $fixedPages = [
                'home' => ['loc' => PageSetting::urlFor($region, 'home'), 'priority' => '1.0'],
                'stores' => ['loc' => PageSetting::urlFor($region, 'stores'), 'priority' => '0.8'],
                'coupons' => ['loc' => PageSetting::urlFor($region, 'coupons'), 'priority' => '0.8'],
                'blogs' => ['loc' => PageSetting::urlFor($region, 'blogs'), 'priority' => '0.6'],
            ];

            foreach ($fixedPages as $pageKey => $entry) {
                $setting = $pageSettings->get($pageKey);
                // Draft pages 404 on the frontend and noindex pages ask
                // crawlers not to index them — neither belongs in the
                // sitemap.
                if ($setting && (! $setting->is_active || ! $setting->robots_index)) {
                    continue;
                }
                $urls[] = $entry;
            }

            foreach (Category::where('region_id', $region->id)->where('type', 'store')->where('is_active', true)->get() as $category) {
                $urls[] = ['loc' => $region->publicUrl("category/{$category->slug}"), 'priority' => '0.7'];
            }

            foreach (Store::where('region_id', $region->id)->visible()->where('robots_index', true)->with(['storeSlugPrefix', 'storeSlugSuffix'])->get() as $store) {
                $urls[] = ['loc' => $store->urlFor($region), 'priority' => '0.7'];
            }

            foreach (Blog::where('region_id', $region->id)->where('is_published', true)->where('robots_index', true)->with(['blogSlugPrefix', 'blogSlugSuffix'])->get() as $blog) {
                $urls[] = ['loc' => $blog->urlFor($region), 'priority' => '0.5'];
            }

            foreach (StaticPage::where('region_id', $region->id)->where('is_active', true)->where('robots_index', true)->with(['pageSlugPrefix', 'pageSlugSuffix'])->get() as $page) {
                $urls[] = ['loc' => $page->urlFor($region), 'priority' => '0.3'];
            }

            return view('public.sitemap', ['urls' => $urls])->render();
        });

        return Response::make($xml, 200, ['Content-Type' => 'application/xml']);
    }

    /**
     * Global /robots.txt — real crawlers only ever fetch this exact
     * bare-domain path, so it serves the DEFAULT region's rules (same
     * "unknown/disabled region falls back to default" pattern used
     * elsewhere in this app). {region}/robots.txt below is the true
     * per-region file, for a setup where each region eventually gets its
     * own domain proxied at this app (in which case that domain's own
     * bare /robots.txt would be rewritten to this per-region path).
     */
    public function robots()
    {
        $default = Region::where('is_active', true)->where('is_default', true)->first()
            ?? Region::where('is_active', true)->orderBy('sort_order')->first();

        if (! $default) {
            return Response::make("User-agent: *\nDisallow: /\n", 200, ['Content-Type' => 'text/plain']);
        }

        return $this->buildRobotsResponse($default, url('/sitemap.xml'));
    }

    public function regionRobots(Region $region)
    {
        return $this->buildRobotsResponse($region, $region->publicUrl('sitemap.xml'));
    }

    private function buildRobotsResponse(Region $region, string $sitemapUrl)
    {
        $lines = [
            'User-agent: *',
            'Disallow: /'.AdminSetting::panelPath(),
        ];

        foreach ($region->robotsExtraAllowLines() as $url) {
            $lines[] = 'Allow: '.$url;
        }

        foreach ($region->robotsExtraDisallowLines() as $url) {
            $lines[] = 'Disallow: '.$url;
        }

        $lines[] = '';
        $lines[] = 'Sitemap: '.$sitemapUrl;

        return Response::make(implode("\n", $lines)."\n", 200, ['Content-Type' => 'text/plain']);
    }
}
