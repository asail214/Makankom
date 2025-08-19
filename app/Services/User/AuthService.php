<?php

namespace App\Services\User;

use App\Models\User;
use App\Traits\ApiResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthService
{
    use ApiResponse;

    /**
     * Register a new user
     */
    public function register(array $data): array
    {
        try {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
            ]);

            $token = $user->createToken('user-token')->plainTextToken;

            return $this->successResponse([
                'user' => $user,
                'token' => $token,
            ], 'User registered successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Registration failed: ' . $e->getMessage());
        }
    }

    /**
     * Login user
     */
    public function login(array $data): array
    {
        try {
            $user = User::where('email', $data['email'])->first();

            if (!$user || !Hash::check($data['password'], $user->password)) {
                return $this->errorResponse('The provided credentials are incorrect.');
            }

            $token = $user->createToken('user-token')->plainTextToken;

            return $this->successResponse([
                'user' => $user,
                'token' => $token,
            ], 'Login successful');
        } catch (\Exception $e) {
            return $this->errorResponse('Login failed: ' . $e->getMessage());
        }
    }

    /**
     * Logout user
     */
    public function logout(): array
    {
        try {
            $user = Auth::guard('user')->user();
            
            if ($user) {
                $user->tokens()->delete();
            }

            return $this->successResponse(null, 'Logout successful');
        } catch (\Exception $e) {
            return $this->errorResponse('Logout failed: ' . $e->getMessage());
        }
    }

    /**
     * Get user profile
     */
    public function profile(): array
    {
        try {
            $user = Auth::guard('user')->user();
            
            if (!$user) {
                return $this->errorResponse('User not found');
            }

            return $this->successResponse($user, 'Profile retrieved successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to get profile: ' . $e->getMessage());
        }
    }

    /**
     * Update user profile
     */
    public function updateProfile(array $data): array
    {
        try {
            $user = Auth::guard('user')->user();
            
            if (!$user) {
                return $this->errorResponse('User not found');
            }

            $user->update($data);

            return $this->successResponse($user->fresh(), 'Profile updated successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Profile update failed: ' . $e->getMessage());
        }
    }

    /**
     * Change user password
     */
    public function changePassword(array $data): array
    {
        try {
            $user = Auth::guard('user')->user();

            if (!$user) {
                return $this->errorResponse('User not found');
            }

            if (!Hash::check($data['current_password'], $user->password)) {
                return $this->errorResponse('The current password is incorrect.');
            }

            $user->update([
                'password' => Hash::make($data['new_password']),
            ]);

            return $this->successResponse(null, 'Password changed successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Password change failed: ' . $e->getMessage());
        }
    }
} 