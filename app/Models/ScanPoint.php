<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class ScanPoint extends Authenticatable
{
    use HasApiTokens, HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'location',
        'description',
        'status',
        'event_id',
        'device_information',
        'location_details',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'remember_token',
    ];

    /**
     * Get the guard name for the model.
     */
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
        return $this->hasMany(\App\Models\TicketScan::class);
    }
}
