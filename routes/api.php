<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\AttendanceController;
use App\Http\Controllers\Api\DoctorController;
use App\Http\Controllers\Api\TestTypeController;
use App\Http\Controllers\Api\PatientController;
use App\Http\Controllers\Api\DelegateController;
use App\Http\Controllers\Api\DelegateVisitController;
use App\Http\Controllers\Api\FinanceController;
use App\Http\Controllers\EmployeeController;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

// =====================================================================
// Public Routes (لا تحتاج مصادقة)
// =====================================================================

// Desktop Auth Polling — عامة لأنها جزء من flow تسجيل الدخول
Route::get('/has-admins', [AuthController::class, 'hasAdmins']);
Route::get('/auth-qr', [AuthController::class, 'generateAuthQr']);
Route::get('/auth-poll/{token}', [AuthController::class, 'pollAuthQr']);

// Login — محمي بـ Rate Limiter لمنع Brute Force
Route::post('/login', [AuthController::class, 'login'])
    ->middleware('throttle:login');

// Attendance — محمي بـ Rate Limiter + يتطلب device_id + QR token verification داخلياً
Route::post('/attend', [AttendanceController::class, 'attend'])
    ->middleware('throttle:attendance');

// Complete Pairing — يُستدعى من هاتف الموظف بعد مسح QR الربط
// Token-based validation داخلياً (pair_token من Cache)
Route::post('/complete-pairing', [AuthController::class, 'completePairing']);

// QR Token Generation — محمي بـ Rate Limiter + يتطلب auth:sanctum
Route::get('/qr-generate', function () {
    $token = Str::random(40);
    // Cache for 15 seconds to allow a 5 second transmission margin
    Cache::put('current_qr_token', $token, 15);
    return response()->json(['token' => $token]);
})->middleware(['auth:sanctum', 'throttle:qr-generate']);

// =====================================================================
// Authenticated Routes (تتطلب auth:sanctum)
// =====================================================================

Route::middleware('auth:sanctum')->group(function () {

    // --- Current User ---
    Route::get('/user', function (Request $request) {
        return new \App\Http\Resources\UserResource($request->user());
    });

    // Mobile Auth QR scanning endpoint
    Route::post('/auth-authorize', [AuthController::class, 'authorizeAuthQr']);

    // --- Clinic Data (Doctors, Test Types, Patients, Delegates) ---
    Route::name('api.')->group(function () {
        Route::apiResource('doctors', DoctorController::class);
        Route::apiResource('test-types', TestTypeController::class);
        Route::apiResource('patients', PatientController::class);
        Route::apiResource('delegates', DelegateController::class);
        Route::apiResource('delegate-visits', DelegateVisitController::class);
    });

    // --- Finance ---
    Route::prefix('finance')->group(function () {
        Route::get('/summary', [FinanceController::class, 'summary']);
        Route::get('/report', [FinanceController::class, 'report']);

        Route::get('/transactions', [FinanceController::class, 'transactions']);
        Route::post('/transactions', [FinanceController::class, 'storeTransaction']);

        Route::get('/expenses', [FinanceController::class, 'expenses']);
        Route::post('/expenses', [FinanceController::class, 'storeExpense']);
        Route::put('/expenses/{expense}', [FinanceController::class, 'updateExpense']);
        Route::delete('/expenses/{expense}', [FinanceController::class, 'destroyExpense']);

        Route::get('/doctor-payouts', [FinanceController::class, 'doctorPayouts']);
        Route::post('/doctor-payouts/mark-paid', [FinanceController::class, 'markPayoutsPaid']);
    });
});

// =====================================================================
// Admin-Only Routes (تتطلب auth:sanctum + role = admin)
// =====================================================================

Route::middleware([\App\Http\Middleware\CheckAdmin::class])->group(function () {

    // --- Employees ---
    Route::get('/employees', [EmployeeController::class, 'index']);
    Route::post('/employees', [EmployeeController::class, 'store']);
    Route::put('/employees/{employee}', [EmployeeController::class, 'update']);
    Route::delete('/employees/{employee}', [EmployeeController::class, 'destroy']);
    Route::post('/employees/{employee}/reset-device', [EmployeeController::class, 'resetDevice']);

    // --- Device Pairing (Admin Only) ---
    Route::post('/pair-device', [AttendanceController::class, 'pairDevice']);
    Route::post('/unpair-device/{user}', [AttendanceController::class, 'unpairDevice']);

    // --- Attendance Management ---
    Route::get('/attendance', [AttendanceController::class, 'index']);
    Route::put('/attendance/{attendance}', [AttendanceController::class, 'update']);
    Route::delete('/attendance/{attendance}', [AttendanceController::class, 'destroy']);
});
