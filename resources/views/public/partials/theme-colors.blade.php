@php
    $wpPrimary = $generalSettings->primary_color ?: '#10b981';
    $wpDeal = $generalSettings->deal_color ?: '#ff7900';
    $wpDark = $generalSettings->dark_surface_color ?: '#0f172a';
@endphp
<style>
    :root {
        --wp-primary-base: {{ $wpPrimary }};
        --wp-deal-base: {{ $wpDeal }};

        /* Emerald re-tint (site primary / coupon accent) */
        --color-emerald-50: color-mix(in srgb, white 94%, var(--wp-primary-base) 6%);
        --color-emerald-100: color-mix(in srgb, white 88%, var(--wp-primary-base) 12%);
        --color-emerald-200: color-mix(in srgb, white 76%, var(--wp-primary-base) 24%);
        --color-emerald-300: color-mix(in srgb, white 60%, var(--wp-primary-base) 40%);
        --color-emerald-400: color-mix(in srgb, white 35%, var(--wp-primary-base) 65%);
        --color-emerald-500: var(--wp-primary-base);
        --color-emerald-600: color-mix(in srgb, black 15%, var(--wp-primary-base) 85%);
        --color-emerald-700: color-mix(in srgb, black 30%, var(--wp-primary-base) 70%);
        --color-emerald-800: color-mix(in srgb, black 42%, var(--wp-primary-base) 58%);
        --color-emerald-900: color-mix(in srgb, black 54%, var(--wp-primary-base) 46%);
        --color-emerald-950: color-mix(in srgb, black 70%, var(--wp-primary-base) 30%);

        {{-- Teal is only ever used alongside emerald as a gradient partner
             (hero blobs, text-gradient, section-title accent bar) — rather
             than a 4th admin color field, it's derived one step lighter than
             the matching emerald shade so gradients keep their two-tone feel
             without a separate setting. --}}
        --color-teal-50: color-mix(in srgb, white 96%, var(--wp-primary-base) 4%);
        --color-teal-100: color-mix(in srgb, white 92%, var(--wp-primary-base) 8%);
        --color-teal-200: color-mix(in srgb, white 82%, var(--wp-primary-base) 18%);
        --color-teal-300: color-mix(in srgb, white 68%, var(--wp-primary-base) 32%);
        --color-teal-400: color-mix(in srgb, white 45%, var(--wp-primary-base) 55%);
        --color-teal-500: color-mix(in srgb, white 20%, var(--wp-primary-base) 80%);
        --color-teal-600: var(--wp-primary-base);
        --color-teal-700: color-mix(in srgb, black 15%, var(--wp-primary-base) 85%);
        --color-teal-800: color-mix(in srgb, black 30%, var(--wp-primary-base) 70%);
        --color-teal-900: color-mix(in srgb, black 42%, var(--wp-primary-base) 58%);
        --color-teal-950: color-mix(in srgb, black 58%, var(--wp-primary-base) 42%);

        /* Deal accent re-tint (replaces the hand-authored default shades) */
        --color-deal: var(--wp-deal-base);
        --color-deal-50: color-mix(in srgb, white 92%, var(--wp-deal-base) 8%);
        --color-deal-300: color-mix(in srgb, white 55%, var(--wp-deal-base) 45%);
        --color-deal-500: var(--wp-deal-base);
        --color-deal-700: color-mix(in srgb, black 25%, var(--wp-deal-base) 75%);
        --color-deal-900: color-mix(in srgb, black 52%, var(--wp-deal-base) 48%);

        /* Dark surface (header utility bar, hero band, footer) */
        --color-dark-surface: {{ $wpDark }};
        --color-dark-surface-deep: color-mix(in srgb, black 25%, {{ $wpDark }} 75%);
    }
</style>
