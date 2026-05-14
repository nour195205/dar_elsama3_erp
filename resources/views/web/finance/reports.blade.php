@extends('layouts.app')

@section('content')
<!-- Quick Nav -->
<div style="display: flex; gap: 1rem; margin-bottom: 2rem;">
    <a href="{{ route('finance.index') }}" class="btn" style="background: rgba(255,255,255,0.05); color: white;">
        <i class="fas fa-receipt"></i> المعاملات
    </a>
    <a href="{{ route('finance.doctor_payouts') }}" class="btn" style="background: rgba(255,255,255,0.05); color: white;">
        <i class="fas fa-hand-holding-dollar"></i> مستحقات الأطباء
    </a>
    <a href="{{ route('finance.reports') }}" class="btn btn-primary" style="background: var(--primary); color: #111;">
        <i class="fas fa-chart-pie"></i> التقارير
    </a>
</div>

<!-- Report Period Filter -->
<div class="table-container" style="margin-bottom: 2rem; background: rgba(0, 149, 255, 0.03);">
    <form method="GET" action="{{ route('finance.reports') }}" style="display: flex; gap: 1rem; align-items: flex-end;">
        <div class="form-group">
            <label style="font-size: 0.8rem; color: var(--text-muted);">من تاريخ</label>
            <input type="date" name="from" value="{{ $from }}" style="padding: 0.75rem; border-radius: 10px; background: #1e293b; border: 1px solid var(--border-color); color: white;">
        </div>
        <div class="form-group">
            <label style="font-size: 0.8rem; color: var(--text-muted);">إلى تاريخ</label>
            <input type="date" name="to" value="{{ $to }}" style="padding: 0.75rem; border-radius: 10px; background: #1e293b; border: 1px solid var(--border-color); color: white;">
        </div>
        <button type="submit" class="btn btn-primary" style="height: 45px;">
            <i class="fas fa-filter"></i> تطبيق الفلتر
        </button>
    </form>
</div>

<!-- Summary Cards -->
<div class="stats-grid" style="margin-bottom: 2rem;">
    <div class="stat-card">
        <div class="stat-icon-wrapper icon-green">
            <i class="fas fa-arrow-trend-up"></i>
        </div>
        <div class="stat-details">
            <span class="stat-value">{{ number_format($report['total_income'], 2) }} ج.م</span>
            <span class="stat-label">إجمالي الإيرادات</span>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon-wrapper icon-blue">
            <i class="fas fa-hand-holding-dollar"></i>
        </div>
        <div class="stat-details">
            <span class="stat-value">{{ number_format($report['total_payouts'], 2) }} ج.م</span>
            <span class="stat-label">مستحقات الأطباء</span>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon-wrapper icon-orange">
            <i class="fas fa-arrow-trend-down"></i>
        </div>
        <div class="stat-details">
            <span class="stat-value">{{ number_format($report['total_expenses'], 2) }} ج.م</span>
            <span class="stat-label">إجمالي المصروفات</span>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon-wrapper icon-purple">
            <i class="fas fa-scale-balanced"></i>
        </div>
        <div class="stat-details">
            <span class="stat-value" style="color: {{ $report['net_profit'] >= 0 ? '#00e699' : '#ff4d4d' }}">{{ number_format($report['net_profit'], 2) }} ج.م</span>
            <span class="stat-label">صافي الربح</span>
        </div>
    </div>
</div>

<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem;">
    <!-- Income Breakdown -->
    <div class="table-container">
        <h2 style="margin-bottom: 1.5rem; font-size: 1.1rem;">
            <i class="fas fa-chart-bar" style="color: #00e699; margin-left: 0.5rem;"></i>
            تفصيل الإيرادات
        </h2>
        @forelse($report['income_breakdown'] as $category => $amount)
            <div style="display: flex; justify-content: space-between; padding: 0.75rem 0; border-bottom: 1px solid rgba(255,255,255,0.05);">
                <span style="color: var(--text-muted);">{{ $category }}</span>
                <span style="font-weight: 700; color: #00e699;">{{ number_format($amount, 2) }} ج.م</span>
            </div>
        @empty
            <p style="color: var(--text-muted); text-align: center; padding: 2rem;">لا توجد إيرادات في هذه الفترة</p>
        @endforelse
    </div>

    <!-- Expense Breakdown -->
    <div class="table-container">
        <h2 style="margin-bottom: 1.5rem; font-size: 1.1rem;">
            <i class="fas fa-chart-bar" style="color: #ff8529; margin-left: 0.5rem;"></i>
            تفصيل المصروفات
        </h2>
        @forelse($report['expense_breakdown'] as $category => $amount)
            <div style="display: flex; justify-content: space-between; padding: 0.75rem 0; border-bottom: 1px solid rgba(255,255,255,0.05);">
                <span style="color: var(--text-muted);">{{ $category }}</span>
                <span style="font-weight: 700; color: #ff8529;">{{ number_format($amount, 2) }} ج.م</span>
            </div>
        @empty
            <p style="color: var(--text-muted); text-align: center; padding: 2rem;">لا توجد مصروفات في هذه الفترة</p>
        @endforelse
    </div>
</div>

<!-- Doctor Payouts Summary -->
@if(count($report['doctor_payouts']) > 0)
<div class="table-container" style="margin-top: 2rem;">
    <h2 style="margin-bottom: 1.5rem; font-size: 1.1rem;">
        <i class="fas fa-user-md" style="color: #0095ff; margin-left: 0.5rem;"></i>
        ملخص مستحقات الأطباء
    </h2>
    <table>
        <thead>
            <tr>
                <th>الطبيب</th>
                <th>النوع</th>
                <th>الإجمالي</th>
                <th>مدفوع</th>
                <th>غير مدفوع</th>
            </tr>
        </thead>
        <tbody>
            @foreach($report['doctor_payouts'] as $dp)
            <tr>
                <td style="font-weight: 600; color: #fff;">{{ $dp['doctor_name'] }}</td>
                <td>
                    <span class="badge" style="background: rgba(0, 149, 255, 0.1); color: #0095ff;">{{ $dp['doctor_type'] == 'internal' ? 'داخلي' : 'محول' }}</span>
                </td>
                <td style="font-weight: 700;">{{ number_format($dp['total'], 2) }} ج.م</td>
                <td style="color: #00e699;">{{ number_format($dp['paid'], 2) }} ج.م</td>
                <td style="color: #ff4d4d;">{{ number_format($dp['unpaid'], 2) }} ج.م</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endif
@endsection
