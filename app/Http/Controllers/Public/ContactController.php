<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use App\Models\ContactPageAgenda;
use App\Models\Region;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ContactController extends Controller
{
    public function store(Request $request, Region $region): RedirectResponse
    {
        $agendaValues = ContactPageAgenda::where('region_id', $region->id)->where('is_active', true)->pluck('value');

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'category' => ['required', Rule::in($agendaValues)],
            'message' => ['required', 'string', 'max:5000'],
        ]);

        $data['region_id'] = $region->id;

        ContactMessage::create($data);

        return back()->with('status', "Thanks for reaching out! We'll get back to you soon.");
    }
}
