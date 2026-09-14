{{-- Blade's compiler treats any raw "<? ... ?>"-shaped text as an existing
     PHP block and passes it through untouched, even inside a quoted string
     — so the literal "<?" / "?>" pairs below are deliberately split with
     concatenation to keep them out of the compiled view's raw text. --}}
{!! '<' . '?xml version="1.0" encoding="UTF-8"' . '?' . '>' !!}
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
@foreach ($urls as $url)
    <url>
        <loc>{{ $url['loc'] }}</loc>
        <priority>{{ $url['priority'] }}</priority>
    </url>
@endforeach
</urlset>
