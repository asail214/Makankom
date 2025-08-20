<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Services\TicketQrService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TicketController extends Controller
{
    use ApiResponse;

    public function __construct(private TicketQrService $qr)
    {
    }

    public function index(Request $request)
    {
        $customer = Auth::guard('customer')->user();
        if (!$customer) {
            return $this->unauthorized();
        }
        $tickets = Ticket::with(['event','ticketType'])
            ->where('customer_id', $customer->id)
            ->latest()
            ->paginate(15);
        return $this->jsonSuccess($tickets);
    }

    public function show(Ticket $ticket)
    {
        $customer = Auth::guard('customer')->user();
        if (!$customer || $ticket->customer_id !== $customer->id) {
            return $this->forbidden('You can only view your own tickets.');
        }
        $ticket->load(['event','ticketType','ticketScans']);
        $payload = $this->qr->generatePayload($ticket);
        return $this->jsonSuccess([
            'ticket' => $ticket,
            'qr_payload' => $payload,
        ]);
    }
}


