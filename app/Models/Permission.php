<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    protected $fillable = [
        'name',
        'description',
    ];

    public function users()
    {
        return $this->belongsToMany(User::class, 'user_permissions');
    }

    public function groups()
    {
        return $this->belongsToMany(PermissionGroup::class, 'permission_group_permission');
    }

    /** @var array<string, string> */
    public const PERMISSIONS = [
        'module_patients' => 'المرضى',
        'module_patients_delete' => 'حذف المرضى',
        'module_doctors' => 'الأطباء',
        'module_doctors_delete' => 'حذف الأطباء',
        'module_test_types' => 'أنواع الفحوصات',
        'module_test_types_delete' => 'حذف أنواع الفحوصات',
        'module_delegates' => 'المناديب',
        'module_delegates_delete' => 'حذف المناديب',
        'module_finance' => 'المالية',
        'module_employees' => 'قائمة الموظفين وصلاحياتهم',
        'module_employees_delete' => 'حذف حسابات موظفين',
        'module_activity_logs' => 'سجل النشاط',
        'module_attendance' => 'الحضور',
        'module_settings' => 'الإعدادات',
        'view_reports' => 'تقارير لوحة التحكم',
        'edit_tests' => 'تعديل الفحوصات',
        'view_finances' => 'عرض المالية',
        'edit_employees' => 'تعديل الموظفين',
        'view_attendance' => 'عرض الحضور',
    ];
}
