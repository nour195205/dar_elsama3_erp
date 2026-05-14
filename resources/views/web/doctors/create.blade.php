@extends('layouts.app')

@section('content')
<div class="table-container" style="max-width: 800px; margin: 0 auto;">
    <form action="{{ route('doctors.store') }}" method="POST">
        @csrf
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 2rem;">
            <div class="form-group">
                <label style="display: block; margin-bottom: 0.5rem; color: var(--text-muted);">اسم الطبيب</label>
                <input type="text" name="name" required style="width: 100%; padding: 1rem; border-radius: 12px; background: rgba(255,255,255,0.05); border: 1px solid var(--border-color); color: white;">
            </div>
            <div class="form-group">
                <label style="display: block; margin-bottom: 0.5rem; color: var(--text-muted);">العنوان</label>
                <input type="text" name="address" style="width: 100%; padding: 1rem; border-radius: 12px; background: rgba(255,255,255,0.05); border: 1px solid var(--border-color); color: white;">
            </div>
            <div class="form-group">
                <label style="display: block; margin-bottom: 0.5rem; color: var(--text-muted);">نوع الطبيب</label>
                <select name="type" style="width: 100%; padding: 1rem; border-radius: 12px; background: rgba(25, 33, 49, 1); border: 1px solid var(--border-color); color: white;">
                    <option value="Internal">طبيب داخلي</option>
                    <option value="External">طبيب محول</option>
                </select>
            </div>
            <div class="form-group">
                <label style="display: block; margin-bottom: 0.5rem; color: var(--text-muted);">نوع العمولة</label>
                <select name="commission_type" style="width: 100%; padding: 1rem; border-radius: 12px; background: rgba(25, 33, 49, 1); border: 1px solid var(--border-color); color: white;">
                    <option value="Percentage">نسبة مئوية (%)</option>
                    <option value="Flat">مبلغ ثابت (ج.م)</option>
                </select>
            </div>
            <div class="form-group">
                <label style="display: block; margin-bottom: 0.5rem; color: var(--text-muted);">قيمة العمولة</label>
                <input type="number" name="commission_value" value="10" step="0.1" style="width: 100%; padding: 1rem; border-radius: 12px; background: rgba(255,255,255,0.05); border: 1px solid var(--border-color); color: white;">
            </div>
        </div>
        
        <div style="display: flex; justify-content: flex-end; gap: 1rem;">
            <a href="{{ route('doctors.index') }}" class="btn" style="background: rgba(255,255,255,0.05); color: white;">إلغاء</a>
            <button type="submit" class="btn btn-primary">حفظ البيانات</button>
        </div>
    </form>
</div>
@endsection
