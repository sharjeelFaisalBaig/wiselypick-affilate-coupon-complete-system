// Modal-based multi-select picker for Homepage Sections: the summary field
// in the main form stays readonly (chips + hidden inputs only); all actual
// filtering/selecting happens in the modal, which stages changes locally
// and only commits them back to the summary field when "Save" is clicked.

function debounce(fn, wait) {
    let timer;
    return (...args) => {
        clearTimeout(timer);
        timer = setTimeout(() => fn(...args), wait);
    };
}

function initPicker(summaryEl) {
    const type = summaryEl.dataset.type;
    const modal = document.querySelector(`[data-picker-modal="${type}"]`);
    if (!modal) return;

    const max = Number(summaryEl.dataset.max);
    const inputName = summaryEl.dataset.inputName;
    const resultsUrl = modal.dataset.resultsUrl;

    const chipsBox = summaryEl.querySelector('[data-picker-chips]');
    const inputsBox = summaryEl.querySelector('[data-picker-inputs]');
    const openBtn = summaryEl.querySelector('[data-picker-open]');

    const resultsBox = modal.querySelector('[data-picker-results]');
    const stagedList = modal.querySelector('[data-picker-staged]');
    const countEl = modal.querySelector('[data-picker-count]');
    const filterFields = Array.from(modal.querySelectorAll('[data-filter]'));

    // committed = what's actually saved into the form right now.
    let committed = new Map();
    chipsBox.querySelectorAll('[data-chip]').forEach((chip) => {
        committed.set(String(chip.dataset.id), chip.textContent.trim());
    });

    // staged = the modal's working copy while it's open.
    let staged = new Map();

    // Drag-to-reorder for the staged list: Map insertion order doubles as
    // the sort order committed back to the form (renderChips() writes
    // hidden inputs in Map iteration order, which is what the controller's
    // syncSelections() turns into pivot sort_order). Native HTML5 DnD only
    // reorders the DOM live as you drag — this listener pair is what syncs
    // `staged`'s Map order to match the DOM once the drag actually
    // completes (same drop-target-gap fix as drag-sort.js: a container-wide
    // dragover preventDefault so drop fires even between/below rows).
    let draggedRow = null;
    stagedList.addEventListener('dragover', (event) => event.preventDefault());
    stagedList.addEventListener('drop', (event) => {
        event.preventDefault();
        const ids = Array.from(stagedList.querySelectorAll('[data-sort-id]')).map((el) => el.dataset.sortId);
        staged = new Map(ids.map((id) => [id, staged.get(id)]));
        stagedList.querySelectorAll('[data-picker-order]').forEach((el, i) => {
            el.textContent = String(i + 1);
        });
    });

    function renderChips() {
        chipsBox.innerHTML = '';
        if (committed.size === 0) {
            const hint = document.createElement('span');
            hint.dataset.pickerEmptyHint = '';
            hint.className = 'text-sm italic text-gray-400';
            hint.textContent = 'Nothing selected yet';
            chipsBox.appendChild(hint);
        } else {
            committed.forEach((label, id) => {
                const chip = document.createElement('span');
                chip.dataset.chip = '';
                chip.dataset.id = id;
                chip.className = 'inline-flex items-center gap-1 rounded-full bg-emerald-100 px-3 py-1 text-xs font-medium text-emerald-800';
                chip.textContent = label;
                chipsBox.appendChild(chip);
            });
        }

        inputsBox.innerHTML = '';
        // Coupon/deal/mixed pickers all submit through the same field name
        // (offer_ids[]) — only the currently-active one's inputs may be
        // enabled, or an inactive picker's re-rendered inputs pad the
        // submitted array past the max:5 validation limit (the same bug
        // homepage-section-form.js's sync() guards against on load/switch;
        // this covers the same case for inputs THIS function (re)creates
        // afterwards, since a fresh createElement('input') is never
        // disabled by default).
        const wrapper = summaryEl.closest('[data-picker]');
        const isActive = !wrapper || !wrapper.classList.contains('hidden');
        committed.forEach((label, id) => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = `${inputName}[]`;
            input.value = id;
            input.disabled = !isActive;
            inputsBox.appendChild(input);
        });
    }

    function renderStaged() {
        stagedList.innerHTML = '';
        let index = 0;
        staged.forEach((label, id) => {
            index += 1;
            const li = document.createElement('li');
            li.dataset.sortId = id;
            li.draggable = true;
            li.className = 'flex cursor-grab items-center gap-2 rounded-md bg-white px-3 py-2 text-sm shadow-sm active:cursor-grabbing';

            const handle = document.createElement('span');
            handle.className = 'shrink-0 text-gray-300';
            handle.setAttribute('aria-hidden', 'true');
            handle.innerHTML = '<svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20"><path d="M7 4a1 1 0 11-2 0 1 1 0 012 0zM7 10a1 1 0 11-2 0 1 1 0 012 0zM7 16a1 1 0 11-2 0 1 1 0 012 0zM15 4a1 1 0 11-2 0 1 1 0 012 0zM15 10a1 1 0 11-2 0 1 1 0 012 0zM15 16a1 1 0 11-2 0 1 1 0 012 0z"/></svg>';

            const order = document.createElement('span');
            order.dataset.pickerOrder = '';
            order.className = 'w-4 shrink-0 text-xs font-semibold text-gray-400';
            order.textContent = String(index);

            const span = document.createElement('span');
            span.className = 'min-w-0 flex-1 truncate';
            span.textContent = label;

            const removeBtn = document.createElement('button');
            removeBtn.type = 'button';
            removeBtn.className = 'shrink-0 text-gray-400 hover:text-red-600';
            removeBtn.setAttribute('aria-label', 'Remove');
            removeBtn.innerHTML = '<svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>';
            removeBtn.addEventListener('click', () => {
                staged.delete(id);
                renderStaged();
                syncResultRowStates();
            });

            li.append(handle, order, span, removeBtn);

            li.addEventListener('dragstart', () => {
                draggedRow = li;
                li.classList.add('opacity-40');
            });
            li.addEventListener('dragend', () => {
                li.classList.remove('opacity-40');
                draggedRow = null;
            });
            li.addEventListener('dragover', (event) => {
                event.preventDefault();
                if (!draggedRow || draggedRow === li) return;
                const bounding = li.getBoundingClientRect();
                const offset = event.clientY - bounding.top;
                if (offset > bounding.height / 2) {
                    li.after(draggedRow);
                } else {
                    li.before(draggedRow);
                }
            });

            stagedList.appendChild(li);
        });
        countEl.textContent = String(staged.size);
    }

    function syncResultRowStates() {
        resultsBox.querySelectorAll('[data-result-row]').forEach((row) => {
            const id = row.dataset.id;
            const btn = row.querySelector('[data-result-action]');
            const isStaged = staged.has(id);
            row.classList.toggle('bg-emerald-50', isStaged);
            if (isStaged) {
                btn.textContent = 'Remove';
                btn.className = 'shrink-0 rounded-md border border-red-200 px-3 py-1.5 text-xs font-medium text-red-600 hover:bg-red-50';
                btn.disabled = false;
            } else {
                const atMax = staged.size >= max;
                btn.textContent = 'Select';
                btn.className = `shrink-0 rounded-md border px-3 py-1.5 text-xs font-medium ${atMax ? 'cursor-not-allowed border-gray-200 text-gray-300' : 'border-emerald-200 text-emerald-700 hover:bg-emerald-50'}`;
                btn.disabled = atMax;
            }
        });
    }

    function renderResults(items) {
        resultsBox.innerHTML = '';
        if (!items.length) {
            const empty = document.createElement('p');
            empty.className = 'p-6 text-center text-sm text-gray-400';
            empty.textContent = 'No results match these filters.';
            resultsBox.appendChild(empty);
            return;
        }

        items.forEach((item) => {
            const id = String(item.id);
            const row = document.createElement('div');
            row.dataset.resultRow = '';
            row.dataset.id = id;
            row.className = 'flex items-center justify-between gap-3 px-6 py-3';

            const text = document.createElement('div');
            text.className = 'min-w-0';
            const title = document.createElement('p');
            title.className = 'truncate text-sm font-medium text-gray-900';
            title.textContent = item.label;
            text.appendChild(title);
            if (item.meta) {
                const meta = document.createElement('p');
                meta.className = 'text-xs text-gray-500';
                meta.textContent = item.meta;
                text.appendChild(meta);
            }

            const btn = document.createElement('button');
            btn.type = 'button';
            btn.dataset.resultAction = '';
            btn.addEventListener('click', () => {
                if (staged.has(id)) {
                    staged.delete(id);
                } else {
                    if (staged.size >= max) return;
                    staged.set(id, item.label);
                }
                renderStaged();
                syncResultRowStates();
            });

            row.append(text, btn);
            resultsBox.appendChild(row);
        });

        syncResultRowStates();
    }

    async function fetchResults() {
        const params = new URLSearchParams({ type });
        filterFields.forEach((field) => {
            if (field.value) params.set(field.dataset.filter, field.value);
        });

        resultsBox.innerHTML = '<p class="p-6 text-center text-sm text-gray-400">Loading…</p>';

        try {
            const response = await fetch(`${resultsUrl}?${params.toString()}`, {
                headers: { Accept: 'application/json' },
            });
            const data = await response.json();
            renderResults(data.items || []);
        } catch (err) {
            resultsBox.innerHTML = '<p class="p-6 text-center text-sm text-red-500">Couldn\'t load results — try again.</p>';
        }
    }

    const debouncedFetch = debounce(fetchResults, 350);

    filterFields.forEach((field) => {
        const isText = field.tagName === 'INPUT';
        if (isText) {
            field.addEventListener('input', debouncedFetch);
            return;
        }

        // Select2 sets the underlying <select>'s value and fires the
        // resulting `change` through jQuery's own event system, which a
        // plain addEventListener('change', ...) never sees — jQuery is
        // already global by this point (see select2-init.js), so bind
        // through it instead for select2-enabled fields.
        if (window.jQuery) {
            window.jQuery(field).on('change', fetchResults);
        } else {
            field.addEventListener('change', fetchResults);
        }
    });

    function openModal() {
        staged = new Map(committed);
        renderStaged();
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        fetchResults();
    }

    function closeModal() {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    openBtn.addEventListener('click', openModal);
    modal.querySelector('[data-picker-close]').addEventListener('click', closeModal);
    modal.querySelector('[data-picker-cancel]').addEventListener('click', closeModal);
    modal.addEventListener('click', (e) => {
        if (e.target === modal) closeModal();
    });
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && !modal.classList.contains('hidden')) closeModal();
    });

    modal.querySelector('[data-picker-save]').addEventListener('click', () => {
        committed = new Map(staged);
        renderChips();
        closeModal();
    });

    renderChips();
}

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-picker-summary]').forEach(initPicker);
});
