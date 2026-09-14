/**
 * Fades/slides [data-reveal] elements in as they enter the viewport. Falls
 * back to immediately marking everything visible if IntersectionObserver
 * isn't available, so content is never permanently hidden.
 *
 * A [data-reveal-group] ancestor (e.g. a card grid) staggers its direct
 * [data-reveal] children — each gets an increasing transition-delay so a
 * whole row/grid cascades in rather than popping together, capped so a long
 * grid doesn't leave late cards waiting several seconds.
 *
 * Exposed as window.initScrollReveal(root) so ajax-filters.js can re-run it
 * scoped to just-swapped-in content — [data-reveal] starts at opacity:0 in
 * CSS, and this is the ONLY thing that ever adds `.is-visible`. Without
 * re-running it after an AJAX results swap, every filtered/paginated card
 * would sit at opacity:0 forever (found via real click-through testing:
 * cards were present in the DOM, just permanently invisible).
 */
function initScrollReveal(root = document) {
    root.querySelectorAll('[data-reveal-group]').forEach((group) => {
        Array.from(group.querySelectorAll(':scope > [data-reveal]')).forEach((el, index) => {
            el.style.transitionDelay = `${Math.min(index * 70, 420)}ms`;
        });
    });

    const targets = root.querySelectorAll('[data-reveal]');
    if (!targets.length) return;

    if (!('IntersectionObserver' in window)) {
        targets.forEach((el) => el.classList.add('is-visible'));
        return;
    }

    const observer = new IntersectionObserver(
        (entries, obs) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-visible');
                    obs.unobserve(entry.target);
                }
            });
        },
        { threshold: 0.1, rootMargin: '0px 0px -40px 0px' }
    );

    targets.forEach((el) => observer.observe(el));
}

window.initScrollReveal = initScrollReveal;

document.addEventListener('DOMContentLoaded', () => initScrollReveal(document));
