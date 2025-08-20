<?php

namespace App\Mail;

use App\Models\Event;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class EventApproval extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Event $event, public bool $approved = true)
    {
    }

    public function build(): self
    {
        return $this->subject('Event '.($this->approved ? 'Approved' : 'Rejected').': '.$this->event->title)
            ->markdown('emails.events.approval', [
                'event' => $this->event,
                'approved' => $this->approved,
            ]);
    }
}


