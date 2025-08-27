<x-mail::message>
# Event {{ $approved ? 'Approved' : 'Rejected' }}

Hello {{ $event->organizer->name }},

@if($approved)
Great news! Your event "{{ $event->title }}" has been approved and is now live on our platform.

**Event Details:**
- **Title:** {{ $event->title }}
- **Date:** {{ $event->start_date->format('F j, Y \a\t g:i A') }}
- **Venue:** {{ $event->venue_name }}

<x-mail::button :url="config('app.url') . '/events/' . $event->slug">
View Your Event
</x-mail::button>
@else
Unfortunately, your event "{{ $event->title }}" has been rejected.

Please review our event guidelines and resubmit if needed. You can contact our support team for more information.
@endif

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>