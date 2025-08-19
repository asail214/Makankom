<?php

namespace App\Services\Merchant;

use App\Models\Merchant;
use App\Traits\ApiResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthService
{
    use ApiResponse;

    /**
     * Register a new merchant
     */
    public function register(array $data): array
    {
        try {
            $merchant = Merchant::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'phone' => $data['phone'] ?? null,
                'business_name' => $data['business_name'],
                'business_address' => $data['business_address'] ?? null,
                'business_phone' => $data['business_phone'] ?? null,
                'status' => 'pending',
            ]);

            $token = $merchant->createToken('merchant-token')->plainTextToken;

            return $this->successResponse([
                'merchant' => $merchant,
                'token' => $token,
            ], 'Merchant registered successfully. Your account is pending approval.');
        } catch (\Exception $e) {
            return $this->errorResponse('Registration failed: ' . $e->getMessage());
        }
    }

    /**
     * Login merchant
     */
    public function login(array $data): array
    {
        try {
            $merchant = Merchant::where('email', $data['email'])->first();

            if (!$merchant || !Hash::check($data['password'], $merchant->password)) {
                return $this->errorResponse('The provided credentials are incorrect.');
            }

            if ($merchant->status === 'inactive') {
                return $this->errorResponse('Your account is inactive.');
            }

            if ($merchant->status === 'pending') {
                return $this->errorResponse('Your account is pending approval.');
            }

            $token = $merchant->createToken('merchant-token')->plainTextToken;

            return $this->successResponse([
                'merchant' => $merchant,
                'token' => $token,
            ], 'Login successful');
        } catch (\Exception $e) {
            return $this->errorResponse('Login failed: ' . $e->getMessage());
        }
    }

    /**
     * Logout merchant
     */
    public function logout(): array
    {
        try {
            $merchant = Auth::guard('merchant')->user();
            
            if ($merchant) {
                $merchant->tokens()->delete();
            }

            return $this->successResponse(null, 'Logout successful');
        } catch (\Exception $e) {
            return $this->errorResponse('Logout failed: ' . $e->getMessage());
        }
    }

    /**
     * Get merchant profile
     */
    public function profile(): array
    {
        try {
            $merchant = Auth::guard('merchant')->user();
            
            if (!$merchant) {
                return $this->errorResponse('Merchant not found');
            }

            return $this->successResponse($merchant, 'Profile retrieved successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to get profile: ' . $e->getMessage());
        }
    }

    /**
     * Update merchant profile
     */
    public function updateProfile(array $data): array
    {
        try {
            $merchant = Auth::guard('merchant')->user();
            
            if (!$merchant) {
                return $this->errorResponse('Merchant not found');
            }

            $merchant->update($data);

            return $this->successResponse($merchant->fresh(), 'Profile updated successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Profile update failed: ' . $e->getMessage());
        }
    }

    /**
     * Change merchant password
     */
    public function changePassword(array $data): array
    {
        try {
            $merchant = Auth::guard('merchant')->user();

            if (!$merchant) {
                return $this->errorResponse('Merchant not found');
            }

            if (!Hash::check($data['current_password'], $merchant->password)) {
                return $this->errorResponse('The current password is incorrect.');
            }

            $merchant->update([
                'password' => Hash::make($data['new_password']),
            ]);

            return $this->successResponse(null, 'Password changed successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Password change failed: ' . $e->getMessage());
        }
    }
} 