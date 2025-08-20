<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EventManagementController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        $this->authorizeAdmin();

        $query = Event::query()->with(['brand', 'category', 'organizer']);

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }
        if ($request->filled('organizer_id')) {
            $query->where('organizer_id', $request->integer('organizer_id'));
        }
        if ($request->filled('from')) {
            $query->whereDate('start_date', '>=', $request->date('from'));
        }
        if ($request->filled('to')) {
            $query->whereDate('end_date', '<=', $request->date('to'));
        }

        $events = $query->latest('start_date')->paginate(20);
        return $this->jsonSuccess($events);
    }

    public function pending(Request $request)
    {
        $this->authorizeAdmin();

        $events = Event::with(['brand','category','organizer'])
            ->where('is_approved', false)
            ->orderBy('created_at', 'desc')
            ->paginate(20);
        return $this->jsonSuccess($events);
    }

    public function show(Event $event)
    {
        $this->authorizeAdmin();
        return $this->jsonSuccess($event->load(['brand','category','organizer','ticketTypes']));
    }

    public function approve(Event $event)
    {
        $admin = $this->authorizeAdmin();
        $event->approve($admin);
        return $this->jsonSuccess($event->fresh(), 'Event approved');
    }

    public function reject(Request $request, Event $event)
    {
        $admin = $this->authorizeAdmin();
        $request->validate(['reason' => ['nullable', 'string', 'max:1000']]);
        $event->reject($admin, $request->input('reason'));
        return $this->jsonSuccess($event->fresh(), 'Event rejected');
    }

    protected function authorizeAdmin()
    {
        $admin = Auth::guard('admin')->user();
        abort_unless($admin, 401, 'Unauthorized');
        return $admin;
    }
}


