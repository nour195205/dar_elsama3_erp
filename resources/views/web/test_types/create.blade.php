@extends('layouts.app')

@section('content')
<div class="table-container" style="max-width: 600px; margin: 0 auto;">
    <form action="{{ route('test-types.store') }}" method="POST">
        @csrf
        <div style="display: grid; grid-template-columns: 1fr; gap: 1.5rem; margin-bottom: 2rem;">
            <div class="form-group">
                <label style="display: block; margin-bottom: 0.5rem; color: var(--text-muted);">اسم الفحص</label>
                <input type="text" name="name" required style="width: 100%; padding: 1rem; border-radius: 12px; background: rgba(255,255,255,0.05); border: 1px solid var(--border-color); color: white;" placeholder="مثال: تخطيط سمع">
            </div>
            
            <div class="form-group">
                <label style="display: block; margin-bottom: 0.5rem; color: var(--text-muted);">السعر الافتراضي (ج.م)</label>
                <input type="number" name="price" value="0" step="0.1" required style="width: 100%; padding: 1rem; border-radius: 12px; background: rgba(255,255,255,0.05); border: 1px solid var(--border-color); color: white;">
            </div>
        </div>
        
        <div style="display: flex; justify-content: flex-end; gap: 1rem;">
            <a href="{{ route('test-types.index') }}" class="btn" style="background: rgba(255,255,255,0.05); color: white;">إلغاء</a>
            <button type="submit" class="btn btn-primary">حفظ الفحص</button>
        </div>
    </form>
</div>
@endsection
