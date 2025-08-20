<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TicketScan extends Model
{
    use HasFactory;

    protected $fillable = [
        'ticket_id',
        'scan_point_id',
        'event_id',
        'scan_type',
        'scanned_at',
        'device_info',
        'location',
        'notes',
    ];

    protected $casts = [
        'scanned_at' => 'datetime',
    ];

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    public function scanPoint(): BelongsTo
    {
        return $this->belongsTo(ScanPoint::class);
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }
}


