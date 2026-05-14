<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class WebCheckAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $hasAdmins = User::where('role', 'admin')->exists();

        if ($hasAdmins) {
            // Admins exist â€” enforce session auth
            if (!Auth::check() || Auth::user()->role !== 'admin') {
                return redirect()->route('login')->with('error', 'يجب تسجيل الدخول كمسؤول للوصول إلى هذه الصفحة');
            }
        }

        return $next($request);
    }
}
