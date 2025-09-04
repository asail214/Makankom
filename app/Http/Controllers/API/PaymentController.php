<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use App\Services\PaymentService;
use App\Mail\OrderConfirmation;
use Illuminate\Support\Facades\Mail;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PaymentController extends Controller
{
    use ApiResponse;

    public function __construct(private PaymentService $paymentService)
    {
    }

    public function index()
    {
        $customer = Auth::guard('customer')->user();
        if (!$customer) {
            return $this->unauthorized();
        }
        $payments = Payment::where('customer_id', $customer->id)->latest()->paginate(20);
        return $this->jsonSuccess($payments);
    }

    public function store(Request $request)
    {
        $customer = Auth::guard('customer')->user();
        if (!$customer) {
            return $this->unauthorized();
        }
        
        $validated = $request->validate([
            'order_id' => ['required', 'integer', 'exists:orders,id'],
            'amount' => ['required', 'numeric', 'min:0'],
            'payment_method' => ['required', 'in:credit_card,debit_card,bank_transfer,cash,digital_wallet'],
            'transaction_id' => ['nullable', 'string'],
            'payment_details' => ['nullable', 'array'],
        ]);

        $order = Order::findOrFail($validated['order_id']);
        if ($order->customer_id !== $customer->id) {
            return $this->forbidden('You can only pay for your own orders.');
        }

        // Create payment record
        $payment = Payment::create([
            'payment_reference' => strtoupper(uniqid('PAY')),
            'order_id' => $order->id,
            'customer_id' => $customer->id,
            'amount' => $validated['amount'],
            'payment_method' => $validated['payment_method'],
            'status' => 'completed', // Auto-complete for testing
            'transaction_id' => $validated['transaction_id'] ?? strtoupper(uniqid('TXN')),
            'payment_details' => $validated['payment_details'] ?? null,
            'paid_at' => now(),
        ]);

        // Mark order as paid and generate tickets
        $order->markAsPaid($payment);
        
        // Queue order confirmation email
        \App\Jobs\SendOrderConfirmationEmail::dispatch($order);
        
        // Queue ticket generation
        $order->generateTickets();

        return $this->jsonSuccess($payment, 'Payment recorded successfully', 201);
    }
}


