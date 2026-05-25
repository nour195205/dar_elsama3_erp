<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class EmployeeController extends Controller
{
    public function index()
    {
        $employees = User::all();
        return response()->json(['employees' => $employees]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'nullable|string|max:20',
            'hourly_rate' => 'required|numeric|min:0',
            'password' => 'required|string|min:6',
        ]);

        $employee = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'hourly_rate' => $request->hourly_rate,
            'role' => $request->role ?? 'employee',
            'password' => $request->password, // الـ User model يحتوي على hashed cast
        ]);

        return response()->json(['message' => 'Employee added successfully!', 'employee' => $employee]);
    }

    public function update(Request $request, User $employee)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,'.$employee->id,
            'phone' => 'nullable|string|max:20',
            'hourly_rate' => 'required|numeric|min:0',
        ]);

        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'hourly_rate' => $request->hourly_rate,
        ];

        if ($request->has('role')) {
            $data['role'] = $request->role;
        }

        if ($request->filled('password')) {
            $data['password'] = $request->password; // الـ hashed cast يتكفل بالتشفير
        }

        $employee->update($data);

        return response()->json(['message' => 'Employee updated successfully!', 'employee' => $employee]);
    }

    public function destroy(User $employee)
    {
        if ($employee->id === 1) { // Prevents deleting main admin conceptually
            return response()->json(['message' => 'Cannot delete main admin.'], 403);
        }
        $employee->delete();
        return response()->json(['message' => 'Employee deleted successfully!']);
    }

    public function resetDevice(User $employee)
    {
        $employee->update(['device_id' => null]);
        return response()->json(['message' => 'Device binding reset for employee!']);
    }
}
