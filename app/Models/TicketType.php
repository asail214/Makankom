<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Exceptions\BusinessLogicException;

class TicketType extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id',
        'name',
        'description',
        'price',
        'quantity_available',
        'quantity_sold',
        'max_per_order',
        'sale_start_date',
        'sale_end_date',
        'is_active',
        'benefits',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'sale_start_date' => 'datetime',
        'sale_end_date' => 'datetime',
        'is_active' => 'boolean',
        'benefits' => 'array',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function canPurchase(int $quantity): bool
    {
        if (!$this->is_active) {
            return false;
        }
        $now = now();
        if (($this->sale_start_date && $now->lt($this->sale_start_date)) ||
            ($this->sale_end_date && $now->gt($this->sale_end_date))) {
            return false;
        }
        if ($this->max_per_order && $quantity > $this->max_per_order) {
            return false;
        }
        $remaining = $this->quantity_available - $this->quantity_sold;
        return $quantity > 0 && $quantity <= $remaining;
    }

    public function reserveQuantity(int $quantity): void
    {
        if (!$this->canPurchase($quantity)) {
            throw new \RuntimeException('Cannot reserve the requested quantity');
        }
        $this->increment('quantity_sold', $quantity);
    }
}


