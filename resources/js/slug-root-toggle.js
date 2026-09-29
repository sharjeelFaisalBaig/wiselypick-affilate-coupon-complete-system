// Disables a Store/Blog's Slug Prefix <select> while its own "Start Slug
// from Root" checkbox (data-root-toggle, data-disables="#target") is
// checked — the two are mutually exclusive by construction (see
// stores/form.blade.php, blogs/form.blade.php); the server also nulls out
// the prefix id regardless of what's submitted, so this is UX only.
function syncSlugRootToggle(checkbox) {
    const targetSelector = checkbox.dataset.disables;
    const select = targetSelector ? document.querySelector(targetSelector) : null;
    if (!select) return;

    const sync = () => {
        const disabled = checkbox.checked;
        // Select2 wraps the native <select> and reflects its `disabled`
        // property automatically once set through jQuery — a plain
        // `select.disabled = ...` leaves the Select2 UI looking enabled
        // even though the underlying field correctly stops submitting.
        if (window.jQuery) {
            window.jQuery(select).prop('disabled', disabled);
        } else {
            select.disabled = disabled;
        }
    };

    checkbox.addEventListener('change', sync);
    sync();
}

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-root-toggle]').forEach(syncSlugRootToggle);
});
