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

// Generate QR Token
Route::get('/qr-generate', function () {
    $token = Str::random(40);
    // Cache for 15 seconds to allow a 5 second transmission margin
    Cache::put('current_qr_token', $token, 15);
    return response()->json(['token' => $token]);
});

// Desktop Auth Polling
Route::get('/has-admins', [AuthController::class, 'hasAdmins']);
Route::get('/auth-qr', [AuthController::class, 'generateAuthQr']);
Route::get('/auth-poll/{token}', [AuthController::class, 'pollAuthQr']);

// Public Auth
Route::post('/login', [AuthController::class, 'login']);
Route::post('/attend', [AttendanceController::class, 'attend']);
Route::post('/pair-device', [AttendanceController::class, 'pairDevice']);

// Secure Admin Routes
Route::middleware([\App\Http\Middleware\CheckAdmin::class])->group(function () {

    // --- Employees ---
    Route::get('/employees', [EmployeeController::class, 'index']);
    Route::post('/employees', [EmployeeController::class, 'store']);
    Route::put('/employees/{employee}', [EmployeeController::class, 'update']);
    Route::delete('/employees/{employee}', [EmployeeController::class, 'destroy']);
    Route::post('/employees/{employee}/reset-device', [EmployeeController::class, 'resetDevice']);

    // --- Attendance ---
    Route::get('/attendance', [AttendanceController::class, 'index']);
    Route::put('/attendance/{attendance}', [AttendanceController::class, 'update']);
    Route::delete('/attendance/{attendance}', [AttendanceController::class, 'destroy']);
});

// --- Public Data Routes (No Auth Required) ---
Route::name('api.')->group(function () {
    // --- Doctors ---
    Route::apiResource('doctors', DoctorController::class);

    // --- Test Types ---
    Route::apiResource('test-types', TestTypeController::class);

    // --- Patients ---
    Route::apiResource('patients', PatientController::class);

    // --- Delegates ---
    Route::apiResource('delegates', DelegateController::class);

    // --- Delegate Visits ---
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

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // Mobile Auth QR scanning endpoint
    Route::post('/auth-authorize', [AuthController::class, 'authorizeAuthQr']);
});
