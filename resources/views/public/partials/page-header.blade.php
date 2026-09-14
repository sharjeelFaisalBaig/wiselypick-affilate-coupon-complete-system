{{-- Shared dark-surface header band for listing pages. Expects $heading, optional $subheading. --}}
<div class="relative overflow-hidden bg-gradient-to-r from-[var(--color-dark-surface)] to-[var(--color-dark-surface-deep)]">
    <div class="pointer-events-none absolute -right-16 -top-16 h-56 w-56 rounded-full bg-emerald-500/10 blur-3xl"></div>
    <div class="relative mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
        <h1 class="text-2xl font-bold text-white sm:text-3xl">{{ $heading }}</h1>
        @if ($subheading ?? null)
            <p class="mt-2 text-sm text-gray-300">{{ $subheading }}</p>
        @endif
    </div>
</div>
