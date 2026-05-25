@extends('layouts.app')

@section('content')
@php
    $doctorNames = collect($report['doctor_payouts'])->pluck('doctor_name')->toArray();
    $doctorTotals = collect($report['doctor_payouts'])->pluck('total')->toArray();
    $doctorPaid = collect($report['doctor_payouts'])->pluck('paid')->toArray();
    $doctorUnpaid = collect($report['doctor_payouts'])->pluck('unpaid')->toArray();

    $dailyDates = collect($report['daily_cashflow'])->pluck('date')->toArray();
    $dailyIncome = collect($report['daily_cashflow'])->pluck('income')->map(fn($v) => (float)$v)->toArray();
    $dailyExpense = collect($report['daily_cashflow'])->pluck('expense')->map(fn($v) => (float)$v)->toArray();

    $incomeCategories = array_keys($report['income_breakdown']->toArray());
    $incomeAmounts = array_values($report['income_breakdown']->toArray());

    // تعريب أسماء الفئات للإيرادات
    $incomeCategoriesArabic = array_map(function($cat) {
        return match($cat) {
            'test_revenue' => 'إيرادات الفحوصات',
            'manual_income' => 'إيرادات يدوية',
            default => $cat
        };
    }, $incomeCategories);

    $expenseCategories = array_keys($report['expense_breakdown']->toArray());
    $expenseAmounts = array_values($report['expense_breakdown']->toArray());

    // تعريب أسماء الفئات للمصروفات
    $expenseCategoriesArabic = array_map(function($cat) {
        return match($cat) {
            'medical_supplies' => 'مستلزمات طبية',
            'manual_expense' => 'مصروفات يدوية',
            default => $cat
        };
    }, $expenseCategories);

    // تقسيم عمولات الأطباء حسب النوع
    $allDoctorPayouts = collect($report['doctor_payouts']);
    $internalPayouts = $allDoctorPayouts->filter(fn($dp) => strtolower($dp['doctor_type']) === 'internal')->values();
    $externalPayouts = $allDoctorPayouts->filter(fn($dp) => strtolower($dp['doctor_type']) === 'external')->values();
@endphp

<!-- أسلوب طباعة مخصص للـ PDF ليكون أنيقاً ومتوافقاً مع ورق A4 -->
<style>
    @media print {
        body {
            background: #ffffff !important;
            color: #000000 !important;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif !important;
        }
        .sidebar, .navbar, .quick-nav, .filter-section, .action-buttons-wrapper, .btn {
            display: none !important;
        }
        .main-content, .content-wrapper, .container {
            margin: 0 !important;
            padding: 0 !important;
            width: 100% !important;
            max-width: 100% !important;
        }
        .table-container {
            background: none !important;
            border: 1px solid #cbd5e1 !important;
            box-shadow: none !important;
            margin-bottom: 2rem !important;
            padding: 1.5rem !important;
            page-break-inside: avoid;
        }
        table {
            color: #000000 !important;
            border-collapse: collapse !important;
            width: 100% !important;
        }
        th {
            background-color: #f1f5f9 !important;
            color: #0f172a !important;
            border-bottom: 2px solid #cbd5e1 !important;
            padding: 8px !important;
        }
        td {
            border-bottom: 1px solid #e2e8f0 !important;
            color: #334155 !important;
            padding: 8px !important;
        }
        .stat-card {
            background: #ffffff !important;
            border: 1px solid #cbd5e1 !important;
            box-shadow: none !important;
            color: #000000 !important;
        }
        .stat-value {
            color: #0f172a !important;
        }
        .print-header {
            display: block !important;
            margin-bottom: 2rem;
            border-bottom: 3px double #cbd5e1;
            padding-bottom: 1rem;
        }
        .charts-row {
            display: none !important;
        }
    }
    .print-header {
        display: none;
    }
    .filter-btn {
        background: rgba(255, 255, 255, 0.05);
        color: var(--text-muted);
        border: 1px solid rgba(255, 255, 255, 0.1);
        padding: 0.5rem 1rem;
        border-radius: 8px;
        cursor: pointer;
        transition: all 0.2s ease;
        font-size: 0.85rem;
    }
    .filter-btn:hover, .filter-btn.active {
        background: var(--primary);
        color: #111;
        border-color: var(--primary);
    }
</style>

<!-- عنوان التقرير عند الطباعة لـ PDF -->
<div class="print-header" style="direction: rtl; text-align: center;">
    <h1 style="font-size: 1.8rem; margin-bottom: 0.5rem; color: #0f172a;">مركز دار السماء للسمعيات والاتزان</h1>
    <h2 style="font-size: 1.3rem; margin-bottom: 0.5rem; color: #475569;">تقرير الحسابات المالية المفصل</h2>
    <p style="font-size: 0.95rem; color: #64748b;">الفترة الزمنية للتقرير: من <strong>{{ $from }}</strong> إلى <strong>{{ $to }}</strong></p>
    <p style="font-size: 0.8rem; color: #94a3b8; text-align: left; margin-top: 1rem;">تاريخ الطباعة: {{ now()->toDateTimeString() }}</p>
</div>

<!-- Quick Nav -->
<div class="quick-nav" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
    <div style="display: flex; gap: 1rem;">
        <a href="{{ route('finance.index') }}" class="btn" style="background: rgba(255,255,255,0.05); color: white;">
            <i class="fas fa-receipt"></i> المعاملات
        </a>
        <a href="{{ route('finance.doctor_payouts') }}" class="btn" style="background: rgba(255,255,255,0.05); color: white;">
            <i class="fas fa-hand-holding-dollar"></i> مستحقات الأطباء
        </a>
        <a href="{{ route('finance.reports') }}" class="btn btn-primary" style="background: var(--primary); color: #111;">
            <i class="fas fa-chart-pie"></i> التقارير المالية
        </a>
    </div>
    
    <!-- أزرار التصدير (PDF / Excel) -->
    <div class="action-buttons-wrapper" style="display: flex; gap: 1rem;">
        <a href="{{ route('finance.reports.export', ['from' => $from, 'to' => $to]) }}" class="btn" style="background: #107c41; color: white; border: none;">
            <i class="fas fa-file-excel" style="margin-left: 0.5rem;"></i> تصدير لـ Excel
        </a>
        <button onclick="window.print()" class="btn" style="background: #e11d48; color: white; border: none;">
            <i class="fas fa-file-pdf" style="margin-left: 0.5rem;"></i> تنزيل تقرير PDF
        </button>
    </div>
</div>

<!-- فلترة الفترة الزمنية -->
<div class="table-container filter-section" style="margin-bottom: 2rem; background: rgba(0, 149, 255, 0.03); border: 1px solid rgba(0, 149, 255, 0.15);">
    <form id="filterForm" method="GET" action="{{ route('finance.reports') }}" style="display: flex; flex-direction: column; gap: 1.5rem;">
        <!-- أزرار الفلترة السريعة -->
        <div style="display: flex; flex-wrap: wrap; gap: 0.75rem; align-items: center;">
            <span style="font-size: 0.9rem; color: var(--text-muted); font-weight: 600; margin-left: 0.5rem;">فترات سريعة:</span>
            <button type="button" class="filter-btn" onclick="setDateRange('today')">اليوم</button>
            <button type="button" class="filter-btn" onclick="setDateRange('yesterday')">أمس</button>
            <button type="button" class="filter-btn" onclick="setDateRange('this-month')">الشهر الحالي</button>
            <button type="button" class="filter-btn" onclick="setDateRange('last-month')">الشهر الماضي</button>
            <button type="button" class="filter-btn" onclick="setDateRange('this-year')">السنة الحالية</button>
        </div>

        <hr style="border: 0; border-top: 1px solid rgba(255,255,255,0.05); margin: 0;">

        <!-- الفلترة بالتاريخ المخصص -->
        <div style="display: flex; flex-wrap: wrap; gap: 1.5rem; align-items: flex-end;">
            <div class="form-group" style="flex: 1; min-width: 200px;">
                <label style="font-size: 0.85rem; color: var(--text-muted); font-weight: 600; margin-bottom: 0.5rem; display: block;">من تاريخ</label>
                <input type="date" id="from_date" name="from" value="{{ $from }}" style="padding: 0.75rem; border-radius: 10px; background: #1e293b; border: 1px solid var(--border-color); color: white; width: 100%;">
            </div>
            <div class="form-group" style="flex: 1; min-width: 200px;">
                <label style="font-size: 0.85rem; color: var(--text-muted); font-weight: 600; margin-bottom: 0.5rem; display: block;">إلى تاريخ</label>
                <input type="date" id="to_date" name="to" value="{{ $to }}" style="padding: 0.75rem; border-radius: 10px; background: #1e293b; border: 1px solid var(--border-color); color: white; width: 100%;">
            </div>
            <button type="submit" class="btn btn-primary" style="height: 48px; padding: 0 2rem;">
                <i class="fas fa-filter"></i> تطبيق الفلتر المخصص
            </button>
        </div>
    </form>
</div>

<!-- بطاقات الملخص المالي -->
<div class="stats-grid" style="margin-bottom: 2rem;">
    <div class="stat-card" style="border-right: 4px solid #00e699;">
        <div class="stat-icon-wrapper icon-green">
            <i class="fas fa-arrow-trend-up"></i>
        </div>
        <div class="stat-details">
            <span class="stat-value" style="font-size: 1.5rem;">{{ number_format($report['total_income'], 2) }} ج.م</span>
            <span class="stat-label">إجمالي الإيرادات</span>
        </div>
    </div>
    <div class="stat-card" style="border-right: 4px solid #0095ff;">
        <div class="stat-icon-wrapper icon-blue">
            <i class="fas fa-hand-holding-dollar"></i>
        </div>
        <div class="stat-details">
            <span class="stat-value" style="font-size: 1.5rem;">{{ number_format($report['total_payouts'], 2) }} ج.م</span>
            <span class="stat-label">مستحقات الأطباء</span>
        </div>
    </div>
    <div class="stat-card" style="border-right: 4px solid #ff8529;">
        <div class="stat-icon-wrapper icon-orange">
            <i class="fas fa-arrow-trend-down"></i>
        </div>
        <div class="stat-details">
            <span class="stat-value" style="font-size: 1.5rem;">{{ number_format($report['total_expenses'], 2) }} ج.م</span>
            <span class="stat-label">إجمالي المصروفات</span>
        </div>
    </div>
    <div class="stat-card" style="border-right: 4px solid {{ $report['net_profit'] >= 0 ? '#00e699' : '#ff4d4d' }};">
        <div class="stat-icon-wrapper icon-purple">
            <i class="fas fa-scale-balanced"></i>
        </div>
        <div class="stat-details">
            <span class="stat-value" style="font-size: 1.5rem; color: {{ $report['net_profit'] >= 0 ? '#00e699' : '#ff4d4d' }}">{{ number_format($report['net_profit'], 2) }} ج.م</span>
            <span class="stat-label">صافي الربح للمركز</span>
        </div>
    </div>
</div>

<!-- ========================================== -->
<!-- جدول كشف المرضى للفترات (شهر أو أقل) -->
<!-- ========================================== -->
@if($diffInDays <= 31 && count($patients) > 0)
<div class="table-container" style="margin-bottom: 2rem; border: 1px solid rgba(0, 230, 153, 0.15); background: rgba(0, 230, 153, 0.01); padding: 1.5rem;">
    <h2 style="margin-bottom: 1.5rem; font-size: 1.15rem; display: flex; align-items: center; justify-content: space-between;">
        <span style="display: flex; align-items: center; gap: 0.5rem;">
            <i class="fas fa-users" style="color: #00e699;"></i>
            كشف وحالات المرضى والزيارات التفصيلي (الفترة المحددة)
        </span>
        <span class="badge" style="background: rgba(0, 230, 153, 0.15); color: #00e699; padding: 0.4rem 0.8rem; font-size: 0.8rem;">
            عدد الحالات: {{ count($patients) }} حالة
        </span>
    </h2>
    <div style="overflow-x: auto;">
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>التاريخ</th>
                    <th>اسم المريض</th>
                    <th>السن</th>
                    <th>الهاتف</th>
                    <th>نوع الفحص</th>
                    <th>سعر الفحص</th>
                    <th>طبيب المركز (الداخلي)</th>
                    <th>الطبيب المحول (الخارجي)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($patients as $index => $patient)
                <tr>
                    <td style="font-weight: bold; color: var(--text-muted);">{{ $index + 1 }}</td>
                    <td style="font-weight: 500;">{{ \Carbon\Carbon::parse($patient->date)->format('Y-m-d') }}</td>
                    <td style="font-weight: bold; color: #fff;">{{ $patient->name }}</td>
                    <td>{{ $patient->age }} سنة</td>
                    <td>{{ $patient->phone !== '0' && $patient->phone !== '' ? $patient->phone : '—' }}</td>
                    <td>
                        <span class="badge" style="background: rgba(255,255,255,0.05); color: white;">
                            {{ $patient->test_type_name ?? 'استشارة/عام' }}
                        </span>
                    </td>
                    <td style="font-weight: bold; color: #00e699;">{{ number_format($patient->test_price, 2) }} ج.م</td>
                    <td style="color: #0095ff; font-weight: 500;">{{ $patient->internal_doctor_name ?? '—' }}</td>
                    <td style="color: #ff8529; font-weight: 500;">{{ $patient->external_doctor_name ?? '—' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

<!-- ========================================== -->
<!-- ملخص عمولات ومستحقات الأطباء المبسط والنظيف (PDF & Web) -->
<!-- ========================================== -->
@if(count($report['doctor_payouts']) > 0)
<div class="table-container" style="margin-bottom: 2rem; border: 1px solid rgba(0, 149, 255, 0.15); padding: 1.5rem; page-break-inside: avoid;">
    <h2 style="margin-bottom: 1.5rem; font-size: 1.15rem; display: flex; align-items: center; gap: 0.5rem; border-bottom: 1px solid rgba(255,255,255,0.05); padding-bottom: 0.75rem;">
        <i class="fas fa-user-md" style="color: #0095ff;"></i>
        ملخص عمولات ومستحقات الأطباء
    </h2>
    
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem;">
        <!-- الأطباء الداخليين -->
        <div style="background: rgba(0, 149, 255, 0.02); padding: 1.25rem; border-radius: 12px; border: 1px solid rgba(0, 149, 255, 0.08);">
            <h3 style="font-size: 1rem; color: #0095ff; margin-bottom: 0.75rem; display: flex; align-items: center; justify-content: space-between;">
                <span>أطباء المركز (الداخليين)</span>
                <span style="font-weight: 800; font-size: 1.15rem;">{{ number_format($internalPayouts->sum('total'), 2) }} ج.م</span>
            </h3>
            <p style="font-size: 0.9rem; color: var(--text-muted); line-height: 1.6; margin: 0;">
                <strong style="color: #fff; margin-left: 0.25rem;">الأطباء المستحقون:</strong>
                @if(count($internalPayouts) > 0)
                    {{ implode('، ', collect($internalPayouts)->pluck('doctor_name')->unique()->toArray()) }}
                @else
                    لا توجد مستحقات لأطباء داخليين في هذه الفترة.
                @endif
            </p>
        </div>

        <!-- الأطباء الخارجيين -->
        <div style="background: rgba(255, 133, 41, 0.02); padding: 1.25rem; border-radius: 12px; border: 1px solid rgba(255, 133, 41, 0.08);">
            <h3 style="font-size: 1rem; color: #ff8529; margin-bottom: 0.75rem; display: flex; align-items: center; justify-content: space-between;">
                <span>الأطباء المحولين (الخارجيين)</span>
                <span style="font-weight: 800; font-size: 1.15rem;">{{ number_format($externalPayouts->sum('total'), 2) }} ج.م</span>
            </h3>
            <p style="font-size: 0.9rem; color: var(--text-muted); line-height: 1.6; margin: 0;">
                <strong style="color: #fff; margin-left: 0.25rem;">الأطباء المستحقون:</strong>
                @if(count($externalPayouts) > 0)
                    {{ implode('، ', collect($externalPayouts)->pluck('doctor_name')->unique()->toArray()) }}
                @else
                    لا توجد مستحقات لأطباء خارجيين في هذه الفترة.
                @endif
            </p>
        </div>
    </div>
</div>
@endif

<!-- قسم الرسوم البيانية التفاعلية (ApexCharts) -->
<div class="charts-row" style="display: grid; grid-template-columns: 2fr 1fr; gap: 2rem; margin-bottom: 2rem;">
    <!-- مخطط التدفق النقدي اليومي -->
    <div class="table-container" style="padding: 1.5rem;">
        <h3 style="margin-bottom: 1.5rem; font-size: 1.05rem; display: flex; align-items: center; gap: 0.5rem;">
            <i class="fas fa-chart-line" style="color: var(--primary);"></i>
            مخطط التدفق النقدي اليومي (Cash Flow Trend)
        </h3>
        <div id="cashflowTrendChart" style="min-height: 350px;"></div>
    </div>
    
    <!-- مخطط حصة الإيرادات مقابل النفقات والصافي -->
    <div class="table-container" style="padding: 1.5rem; display: flex; flex-direction: column;">
        <h3 style="margin-bottom: 1.5rem; font-size: 1.05rem; display: flex; align-items: center; gap: 0.5rem;">
            <i class="fas fa-chart-pie" style="color: #ff8529;"></i>
            توزيع الهيكل المالي
        </h3>
        <div style="flex: 1; display: flex; align-items: center; justify-content: center;">
            <div id="financialStructureChart" style="width: 100%;"></div>
        </div>
    </div>
</div>

<div class="charts-row" style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; margin-bottom: 2rem;">
    <!-- مخطط تفصيل فئات الإيرادات -->
    <div class="table-container" style="padding: 1.5rem;">
        <h3 style="margin-bottom: 1.5rem; font-size: 1.05rem; display: flex; align-items: center; gap: 0.5rem;">
            <i class="fas fa-chart-pie" style="color: #00e699;"></i>
            مخطط مصادر الدخل
        </h3>
        <div id="incomePieChart" style="min-height: 250px;"></div>
    </div>

    <!-- مخطط تفصيل فئات المصروفات -->
    <div class="table-container" style="padding: 1.5rem;">
        <h3 style="margin-bottom: 1.5rem; font-size: 1.05rem; display: flex; align-items: center; gap: 0.5rem;">
            <i class="fas fa-chart-pie" style="color: #ff4d4d;"></i>
            مخطط النفقات والمستلزمات
        </h3>
        <div id="expensePieChart" style="min-height: 250px;"></div>
    </div>
</div>

<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; margin-bottom: 2rem;">
    <!-- تفصيل الإيرادات رقمياً -->
    <div class="table-container">
        <h2 style="margin-bottom: 1.5rem; font-size: 1.1rem; display: flex; align-items: center; gap: 0.5rem;">
            <i class="fas fa-list-ul" style="color: #00e699;"></i>
            تفصيل الإيرادات رقمياً
        </h2>
        @forelse($report['income_breakdown'] as $category => $amount)
            <div style="display: flex; justify-content: space-between; padding: 0.9rem 0; border-bottom: 1px solid rgba(255,255,255,0.05);">
                <span style="color: var(--text-muted); font-weight: 500;">
                    {{ match($category) { 'test_revenue' => 'إيرادات الفحوصات', 'manual_income' => 'إيرادات يدوية', default => $category } }}
                </span>
                <span style="font-weight: 700; color: #00e699;">{{ number_format($amount, 2) }} ج.م</span>
            </div>
        @empty
            <p style="color: var(--text-muted); text-align: center; padding: 2rem;">لا توجد إيرادات مسجلة في هذه الفترة</p>
        @endforelse
    </div>

    <!-- تفصيل المصروفات رقمياً -->
    <div class="table-container">
        <h2 style="margin-bottom: 1.5rem; font-size: 1.1rem; display: flex; align-items: center; gap: 0.5rem;">
            <i class="fas fa-list-ul" style="color: #ff8529;"></i>
            تفصيل المصروفات رقمياً
        </h2>
        @forelse($report['expense_breakdown'] as $category => $amount)
            <div style="display: flex; justify-content: space-between; padding: 0.9rem 0; border-bottom: 1px solid rgba(255,255,255,0.05);">
                <span style="color: var(--text-muted); font-weight: 500;">
                    {{ match($category) { 'medical_supplies' => 'مستلزمات طبية', 'manual_expense' => 'مصروفات يدوية', default => $category } }}
                </span>
                <span style="font-weight: 700; color: #ff8529;">{{ number_format($amount, 2) }} ج.م</span>
            </div>
        @empty
            <p style="color: var(--text-muted); text-align: center; padding: 2rem;">لا توجد مصروفات مسجلة في هذه الفترة</p>
        @endforelse
    </div>
</div>

<!-- تضمين مكتبة ApexCharts وعمل الإعدادات التفاعلية -->
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
    // تحديد نطاق التواريخ تلقائياً بالأزرار السريعة
    function setDateRange(period) {
        const fromInput = document.getElementById('from_date');
        const toInput = document.getElementById('to_date');
        const today = new Date();
        
        let fromDate, toDate;
        
        switch(period) {
            case 'today':
                fromDate = today.toISOString().split('T')[0];
                toDate = fromDate;
                break;
            case 'yesterday':
                const yesterday = new Date();
                yesterday.setDate(today.getDate() - 1);
                fromDate = yesterday.toISOString().split('T')[0];
                toDate = fromDate;
                break;
            case 'this-month':
                fromDate = new Date(today.getFullYear(), today.getMonth(), 1 + 1).toISOString().split('T')[0];
                toDate = today.toISOString().split('T')[0];
                break;
            case 'last-month':
                const firstDayLastMonth = new Date(today.getFullYear(), today.getMonth() - 1, 1 + 1);
                const lastDayLastMonth = new Date(today.getFullYear(), today.getMonth(), 0 + 1);
                fromDate = firstDayLastMonth.toISOString().split('T')[0];
                toDate = lastDayLastMonth.toISOString().split('T')[0];
                break;
            case 'this-year':
                fromDate = new Date(today.getFullYear(), 0, 1 + 1).toISOString().split('T')[0];
                toDate = today.toISOString().split('T')[0];
                break;
        }
        
        fromInput.value = fromDate;
        toInput.value = toDate;
        
        // إرسال الفورم فوراً
        document.getElementById('filterForm').submit();
    }

    // إعدادات رسوم ApexCharts
    document.addEventListener("DOMContentLoaded", function() {
        const isDark = true;
        const chartTheme = {
            mode: 'dark',
            palette: 'palette1',
            monochrome: {
                enabled: false
            }
        };

        // 1. مخطط التدفق النقدي اليومي (مخطط مساحي)
        const dailyDates = @json($dailyDates);
        const dailyIncome = @json($dailyIncome);
        const dailyExpense = @json($dailyExpense);

        const cashflowOptions = {
            chart: {
                type: 'area',
                height: 350,
                toolbar: { show: false },
                fontFamily: 'inherit',
                background: 'transparent'
            },
            theme: chartTheme,
            colors: ['#00e699', '#ff4d4d'],
            dataLabels: { enabled: false },
            stroke: { curve: 'smooth', width: 2 },
            series: [
                { name: 'الواردات (الإيرادات)', data: dailyIncome },
                { name: 'الصادرات (المصروفات والعمولات)', data: dailyExpense }
            ],
            xaxis: {
                categories: dailyDates,
                labels: { style: { colors: '#94a3b8' } }
            },
            yaxis: {
                labels: { 
                    formatter: function (value) { return value.toLocaleString() + " ج.م"; },
                    style: { colors: '#94a3b8' }
                }
            },
            grid: { borderColor: 'rgba(255,255,255,0.05)' },
            tooltip: { theme: 'dark' }
        };
        new ApexCharts(document.querySelector("#cashflowTrendChart"), cashflowOptions).render();

        // 2. مخطط الهيكل المالي العام (Donut)
        const totalIncome = {{ $report['total_income'] }};
        const totalPayouts = {{ $report['total_payouts'] }};
        const totalExpenses = {{ $report['total_expenses'] }};
        const netProfit = {{ $report['net_profit'] }};

        const structOptions = {
            chart: {
                type: 'donut',
                height: 300,
                fontFamily: 'inherit',
                background: 'transparent'
            },
            theme: chartTheme,
            colors: ['#0095ff', '#ff8529', '#00e699'],
            labels: ['مستحقات أطباء', 'مصروفات ومستلزمات', 'صافي ربح المركز'],
            series: [totalPayouts, totalExpenses, Math.max(0, netProfit)],
            legend: {
                position: 'bottom',
                labels: { colors: '#94a3b8' }
            },
            dataLabels: { enabled: true },
            tooltip: { theme: 'dark' }
        };
        new ApexCharts(document.querySelector("#financialStructureChart"), structOptions).render();

        // 3. مخطط فئات الإيرادات
        const incomeCategories = @json($incomeCategoriesArabic);
        const incomeAmounts = @json($incomeAmounts);

        const incomePieOptions = {
            chart: {
                type: 'pie',
                height: 250,
                fontFamily: 'inherit',
                background: 'transparent'
            },
            theme: chartTheme,
            colors: ['#00e699', '#0095ff', '#ffc107'],
            labels: incomeCategories.length > 0 ? incomeCategories : ['لا توجد بيانات'],
            series: incomeAmounts.length > 0 ? incomeAmounts : [0],
            legend: {
                position: 'bottom',
                labels: { colors: '#94a3b8' }
            },
            tooltip: { theme: 'dark' }
        };
        new ApexCharts(document.querySelector("#incomePieChart"), incomePieOptions).render();

        // 4. مخطط فئات المصروفات
        const expenseCategories = @json($expenseCategoriesArabic);
        const expenseAmounts = @json($expenseAmounts);

        const expensePieOptions = {
            chart: {
                type: 'pie',
                height: 250,
                fontFamily: 'inherit',
                background: 'transparent'
            },
            theme: chartTheme,
            colors: ['#ff8529', '#ff4d4d', '#9c27b0'],
            labels: expenseCategories.length > 0 ? expenseCategories : ['لا توجد بيانات'],
            series: expenseAmounts.length > 0 ? expenseAmounts : [0],
            legend: {
                position: 'bottom',
                labels: { colors: '#94a3b8' }
            },
            tooltip: { theme: 'dark' }
        };
        new ApexCharts(document.querySelector("#expensePieChart"), expensePieOptions).render();
    });
</script>
@endsection
