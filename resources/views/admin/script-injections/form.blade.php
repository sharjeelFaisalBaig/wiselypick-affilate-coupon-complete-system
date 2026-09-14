@extends('admin.layouts.app')

@section('title', $injection->exists ? 'Edit Script Injection' : 'Add Script Injection')

@section('content')
    <div class="max-w-2xl rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
        <form method="POST"
              action="{{ $injection->exists ? route('admin.script-injections.update', $injection) : route('admin.script-injections.store') }}"
              data-script-injection-form class="space-y-5">
            @csrf
            @if ($injection->exists) @method('PUT') @endif

            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">Name</label>
                <input type="text" name="name" value="{{ old('name', $injection->name) }}" required placeholder="e.g. Amazon Associates Tag"
                       class="block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">Placement</label>
                <select name="placement" required data-select2-enable class="block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                    <option value="head_start" @selected(old('placement', $injection->placement) === 'head_start')>Start of &lt;head&gt;</option>
                    <option value="head_end" @selected(old('placement', $injection->placement) === 'head_end')>End of &lt;head&gt;</option>
                    <option value="body_start" @selected(old('placement', $injection->placement) === 'body_start')>Start of &lt;body&gt;</option>
                    <option value="body_end" @selected(old('placement', $injection->placement) === 'body_end')>End of &lt;body&gt;</option>
                </select>
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">Script Content (raw HTML/JS)</label>
                <textarea name="script_content" rows="6" required
                          class="block w-full rounded-md border-gray-300 font-mono text-xs shadow-sm focus:border-emerald-500 focus:ring-emerald-500">{{ old('script_content', $injection->script_content) }}</textarea>
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">Target</label>
                <select name="target_type" data-target-type required class="block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                    <option value="all_pages" @selected(old('target_type', $injection->target_type) === 'all_pages')>All Pages</option>
                    <option value="specific_pages" @selected(old('target_type', $injection->target_type) === 'specific_pages')>Specific Page Types</option>
                    <option value="specific_stores" @selected(old('target_type', $injection->target_type) === 'specific_stores')>Specific Stores</option>
                </select>
            </div>

            <div data-pages-wrapper class="hidden rounded-md border border-gray-200 p-4">
                <p class="mb-2 text-xs font-medium text-gray-500">Page Types</p>
                <div class="grid grid-cols-2 gap-2">
                    @foreach ($pageTypes as $value => $label)
                        <label class="flex items-center gap-2 text-sm">
                            <input type="checkbox" name="page_types[]" value="{{ $value }}" @checked(in_array($value, old('page_types', $selectedPages)))
                                   class="rounded border-gray-300 text-emerald-500 focus:ring-emerald-500">
                            {{ $label }}
                        </label>
                    @endforeach
                </div>
            </div>

            <div data-stores-wrapper class="hidden rounded-md border border-gray-200 p-4">
                <p class="mb-2 text-xs font-medium text-gray-500">Stores</p>
                <select name="store_ids[]" multiple data-select2-enable data-placeholder="Select stores..." class="block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                    @foreach ($stores as $s)
                        <option value="{{ $s->id }}" @selected(in_array($s->id, old('store_ids', $selectedStores)))>{{ $s->name }}</option>
                    @endforeach
                </select>
            </div>

            <label class="flex items-center gap-2">
                <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $injection->id ? $injection->is_active : true))
                       class="rounded border-gray-300 text-emerald-500 focus:ring-emerald-500">
                <span class="text-sm text-gray-700">Active</span>
            </label>

            <div class="flex gap-3">
                <button type="submit" class="rounded-md bg-emerald-500 px-4 py-2 text-sm font-medium text-white shadow-sm hover:-translate-y-0.5 hover:bg-emerald-600 hover:shadow-md active:translate-y-0">
                    {{ $injection->exists ? 'Save Changes' : 'Create' }}
                </button>
                <a href="{{ route('admin.script-injections.index') }}" class="rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:-translate-y-0.5 hover:border-gray-400 hover:bg-gray-50 hover:shadow-sm active:translate-y-0">
                    Cancel
                </a>
            </div>
        </form>
    </div>
@endsection
