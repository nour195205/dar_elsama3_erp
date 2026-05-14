<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    public function handle(Request $request, Closure $next, $permission): Response
    {
        if (auth()->check() && auth()->user()->hasPermission($permission)) {
            return $next($request);
        }

        return response()->view('errors.403', [], 403);
    }
}
