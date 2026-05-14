<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * مسؤول النظام فقط (إدارة مجموعات الصلاحيات وما شابه).
 */
class EnsureWebAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()?->role !== 'admin') {
            return redirect()
                ->route('dashboard')
                ->with('error', 'هذه الصفحة متاحة لمسؤول النظام فقط.');
        }

        return $next($request);
    }
}
