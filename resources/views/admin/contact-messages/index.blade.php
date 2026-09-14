@extends('admin.layouts.app')

@section('title', 'Contact Messages')

@section('content')
    <div class="space-y-3">
        @forelse ($messages as $message)
            <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm {{ $message->is_read ? '' : 'border-l-4 border-l-emerald-500' }}">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="font-medium text-gray-900">{{ $message->name }} <span class="font-normal text-gray-400">&lt;{{ $message->email }}&gt;</span></p>
                        <p class="mt-1 text-xs uppercase tracking-wide text-gray-400">{{ str_replace('_', ' ', $message->category) }} · {{ $message->created_at->diffForHumans() }}</p>
                        <p class="mt-2 text-sm text-gray-700">{{ $message->message }}</p>
                    </div>
                    <div class="flex shrink-0 flex-col items-end gap-2">
                        <form action="{{ route('admin.contact-messages.toggle-read', $message) }}" method="POST">
                            @csrf
                            <button type="submit" class="rounded-full px-2 py-1 text-xs font-medium {{ $message->is_read ? 'bg-gray-100 text-gray-500' : 'bg-emerald-50 text-emerald-700' }}">
                                {{ $message->is_read ? 'Read' : 'Mark as read' }}
                            </button>
                        </form>
                        <form action="{{ route('admin.contact-messages.destroy', $message) }}" method="POST" onsubmit="return confirm('Delete this message?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-xs font-medium text-red-600 hover:text-red-700">Delete</button>
                        </form>
                    </div>
                </div>
            </div>
        @empty
            <div class="rounded-xl border border-dashed border-gray-300 bg-white p-10 text-center text-gray-400">
                No contact messages yet.
            </div>
        @endforelse
    </div>

    <div class="mt-4">{{ $messages->links() }}</div>
@endsection
