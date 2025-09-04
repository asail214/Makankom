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
            $sanctumToken = $scanPoint->createToken('scan-point-token', $abilities)->plainTextToken;

            return $this->successResponse([
                'scan_point' => $scanPoint->load('event'),
                'simple_token' => $scanPoint->token,  // Add the simple token (SP_...)
                'sanctum_token' => $sanctumToken,     // The API token for subsequent calls
                'token' => $sanctumToken              // Keep this for backward compatibility
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
            // Find scan point by token
            $scanPoint = ScanPoint::where('token', $token)
                ->where('status', 'active')
                ->first();
            
            if (!$scanPoint) {
                return $this->errorResponse('Invalid token or inactive scan point.');
            }

            // Load the related event
            $scanPoint->load('event');
            
            // Check if event is approved
            if (!$scanPoint->event || !$scanPoint->event->is_approved) {
                return $this->errorResponse('Scan point event is not approved.');
            }

            // Create Sanctum token for API authentication (keep this for API calls)
            $abilities = TokenAbilityService::getAbilitiesFor('scan_point');
            $sanctumToken = $scanPoint->createToken('scan-point-token', $abilities)->plainTextToken;

            return $this->successResponse([
                'scan_point' => $scanPoint,
                'token' => $sanctumToken, // This is for API calls
                'simple_token' => $token,  // This is the original token
                'abilities' => $abilities
            ], 'Login successful');
            
        } catch (\Exception $e) {
            return $this->errorResponse('Login failed: ' . $e->getMessage());
        }
    }

    /**
     * Login with scan_point_id and token (as per Postman collection)
     */
    public function login(array $data): array
    {
        try {
            $scanPoint = ScanPoint::where('id', $data['scan_point_id'])
                ->where('token', $data['token'])
                ->where('status', 'active')
                ->first();
            
            if (!$scanPoint) {
                return $this->errorResponse('Invalid scan point ID or token.');
            }

            $scanPoint->load('event');
            
            if (!$scanPoint->event || !$scanPoint->event->is_approved) {
                return $this->errorResponse('Scan point event is not approved.');
            }

            $abilities = TokenAbilityService::getAbilitiesFor('scan_point');
            $sanctumToken = $scanPoint->createToken('scan-point-token', $abilities)->plainTextToken;

            return $this->successResponse([
                'scan_point' => $scanPoint,
                'token' => $sanctumToken,
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

    /**
     * Delete a scan point
     */
    public function destroy(int $scanPointId): array
    {
        try {
            $scanPoint = ScanPoint::with('event')->find($scanPointId);
            
            if (!$scanPoint) {
                return $this->errorResponse('Scan point not found');
            }

            // Check if the authenticated user is the organizer of the event
            $organizer = Auth::guard('organizer')->user();
            if (!$organizer || $scanPoint->event->organizer_id !== $organizer->id) {
                return $this->errorResponse('Unauthorized. You can only delete scan points for your own events.');
            }

            // Check if scan point has any scans - prevent deletion if it has scan history
            $scanCount = $scanPoint->ticketScans()->count();
            if ($scanCount > 0) {
                return $this->errorResponse("Cannot delete scan point with {$scanCount} scan records. Deactivate instead.");
            }

            $scanPoint->delete();

            return $this->successResponse(null, 'Scan point deleted successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Deletion failed: ' . $e->getMessage());
        }
    }

    /**
     * Deactivate a scan point (safer alternative to deletion)
     */
    public function deactivate(int $scanPointId): array
    {
        try {
            $scanPoint = ScanPoint::with('event')->find($scanPointId);
            
            if (!$scanPoint) {
                return $this->errorResponse('Scan point not found');
            }

            $organizer = Auth::guard('organizer')->user();
            if (!$organizer || $scanPoint->event->organizer_id !== $organizer->id) {
                return $this->errorResponse('Unauthorized. You can only manage scan points for your own events.');
            }

            $scanPoint->status = 'inactive';
            $scanPoint->save();

            return $this->successResponse($scanPoint->fresh(['event']), 'Scan point deactivated successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Deactivation failed: ' . $e->getMessage());
        }
    }
}

