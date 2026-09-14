@extends('admin.layouts.app')

@section('title', $network->exists ? 'Edit Affiliate Network' : 'Add Affiliate Network')

@section('content')
    <div class="max-w-xl rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
        <form method="POST"
              action="{{ $network->exists ? route('admin.affiliate-networks.update', $network) : route('admin.affiliate-networks.store') }}"
              class="space-y-5">
            @csrf
            @if ($network->exists) @method('PUT') @endif

            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">Network Name</label>
                <input type="text" name="network_name" value="{{ old('network_name', $network->network_name) }}" required placeholder="e.g. Amazon Associates"
                       class="block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">Script</label>
                <textarea name="script" rows="5" placeholder="&lt;script&gt;...&lt;/script&gt;"
                          class="block w-full rounded-md border-gray-300 font-mono text-xs shadow-sm focus:border-emerald-500 focus:ring-emerald-500">{{ old('script', $network->script) }}</textarea>
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">Placement Position</label>
                <select name="placement" data-select2-enable class="block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                    @foreach ([
                        'head_start' => 'Start of <head>',
                        'head_end' => 'End of <head>',
                        'body_start' => 'Start of <body>',
                        'body_end' => 'End of <body>',
                    ] as $value => $label)
                        <option value="{{ $value }}" @selected(old('placement', $network->placement ?? 'head_end') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <label class="flex items-center gap-2">
                <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $network->is_active))
                       class="rounded border-gray-300 text-emerald-500 focus:ring-emerald-500">
                <span class="text-sm text-gray-700">Connected / Active</span>
            </label>
            <p class="-mt-3 text-xs text-gray-400">Informational only, for the admin's own tracking — has no effect on whether the script above actually renders.</p>

            <div class="flex gap-3">
                <button type="submit" class="rounded-md bg-emerald-500 px-4 py-2 text-sm font-medium text-white shadow-sm hover:-translate-y-0.5 hover:bg-emerald-600 hover:shadow-md active:translate-y-0">
                    {{ $network->exists ? 'Save Changes' : 'Create' }}
                </button>
                <a href="{{ route('admin.affiliate-networks.index') }}" class="rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:-translate-y-0.5 hover:border-gray-400 hover:bg-gray-50 hover:shadow-sm active:translate-y-0">
                    Cancel
                </a>
            </div>
        </form>
    </div>
@endsection
