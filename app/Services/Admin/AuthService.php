<?php

namespace App\Services\Admin;

use App\Models\Admin;
use App\Traits\ApiResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthService
{
    use ApiResponse;

    /**
     * Register a new admin
     */
    public function register(array $data): array
    {
        try {
            $admin = Admin::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'status' => 'active',
            ]);

            $token = $admin->createToken('admin-token')->plainTextToken;

            return $this->successResponse([
                'admin' => $admin,
                'token' => $token,
            ], 'Admin registered successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Registration failed: ' . $e->getMessage());
        }
    }

    /**
     * Login admin
     */
    public function login(array $data): array
    {
        try {
            $admin = Admin::where('email', $data['email'])->first();

            if (!$admin || !Hash::check($data['password'], $admin->password)) {
                return $this->errorResponse('The provided credentials are incorrect.');
            }

            if ($admin->status !== 'active') {
                return $this->errorResponse('Your account is not active.');
            }

            $token = $admin->createToken('admin-token')->plainTextToken;

            return $this->successResponse([
                'admin' => $admin,
                'token' => $token,
            ], 'Login successful');
        } catch (\Exception $e) {
            return $this->errorResponse('Login failed: ' . $e->getMessage());
        }
    }

    /**
     * Logout admin
     */
    public function logout(): array
    {
        try {
            $admin = Auth::guard('admin')->user();
            
            if ($admin) {
                $admin->tokens()->delete();
            }

            return $this->successResponse(null, 'Logout successful');
        } catch (\Exception $e) {
            return $this->errorResponse('Logout failed: ' . $e->getMessage());
        }
    }

    /**
     * Get admin profile
     */
    public function profile(): array
    {
        try {
            $admin = Auth::guard('admin')->user();
            
            if (!$admin) {
                return $this->errorResponse('Admin not found');
            }

            return $this->successResponse($admin, 'Profile retrieved successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to get profile: ' . $e->getMessage());
        }
    }

    /**
     * Update admin profile
     */
    public function updateProfile(array $data): array
    {
        try {
            $admin = Auth::guard('admin')->user();
            
            if (!$admin) {
                return $this->errorResponse('Admin not found');
            }

            $admin->update($data);

            return $this->successResponse($admin->fresh(), 'Profile updated successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Profile update failed: ' . $e->getMessage());
        }
    }

    /**
     * Change admin password
     */
    public function changePassword(array $data): array
    {
        try {
            $admin = Auth::guard('admin')->user();

            if (!$admin) {
                return $this->errorResponse('Admin not found');
            }

            if (!Hash::check($data['current_password'], $admin->password)) {
                return $this->errorResponse('The current password is incorrect.');
            }

            $admin->update([
                'password' => Hash::make($data['new_password']),
            ]);

            return $this->successResponse(null, 'Password changed successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Password change failed: ' . $e->getMessage());
        }
    }
} 