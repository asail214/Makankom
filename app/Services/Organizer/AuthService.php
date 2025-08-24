<?php

namespace App\Services\Organizer;

use App\Models\Organizer;
use App\Traits\ApiResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthService
{
    use ApiResponse;

    /**
     * Register a new organizer
     */
    public function register(array $data): array
    {
        try {
            $organizer = Organizer::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'phone' => $data['phone'] ?? null,
                'type' => $data['type'],
                'profile_img_url' => $data['profile_img_url'] ?? null,
                'cr_number' => $data['cr_number'] ?? null,
                'cr_document_path' => null,
                'status' => 'pending',
            ]);

            $token = $organizer->createToken('organizer-token')->plainTextToken;

            return $this->successResponse([
                'organizer' => $organizer,
                'token' => $token,
            ], 'Organizer registered successfully. Your account is pending verification.');
        } catch (\Exception $e) {
            return $this->errorResponse('Registration failed: ' . $e->getMessage());
        }
    }

    /**
     * Login organizer
     */
    public function login(array $data): array
    {
        try {
            $organizer = Organizer::where('email', $data['email'])->first();

            if (!$organizer || !Hash::check($data['password'], $organizer->password)) {
                return $this->errorResponse('The provided credentials are incorrect.');
            }

            if ($organizer->status === 'inactive') {
                return $this->errorResponse('Your account is inactive.');
            }

            if ($organizer->status === 'pending') {
                return $this->errorResponse('Your account is pending approval.');
            }

            $token = $organizer->createToken('organizer-token')->plainTextToken;

            return $this->successResponse([
                'organizer' => $organizer,
                'token' => $token,
            ], 'Login successful');
        } catch (\Exception $e) {
            return $this->errorResponse('Login failed: ' . $e->getMessage());
        }
    }

    /**
     * Logout organizer
     */
    public function logout(): array
    {
        try {
            $organizer = Auth::guard('organizer')->user();
            
            if ($organizer) {
                $organizer->tokens()->delete();
            }

            return $this->successResponse(null, 'Logout successful');
        } catch (\Exception $e) {
            return $this->errorResponse('Logout failed: ' . $e->getMessage());
        }
    }

    /**
     * Get organizer profile
     */
    public function profile(): array
    {
        try {
            $organizer = Auth::guard('organizer')->user();
            
            if (!$organizer) {
                return $this->errorResponse('Organizer not found');
            }

            return $this->successResponse($organizer, 'Profile retrieved successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to get profile: ' . $e->getMessage());
        }
    }

    /**
     * Update organizer profile
     */
    public function updateProfile(array $data): array
    {
        try {
            $organizer = Auth::guard('organizer')->user();
            
            if (!$organizer) {
                return $this->errorResponse('Organizer not found');
            }

            $organizer->update($data);

            return $this->successResponse($organizer->fresh(), 'Profile updated successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Profile update failed: ' . $e->getMessage());
        }
    }

    /**
     * Change organizer password
     */
    public function changePassword(array $data): array
    {
        try {
            $organizer = Auth::guard('organizer')->user();

            if (!$organizer) {
                return $this->errorResponse('Organizer not found');
            }

            if (!Hash::check($data['current_password'], $organizer->password)) {
                return $this->errorResponse('The current password is incorrect.');
            }

            $organizer->update([
                'password' => Hash::make($data['new_password']),
            ]);

            return $this->successResponse(null, 'Password changed successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Password change failed: ' . $e->getMessage());
        }
    }
} 