@extends('admin.layouts.app')

@section('title', 'Delete Region')

@section('content')
    <div class="max-w-xl rounded-xl border border-red-200 bg-white p-6 shadow-sm">
        <div class="mb-5 rounded-lg border border-red-300 bg-red-50 p-4">
            <p class="text-sm font-bold text-red-800">This permanently deletes "{{ $region->name }}" and EVERYTHING that belongs to it.</p>
            <p class="mt-2 text-sm text-red-700">That includes every store, coupon and deal, blog post and blog category, category, static page (Terms, Privacy, Contact copy, etc.), homepage section, menu, script injection, affiliate network, store/blog/page slug prefix and suffix, general setting, and uploaded logo/image file owned by this region.</p>
            <p class="mt-2 text-sm font-semibold text-red-800">This cannot be undone. There is no recovery once you confirm.</p>
        </div>

        @error('confirm') <p class="mb-4 rounded-md border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700">{{ $message }}</p> @enderror

        <form method="POST" action="{{ route('admin.regions.destroy', $region) }}" class="space-y-5">
            @csrf
            @method('DELETE')

            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">Your Admin Password</label>
                <input type="password" name="password" required autocomplete="current-password"
                       class="block w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500">
                @error('password') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">Type the region's slug to confirm: <span class="font-mono font-semibold">{{ $region->code }}</span></label>
                <input type="text" name="confirm_code" required autocomplete="off"
                       class="block w-full rounded-md border-gray-300 font-mono shadow-sm focus:border-red-500 focus:ring-red-500">
                @error('confirm_code') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">Type the region's exact name to confirm: <span class="font-semibold">{{ $region->name }}</span></label>
                <input type="text" name="confirm_name" required autocomplete="off"
                       class="block w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500">
                @error('confirm_name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <div class="flex gap-3">
                <button type="submit" class="rounded-md bg-red-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:-translate-y-0.5 hover:bg-red-700 hover:shadow-md active:translate-y-0">
                    Permanently Delete "{{ $region->name }}"
                </button>
                <a href="{{ route('admin.regions.index') }}" class="rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:-translate-y-0.5 hover:border-gray-400 hover:bg-gray-50 hover:shadow-sm active:translate-y-0">
                    Cancel
                </a>
            </div>
        </form>
    </div>
@endsection
