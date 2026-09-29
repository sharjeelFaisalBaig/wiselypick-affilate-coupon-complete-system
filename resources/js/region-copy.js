// Powers the "Copy" popup on the admin Regions list (Slug + Name prompt),
// per-region since the modal's form action is set from whichever row's
// "Copy" button was clicked.

document.addEventListener('DOMContentLoaded', () => {
    const modal = document.querySelector('[data-copy-region-modal]');
    const form = document.querySelector('[data-copy-region-form]');
    const sourceLabel = document.querySelector('[data-copy-region-source]');
    if (!modal || !form || !sourceLabel) return;

    document.querySelectorAll('[data-copy-region-open]').forEach((button) => {
        button.addEventListener('click', () => {
            form.action = button.getAttribute('data-copy-url');
            sourceLabel.textContent = button.getAttribute('data-region-name');
            form.reset();
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        });
    });

    const close = () => {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    };

    modal.querySelector('[data-copy-region-cancel]')?.addEventListener('click', close);
    modal.addEventListener('click', (event) => {
        if (event.target === modal) close();
    });

    // Copying a large region (many stores/files) can take a while — disable
    // the button and show progress text so a slow synchronous request
    // (no queue worker on this host) doesn't read as a frozen/broken form.
    form.addEventListener('submit', () => {
        form.querySelector('[data-copy-region-submit]').disabled = true;
        form.querySelector('[data-copy-region-progress]').classList.remove('hidden');
    });
});
