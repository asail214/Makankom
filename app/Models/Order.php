<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_number',
        'customer_id',
        'event_id',
        'subtotal',
        'tax_amount',
        'discount_amount',
        'total_amount',
        'status',
        'notes',
        'confirmed_at',
        'cancelled_at',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'confirmed_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function tickets()
    {
        return $this->hasManyThrough(
            Ticket::class,
            OrderItem::class,
            'order_id',       // Foreign key on order_items table...
            'order_item_id',  // Foreign key on tickets table...
            'id',             // Local key on orders table...
            'id'              // Local key on order_items table...
        );
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function calculateTotal(): void
    {
        $subtotal = $this->orderItems->sum(function (OrderItem $item) {
            return (float) $item->total_price;
        });
        $this->subtotal = $subtotal;
        $this->total_amount = $subtotal + (float) $this->tax_amount - (float) $this->discount_amount;
        $this->save();
    }

    public function markAsPaid(Payment $payment): void
    {
        if ($this->status !== 'confirmed') {
            $this->status = 'confirmed';
            $this->confirmed_at = now();
            $this->save();
        }
        // Payment assumed to be already created; enforce relation
        if ($payment->order_id !== $this->id) {
            $payment->order()->associate($this);
            $payment->save();
        }
    }

    public function generateTickets(): void
    {
        $this->loadMissing('orderItems.ticketType');
        foreach ($this->orderItems as $item) {
            for ($i = 0; $i < $item->quantity; $i++) {
                Ticket::create([
                    'ticket_number' => strtoupper(Str::random(12)),
                    'order_item_id' => $item->id,
                    'customer_id' => $this->customer_id,
                    'event_id' => $this->event_id,
                    'ticket_type_id' => $item->ticket_type_id,
                    'status' => 'active',
                    'qr_code' => strtoupper(Str::uuid()->toString()),
                ]);
            }
        }
    }
}


