@extends('layouts.app')

@section('content')
<div class="table-container" style="max-width: 800px; margin: 0 auto;">
    <form action="{{ route('patients.update', $patient->id) }}" method="POST">
        @csrf
        @method('PUT')
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 2rem;">
            <div class="form-group" style="grid-column: span 2;">
                <label style="display: block; margin-bottom: 0.5rem; color: var(--text-muted);">اسم المريض</label>
                <input type="text" name="name" value="{{ $patient->name }}" required style="width: 100%; padding: 1rem; border-radius: 12px; background: rgba(255,255,255,0.05); border: 1px solid var(--border-color); color: white;">
            </div>
            <div class="form-group">
                <label style="display: block; margin-bottom: 0.5rem; color: var(--text-muted);">الهاتف</label>
                <input type="text" name="phone" value="{{ $patient->phone }}" style="width: 100%; padding: 1rem; border-radius: 12px; background: rgba(255,255,255,0.05); border: 1px solid var(--border-color); color: white;">
            </div>
            <div class="form-group">
                <label style="display: block; margin-bottom: 0.5rem; color: var(--text-muted);">العمر</label>
                <input type="number" name="age" value="{{ $patient->age }}" style="width: 100%; padding: 1rem; border-radius: 12px; background: rgba(255,255,255,0.05); border: 1px solid var(--border-color); color: white;">
            </div>
            <div class="form-group" style="grid-column: span 2;">
                <label style="display: block; margin-bottom: 0.5rem; color: var(--text-muted);">العنوان</label>
                <input type="text" name="address" value="{{ $patient->address }}" style="width: 100%; padding: 1rem; border-radius: 12px; background: rgba(255,255,255,0.05); border: 1px solid var(--border-color); color: white;">
            </div>
            <div class="form-group">
                <label style="display: block; margin-bottom: 0.5rem; color: var(--text-muted);">نوع الزيارة</label>
                <select name="visit_type" style="width: 100%; padding: 1rem; border-radius: 12px; background: rgba(25,33,49,1); border: 1px solid var(--border-color); color: white;">
                    <option value="Initial" {{ ($patient->visit_type ?? '') == 'Initial' ? 'selected' : '' }}>كشف جديد</option>
                    <option value="Follow-up" {{ ($patient->visit_type ?? '') == 'Follow-up' ? 'selected' : '' }}>إعادة كشف</option>
                </select>
            </div>
        </div>
        <div style="display: flex; justify-content: flex-end; gap: 1rem;">
            <a href="{{ route('patients.index') }}" class="btn" style="background: rgba(255,255,255,0.05); color: white;">إلغاء</a>
            <button type="submit" class="btn btn-primary">تحديث البيانات</button>
        </div>
    </form>
</div>
@endsection
