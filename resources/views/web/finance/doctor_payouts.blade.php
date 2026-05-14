@extends('layouts.app')

@section('content')
<div class="table-header" style="margin-bottom: 2rem;">
    <h2 style="font-size: 1.8rem;">مستحقات الأطباء</h2>
    
    <div style="display: flex; gap: 0.5rem; background: rgba(255,255,255,0.05); padding: 0.25rem; border-radius: 12px;">
        <a href="{{ route('finance.doctor_payouts', ['filter' => 'all']) }}" 
           class="btn {{ $filter == 'all' ? 'btn-primary' : '' }}" 
           style="background: {{ $filter == 'all' ? 'var(--primary)' : 'transparent' }}; border: none;">الكل</a>
        <a href="{{ route('finance.doctor_payouts', ['filter' => 'unpaid']) }}" 
           class="btn {{ $filter == 'unpaid' ? 'btn-primary' : '' }}"
           style="background: {{ $filter == 'unpaid' ? 'var(--primary)' : 'transparent' }}; border: none;">الغير مدفوع</a>
        <a href="{{ route('finance.doctor_payouts', ['filter' => 'paid']) }}" 
           class="btn {{ $filter == 'paid' ? 'btn-primary' : '' }}"
           style="background: {{ $filter == 'paid' ? 'var(--primary)' : 'transparent' }}; border: none;">المدفوع</a>
    </div>
</div>

<form id="doctor-payouts-bulk-form" action="{{ route('finance.doctor_payouts.mark') }}" method="POST">
    @csrf
</form>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th style="width: 50px;">
                        <input type="checkbox" id="selectAll" onclick="toggleSelectAll()">
                    </th>
                    <th>الطبيب</th>
                    <th>النوع</th>
                    <th>المريض</th>
                    <th>قيمة العمولة</th>
                    <th>تاريخ الفحص</th>
                    <th>الحالة</th>
                    <th>الإجراءات</th>
                </tr>
            </thead>
            <tbody>
                @forelse($payouts as $p)
                <tr>
                    <td>
                        @if(!$p->is_paid)
                            <input type="checkbox" name="payout_ids[]" value="{{ $p->id }}" class="payout-checkbox" form="doctor-payouts-bulk-form">
                        @else
                            <i class="fas fa-check-circle" style="color: #00e699;"></i>
                        @endif
                    </td>
                    <td style="font-weight: 600; color: #fff;">{{ $p->doctor->name }}</td>
                    <td>
                        @if($p->doctor_type == 'external')
                            <span class="badge" style="background: rgba(255, 153, 0, 0.1); color: #ff9900;">محول (خارجي)</span>
                        @else
                            <span class="badge" style="background: rgba(0, 230, 153, 0.1); color: #00e699;">معالج (داخلي)</span>
                        @endif
                    </td>
                    <td>{{ $p->patient?->name ?? '-' }}</td>
                    <td style="font-weight: bold; font-size: 1.1rem; color: #fff;">
                        {{ number_format($p->amount, 2) }} <span style="font-size: 0.8rem; color: var(--text-muted);">ج.م</span>
                    </td>
                    <td style="color: var(--text-muted);">{{ \Carbon\Carbon::parse($p->date)->format('Y-m-d') }}</td>
                    <td>
                        @if($p->is_paid)
                            <span class="badge" style="background: rgba(0, 230, 153, 0.1); color: #00e699;">مدفوع</span>
                        @else
                            <span class="badge" style="background: rgba(255, 77, 77, 0.1); color: #ff4d4d;">مستحق</span>
                        @endif
                    </td>
                    <td>
                        @if(!$p->is_paid)
                            <form action="{{ route('finance.doctor_payouts.mark_individual', $p->id) }}" method="POST" style="display: inline;">
                                @csrf
                                <button type="submit" style="background: none; border: none; color: #00e699; cursor: pointer; font-size: 0.9rem; padding: 0;" title="تأكيد الدفع">
                                    <i class="fas fa-check"></i> تأكيد
                                </button>
                            </form>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" style="text-align: center; padding: 5rem; color: var(--text-muted);">
                        <i class="fas fa-hand-holding-dollar" style="font-size: 4rem; margin-bottom: 1.5rem; display: block; opacity: 0.15;"></i>
                        لا توجد مستحقات تطابق حالة الفلتر
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div style="margin-top: 1.5rem; display: flex; justify-content: flex-end;">
        <button type="submit" form="doctor-payouts-bulk-form" class="btn btn-primary" id="markPaidBtn" style="display: none; background: #00e699; color: #111827; font-weight: bold;">
            <i class="fas fa-check-double"></i>
            تأكيد دفع العمولات المحددة
        </button>
    </div>

<script>
    function toggleSelectAll() {
        const selectAll = document.getElementById('selectAll').checked;
        const checkboxes = document.querySelectorAll('.payout-checkbox');
        checkboxes.forEach(cb => cb.checked = selectAll);
        toggleButtonVisibility();
    }

    document.querySelectorAll('.payout-checkbox').forEach(cb => {
        cb.addEventListener('change', toggleButtonVisibility);
    });

    function toggleButtonVisibility() {
        const anyChecked = document.querySelectorAll('.payout-checkbox:checked').length > 0;
        document.getElementById('markPaidBtn').style.display = anyChecked ? 'block' : 'none';
    }
</script>
@endsection
