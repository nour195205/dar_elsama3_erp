@extends('layouts.app')

@section('content')
<!-- Settings Tabs -->
<div style="display: flex; gap: 0.5rem; background: rgba(255,255,255,0.03); padding: 0.3rem; border-radius: 16px; margin-bottom: 2.5rem; width: fit-content;">
    <button class="settings-tab active" onclick="switchTab('general', this)" style="padding: 0.75rem 1.5rem; border-radius: 12px; border: none; cursor: pointer; font-weight: 600; font-size: 0.95rem; transition: all 0.3s;">
        <i class="fas fa-cog"></i> عام
    </button>
    <button class="settings-tab" onclick="switchTab('branding', this)" style="padding: 0.75rem 1.5rem; border-radius: 12px; border: none; cursor: pointer; font-weight: 600; font-size: 0.95rem; transition: all 0.3s;">
        <i class="fas fa-palette"></i> الهوية
    </button>
    <button class="settings-tab" onclick="switchTab('system', this)" style="padding: 0.75rem 1.5rem; border-radius: 12px; border: none; cursor: pointer; font-weight: 600; font-size: 0.95rem; transition: all 0.3s;">
        <i class="fas fa-server"></i> النظام
    </button>
    <button class="settings-tab" onclick="switchTab('security', this)" style="padding: 0.75rem 1.5rem; border-radius: 12px; border: none; cursor: pointer; font-weight: 600; font-size: 0.95rem; transition: all 0.3s;">
        <i class="fas fa-shield-halved"></i> الأمان
    </button>
</div>

<!-- General Tab -->
<div id="tab-general" class="tab-content" style="display: block;">
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem;">
        <div class="table-container">
            <h2 style="margin-bottom: 1.5rem;"><i class="fas fa-hospital" style="color: var(--primary); margin-left: 0.75rem;"></i> بيانات العيادة</h2>
            <form action="{{ route('settings.update_clinic') }}" method="POST">
                @csrf
                <div style="display: grid; gap: 1.25rem;">
                    <div>
                        <label style="display: block; margin-bottom: 0.5rem; color: var(--text-muted); font-size: 0.85rem;">اسم العيادة</label>
                        <input type="text" name="clinic_name" value="{{ $settings['clinic_name'] ?? 'دار السمع' }}" style="width: 100%; padding: 0.9rem; border-radius: 12px; background: rgba(255,255,255,0.05); border: 1px solid var(--border-color); color: white;">
                    </div>
                    <div>
                        <label style="display: block; margin-bottom: 0.5rem; color: var(--text-muted); font-size: 0.85rem;">رقم الهاتف</label>
                        <input type="text" name="clinic_phone" value="{{ $settings['clinic_phone'] ?? '' }}" style="width: 100%; padding: 0.9rem; border-radius: 12px; background: rgba(255,255,255,0.05); border: 1px solid var(--border-color); color: white;">
                    </div>
                    <div>
                        <label style="display: block; margin-bottom: 0.5rem; color: var(--text-muted); font-size: 0.85rem;">العنوان</label>
                        <input type="text" name="clinic_address" value="{{ $settings['clinic_address'] ?? '' }}" style="width: 100%; padding: 0.9rem; border-radius: 12px; background: rgba(255,255,255,0.05); border: 1px solid var(--border-color); color: white;">
                    </div>
                    <button type="submit" class="btn btn-primary" style="width: fit-content;"><i class="fas fa-save"></i> حفظ التغييرات</button>
                </div>
            </form>
        </div>

        <div class="table-container">
            <h2 style="margin-bottom: 1.5rem;"><i class="fas fa-network-wired" style="color: #0095ff; margin-left: 0.75rem;"></i> إعدادات الشبكة</h2>
            <div style="display: grid; gap: 1.25rem;">
                <div>
                    <label style="display: block; margin-bottom: 0.5rem; color: var(--text-muted); font-size: 0.85rem;">عنوان الـ IP المحلي</label>
                    <input type="text" value="{{ request()->server('SERVER_ADDR', '127.0.0.1') }}" readonly style="width: 100%; padding: 0.9rem; border-radius: 12px; background: rgba(255,255,255,0.03); border: 1px solid var(--border-color); color: var(--primary); font-family: monospace; font-weight: 700;">
                </div>
                <div>
                    <label style="display: block; margin-bottom: 0.5rem; color: var(--text-muted); font-size: 0.85rem;">المنفذ (Port)</label>
                    <input type="text" value="{{ request()->server('SERVER_PORT', '8000') }}" readonly style="width: 100%; padding: 0.9rem; border-radius: 12px; background: rgba(255,255,255,0.03); border: 1px solid var(--border-color); color: var(--text-muted); font-family: monospace;">
                </div>
                <p style="font-size: 0.8rem; color: var(--text-muted); line-height: 1.6;">
                    <i class="fas fa-info-circle" style="color: #0095ff;"></i>
                    تأكد من اتصال جميع الأجهزة بنفس الشبكة المحلية لضمان عمل نظام الحضور.
                </p>
            </div>
        </div>
    </div>
</div>

<!-- Branding Tab -->
<div id="tab-branding" class="tab-content" style="display: none;">
    <div class="table-container" style="max-width: 700px;">
        <h2 style="margin-bottom: 1.5rem;"><i class="fas fa-palette" style="color: #a155ff; margin-left: 0.75rem;"></i> الهوية البصرية</h2>
        <div style="display: grid; gap: 1.5rem;">
            <div style="display: flex; align-items: center; gap: 2rem; padding: 1.5rem; background: rgba(255,255,255,0.03); border-radius: 16px;">
                <div style="width: 80px; height: 80px; border-radius: 20px; background: linear-gradient(135deg, var(--primary), #00b377); display: flex; align-items: center; justify-content: center; font-size: 2.5rem;">
                    <i class="fas fa-ear-listen" style="color: #000;"></i>
                </div>
                <div>
                    <h3 style="margin-bottom: 0.25rem;">شعار العيادة</h3>
                    <p style="color: var(--text-muted); font-size: 0.85rem;">يظهر في الشريط الجانبي والتقارير</p>
                </div>
            </div>
            <div>
                <label style="display: block; margin-bottom: 0.5rem; color: var(--text-muted); font-size: 0.85rem;">اللون الرئيسي</label>
                <div style="display: flex; gap: 0.75rem;">
                    <div onclick="this.style.outline='2px solid white'" style="width: 45px; height: 45px; border-radius: 12px; background: #00e699; cursor: pointer; border: 2px solid transparent;"></div>
                    <div onclick="this.style.outline='2px solid white'" style="width: 45px; height: 45px; border-radius: 12px; background: #0095ff; cursor: pointer; border: 2px solid transparent;"></div>
                    <div onclick="this.style.outline='2px solid white'" style="width: 45px; height: 45px; border-radius: 12px; background: #a155ff; cursor: pointer; border: 2px solid transparent;"></div>
                    <div onclick="this.style.outline='2px solid white'" style="width: 45px; height: 45px; border-radius: 12px; background: #ff8529; cursor: pointer; border: 2px solid transparent;"></div>
                    <div onclick="this.style.outline='2px solid white'" style="width: 45px; height: 45px; border-radius: 12px; background: #ff4d6a; cursor: pointer; border: 2px solid transparent;"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- System Tab -->
<div id="tab-system" class="tab-content" style="display: none;">
    <div class="table-container" style="max-width: 700px;">
        <h2 style="margin-bottom: 1.5rem;"><i class="fas fa-server" style="color: #ff8529; margin-left: 0.75rem;"></i> معلومات النظام</h2>
        <div style="display: flex; flex-direction: column; gap: 0;">
            @php
                $sysInfo = [
                    ['label' => 'الإصدار', 'value' => 'v2.2 (Web Edition)', 'icon' => 'fa-code-branch', 'color' => '#00e699'],
                    ['label' => 'إطار العمل', 'value' => 'Laravel ' . app()->version(), 'icon' => 'fa-laravel', 'color' => '#ff4d4d'],
                    ['label' => 'PHP', 'value' => phpversion(), 'icon' => 'fa-php', 'color' => '#8B93FF'],
                    ['label' => 'قاعدة البيانات', 'value' => 'SQLite 3', 'icon' => 'fa-database', 'color' => '#0095ff'],
                    ['label' => 'نظام التشغيل', 'value' => PHP_OS, 'icon' => 'fa-desktop', 'color' => '#ff8529'],
                    ['label' => 'حالة السيرفر', 'value' => 'يعمل بكفاءة', 'icon' => 'fa-circle-check', 'color' => '#00e699'],
                ];
            @endphp
            @foreach($sysInfo as $info)
            <div style="display: flex; justify-content: space-between; align-items: center; padding: 1rem 0; {{ !$loop->last ? 'border-bottom: 1px solid var(--border-color);' : '' }}">
                <span style="color: var(--text-muted); display: flex; align-items: center; gap: 0.75rem;">
                    <i class="fas {{ $info['icon'] }}" style="color: {{ $info['color'] }}; width: 20px; text-align: center;"></i>
                    {{ $info['label'] }}
                </span>
                <span style="font-weight: 600; font-family: monospace;">{{ $info['value'] }}</span>
            </div>
            @endforeach
        </div>

        <div style="margin-top: 2rem; padding-top: 1.5rem; border-top: 1px solid var(--border-color);">
            <h3 style="margin-bottom: 1rem; font-size: 1rem;">إحصائيات قاعدة البيانات</h3>
            <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem;">
                @php
                    $dbStats = [
                        ['label' => 'الأطباء', 'count' => DB::table('doctors')->count(), 'color' => '#00e699'],
                        ['label' => 'المرضى', 'count' => DB::table('patients')->count(), 'color' => '#0095ff'],
                        ['label' => 'الموظفين', 'count' => DB::table('users')->count(), 'color' => '#a155ff'],
                    ];
                @endphp
                @foreach($dbStats as $stat)
                <div style="background: rgba(255,255,255,0.03); padding: 1rem; border-radius: 12px; text-align: center;">
                    <div style="font-size: 1.8rem; font-weight: 700; color: {{ $stat['color'] }};">{{ $stat['count'] }}</div>
                    <div style="color: var(--text-muted); font-size: 0.8rem;">{{ $stat['label'] }}</div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

<!-- Security Tab -->
<div id="tab-security" class="tab-content" style="display: none;">
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem;">
        <div class="table-container">
            <h2 style="margin-bottom: 1.5rem;"><i class="fas fa-key" style="color: #ff8529; margin-left: 0.75rem;"></i> تغيير كلمة المرور</h2>
            <form action="{{ route('settings.update_password') }}" method="POST">
                @csrf
                <div style="display: grid; gap: 1.25rem;">
                    <div>
                        <label style="display: block; margin-bottom: 0.5rem; color: var(--text-muted); font-size: 0.85rem;">كلمة المرور الحالية</label>
                        <input type="password" name="current_password" required style="width: 100%; padding: 0.9rem; border-radius: 12px; background: rgba(255,255,255,0.05); border: 1px solid var(--border-color); color: white;">
                    </div>
                    <div>
                        <label style="display: block; margin-bottom: 0.5rem; color: var(--text-muted); font-size: 0.85rem;">كلمة المرور الجديدة</label>
                        <input type="password" name="new_password" required style="width: 100%; padding: 0.9rem; border-radius: 12px; background: rgba(255,255,255,0.05); border: 1px solid var(--border-color); color: white;">
                    </div>
                    <div>
                        <label style="display: block; margin-bottom: 0.5rem; color: var(--text-muted); font-size: 0.85rem;">تأكيد كلمة المرور</label>
                        <input type="password" name="new_password_confirmation" required style="width: 100%; padding: 0.9rem; border-radius: 12px; background: rgba(255,255,255,0.05); border: 1px solid var(--border-color); color: white;">
                    </div>
                    <button type="submit" class="btn btn-primary" style="width: fit-content;"><i class="fas fa-lock"></i> تحديث كلمة المرور</button>
                </div>
            </form>
        </div>

        <div class="table-container">
            <h2 style="margin-bottom: 1.5rem;"><i class="fas fa-users-gear" style="color: #a155ff; margin-left: 0.75rem;"></i> المستخدمون النشطون</h2>
            <div style="display: flex; flex-direction: column; gap: 0;">
                @foreach(DB::table('users')->get() as $user)
                <div style="display: flex; justify-content: space-between; align-items: center; padding: 0.9rem 0; {{ !$loop->last ? 'border-bottom: 1px solid var(--border-color);' : '' }}">
                    <div style="display: flex; align-items: center; gap: 0.75rem;">
                        <div style="width: 36px; height: 36px; border-radius: 50%; background: {{ $user->role == 'admin' ? 'rgba(255,133,41,0.15)' : 'rgba(0,149,255,0.15)' }}; display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-user" style="color: {{ $user->role == 'admin' ? '#ff8529' : '#0095ff' }}; font-size: 0.8rem;"></i>
                        </div>
                        <div>
                            <div style="font-weight: 600; font-size: 0.9rem;">{{ $user->name }}</div>
                            <div style="color: var(--text-muted); font-size: 0.75rem;">{{ $user->email }}</div>
                        </div>
                    </div>
                    <span class="badge" style="background: {{ $user->role == 'admin' ? 'rgba(255,133,41,0.1)' : 'rgba(0,149,255,0.1)' }}; color: {{ $user->role == 'admin' ? '#ff8529' : '#0095ff' }};">
                        {{ $user->role == 'admin' ? 'مسؤول' : 'موظف' }}
                    </span>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <div class="table-container" style="margin-top: 2rem;">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <div>
                <h2 style="margin-bottom: 0.25rem;"><i class="fas fa-right-from-bracket" style="color: #ff4d4d; margin-left: 0.75rem;"></i> تسجيل الخروج</h2>
                <p style="color: var(--text-muted); font-size: 0.85rem;">إنهاء جلسة العمل الحالية والرجوع لشاشة الدخول</p>
            </div>
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="btn" style="background: rgba(255,77,77,0.1); color: #ff4d4d; border: 1px solid rgba(255,77,77,0.2);">
                    <i class="fas fa-sign-out-alt"></i> تسجيل الخروج
                </button>
            </form>
        </div>
    </div>
</div>

<style>
    .settings-tab { background: transparent; color: var(--text-muted); }
    .settings-tab.active { background: var(--primary) !important; color: #000 !important; }
    .settings-tab:hover:not(.active) { background: rgba(255,255,255,0.05); color: #fff; }
</style>

<script>
function switchTab(tabId, btn) {
    document.querySelectorAll('.tab-content').forEach(t => t.style.display = 'none');
    document.querySelectorAll('.settings-tab').forEach(b => b.classList.remove('active'));
    document.getElementById('tab-' + tabId).style.display = 'block';
    btn.classList.add('active');
}
</script>
@endsection
