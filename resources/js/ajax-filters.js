// Generic AJAX-filter + AJAX-pagination controller. A page opts in by
// wrapping its filter form and results container in:
//
//   <div data-ajax-filter data-base-url="{{ url()->current() }}">
//     <form data-ajax-filter-form>
//       ...filter fields...
//       <button type="submit" data-ajax-filter-submit>
//         <svg data-ajax-filter-spinner class="hidden animate-spin">...</svg>
//         Search
//       </button>
//     </form>
//     <div data-ajax-filter-results>...initial server-rendered results...</div>
//   </div>
//
// Filtering fires on an explicit Search click (or Enter, which submits the
// form the same way), on a pagination link inside the results container, and
// on any field marked [data-instant-filter] (a <select> applies the instant
// it changes) or [data-instant-filter-tab] (a button-styled tab — see below)
// — everything else (plain text/search inputs without that attribute) still
// waits for Search/Enter. Each request re-fetches the SAME route with an
// X-Ajax-Filter header so the server can return just the results partial,
// swaps it in, and pushes the new query string into the URL via
// history.pushState — so the resulting URL is always shareable/reloadable to
// the same filtered state, and back/forward navigation restores prior
// states. While a request is in flight, the Search button + free-text search
// input are disabled and the button shows a spinner.
//
// [data-instant-filter-tab] buttons are a pill-style alternative to a
// <select> for the same "apply immediately" behavior — e.g. an All/Codes/
// Offers row. Each tab needs `data-value` and `data-instant-filter-target`
// (the `name` of the hidden/visible field it writes into); clicking one sets
// that field's value, restyles the tab row to show which is active, and
// applies filters immediately, same as an instant-filter select's change.

function initAjaxFilter(root) {
    const form = root.querySelector('[data-ajax-filter-form]');
    const results = root.querySelector('[data-ajax-filter-results]');
    const baseUrl = root.getAttribute('data-base-url') || window.location.pathname;
    if (!form || !results) return;

    const submitBtn = form.querySelector('[data-ajax-filter-submit]');
    const spinner = submitBtn?.querySelector('[data-ajax-filter-spinner]');
    const searchInput = form.querySelector('input[name="q"]');

    let activeController = null;

    function buildQueryString(source) {
        const params = new URLSearchParams();
        const formData = new FormData(source);
        for (const [key, value] of formData.entries()) {
            if (value !== '' && value !== null) params.append(key, value);
        }
        return params.toString();
    }

    async function fetchAndSwap(url, { pushState = true } = {}) {
        if (activeController) activeController.abort();
        activeController = new AbortController();

        results.classList.add('opacity-50', 'pointer-events-none', 'transition-opacity');
        if (submitBtn) submitBtn.disabled = true;
        if (searchInput) searchInput.disabled = true;
        spinner?.classList.remove('hidden');

        try {
            const response = await fetch(url, {
                headers: { 'X-Ajax-Filter': '1', 'X-Requested-With': 'XMLHttpRequest' },
                signal: activeController.signal,
            });
            if (!response.ok) throw new Error(`Request failed: ${response.status}`);
            const html = await response.text();
            results.innerHTML = html;
            // Swapped-in [data-reveal] cards start at opacity:0 in CSS and
            // only ever become visible via scroll-reveal.js's observer —
            // which only scans the DOM once on page load, so it never sees
            // this new content unless re-run scoped to it here.
            window.initScrollReveal?.(results);

            if (pushState) {
                window.history.pushState({ ajaxFilter: true, url }, '', url);
            }

            results.dispatchEvent(new CustomEvent('ajax-filter:updated', { bubbles: true }));
            window.scrollTo({ top: root.getBoundingClientRect().top + window.scrollY - 80, behavior: 'smooth' });
        } catch (error) {
            if (error.name !== 'AbortError') {
                // Fall back to a real navigation rather than leaving the UI stuck.
                window.location.href = url;
            }
        } finally {
            results.classList.remove('opacity-50', 'pointer-events-none');
            if (submitBtn) submitBtn.disabled = false;
            if (searchInput) searchInput.disabled = false;
            spinner?.classList.add('hidden');
        }
    }

    function applyFilters(pushState = true) {
        const qs = buildQueryString(form);
        const url = qs ? `${baseUrl}?${qs}` : baseUrl;
        fetchAndSwap(url, { pushState });
    }

    form.addEventListener('submit', (event) => {
        event.preventDefault();
        applyFilters(true);
    });

    // [data-instant-filter] fields (e.g. a category <select>) apply the
    // instant they change, without waiting for Search. Select2 changes a
    // field by calling jQuery's `.trigger('change')` on the underlying
    // <select>, which does NOT reach a plain addEventListener('change', ...)
    // — jQuery's synthetic event never reaches native listeners bound
    // outside jQuery. jQuery + Select2 are always loaded globally on every
    // page that could use this (see select2-init.js), so bind through
    // jQuery's own delegated `.on()` to actually catch it; a plain listener
    // is kept as a fallback for any non-Select2 instant-filter field (or if
    // jQuery is ever removed).
    if (window.jQuery) {
        window.jQuery(form).on('change', '[data-instant-filter]', () => applyFilters(true));
    } else {
        form.addEventListener('change', (event) => {
            if (event.target.matches('[data-instant-filter]')) applyFilters(true);
        });
    }

    // [data-instant-filter-tab] buttons — see the file-level doc comment.
    const instantTabs = Array.from(form.querySelectorAll('[data-instant-filter-tab]'));
    const activeTabClasses = ['bg-emerald-500', 'text-white', 'shadow-sm'];
    const inactiveTabClasses = ['bg-white', 'text-gray-600', 'border', 'border-gray-300', 'hover:bg-gray-50'];

    function syncInstantTabs() {
        instantTabs.forEach((tab) => {
            const target = form.querySelector(`[name="${tab.getAttribute('data-instant-filter-target')}"]`);
            const isActive = (target?.value || '') === tab.getAttribute('data-value');
            tab.classList.remove(...activeTabClasses, ...inactiveTabClasses);
            tab.classList.add(...(isActive ? activeTabClasses : inactiveTabClasses));
        });
    }

    instantTabs.forEach((tab) => {
        tab.addEventListener('click', () => {
            const target = form.querySelector(`[name="${tab.getAttribute('data-instant-filter-target')}"]`);
            if (!target) return;
            target.value = tab.getAttribute('data-value');
            syncInstantTabs();
            applyFilters(true);
        });
    });

    syncInstantTabs();

    // Intercept pagination links (and any other in-results links opted in
    // via [data-ajax-link]) rendered inside the results partial.
    results.addEventListener('click', (event) => {
        const link = event.target.closest('a');
        if (!link || !results.contains(link)) return;
        if (link.target === '_blank' || link.hasAttribute('data-no-ajax')) return;

        const href = link.getAttribute('href');
        if (!href || href.startsWith('#')) return;

        // Only intercept links back to this same page (pagination) — a
        // "More {Store} coupons" link, for instance, should navigate normally.
        const linkUrl = new URL(href, window.location.origin);
        const baseUrlObj = new URL(baseUrl, window.location.origin);
        if (linkUrl.pathname !== baseUrlObj.pathname) return;

        event.preventDefault();
        fetchAndSwap(href, { pushState: true });
    });

    window.addEventListener('popstate', (event) => {
        const url = event.state?.url || window.location.href;
        const targetUrl = new URL(url, window.location.origin);
        const baseUrlObj = new URL(baseUrl, window.location.origin);
        if (targetUrl.pathname !== baseUrlObj.pathname) return;

        // Restore form field values from the URL so the visible filter UI
        // matches what's now on screen, then re-fetch without pushing again.
        const params = targetUrl.searchParams;
        form.querySelectorAll('[name]').forEach((field) => {
            if (field.type === 'radio' || field.type === 'checkbox') {
                field.checked = params.get(field.name) === field.value;
            } else {
                field.value = params.get(field.name) || '';
            }
        });
        syncInstantTabs();

        fetchAndSwap(url, { pushState: false });
    });
}

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-ajax-filter]').forEach(initAjaxFilter);
});
