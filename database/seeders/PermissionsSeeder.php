<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\PermissionGroup;
use Illuminate\Database\Seeder;

class PermissionsSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            ['name' => 'module_patients', 'description' => 'قسم المرضى (عرض وتسجيل وتعديل)'],
            ['name' => 'module_patients_delete', 'description' => 'حذف سجلات المرضى'],
            ['name' => 'module_doctors', 'description' => 'قسم الأطباء'],
            ['name' => 'module_doctors_delete', 'description' => 'حذف أطباء'],
            ['name' => 'module_test_types', 'description' => 'أنواع الفحوصات'],
            ['name' => 'module_test_types_delete', 'description' => 'حذف أنواع فحوصات'],
            ['name' => 'module_delegates', 'description' => 'المناديب'],
            ['name' => 'module_delegates_delete', 'description' => 'حذف مناديب'],
            ['name' => 'module_finance', 'description' => 'المالية والتقارير المالية'],
            ['name' => 'module_employees', 'description' => 'قائمة الموظفين وصلاحياتهم'],
            ['name' => 'module_employees_delete', 'description' => 'حذف حسابات موظفين'],
            ['name' => 'module_attendance', 'description' => 'الحضور والربط'],
            ['name' => 'module_settings', 'description' => 'إعدادات النظام'],
            ['name' => 'module_activity_logs', 'description' => 'سجل النشاط والمراجعة'],
            ['name' => 'view_reports', 'description' => 'عرض تقارير لوحة التحكم'],
            ['name' => 'edit_tests', 'description' => 'تعديل الفحوصات (قديم — يعادل module_test_types)'],
            ['name' => 'view_finances', 'description' => 'عرض المالية (قديم — يعادل module_finance)'],
            ['name' => 'edit_employees', 'description' => 'تعديل الموظفين (قديم — يعادل module_employees)'],
            ['name' => 'view_attendance', 'description' => 'عرض الحضور (قديم — يعادل module_attendance)'],
        ];

        foreach ($permissions as $permission) {
            Permission::updateOrCreate(
                ['name' => $permission['name']],
                ['description' => $permission['description']]
            );
        }

        $group = PermissionGroup::updateOrCreate(
            ['name' => 'موظف عيادة — أساسي'],
            ['description' => 'وصول للعيادة والحضور والوحة بدون مالية أو إعدادات']
        );

        $attachNames = [
            'module_patients',
            'module_doctors',
            'module_test_types',
            'module_delegates',
            'module_attendance',
            'module_employees',
            'module_activity_logs',
            'view_reports',
        ];

        $ids = Permission::query()->whereIn('name', $attachNames)->pluck('id')->all();
        $group->permissions()->sync($ids);
    }
}
