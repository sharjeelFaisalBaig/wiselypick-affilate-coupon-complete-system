/**
 * Live client-side row filter: typing into a [data-row-filter] input hides
 * <tr> rows in its own [data-tab-panel] ancestor whose text doesn't match,
 * so filtering one tab's table never touches another tab's rows (each tab
 * panel is its own DOM subtree).
 */
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-row-filter]').forEach((input) => {
        const scope = input.closest('[data-tab-panel]') || document;
        const rows = () => scope.querySelectorAll('tbody tr[data-sort-id]');

        input.addEventListener('input', () => {
            const q = input.value.trim().toLowerCase();
            rows().forEach((row) => {
                row.style.display = !q || row.textContent.toLowerCase().includes(q) ? '' : 'none';
            });
        });
    });
});
