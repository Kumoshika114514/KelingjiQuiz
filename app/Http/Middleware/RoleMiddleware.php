<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */

    //original function 
    public function handle(Request $request, Closure $next, $role): Response
    {
        // If not logged in
        if (!Auth::check()) {
            // redirect to login page for users that are not logged in
            return redirect()->route('login');
        }

         // If logged in but wrong role
        if (Auth::user()->role !== $role) {
            // Return a custom error view instead of redirecting to login
            return response()->view('errors.role_denied', [
                'expectedRole' => $role,
                'actualRole'   => Auth::user()->role,
            ], 403); // 403 Forbidden Error
        }

        return $next($request);
    }

    // Identifying roles using API tokens
    public function APIhandle(Request $request, Closure $next, $role)
    {
        $user = $request->user();

        if (! $user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        // If token exists, check token abilities for their role
        $token = $user->currentAccessToken();
        if ($token) {
            $abilities = $token->abilities ?? [];
            foreach ($abilities as $ability) {
                if (str_starts_with($ability, 'role:')) {
                    $tokenRole = substr($ability, strlen('role:'));
                    if ($tokenRole === $role) {
                        return $next($request);
                    } else {
                        return response()->json(['message' => 'Forbidden - insufficient role'], 403);
                    }
                }
            }
        }

        // fallback: check DB role
        if (isset($user->role) && $user->role === $role) {
            return $next($request);
        }

        return response()->json(['message' => 'Forbidden - insufficient role'], 403);
    }
}
