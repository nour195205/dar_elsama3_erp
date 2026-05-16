<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إعداد هاتف الموظف</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Segoe UI', Tahoma, Arial, sans-serif; background: #0d1117; color: #c9d1d9; display: flex; justify-content: center; align-items: center; min-height: 100vh; margin: 0; padding: 1rem; }
        .card { background: #161b22; padding: 40px 30px; border-radius: 16px; text-align: center; border: 1px solid #1f6feb; box-shadow: 0 0 30px rgba(31,111,235,0.15); width: 100%; max-width: 420px; }
        h1 { color: #58a6ff; font-size: 22px; margin-bottom: 10px; }
        .icon { font-size: 56px; margin-bottom: 20px; }
        .spinner { border: 4px solid rgba(255,255,255,0.1); border-left-color: #58a6ff; border-radius: 50%; width: 44px; height: 44px; animation: spin 1s linear infinite; margin: 20px auto; }
        @keyframes spin { 100% { transform: rotate(360deg); } }
        .success { color: #2ea043; }
        .error { color: #ff4d4d; }
        .info-text { color: #8b949e; font-size: 14px; line-height: 1.8; margin-top: 15px; }
        .highlight { color: #00ffd5; font-weight: bold; }
        .no-token-card { border-color: #ffa500; box-shadow: 0 0 20px rgba(255,165,0,0.15); }
    </style>
</head>
<body>
    <div class="card" id="card">
        <div id="content">
            <div class="icon">📱</div>
            <h1>ربط الهاتف بالنظام</h1>
            <div class="spinner"></div>
            <p style="color: #8b949e;">جاري تسجيل جهازك...</p>
        </div>
    </div>

    <script>
        const urlParams = new URLSearchParams(window.location.search);
        const pairToken = urlParams.get('pair_token');
        const card = document.getElementById('card');
        const content = document.getElementById('content');

        async function setupDevice() {
            // No pairing token = show "contact admin" message
            if (!pairToken) {
                card.classList.add('no-token-card');
                card.style.borderColor = '#ffa500';
                content.innerHTML = `
                    <div class="icon">🔒</div>
                    <h1 style="color: #ffa500;">ربط الجهاز غير متاح</h1>
                    <p class="info-text">
                        عملية ربط الهاتف لتسجيل الحضور والانصراف
                        <br>تتم فقط عن طريق <span class="highlight">المدير أو المسؤول</span>
                        <br>من لوحة تحكم النظام.
                    </p>
                    <p style="color: #58a6ff; font-size: 13px; margin-top: 20px; border-top: 1px solid rgba(255,255,255,0.1); padding-top: 15px;">
                        📋 تواصل مع المسؤول لربط جهازك بحسابك في النظام.
                    </p>`;
                return;
            }

            // Generate or retrieve a persistent device ID for this phone
            let deviceId = localStorage.getItem('erp_device_id');
            if (!deviceId) {
                deviceId = 'dev_' + crypto.randomUUID().replace(/-/g, '') + '_' + Date.now();
            }

            try {
                const response = await fetch('/api/complete-pairing', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        pair_token: pairToken,
                        device_id: deviceId
                    })
                });

                const data = await response.json();

                if (response.ok) {
                    // Save device info to this phone's browser
                    localStorage.setItem('erp_device_id', deviceId);
                    localStorage.setItem('erp_user_id', data.user_id);
                    localStorage.setItem('erp_user_name', data.user_name);

                    card.style.borderColor = '#2ea043';
                    card.style.boxShadow = '0 0 30px rgba(46,160,67,0.2)';
                    content.innerHTML = `
                        <div style="font-size: 64px; margin-bottom: 20px;">✅</div>
                        <h1 class="success">تم الربط بنجاح!</h1>
                        <p style="color: #c9d1d9; font-size: 16px; margin-top: 15px;">
                            هذا الهاتف مسجل الآن باسم:
                            <br><strong class="highlight">${data.user_name}</strong>
                        </p>
                        <p class="info-text" style="margin-top: 20px; border-top: 1px solid rgba(255,255,255,0.1); padding-top: 15px;">
                            يمكنك الآن استخدام هذا الهاتف لمسح كود الحضور والانصراف يومياً.
                            <br>يمكنك إغلاق هذه الصفحة الآن.
                        </p>`;
                } else {
                    card.style.borderColor = '#ff4d4d';
                    card.style.boxShadow = '0 0 30px rgba(255,77,77,0.15)';
                    content.innerHTML = `
                        <div style="font-size: 64px; margin-bottom: 20px;">❌</div>
                        <h1 class="error">فشل الربط</h1>
                        <p style="color: #c9d1d9; font-size: 15px; margin-top: 15px;">${data.message}</p>
                        <p class="info-text" style="margin-top: 20px;">تواصل مع المسؤول لإنشاء رمز ربط جديد.</p>`;
                }
            } catch (e) {
                card.style.borderColor = '#ff4d4d';
                content.innerHTML = `
                    <div style="font-size: 64px; margin-bottom: 20px;">❌</div>
                    <h1 class="error">خطأ في الاتصال</h1>
                    <p style="color: #c9d1d9; font-size: 15px; margin-top: 15px;">تأكد أنك متصل بالشبكة وحاول مرة أخرى.</p>`;
            }
        }

        // Wait a moment then process
        setTimeout(setupDevice, 600);
    </script>
</body>
</html>
