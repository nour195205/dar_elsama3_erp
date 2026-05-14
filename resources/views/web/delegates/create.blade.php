@extends('layouts.app')

@section('content')
<div class="table-container" style="max-width: 600px; margin: 0 auto;">
    <form action="{{ route('delegates.store') }}" method="POST">
        @csrf
        <div style="display: grid; grid-template-columns: 1fr; gap: 1.5rem; margin-bottom: 2rem;">
            <div class="form-group">
                <label style="display: block; margin-bottom: 0.5rem; color: var(--text-muted);">اسم المندوب</label>
                <input type="text" name="name" required style="width: 100%; padding: 1rem; border-radius: 12px; background: rgba(255,255,255,0.05); border: 1px solid var(--border-color); color: white;">
            </div>
            
            <div class="form-group">
                <label style="display: block; margin-bottom: 0.5rem; color: var(--text-muted);">الشركة التابع لها</label>
                <input type="text" name="company" required style="width: 100%; padding: 1rem; border-radius: 12px; background: rgba(255,255,255,0.05); border: 1px solid var(--border-color); color: white;">
            </div>

            <div class="form-group">
                <label style="display: block; margin-bottom: 0.5rem; color: var(--text-muted);">رقم الهاتف</label>
                <input type="text" name="phone" required style="width: 100%; padding: 1rem; border-radius: 12px; background: rgba(255,255,255,0.05); border: 1px solid var(--border-color); color: white;">
            </div>

            <div class="form-group">
                <label style="display: block; margin-bottom: 0.5rem; color: var(--text-muted);">ملاحظات</label>
                <textarea name="notes" rows="3" style="width: 100%; padding: 1rem; border-radius: 12px; background: rgba(255,255,255,0.05); border: 1px solid var(--border-color); color: white; resize: vertical;"></textarea>
            </div>
        </div>
        
        <div style="display: flex; justify-content: flex-end; gap: 1rem;">
            <a href="{{ route('delegates.index') }}" class="btn" style="background: rgba(255,255,255,0.05); color: white;">إلغاء</a>
            <button type="submit" class="btn btn-primary">حفظ المندوب</button>
        </div>
    </form>
</div>
@endsection
