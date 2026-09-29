<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Mail\ContactFormReceived;
use App\Mail\ContactFormThankYou;
use App\Models\ContactMessage;
use App\Models\ContactPageAgenda;
use App\Models\GeneralSetting;
use App\Models\Region;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use Throwable;

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

        $contactMessage = ContactMessage::create($data);

        // Sent synchronously (by request — no queue worker/cron is running
        // on the host to drain a queued mail). Each send is isolated in its
        // own try/catch: the message is already saved, and the visitor
        // already got their "thanks" regardless of whether SMTP is
        // reachable, so a mail failure (unconfigured/misconfigured SMTP, a
        // transient outage) must not turn into a 500 for the form
        // submission itself — it's logged instead.
        $notifyEmails = GeneralSetting::forRegion($region->id)->contactNotificationEmailList();
        if ($notifyEmails) {
            try {
                Mail::to($notifyEmails)->send(new ContactFormReceived($contactMessage, $region));
            } catch (Throwable $e) {
                Log::error('Failed to send contact form admin notification email.', ['exception' => $e]);
            }
        }

        try {
            Mail::to($contactMessage->email)->send(new ContactFormThankYou($contactMessage, $region));
        } catch (Throwable $e) {
            Log::error('Failed to send contact form thank-you email.', ['exception' => $e]);
        }

        return back()->with('status', "Thanks for reaching out! We'll get back to you soon.");
    }
}
