<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class EmployeeController extends Controller
{
    public function index()
    {
        $employees = DB::table('users')
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

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:4',
            'role' => 'required|in:admin,employee',
        ]);

        DB::table('users')->insert([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'phone' => $request->phone,
            'role' => $request->role,
            'is_active' => $request->has('is_active'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('employees.index')->with('success', 'تم إضافة الموظف بنجاح');
    }

    public function edit($id)
    {
        $employee = DB::table('users')->find($id);
        if (!$employee) abort(404);

        return view('web.employees.edit', [
            'title' => 'تعديل بيانات الموظف',
            'subtitle' => 'تحديث بيانات ' . $employee->name,
            'employee' => $employee
        ]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,'.$id,
            'role' => 'required|in:admin,employee',
        ]);

        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'role' => $request->role,
            'is_active' => $request->has('is_active'),
            'updated_at' => now(),
        ];
        
        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        DB::table('users')->where('id', $id)->update($data);

        return redirect()->route('employees.index')->with('success', 'تم تحديث بيانات الموظف');
    }

    public function destroy($id)
    {
        $employee = DB::table('users')->where('id', $id)->first();
        if (!$employee) return redirect()->route('employees.index')->with('error', 'الموظف غير موجود.');

        if ((int) $employee->id === (int) Auth::id()) {
            return redirect()->route('employees.index')->with('error', 'لا يمكنك حذف حسابك الحالي.');
        }

        if ($employee->role === 'admin' && Auth::user()->role !== 'admin') {
            return redirect()->route('employees.index')->with('error', 'لا يمكن حذف حساب مسؤول.');
        }

        DB::table('users')->where('id', $id)->delete();

        return redirect()->route('employees.index')->with('success', 'تم حذف الموظف');
    }

    public function pairDevice($id)
    {
        $employee = DB::table('users')->find($id);
        if (!$employee) return redirect()->route('employees.index')->with('error', 'الموظف غير موجود.');

        // Generate a one-time pairing token and cache it for 5 minutes
        $pairToken = bin2hex(random_bytes(20));
        \Illuminate\Support\Facades\Cache::put('pair_token:' . $pairToken, [
            'user_id' => $id,
            'user_name' => $employee->name,
        ], 300); // 5 minutes

        return redirect()->route('employees.edit', $id)->with('pair_token', $pairToken);
    }

    public function unpairDevice($id)
    {
        $employee = DB::table('users')->find($id);
        if (!$employee) return redirect()->route('employees.index')->with('error', 'الموظف غير موجود.');

        DB::table('users')->where('id', $id)->update([
            'device_id' => null,
            'updated_at' => now(),
        ]);

        return redirect()->route('employees.edit', $id)->with('success', 'تم فك ربط الجهاز بنجاح.');
    }
}
