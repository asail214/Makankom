<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Ticket;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReportsController extends Controller
{
    use ApiResponse;

    public function salesSummary(Request $request)
    {
        $this->authorizeAdmin();

        $from = $request->date('from', now()->startOfMonth());
        $to = $request->date('to', now());

        $totals = [
            'orders' => Order::whereBetween('created_at', [$from, $to])->count(),
            'revenue' => (float) Order::where('status', 'confirmed')->whereBetween('created_at', [$from, $to])->sum('total_amount'),
            'payments' => (float) Payment::where('status', 'completed')->whereBetween('created_at', [$from, $to])->sum('amount'),
            'tickets_sold' => Ticket::whereBetween('created_at', [$from, $to])->count(),
        ];

        return $this->jsonSuccess($totals);
    }

    public function platformMetrics()
    {
        $this->authorizeAdmin();
        return $this->jsonSuccess([
            'events_total' => Event::count(),
            'orders_total' => Order::count(),
            'payments_total' => Payment::count(),
            'tickets_total' => Ticket::count(),
        ]);
    }

    protected function authorizeAdmin()
    {
        $admin = Auth::guard('admin')->user();
        abort_unless($admin, 401, 'Unauthorized');
        return $admin;
    }
}


