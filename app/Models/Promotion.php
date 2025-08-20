<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Promotion extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'description',
        'type',
        'value',
        'max_usage',
        'used_count',
        'valid_from',
        'valid_until',
        'is_active',
        'applicable_events',
        'applicable_ticket_types',
        'minimum_order_amount',
    ];

    protected $casts = [
        'value' => 'decimal:2',
        'valid_from' => 'datetime',
        'valid_until' => 'datetime',
        'is_active' => 'boolean',
        'applicable_events' => 'array',
        'applicable_ticket_types' => 'array',
        'minimum_order_amount' => 'decimal:2',
    ];
}


