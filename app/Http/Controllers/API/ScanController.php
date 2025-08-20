<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\ScanPoint;
use App\Models\Ticket;
use App\Traits\ApiResponse;
use App\Services\TicketQrService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ScanController extends Controller
{
    use ApiResponse;

    public function __construct(private TicketQrService $qr)
    {
    }

    public function scanTicket(Request $request)
    {
        $scanPoint = Auth::guard('scan_point')->user();
        if (!$scanPoint) {
            return $this->unauthorized();
        }
        $validated = $request->validate([
            'qr_code' => ['required', 'string'],
            'scan_type' => ['nullable', 'in:entry,exit'],
            'device_info' => ['nullable', 'string'],
            'location' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
        ]);
        $ticket = Ticket::where('qr_code', $validated['qr_code'])->first();
        if (!$ticket) {
            return $this->notFound('Ticket not found');
        }
        try {
            $scan = $ticket->scan($scanPoint, $validated['scan_type'] ?? 'entry', $validated['device_info'] ?? null, $validated['location'] ?? null, $validated['notes'] ?? null);
        } catch (\Throwable $e) {
            return $this->validationError(['ticket' => [$e->getMessage()]]);
        }
        return $this->jsonSuccess($scan, 'Ticket scanned');
    }

    public function validateTicket(Request $request)
    {
        $scanPoint = Auth::guard('scan_point')->user();
        if (!$scanPoint) {
            return $this->unauthorized();
        }
        $validated = $request->validate([
            'qr_code' => ['required_without:payload', 'string'],
            'payload' => ['required_without:qr_code', 'array'],
        ]);
        if (isset($validated['payload']) && !$this->qr->verify($validated['payload'])) {
            return $this->validationError(['payload' => ['Invalid signature']]);
        }
        $ticket = isset($validated['qr_code'])
            ? Ticket::where('qr_code', $validated['qr_code'])->first()
            : Ticket::where('ticket_number', $validated['payload']['t'] ?? '')->first();
        if (!$ticket) {
            return $this->notFound('Ticket not found');
        }
        return $this->jsonSuccess([
            'valid' => $ticket->canBeScan(),
            'status' => $ticket->status,
        ]);
    }

    public function scanHistory(Request $request)
    {
        $scanPoint = Auth::guard('scan_point')->user();
        if (!$scanPoint) {
            return $this->unauthorized();
        }
        $history = $scanPoint->event
            ? $scanPoint->event->load(['orders', 'ticketTypes']) // eager context
            : null;
        $scans = $scanPoint->hasMany(\App\Models\TicketScan::class)->latest()->paginate(20);
        return $this->jsonSuccess($scans);
    }

    public function scanStats(Event $event)
    {
        $scanPoint = Auth::guard('scan_point')->user();
        if (!$scanPoint) {
            return $this->unauthorized();
        }
        $totalTickets = $event->ticketTypes()->sum('quantity_sold');
        $usedTickets = \App\Models\Ticket::where('event_id', $event->id)->where('status', 'used')->count();
        return $this->jsonSuccess([
            'total_tickets' => $totalTickets,
            'used_tickets' => $usedTickets,
            'unused_tickets' => max(0, $totalTickets - $usedTickets),
        ]);
    }
}


