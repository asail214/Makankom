<?php

namespace App\Jobs;

use App\Models\Event;
use App\Mail\EventApproval;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendEventApprovalEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public Event $event,
        public bool $approved
    ) {}

    public function handle(): void
    {
        Mail::to($this->event->organizer->email)
            ->send(new EventApproval($this->event, $this->approved));
    }

    public function failed(\Throwable $exception): void
    {
        \Log::error('Event approval email failed', [
            'event_id' => $this->event->id,
            'approved' => $this->approved,
            'error' => $exception->getMessage()
        ]);
    }
}