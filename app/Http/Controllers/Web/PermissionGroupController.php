<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Models\PermissionGroup;
use App\Support\ActivityLogger;
use Illuminate\Http\Request;

class PermissionGroupController extends Controller
{
    public function index()
    {
        $groups = PermissionGroup::query()->withCount('permissions')->orderBy('name')->get();

        return view('web.permission_groups.index', [
            'title' => 'مجموعات الصلاحيات',
            'subtitle' => 'تجميع صلاحيات متعددة لتعيينها دفعة واحدة للموظفين',
            'groups' => $groups,
        ]);
    }

    public function create()
    {
        $permissions = Permission::query()->orderBy('description')->get();

        return view('web.permission_groups.create', [
            'title' => 'مجموعة صلاحيات جديدة',
            'subtitle' => 'اختر الاسم والصلاحيات المضمنة',
            'permissions' => $permissions,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:120',
            'description' => 'nullable|string|max:500',
            'permission_ids' => 'nullable|array',
            'permission_ids.*' => 'integer|exists:permissions,id',
        ]);

        $group = PermissionGroup::create([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
        ]);

        $ids = $request->input('permission_ids', []);
        $group->permissions()->sync(is_array($ids) ? $ids : []);

        ActivityLogger::record('permission_group.created', 'تم إنشاء مجموعة صلاحيات: ' . $group->name);

        return redirect()->route('permission-groups.index')->with('success', 'تم حفظ المجموعة');
    }

    public function edit(PermissionGroup $permission_group)
    {
        $permissions = Permission::query()->orderBy('description')->get();
        $permission_group->load('permissions');

        return view('web.permission_groups.edit', [
            'title' => 'تعديل مجموعة الصلاحيات',
            'subtitle' => $permission_group->name,
            'group' => $permission_group,
            'permissions' => $permissions,
        ]);
    }

    public function update(Request $request, PermissionGroup $permission_group)
    {
        $data = $request->validate([
            'name' => 'required|string|max:120',
            'description' => 'nullable|string|max:500',
            'permission_ids' => 'nullable|array',
            'permission_ids.*' => 'integer|exists:permissions,id',
        ]);

        $permission_group->update([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
        ]);

        $ids = $request->input('permission_ids', []);
        $permission_group->permissions()->sync(is_array($ids) ? $ids : []);

        ActivityLogger::record('permission_group.updated', 'تم تحديث مجموعة صلاحيات: ' . $permission_group->name);

        return redirect()->route('permission-groups.index')->with('success', 'تم تحديث المجموعة');
    }

    public function destroy(PermissionGroup $permission_group)
    {
        $name = $permission_group->name;
        $permission_group->delete();

        ActivityLogger::record('permission_group.deleted', 'تم حذف مجموعة صلاحيات: ' . $name);

        return redirect()->route('permission-groups.index')->with('success', 'تم حذف المجموعة');
    }
}
