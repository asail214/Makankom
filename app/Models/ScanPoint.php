<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Str;

class ScanPoint extends Authenticatable
{
    use HasApiTokens, HasFactory;

    protected $fillable = [
        'label',
        'event_id',
        'device_information',
        'token',
        'location',
        'status'
    ];

    protected $hidden = [
        'remember_token',
        'token'
    ];

    protected $casts = [
        'status' => 'string',
    ];

    // Generate simple token when creating scan point
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($scanPoint) {
            if (empty($scanPoint->token)) {
                $scanPoint->token = 'SP_' . strtoupper(Str::random(32));
            }
        });
    }

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

    // Check if scan point can scan tickets
    public function canScan(): bool
    {
        return $this->status === 'active' && $this->event && $this->event->is_approved;
    }
}