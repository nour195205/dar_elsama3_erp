@extends('layouts.app')

@section('content')
<div class="table-header" style="margin-bottom: 2rem;">
    <h2 style="font-size: 1.8rem;">إدارة الأطباء</h2>
    <a href="{{ route('doctors.create') }}" class="btn btn-primary">
        <i class="fas fa-plus"></i>
        إضافة طبيب جديد
    </a>
</div>

<div class="table-container">
    <table>
        <thead>
            <tr>
                <th>اسم الطبيب</th>
                <th>نوع الطبيب</th>
                <th>العنوان</th>
                <th>العمولة</th>
                <th>الإجراءات</th>
            </tr>
        </thead>
        <tbody>
            @forelse($doctors as $doctor)
            <tr>
                <td style="font-weight: 600; color: #fff;">
                    <i class="fas fa-user-md" style="margin-left: 0.75rem; color: var(--primary);"></i>
                    {{ $doctor->name }}
                </td>
                <td>
                    @if($doctor->type == 'Internal')
                        <span class="badge" style="background: rgba(0, 230, 153, 0.1); color: #00e699;">طبيب داخلي</span>
                    @else
                        <span class="badge" style="background: rgba(255, 153, 0, 0.1); color: #ff9900;">طبيب محول</span>
                    @endif
                </td>
                <td style="color: var(--text-muted);">{{ $doctor->address ?? '---' }}</td>
                <td style="font-weight: 700;">{{ $doctor->commission_value }} {{ $doctor->commission_type == 'Percentage' ? '%' : 'ج.م' }}</td>
                <td>
                    <div style="display: flex; gap: 1rem;">
                        <a href="{{ route('doctors.edit', $doctor->id) }}" style="color: var(--primary); font-size: 1.1rem;" title="تعديل"><i class="fas fa-pen-to-square"></i></a>
                        <form action="{{ route('doctors.destroy', $doctor->id) }}" method="POST" onsubmit="return confirm('هل أنت متأكد من حذف هذا الطبيب؟');" style="display: inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" style="background: none; border: none; color: #ff4d4d; font-size: 1.1rem; cursor: pointer;" title="حذف"><i class="fas fa-trash-can"></i></button>
                        </form>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5" style="text-align: center; padding: 5rem; color: var(--text-muted);">
                    <i class="fas fa-user-slash" style="font-size: 4rem; margin-bottom: 1.5rem; display: block; opacity: 0.15;"></i>
                    لا يوجد أطباء مسجلين في النظام حالياً
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
