@extends('layouts.app')

@section('content')
<div class="table-container" style="max-width: 760px; margin: 0 auto;">
    @if($isSuperAdmin)
        <div style="background: rgba(0, 230, 153, 0.08); border: 1px solid rgba(0, 230, 153, 0.25); padding: 1.25rem; border-radius: 14px; margin-bottom: 1.5rem;">
            <p style="margin: 0; color: #e2e8f0;">حساب <strong>مسؤول النظام</strong> يملك تلقائياً كل الصلاحيات على كل الأقسام؛ لا حاجة لتعيين يدوي.</p>
        </div>
        <div style="display: flex; justify-content: flex-end;">
            <a href="{{ route('employees.index') }}" class="btn" style="background: rgba(255,255,255,0.06); color: white;">رجوع للقائمة</a>
        </div>
    @else
        <p style="color: var(--text-muted); margin-bottom: 1.25rem;">
            اختر <strong>مجموعات</strong> (تُطبَّق كل صلاحياتها) و/أو صلاحيات فردية إضافية. المجموعات والصلاحيات المباشرة تُدمج معاً.
        </p>

        <form action="{{ route('staff.permissions.update', $employee->id) }}" method="POST">
            @csrf
            @method('PUT')

            <h3 style="font-size: 1rem; margin-bottom: 0.75rem; color: var(--text-muted);">المجموعات</h3>
            <div style="display: grid; gap: 0.6rem; margin-bottom: 1.75rem; max-height: 220px; overflow-y: auto; padding: 0.5rem; border-radius: 12px; border: 1px solid var(--border-color);">
                @forelse($allGroups as $grp)
                <label style="display: flex; align-items: flex-start; gap: 0.65rem; cursor: pointer; font-size: 0.92rem;">
                    <input type="checkbox" name="permission_group_ids[]" value="{{ $grp->id }}"
                        {{ $employee->permissionGroups->contains('id', $grp->id) ? 'checked' : '' }}
                        style="width: 17px; height: 17px; margin-top: 2px; flex-shrink: 0;">
                    <span><strong style="color: #fff;">{{ $grp->name }}</strong>
                        @if($grp->description)<span style="color: var(--text-muted); font-size: 0.85rem;"> — {{ $grp->description }}</span>@endif
                    </span>
                </label>
                @empty
                <p style="color: var(--text-muted); font-size: 0.9rem;">لا توجد مجموعات بعد. أنشئ مجموعة من قائمة «مجموعات الصلاحيات» (مسؤول النظام).</p>
                @endforelse
            </div>

            <h3 style="font-size: 1rem; margin-bottom: 0.75rem; color: var(--text-muted);">صلاحيات إضافية (مباشرة)</h3>
            <div style="display: grid; gap: 0.65rem; margin-bottom: 2rem; max-height: 280px; overflow-y: auto; padding: 0.5rem; border-radius: 12px; border: 1px solid var(--border-color);">
                @foreach($allPermissions as $perm)
                <label style="display: flex; align-items: flex-start; gap: 0.75rem; padding: 0.65rem 0.85rem; border-radius: 10px; background: rgba(255,255,255,0.03); cursor: pointer;">
                    <input type="checkbox" name="permission_ids[]" value="{{ $perm->id }}"
                        {{ $employee->permissions->contains('id', $perm->id) ? 'checked' : '' }}
                        style="width: 17px; height: 17px; margin-top: 2px; flex-shrink: 0;">
                    <span>
                        <span style="font-weight: 600; color: #fff;">{{ $perm->description }}</span>
                        <code style="display: block; font-size: 0.72rem; color: #64748b; margin-top: 0.2rem;">{{ $perm->name }}</code>
                    </span>
                </label>
                @endforeach
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 1rem;">
                <a href="{{ route('employees.index') }}" class="btn" style="background: rgba(255,255,255,0.06); color: white;">رجوع للقائمة</a>
                <button type="submit" class="btn btn-primary">حفظ</button>
            </div>
        </form>
    @endif
</div>
@endsection
