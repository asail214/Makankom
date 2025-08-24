<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

class Event extends Model
{
    use HasFactory;

    protected $fillable = [
        'organizer_id',
        'brand_id',
        'category_id',
        'title',
        'slug',
        'description',
        'short_description',
        'start_date',
        'end_date',
        'venue_name',
        'venue_address',
        'latitude',
        'longitude',
        'banner_image',
        'gallery_images',
        'status',
        'is_featured',
        'is_approved',
        'approved_at',
        'approved_by',
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'gallery_images' => 'array',
        'is_featured' => 'boolean',
        'is_approved' => 'boolean',
        'approved_at' => 'datetime',
    ];

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(EventCategory::class, 'category_id');     
    }

    public function ticketTypes(): HasMany
    {
        return $this->hasMany(TicketType::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function scanPoints(): HasMany
    {
        return $this->hasMany(ScanPoint::class);
    }

    public function organizer(): BelongsTo
    {
        return $this->belongsTo(Organizer::class);
    }

    public function approve(Admin $admin): void
    {
        $this->forceFill([
            'is_approved' => true,
            'approved_by' => $admin->id,
            'approved_at' => now(),
            'status' => 'published',
        ])->save();
        // Optionally notify organizer via email
        \Illuminate\Support\Facades\Mail::to($this->organizer->email)->send(new \App\Mail\EventApproval($this, true));
    }

    public function reject(Admin $admin, string $reason = null): void
    {
        $this->forceFill([
            'is_approved' => false,
            'approved_by' => $admin->id,
            'approved_at' => null,
            'status' => 'draft',
        ])->save();
        // Persist reason on related organizer if needed; schema has rejection_reason on organizers
        if ($this->organizer && $reason) {
            $this->organizer->forceFill(['rejection_reason' => $reason])->save();
        }
        \Illuminate\Support\Facades\Mail::to($this->organizer->email)->send(new \App\Mail\EventApproval($this, false));
    }

    public function getTotalTicketsSold(): int
    {
        return (int) $this->ticketTypes()->sum('quantity_sold');
    }
}


