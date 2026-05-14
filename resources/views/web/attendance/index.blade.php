@extends('layouts.app')

@section('content')
<div class="table-header" style="margin-bottom: 2rem;">
    <h2 style="font-size: 1.8rem;">سجلات الحضور والانصراف</h2>
    <div style="display: flex; gap: 1rem;">
        <a href="{{ route('attendance.scanner') }}" class="btn btn-primary" style="background: var(--primary); color: #000;">
            <i class="fas fa-qrcode"></i>
            شاشة الحضور الذكية
        </a>
    </div>
</div>

<div class="table-container">
    <table>
        <thead>
            <tr>
                <th>اسم الموظف</th>
                <th>التاريخ</th>
                <th>وقت الحضور</th>
                <th>وقت الانصراف</th>
                <th>الحالة</th>
            </tr>
        </thead>
        <tbody>
            @forelse($attendance as $record)
            <tr>
                <td style="font-weight: 600; color: #fff;">
                    <i class="fas fa-user" style="margin-left: 0.75rem; color: #0095ff; opacity: 0.8;"></i>
                    {{ $record->employee_name }}
                </td>
                <td>{{ $record->date }}</td>
                <td style="color: var(--primary); font-weight: 600;">{{ $record->check_in ?? '---' }}</td>
                <td style="color: #ff8529; font-weight: 600;">{{ $record->check_out ?? '---' }}</td>
                <td>
                    @if($record->check_in && $record->check_out)
                        <span class="badge badge-success" style="background: rgba(0, 230, 153, 0.1); color: var(--primary);">مكتمل</span>
                    @elseif($record->check_in && !$record->check_out)
                        <span class="badge" style="background: rgba(0, 149, 255, 0.1); color: #0095ff;">قيد العمل</span>
                    @else
                        <span class="badge" style="background: rgba(255, 77, 77, 0.1); color: #ff4d4d;">غائب</span>
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5" style="text-align: center; padding: 5rem; color: var(--text-muted);">
                    <i class="fas fa-clipboard-user" style="font-size: 4rem; margin-bottom: 1.5rem; display: block; opacity: 0.15;"></i>
                    لا توجد سجلات حضور حتى الآن
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
