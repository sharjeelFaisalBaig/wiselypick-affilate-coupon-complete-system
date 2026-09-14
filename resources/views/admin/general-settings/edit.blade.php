@extends('admin.layouts.app')

@section('title', 'General Settings')

@push('head')
    @vite(['resources/js/blog-editor.js', 'resources/js/image-dimension-check.js'])
@endpush

@section('content')
    <div class="max-w-2xl rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
        <form method="POST" action="{{ route('admin.general-settings.update') }}" enctype="multipart/form-data" class="space-y-5">
            @csrf
            @method('PUT')

            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">Header / Footer Logo</label>
                @if ($settings->logo_path)
                    <img data-live-preview src="{{ Storage::url($settings->logo_path) }}" alt="" width="120" height="32" class="mb-2 h-8 w-auto object-contain">
                @endif
                <input type="file" name="logo" accept="image/*"
                       class="block text-sm text-gray-600 file:mr-3 file:rounded-md file:border-0 file:bg-gray-100 file:px-3 file:py-2 file:text-sm file:font-medium hover:file:bg-gray-200">
                <p class="mt-1 text-xs text-gray-400">* Optimal size: 160x40px, transparent PNG/SVG.</p>
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">Footer Textual Content</label>
                <div data-quill-editor="footer_text" style="min-height: 140px;" class="bg-white"></div>
                <textarea name="footer_text" data-content-field="footer_text" class="hidden">{{ old('footer_text', $settings->footer_text) }}</textarea>
                <p class="mt-1 text-xs text-gray-400">Shown in the footer's fourth column (site description / how-it-works copy).</p>
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">Footer Disclaimer</label>
                <textarea name="footer_disclaimer" rows="3"
                          class="block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500">{{ old('footer_disclaimer', $settings->footer_disclaimer) }}</textarea>
                <p class="mt-1 text-xs text-gray-400">Optimal length: ~400 characters to avoid wrapping past 3 lines.</p>
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">Store Page Disclaimer</label>
                <textarea name="store_page_disclaimer" rows="3"
                          class="block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500">{{ old('store_page_disclaimer', $settings->store_page_disclaimer) }}</textarea>
                <p class="mt-1 text-xs text-gray-400">Shown just under the heading on every store detail page.</p>
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">All Rights Reserved Text</label>
                <input type="text" name="rights_text" value="{{ old('rights_text', $settings->rights_text) }}" maxlength="255"
                       class="block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                <p class="mt-1 text-xs text-gray-400">e.g. "© 2015 – 2026 WisleyPick. All rights reserved."</p>
            </div>

            <div>
                <label class="mb-2 block text-sm font-medium text-gray-700">Brand Theme Colors</label>
                <p class="mb-3 text-xs text-gray-400">Drives every coupon/primary accent, deal accent, and dark surface (header/hero/footer) color across the public site — deliberately kept to these 3 colors, matching the site's design system.</p>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                    <div>
                        <span class="mb-1 block text-xs font-medium text-gray-600">Primary / Coupon Color</span>
                        <div class="flex items-center gap-2">
                            <input type="color" name="primary_color" value="{{ old('primary_color', $settings->primary_color ?: '#10b981') }}"
                                   class="h-10 w-14 shrink-0 cursor-pointer rounded-md border border-gray-300 p-1">
                            <span class="font-mono text-xs text-gray-500" data-color-hex-display>{{ old('primary_color', $settings->primary_color ?: '#10b981') }}</span>
                        </div>
                    </div>
                    <div>
                        <span class="mb-1 block text-xs font-medium text-gray-600">Deal Color</span>
                        <div class="flex items-center gap-2">
                            <input type="color" name="deal_color" value="{{ old('deal_color', $settings->deal_color ?: '#ff7900') }}"
                                   class="h-10 w-14 shrink-0 cursor-pointer rounded-md border border-gray-300 p-1">
                            <span class="font-mono text-xs text-gray-500" data-color-hex-display>{{ old('deal_color', $settings->deal_color ?: '#ff7900') }}</span>
                        </div>
                    </div>
                    <div>
                        <span class="mb-1 block text-xs font-medium text-gray-600">Dark Surface Color</span>
                        <div class="flex items-center gap-2">
                            <input type="color" name="dark_surface_color" value="{{ old('dark_surface_color', $settings->dark_surface_color ?: '#0f172a') }}"
                                   class="h-10 w-14 shrink-0 cursor-pointer rounded-md border border-gray-300 p-1">
                            <span class="font-mono text-xs text-gray-500" data-color-hex-display>{{ old('dark_surface_color', $settings->dark_surface_color ?: '#0f172a') }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex gap-3">
                <button type="submit" class="rounded-md bg-emerald-500 px-4 py-2 text-sm font-medium text-white shadow-sm hover:-translate-y-0.5 hover:bg-emerald-600 hover:shadow-md active:translate-y-0">
                    Save Changes
                </button>
            </div>
        </form>
    </div>

    <script>
        document.querySelectorAll('input[type="color"]').forEach((input) => {
            const display = input.parentElement.querySelector('[data-color-hex-display]');
            input.addEventListener('input', () => {
                if (display) display.textContent = input.value;
            });
        });
    </script>
@endsection
