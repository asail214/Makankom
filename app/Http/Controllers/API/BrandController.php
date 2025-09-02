<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class BrandController extends Controller
{
    use ApiResponse;

    /**
     * Display a listing of brands for the authenticated organizer
     */
    public function index()
    {
        $organizer = Auth::guard('organizer')->user();
        if (!$organizer) {
            return $this->unauthorized();
        }

        $brands = Brand::where('organizer_id', $organizer->id)
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return $this->jsonSuccess($brands, 'Brands retrieved successfully');
    }

    /**
     * Store a newly created brand
     */
    public function store(Request $request)
    {
        $organizer = Auth::guard('organizer')->user();
        if (!$organizer) {
            return $this->unauthorized();
        }

        $validated = $request->validate([
            'name' => [
                'required', 
                'string', 
                'max:255',
                Rule::unique('brands')->where(function ($query) use ($organizer) {
                    return $query->where('organizer_id', $organizer->id);
                })
            ],
            'logo' => ['nullable', 'string'], // Allow file paths or URLs
        ]);

        try {
            $brand = Brand::create([
                'organizer_id' => $organizer->id,
                'name' => $validated['name'],
                'logo' => $validated['logo'] ?? null,
            ]);

            return $this->jsonSuccess($brand->load('organizer'), 'Brand created successfully', 201);

        } catch (\Exception $e) {
            return $this->serverError('Failed to create brand: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified brand
     */
    public function show(Brand $brand)
    {
        $organizer = Auth::guard('organizer')->user();
        if (!$organizer) {
            return $this->unauthorized();
        }

        // Check if brand belongs to the authenticated organizer
        if ($brand->organizer_id !== $organizer->id) {
            return $this->forbidden('You can only view your own brands.');
        }

        return $this->jsonSuccess($brand, 'Brand retrieved successfully');
    }

    /**
     * Update the specified brand
     */
    public function update(Request $request, Brand $brand)
    {
        $organizer = Auth::guard('organizer')->user();
        if (!$organizer) {
            return $this->unauthorized();
        }

        // Check if brand belongs to the authenticated organizer
        if ($brand->organizer_id !== $organizer->id) {
            return $this->forbidden('You can only update your own brands.');
        }

        $validated = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'logo' => ['nullable', 'url'],
        ]);

        try {
            $brand->update($validated);
            return $this->jsonSuccess($brand->fresh(), 'Brand updated successfully');

        } catch (\Exception $e) {
            return $this->serverError('Failed to update brand: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified brand
     */
    public function destroy(Request $request, Brand $brand)
    {
        $organizer = Auth::guard('organizer')->user();
        if (!$organizer) {
            return $this->unauthorized();
        }

        // Check if brand belongs to the authenticated organizer
        if ($brand->organizer_id !== $organizer->id) {
            return $this->forbidden('You can only delete your own brands.');
        }

        // Check if brand is being used by any events
        $eventsCount = $brand->events()->count();
        $forceDelete = $request->boolean('force', false);
        
        if ($eventsCount > 0 && !$forceDelete) {
            return $this->validationError([
                'brand' => ['Cannot delete brand that is being used by events. Use force=true to delete anyway.']
            ]);
        }

        try {
            // If force delete, remove brand_id from related events
            if ($forceDelete && $eventsCount > 0) {
                $brand->events()->update(['brand_id' => null]);
            }
            
            $brand->delete();
            $message = $forceDelete && $eventsCount > 0 
                ? "Brand deleted successfully. {$eventsCount} events updated to have no brand."
                : 'Brand deleted successfully';
                
            return $this->jsonSuccess(null, $message);

        } catch (\Exception $e) {
            return $this->serverError('Failed to delete brand: ' . $e->getMessage());
        }
    }

    /**
     * Get brands for dropdown/selection (simplified response)
     */
    public function dropdown()
    {
        $organizer = Auth::guard('organizer')->user();
        if (!$organizer) {
            return $this->unauthorized();
        }

        $brands = Brand::where('organizer_id', $organizer->id)
            ->select('id', 'name', 'logo')
            ->orderBy('name')
            ->get();

        return $this->jsonSuccess($brands, 'Brands for dropdown retrieved successfully');
    }

    /**
     * Get events using a specific brand
     */
    public function usage(Brand $brand)
    {
        $organizer = Auth::guard('organizer')->user();
        if (!$organizer) {
            return $this->unauthorized();
        }

        // Check if brand belongs to the authenticated organizer
        if ($brand->organizer_id !== $organizer->id) {
            return $this->forbidden('You can only view your own brands.');
        }

        $events = $brand->events()->select('id', 'title', 'status', 'start_date')->get();
        $eventsCount = $events->count();

        return $this->jsonSuccess([
            'brand' => $brand,
            'events_count' => $eventsCount,
            'events' => $events,
            'can_delete' => $eventsCount === 0
        ], "Brand is used by {$eventsCount} events");
    }
}