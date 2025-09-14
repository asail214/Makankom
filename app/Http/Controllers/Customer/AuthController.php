<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Services\Customer\AuthService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    use ApiResponse;

    protected AuthService $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    /**
     * Register a new customer
     */
    public function register(Request $request): JsonResponse
    {
        try {
            // Debug: Log the incoming request
            Log::info('Customer registration attempt', [
                'request_data' => $request->all(),
                'headers' => $request->headers->all()
            ]);

            $validator = Validator::make($request->all(), [
                'first_name' => 'required|string|max:255',
                'last_name'  => 'required|string|max:255',
                'email'      => 'required|string|email|max:255|unique:customers',
                'phone'      => 'nullable|string|max:20|unique:customers',
                'password'   => 'required|string|min:8|confirmed',
            ]);

            if ($validator->fails()) {
                Log::warning('Customer registration validation failed', [
                    'errors' => $validator->errors()->toArray()
                ]);
                return $this->validationError($validator->errors());
            }

            Log::info('Validation passed, calling auth service');

            $result = $this->authService->register($request->all());

            Log::info('Auth service response', ['result' => $result]);

            if ($result['success']) {
                return $this->jsonSuccess($result['data'], $result['message'], 201);
            }

            return $this->jsonError($result['message'], null, 400);

        } catch (\Exception $e) {
            Log::error('Customer registration exception', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            return $this->jsonError('Registration failed: ' . $e->getMessage(), null, 500);
        }
    }

    /**
     * Login customer
     */
    public function login(Request $request): JsonResponse
    {
        try {
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
        } catch (\Exception $e) {
            Log::error('Customer login exception', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return $this->jsonError('Login failed', null, 500);
        }
    }

    /**
     * Logout customer
     */
    public function logout(): JsonResponse
    {
        try {
            $result = $this->authService->logout();

            if ($result['success']) {
                return $this->jsonSuccess(null, $result['message']);
            }

            return $this->serverError($result['message']);
        } catch (\Exception $e) {
            Log::error('Customer logout exception', [
                'message' => $e->getMessage()
            ]);
            return $this->serverError('Logout failed');
        }
    }

    /**
     * Get customer profile
     */
    public function profile(): JsonResponse
    {
        try {
            $result = $this->authService->profile();

            if ($result['success']) {
                return $this->jsonSuccess($result['data'], $result['message']);
            }

            return $this->notFound($result['message']);
        } catch (\Exception $e) {
            Log::error('Customer profile exception', [
                'message' => $e->getMessage()
            ]);
            return $this->serverError('Profile retrieval failed');
        }
    }

    /**
     * Update customer profile
     */
    public function updateProfile(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'first_name' => 'sometimes|string|max:255',
                'last_name' => 'sometimes|string|max:255',
                'phone' => 'nullable|string|max:20',
            ]);

            if ($validator->fails()) {
                return $this->validationError($validator->errors());
            }

            $result = $this->authService->updateProfile($request->all());

            if ($result['success']) {
                return $this->jsonSuccess($result['data'], $result['message']);
            }

            return $this->jsonError($result['message'], null, 400);
        } catch (\Exception $e) {
            Log::error('Customer update profile exception', [
                'message' => $e->getMessage()
            ]);
            return $this->serverError('Profile update failed');
        }
    }

    /**
     * Change customer password
     */
    public function changePassword(Request $request): JsonResponse
    {
        try {
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
        } catch (\Exception $e) {
            Log::error('Customer change password exception', [
                'message' => $e->getMessage()
            ]);
            return $this->serverError('Password change failed');
        }
    }
}