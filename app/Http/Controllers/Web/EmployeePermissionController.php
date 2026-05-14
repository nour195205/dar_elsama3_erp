<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Models\PermissionGroup;
use App\Models\User;
use App\Support\ActivityLogger;
use Illuminate\Http\Request;

class EmployeePermissionController extends Controller
{
    public function edit(string $id)
    {
        $employee = User::findOrFail($id);
        $employee->load(['permissions', 'permissionGroups']);
        $allPermissions = Permission::query()->orderBy('description')->get();
        $allGroups = PermissionGroup::query()->orderBy('name')->get();

        return view('web.employees.permissions', [
            'title' => 'صلاحيات الموظف',
            'subtitle' => 'المستخدم: ' . $employee->name,
            'employee' => $employee,
            'allPermissions' => $allPermissions,
            'allGroups' => $allGroups,
            'isSuperAdmin' => $employee->role === 'admin',
        ]);
    }

    public function update(Request $request, string $id)
    {
        $employee = User::findOrFail($id);

        if ($employee->role === 'admin') {
            return redirect()
                ->route('staff.permissions.edit', $employee->id)
                ->with('success', 'حساب المسؤول يملك جميع الصلاحيات تلقائياً ولا يحتاج تعييناً يدوياً.');
        }

        $ids = $request->input('permission_ids', []);
        if (! is_array($ids)) {
            $ids = [];
        }

        $groupIds = $request->input('permission_group_ids', []);
        if (! is_array($groupIds)) {
            $groupIds = [];
        }

        $valid = Permission::query()->whereIn('id', $ids)->pluck('id')->all();
        $validGroups = PermissionGroup::query()->whereIn('id', $groupIds)->pluck('id')->all();

        $employee->permissions()->sync($valid);
        $employee->permissionGroups()->sync($validGroups);

        ActivityLogger::record(
            'employee.permissions_updated',
            'تم تحديث صلاحيات/مجموعات للموظف: ' . $employee->name,
            User::class,
            $employee->id,
            ['permission_ids' => $valid, 'permission_group_ids' => $validGroups]
        );

        return redirect()
            ->route('staff.permissions.edit', $employee->id)
            ->with('success', 'تم حفظ الصلاحيات والمجموعات');
    }
}
