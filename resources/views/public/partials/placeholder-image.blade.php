{{--
    Generic "image unavailable" placeholder — used anywhere the reference
    design shows a thumbnail/logo but no real asset has been uploaded yet.
    Expects: $class (sizing/rounding classes), optional $iconClass.
--}}
<div class="{{ $class ?? 'h-12 w-12' }} flex shrink-0 items-center justify-center rounded-md border border-gray-200 bg-gray-50 text-gray-300">
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="{{ $iconClass ?? 'h-1/2 w-1/2' }}">
        <path fill-rule="evenodd" d="M3 5.25A2.25 2.25 0 015.25 3h13.5A2.25 2.25 0 0121 5.25v13.5A2.25 2.25 0 0118.75 21H5.25A2.25 2.25 0 013 18.75V5.25zm4.5 3a1.5 1.5 0 100 3 1.5 1.5 0 000-3zm-1.5 9.75l3.086-3.086a1.5 1.5 0 012.122 0l.879.879 3.293-3.293a1.5 1.5 0 012.122 0l2.498 2.498V18.75a.75.75 0 01-.75.75H6.75a.75.75 0 01-.75-.75v-.75z" clip-rule="evenodd" />
    </svg>
</div>
