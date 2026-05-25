<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Rate Limiter: Login — 5 محاولات كحد أقصى في الدقيقة لكل IP
        RateLimiter::for('login', function (Request $request) {
            $key = $request->input('email', '') . '|' . $request->ip();
            return Limit::perMinute(5)->by($key)->response(function () {
                return response()->json([
                    'message' => 'محاولات تسجيل دخول كثيرة. يرجى المحاولة بعد دقيقة.',
                ], 429);
            });
        });

        // Rate Limiter: QR Token generation — 10 طلبات في الدقيقة لكل IP
        RateLimiter::for('qr-generate', function (Request $request) {
            return Limit::perMinute(10)->by($request->ip())->response(function () {
                return response()->json([
                    'message' => 'طلبات كثيرة لتوليد رمز QR. يرجى المحاولة لاحقاً.',
                ], 429);
            });
        });

        // Rate Limiter: Attendance — 6 طلبات في الدقيقة لكل IP (حضور + انصراف)
        RateLimiter::for('attendance', function (Request $request) {
            return Limit::perMinute(6)->by($request->ip())->response(function () {
                return response()->json([
                    'message' => 'طلبات حضور كثيرة. يرجى المحاولة لاحقاً.',
                ], 429);
            });
        });

        // Rate Limiter: API عام — 60 طلب في الدقيقة لكل مستخدم/IP
        RateLimiter::for('api-global', function (Request $request) {
            $user = $request->user();
            $key = $user ? $user->id : $request->ip();
            return Limit::perMinute(60)->by($key);
        });
    }
}
