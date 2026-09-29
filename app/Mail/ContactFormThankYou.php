<?php

namespace App\Mail;

use App\Models\ContactMessage;
use App\Models\Region;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Sent to the visitor who submitted a region's Contact Us form, confirming
 * receipt. Sent synchronously — see ContactFormReceived's docblock for why,
 * and for why a failed send doesn't fail the form submission itself.
 */
class ContactFormThankYou extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public ContactMessage $contactMessage, public Region $region)
    {
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $replyTo = \App\Models\GeneralSetting::forRegion($this->region->id)->contactNotificationEmailList()[0] ?? null;

        return new Envelope(
            subject: "We've received your message — ".config('app.name'),
            replyTo: $replyTo ? [$replyTo] : [],
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.contact-thank-you',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
