<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Organizer;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrganizerManagementController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        $this->authorizeAdmin();
        $query = Organizer::query();
        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }
        if ($request->filled('q')) {
            $q = $request->string('q');
            $query->where(function ($inner) use ($q) {
                $inner->where('name', 'like', "%{$q}%")
                    ->orWhere('email', 'like', "%{$q}%");
            });
        }
        return $this->jsonSuccess($query->latest()->paginate(20));
    }

    public function show(Organizer $organizer)
    {
        $this->authorizeAdmin();
        return $this->jsonSuccess($organizer);
    }

    public function verify(Organizer $organizer)
    {
        $admin = $this->authorizeAdmin();
        $organizer->update([
            'status' => 'active',
            'approved_by' => $admin->id,
            'approved_at' => now(),
        ]);
        return $this->jsonSuccess($organizer->fresh(), 'Organizer verified and activated');
    }

    public function deactivate(Organizer $organizer)
    {
        $this->authorizeAdmin();
        $organizer->update(['status' => 'inactive']);
        return $this->jsonSuccess($organizer->fresh(), 'Organizer deactivated');
    }

    protected function authorizeAdmin()
    {
        $admin = Auth::guard('admin')->user();
        abort_unless($admin, 401, 'Unauthorized');
        return $admin;
    }
}


