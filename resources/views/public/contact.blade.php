@extends('public.layouts.app')

@section('content')
    <div class="mx-auto max-w-2xl px-4 py-10 sm:px-6 lg:px-8">
        <h1 class="text-2xl font-bold text-gray-900">{{ $page->title }}</h1>
        <div class="prose prose-emerald mt-4 max-w-none">{!! $page->content !!}</div>

        @if (session('status'))
            <div class="mt-6 rounded-md border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                {{ session('status') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="mt-6 rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                <ul class="list-inside list-disc">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('public.contact.submit', $region->code) }}" class="mt-6 space-y-5">
            @csrf

            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">Your email</label>
                <input type="email" name="email" value="{{ old('email') }}" required
                       class="block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">Your name</label>
                <input type="text" name="name" value="{{ old('name') }}" required
                       class="block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
            </div>

            <div>
                <p class="mb-2 block text-sm font-medium text-gray-700">I have a question about:</p>
                <div class="space-y-2">
                    @foreach ($agendas as $agenda)
                        <label class="flex items-start gap-2">
                            <input type="radio" name="category" value="{{ $agenda->value }}" @checked(old('category') === $agenda->value || (!old('category') && $loop->first)) required
                                   class="mt-1 border-gray-300 text-emerald-500 focus:ring-emerald-500">
                            <span class="text-sm text-gray-700"><strong>{{ $agenda->label }}</strong> {{ $agenda->description }}</span>
                        </label>
                    @endforeach
                </div>
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">Your question (provide as much detail as possible)</label>
                <textarea name="message" rows="5" required
                          class="block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">{{ old('message') }}</textarea>
            </div>

            <button type="submit" class="rounded-md bg-emerald-500 px-6 py-2 text-sm font-semibold text-white hover:bg-emerald-600">
                Send Message
            </button>
        </form>
    </div>
@endsection
