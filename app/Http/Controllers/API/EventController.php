<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\Event;
use App\Models\EventCategory;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EventController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        $query = Event::query()->with(['brand', 'category']);

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->integer('category_id'));
        }
        if ($request->filled('brand_id')) {
            $query->where('brand_id', $request->integer('brand_id'));
        }
        if ($request->filled('organizer_id')) {
            $query->where('organizer_id', $request->integer('organizer_id'));
        }
        if ($request->filled('status')) {
            $query->where('status', $request->string('status')); 
        }
        if ($request->filled('from')) {
            $query->whereDate('start_date', '>=', $request->date('from'));
        }
        if ($request->filled('to')) {
            $query->whereDate('end_date', '<=', $request->date('to'));
        }
        if ($request->filled('q')) {
            $q = $request->string('q');
            $query->where(function ($inner) use ($q) {
                $inner->where('title', 'like', "%{$q}%")
                      ->orWhere('description', 'like', "%{$q}%");
            });
        }

        $events = $query->orderByDesc('start_date')->paginate(15);
        return $this->jsonSuccess($events);
    }

    public function show(Event $event)
    {
        return $this->jsonSuccess($event->load(['brand','category','ticketTypes']));
    }

    public function store(Request $request)
    {
        $organizer = Auth::guard('organizer')->user();
        if (!$organizer) {
            return $this->unauthorized();
        }

        $validated = $request->validate([
            'brand_id' => ['nullable', 'integer', 'exists:brands,id'],
            'category_id' => ['required', 'integer', 'exists:event_categories,id'],
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'unique:events,slug'],
            'description' => ['required', 'string'],
            'short_description' => ['nullable', 'string'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'venue_name' => ['required', 'string', 'max:255'],
            'venue_address' => ['required', 'string'],
            'latitude' => ['nullable', 'numeric'],
            'longitude' => ['nullable', 'numeric'],
            'banner_image' => ['nullable', 'string'],
            'gallery_images' => ['nullable', 'array'],
            'status' => ['nullable', 'in:draft,published,cancelled,completed'],
        ]);

        $event = new Event($validated);
        $event->organizer_id = $organizer->id;
        $event->is_approved = false;
        $event->status = $event->status ?? 'draft';
        $event->save();

        return $this->jsonSuccess($event->fresh(['brand','category']), 'Event created', 201);
    }

    public function update(Request $request, Event $event)
    {
        $organizer = Auth::guard('organizer')->user();
        if (!$organizer || $event->organizer_id !== $organizer->id) {
            return $this->forbidden('You can only update your own events.');
        }

        $validated = $request->validate([
            'brand_id' => ['nullable', 'integer', 'exists:brands,id'],
            'category_id' => ['sometimes', 'integer', 'exists:event_categories,id'],
            'title' => ['sometimes', 'string', 'max:255'],
            'slug' => ['sometimes', 'string', 'max:255', 'unique:events,slug,'.$event->id],
            'description' => ['sometimes', 'string'],
            'short_description' => ['nullable', 'string'],
            'start_date' => ['sometimes', 'date'],
            'end_date' => ['sometimes', 'date', 'after_or_equal:start_date'],
            'venue_name' => ['sometimes', 'string', 'max:255'],
            'venue_address' => ['sometimes', 'string'],
            'latitude' => ['nullable', 'numeric'],
            'longitude' => ['nullable', 'numeric'],
            'banner_image' => ['nullable', 'string'],
            'gallery_images' => ['nullable', 'array'],
            'status' => ['nullable', 'in:draft,published,cancelled,completed'],
        ]);

        $event->fill($validated)->save();
        return $this->jsonSuccess($event->fresh(['brand','category']), 'Event updated');
    }

    public function destroy(Event $event)
    {
        $organizer = Auth::guard('organizer')->user();
        if (!$organizer || $event->organizer_id !== $organizer->id) {
            return $this->forbidden('You can only delete your own events.');
        }
        $event->delete();
        return $this->jsonSuccess(null, 'Event deleted');
    }

    // Not used in API context but present to satisfy resource routes when generated
    public function create()
    {
        return $this->jsonError('Not supported', null, 405);
    }

    public function edit(Event $event)
    {
        return $this->jsonError('Not supported', null, 405);
    }

    public function submitForApproval(Event $event)
    {
        $organizer = Auth::guard('organizer')->user();
        if (!$organizer || $event->organizer_id !== $organizer->id) {
            return $this->forbidden('You can only submit your own events.');
        }
        $event->is_approved = false;
        $event->status = 'draft';
        $event->approved_by = null;
        $event->approved_at = null;
        $event->save();
        return $this->jsonSuccess($event, 'Event submitted for approval');
    }

    public function myEvents(Request $request)
    {
        $organizer = Auth::guard('organizer')->user();
        if (!$organizer) {
            return $this->unauthorized();
        }
        $events = Event::where('organizer_id', $organizer->id)
            ->latest('start_date')
            ->paginate(15);
        return $this->jsonSuccess($events);
    }

    public function categories()
    {
        $categories = EventCategory::query()->orderBy('sort_order')->get();
        return $this->jsonSuccess($categories);
    }
}


