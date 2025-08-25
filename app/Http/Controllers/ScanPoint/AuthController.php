<?php

namespace App\Http\Controllers\ScanPoint;

use App\Http\Controllers\Controller;
use App\Services\ScanPoint\AuthService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
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
            'description' => 'nullable|string|max:1000',
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
}
