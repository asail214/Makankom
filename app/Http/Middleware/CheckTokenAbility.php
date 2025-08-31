<?php

namespace App\Http\Middleware;

use App\Traits\ApiResponse;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckTokenAbility
{
    use ApiResponse;

    public function handle(Request $request, Closure $next, string $ability): Response
    {
        $user = $request->user();
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Authentication required'
            ], 401);
        }

        if (!$user->tokenCan($ability)) {
            return response()->json([
                'success' => false,
                'message' => 'Insufficient permissions for this action'
            ], 403);
        }

        return $next($request);
    }
}