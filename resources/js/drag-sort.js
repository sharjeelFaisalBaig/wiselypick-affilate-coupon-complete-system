// Native HTML5 drag-and-drop reordering for admin lists.
// Any container with [data-sortable] containing children marked [data-sort-id]
// will be made draggable; on drop it POSTs the new id order to data-sortable-url.

function initSortable(container) {
    const url = container.getAttribute('data-sortable-url');
    let dragged = null;

    container.querySelectorAll('[data-sort-id]').forEach((item) => {
        item.setAttribute('draggable', 'true');

        item.addEventListener('dragstart', () => {
            dragged = item;
            item.classList.add('opacity-40');
        });

        item.addEventListener('dragend', () => {
            item.classList.remove('opacity-40');
        });

        item.addEventListener('dragover', (event) => {
            event.preventDefault();
            const bounding = item.getBoundingClientRect();
            const offset = event.clientY - bounding.top;
            if (offset > bounding.height / 2) {
                item.after(dragged);
            } else {
                item.before(dragged);
            }
        });
    });

    container.addEventListener('drop', async (event) => {
        event.preventDefault();
        if (!url) return;

        const ids = Array.from(container.querySelectorAll('[data-sort-id]')).map((el) =>
            el.getAttribute('data-sort-id')
        );

        // A decent loading cue while the new order saves — the closest
        // [data-reorder-loading-target] ancestor (falling back to the
        // sortable container itself) gets dimmed for the duration.
        const loadingTarget = container.closest('[data-reorder-loading-target]') || container;
        loadingTarget.classList.add('opacity-50', 'pointer-events-none', 'transition-opacity');

        try {
            await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
                body: JSON.stringify({ ids }),
            });
        } finally {
            loadingTarget.classList.remove('opacity-50', 'pointer-events-none');
        }
    });
}

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-sortable]').forEach(initSortable);
});
