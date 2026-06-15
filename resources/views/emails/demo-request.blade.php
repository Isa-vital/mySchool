<x-mail::message>
# New Demo Request

A new demo request has just been submitted from the website.

**School:** {{ $demoRequest->school_name }}
**Contact:** {{ $demoRequest->contact_name }}
**WhatsApp:** {{ $demoRequest->whatsapp }}
**Submitted:** {{ $demoRequest->created_at->format('d M Y, H:i') }}

<x-mail::button :url="'https://wa.me/' . preg_replace('/[^0-9]/', '', $demoRequest->whatsapp)">
Reply on WhatsApp
</x-mail::button>

Please follow up within one working day.

Regards,
{{ config('app.name') }}
</x-mail::message>
