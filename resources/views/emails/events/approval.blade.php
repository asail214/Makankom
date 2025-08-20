@component('mail::message')
# Event {{ $approved ? 'Approved' : 'Rejected' }}

Event: {{ $event->title }}

@if($approved)
Your event has been approved and published.
@else
Your event has been rejected. Please review and resubmit.
@endif

Thanks,
{{ config('app.name') }}
@endcomponent


