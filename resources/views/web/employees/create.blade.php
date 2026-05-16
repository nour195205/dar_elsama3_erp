@extends('layouts.app')

@section('content')
<div class="table-container" style="max-width: 600px; margin: 0 auto;">
    <form action="{{ route('employees.store') }}" method="POST">
        @csrf
        <div style="display: grid; gap: 1.5rem; margin-bottom: 2rem;">
            @if($errors->any())
            <div style="background: rgba(255,77,77,0.1); border: 1px solid rgba(255,77,77,0.3); padding: 1rem; border-radius: 12px;">
                @foreach($errors->all() as $error)
                <p style="color: #ff4d4d; margin: 0.25rem 0; font-size: 0.9rem;"><i class="fas fa-exclamation-triangle"></i> {{ $error }}</p>
                @endforeach
            </div>
            @endif

            <div class="form-group">
                <label style="display: block; margin-bottom: 0.5rem; color: var(--text-muted);">الاسم</label>
                <input type="text" name="name" value="{{ old('name') }}" required style="width: 100%; padding: 1rem; border-radius: 12px; background: rgba(255,255,255,0.05); border: 1px solid var(--border-color); color: white;">
            </div>
            <div class="form-group">
                <label style="display: block; margin-bottom: 0.5rem; color: var(--text-muted);">البريد</label>
                <input type="email" name="email" value="{{ old('email') }}" required style="width: 100%; padding: 1rem; border-radius: 12px; background: rgba(255,255,255,0.05); border: 1px solid var(--border-color); color: white;">
            </div>
            <div class="form-group">
                <label style="display: block; margin-bottom: 0.5rem; color: var(--text-muted);">كلمة المرور</label>
                <input type="password" name="password" required style="width: 100%; padding: 1rem; border-radius: 12px; background: rgba(255,255,255,0.05); border: 1px solid var(--border-color); color: white;">
            </div>
            <div class="form-group">
                <label style="display: block; margin-bottom: 0.5rem; color: var(--text-muted);">تأكيد كلمة المرور</label>
                <input type="password" name="password_confirmation" required style="width: 100%; padding: 1rem; border-radius: 12px; background: rgba(255,255,255,0.05); border: 1px solid var(--border-color); color: white;">
            </div>
            <div class="form-group">
                <label style="display: block; margin-bottom: 0.5rem; color: var(--text-muted);">الهاتف</label>
                <input type="text" name="phone" value="{{ old('phone') }}" style="width: 100%; padding: 1rem; border-radius: 12px; background: rgba(255,255,255,0.05); border: 1px solid var(--border-color); color: white;">
            </div>
            <div class="form-group">
                <label style="display: block; margin-bottom: 0.5rem; color: var(--text-muted);">الدور</label>
                <select name="role" required style="width: 100%; padding: 1rem; border-radius: 12px; background: rgba(25,33,49,1); border: 1px solid var(--border-color); color: white;">
                    <option value="employee" selected>موظف</option>
                    <option value="manager">مدير</option>
                    <option value="admin">مسؤول</option>
                </select>
            </div>
            <div class="form-group">
                <label style="display: block; margin-bottom: 0.5rem; color: var(--text-muted);">سعر الساعة (ج.م)</label>
                <input type="number" name="hourly_rate" value="{{ old('hourly_rate', 0) }}" step="0.01" min="0" style="width: 100%; padding: 1rem; border-radius: 12px; background: rgba(255,255,255,0.05); border: 1px solid var(--border-color); color: white;">
                <p style="font-size: 0.8rem; color: var(--text-muted); margin-top: 0.4rem;">يُستخدم لحساب المرتب بناءً على ساعات الحضور</p>
            </div>
        </div>
        <div style="display: flex; justify-content: flex-end; gap: 1rem;">
            <a href="{{ route('employees.index') }}" class="btn" style="background: rgba(255,255,255,0.05); color: white;">إلغاء</a>
            <button type="submit" class="btn btn-primary">حفظ</button>
        </div>
    </form>
</div>
@endsection
