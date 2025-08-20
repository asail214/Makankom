<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Wishlist;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WishlistController extends Controller
{
    use ApiResponse;

    public function index()
    {
        $customer = Auth::guard('customer')->user();
        if (!$customer) {
            return $this->unauthorized();
        }
        $items = Wishlist::with('event')->where('customer_id', $customer->id)->latest()->paginate(20);
        return $this->jsonSuccess($items);
    }

    public function store(Request $request)
    {
        $customer = Auth::guard('customer')->user();
        if (!$customer) {
            return $this->unauthorized();
        }
        $validated = $request->validate([
            'event_id' => ['required', 'integer', 'exists:events,id'],
        ]);
        $exists = Wishlist::where('customer_id', $customer->id)->where('event_id', $validated['event_id'])->exists();
        if ($exists) {
            return $this->validationError(['event_id' => ['Already in wishlist.']]);
        }
        $item = Wishlist::create([
            'customer_id' => $customer->id,
            'event_id' => $validated['event_id'],
            'added_at' => now(),
        ]);
        return $this->jsonSuccess($item->load('event'), 'Added to wishlist', 201);
    }

    public function destroy(Wishlist $wishlist)
    {
        $customer = Auth::guard('customer')->user();
        if (!$customer || $wishlist->customer_id !== $customer->id) {
            return $this->forbidden('You can only remove your own wishlist items.');
        }
        $wishlist->delete();
        return $this->jsonSuccess(null, 'Removed from wishlist');
    }

    // Resource route placeholders to satisfy Route::resource
    public function show(Wishlist $wishlist)
    {
        $customer = Auth::guard('customer')->user();
        if (!$customer || $wishlist->customer_id !== $customer->id) {
            return $this->forbidden('You can only view your own wishlist items.');
        }
        return $this->jsonSuccess($wishlist->load('event'));
    }

    public function update(Request $request, Wishlist $wishlist)
    {
        return $this->jsonError('Not supported', null, 405);
    }

    public function create()
    {
        return $this->jsonError('Not supported', null, 405);
    }

    public function edit(Wishlist $wishlist)
    {
        return $this->jsonError('Not supported', null, 405);
    }
}


