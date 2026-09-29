<?php

namespace App\Mail;

use App\Models\ContactMessage;
use App\Models\Region;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Sent to a region's configured notification recipients (item 3 —
 * GeneralSetting::contactNotificationEmailList()) whenever that region's
 * Contact Us form is submitted. Sent synchronously (by request, no queue
 * worker running on the host) — ContactController wraps the send in its own
 * try/catch so a slow/unreachable SMTP server fails the email, not the
 * visitor's form submission.
 */
class ContactFormReceived extends Mailable
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
        return new Envelope(
            subject: "New Contact Us message — {$this->region->name} ({$this->contactMessage->category})",
            replyTo: [new Address($this->contactMessage->email, $this->contactMessage->name)],
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.contact-received',
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
