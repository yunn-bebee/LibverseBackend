<?php
// app/Http/Middleware/RoleMiddleware.php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Enums\UserRole;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        // Convert enum cases to values if needed
        $roleValues = array_map(function($role) {
            // If $role is a valid UserRole case name, get its value; otherwise, use $role as is
            return UserRole::tryFrom($role)?->value ?? $role;
        }, $roles);


        // Check if user's role matches any allowed roles (case-insensitive)
        $userRoleLower = strtolower($user->role);
        $roleValuesLower = array_map('strtolower', $roleValues);

        if (!in_array($userRoleLower, $roleValuesLower)) {
            return response()->json([
            'message' => $user->role . ' ' . implode(',', $roleValues) . ' Unauthorized that doesnt work bro'
            ], 403);
        }

        return $next($request);
    }
}
