<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Services\Customer\AuthService;
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
     * Register a new customer
     */
    public function register(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'full_name' => [
                'required', 'string', 'max:300',
                function ($attribute, $value, $fail) {
                    $parts = preg_split('/\s+/', trim((string) $value));
                    $parts = array_values(array_filter($parts, fn ($p) => $p !== ''));
                    if (count($parts) === 0 || count($parts) > 2) {
                        $fail('Write First Name and Last Name');
                    }
                },
            ],
            'email' => 'required|string|email|max:255|unique:customers',
            'password' => 'required|string|min:8|confirmed',
            'phone' => 'nullable|string|max:8|unique:customers',
            'date_of_birth' => 'nullable|date|before:today',
            'gender' => 'nullable|in:male,female',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'preferred_language' => 'nullable|string|in:en,ar',
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator->errors());
        }


        //--------------------------------------------------------------
        // Split full_name into first_name and last_name
        $parts = preg_split('/\s+/', trim($request->full_name));
        $parts = array_values(array_filter($parts, fn ($p) => $p !== ''));
        $firstName = $parts[0];
        $lastName = count($parts) === 2 ? $parts[1] : null;

        // Prepare data for registration
        $registrationData = $request->all();
        $registrationData['first_name'] = $firstName;
        $registrationData['last_name'] = $lastName;
        
        // Remove full_name from data as it's not needed in the service
        unset($registrationData['full_name']);

        $result = $this->authService->register($registrationData);
        //--------------------------------------------------------------

        if ($result['success']) {
            return $this->jsonSuccess($result['data'], $result['message'], 201);
        }

        return $this->jsonError($result['message'], null, 400);
    }

    /**
     * Login customer
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
     * Logout customer
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
     * Get customer profile
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
     * Update customer profile
     */
    public function updateProfile(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
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
     * Change customer password
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
