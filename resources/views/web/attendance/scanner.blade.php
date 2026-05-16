@extends('layouts.app')

@section('content')
<div style="max-width: 600px; margin: 0 auto;">
    <!-- QR Scanner Screen -->
    <div class="table-container" style="text-align: center; padding: 3rem;">
        <h2 style="margin-bottom: 2rem;">كود الحضور والانصراف</h2>
        <p style="color: var(--text-muted); margin-bottom: 2rem;">قم بمسح الكود باستخدام هاتفك المسجل لتسجيل الحضور</p>
        
        <div id="qrcode" style="background: white; padding: 1.5rem; border-radius: 20px; display: inline-block; box-shadow: 0 0 30px var(--primary-glow);"></div>
        
        <div style="margin-top: 2rem; display: flex; flex-direction: column; align-items: center;">
            <div id="timer-text" style="font-size: 1.5rem; font-weight: 700; color: var(--primary); margin-bottom: 0.5rem;">10</div>
            <p style="font-size: 0.8rem; color: var(--text-muted);">سيتم تحديث الكود تلقائياً</p>
        </div>
    </div>

    <div style="margin-top: 1.5rem; text-align: center;">
        <p style="font-size: 0.85rem; color: var(--text-muted); line-height: 1.8;">
            <i class="fas fa-info-circle" style="margin-left: 0.4rem; color: var(--primary);"></i>
            لربط جهاز موظف جديد، توجه لصفحة <a href="{{ route('employees.index') }}" style="color: var(--primary);">شؤون الموظفين</a> واختر تعديل الموظف المطلوب.
        </p>
    </div>
</div>

@push('scripts')
<script src="/qrcode.min.js"></script>
<script>
    const REFRESH_INTERVAL = 10;
    let secondsLeft = REFRESH_INTERVAL;
    const qrcodeContainer = document.getElementById('qrcode');
    const timerText = document.getElementById('timer-text');

    async function fetchNewToken() {
        try {
            const response = await fetch('/api/qr-generate');
            const data = await response.json();
            generateQR(data.token);
            secondsLeft = REFRESH_INTERVAL;
            timerText.textContent = secondsLeft;
        } catch (error) {
            console.error("Error:", error);
        }
    }

    function generateQR(token) {
        qrcodeContainer.innerHTML = '';
        const scanUrl = window.location.origin + '/attend-process?token=' + encodeURIComponent(token);
        new QRCode(qrcodeContainer, {
            text: scanUrl,
            width: 250,
            height: 250,
            colorDark : "#0f172a",
            colorLight : "#ffffff",
            correctLevel : QRCode.CorrectLevel.H
        });
    }

    fetchNewToken();
    setInterval(() => {
        secondsLeft--;
        if (secondsLeft <= 0) fetchNewToken();
        else timerText.textContent = secondsLeft;
    }, 1000);
</script>
@endpush
@endsection
