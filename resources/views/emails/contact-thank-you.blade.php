<x-mail::message>
# Thanks for reaching out, {{ $contactMessage->name }}!

We've received your message and a member of the {{ $region->name }} team will get back to you as soon as possible — usually within 1–2 business days.

<x-mail::panel>
**Your message**

{{ $contactMessage->message }}
</x-mail::panel>

If you need to add anything else, just reply directly to this email — it'll reach us.

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
