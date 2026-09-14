<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\GuardsRegionOwnership;
use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use App\Models\Region;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ContactMessageController extends Controller
{
    use GuardsRegionOwnership;

    public function index(Request $request): View
    {
        /** @var Region $region */
        $region = $request->attributes->get('activeRegion');

        $messages = ContactMessage::where('region_id', $region->id)->latest()->paginate(20);

        return view('admin.contact-messages.index', compact('messages'));
    }

    public function markRead(Request $request, ContactMessage $contactMessage): RedirectResponse
    {
        $this->abortUnlessOwnedByActiveRegion($request, $contactMessage->region_id);

        $contactMessage->update(['is_read' => ! $contactMessage->is_read]);

        return back();
    }

    public function destroy(Request $request, ContactMessage $contactMessage): RedirectResponse
    {
        $this->abortUnlessOwnedByActiveRegion($request, $contactMessage->region_id);

        $contactMessage->delete();

        return back()->with('status', 'Message deleted.');
    }
}
