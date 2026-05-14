@extends('layouts.app')

@section('content')
<div class="stats-grid">
    @if(auth()->user()->hasPermission('module_doctors'))
    <a href="{{ route('doctors.index') }}" style="text-decoration: none; color: inherit;">
        <div class="stat-card">
            <div class="stat-icon-wrapper icon-blue"><i class="fas fa-user-md"></i></div>
            <div class="stat-details">
                <span class="stat-value">{{ $stats['doctors'] }}</span>
                <span class="stat-label">الأطباء</span>
            </div>
        </div>
    </a>
    @endif
    @if(auth()->user()->hasPermission('module_patients'))
    <a href="{{ route('patients.index') }}" style="text-decoration: none; color: inherit;">
        <div class="stat-card">
            <div class="stat-icon-wrapper icon-green"><i class="fas fa-hospital-user"></i></div>
            <div class="stat-details">
                <span class="stat-value">{{ $stats['patients'] }}</span>
                <span class="stat-label">المرضى</span>
            </div>
        </div>
    </a>
    @endif
    @if(auth()->user()->hasPermission('module_test_types'))
    <a href="{{ route('test-types.index') }}" style="text-decoration: none; color: inherit;">
        <div class="stat-card">
            <div class="stat-icon-wrapper icon-orange"><i class="fas fa-stethoscope"></i></div>
            <div class="stat-details">
                <span class="stat-value">{{ $stats['test_types'] }}</span>
                <span class="stat-label">أنواع الفحوصات</span>
            </div>
        </div>
    </a>
    @endif
    @if(auth()->user()->hasPermission('module_finance'))
    <a href="{{ route('finance.index') }}" style="text-decoration: none; color: inherit;">
        <div class="stat-card">
            <div class="stat-icon-wrapper icon-purple"><i class="fas fa-money-bill-trend-up"></i></div>
            <div class="stat-details">
                <span class="stat-value">{{ number_format($stats['income'], 0) }}</span>
                <span class="stat-label">إجمالي الدخل (ج.م)</span>
            </div>
        </div>
    </a>
    @endif
</div>

<div style="display: flex; gap: 1rem; margin-bottom: 2rem; flex-wrap: wrap;">
    @if(auth()->user()->hasPermission('module_patients'))
    <a href="{{ route('patients.create') }}" class="btn btn-primary"><i class="fas fa-user-plus"></i> تسجيل مريض</a>
    @endif
    @if(auth()->user()->hasPermission('module_doctors'))
    <a href="{{ route('doctors.create') }}" class="btn" style="background: rgba(255,255,255,0.05); color: white;"><i class="fas fa-plus"></i> إضافة طبيب</a>
    @endif
    @if(auth()->user()->hasPermission('module_finance'))
    <a href="{{ route('finance.reports') }}" class="btn" style="background: rgba(255,255,255,0.05); color: white;"><i class="fas fa-chart-pie"></i> التقارير المالية</a>
    @endif
</div>

<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 2rem;">
    @if(auth()->user()->hasPermission('module_patients'))
    <div class="table-container">
        <div class="table-header">
            <h2>آخر المرضى المسجلين</h2>
            <a href="{{ route('patients.index') }}" class="btn" style="background: rgba(255,255,255,0.05); color: white; font-size: 0.8rem;">عرض الكل</a>
        </div>
        <div class="table-scroll-wrap">
            <table style="min-width: 100%;">
                <thead><tr><th>الاسم</th><th>الهاتف</th><th>التاريخ</th></tr></thead>
                <tbody>
                    @forelse($recentPatients as $p)
                    <tr>
                        <td style="font-weight: 600; color: #fff;">{{ $p->name }}</td>
                        <td>{{ $p->phone }}</td>
                        <td style="color: var(--text-muted);">{{ \Carbon\Carbon::parse($p->created_at)->format('Y-m-d') }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="3" style="text-align: center; padding: 2rem; color: var(--text-muted);">لا يوجد مرضى بعد</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @endif

    @if(auth()->user()->hasPermission('module_finance'))
    <div class="table-container">
        <div class="table-header">
            <h2>آخر المعاملات المالية</h2>
            <a href="{{ route('finance.index') }}" class="btn" style="background: rgba(255,255,255,0.05); color: white; font-size: 0.8rem;">عرض الكل</a>
        </div>
        <div class="table-scroll-wrap">
            <table style="min-width: 100%;">
                <thead><tr><th>التاريخ</th><th>الوصف</th><th>المبلغ</th></tr></thead>
                <tbody>
                    @forelse($recentTransactions as $tx)
                    <tr>
                        <td style="color: var(--text-muted);">{{ $tx->date }}</td>
                        <td style="font-weight: 500;">{{ \Illuminate\Support\Str::limit($tx->description, 25) }}</td>
                        <td style="font-weight: 700; color: {{ $tx->type == 'income' ? '#00e699' : '#ff8529' }}">
                            {{ $tx->type == 'income' ? '+' : '-' }}{{ number_format($tx->amount, 0) }} ج.م
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="3" style="text-align: center; padding: 2rem; color: var(--text-muted);">لا توجد معاملات</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @endif
</div>
@endsection
