@extends('layouts.app')

@section('content')
<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem;">
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

    <!-- Device Pairing Section -->
    <div class="table-container">
        <h2 style="margin-bottom: 1.5rem;">ربط جهاز جديد</h2>
        <p style="color: var(--text-muted); margin-bottom: 2rem;">اختر الموظف لتوليد كود الربط الخاص به</p>
        
        <div class="form-group" style="margin-bottom: 2rem;">
            <select id="employee-select" style="width: 100%; padding: 1rem; border-radius: 12px; background: rgba(25, 33, 49, 1); border: 1px solid var(--border-color); color: white;">
                <option value="">-- اختر الموظف --</option>
                @foreach($employees as $emp)
                    <option value="{{ $emp->id }}">{{ $emp->name }}</option>
                @endforeach
            </select>
        </div>
        
        <button onclick="generatePairingCode()" class="btn btn-primary" style="width: 100%;">توليد كود الربط</button>
        
        <div id="pairing-qr-container" style="margin-top: 2rem; text-align: center; display: none;">
            <div id="pairing-qrcode" style="background: white; padding: 1rem; border-radius: 15px; display: inline-block;"></div>
            <p style="margin-top: 1rem; font-weight: 600;">امسح الكود لربط الجهاز</p>
        </div>
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

    function generatePairingCode() {
        const empId = document.getElementById('employee-select').value;
        if(!empId) return alert('برجاء اختيار موظف');
        
        const container = document.getElementById('pairing-qr-container');
        const qrBox = document.getElementById('pairing-qrcode');
        qrBox.innerHTML = '';
        
        const pairingUrl = window.location.origin + '/setup-phone?user_id=' + empId;
        new QRCode(qrBox, {
            text: pairingUrl,
            width: 180,
            height: 180
        });
        container.style.display = 'block';
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
