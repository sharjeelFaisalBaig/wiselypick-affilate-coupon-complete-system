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

    // Native HTML5 DnD only fires 'drop' on a target if some 'dragover'
    // listener up the tree called preventDefault() for that exact pointer
    // position. Each row's own dragover handler covers the area directly
    // over rows, but the gaps around/below them (short tables, padding,
    // the space below the last row) were uncovered — dropping there
    // silently cancelled the whole reorder with no feedback, reading as
    // "sorting doesn't work" for drops that didn't land exactly on a row.
    container.addEventListener('dragover', (event) => event.preventDefault());

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

// Exposed so ajax-filters.js can re-run it scoped to just-swapped-in content
// — a [data-sortable] container injected by an AJAX filter/search/pagination
// swap otherwise never gets its drag handlers attached at all (found via a
// real repro: reordering silently stopped working on the admin Coupons
// listing after using its search box, and stayed broken — even navigating
// back from Add/Edit — until a hard reload re-ran DOMContentLoaded). Same
// gap, and same fix shape, as scroll-reveal.js's window.initScrollReveal.
function initDragSort(root = document) {
    root.querySelectorAll('[data-sortable]').forEach(initSortable);
}

window.initDragSort = initDragSort;

document.addEventListener('DOMContentLoaded', () => initDragSort(document));
