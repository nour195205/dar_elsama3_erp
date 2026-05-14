@extends('layouts.app')

@section('content')
<div class="table-header" style="margin-bottom: 2rem;">
    <h2 style="font-size: 1.8rem;">سجلات المرضى</h2>
    <a href="{{ route('patients.create') }}" class="btn btn-primary">
        <i class="fas fa-user-plus"></i>
        تسجيل مريض جديد
    </a>
</div>

<div class="table-container">
    <table>
        <thead>
            <tr>
                <th>اسم المريض</th>
                <th>رقم الهاتف</th>
                <th>العمر</th>
                <th>نوع الزيارة</th>
                <th>سعر الفحص</th>
                <th>تاريخ التسجيل</th>
                <th>الإجراءات</th>
            </tr>
        </thead>
        <tbody>
            @forelse($patients as $patient)
            <tr>
                <td style="font-weight: 600; color: #fff;">
                    <i class="fas fa-circle-user" style="margin-left: 0.75rem; color: #a155ff; opacity: 0.8;"></i>
                    {{ $patient->name }}
                </td>
                <td>{{ $patient->phone }}</td>
                <td>{{ $patient->age ?? '---' }} سنة</td>
                <td>
                    @if(($patient->visit_type ?? '') == 'Follow-up')
                        <span class="badge" style="background: rgba(0, 149, 255, 0.1); color: #0095ff;">إعادة</span>
                    @else
                        <span class="badge" style="background: rgba(0, 230, 153, 0.1); color: #00e699;">جديد</span>
                    @endif
                </td>
                <td style="font-weight: 700; color: #00e699;">{{ number_format($patient->test_price ?? 0, 2) }} ج.م</td>
                <td style="color: var(--text-muted);">{{ \Carbon\Carbon::parse($patient->created_at)->format('Y-m-d') }}</td>
                <td>
                    <div style="display: flex; gap: 1rem;">
                        <a href="{{ route('patients.edit', $patient->id) }}" title="تعديل" style="color: var(--primary);"><i class="fas fa-user-pen"></i></a>
                        <form action="{{ route('patients.destroy', $patient->id) }}" method="POST" onsubmit="return confirm('هل أنت متأكد من حذف هذا المريض؟');" style="display: inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" style="background: none; border: none; color: #ff4d4d; font-size: 1rem; cursor: pointer;" title="حذف"><i class="fas fa-trash-can"></i></button>
                        </form>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" style="text-align: center; padding: 5rem; color: var(--text-muted);">
                    <i class="fas fa-users-viewfinder" style="font-size: 4rem; margin-bottom: 1.5rem; display: block; opacity: 0.15;"></i>
                    لم يتم تسجيل أي مرضى في قاعدة البيانات بعد
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
