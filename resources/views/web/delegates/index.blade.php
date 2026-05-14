@extends('layouts.app')

@section('content')
<div class="table-header" style="margin-bottom: 2rem;">
    <h2 style="font-size: 1.8rem;">إدارة المناديب</h2>
    <a href="{{ route('delegates.create') }}" class="btn btn-primary">
        <i class="fas fa-user-plus"></i>
        إضافة مندوب جديد
    </a>
</div>

<div class="table-container">
    <table>
        <thead>
            <tr>
                <th>اسم المندوب</th>
                <th>الشركة</th>
                <th>رقم الهاتف</th>
                <th>ملاحظات</th>
                <th>الإجراءات</th>
            </tr>
        </thead>
        <tbody>
            @forelse($delegates as $delegate)
            <tr>
                <td style="font-weight: 600; color: #fff;">
                    <i class="fas fa-user-tie" style="margin-left: 0.75rem; color: var(--primary);"></i>
                    {{ $delegate->name }}
                </td>
                <td>
                    <span class="badge" style="background: rgba(0, 149, 255, 0.1); color: #0095ff;">
                        {{ $delegate->company }}
                    </span>
                </td>
                <td>{{ $delegate->phone }}</td>
                <td style="color: var(--text-muted);">{{ Str::limit($delegate->notes, 30) }}</td>
                <td>
                    <form action="{{ route('delegates.destroy', $delegate->id) }}" method="POST" onsubmit="return confirm('حذف هذا المندوب؟');" style="display: inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" style="background: none; border: none; color: #ff4d4d; font-size: 1.1rem; cursor: pointer;"><i class="fas fa-trash-can"></i></button>
                    </form>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5" style="text-align: center; padding: 5rem; color: var(--text-muted);">
                    <i class="fas fa-users-slash" style="font-size: 4rem; margin-bottom: 1.5rem; display: block; opacity: 0.15;"></i>
                    لا يوجد مناديب مسجلين في النظام حالياً
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
