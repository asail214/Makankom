<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\TicketType;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        $customer = Auth::guard('customer')->user();
        if (!$customer) {
            return $this->unauthorized();
        }
        $orders = Order::with(['orderItems.ticketType', 'payments'])
            ->where('customer_id', $customer->id)
            ->latest()
            ->paginate(15);
        return $this->jsonSuccess($orders);
    }

    public function store(Request $request)
    {
        $customer = Auth::guard('customer')->user();
        if (!$customer) {
            return $this->unauthorized();
        }

        $validated = $request->validate([
            'event_id' => ['required', 'integer', 'exists:events,id'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.ticket_type_id' => ['required', 'integer', 'exists:ticket_types,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
        ]);

        $order = DB::transaction(function () use ($customer, $validated) {
            $order = new Order([
                'order_number' => strtoupper(uniqid('ORD')),
                'customer_id' => $customer->id,
                'event_id' => $validated['event_id'],
                'subtotal' => 0,
                'tax_amount' => 0,
                'discount_amount' => 0,
                'total_amount' => 0,
                'status' => 'pending',
            ]);
            $order->save();

            $subtotal = 0;
            foreach ($validated['items'] as $line) {
                $ticketType = TicketType::lockForUpdate()->findOrFail($line['ticket_type_id']);
                if ($ticketType->event_id !== (int) $validated['event_id']) {
                    abort(422, 'Ticket type does not belong to the selected event');
                }
                if (!$ticketType->canPurchase($line['quantity'])) {
                    abort(422, 'Requested quantity not available for '.$ticketType->name);
                }
                $ticketType->reserveQuantity($line['quantity']);

                $unitPrice = (float) $ticketType->price;
                $totalPrice = $unitPrice * (int) $line['quantity'];
                $subtotal += $totalPrice;

                OrderItem::create([
                    'order_id' => $order->id,
                    'ticket_type_id' => $ticketType->id,
                    'quantity' => (int) $line['quantity'],
                    'unit_price' => $unitPrice,
                    'total_price' => $totalPrice,
                ]);
            }

            $order->subtotal = $subtotal;
            $order->total_amount = $subtotal; // apply discounts/tax later
            $order->save();

            return $order->fresh(['orderItems.ticketType']);
        });

        return $this->jsonSuccess($order, 'Order created', 201);
    }

    public function show(Order $order)
    {
        $customer = Auth::guard('customer')->user();
        if (!$customer || $order->customer_id !== $customer->id) {
            return $this->forbidden('You can only view your own orders.');
        }
        return $this->jsonSuccess($order->load(['orderItems.ticketType', 'payments']));
    }

    public function cancel(Order $order)
    {
        $customer = Auth::guard('customer')->user();
        if (!$customer || $order->customer_id !== $customer->id) {
            return $this->forbidden('You can only cancel your own orders.');
        }
        if ($order->status !== 'pending') {
            return $this->validationError(['status' => ['Only pending orders can be cancelled.']]);
        }
        $order->status = 'cancelled';
        $order->cancelled_at = now();
        $order->save();
        return $this->jsonSuccess($order, 'Order cancelled');
    }

    public function requestRefund(Order $order)
    {
        $customer = Auth::guard('customer')->user();
        if (!$customer || $order->customer_id !== $customer->id) {
            return $this->forbidden('You can only request refunds for your own orders.');
        }
        if (!in_array($order->status, ['confirmed'])) {
            return $this->validationError(['status' => ['Refunds can only be requested for confirmed orders.']]);
        }
        // Placeholder: integrate with payment gateway / admin review workflow
        return $this->jsonSuccess(null, 'Refund request submitted');
    }

    public function summary(Request $request)
    {
        $validated = $request->validate([
            'items' => ['required', 'array', 'min:1'],
            'items.*.ticket_type_id' => ['required', 'integer', 'exists:ticket_types,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
        ]);

        $subtotal = 0;
        foreach ($validated['items'] as $line) {
            $ticketType = TicketType::findOrFail($line['ticket_type_id']);
            $subtotal += (float) $ticketType->price * (int) $line['quantity'];
        }
        $total = $subtotal; // apply discounts/tax later
        return $this->jsonSuccess([
            'subtotal' => $subtotal,
            'tax' => 0,
            'discount' => 0,
            'total' => $total,
        ]);
    }

    // Resource placeholders to satisfy Route::resource
    public function create()
    {
        return $this->jsonError('Not supported', null, 405);
    }

    public function edit(Order $order)
    {
        return $this->jsonError('Not supported', null, 405);
    }

    public function update(Request $request, Order $order)
    {
        return $this->jsonError('Not supported', null, 405);
    }

    public function destroy(Order $order)
    {
        return $this->jsonError('Not supported', null, 405);
    }
}


