<?php

namespace App\Services\ScanPoint;

use App\Models\ScanPoint;
use App\Traits\ApiResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthService
{
    use ApiResponse;

    /**
     * Create a new scan point
     */
    public function create(array $data): array
    {
        try {
            $scanPoint = ScanPoint::create([
                'name' => $data['name'],
                'location' => $data['location'] ?? null,
                'description' => $data['description'] ?? null,
                'status' => 'active',
            ]);

            $token = $scanPoint->createToken('scan-point-token')->plainTextToken;

            return $this->successResponse([
                'scan_point' => $scanPoint,
                'token' => $token,
            ], 'Scan point created successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Creation failed: ' . $e->getMessage());
        }
    }

    /**
     * Login scan point using token
     */
    public function loginWithToken(string $token): array
    {
        try {
            $scanPoint = ScanPoint::whereHas('tokens', function ($query) use ($token) {
                $query->where('token', hash('sha256', $token));
            })->first();

            if (!$scanPoint) {
                return $this->errorResponse('Invalid token provided.');
            }

            if ($scanPoint->status !== 'active') {
                return $this->errorResponse('This scan point is not active.');
            }

            return $this->successResponse([
                'scan_point' => $scanPoint,
                'token' => $token,
            ], 'Login successful');
        } catch (\Exception $e) {
            return $this->errorResponse('Login failed: ' . $e->getMessage());
        }
    }

    /**
     * Logout scan point
     */
    public function logout(): array
    {
        try {
            $scanPoint = Auth::guard('scan_point')->user();
            
            if ($scanPoint) {
                $scanPoint->tokens()->delete();
            }

            return $this->successResponse(null, 'Logout successful');
        } catch (\Exception $e) {
            return $this->errorResponse('Logout failed: ' . $e->getMessage());
        }
    }

    /**
     * Get scan point profile
     */
    public function profile(): array
    {
        try {
            $scanPoint = Auth::guard('scan_point')->user();
            
            if (!$scanPoint) {
                return $this->errorResponse('Scan point not found');
            }

            return $this->successResponse($scanPoint, 'Profile retrieved successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to get profile: ' . $e->getMessage());
        }
    }

    /**
     * Update scan point profile
     */
    public function updateProfile(array $data): array
    {
        try {
            $scanPoint = Auth::guard('scan_point')->user();
            
            if (!$scanPoint) {
                return $this->errorResponse('Scan point not found');
            }

            $scanPoint->update($data);

            return $this->successResponse($scanPoint->fresh(), 'Profile updated successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Profile update failed: ' . $e->getMessage());
        }
    }

    /**
     * Generate new token for scan point
     */
    public function generateNewToken(): array
    {
        try {
            $scanPoint = Auth::guard('scan_point')->user();
            
            if (!$scanPoint) {
                return $this->errorResponse('Scan point not found');
            }

            // Delete old tokens
            $scanPoint->tokens()->delete();
            
            // Generate new token
            $token = $scanPoint->createToken('scan-point-token')->plainTextToken;

            return $this->successResponse([
                'scan_point' => $scanPoint,
                'token' => $token,
            ], 'New token generated successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Token generation failed: ' . $e->getMessage());
        }
    }
} 