{{-- See sitemap.blade.php's comment — the literal "<?"/"?>" pair below is
     deliberately split with concatenation so Blade doesn't treat it as a
     raw PHP block. --}}
{!! '<' . '?xml version="1.0" encoding="UTF-8"' . '?' . '>' !!}
<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
@foreach ($regionUrls as $url)
    <sitemap>
        <loc>{{ $url }}</loc>
    </sitemap>
@endforeach
</sitemapindex>
