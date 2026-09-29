// Shows the correct picker block (coupons / deals / stores) based on the
// chosen content type. Item selection itself is handled by the modal
// picker in homepage-section-picker.js.

function syncHomepageSectionForm(form) {
    const typeInputs = Array.from(form.querySelectorAll('[data-content-type]'));
    const wrappers = {
        coupon: form.querySelector('[data-picker="coupon"]'),
        deal: form.querySelector('[data-picker="deal"]'),
        mixed: form.querySelector('[data-picker="mixed"]'),
        store: form.querySelector('[data-picker="store"]'),
        ranked: form.querySelector('[data-picker="ranked"]'),
        categories: form.querySelector('[data-picker="categories"]'),
    };
    if (!typeInputs.length) return;

    const sync = () => {
        const selected = typeInputs.find((el) => el.checked)?.value;
        Object.entries(wrappers).forEach(([key, el]) => {
            if (!el) return;
            const isActive = key === selected;
            el.classList.toggle('hidden', !isActive);
            // Inactive pickers share the same field name (offer_ids[]) as the
            // active one, so their leftover hidden inputs must be disabled —
            // disabled fields are excluded from form submission — or they'd
            // pad the submitted array past the max:5 validation limit.
            el.querySelectorAll('input').forEach((input) => {
                input.disabled = !isActive;
            });
        });
    };

    typeInputs.forEach((el) => el.addEventListener('change', sync));
    sync();
}

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-homepage-section-form]').forEach(syncHomepageSectionForm);
});
