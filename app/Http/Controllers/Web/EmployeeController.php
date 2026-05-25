<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreEmployeeRequest;
use App\Http\Requests\UpdateEmployeeRequest;
use App\Models\User;
use App\Support\ActivityLogger;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class EmployeeController extends Controller
{
    public function index()
    {
        $employees = User::query()
            ->orderByRaw("CASE WHEN role = 'admin' THEN 0 ELSE 1 END")
            ->orderByDesc('is_active')
            ->orderBy('name')
            ->get();

        return view('web.employees.index', [
            'title' => 'شؤون الموظفين',
            'subtitle' => 'جميع الحسابات بما فيها المسؤولين؛ المعطّلون يظلون في القائمة لإعادة التفعيل.',
            'employees' => $employees,
        ]);
    }

    public function create()
    {
        return view('web.employees.create', [
            'title' => 'إضافة موظف جديد',
            'subtitle' => 'إدخال بيانات الموظف الجديد',
        ]);
    }

    public function store(StoreEmployeeRequest $request)
    {
        $employee = User::create([
            'name'        => $request->name,
            'email'       => $request->email,
            'password'    => $request->password, // الـ User model يحتوي على hashed cast — لا تستخدم Hash::make
            'phone'       => $request->phone,
            'role'        => $request->role,
            'hourly_rate' => $request->hourly_rate ?? 0,
            'is_active'   => $request->has('is_active'),
        ]);

        ActivityLogger::forModel('employee.created', 'تم إضافة موظف: ' . $employee->name, $employee);

        return redirect()->route('employees.index')->with('success', 'تم إضافة الموظف بنجاح');
    }

    public function edit($id)
    {
        $employee = User::findOrFail($id);

        return view('web.employees.edit', [
            'title' => 'تعديل بيانات الموظف',
            'subtitle' => 'تحديث بيانات ' . $employee->name,
            'employee' => $employee,
        ]);
    }

    public function update(UpdateEmployeeRequest $request, $id)
    {
        $employee = User::findOrFail($id);

        $data = [
            'name'        => $request->name,
            'email'       => $request->email,
            'phone'       => $request->phone,
            'role'        => $request->role,
            'hourly_rate' => $request->hourly_rate ?? 0,
            'is_active'   => $request->has('is_active'),
        ];

        if ($request->filled('password')) {
            $data['password'] = $request->password; // الـ hashed cast يتكفل بالتشفير
        }

        $employee->update($data);

        ActivityLogger::forModel('employee.updated', 'تم تحديث بيانات موظف: ' . $employee->name, $employee);

        return redirect()->route('employees.index')->with('success', 'تم تحديث بيانات الموظف');
    }

    public function destroy($id)
    {
        $employee = User::findOrFail($id);

        if ($employee->id === (int) Auth::id()) {
            return redirect()->route('employees.index')->with('error', 'لا يمكنك حذف حسابك الحالي.');
        }

        if ($employee->role === 'admin' && Auth::user()->role !== 'admin') {
            return redirect()->route('employees.index')->with('error', 'لا يمكن حذف حساب مسؤول.');
        }

        $label = $employee->name;
        $eid = $employee->id;
        $employee->delete();

        ActivityLogger::record('employee.deleted', 'تم حذف موظف: ' . $label, User::class, $eid);

        return redirect()->route('employees.index')->with('success', 'تم حذف الموظف');
    }

    public function pairDevice($id)
    {
        $employee = User::findOrFail($id);

        // Generate a one-time pairing token and cache it for 5 minutes
        $pairToken = bin2hex(random_bytes(20));
        Cache::put('pair_token:' . $pairToken, [
            'user_id' => $employee->id,
            'user_name' => $employee->name,
        ], 300); // 5 minutes

        ActivityLogger::forModel('employee.pair_requested', 'طلب ربط جهاز للموظف: ' . $employee->name, $employee);

        return redirect()->route('employees.edit', $id)->with('pair_token', $pairToken);
    }

    public function unpairDevice($id)
    {
        $employee = User::findOrFail($id);

        $employee->update(['device_id' => null]);

        ActivityLogger::forModel('employee.unpaired', 'تم فك ربط جهاز الموظف: ' . $employee->name, $employee);

        return redirect()->route('employees.edit', $id)->with('success', 'تم فك ربط الجهاز بنجاح.');
    }
}
