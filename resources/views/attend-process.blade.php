<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تسجيل الحضور</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Segoe UI', Tahoma, Arial, sans-serif; background: #0d1117; color: #c9d1d9; display: flex; justify-content: center; align-items: center; min-height: 100vh; margin: 0; padding: 1rem; }
        .card { background: #161b22; padding: 35px 25px; border-radius: 16px; text-align: center; border: 1px solid #1f6feb; box-shadow: 0 0 25px rgba(31,111,235,0.15); width: 100%; max-width: 420px; }
        h1 { color: #58a6ff; font-size: 22px; margin-bottom: 8px; }
        .subtitle { color: #8b949e; font-size: 14px; margin-bottom: 25px; }
        .spinner { border: 4px solid rgba(255,255,255,0.1); border-left-color: #58a6ff; border-radius: 50%; width: 44px; height: 44px; animation: spin 1s linear infinite; margin: 20px auto; }
        @keyframes spin { 100% { transform: rotate(360deg); } }
        .btn-group { display: flex; gap: 1rem; margin-top: 10px; }
        .btn { flex: 1; padding: 16px 10px; border: none; border-radius: 14px; font-size: 17px; font-weight: 700; cursor: pointer; transition: all 0.2s; font-family: inherit; }
        .btn:active { transform: scale(0.96); }
        .btn-in { background: linear-gradient(135deg, #00e699, #00b377); color: #000; box-shadow: 0 4px 20px rgba(0,230,153,0.25); }
        .btn-in:hover { box-shadow: 0 6px 28px rgba(0,230,153,0.35); }
        .btn-out { background: linear-gradient(135deg, #ff8529, #e06b10); color: #000; box-shadow: 0 4px 20px rgba(255,133,41,0.25); }
        .btn-out:hover { box-shadow: 0 6px 28px rgba(255,133,41,0.35); }
        .result { margin-top: 20px; font-size: 16px; font-weight: 600; line-height: 1.6; }
        .success { color: #2ea043; }
        .error { color: #ff4d4d; }
        .icon-large { font-size: 56px; margin-bottom: 15px; }
        .user-info { background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.08); border-radius: 10px; padding: 10px 15px; margin-bottom: 20px; font-size: 14px; color: #8b949e; }
        .user-info strong { color: #58a6ff; }
    </style>
</head>
<body>
    <div class="card" id="card">
        <div id="content">
            <div class="icon-large">⏰</div>
            <h1>تسجيل الحضور والانصراف</h1>
            <p class="subtitle">اختر نوع التسجيل</p>

            <div id="user-info" class="user-info" style="display:none;">
                مرحباً <strong id="user-name-display"></strong>
            </div>

            <div id="error-section" style="display:none;"></div>

            <div id="buttons-section">
                <div class="btn-group">
                    <button class="btn btn-in" onclick="submitAttendance('in')">
                        ✅ تسجيل حضور
                    </button>
                    <button class="btn btn-out" onclick="submitAttendance('out')">
                        🚪 تسجيل انصراف
                    </button>
                </div>
            </div>

            <div id="loading-section" style="display:none;">
                <div class="spinner"></div>
                <p style="color: #8b949e;">جاري التسجيل...</p>
            </div>

            <div id="result-section" style="display:none;" class="result"></div>
        </div>
    </div>

    <script>
        const urlParams = new URLSearchParams(window.location.search);
        const token = urlParams.get('token');
        const userId = localStorage.getItem('erp_user_id');
        const deviceId = localStorage.getItem('erp_device_id');
        const userName = localStorage.getItem('erp_user_name');

        // Show user name if available
        if (userName) {
            document.getElementById('user-name-display').textContent = userName;
            document.getElementById('user-info').style.display = 'block';
        }

        // Check prerequisites
        (function checkPrereqs() {
            const errorSection = document.getElementById('error-section');
            const buttonsSection = document.getElementById('buttons-section');

            if (!token) {
                buttonsSection.style.display = 'none';
                errorSection.style.display = 'block';
                errorSection.innerHTML = '<div class="result error">❌ لم يتم العثور على الكود.<br><span style="font-size:13px;color:#8b949e;">حاول مسح كود QR جديد من شاشة الحضور.</span></div>';
                return;
            }

            if (!userId || !deviceId) {
                buttonsSection.style.display = 'none';
                errorSection.style.display = 'block';
                document.getElementById('card').style.borderColor = '#ff4d4d';
                errorSection.innerHTML = '<div class="result error">❌ هذا الهاتف غير مسجل!<br><span style="font-size:13px;color:#8b949e;">تواصل مع المسؤول لربط جهازك بحسابك أولاً.</span></div>';
                return;
            }
        })();

        async function submitAttendance(type) {
            if (!token || !userId || !deviceId) return;

            const buttonsSection = document.getElementById('buttons-section');
            const loadingSection = document.getElementById('loading-section');
            const resultSection = document.getElementById('result-section');
            const card = document.getElementById('card');

            // Show loading
            buttonsSection.style.display = 'none';
            loadingSection.style.display = 'block';

            try {
                const qrContent = token + '_' + type; // e.g. "abc123_in" or "abc123_out"

                const response = await fetch('/api/attend', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        user_id: userId,
                        qr_content: qrContent,
                        device_id: deviceId
                    })
                });

                const data = await response.json();
                loadingSection.style.display = 'none';
                resultSection.style.display = 'block';

                if (response.ok) {
                    card.style.borderColor = '#2ea043';
                    card.style.boxShadow = '0 0 30px rgba(46,160,67,0.2)';
                    resultSection.innerHTML = `<span style="font-size:56px">✅</span><br><br><span class="success">${data.message}</span>`;
                } else {
                    card.style.borderColor = '#ff4d4d';
                    card.style.boxShadow = '0 0 30px rgba(255,77,77,0.15)';
                    resultSection.innerHTML = `<span class="error">❌ ${data.message}</span>`;
                    // Show buttons again after error
                    setTimeout(() => {
                        resultSection.style.display = 'none';
                        buttonsSection.style.display = 'block';
                        card.style.borderColor = '#1f6feb';
                        card.style.boxShadow = '0 0 25px rgba(31,111,235,0.15)';
                    }, 3000);
                }
            } catch (error) {
                loadingSection.style.display = 'none';
                resultSection.style.display = 'block';
                card.style.borderColor = '#ff4d4d';
                resultSection.innerHTML = '<span class="error">❌ خطأ في الاتصال بالسيرفر.<br><span style="font-size:13px;color:#8b949e;">تأكد أنك متصل بالشبكة.</span></span>';
                setTimeout(() => {
                    resultSection.style.display = 'none';
                    buttonsSection.style.display = 'block';
                    card.style.borderColor = '#1f6feb';
                    card.style.boxShadow = '0 0 25px rgba(31,111,235,0.15)';
                }, 3000);
            }
        }
    </script>
</body>
</html>
