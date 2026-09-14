// Generic vanilla-JS UI helpers shared across admin and public pages.
// No framework (no Alpine/jQuery/React/Vue) — plain DOM APIs only.

function qs(selector, scope = document) {
    return scope.querySelector(selector);
}

function qsa(selector, scope = document) {
    return Array.from(scope.querySelectorAll(selector));
}

// [data-toggle="#target-id"] click toggles the `.hidden` class on the target,
// and closes when clicking outside of both the trigger and the target itself
// (not some ancestor wrapper — hiding the wrong element was what made the
// admin user-menu vanish permanently after any click elsewhere on the page).
function initToggles() {
    document.addEventListener('click', (event) => {
        const trigger = event.target.closest('[data-toggle]');

        if (trigger) {
            const target = document.querySelector(trigger.getAttribute('data-toggle'));
            if (target) {
                target.classList.toggle('hidden');
                event.stopPropagation();
            }
            return;
        }

        qsa('[data-toggle]').forEach((triggerEl) => {
            const target = document.querySelector(triggerEl.getAttribute('data-toggle'));
            if (
                target &&
                !target.classList.contains('hidden') &&
                !target.contains(event.target) &&
                !triggerEl.contains(event.target)
            ) {
                target.classList.add('hidden');
            }
        });
    });
}

// [data-modal-open="#modal-id"] / [data-modal-close] open and close a modal overlay.
// Locks body scroll while a modal is open — without it, iOS Safari lets touch
// scrolls bleed through to the page behind the fixed overlay, which reads as
// the modal itself being scrollable/full-height on mobile.
function openModal(modal) {
    if (!modal) return;
    modal.classList.remove('hidden');
    document.body.classList.add('overflow-hidden');
}

function closeModal(modal) {
    if (!modal) return;
    modal.classList.add('hidden');
    if (!document.querySelector('[data-modal]:not(.hidden)')) {
        document.body.classList.remove('overflow-hidden');
    }
}

function initModals() {
    document.addEventListener('click', (event) => {
        const opener = event.target.closest('[data-modal-open]');
        if (opener) {
            openModal(document.querySelector(opener.getAttribute('data-modal-open')));
            return;
        }

        const closer = event.target.closest('[data-modal-close]');
        if (closer) {
            closeModal(closer.closest('[data-modal]'));
        }
    });
}

// [data-coupon-cta OR data-deal-cta][data-redirect-url][data-offer-id] click
// behavior — identical for both coupons and deals:
//   1. The CURRENT tab navigates to the merchant via our tracked redirect
//      (same-tab, so the click counter increments before the shopper leaves).
//   2. A NEW tab opens pointing back at our own page with a `revealOffer`
//      query flag — the new tab gets browser focus (standard behavior for
//      window.open from a user gesture) and auto-shows the offer modal via
//      initRevealFromQueryString() below. For a coupon that modal shows the
//      code + copy button; for a deal (which never has a code) the same
//      modal shows "No Code Required" in that slot instead — see
//      offer-modal.blade.php.
function initOfferCta() {
    document.addEventListener('click', (event) => {
        const el = event.target.closest('[data-coupon-cta], [data-deal-cta]');
        if (!el) return;

        const redirectUrl = el.getAttribute('data-redirect-url');
        const offerId = el.getAttribute('data-offer-id');

        if (offerId) {
            const revealUrl = new URL(window.location.href);
            revealUrl.searchParams.set('revealOffer', offerId);
            window.open(revealUrl.toString(), '_blank');
        }

        if (redirectUrl) {
            window.location.href = redirectUrl;
        }
    });
}

// On load, if the URL carries ?revealOffer=ID (see initOfferCta above),
// open that offer's modal immediately without requiring another click.
function initRevealFromQueryString() {
    const offerId = new URLSearchParams(window.location.search).get('revealOffer');
    if (!offerId) return;

    openModal(document.querySelector(`#offer-modal-${offerId}`));
}

// [data-copy="CODE_TEXT"] copies text to clipboard and flips its label briefly.
function initCopyButtons() {
    document.addEventListener('click', async (event) => {
        const button = event.target.closest('[data-copy]');
        if (!button) return;

        const text = button.getAttribute('data-copy');
        try {
            await navigator.clipboard.writeText(text);
        } catch {
            const helper = document.createElement('textarea');
            helper.value = text;
            document.body.appendChild(helper);
            helper.select();
            document.execCommand('copy');
            helper.remove();
        }

        const original = button.getAttribute('data-copy-label') || button.textContent;
        button.textContent = 'Copied!';
        setTimeout(() => {
            button.textContent = original;
        }, 1500);
    });
}

// [data-tabs] wraps [data-tab-trigger="key"] buttons and [data-tab-panel="key"] panels.
function initTabs() {
    qsa('[data-tabs]').forEach((wrapper) => {
        const triggers = qsa('[data-tab-trigger]', wrapper);
        const panels = qsa('[data-tab-panel]', wrapper);

        const activate = (key) => {
            triggers.forEach((t) => t.classList.toggle('is-active', t.getAttribute('data-tab-trigger') === key));
            // Inline style avoids relying on Tailwind's utility cascade order
            // when a panel also carries a static `grid`/`flex` display class.
            panels.forEach((p) => {
                p.style.display = p.getAttribute('data-tab-panel') === key ? '' : 'none';
            });
        };

        triggers.forEach((trigger) => {
            trigger.addEventListener('click', () => activate(trigger.getAttribute('data-tab-trigger')));
        });

        if (triggers.length) activate(triggers[0].getAttribute('data-tab-trigger'));
    });
}

// Adds a deepening shadow to the sticky public header once the page has
// scrolled past the hero — a flat border reads fine at the very top, but
// looks disconnected from scrolled-under content without one.
function initHeaderScrollShadow() {
    const header = document.querySelector('[data-site-header]');
    if (!header) return;

    const sync = () => {
        header.classList.toggle('shadow-md', window.scrollY > 8);
    };

    window.addEventListener('scroll', sync, { passive: true });
    sync();
}

// On the admin shell, warns before leaving a create/edit page with unsaved
// input — a native confirm on tab close/refresh/URL-bar navigation
// (beforeunload), and a JS confirm() on in-app link clicks (sidebar/topbar
// links aren't real navigations the browser can intercept). Scoped to
// <main> so the topbar's region-switcher and logout forms (which have no
// editable fields, live outside <main>, and submit via requestSubmit) never
// trip it. Only POST/PUT forms are tracked — every GET form in the admin is
// a search/filter, and changing a filter isn't "unsaved work".
function initUnsavedChangesGuard() {
    const shell = document.querySelector('[data-admin-shell]');
    const main = shell && shell.querySelector('main');
    if (!main) return;

    let dirty = false;
    let submitting = false;

    const isTracked = (form) => form && main.contains(form) && form.method.toLowerCase() !== 'get';

    ['input', 'change'].forEach((eventName) => {
        main.addEventListener(eventName, (event) => {
            if (isTracked(event.target.closest('form'))) dirty = true;
        });
    });

    main.addEventListener('submit', (event) => {
        if (isTracked(event.target)) submitting = true;
    });

    window.addEventListener('beforeunload', (event) => {
        if (!dirty || submitting) return;
        event.preventDefault();
        event.returnValue = '';
    });

    document.addEventListener('click', (event) => {
        if (!dirty || submitting) return;
        const link = event.target.closest('a[href]');
        if (!link || link.target === '_blank' || link.hasAttribute('download')) return;
        if (window.confirm('You have unsaved changes. Are you sure you want to leave this page?')) {
            // The user already confirmed via this dialog — the link
            // navigation that follows would otherwise also fire
            // beforeunload while `dirty` is still true, popping the
            // browser's OWN generic prompt right after this one.
            submitting = true;
        } else {
            event.preventDefault();
            event.stopImmediatePropagation();
        }
    });
}

document.addEventListener('DOMContentLoaded', () => {
    initToggles();
    initModals();
    initOfferCta();
    initRevealFromQueryString();
    initCopyButtons();
    initTabs();
    initHeaderScrollShadow();
    initUnsavedChangesGuard();
});
