@extends('layouts.app')

@section('content')
<div class="table-header" style="margin-bottom: 2rem;">
    <div>
        <h2 style="font-size: 1.8rem;">شؤون الموظفين</h2>
        <p style="color: var(--text-muted); font-size: 0.9rem; margin-top: 0.35rem;">يظهر الموظفون النشطون والمعطّلون؛ يمكنك إعادة التفعيل من تعديل البيانات أو من صفحة الصلاحيات.</p>
    </div>
    @if(auth()->user()->hasPermission('module_employees'))
    <a href="{{ route('employees.create') }}" class="btn btn-primary">
        <i class="fas fa-user-plus"></i>
        إضافة موظف جديد
    </a>
    @endif
</div>

<div class="table-scroll-wrap table-container">
    <table style="min-width: 920px;">
        <thead>
            <tr>
                <th>الاسم</th>
                <th>البريد الإلكتروني</th>
                <th>رقم الهاتف</th>
                <th>الدور الوظيفي</th>
                <th>الحالة</th>
                <th>ربط الجهاز</th>
                <th>الصلاحيات</th>
                <th>الإجراءات</th>
            </tr>
        </thead>
        <tbody>
            @forelse($employees as $employee)
            <tr>
                <td style="font-weight: 600; color: #fff;">
                    <i class="fas fa-user-tie" style="margin-left: 0.75rem; color: var(--primary);"></i>
                    {{ $employee->name }}
                </td>
                <td style="color: var(--text-muted);">{{ $employee->email ?? '---' }}</td>
                <td>{{ $employee->phone ?? '---' }}</td>
                <td>
                    <span class="badge" style="background: rgba(0, 149, 255, 0.1); color: #0095ff;">
                        {{ $employee->role == 'admin' ? 'مسؤول' : ($employee->role == 'manager' ? 'مدير' : 'موظف') }}
                    </span>
                </td>
                <td>
                    @if($employee->is_active)
                        <span class="badge" style="background: rgba(0, 230, 153, 0.1); color: #00e699;">نشط</span>
                    @else
                        <span class="badge" style="background: rgba(255, 77, 77, 0.1); color: #ff4d4d;">معطل</span>
                    @endif
                </td>
                <td>
                    @if($employee->device_id)
                        <span class="badge" style="background: rgba(0, 230, 153, 0.1); color: #00e699;" title="{{ $employee->device_id }}">
                            <i class="fas fa-mobile-alt" style="margin-left: 0.3rem;"></i>مربوط
                        </span>
                    @else
                        <span class="badge" style="background: rgba(255, 165, 0, 0.1); color: #ffa500;">غير مربوط</span>
                    @endif
                </td>
                <td>
                    @if(auth()->user()->hasPermission('module_employees'))
                    <a href="{{ route('staff.permissions.edit', $employee->id) }}" style="color: #a78bfa; font-size: 1rem;" title="صلاحيات">
                        <i class="fas fa-key"></i>
                    </a>
                    @else
                    <span style="color: var(--text-muted);">—</span>
                    @endif
                </td>
                <td>
                    <div style="display: flex; gap: 1rem; align-items: center;">
                        @if(auth()->user()->hasPermission('module_employees'))
                        <a href="{{ route('employees.edit', $employee->id) }}" style="color: var(--primary); font-size: 1.1rem;" title="تعديل"><i class="fas fa-pen-to-square"></i></a>
                        @endif
                        @if(auth()->user()->hasPermission('module_employees_delete') && ($employee->role !== 'admin' || auth()->user()->role === 'admin'))
                        <form action="{{ route('employees.destroy', $employee->id) }}" method="POST" onsubmit="return confirm('هل أنت متأكد من حذف هذا الموظف؟');" style="display: inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" style="background: none; border: none; color: #ff4d4d; font-size: 1.1rem; cursor: pointer;" title="حذف"><i class="fas fa-trash-can"></i></button>
                        </form>
                        @endif
                        @if(! auth()->user()->hasPermission('module_employees') && ! auth()->user()->hasPermission('module_employees_delete'))
                        <span style="color: var(--text-muted); font-size: 0.85rem;">—</span>
                        @endif
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="8" style="text-align: center; padding: 5rem; color: var(--text-muted);">
                    <i class="fas fa-users-slash" style="font-size: 4rem; margin-bottom: 1.5rem; display: block; opacity: 0.15;"></i>
                    لا يوجد موظفين مسجلين حالياً
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
