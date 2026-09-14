{{--
    "Top Categories" icon grid — homepage section content_type "categories".
    Expects $categories (Category collection) and $region.
--}}
<div data-reveal-group class="grid grid-cols-2 gap-4 sm:grid-cols-3">
    @foreach ($categories as $category)
        <a data-reveal href="{{ route('public.category', [$region->code, $category->slug]) }}"
           class="card-lift group flex flex-col items-center gap-2 rounded-xl border border-gray-200 bg-white p-5 text-center shadow-sm hover:border-transparent">
            <span class="flex h-14 w-14 shrink-0 items-center justify-center overflow-hidden rounded-full bg-emerald-50 text-emerald-600 transition-transform duration-300 group-hover:scale-110">
                @if ($category->icon_path)
                    <img src="{{ Storage::url($category->icon_path) }}" alt="{{ $category->name }}" width="32" height="32" loading="lazy" class="h-8 w-8 object-contain">
                @else
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="h-7 w-7">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z" />
                    </svg>
                @endif
            </span>
            <p class="font-medium text-gray-900 group-hover:text-emerald-700">{{ $category->name }}</p>
            <p class="text-xs text-gray-400">Coupons</p>
        </a>
    @endforeach
</div>
