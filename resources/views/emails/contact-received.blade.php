<x-mail::message>
# New Contact Us message

A visitor submitted the Contact Us form for **{{ $region->name }}**.

<x-mail::table>
| | |
|:---|:---|
| **Name** | {{ $contactMessage->name }} |
| **Email** | {{ $contactMessage->email }} |
| **Topic** | {{ $contactMessage->category }} |
| **Submitted** | {{ $contactMessage->created_at->format('F j, Y g:i A') }} |
</x-mail::table>

**Message:**

{{ $contactMessage->message }}

<x-mail::button :url="route('admin.contact-messages.index')">
View in Admin Panel
</x-mail::button>

You can reply directly to this email to respond to {{ $contactMessage->name }}.

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
