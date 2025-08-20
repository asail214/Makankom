<?php

namespace App\Services\Customer;

use App\Models\Customer;
use App\Traits\ApiResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthService
{
    use ApiResponse;

    /**
     * Register a new customer
     */
    public function register(array $data): array
    {
        try {
            $customer = Customer::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
            ]);

            $token = $customer->createToken('customer-token')->plainTextToken;

            return $this->successResponse([
                'customer' => $customer,
                'token' => $token,
            ], 'Customer registered successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Registration failed: ' . $e->getMessage());
        }
    }

    /**
     * Login customer
     */
    public function login(array $data): array
    {
        try {
            $customer = Customer::where('email', $data['email'])->first();

            if (!$customer || !Hash::check($data['password'], $customer->password)) {
                return $this->errorResponse('The provided credentials are incorrect.');
            }

            $token = $customer->createToken('customer-token')->plainTextToken;

            return $this->successResponse([
                'customer' => $customer,
                'token' => $token,
            ], 'Login successful');
        } catch (\Exception $e) {
            return $this->errorResponse('Login failed: ' . $e->getMessage());
        }
    }

    /**
     * Logout customer
     */
    public function logout(): array
    {
        try {
            $customer = Auth::guard('customer')->user();
            
            if ($customer) {
                $customer->tokens()->delete();
            }

            return $this->successResponse(null, 'Logout successful');
        } catch (\Exception $e) {
            return $this->errorResponse('Logout failed: ' . $e->getMessage());
        }
    }

    /**
     * Get customer profile
     */
    public function profile(): array
    {
        try {
            $customer = Auth::guard('customer')->user();
            
            if (!$customer) {
                return $this->errorResponse('Customer not found');
            }

            return $this->successResponse($customer, 'Profile retrieved successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to get profile: ' . $e->getMessage());
        }
    }

    /**
     * Update customer profile
     */
    public function updateProfile(array $data): array
    {
        try {
            $customer = Auth::guard('customer')->user();
            
            if (!$customer) {
                return $this->errorResponse('Customer not found');
            }

            $customer->update($data);

            return $this->successResponse($customer->fresh(), 'Profile updated successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Profile update failed: ' . $e->getMessage());
        }
    }

    /**
     * Change customer password
     */
    public function changePassword(array $data): array
    {
        try {
                        $customer = Auth::guard('customer')->user();
            
            if (!$customer) {
                return $this->errorResponse('Customer not found');
            }

            if (!Hash::check($data['current_password'], $customer->password)) {
                return $this->errorResponse('The current password is incorrect.');
            }

            $customer->update([
                'password' => Hash::make($data['new_password']),
            ]);

            return $this->successResponse(null, 'Password changed successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Password change failed: ' . $e->getMessage());
        }
    }
} 