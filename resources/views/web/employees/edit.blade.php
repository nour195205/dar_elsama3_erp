@extends('layouts.app')

@section('content')
<div class="table-container" style="max-width: 600px; margin: 0 auto;">
    <form action="{{ route('employees.update', $employee->id) }}" method="POST">
        @csrf
        @method('PUT')
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
                <input type="text" name="name" value="{{ $employee->name }}" required style="width: 100%; padding: 1rem; border-radius: 12px; background: rgba(255,255,255,0.05); border: 1px solid var(--border-color); color: white;">
            </div>
            <div class="form-group">
                <label style="display: block; margin-bottom: 0.5rem; color: var(--text-muted);">البريد</label>
                <input type="email" name="email" value="{{ $employee->email }}" required style="width: 100%; padding: 1rem; border-radius: 12px; background: rgba(255,255,255,0.05); border: 1px solid var(--border-color); color: white;">
            </div>
            <div class="form-group">
                <label style="display: block; margin-bottom: 0.5rem; color: var(--text-muted);">كلمة مرور جديدة (اتركها فارغة لعدم التغيير)</label>
                <input type="password" name="password" style="width: 100%; padding: 1rem; border-radius: 12px; background: rgba(255,255,255,0.05); border: 1px solid var(--border-color); color: white;">
            </div>
            <div class="form-group">
                <label style="display: block; margin-bottom: 0.5rem; color: var(--text-muted);">تأكيد كلمة المرور</label>
                <input type="password" name="password_confirmation" style="width: 100%; padding: 1rem; border-radius: 12px; background: rgba(255,255,255,0.05); border: 1px solid var(--border-color); color: white;">
            </div>
            <div class="form-group">
                <label style="display: block; margin-bottom: 0.5rem; color: var(--text-muted);">الهاتف</label>
                <input type="text" name="phone" value="{{ $employee->phone }}" style="width: 100%; padding: 1rem; border-radius: 12px; background: rgba(255,255,255,0.05); border: 1px solid var(--border-color); color: white;">
            </div>
            <div class="form-group">
                <label style="display: block; margin-bottom: 0.5rem; color: var(--text-muted);">الدور</label>
                <select name="role" required style="width: 100%; padding: 1rem; border-radius: 12px; background: rgba(25,33,49,1); border: 1px solid var(--border-color); color: white;">
                    <option value="employee" {{ $employee->role == 'employee' ? 'selected' : '' }}>موظف</option>
                    <option value="manager" {{ $employee->role == 'manager' ? 'selected' : '' }}>مدير</option>
                    <option value="admin" {{ $employee->role == 'admin' ? 'selected' : '' }}>مسؤول</option>
                </select>
            </div>
            <div class="form-group">
                <label style="display: block; margin-bottom: 0.5rem; color: var(--text-muted);">سعر الساعة (ج.م)</label>
                <input type="number" name="hourly_rate" value="{{ $employee->hourly_rate ?? 0 }}" step="0.01" min="0" style="width: 100%; padding: 1rem; border-radius: 12px; background: rgba(255,255,255,0.05); border: 1px solid var(--border-color); color: white;">
                <p style="font-size: 0.8rem; color: var(--text-muted); margin-top: 0.4rem;">يُستخدم لحساب المرتب بناءً على ساعات الحضور</p>
            </div>
            <div class="form-group">
                <label style="display: flex; align-items: center; gap: 0.75rem; cursor: pointer;">
                    <input type="checkbox" name="is_active" value="1" {{ $employee->is_active ? 'checked' : '' }} style="width: 18px; height: 18px; cursor: pointer;">
                    <span style="color: var(--text-muted);">تفعيل الموظف (نشط)</span>
                </label>
                <p style="font-size: 0.85rem; color: var(--text-muted); margin-top: 0.5rem;">إذا تم إلغاء التفعيل، لن يظهر الموظف في سجلات الحضور والقائمة النشطة</p>
            </div>
        </div>
        <div style="display: flex; justify-content: flex-end; gap: 1rem;">
            <a href="{{ route('employees.index') }}" class="btn" style="background: rgba(255,255,255,0.05); color: white;">إلغاء</a>
            <button type="submit" class="btn btn-primary">تحديث</button>
        </div>
    </form>
</div>

{{-- Device Management Section --}}
<div class="table-container" style="max-width: 600px; margin: 2rem auto 0;">
    <h3 style="margin-bottom: 1.5rem; font-size: 1.2rem; display: flex; align-items: center; gap: 0.75rem;">
        <i class="fas fa-mobile-alt" style="color: var(--primary);"></i>
        إدارة ربط الجهاز (الحضور والانصراف)
    </h3>

    {{-- QR Code for Pairing (shown after admin clicks "pair device") --}}
    @if(session('pair_token'))
        <div style="background: rgba(0, 149, 255, 0.06); border: 1px solid rgba(0, 149, 255, 0.3); border-radius: 16px; padding: 2rem; margin-bottom: 1.5rem; text-align: center;">
            <div style="display: flex; align-items: center; justify-content: center; gap: 0.75rem; margin-bottom: 1rem;">
                <i class="fas fa-qrcode" style="color: #0095ff; font-size: 1.3rem;"></i>
                <span style="color: #0095ff; font-weight: 700; font-size: 1.1rem;">امسح الكود من موبايل الموظف</span>
            </div>
            <p style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 1.5rem;">اطلب من <strong style="color: #fff;">{{ $employee->name }}</strong> مسح الكود ده بموبايله عشان يتربط بحسابه</p>
            
            <div id="pair-qr-code" style="background: white; padding: 1.25rem; border-radius: 16px; display: inline-block; box-shadow: 0 0 25px rgba(0, 149, 255, 0.15);"></div>
            
            <div style="margin-top: 1.5rem; display: flex; flex-direction: column; align-items: center; gap: 0.5rem;">
                <div style="display: flex; align-items: center; gap: 0.5rem;">
                    <i class="fas fa-clock" style="color: #ffa500;"></i>
                    <span style="font-size: 0.85rem; color: #ffa500;">الكود صالح لمدة 5 دقائق فقط</span>
                </div>
                <p style="font-size: 0.75rem; color: var(--text-muted);">بعد المسح، الموبايل هيتربط تلقائياً بالحساب</p>
            </div>
        </div>
    @endif

    @if($employee->device_id)
        <div style="background: rgba(0, 230, 153, 0.06); border: 1px solid rgba(0, 230, 153, 0.2); border-radius: 12px; padding: 1.25rem; margin-bottom: 1.5rem;">
            <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.75rem;">
                <i class="fas fa-link" style="color: #00e699; font-size: 1.1rem;"></i>
                <span style="color: #00e699; font-weight: 600;">جهاز مربوط</span>
            </div>
            <p style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 0.25rem;">معرف الجهاز:</p>
            <code style="display: block; background: rgba(0,0,0,0.3); padding: 0.75rem; border-radius: 8px; font-size: 0.8rem; color: #a5d6ff; word-break: break-all; direction: ltr; text-align: left;">{{ $employee->device_id }}</code>
        </div>

        <div style="display: flex; gap: 1rem; flex-wrap: wrap;">
            <form action="{{ route('employees.unpair_device', $employee->id) }}" method="POST" onsubmit="return confirm('هل أنت متأكد من فك ربط الجهاز؟ سيحتاج الموظف لربط جهاز جديد لتسجيل الحضور.');">
                @csrf
                <button type="submit" class="btn" style="background: rgba(255, 77, 77, 0.1); color: #ff4d4d; border: 1px solid rgba(255, 77, 77, 0.3); padding: 0.7rem 1.5rem; border-radius: 10px; cursor: pointer;">
                    <i class="fas fa-unlink"></i>
                    فك ربط الجهاز
                </button>
            </form>
            <form action="{{ route('employees.pair_device', $employee->id) }}" method="POST" onsubmit="return confirm('سيتم إنشاء كود ربط جديد. الموظف لازم يسكان الكود من موبايله الجديد.');">
                @csrf
                <button type="submit" class="btn" style="background: rgba(0, 149, 255, 0.1); color: #0095ff; border: 1px solid rgba(0, 149, 255, 0.3); padding: 0.7rem 1.5rem; border-radius: 10px; cursor: pointer;">
                    <i class="fas fa-sync-alt"></i>
                    تجديد الربط (جهاز جديد)
                </button>
            </form>
        </div>
    @else
        @if(!session('pair_token'))
        <div style="background: rgba(255, 165, 0, 0.06); border: 1px solid rgba(255, 165, 0, 0.2); border-radius: 12px; padding: 1.25rem; margin-bottom: 1.5rem;">
            <div style="display: flex; align-items: center; gap: 0.75rem;">
                <i class="fas fa-exclamation-triangle" style="color: #ffa500; font-size: 1.1rem;"></i>
                <span style="color: #ffa500; font-weight: 600;">لا يوجد جهاز مربوط</span>
            </div>
            <p style="font-size: 0.85rem; color: var(--text-muted); margin-top: 0.5rem;">الموظف لن يتمكن من تسجيل الحضور حتى يتم ربط جهازه.</p>
        </div>
        @endif

        <form action="{{ route('employees.pair_device', $employee->id) }}" method="POST">
            @csrf
            <button type="submit" class="btn btn-primary" style="padding: 0.7rem 1.5rem;">
                <i class="fas fa-qrcode"></i>
                إنشاء كود ربط جهاز
            </button>
        </form>
    @endif

    <p style="font-size: 0.8rem; color: var(--text-muted); margin-top: 1.5rem; line-height: 1.6;">
        <i class="fas fa-info-circle" style="margin-left: 0.4rem;"></i>
        عند الضغط على "إنشاء كود ربط"، سيظهر QR code يجب على الموظف مسحه بموبايله. الموبايل هيسجل نفسه تلقائياً وهيربط بحساب الموظف. الكود صالح لمدة 5 دقائق فقط.
    </p>
</div>

@push('scripts')
@if(session('pair_token'))
<script src="/qrcode.min.js"></script>
<script>
    (function() {
        const pairUrl = window.location.origin + '/setup-phone?pair_token={{ session('pair_token') }}';
        const container = document.getElementById('pair-qr-code');
        if (container) {
            new QRCode(container, {
                text: pairUrl,
                width: 220,
                height: 220,
                colorDark: "#0f172a",
                colorLight: "#ffffff",
                correctLevel: QRCode.CorrectLevel.H
            });
        }
    })();
</script>
@endif
@endpush
@endsection
