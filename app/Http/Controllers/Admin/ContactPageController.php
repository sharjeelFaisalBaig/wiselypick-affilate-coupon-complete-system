<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\GuardsRegionOwnership;
use App\Http\Controllers\Controller;
use App\Models\ContactPageAgenda;
use App\Models\Region;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class ContactPageController extends Controller
{
    use GuardsRegionOwnership;

    public function edit(Request $request): View
    {
        /** @var Region $region */
        $region = $request->attributes->get('activeRegion');

        $agendas = ContactPageAgenda::where('region_id', $region->id)->orderBy('sort_order')->get();

        return view('admin.contact-page.edit', compact('agendas'));
    }

    public function storeAgenda(Request $request): RedirectResponse
    {
        /** @var Region $region */
        $region = $request->attributes->get('activeRegion');

        $data = $request->validate([
            'value' => ['required', 'string', 'max:100', 'alpha_dash'],
            'label' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:500'],
        ]);
        $data['region_id'] = $region->id;
        $data['sort_order'] = (ContactPageAgenda::where('region_id', $region->id)->max('sort_order') ?? 0) + 1;

        ContactPageAgenda::create($data);

        return back()->with('status', 'Agenda option added.');
    }

    public function updateAgenda(Request $request, ContactPageAgenda $agenda): RedirectResponse
    {
        $this->abortUnlessOwnedByActiveRegion($request, $agenda->region_id);

        $data = $request->validate([
            'label' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:500'],
        ]);
        $data['is_active'] = $request->boolean('is_active');

        $agenda->update($data);

        return back()->with('status', 'Agenda option updated.');
    }

    public function destroyAgenda(Request $request, ContactPageAgenda $agenda): RedirectResponse
    {
        $this->abortUnlessOwnedByActiveRegion($request, $agenda->region_id);

        if (ContactPageAgenda::where('region_id', $agenda->region_id)->where('is_active', true)->count() <= 1) {
            return back()->with('error', 'At least one active agenda option must remain.');
        }

        $agenda->delete();

        return back()->with('status', 'Agenda option removed.');
    }

    public function reorderAgendas(Request $request): Response
    {
        /** @var Region $region */
        $region = $request->attributes->get('activeRegion');
        $ids = $request->validate(['ids' => ['required', 'array']])['ids'];

        foreach ($ids as $index => $id) {
            ContactPageAgenda::where('id', $id)->where('region_id', $region->id)->update(['sort_order' => $index + 1]);
        }

        return response()->noContent();
    }
}
