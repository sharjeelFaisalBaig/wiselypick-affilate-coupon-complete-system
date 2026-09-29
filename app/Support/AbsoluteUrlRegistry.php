<?php

namespace App\Support;

use App\Models\Blog;
use App\Models\PageSetting;
use App\Models\Region;
use App\Models\StaticPage;
use App\Models\Store;
use Illuminate\Support\Collection;

/**
 * Every absolute public URL currently "owned" by something in the system —
 * across ALL regions, not just the one being edited — used to stop an admin
 * from creating a genuine URL collision no matter which side of it is saved
 * second. A root-mounted store/blog (Store/Blog::starts_from_root), a custom
 * prefix/suffix, or a region's own "/{code}" can all land on the exact same
 * path from completely different admin screens — e.g. a root-mounted store
 * slugged "fr" collides with an actual region coded "fr", even though
 * neither one's own region-scoped uniqueness rules would ever catch that.
 */
class AbsoluteUrlRegistry
{
    /**
     * @return Collection<int, array{type: string, id: ?int, region_id: int, page_key: ?string, label: string, url: string}>
     */
    public function entries(): Collection
    {
        $entries = collect();

        foreach (Region::all() as $region) {
            $entries->push([
                'type' => 'region', 'id' => $region->id, 'region_id' => $region->id, 'page_key' => null,
                'label' => "the \"{$region->name}\" region",
                'url' => $region->publicUrl(''),
            ]);

            foreach (array_keys(PageSetting::DEFAULT_SLUGS) as $pageKey) {
                $entries->push([
                    'type' => 'page_setting', 'id' => null, 'region_id' => $region->id, 'page_key' => $pageKey,
                    'label' => "the \"{$region->name}\" region's ".($pageKey === 'home' ? 'homepage' : $pageKey.' page'),
                    'url' => PageSetting::urlFor($region, $pageKey),
                ]);
            }

            foreach (StaticPage::where('region_id', $region->id)->with(['pageSlugPrefix', 'pageSlugSuffix'])->get() as $page) {
                $entries->push([
                    'type' => 'static_page', 'id' => $page->id, 'region_id' => $region->id, 'page_key' => null,
                    'label' => "the page \"{$page->title}\" ({$region->name})",
                    'url' => $page->urlFor($region),
                ]);
            }

            foreach (Store::where('region_id', $region->id)->with(['storeSlugPrefix', 'storeSlugSuffix'])->get() as $store) {
                $entries->push([
                    'type' => 'store', 'id' => $store->id, 'region_id' => $region->id, 'page_key' => null,
                    'label' => "the store \"{$store->name}\" ({$region->name})",
                    'url' => $store->urlFor($region),
                ]);
            }

            foreach (Blog::where('region_id', $region->id)->with(['blogSlugPrefix', 'blogSlugSuffix'])->get() as $blog) {
                $entries->push([
                    'type' => 'blog', 'id' => $blog->id, 'region_id' => $region->id, 'page_key' => null,
                    'label' => "the blog post \"{$blog->title}\" ({$region->name})",
                    'url' => $blog->urlFor($region),
                ]);
            }
        }

        return $entries;
    }

    /**
     * @param  string  $excludeType  'store'|'blog'|'static_page'|'region' — the
     *                               kind of entity currently being saved, so its
     *                               own (pre-existing, on update) entry doesn't
     *                               flag itself as a conflict.
     * @param  int|null  $excludeId  null on create (nothing to exclude yet).
     */
    public function findConflict(string $absoluteUrl, string $excludeType, ?int $excludeId): ?string
    {
        $needle = self::normalize($absoluteUrl);

        foreach ($this->entries() as $entry) {
            if ($entry['type'] === $excludeType && $entry['id'] === $excludeId) {
                continue;
            }

            // A region's own base URL is, BY DEFINITION, identical to that
            // same region's own homepage URL (PageSetting::urlFor(..,'home')
            // is just $region->publicUrl('') again) — not a real collision,
            // so it must never block saving the region itself.
            if ($excludeType === 'region' && $entry['type'] === 'page_setting' && $entry['page_key'] === 'home' && $entry['region_id'] === $excludeId) {
                continue;
            }

            if (self::normalize($entry['url']) === $needle) {
                return $entry['label'];
            }
        }

        return null;
    }

    private static function normalize(string $url): string
    {
        return rtrim(strtolower($url), '/');
    }
}
