<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Brand extends Model
{
    use HasFactory;

    protected $fillable = [
        'organizer_id',
        'name',
        'slug',
        'description',
        'logo',
        'website',
        'email',
        'phone',
        'address',
        'is_active',
    ];

    public function events(): HasMany
    {
        return $this->hasMany(Event::class);
    }

    public function organizer(): BelongsTo
    {
        // Requires brands.organizer_id (FK) to exist
        return $this->belongsTo(Organizer::class);
    }
}


