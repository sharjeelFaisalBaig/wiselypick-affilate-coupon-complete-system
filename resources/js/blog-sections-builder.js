// Blog Content Sections repeater — add/remove/copy/reorder. Each row pairs a
// Quick Link Title input with its own Quill editor (wired through the
// generic [data-quill-editor]/[data-content-field] keying in blog-editor.js).
//
// Field `name`s are deliberately NOT kept index-correct while editing —
// they're renumbered from scratch, in current DOM order, right before
// submit. That's what makes drag-reordering "just work": moving a row's DOM
// node is enough, nothing needs to track/rewrite indices as you go.

function buildSectionRow(key, titleValue, contentValue) {
    const row = document.createElement('div');
    row.className = 'rounded-md border border-gray-200 p-4';
    row.setAttribute('data-section-row', '');
    row.setAttribute('data-section-key', key);
    row.innerHTML = `
        <div class="mb-3 flex items-center gap-2">
            <span data-section-drag-handle draggable="true" class="cursor-grab select-none text-gray-300 hover:text-gray-500" title="Drag to reorder">&#10021;</span>
            <input type="text" data-section-title placeholder="Quick Link Title, e.g. Overview" required
                   class="block flex-1 rounded-md border-gray-300 text-sm font-medium shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
            <button type="button" data-section-copy class="text-xs font-medium text-gray-500 hover:text-gray-700">Copy</button>
            <button type="button" data-section-remove class="text-xs font-medium text-red-600 hover:text-red-700">Remove</button>
        </div>
        <div data-quill-editor="${key}" style="min-height: 180px;" class="bg-white"></div>
        <textarea data-content-field="${key}" class="hidden"></textarea>
    `;
    row.querySelector('[data-section-title]').value = titleValue || '';
    row.querySelector(`[data-content-field="${key}"]`).value = contentValue || '';
    return row;
}

function initBlogSectionsBuilder() {
    const container = document.querySelector('[data-section-list]');
    const addButton = document.querySelector('[data-section-add]');
    const form = container?.closest('form');
    if (!container || !addButton || !form) return;

    let keyCounter = document.querySelectorAll('[data-section-row]').length;

    addButton.addEventListener('click', () => {
        const row = buildSectionRow(`section-${keyCounter++}`, '', '');
        container.appendChild(row);
        window.initQuillEditors();
    });

    container.addEventListener('click', (event) => {
        if (event.target.closest('[data-section-remove]')) {
            if (container.querySelectorAll('[data-section-row]').length <= 1) {
                alert('At least one content section is required.');
                return;
            }
            event.target.closest('[data-section-row]').remove();
            return;
        }

        const copyButton = event.target.closest('[data-section-copy]');
        if (copyButton) {
            const sourceRow = copyButton.closest('[data-section-row]');
            const sourceKey = sourceRow.getAttribute('data-section-key');
            const sourceQuill = window.quillInstances?.[sourceKey];
            const sourceTitle = sourceRow.querySelector('[data-section-title]').value;

            const row = buildSectionRow(
                `section-${keyCounter++}`,
                sourceTitle ? `${sourceTitle} (Copy)` : '',
                sourceQuill ? sourceQuill.root.innerHTML : ''
            );
            sourceRow.after(row);
            window.initQuillEditors();
        }
    });

    // Native HTML5 drag-and-drop, initiated only from the handle icon (so
    // selecting/editing text inside a Quill editor never accidentally
    // starts a row drag) but moving the whole [data-section-row] ancestor.
    let dragged = null;

    container.addEventListener('dragstart', (event) => {
        const handle = event.target.closest('[data-section-drag-handle]');
        if (!handle) return;
        dragged = handle.closest('[data-section-row]');
        dragged.classList.add('opacity-40');
    });

    container.addEventListener('dragend', () => {
        dragged?.classList.remove('opacity-40');
        dragged = null;
    });

    container.addEventListener('dragover', (event) => {
        event.preventDefault();
        if (!dragged) return;

        const target = event.target.closest('[data-section-row]');
        if (!target || target === dragged) return;

        const bounding = target.getBoundingClientRect();
        const offset = event.clientY - bounding.top;
        if (offset > bounding.height / 2) {
            target.after(dragged);
        } else {
            target.before(dragged);
        }
    });

    container.addEventListener('drop', (event) => event.preventDefault());

    // Renumber every row's field names from current DOM order right before
    // submit — see the module docblock for why this is the only place
    // indices are assigned.
    form.addEventListener('submit', () => {
        container.querySelectorAll('[data-section-row]').forEach((row, index) => {
            row.querySelector('[data-section-title]').name = `content_sections[${index}][title]`;
            const key = row.getAttribute('data-section-key');
            const contentField = row.querySelector(`[data-content-field="${key}"]`);
            if (contentField) contentField.name = `content_sections[${index}][content]`;
        });
    });
}

document.addEventListener('DOMContentLoaded', initBlogSectionsBuilder);
