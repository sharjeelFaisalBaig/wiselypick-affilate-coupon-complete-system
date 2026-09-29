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
                <input type="file" name="logo" accept="image/*" data-required-width="160" data-required-height="40"
                       class="block text-sm text-gray-600 file:mr-3 file:rounded-md file:border-0 file:bg-gray-100 file:px-3 file:py-2 file:text-sm file:font-medium hover:file:bg-gray-200">
                <p class="mt-1 text-xs text-gray-400">* Required dimensions: exactly 160x40px. JPG, PNG or WEBP, up to 1MB.</p>
                <p data-dimension-check-result class="mt-1 text-xs"></p>
                @error('logo') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">Logo Link — Main Site</label>
                    <select name="logo_link_page" data-select2-enable data-no-clear required
                            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                        @foreach (\App\Http\Controllers\Admin\PageSettingController::PAGES as $key => $label)
                            <option value="{{ $key }}" @selected(old('logo_link_page', $settings->logo_link_page ?: 'home') === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                    <p class="mt-1 text-xs text-gray-400">Where the logo goes when clicked from anywhere outside the Blog section.</p>
                    @error('logo_link_page') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">Logo Link — Blog Site</label>
                    <select name="logo_link_page_blog" data-select2-enable data-no-clear required
                            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                        @foreach (\App\Http\Controllers\Admin\PageSettingController::PAGES as $key => $label)
                            <option value="{{ $key }}" @selected(old('logo_link_page_blog', $settings->logo_link_page_blog ?: 'blogs') === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                    <p class="mt-1 text-xs text-gray-400">Where the logo goes when clicked from the Blog listing/detail pages or Contact Us/Terms/Privacy.</p>
                    @error('logo_link_page_blog') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
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

            <fieldset class="rounded-md border border-gray-200 p-4 space-y-2">
                <legend class="px-1 text-sm font-medium text-gray-700">Contact Us Notification Recipients</legend>
                <p class="text-xs text-gray-400">One email address per line. Every address here is emailed when a visitor submits this region's Contact Us form. Leave blank to disable admin notification emails for this region (the visitor still gets their own confirmation email).</p>
                <textarea name="contact_notification_emails" rows="3" placeholder="support@example.com&#10;sales@example.com"
                          class="block w-full rounded-md border-gray-300 font-mono text-xs shadow-sm focus:border-emerald-500 focus:ring-emerald-500">{{ old('contact_notification_emails', $settings->contact_notification_emails) }}</textarea>
                @error('contact_notification_emails') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </fieldset>

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
