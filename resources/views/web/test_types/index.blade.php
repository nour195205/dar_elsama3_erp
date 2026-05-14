@extends('layouts.app')

@section('content')
<div class="table-header" style="margin-bottom: 2rem;">
    <h2 style="font-size: 1.8rem;">أنواع الفحوصات</h2>
    <a href="{{ route('test-types.create') }}" class="btn btn-primary">
        <i class="fas fa-plus"></i>
        إضافة فحص جديد
    </a>
</div>

<div class="table-container">
    <table>
        <thead>
            <tr>
                <th>اسم الفحص</th>
                <th>السعر الافتراضي</th>
                <th>تاريخ الإضافة</th>
                <th>الإجراءات</th>
            </tr>
        </thead>
        <tbody>
            @forelse($testTypes as $testType)
            <tr>
                <td style="font-weight: 600; color: #fff;">
                    <i class="fas fa-stethoscope" style="margin-left: 0.75rem; color: var(--primary);"></i>
                    {{ $testType->name }}
                </td>
                <td style="font-weight: 700; color: #00e699;">{{ number_format($testType->price, 2) }} ج.م</td>
                <td style="color: var(--text-muted);">{{ \Carbon\Carbon::parse($testType->created_at)->format('Y-m-d') }}</td>
                <td>
                    <div style="display: flex; gap: 1rem;">
                        <a href="{{ route('test-types.edit', $testType->id) }}" style="color: var(--primary); font-size: 1.1rem;" title="تعديل"><i class="fas fa-pen-to-square"></i></a>
                        <form action="{{ route('test-types.destroy', $testType->id) }}" method="POST" onsubmit="return confirm('حذف هذا الفحص؟');" style="display: inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" style="background: none; border: none; color: #ff4d4d; font-size: 1.1rem; cursor: pointer;" title="حذف"><i class="fas fa-trash-can"></i></button>
                        </form>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="4" style="text-align: center; padding: 5rem; color: var(--text-muted);">
                    <i class="fas fa-notes-medical" style="font-size: 4rem; margin-bottom: 1.5rem; display: block; opacity: 0.15;"></i>
                    لا يوجد فحوصات مسجلة حالياً
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
