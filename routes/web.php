<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\ClinicController;
use App\Http\Controllers\Web\FinanceController;
use App\Http\Controllers\Web\EmployeeController;
use App\Http\Controllers\Web\AttendanceController;
use App\Http\Controllers\Web\SettingsController;
use App\Http\Controllers\Web\ActivityLogController;
use App\Http\Controllers\Web\EmployeePermissionController;
use App\Http\Controllers\Web\PermissionGroupController;
use App\Http\Controllers\Api\GlobalSearchController;

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('dashboard')
        : redirect()->route('login');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [SettingsController::class, 'loginForm'])->name('login');
    Route::post('/login', [SettingsController::class, 'loginSubmit'])->name('login.submit');
});

// Public mobile-facing pages (accessed by scanning QR codes)
Route::get('/attend-process', function () {
    return view('attend-process');
})->name('attend.process');

Route::get('/setup-phone', function () {
    return view('setup-phone');
})->name('setup.phone');

Route::middleware('auth')->group(function () {
    Route::get('/api/qr-generate', function () {
        $token = \Illuminate\Support\Str::random(40);
        \Illuminate\Support\Facades\Cache::put('current_qr_token', $token, 15);
        return response()->json(['token' => $token]);
    })->middleware(['permission:module_attendance', 'throttle:qr-generate']);

    Route::post('/logout', [SettingsController::class, 'logout'])->name('logout');

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/search/global', GlobalSearchController::class)->name('global.search');

    Route::prefix('patients')->middleware('permission:module_patients')->name('patients.')->group(function () {
        Route::get('/', [ClinicController::class, 'patientsIndex'])->name('index');
        Route::get('/create', [ClinicController::class, 'patientsCreate'])->name('create');
        Route::post('/', [ClinicController::class, 'patientsStore'])->name('store');
        Route::get('/{id}/edit', [ClinicController::class, 'patientsEdit'])->name('edit');
        Route::put('/{id}', [ClinicController::class, 'patientsUpdate'])->name('update');
        Route::delete('/{id}', [ClinicController::class, 'patientsDestroy'])
            ->middleware('permission:module_patients_delete')
            ->name('destroy');
    });

    Route::prefix('doctors')->middleware('permission:module_doctors')->name('doctors.')->group(function () {
        Route::get('/', [ClinicController::class, 'doctorsIndex'])->name('index');
        Route::get('/create', [ClinicController::class, 'doctorsCreate'])->name('create');
        Route::post('/', [ClinicController::class, 'doctorsStore'])->name('store');
        Route::get('/{id}/edit', [ClinicController::class, 'doctorsEdit'])->name('edit');
        Route::put('/{id}', [ClinicController::class, 'doctorsUpdate'])->name('update');
        Route::delete('/{id}', [ClinicController::class, 'doctorsDestroy'])
            ->middleware('permission:module_doctors_delete')
            ->name('destroy');
    });

    Route::prefix('test-types')->middleware('permission:module_test_types')->name('test-types.')->group(function () {
        Route::get('/', [ClinicController::class, 'testTypesIndex'])->name('index');
        Route::get('/create', [ClinicController::class, 'testTypesCreate'])->name('create');
        Route::post('/', [ClinicController::class, 'testTypesStore'])->name('store');
        Route::get('/{id}/edit', [ClinicController::class, 'testTypesEdit'])->name('edit');
        Route::put('/{id}', [ClinicController::class, 'testTypesUpdate'])->name('update');
        Route::delete('/{id}', [ClinicController::class, 'testTypesDestroy'])
            ->middleware('permission:module_test_types_delete')
            ->name('destroy');
    });

    Route::prefix('delegates')->middleware('permission:module_delegates')->name('delegates.')->group(function () {
        Route::get('/', [ClinicController::class, 'delegatesIndex'])->name('index');
        Route::get('/create', [ClinicController::class, 'delegatesCreate'])->name('create');
        Route::post('/', [ClinicController::class, 'delegatesStore'])->name('store');
        Route::delete('/{id}', [ClinicController::class, 'delegatesDestroy'])
            ->middleware('permission:module_delegates_delete')
            ->name('destroy');
    });

    Route::prefix('finance')->middleware('permission:module_finance')->name('finance.')->group(function () {
        Route::get('/', [FinanceController::class, 'index'])->name('index');
        Route::get('/reports', [FinanceController::class, 'reports'])->name('reports');
        Route::get('/reports/export', [FinanceController::class, 'exportCsv'])->name('reports.export');
        Route::post('/store', [FinanceController::class, 'storeTransaction'])->name('store');
        Route::get('/doctor-payouts', [FinanceController::class, 'doctorPayouts'])->name('doctor_payouts');
        Route::post('/doctor-payouts/mark', [FinanceController::class, 'markPayoutsPaid'])->name('doctor_payouts.mark');
        Route::post('/doctor-payouts/{id}/mark', [FinanceController::class, 'markPayoutIndividual'])->name('doctor_payouts.mark_individual');
    });

    Route::prefix('settings')->middleware('permission:module_settings')->name('settings.')->group(function () {
        Route::get('/', [SettingsController::class, 'index'])->name('index');
        Route::post('/update-clinic', [SettingsController::class, 'updateClinic'])->name('update_clinic');
        Route::post('/update-password', [SettingsController::class, 'updatePassword'])->name('update_password');
    });

    Route::prefix('employees')->middleware('permission:module_employees')->name('employees.')->group(function () {
        Route::get('/', [EmployeeController::class, 'index'])->name('index');
        Route::get('/create', [EmployeeController::class, 'create'])->name('create');
        Route::post('/', [EmployeeController::class, 'store'])->name('store');
        Route::get('/{id}/edit', [EmployeeController::class, 'edit'])->name('edit');
        Route::put('/{id}', [EmployeeController::class, 'update'])->name('update');
        Route::delete('/{id}', [EmployeeController::class, 'destroy'])
            ->middleware('permission:module_employees_delete')
            ->name('destroy');
        Route::post('/{id}/pair-device', [EmployeeController::class, 'pairDevice'])->name('pair_device');
        Route::post('/{id}/unpair-device', [EmployeeController::class, 'unpairDevice'])->name('unpair_device');
    });

    Route::get('staff/activity-logs', [ActivityLogController::class, 'index'])
        ->middleware('permission:module_activity_logs')
        ->name('staff.activity_logs');

    Route::prefix('staff')->name('staff.')->middleware('permission:module_employees')->group(function () {
        Route::get('{id}/permissions', [EmployeePermissionController::class, 'edit'])->name('permissions.edit');
        Route::put('{id}/permissions', [EmployeePermissionController::class, 'update'])->name('permissions.update');
    });

    Route::prefix('attendance')->middleware('permission:module_attendance')->name('attendance.')->group(function () {
        Route::get('/', [AttendanceController::class, 'index'])->name('index');
        Route::get('/scanner', [AttendanceController::class, 'scanner'])->name('scanner');
    });

    Route::middleware('admin.web')->prefix('permission-groups')->name('permission-groups.')->group(function () {
        Route::get('/', [PermissionGroupController::class, 'index'])->name('index');
        Route::get('/create', [PermissionGroupController::class, 'create'])->name('create');
        Route::post('/', [PermissionGroupController::class, 'store'])->name('store');
        Route::get('/{permission_group}/edit', [PermissionGroupController::class, 'edit'])->name('edit');
        Route::put('/{permission_group}', [PermissionGroupController::class, 'update'])->name('update');
        Route::delete('/{permission_group}', [PermissionGroupController::class, 'destroy'])->name('destroy');
    });
});
