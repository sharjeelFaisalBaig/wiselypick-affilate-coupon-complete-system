import $ from 'jquery';
import select2 from 'select2';

window.$ = window.jQuery = $;
select2(window, $);

// Renders an <option data-flag="/storage/..."> as a small flag image + its
// text, both in the closed selection box and in the open dropdown list — a
// native <option> can't contain an <img> itself, so this is Select2's
// templateResult/templateSelection hook standing in for that.
function formatOptionWithFlag(option) {
    if (!option.id) return option.text;
    const flagUrl = option.element && option.element.getAttribute('data-flag');
    // Always return a jQuery/DOM node (never a plain string) once an id is
    // present, even with no flag — Select2 only runs `escapeMarkup` against
    // string returns, so returning a node here means the option's own text
    // stays safely escaped via jQuery's .text() below in every case,
    // without needing to touch (or weaken) escapeMarkup at all.
    if (!flagUrl) return $('<span></span>').text(option.text);

    const $rendered = $(
        '<span class="inline-flex items-center gap-1.5 whitespace-nowrap"><img class="h-3.5 w-5 shrink-0 rounded-sm border border-gray-200 object-cover" alt=""></span>'
    );
    $rendered.find('img').attr('src', flagUrl);
    $rendered.append(document.createTextNode(option.text));
    return $rendered;
}

/**
 * Initializes Select2 (with search built in) on every `<select data-select2-enable>`
 * — single or multiple, admin or public. New elements added later (e.g. an
 * AJAX-swapped filter results fragment) are picked up by re-running this on
 * DOMContentLoaded only; ajax-filters.js swaps don't currently inject new
 * selects, so a one-time init is sufficient.
 *
 * `data-auto-submit` submits the select's form the instant an option is
 * chosen (e.g. the admin region-switcher) — bound through jQuery rather
 * than a plain native <select onchange>, because Select2 changes the
 * underlying <select> via jQuery's own synthetic `.trigger('change')`,
 * which a native `onchange` attribute/property handler does not reliably
 * receive (confirmed the hard way elsewhere in this app — see
 * ajax-filters.js's [data-instant-filter] handling for the same issue).
 */
function initSelect2() {
    $('select[data-select2-enable]').each(function () {
        const $el = $(this);
        if ($el.hasClass('select2-hidden-accessible')) return;

        const hasFlags = $el.find('option[data-flag]').length > 0;

        $el.select2({
            width: '100%',
            placeholder: $el.data('placeholder') || 'Select...',
            allowClear: !$el.prop('multiple') && !$el.prop('required'),
            ...(hasFlags && {
                templateResult: formatOptionWithFlag,
                templateSelection: formatOptionWithFlag,
                // The dropdown popup otherwise matches the (deliberately
                // compact, e.g. topbar) trigger's own width, which is too
                // narrow once a flag icon eats into the space "US — United
                // States" needs — wrapping every option's text. Letting the
                // popup size to its own content (only for flagged selects —
                // every other existing select keeps matching its trigger's
                // width exactly as before) fixes that without having to
                // widen the closed trigger box itself.
                dropdownAutoWidth: true,
            }),
        });

        if ($el.is('[data-auto-submit]')) {
            $el.on('change', function () {
                this.form?.submit();
            });
        }
    });
}

document.addEventListener('DOMContentLoaded', initSelect2);
