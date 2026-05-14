@extends('layouts.app')

@section('content')
<div class="table-header" style="margin-bottom: 1.5rem; display: flex; flex-wrap: wrap; justify-content: space-between; gap: 1rem; align-items: center;">
    <div>
        <h2 style="font-size: 1.8rem;">مجموعات الصلاحيات</h2>
        <p style="color: var(--text-muted); margin-top: 0.35rem;">عرّف مجموعة مرة واحدة ثم اربطها بالموظفين من صفحة صلاحيات كل موظف.</p>
    </div>
    <a href="{{ route('permission-groups.create') }}" class="btn btn-primary"><i class="fas fa-layer-group"></i> مجموعة جديدة</a>
</div>

<div class="table-scroll-wrap table-container">
    <table style="min-width: 640px;">
        <thead>
            <tr>
                <th>الاسم</th>
                <th>الوصف</th>
                <th>عدد الصلاحيات</th>
                <th>الإجراءات</th>
            </tr>
        </thead>
        <tbody>
            @forelse($groups as $g)
            <tr>
                <td style="font-weight: 600;">{{ $g->name }}</td>
                <td style="color: var(--text-muted);">{{ $g->description ?? '—' }}</td>
                <td>{{ $g->permissions_count }}</td>
                <td>
                    <a href="{{ route('permission-groups.edit', $g) }}" style="color: var(--primary); margin-left: 0.75rem;"><i class="fas fa-pen-to-square"></i></a>
                    <form action="{{ route('permission-groups.destroy', $g) }}" method="POST" style="display:inline;" onsubmit="return confirm('حذف هذه المجموعة؟');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" style="background:none;border:none;color:#ff4d4d;cursor:pointer;"><i class="fas fa-trash-can"></i></button>
                    </form>
                </td>
            </tr>
            @empty
            <tr><td colspan="4" style="text-align:center;padding:3rem;color:var(--text-muted);">لا توجد مجموعات بعد</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
