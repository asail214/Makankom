<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ticket extends Model
{
    use HasFactory;

    protected $fillable = [
        'ticket_number',
        'order_item_id',
        'customer_id',
        'event_id',
        'ticket_type_id',
        'status',
        'qr_code',
        'used_at',
        'used_by_scan_point',
    ];

    protected $casts = [
        'used_at' => 'datetime',
    ];

    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function ticketType(): BelongsTo
    {
        return $this->belongsTo(TicketType::class);
    }

    public function ticketScans(): HasMany
    {
        return $this->hasMany(TicketScan::class);
    }

    public function canBeScan(): bool
    {
        // If ticket is cancelled or refunded, cannot scan
        if (in_array($this->status, ['cancelled', 'refunded'])) {
            return false;
        }
        
        // Check if ticket type allows re-entry
        $ticketType = $this->ticketType;
        
        // If ticket allows re-entry, it can always be scanned (even if used before)
        if ($ticketType && $ticketType->benefits && 
            is_array($ticketType->benefits) && 
            in_array('re-entry', $ticketType->benefits)) {
            return true;
        }
        
        // For regular tickets, can only scan if not used
        return $this->status === 'active';
    }

    public function scan(ScanPoint $scanPoint, string $scanType = 'entry', ?string $deviceInfo = null, ?string $location = null, ?string $notes = null): TicketScan
    {
        if (!$this->canBeScan()) {
            throw new \RuntimeException('Ticket cannot be scanned.');
        }

        // Check if scan point can scan
        if (!$scanPoint->canScan()) {
            throw new \RuntimeException('Scan point is not authorized to scan tickets.');
        }

        $scan = TicketScan::create([
            'ticket_id' => $this->id,
            'scan_point_id' => $scanPoint->id,
            'event_id' => $this->event_id,
            'scan_type' => $scanType,
            'scanned_at' => now(),
            'device_info' => $deviceInfo,
            'location' => $location,
            'notes' => $notes,
        ]);

        // Only mark as used if it's not a re-entry ticket
        $ticketType = $this->ticketType;
        $allowsReentry = $ticketType && 
                        $ticketType->benefits && 
                        is_array($ticketType->benefits) && 
                        in_array('re-entry', $ticketType->benefits);

        if (!$allowsReentry && $this->status === 'active') {
            $this->forceFill([
                'status' => 'used',
                'used_at' => $scan->scanned_at,
                'used_by_scan_point' => $scanPoint->id,
            ])->save();
        }

        return $scan;
    }
}


