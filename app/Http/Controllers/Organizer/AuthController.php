<?php

namespace App\Http\Controllers\Organizer;

use App\Http\Controllers\Controller;
use App\Services\Organizer\AuthService;
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
     * Register a new organizer
     */
    public function register(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:organizers',
            'password' => 'required|string|min:8|confirmed',
            'phone' => 'nullable|string|max:20',
            'business_name' => 'required|string|max:255',
            'business_address' => 'nullable|string|max:500',
            'business_phone' => 'nullable|string|max:20',
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator->errors());
        }

        $result = $this->authService->register($request->all());

        if ($result['success']) {
            return $this->jsonSuccess($result['data'], $result['message'], 201);
        }

        return $this->jsonError($result['message'], null, 400);
    }

    /**
     * Login organizer
     */
    public function login(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email',
            'password' => 'required|string',
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
     * Logout organizer
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
     * Get organizer profile
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
     * Get organizer profile
     */
    public function updateProfile(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'phone' => 'nullable|string|max:20',
            'business_name' => 'sometimes|string|max:255',
            'business_address' => 'nullable|string|max:500',
            'business_phone' => 'nullable|string|max:20',
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
     * Change organizer password
     */
    public function changePassword(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator->errors());
        }

        $result = $this->authService->changePassword($request->all());

        if ($result['success']) {
            return $this->jsonSuccess(null, $result['message']);
        }

        return $this->jsonError($result['message'], null, 400);
    }
}
