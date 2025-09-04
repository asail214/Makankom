<?php

namespace App\Http\Controllers\ScanPoint;

use App\Http\Controllers\Controller;
use App\Services\ScanPoint\AuthService;
use App\Traits\ApiResponse;
use App\Models\Event;
use App\Models\ScanPoint;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    use ApiResponse;

    protected AuthService $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    /**
     * Create a new scan point
     */
    public function create(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'label' => 'required|string|max:255',
            'event_id' => 'required|integer',
            'device_information' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator->errors());
        }

        $result = $this->authService->create($request->all());

        if ($result['success']) {
            return $this->jsonSuccess($result['data'], $result['message'], 201);
        }

        return $this->jsonError($result['message'], null, 400);
    }

    /**
     * Login scan point using token
     */
    public function loginWithToken(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required|string',
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator->errors());
        }

        $result = $this->authService->loginWithToken($request->token);

        if ($result['success']) {
            return $this->jsonSuccess($result['data'], $result['message']);
        }

        return $this->jsonError($result['message'], null, 401);
    }

    /**
     * Login scan point using ID and token (as per Postman)
     */
    public function login(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'scan_point_id' => 'required|integer',
            'token' => 'required|string',
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator->errors());
        }

        $result = $this->authService->login($request->all());

        if ($result['success']) {
            return $this->jsonSuccess($result['data'], $result['message']);
        }

        return $this->jsonError($result['message'], null, 401);
    }

    /**
     * Logout scan point
     */
    public function logout(): JsonResponse
    {
        $result = $this->authService->logout();

        if ($result['success']) {
            return $this->jsonSuccess(null, $result['message']);
        }

        return $this->serverError($result['message']);
    }

    /**
     * Get scan point profile
     */
    public function profile(): JsonResponse
    {
        $result = $this->authService->profile();

        if ($result['success']) {
            return $this->jsonSuccess($result['data'], $result['message']);
        }

        return $this->notFound($result['message']);
    }

    /**
     * Update scan point profile
     */
    public function updateProfile(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'label' => 'sometimes|string|max:255',
            'location' => 'nullable|string|max:500',
            'device_information' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator->errors());
        }

        $result = $this->authService->updateProfile($request->all());

        if ($result['success']) {
            return $this->jsonSuccess($result['data'], $result['message']);
        }

        return $this->jsonError($result['message'], null, 400);
    }

    /**
     * Generate new token for scan point
     */
    public function generateNewToken(): JsonResponse
    {
        $result = $this->authService->generateNewToken();

        if ($result['success']) {
            return $this->jsonSuccess($result['data'], $result['message']);
        }

        return $this->serverError($result['message']);
    }


    /**
     * Delete a scan point (only by organizer who owns the event)
     */
    public function destroy(Request $request, $scanPointId): JsonResponse
    {
        $validator = Validator::make(['scan_point_id' => $scanPointId], [
            'scan_point_id' => 'required|integer|exists:scan_points,id',
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator->errors());
        }

        $result = $this->authService->destroy($scanPointId);

        if ($result['success']) {
            return $this->jsonSuccess(null, $result['message']);
        }

        return $this->jsonError($result['message'], null, 400);
    }

    /**
     * Deactivate a scan point
     */
    public function deactivate(Request $request, $scanPointId): JsonResponse
    {
        $validator = Validator::make(['scan_point_id' => $scanPointId], [
            'scan_point_id' => 'required|integer|exists:scan_points,id',
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator->errors());
        }

        $result = $this->authService->deactivate($scanPointId);

        if ($result['success']) {
            return $this->jsonSuccess($result['data'], $result['message']);
        }

        return $this->jsonError($result['message'], null, 400);
    }

    /**
     * List scan points for an event (for organizers)
     */
    public function index($eventId): JsonResponse
    {
        // Get the authenticated organizer
        $organizer = Auth::guard('organizer')->user();
        if (!$organizer) {
            return $this->unauthorized('Organizer authentication required');
        }

        // Verify the event exists and belongs to this organizer
        $event = \App\Models\Event::where('id', $eventId)
            ->where('organizer_id', $organizer->id)
            ->first();

        if (!$event) {
            return $this->notFound('Event not found or not owned by you');
        }

        // Get scan points for this event
        $scanPoints = \App\Models\ScanPoint::where('event_id', $eventId)
            ->orderBy('created_at', 'desc')
            ->get();

        return $this->jsonSuccess([
            'event' => $event,
            'scan_points' => $scanPoints,
            'count' => $scanPoints->count()
        ], 'Scan points retrieved successfully');
    }
}
