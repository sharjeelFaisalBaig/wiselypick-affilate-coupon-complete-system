<!DOCTYPE html>
<html lang="en" class="h-full bg-gray-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Login · Coupons Platform</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex h-full min-h-screen items-center justify-center bg-gray-50">
    <div class="w-full max-w-sm rounded-xl border border-gray-200 bg-white p-8 shadow-sm">
        <div class="mb-6 flex items-center gap-2">
            <span class="flex h-8 w-8 items-center justify-center rounded-full bg-emerald-500 text-white">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="h-4 w-4"><path d="M13 2 3 14h7l-1 8 10-12h-7l1-8z"/></svg>
            </span>
            <span class="text-lg font-bold">Coupons<span class="text-emerald-500">CMS</span></span>
        </div>

        <h1 class="mb-6 text-xl font-semibold">Admin Sign In</h1>

        @if ($errors->any())
            <div class="mb-4 rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                <ul class="list-inside list-disc">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('admin.login.attempt') }}" class="space-y-4">
            @csrf

            <div>
                <label for="email" class="mb-1 block text-sm font-medium text-gray-700">Email</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
                       class="block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
            </div>

            <div>
                <label for="password" class="mb-1 block text-sm font-medium text-gray-700">Password</label>
                <input id="password" type="password" name="password" required
                       class="block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
            </div>

            <button type="submit"
                    class="w-full rounded-md bg-emerald-500 px-4 py-2 font-medium text-white hover:bg-emerald-600">
                Sign In
            </button>
        </form>
    </div>
</body>
</html>
