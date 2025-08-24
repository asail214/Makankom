<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Organizer extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'type',
        'profile_img_url',
        'cr_number',
        'cr_document_path',
        'status',
        'email_verified_at',
        'approved_by',
        'approved_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'approved_at' => 'datetime',
        'cr_document_path' => 'array', // Cast JSON to array
    ];

    public function getGuardName(): string
    {
        return 'organizer';
    }

    // Relationships
    public function events()
    {
        return $this->hasMany(Event::class);
    }

    public function brands()
    {
        return $this->hasMany(Brand::class);
    }

    public function approvedBy()
    {
        return $this->belongsTo(Admin::class, 'approved_by');
    }
}