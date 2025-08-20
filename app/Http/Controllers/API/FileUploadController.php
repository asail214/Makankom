<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Event;
use App\Models\Organizer;
use App\Services\UploadService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FileUploadController extends Controller
{
    use ApiResponse;

    public function __construct(private UploadService $uploader)
    {
    }

    public function uploadEventCover(Request $request, Event $event)
    {
        $organizer = Auth::guard('organizer')->user();
        if (!$organizer || $event->organizer_id !== $organizer->id) {
            return $this->forbidden('You can only upload for your own events.');
        }
        $validated = $request->validate([
            'file' => ['required', 'file', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);
        $result = $this->uploader->uploadFile($event, $validated['file'], 'events/'.$event->id, 'event-cover');
        $event->banner_image = $result['url'];
        $event->save();
        return $this->jsonSuccess($result, 'Event cover uploaded');
    }

    public function uploadOrganizerCr(Request $request)
    {
        $organizer = Auth::guard('organizer')->user();
        if (!$organizer) {
            return $this->unauthorized();
        }
        $validated = $request->validate([
            'file' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:8192'],
        ]);
        $result = $this->uploader->uploadFile($organizer, $validated['file'], 'organizers/'.$organizer->id.'/cr', 'cr-document');
        return $this->jsonSuccess($result, 'CR document uploaded');
    }

    public function uploadBrandLogo(Request $request, Brand $brand)
    {
        $organizer = Auth::guard('organizer')->user();
        if (!$organizer || $brand->organizer_id !== $organizer->id) {
            return $this->forbidden('You can only upload for your own brands.');
        }
        $validated = $request->validate([
            'file' => ['required', 'file', 'mimes:jpg,jpeg,png,webp,svg', 'max:4096'],
        ]);
        $result = $this->uploader->uploadFile($brand, $validated['file'], 'brands/'.$brand->id, 'brand-logo');
        $brand->logo = $result['url'];
        $brand->save();
        return $this->jsonSuccess($result, 'Brand logo uploaded');
    }

    public function saveExternal(Request $request)
    {
        $validated = $request->validate([
            'type' => ['required', 'in:event,brand,organizer'],
            'id' => ['required', 'integer'],
            'url' => ['required', 'url'],
            'tag' => ['nullable', 'string'],
        ]);
        $model = match ($validated['type']) {
            'event' => Event::findOrFail($validated['id']),
            'brand' => Brand::findOrFail($validated['id']),
            'organizer' => Organizer::findOrFail($validated['id']),
        };
        $result = $this->uploader->saveExternalUrl($model, $validated['url'], $validated['tag'] ?? null);
        return $this->jsonSuccess($result, 'External media saved');
    }
}


