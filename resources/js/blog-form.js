// Shows/hides the manual "Related Blogs" picker based on the
// "Related Blogs Auto-linking" checkbox — the picker is only meaningful
// when auto-linking is off.
document.addEventListener('DOMContentLoaded', () => {
    const toggle = document.querySelector('[data-auto-link-toggle]');
    const picker = document.querySelector('[data-related-blogs-picker]');
    if (!toggle || !picker) return;

    toggle.addEventListener('change', () => {
        picker.classList.toggle('hidden', toggle.checked);
    });
});
