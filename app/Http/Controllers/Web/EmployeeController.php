<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Http\Requests\StoreEmployeeRequest;
use App\Http\Requests\UpdateEmployeeRequest;
use App\Support\ActivityLogger;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

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
        $created = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'phone' => $request->phone,
            'role' => $request->role,
            'is_active' => true,
        ]);

        ActivityLogger::record('employee.created', 'تمت إضافة موظف: ' . $created->name, User::class, $created->id);

        return redirect()->route('employees.index')->with('success', 'تم إضافة الموظف بنجاح');
    }

    public function edit($id)
    {
        $employee = User::findOrFail($id);
        if ($employee->role === 'admin' && Auth::user()->role !== 'admin') {
            return redirect()->route('employees.index')->with('error', 'لا يمكن تعديل حساب مسؤول إلا من مسؤول آخر.');
        }

        return view('web.employees.edit', [
            'title' => 'تعديل بيانات الموظف',
            'subtitle' => 'تحديث بيانات ' . $employee->name,
            'employee' => $employee,
        ]);
    }

    public function update(UpdateEmployeeRequest $request, $id)
    {
        $employee = User::findOrFail($id);
        if ($employee->role === 'admin' && Auth::user()->role !== 'admin') {
            return redirect()->route('employees.index')->with('error', 'غير مصرح بتعديل هذا الحساب.');
        }

        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'role' => $request->role,
            'is_active' => $request->boolean('is_active'),
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $employee->update($data);
        ActivityLogger::record('employee.updated', 'تم تحديث بيانات موظف: ' . $employee->name, User::class, $employee->id);

        return redirect()->route('employees.index')->with('success', 'تم تحديث بيانات الموظف');
    }

    public function destroy($id)
    {
        $employee = User::findOrFail($id);

        if ((int) $employee->id === (int) Auth::id()) {
            return redirect()->route('employees.index')->with('error', 'لا يمكنك حذف حسابك الحالي.');
        }

        if ($employee->role === 'admin' && Auth::user()->role !== 'admin') {
            return redirect()->route('employees.index')->with('error', 'لا يمكن حذف حساب مسؤول.');
        }

        if ($employee->role === 'admin' && User::where('role', 'admin')->count() <= 1) {
            return redirect()->route('employees.index')->with('error', 'يجب أن يبقى مسؤول واحد على الأقل في النظام.');
        }

        $name = $employee->name;
        $eid = $employee->id;
        $employee->delete();
        ActivityLogger::record('employee.deleted', 'تم حذف موظف: ' . $name, User::class, $eid);

        return redirect()->route('employees.index')->with('success', 'تم حذف الموظف');
    }
}
