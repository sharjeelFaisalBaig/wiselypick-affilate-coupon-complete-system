@extends('admin.layouts.app')

@section('title', 'Users')

@section('content')
    <div class="mb-4 flex items-center justify-between">
        <p class="text-sm text-gray-500">Superadmins can manage everything, including other users. Managers see everything except this module.</p>
        <a href="{{ route('admin.users.create') }}" class="rounded-md bg-emerald-500 px-4 py-2 text-sm font-medium text-white shadow-sm hover:-translate-y-0.5 hover:bg-emerald-600 hover:shadow-md active:translate-y-0">
            + Add User
        </a>
    </div>

    <div class="overflow-x-auto rounded-xl border border-gray-200 bg-white shadow-sm">
        <table class="w-full text-left text-sm">
            <thead class="border-b border-gray-200 bg-gray-50 text-xs uppercase text-gray-500">
                <tr>
                    <th class="px-4 py-3">Name</th>
                    <th class="px-4 py-3">Email</th>
                    <th class="px-4 py-3">Role</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($users as $user)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 font-medium text-gray-900">
                            {{ $user->name }}
                            @if ($user->id === auth()->id())
                                <span class="ml-1 rounded bg-sky-50 px-1.5 py-0.5 text-xs font-normal text-sky-700">You</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-gray-500">{{ $user->email }}</td>
                        <td class="px-4 py-3 text-gray-500">{{ ucfirst($user->role) }}</td>
                        <td class="px-4 py-3">
                            @if ($user->is_active)
                                <span class="rounded-full bg-emerald-50 px-2 py-1 text-xs font-medium text-emerald-700">Active</span>
                            @else
                                <span class="rounded-full bg-gray-100 px-2 py-1 text-xs font-medium text-gray-500">Suspended</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('admin.users.edit', $user) }}" class="font-medium text-emerald-600 hover:text-emerald-700">Edit</a>
                            @unless ($user->id === auth()->id())
                                @if ($user->is_active)
                                    <form action="{{ route('admin.users.suspend', $user) }}" method="POST" class="ml-3 inline">
                                        @csrf
                                        <button type="submit" class="font-medium text-amber-600 hover:text-amber-700">Suspend</button>
                                    </form>
                                @else
                                    <form action="{{ route('admin.users.reactivate', $user) }}" method="POST" class="ml-3 inline">
                                        @csrf
                                        <button type="submit" class="font-medium text-emerald-600 hover:text-emerald-700">Re-enable</button>
                                    </form>
                                @endif
                                <form action="{{ route('admin.users.destroy', $user) }}" method="POST" class="ml-3 inline"
                                      onsubmit="return confirm('Delete this user?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="font-medium text-red-600 hover:text-red-700">Delete</button>
                                </form>
                            @endunless
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-6 text-center text-gray-400">No users yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
