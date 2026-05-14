<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * قائمة الموظفين: مسؤول أو مدير (لعرض المعطّلين وربط صفحة الصلاحيات).
 */
class WebCheckAdminOrManagement
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! User::where('role', 'admin')->exists()) {
            return $next($request);
        }

        if (! Auth::check()) {
            return redirect()->route('login')->with('error', 'يجب تسجيل الدخول للوصول إلى هذه الصفحة');
        }

        if (in_array(Auth::user()->role, ['admin', 'manager'], true)) {
            return $next($request);
        }

        return redirect()->route('dashboard')->with('error', 'غير مصرح لك بعرض هذه الصفحة');
    }
}
