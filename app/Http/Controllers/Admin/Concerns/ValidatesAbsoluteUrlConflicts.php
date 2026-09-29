<?php

namespace App\Http\Controllers\Admin\Concerns;

use App\Support\AbsoluteUrlRegistry;
use Illuminate\Validation\ValidationException;

trait ValidatesAbsoluteUrlConflicts
{
    /**
     * Throws a ValidationException (same as a failed $request->validate())
     * if $absoluteUrl already belongs to a different entity anywhere in the
     * system — every region's stores, blogs, pages, fixed pages, and the
     * regions themselves. See AbsoluteUrlRegistry for why this has to be
     * checked across ALL regions, not just the one being saved into.
     */
    protected function guardAgainstUrlConflict(string $absoluteUrl, string $type, ?int $id, string $field = 'slug'): void
    {
        $conflict = app(AbsoluteUrlRegistry::class)->findConflict($absoluteUrl, $type, $id);

        if ($conflict) {
            throw ValidationException::withMessages([
                $field => "This URL (\"{$absoluteUrl}\") is already used by {$conflict} — change the slug, prefix, or suffix so it's unique.",
            ]);
        }
    }
}
