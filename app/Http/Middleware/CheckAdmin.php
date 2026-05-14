<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $hasAdmins = \App\Models\User::where('role', 'admin')->exists();

        if ($hasAdmins) {
            // Admins exist — enforce token auth
            $user = auth('sanctum')->user();
            if (!$user || $user->role !== 'admin') {
                return response()->json(['message' => 'Unauthorized. Admin access required.'], 403);
            }
        }
        // No admins at all — free access

        return $next($request);
    }
}
