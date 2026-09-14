import Quill from 'quill';
import 'quill/dist/quill.snow.css';

// [data-quill-editor="key"] pairs with [data-content-field="key"] — supports
// any number of rich-text editors on one page (e.g. a store form with
// separate "About", "Curate Left", "Curate Right" fields), not just one.
function initQuillEditors() {
    document.querySelectorAll('[data-quill-editor]').forEach((container) => {
        if (container.dataset.quillInitialized) return;

        const key = container.getAttribute('data-quill-editor');
        const hiddenField = document.querySelector(`[data-content-field="${key}"]`);
        if (!hiddenField) return;

        container.dataset.quillInitialized = 'true';

        const quill = new Quill(container, {
            theme: 'snow',
            modules: {
                toolbar: [
                    [{ header: [2, 3, false] }],
                    ['bold', 'italic', 'underline'],
                    ['link', 'blockquote'],
                    [{ list: 'ordered' }, { list: 'bullet' }],
                    ['clean'],
                ],
            },
        });

        if (hiddenField.value) {
            quill.root.innerHTML = hiddenField.value;
        }

        const form = container.closest('form');
        form?.addEventListener('submit', () => {
            hiddenField.value = quill.root.innerHTML;
        });
    });
}

document.addEventListener('DOMContentLoaded', initQuillEditors);

// Exposed so repeater builders (e.g. custom-sections-builder.js) can
// initialize a Quill editor for a row they've just added to the DOM.
window.initQuillEditors = initQuillEditors;
