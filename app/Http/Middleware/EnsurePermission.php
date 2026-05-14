<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePermission
{
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        $user = $request->user();
        if (! $user || ! $user->hasPermission($permission)) {
            return redirect()
                ->route('dashboard')
                ->with('error', 'ليس لديك صلاحية للوصول إلى هذا القسم.');
        }

        return $next($request);
    }
}
