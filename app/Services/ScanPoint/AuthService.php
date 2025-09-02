<?php

namespace App\Services\ScanPoint;

use App\Models\ScanPoint;
use App\Models\Event;
use App\Traits\ApiResponse;
use App\Services\TokenAbilityService;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\PersonalAccessToken;

class AuthService
{
    use ApiResponse;

    /**
     * Create a new scan point
     */
    public function create(array $data): array
    {
        try {
            // Verify the event exists and is approved
            $event = Event::find($data['event_id']);
            if (!$event) {
                return $this->errorResponse('Event not found');
            }

            if (!$event->is_approved) {
                return $this->errorResponse('Can only create scan points for approved events');
            }

            $scanPoint = ScanPoint::create([
                'label' => $data['label'],
                'event_id' => $data['event_id'],
                'device_information' => $data['device_information'] ?? null,
            ]);

            $abilities = TokenAbilityService::getAbilitiesFor('scan_point');
            $token = $scanPoint->createToken('scan-point-token', $abilities)->plainTextToken;

            return $this->successResponse([
                'scan_point' => $scanPoint->load('event'),
                'token' => $token,
            ], 'Scan point created successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Creation failed: ' . $e->getMessage());
        }
    }

    /**
     * Login scan point using token - SIMPLIFIED VERSION
     */
    public function loginWithToken(string $token): array
    {
        try {
            // Split the token to get the ID and the actual token
            $tokenParts = explode('|', $token);
            
            if (count($tokenParts) !== 2) {
                return $this->errorResponse('Invalid token format provided.');
            }

            $tokenId = $tokenParts[0];
            $tokenValue = $tokenParts[1];
            
            // Find the token record
            $personalAccessToken = PersonalAccessToken::find($tokenId);
            
            if (!$personalAccessToken) {
                return $this->errorResponse('Token not found.');
            }
            
            // Verify the token hash
            if (!hash_equals($personalAccessToken->token, hash('sha256', $tokenValue))) {
                return $this->errorResponse('Invalid token provided.');
            }
            
            // Get the scan point
            $scanPoint = $personalAccessToken->tokenable;
            
            if (!$scanPoint || !($scanPoint instanceof ScanPoint)) {
                return $this->errorResponse('Invalid scan point token.');
            }

            // Load the related event
            $scanPoint->load('event');

            return $this->successResponse([
                'scan_point' => $scanPoint,
                'token' => $token,
                'abilities' => $personalAccessToken->abilities ?? []
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

            $scanPoint->load('event');

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

            return $this->successResponse($scanPoint->fresh(['event']), 'Profile updated successfully');
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
            $abilities = TokenAbilityService::getAbilitiesFor('scan_point');
            $token = $scanPoint->createToken('scan-point-token', $abilities)->plainTextToken;

            return $this->successResponse([
                'scan_point' => $scanPoint->load('event'),
                'token' => $token,
            ], 'New token generated successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Token generation failed: ' . $e->getMessage());
        }
    }
}