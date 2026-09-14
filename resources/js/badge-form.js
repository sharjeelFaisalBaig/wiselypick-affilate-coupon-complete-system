// Shows/hides the custom color picker based on the "Use a custom color" checkbox.
document.addEventListener('DOMContentLoaded', () => {
    const toggle = document.querySelector('[data-toggle-color]');
    const field = document.querySelector('[data-color-field]');
    if (!toggle || !field) return;

    toggle.addEventListener('change', () => {
        field.style.display = toggle.checked ? '' : 'none';
    });
});
