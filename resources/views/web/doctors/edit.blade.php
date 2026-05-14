@extends('layouts.app')

@section('content')
<div class="table-container" style="max-width: 800px; margin: 0 auto;">
    <form action="{{ route('doctors.update', $doctor->id) }}" method="POST">
        @csrf
        @method('PUT')
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 2rem;">
            <div class="form-group">
                <label style="display: block; margin-bottom: 0.5rem; color: var(--text-muted);">اسم الطبيب</label>
                <input type="text" name="name" value="{{ $doctor->name }}" required style="width: 100%; padding: 1rem; border-radius: 12px; background: rgba(255,255,255,0.05); border: 1px solid var(--border-color); color: white;">
            </div>
            <div class="form-group">
                <label style="display: block; margin-bottom: 0.5rem; color: var(--text-muted);">العنوان</label>
                <input type="text" name="address" value="{{ $doctor->address }}" style="width: 100%; padding: 1rem; border-radius: 12px; background: rgba(255,255,255,0.05); border: 1px solid var(--border-color); color: white;">
            </div>
            <div class="form-group">
                <label style="display: block; margin-bottom: 0.5rem; color: var(--text-muted);">نوع الطبيب</label>
                <select name="type" style="width: 100%; padding: 1rem; border-radius: 12px; background: rgba(25,33,49,1); border: 1px solid var(--border-color); color: white;">
                    <option value="Internal" {{ $doctor->type == 'Internal' ? 'selected' : '' }}>طبيب داخلي</option>
                    <option value="External" {{ $doctor->type == 'External' ? 'selected' : '' }}>طبيب محول</option>
                </select>
            </div>
            <div class="form-group">
                <label style="display: block; margin-bottom: 0.5rem; color: var(--text-muted);">نوع العمولة</label>
                <select name="commission_type" style="width: 100%; padding: 1rem; border-radius: 12px; background: rgba(25,33,49,1); border: 1px solid var(--border-color); color: white;">
                    <option value="Percentage" {{ $doctor->commission_type == 'Percentage' ? 'selected' : '' }}>نسبة مئوية (%)</option>
                    <option value="Flat" {{ $doctor->commission_type == 'Flat' ? 'selected' : '' }}>مبلغ ثابت (ج.م)</option>
                </select>
            </div>
            <div class="form-group">
                <label style="display: block; margin-bottom: 0.5rem; color: var(--text-muted);">قيمة العمولة</label>
                <input type="number" name="commission_value" value="{{ $doctor->commission_value }}" step="0.1" style="width: 100%; padding: 1rem; border-radius: 12px; background: rgba(255,255,255,0.05); border: 1px solid var(--border-color); color: white;">
            </div>
        </div>
        <div style="display: flex; justify-content: flex-end; gap: 1rem;">
            <a href="{{ route('doctors.index') }}" class="btn" style="background: rgba(255,255,255,0.05); color: white;">إلغاء</a>
            <button type="submit" class="btn btn-primary">تحديث البيانات</button>
        </div>
    </form>
</div>
@endsection
