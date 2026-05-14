@extends('layouts.app')

@section('content')
<div class="table-header" style="margin-bottom: 1.5rem; display: flex; flex-wrap: wrap; gap: 1rem; align-items: center; justify-content: space-between;">
    <div>
        <h2 style="font-size: 1.8rem;">سجل النشاط</h2>
        <p style="color: var(--text-muted); margin-top: 0.35rem;">يُسجَّل المستخدم الحالي عند تسجيل الدخول؛ الإجراءات من دون حساب تظهر بدون اسم.</p>
    </div>
</div>

<form method="get" action="{{ route('staff.activity_logs') }}" class="table-container" style="padding: 1rem 1.25rem; margin-bottom: 1.5rem; display: flex; flex-wrap: wrap; gap: 1rem; align-items: flex-end;">
    <div class="form-group" style="min-width: 200px;">
        <label style="display: block; margin-bottom: 0.35rem; color: var(--text-muted); font-size: 0.85rem;">المستخدم</label>
        <select name="user_id" style="width: 100%; padding: 0.75rem; border-radius: 10px; background: rgba(25,33,49,1); border: 1px solid var(--border-color); color: white;">
            <option value="">الكل</option>
            @foreach($actors as $a)
                <option value="{{ $a->id }}" {{ (string)$filterUserId === (string)$a->id ? 'selected' : '' }}>{{ $a->name }} — {{ $a->email }}</option>
            @endforeach
        </select>
    </div>
    <div class="form-group" style="min-width: 180px;">
        <label style="display: block; margin-bottom: 0.35rem; color: var(--text-muted); font-size: 0.85rem;">نوع الإجراء (يحتوي)</label>
        <input type="text" name="action" value="{{ $filterAction }}" placeholder="مثال: patient" style="width: 100%; padding: 0.75rem; border-radius: 10px; background: rgba(255,255,255,0.05); border: 1px solid var(--border-color); color: white;">
    </div>
    <button type="submit" class="btn btn-primary">تصفية</button>
    <a href="{{ route('staff.activity_logs') }}" class="btn" style="background: rgba(255,255,255,0.06); color: white;">إعادة ضبط</a>
</form>

<div class="table-container">
    <table>
        <thead>
            <tr>
                <th>الوقت</th>
                <th>المستخدم</th>
                <th>الإجراء</th>
                <th>التفاصيل</th>
                <th>مرجع</th>
            </tr>
        </thead>
        <tbody>
            @forelse($logs as $log)
            <tr>
                <td style="color: var(--text-muted); white-space: nowrap;">{{ $log->created_at->format('Y-m-d H:i') }}</td>
                <td style="font-weight: 600;">
                    @if($log->user)
                        {{ $log->user->name }}
                    @else
                        <span style="color: var(--text-muted);">—</span>
                    @endif
                </td>
                <td><code style="font-size: 0.8rem; color: #7dd3fc;">{{ $log->action }}</code></td>
                <td>{{ $log->description }}</td>
                <td style="font-size: 0.85rem; color: var(--text-muted);">
                    @if($log->subject_type)
                        {{ class_basename($log->subject_type) }} #{{ $log->subject_id }}
                    @else
                        —
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5" style="text-align: center; padding: 3rem; color: var(--text-muted);">لا توجد سجلات بعد</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div style="margin-top: 1.5rem;">
    {{ $logs->links() }}
</div>
@endsection
