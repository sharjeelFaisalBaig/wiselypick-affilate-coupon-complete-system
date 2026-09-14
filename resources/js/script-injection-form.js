// Shows/hides the page-type checkboxes or store multiselect depending
// on the chosen target_type for a script injection.

function syncTargetType(form) {
    const select = form.querySelector('[data-target-type]');
    const pagesWrapper = form.querySelector('[data-pages-wrapper]');
    const storesWrapper = form.querySelector('[data-stores-wrapper]');
    if (!select) return;

    const sync = () => {
        pagesWrapper?.classList.toggle('hidden', select.value !== 'specific_pages');
        storesWrapper?.classList.toggle('hidden', select.value !== 'specific_stores');
    };

    select.addEventListener('change', sync);
    sync();
}

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-script-injection-form]').forEach(syncTargetType);
});
