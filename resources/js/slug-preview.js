/**
 * Live client-side slug preview: mirrors the server's own slugify fallback
 * (lowercase, non-alphanumeric -> "-", collapsed/trimmed dashes) into a
 * [data-slug-preview] field's placeholder as the [data-slug-source] name
 * field is typed — only while the admin hasn't manually typed a slug
 * themselves. The server remains authoritative on submit (including
 * uniqueness suffixing); this is a preview only.
 */
function slugify(value) {
    return value
        .toLowerCase()
        .trim()
        .replace(/[^a-z0-9]+/g, '-')
        .replace(/^-+|-+$/g, '');
}

function initSlugPreview() {
    document.querySelectorAll('[data-slug-source]').forEach((source) => {
        const preview = source.closest('form')?.querySelector('[data-slug-preview]');
        if (!preview) return;

        let userEditedSlug = preview.value.trim().length > 0;

        preview.addEventListener('input', () => {
            userEditedSlug = preview.value.trim().length > 0;
        });

        source.addEventListener('input', () => {
            if (userEditedSlug) return;
            preview.placeholder = slugify(source.value) || 'auto-generated from name if left blank';
        });
    });
}

document.addEventListener('DOMContentLoaded', initSlugPreview);
