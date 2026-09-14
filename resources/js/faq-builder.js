// Repeatable question/answer row builder, used by both the Blog and Store
// admin forms to build the `faqs` JSON column (also powers FAQ schema markup).

function initFaqBuilder() {
    const container = document.querySelector('[data-faq-list]');
    const addButton = document.querySelector('[data-faq-add]');
    if (!container || !addButton) return;

    let index = Number(container.getAttribute('data-faq-next-index') || '0');

    addButton.addEventListener('click', () => {
        const row = document.createElement('div');
        row.className = 'flex gap-2 items-start rounded-md border border-gray-200 p-3';
        row.innerHTML = `
            <div class="flex-1 space-y-2">
                <input type="text" name="faqs[${index}][question]" placeholder="Question"
                       class="block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                <textarea name="faqs[${index}][answer]" rows="2" placeholder="Answer"
                          class="block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500"></textarea>
            </div>
            <button type="button" data-faq-remove class="text-sm font-medium text-red-600 hover:text-red-700">Remove</button>
        `;
        container.appendChild(row);
        index += 1;
    });

    container.addEventListener('click', (event) => {
        const removeButton = event.target.closest('[data-faq-remove]');
        if (removeButton) {
            removeButton.closest('div.flex').remove();
        }
    });
}

document.addEventListener('DOMContentLoaded', initFaqBuilder);
