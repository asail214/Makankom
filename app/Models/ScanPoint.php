<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class ScanPoint extends Authenticatable
{
    use HasApiTokens, HasFactory;

    protected $fillable = [
        'label',
        'event_id',
        'device_information',
        'location',
        'description',
    ];

    protected $hidden = [
        'remember_token',
    ];

    public function getGuardName(): string
    {
        return 'scan_point';
    }

    // Relationships
    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function ticketScans()
    {
        return $this->hasMany(TicketScan::class);
    }
}