<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\GuardsRegionOwnership;
use App\Http\Controllers\Controller;
use App\Models\Badge;
use App\Models\Region;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class BadgeController extends Controller
{
    use GuardsRegionOwnership;

    public function index(Request $request): View
    {
        /** @var Region $region */
        $region = $request->attributes->get('activeRegion');

        $badges = Badge::where('region_id', $region->id)->withCount('offers')->orderBy('name')->get();

        return view('admin.badges.index', compact('badges'));
    }

    public function create(): View
    {
        return view('admin.badges.form', ['badge' => new Badge()]);
    }

    public function store(Request $request): RedirectResponse
    {
        /** @var Region $region */
        $region = $request->attributes->get('activeRegion');

        $data = $this->validated($request);
        $data['region_id'] = $region->id;

        Badge::create($data);

        return redirect()->route('admin.badges.index')->with('status', 'Badge created.');
    }

    public function edit(Request $request, Badge $badge): View
    {
        $this->abortUnlessOwnedByActiveRegion($request, $badge->region_id);

        return view('admin.badges.form', compact('badge'));
    }

    public function update(Request $request, Badge $badge): RedirectResponse
    {
        $this->abortUnlessOwnedByActiveRegion($request, $badge->region_id);

        $badge->update($this->validated($request, $badge));

        return redirect()->route('admin.badges.index')->with('status', 'Badge updated.');
    }

    public function destroy(Request $request, Badge $badge): RedirectResponse
    {
        $this->abortUnlessOwnedByActiveRegion($request, $badge->region_id);

        if ($badge->offers()->exists()) {
            return back()->with('error', 'This badge is assigned to active promotions and cannot be deleted.');
        }

        $badge->delete();

        return redirect()->route('admin.badges.index')->with('status', 'Badge deleted.');
    }

    private function validated(Request $request, ?Badge $badge = null): array
    {
        /** @var Region $region */
        $region = $request->attributes->get('activeRegion');

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('badges', 'name')->where('region_id', $region->id)->ignore($badge)],
            'color' => ['nullable', 'string', 'regex:/^#[0-9a-fA-F]{6}$/'],
        ]);

        // The <input type="color"> always submits a value (defaults to
        // #000000 if the admin never touched it) — only actually persist it
        // when the "use a custom color" checkbox is on, so badges with no
        // deliberate color choice keep falling back to classes()' presets.
        $data['color'] = $request->boolean('use_custom_color') ? $data['color'] : null;
        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }
}
