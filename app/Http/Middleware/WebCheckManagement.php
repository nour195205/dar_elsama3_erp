<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * مسؤول أو مدير — لسجل النشاط وإدارة صلاحيات الموظفين.
 */
class WebCheckManagement
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! User::where('role', 'admin')->exists()) {
            return $next($request);
        }

        if (! Auth::check()) {
            return redirect()->route('login')->with('error', 'يجب تسجيل الدخول للوصول إلى هذه الصفحة');
        }

        $role = Auth::user()->role;
        if (! in_array($role, ['admin', 'manager'], true)) {
            return redirect()->route('dashboard')->with('error', 'غير مصرح لك بعرض هذه الصفحة');
        }

        return $next($request);
    }
}
