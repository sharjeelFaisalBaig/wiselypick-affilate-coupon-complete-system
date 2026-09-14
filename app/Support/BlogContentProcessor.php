<?php

namespace App\Support;

use DOMDocument;
use Illuminate\Support\Str;

class BlogContentProcessor
{
    /**
     * Walk H2/H3 headings in the given HTML, assign an anchor id to each
     * (if missing), and return the (possibly modified) HTML alongside a
     * flat table-of-contents array of ['text' => ..., 'anchor' => ...].
     */
    public static function extractToc(string $html): array
    {
        if (trim($html) === '') {
            return ['content' => $html, 'toc' => []];
        }

        $dom = new DOMDocument();
        libxml_use_internal_errors(true);
        $dom->loadHTML(
            '<?xml encoding="utf-8" ?><div>'.$html.'</div>',
            LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
        );
        libxml_clear_errors();

        $toc = [];
        $usedAnchors = [];

        $xpath = new \DOMXPath($dom);
        foreach ($xpath->query('//h2 | //h3') as $heading) {
            $text = trim($heading->textContent);
            if ($text === '') {
                continue;
            }

            $anchor = $heading->getAttribute('id');
            if ($anchor === '') {
                $anchor = Str::slug($text);
            }

            $original = $anchor;
            $suffix = 2;
            while (in_array($anchor, $usedAnchors, true)) {
                $anchor = "{$original}-{$suffix}";
                $suffix++;
            }
            $usedAnchors[] = $anchor;

            $heading->setAttribute('id', $anchor);
            $toc[] = ['text' => $text, 'anchor' => $anchor, 'level' => $heading->tagName];
        }

        $wrapper = $dom->getElementsByTagName('div')->item(0);
        $innerHtml = '';
        foreach ($wrapper->childNodes as $child) {
            $innerHtml .= $dom->saveHTML($child);
        }

        return ['content' => $innerHtml, 'toc' => $toc];
    }

    public static function estimateReadingTimeMinutes(string $html): int
    {
        $words = str_word_count(strip_tags($html));

        return max(1, (int) round($words / 200));
    }
}
