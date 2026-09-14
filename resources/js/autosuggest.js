/**
 * Lightweight autosuggest dropdown: on debounced keystroke, fetches the
 * input's [data-autosuggest-endpoint] with the current value as `q`,
 * expects a JSON array of {label, url} results, and renders them as a
 * clickable dropdown that navigates straight to that result's own page.
 * This sits ABOVE the existing deliberate-submit search (ajax-filters.js
 * or a plain form) as a purely additive layer — it never intercepts the
 * input's normal typing/submit behavior, it only adds an optional dropdown.
 *
 * The dropdown is appended to <body> and positioned via the input's own
 * getBoundingClientRect() rather than inserted next to the input in the
 * DOM, so it never affects the surrounding template's layout (several very
 * different form layouts use this across the app).
 *
 * Suggestions are scoped to the input's other sibling form fields: every
 * other non-empty named field in the same <form> (a category <select>, a
 * status filter, etc.) is sent alongside `q`, so results respect whatever
 * else is currently selected in that filter form. This applies everywhere,
 * including admin filter forms — those forms don't auto-submit on select
 * change (see ajax-filters.js's [data-instant-filter] opt-in), but their
 * autosuggest dropdown still narrows to match the other selected filters.
 */
function debounce(fn, delay) {
    let timer;
    return (...args) => {
        clearTimeout(timer);
        timer = setTimeout(() => fn(...args), delay);
    };
}

function initAutosuggest(input) {
    const endpoint = input.getAttribute('data-autosuggest-endpoint');
    if (!endpoint) return;

    const dropdown = document.createElement('div');
    dropdown.className =
        'hidden fixed z-50 max-h-72 overflow-y-auto rounded-md border border-gray-200 bg-white text-left shadow-lg';
    document.body.appendChild(dropdown);

    const position = () => {
        const rect = input.getBoundingClientRect();
        dropdown.style.top = `${rect.bottom}px`;
        dropdown.style.left = `${rect.left}px`;
        dropdown.style.width = `${rect.width}px`;
    };

    const close = () => {
        dropdown.innerHTML = '';
        dropdown.classList.add('hidden');
    };

    const render = (results) => {
        if (!results.length) {
            close();
            return;
        }

        position();
        dropdown.innerHTML = results
            .map(
                (r) =>
                    `<a href="${r.url}" class="block px-3 py-2 text-sm text-gray-700 hover:bg-gray-50">${r.label}</a>`
            )
            .join('');
        dropdown.classList.remove('hidden');
    };

    const search = debounce(async () => {
        const q = input.value.trim();
        if (q.length < 2) {
            close();
            return;
        }

        const params = new URLSearchParams({ q });
        const form = input.closest('form');
        if (form) {
            for (const [key, value] of new FormData(form).entries()) {
                if (key === input.name || value === '') continue;
                params.append(key, value);
            }
        }

        try {
            const response = await fetch(`${endpoint}?${params.toString()}`, {
                headers: { Accept: 'application/json' },
            });
            if (!response.ok) return;
            render(await response.json());
        } catch {
            close();
        }
    }, 250);

    input.addEventListener('input', search);
    input.addEventListener('focus', () => {
        if (input.value.trim().length >= 2) search();
    });
    input.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') close();
    });

    window.addEventListener('scroll', () => {
        if (!dropdown.classList.contains('hidden')) position();
    }, true);
    window.addEventListener('resize', () => {
        if (!dropdown.classList.contains('hidden')) position();
    });

    document.addEventListener('click', (event) => {
        if (event.target !== input && !dropdown.contains(event.target)) close();
    });
}

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-autosuggest-endpoint]').forEach(initAutosuggest);
});
