@extends('admin.layouts.app')

@section('title', 'Blogs')

@section('content')
    <div class="mb-4 flex items-center justify-between">
        <p class="text-sm text-gray-500">Manage blog posts for this region. Drag the handle to reorder.</p>
        <a href="{{ route('admin.blogs.create') }}" class="rounded-md bg-emerald-500 px-4 py-2 text-sm font-medium text-white shadow-sm hover:-translate-y-0.5 hover:bg-emerald-600 hover:shadow-md active:translate-y-0">
            + Add Blog Post
        </a>
    </div>

    <div class="overflow-visible rounded-xl border border-gray-200 bg-white shadow-sm" data-reorder-loading-target>
        <table class="w-full text-left text-sm">
            <thead class="border-b border-gray-200 bg-gray-50 text-xs uppercase text-gray-500">
                <tr>
                    <th class="w-8 px-2 py-3"></th>
                    <th class="px-4 py-3">Title</th>
                    <th class="px-4 py-3">Category</th>
                    <th class="px-4 py-3">Published</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody data-sortable data-sortable-url="{{ route('admin.blogs.reorder') }}" class="divide-y divide-gray-100">
                @forelse ($blogs as $blog)
                    <tr data-sort-id="{{ $blog->id }}" class="cursor-move hover:bg-gray-50">
                        <td class="px-2 py-3 text-center text-gray-300" aria-hidden="true">⠿</td>
                        <td class="px-4 py-3 font-medium text-gray-900">{{ $blog->title }}</td>
                        <td class="px-4 py-3 text-gray-500">{{ $blog->blogCategory?->name ?? '—' }}</td>
                        <td class="px-4 py-3 text-gray-500">{{ optional($blog->published_at)->format('M j, Y') ?? '—' }}</td>
                        <td class="px-4 py-3">
                            @if ($blog->is_published)
                                <span class="rounded-full bg-emerald-50 px-2 py-1 text-xs font-medium text-emerald-700">Published</span>
                            @else
                                <span class="rounded-full bg-gray-100 px-2 py-1 text-xs font-medium text-gray-500">Draft</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('admin.blogs.edit', $blog) }}" class="font-medium text-emerald-600 hover:text-emerald-700" data-no-ajax>Edit</a>
                            <form action="{{ route('admin.blogs.destroy', $blog) }}" method="POST" class="ml-3 inline"
                                  onsubmit="return confirm('Delete this blog post?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="font-medium text-red-600 hover:text-red-700">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-6 text-center text-gray-400">No blog posts yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
