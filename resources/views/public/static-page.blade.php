@extends('public.layouts.app')

@section('content')
    <div class="mx-auto max-w-3xl px-4 py-10 sm:px-6 lg:px-8">
        <h1 class="text-2xl font-bold text-gray-900">{{ $page->title }}</h1>
        <div class="prose prose-emerald mt-6 max-w-none">
            {!! $page->content !!}
        </div>
    </div>
@endsection
