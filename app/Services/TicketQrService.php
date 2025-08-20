<?php

namespace App\Services;

use App\Models\Ticket;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class TicketQrService
{
    public function generatePayload(Ticket $ticket): array
    {
        $payload = [
            'v' => config('qr.version', 'v1'),
            't' => $ticket->ticket_number,
            'e' => $ticket->event_id,
            'c' => $ticket->customer_id,
            'ts' => now()->timestamp,
        ];
        $payload['sig'] = $this->sign($payload);
        return $payload;
    }

    public function sign(array $payload): string
    {
        $secret = config('qr.secret');
        $algo = config('qr.algo', 'sha256');
        $data = json_encode($payload, JSON_UNESCAPED_SLASHES);
        return hash_hmac($algo, $data, $secret);
    }

    public function verify(array $payload): bool
    {
        if (!isset($payload['sig'])) {
            return false;
        }
        $sig = $payload['sig'];
        $copy = $payload;
        unset($copy['sig']);
        return hash_equals($sig, $this->sign($copy));
    }
}


