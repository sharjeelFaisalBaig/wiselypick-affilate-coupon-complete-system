<footer class="relative bg-[var(--color-dark-surface)]">
    <div class="absolute inset-x-0 top-0 h-px bg-gradient-to-r from-transparent via-emerald-500/60 to-transparent"></div>
    <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
        <div class="grid grid-cols-2 gap-8 sm:grid-cols-4">
            <div>
                <h3 class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ $footerAboutMenu?->name ?? 'About Us' }}</h3>
                <ul class="mt-3 space-y-2 text-sm text-slate-300">
                    @foreach (($footerAboutMenu?->items ?? []) as $item)
                        <li><a href="{{ $item->resolvedUrl($region) }}" @if ($item->opensInNewTab()) target="_blank" rel="noopener" @endif class="link-underline hover:text-emerald-400">{{ $item->title }}</a></li>
                    @endforeach
                </ul>
            </div>
            <div>
                <h3 class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ $footerConnectMenu?->name ?? 'Connect' }}</h3>
                <ul class="mt-3 space-y-2 text-sm text-slate-300">
                    @foreach (($footerConnectMenu?->items ?? []) as $item)
                        @php
                            $label = strtolower($item->title);
                            $icon = match (true) {
                                str_contains($label, 'twitter') || str_contains($label, ' x') || $label === 'x' => 'M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z',
                                str_contains($label, 'facebook') => 'M22 12a10 10 0 10-11.563 9.876v-6.988H7.898V12h2.54V9.797c0-2.506 1.492-3.89 3.777-3.89 1.094 0 2.238.195 2.238.195v2.46h-1.26c-1.243 0-1.63.771-1.63 1.562V12h2.773l-.443 2.888h-2.33v6.988A10 10 0 0022 12z',
                                str_contains($label, 'instagram') => 'M12 2c-2.72 0-3.06.012-4.123.06-1.062.05-1.79.218-2.425.465a4.9 4.9 0 00-1.771 1.153A4.9 4.9 0 002.525 5.45c-.247.636-.415 1.363-.465 2.425C2.012 8.94 2 9.28 2 12s.012 3.06.06 4.123c.05 1.062.218 1.79.465 2.425a4.9 4.9 0 001.153 1.771 4.9 4.9 0 001.771 1.153c.636.247 1.363.415 2.425.465C8.94 21.988 9.28 22 12 22s3.06-.012 4.123-.06c1.062-.05 1.79-.218 2.425-.465a4.9 4.9 0 001.771-1.153 4.9 4.9 0 001.153-1.771c.247-.636.415-1.363.465-2.425.048-1.063.06-1.403.06-4.123s-.012-3.06-.06-4.123c-.05-1.062-.218-1.79-.465-2.425a4.9 4.9 0 00-1.153-1.771A4.9 4.9 0 0018.548 2.525c-.636-.247-1.363-.415-2.425-.465C15.06 2.012 14.72 2 12 2zm0 1.802c2.67 0 2.987.01 4.042.059.976.045 1.505.207 1.858.344.467.182.8.399 1.15.748.35.35.566.683.748 1.15.137.353.3.882.344 1.858.048 1.055.058 1.372.058 4.042s-.01 2.987-.058 4.042c-.045.976-.207 1.505-.344 1.858a3.1 3.1 0 01-.748 1.15c-.35.35-.683.566-1.15.748-.353.137-.882.3-1.858.344-1.055.048-1.372.058-4.042.058s-2.987-.01-4.042-.058c-.976-.045-1.505-.207-1.858-.344a3.1 3.1 0 01-1.15-.748 3.1 3.1 0 01-.748-1.15c-.137-.353-.3-.882-.344-1.858C3.812 14.987 3.802 14.67 3.802 12s.01-2.987.058-4.042c.045-.976.207-1.505.344-1.858.182-.467.399-.8.748-1.15.35-.35.683-.566 1.15-.748.353-.137.882-.3 1.858-.344C9.013 3.812 9.33 3.802 12 3.802zm0 3.064a5.134 5.134 0 100 10.268 5.134 5.134 0 000-10.268zm0 8.468a3.334 3.334 0 110-6.668 3.334 3.334 0 010 6.668zm6.538-8.671a1.2 1.2 0 11-2.4 0 1.2 1.2 0 012.4 0z',
                                str_contains($label, 'linkedin') => 'M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.446-2.136 2.94v5.666H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433a2.062 2.062 0 11.001-4.124 2.062 2.062 0 010 4.124zM7.114 20.452H3.558V9h3.556v11.452z',
                                str_contains($label, 'youtube') => 'M23.498 6.186a3.016 3.016 0 00-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 00.502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 002.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 002.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z',
                                str_contains($label, 'pinterest') => 'M12 2C6.477 2 2 6.477 2 12c0 4.237 2.636 7.855 6.356 9.312-.088-.791-.167-2.005.035-2.868.182-.78 1.172-4.97 1.172-4.97s-.299-.6-.299-1.486c0-1.39.806-2.428 1.81-2.428.852 0 1.264.64 1.264 1.408 0 .858-.546 2.14-.828 3.33-.236.995.499 1.807 1.48 1.807 1.778 0 3.144-1.874 3.144-4.58 0-2.393-1.72-4.068-4.177-4.068-2.845 0-4.515 2.135-4.515 4.34 0 .859.331 1.781.744 2.281a.3.3 0 01.069.288c-.076.316-.245.995-.278 1.134-.044.183-.145.222-.334.134-1.249-.581-2.03-2.407-2.03-3.874 0-3.154 2.292-6.052 6.608-6.052 3.469 0 6.165 2.472 6.165 5.775 0 3.444-2.171 6.216-5.185 6.216-1.013 0-1.966-.526-2.292-1.148l-.623 2.378c-.226.869-.835 1.958-1.244 2.622.937.29 1.931.446 2.964.446 5.523 0 10-4.477 10-10S17.523 2 12 2z',
                                default => null,
                            };
                        @endphp
                        <li>
                            <a href="{{ $item->resolvedUrl($region) }}" @if ($item->opensInNewTab()) target="_blank" rel="noopener" @endif
                               class="group inline-flex items-center gap-2 text-slate-300 transition-colors duration-200 hover:text-emerald-400">
                                @if ($icon)
                                    <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-slate-800 text-slate-400 transition-all duration-300 group-hover:-translate-y-0.5 group-hover:bg-emerald-500/15 group-hover:text-emerald-400">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="h-3.5 w-3.5"><path d="{{ $icon }}" /></svg>
                                    </span>
                                @endif
                                <span class="link-underline">{{ $item->title }}</span>
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
            <div class="col-span-2 sm:col-span-1">
                <h3 class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ $footerShopMenu?->name ?? 'Shop Coupons' }}</h3>
                <ul class="mt-3 space-y-2 text-sm text-slate-300">
                    @foreach (($footerShopMenu?->items ?? []) as $item)
                        <li><a href="{{ $item->resolvedUrl($region) }}" @if ($item->opensInNewTab()) target="_blank" rel="noopener" @endif class="link-underline hover:text-emerald-400">{{ $item->title }}</a></li>
                    @endforeach
                </ul>
            </div>
            <div class="col-span-2 text-sm text-slate-400 sm:col-span-1">
                @if ($generalSettings->footer_text)
                    <div class="prose prose-sm prose-invert max-w-none">{!! $generalSettings->footer_text !!}</div>
                @else
                    <p>WisleyPick finds coupon codes, discount sales and promotions for e-commerce stores listed in our <a href="{{ \App\Models\PageSetting::urlFor($region, 'stores') }}" class="link-underline text-emerald-400">store directory</a>.</p>
                @endif
            </div>
        </div>

        <div class="mt-8 border-t border-slate-800 pt-6 text-center text-xs text-slate-500">
            @if ($generalSettings->footer_disclaimer)
                <p><strong class="text-slate-400">Disclaimer:</strong> {{ $generalSettings->footer_disclaimer }}</p>
            @endif
            <p class="mt-2">{{ $generalSettings->rights_text ?: '© 2015 – '.date('Y').' WisleyPick. All rights reserved.' }}</p>
        </div>
    </div>
</footer>
