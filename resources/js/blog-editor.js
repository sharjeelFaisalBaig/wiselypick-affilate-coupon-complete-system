import Quill from 'quill';
import 'quill/dist/quill.snow.css';

// [data-quill-editor="key"] pairs with [data-content-field="key"] — supports
// any number of rich-text editors on one page (e.g. a Blog post's Content
// Sections repeater, which can have any number of them), not just one.
function initQuillEditors() {
    document.querySelectorAll('[data-quill-editor]').forEach((container) => {
        // Each container is handled independently — a failure building ONE
        // editor (e.g. a `new Quill()` or clipboard.convert() throw) must
        // not abort the whole forEach, which would otherwise silently skip
        // every editor still to come (the 3rd content section onward,
        // reported as simply uneditable — nothing ever built its toolbar
        // or contenteditable area in the first place).
        try {
            if (container.dataset.quillInitialized) return;

            const key = container.getAttribute('data-quill-editor');
            const hiddenField = document.querySelector(`[data-content-field="${key}"]`);
            if (!hiddenField) return;

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

            // Keyed registry so repeater builders (e.g. blog-sections-builder.js)
            // can read a row's live, not-yet-submitted content — Quill only
            // writes back to its hidden field on the form's 'submit' event.
            window.quillInstances = window.quillInstances || {};
            window.quillInstances[key] = quill;

            if (hiddenField.value) {
                // Setting quill.root.innerHTML directly writes DOM Quill never
                // parsed into its own Delta/blot model, so the model and the
                // rendered DOM disagree about content structure — clicking
                // inside the editor then maps to the wrong (or no) blot and the
                // cursor fails to place, until something (e.g. a full reload
                // re-running this same path) forces Quill to resync. Feeding
                // the HTML through Quill's own clipboard parser builds a model
                // that matches the DOM from the start, so click-to-cursor
                // mapping works immediately.
                quill.setContents(quill.clipboard.convert({html: hiddenField.value}));
            }

            // Only mark as done once construction actually succeeded — a
            // container that threw stays eligible for a retry (e.g. the
            // next time a repeater builder calls this) instead of being
            // permanently skipped in a half-built state.
            container.dataset.quillInitialized = 'true';

            const form = container.closest('form');
            form?.addEventListener('submit', () => {
                hiddenField.value = quill.root.innerHTML;
            });
        } catch (error) {
            console.error('Failed to initialize a rich text editor:', error);
        }
    });
}

document.addEventListener('DOMContentLoaded', initQuillEditors);

// Exposed so repeater builders (e.g. custom-sections-builder.js) can
// initialize a Quill editor for a row they've just added to the DOM.
window.initQuillEditors = initQuillEditors;

// A page restored from the browser's back/forward cache keeps its DOM
// exactly as it was — including every [data-quill-initialized] marker —
// but the JS heap (and with it every live Quill instance) is gone. The
// result is a rich text editor that LOOKS completely normal (toolbar,
// contenteditable area, previous content) but is fully inert, since
// nothing is listening for input anymore; only a real reload (a fresh,
// non-bfcache navigation) was rebuilding it. Detect that restore and
// re-run initialization instead of waiting for the admin to notice.
window.addEventListener('pageshow', (event) => {
    if (!event.persisted) return;

    document.querySelectorAll('[data-quill-editor]').forEach((container) => {
        delete container.dataset.quillInitialized;
    });
    window.quillInstances = {};
    initQuillEditors();
});
