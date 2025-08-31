<?php

use Illuminate\Support\Facades\Route;

// welcome route
Route::get('/', function () {
    return response()->json([
        'message' => 'Welcome to Makankom API',
        'version' => '1.0.0',
        'status' => 'active',
        'database' => 'connected',
        'endpoints' => [
            'health' => '/api/health/database',
            'events' => '/api/v1/events',
            'categories' => '/api/v1/event-categories'
        ]
    ]);
});

Route::get('/email-preview/{type}', function ($type) {
    $event = \App\Models\Event::first();
    if (!$event) {
        return 'No events found. Run: php artisan db:seed';
    }
    
    switch ($type) {
        case 'approval':
            return new \App\Mail\EventApproval($event, true);
        case 'rejection':
            return new \App\Mail\EventApproval($event, false);
        case 'confirmation':
        case 'tickets':
            // Create a mock order with all required relationships
            $customer = \App\Models\Customer::first();
            if (!$customer) {
                return 'No customers found. Run: php artisan db:seed';
            }
            
            $order = new \App\Models\Order([
                'order_number' => 'ORD123456',
                'total_amount' => 150.00,
                'status' => 'confirmed'
            ]);
            $order->customer = $customer;
            $order->event = $event;
            
            // Create mock order items
            $orderItem = new \App\Models\OrderItem([
                'quantity' => 2,
                'unit_price' => 75.00,
                'total_price' => 150.00
            ]);
            
            // Create mock ticket type
            $ticketType = new \App\Models\TicketType([
                'name' => 'General Admission',
                'price' => 75.00
            ]);
            $orderItem->ticketType = $ticketType;
            
            $order->setRelation('orderItems', collect([$orderItem]));
            
            if ($type === 'tickets') {
                // Create mock tickets
                $ticket = new \App\Models\Ticket([
                    'ticket_number' => 'TKT123456',
                    'status' => 'active'
                ]);
                $ticket->ticketType = $ticketType;
                
                $order->setRelation('tickets', collect([$ticket]));
                return new \App\Mail\TicketDelivery($order);
            }
            
            return new \App\Mail\OrderConfirmation($order);
            
        default:
            abort(404);
    }
})->where('type', 'approval|rejection|confirmation|tickets');


// Test job dispatch route
Route::get('/test-jobs', function () {
    $order = \App\Models\Order::first();
    if ($order) {
        \App\Jobs\SendOrderConfirmationEmail::dispatch($order);
        return 'Order confirmation email job dispatched';
    }
    return 'No orders found';
});

// Test rate limiting
Route::get('/test-rate-limit', function () {
    return response()->json([
        'message' => 'Rate limit test successful',
        'timestamp' => now()->toISOString()
    ]);
})->middleware('throttle:3,1'); // 3 requests per minute