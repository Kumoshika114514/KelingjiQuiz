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
}
