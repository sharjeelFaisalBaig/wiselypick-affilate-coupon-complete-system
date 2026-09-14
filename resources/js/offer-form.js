// Toggles the coupon "code" field based on offer_type, for every
// offer form on the page (the create page has one; the slide-over
// drawers on the index page can have many). Also enforces the
// max-2 Coupon Features checkbox limit.

function syncOfferForm(form) {
    const typeSelect = form.querySelector('[data-offer-type]');
    const codeWrapper = form.querySelector('[data-code-wrapper]');

    if (typeSelect && codeWrapper) {
        const sync = () => {
            codeWrapper.classList.toggle('hidden', typeSelect.value !== 'coupon');
        };
        typeSelect.addEventListener('change', sync);
        sync();
    }

    const badgeGroup = form.querySelector('[data-max-badges]');
    if (badgeGroup) {
        const max = Number(badgeGroup.getAttribute('data-max-badges'));
        const checkboxes = Array.from(badgeGroup.querySelectorAll('[data-badge-checkbox]'));

        const syncBadgeLimit = () => {
            const checkedCount = checkboxes.filter((c) => c.checked).length;
            checkboxes.forEach((c) => {
                if (!c.checked) c.disabled = checkedCount >= max;
            });
        };

        checkboxes.forEach((checkbox) => checkbox.addEventListener('change', syncBadgeLimit));
        syncBadgeLimit();
    }
}

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-offer-form]').forEach(syncOfferForm);
});
