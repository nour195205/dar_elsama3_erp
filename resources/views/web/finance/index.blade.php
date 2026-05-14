@extends('layouts.app')

@section('content')
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon-wrapper icon-green">
            <i class="fas fa-arrow-trend-up"></i>
        </div>
        <div class="stat-details">
            <span class="stat-value">{{ number_format($summary['total_income'], 0) }} ج.م</span>
            <span class="stat-label">إجمالي الإيرادات</span>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon-wrapper icon-orange">
            <i class="fas fa-arrow-trend-down"></i>
        </div>
        <div class="stat-details">
            <span class="stat-value">{{ number_format($summary['total_expenses'], 0) }} ج.م</span>
            <span class="stat-label">إجمالي المصروفات</span>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon-wrapper icon-blue">
            <i class="fas fa-hand-holding-dollar"></i>
        </div>
        <div class="stat-details">
            <span class="stat-value">{{ number_format($summary['total_payouts'], 0) }} ج.م</span>
            <span class="stat-label">عمولات الأطباء</span>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon-wrapper icon-purple">
            <i class="fas fa-scale-balanced"></i>
        </div>
        <div class="stat-details">
            <span class="stat-value">{{ number_format($summary['total_income'] - $summary['total_expenses'] - $summary['total_payouts'], 0) }} ج.م</span>
            <span class="stat-label">صافي الربح</span>
        </div>
    </div>
</div>

<!-- Quick Nav -->
<div style="display: flex; gap: 1rem; margin-bottom: 2rem;">
    <a href="{{ route('finance.index') }}" class="btn btn-primary" style="background: var(--primary); color: #111;">
        <i class="fas fa-receipt"></i> المعاملات
    </a>
    <a href="{{ route('finance.doctor_payouts') }}" class="btn" style="background: rgba(255,255,255,0.05); color: white;">
        <i class="fas fa-hand-holding-dollar"></i> مستحقات الأطباء
    </a>
    <a href="{{ route('finance.reports') }}" class="btn" style="background: rgba(255,255,255,0.05); color: white;">
        <i class="fas fa-chart-pie"></i> التقارير
    </a>
</div>

<div class="table-container" style="margin-bottom: 2.5rem; background: rgba(0, 230, 153, 0.03);">
    <h2 style="margin-bottom: 1.5rem; font-size: 1.2rem;">إضافة معاملة جديدة</h2>
    <form action="{{ route('finance.store') }}" method="POST" style="display: grid; grid-template-columns: 2fr 1fr 1fr 1fr auto; gap: 1rem; align-items: flex-end;">
        @csrf
        <div class="form-group">
            <label style="font-size: 0.8rem; color: var(--text-muted);">البيان</label>
            <input type="text" name="description" required placeholder="مثلاً: كشف سمع، مصاريف كهرباء..." style="width: 100%; padding: 0.75rem; border-radius: 10px; background: #1e293b; border: 1px solid var(--border-color); color: white;">
        </div>
        <div class="form-group">
            <label style="font-size: 0.8rem; color: var(--text-muted);">المبلغ</label>
            <input type="number" name="amount" required step="0.01" style="width: 100%; padding: 0.75rem; border-radius: 10px; background: #1e293b; border: 1px solid var(--border-color); color: white;">
        </div>
        <div class="form-group">
            <label style="font-size: 0.8rem; color: var(--text-muted);">النوع</label>
            <select name="type" style="width: 100%; padding: 0.75rem; border-radius: 10px; background: #1e293b; border: 1px solid var(--border-color); color: white;">
                <option value="income">إيراد</option>
                <option value="expense">مصروف</option>
            </select>
        </div>
        <div class="form-group">
            <label style="font-size: 0.8rem; color: var(--text-muted);">التاريخ</label>
            <input type="date" name="date" value="{{ now()->toDateString() }}" style="width: 100%; padding: 0.75rem; border-radius: 10px; background: #1e293b; border: 1px solid var(--border-color); color: white;">
        </div>
        <button type="submit" class="btn btn-primary" style="height: 45px; justify-content: center;">
            <i class="fas fa-plus"></i> إضافة
        </button>
    </form>
</div>

<div class="table-container">
    <div class="table-header">
        <h2>سجل المعاملات المالية</h2>
    </div>
    <table>
        <thead>
            <tr>
                <th>التاريخ</th>
                <th>البيان / الوصف</th>
                <th>الفئة</th>
                <th>المبلغ</th>
                <th>النوع</th>
            </tr>
        </thead>
        <tbody>
            @forelse($transactions as $tx)
            <tr>
                <td style="color: var(--text-muted);">{{ $tx->date }}</td>
                <td style="font-weight: 500;">{{ $tx->description }}</td>
                <td style="color: var(--text-muted); font-size: 0.85rem;">{{ $tx->category ?? '---' }}</td>
                <td style="font-weight: 700; color: {{ $tx->type == 'income' ? '#00e699' : ($tx->type == 'payout' ? '#0095ff' : '#ff8529') }}">
                    {{ $tx->type == 'income' ? '+' : '-' }}{{ number_format($tx->amount, 2) }} ج.م
                </td>
                <td>
                    @if($tx->type == 'income')
                        <span class="badge" style="background: rgba(0, 230, 153, 0.1); color: #00e699;">إيراد</span>
                    @elseif($tx->type == 'payout')
                        <span class="badge" style="background: rgba(0, 149, 255, 0.1); color: #0095ff;">عمولة</span>
                    @else
                        <span class="badge" style="background: rgba(255, 133, 41, 0.1); color: #ff8529;">مصروف</span>
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5" style="text-align: center; padding: 4rem; color: var(--text-muted);">
                    <i class="fas fa-receipt" style="font-size: 3rem; margin-bottom: 1rem; display: block; opacity: 0.2;"></i>
                    لا توجد معاملات مسجلة في الوقت الحالي
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
