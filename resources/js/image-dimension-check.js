/**
 * Real-time client-side image handling for every `<input type="file">` that
 * accepts images: shows a live thumbnail preview of whatever the admin just
 * picked (works even on "Add" forms with no existing stored image yet), and
 * additionally validates pixel dimensions on inputs carrying
 * data-required-width/data-required-height, showing a pass/fail message
 * plus the uploaded size. The server-side `dimensions:` rule remains the
 * authoritative guard — this is a helpful preview only.
 */
function initImagePreviews() {
    document.querySelectorAll('input[type="file"][accept*="image"]').forEach((input) => {
        const requiredWidth = input.dataset.requiredWidth ? parseInt(input.dataset.requiredWidth, 10) : null;
        const requiredHeight = input.dataset.requiredHeight ? parseInt(input.dataset.requiredHeight, 10) : null;
        const result = input.closest('div')?.querySelector('[data-dimension-check-result]');

        let preview = input.parentElement.querySelector('[data-live-preview]');
        if (!preview) {
            preview = document.createElement('img');
            preview.setAttribute('data-live-preview', '');
            // A field with no existing image yet (a fresh "Add" form) has no
            // element to reuse the shape/class of, so this fixed square is
            // just a reasonable default — data-preview-class overrides it
            // for fields whose image isn't square (e.g. a wide region flag).
            preview.className = input.dataset.previewClass || 'mb-2 hidden h-16 w-16 rounded border border-gray-200 object-contain';
            input.before(preview);
        }

        input.addEventListener('change', () => {
            const file = input.files?.[0];
            if (!file) {
                preview.classList.add('hidden');
                if (result) result.textContent = '';
                return;
            }

            const url = URL.createObjectURL(file);
            preview.src = url;
            preview.classList.remove('hidden');

            if (!requiredWidth || !requiredHeight || !result) return;

            const img = new Image();
            img.onload = () => {
                const { naturalWidth: width, naturalHeight: height } = img;

                if (width === requiredWidth && height === requiredHeight) {
                    result.textContent = `Uploaded: ${width}×${height}px — matches the required size.`;
                    result.className = 'mt-1 text-xs text-emerald-600';
                } else {
                    result.textContent = `Uploaded: ${width}×${height}px — required exactly ${requiredWidth}×${requiredHeight}px.`;
                    result.className = 'mt-1 text-xs text-red-600';
                }
            };
            img.src = url;
        });
    });
}

document.addEventListener('DOMContentLoaded', initImagePreviews);
