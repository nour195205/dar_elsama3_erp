<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إعداد هاتف الموظف</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Arial, sans-serif; background: #0d1117; color: #c9d1d9; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .card { background: #161b22; padding: 40px; border-radius: 16px; text-align: center; border: 1px solid #00ffd5; box-shadow: 0 0 20px rgba(0,255,213,0.2); width: 90%; max-width: 400px; }
        h1 { color: #00ffd5; font-size: 24px; }
        .success { color: #2ea043; font-weight: bold; font-size: 18px; margin-top: 20px;}
        .icon { font-size: 60px; margin-bottom: 20px; }
    </style>
</head>
<body>
    <div class="card">
        <div class="icon">📱</div>
        <h1>ربط الهاتف بالموظف</h1>
        <p>يتم الآن تخزين بياناتك على هذا المتصفح بشكل آمن...</p>
        <div id="status"></div>
    </div>

    <script>
        // Get query parameters
        const urlParams = new URLSearchParams(window.location.search);
        const userId = urlParams.get('user_id');
        const name = urlParams.get('name');

        async function setupDevice() {
            if (userId && name) {
                // Generate a random Device ID if not exists
                let deviceId = localStorage.getItem('erp_device_id');
                if(!deviceId) {
                    deviceId = 'web_' + Math.random().toString(36).substr(2, 9) + '_' + Date.now();
                }

                try {
                    const response = await fetch('/api/pair-device', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            user_id: userId,
                            device_id: deviceId
                        })
                    });

                    const data = await response.json();

                    if (response.ok) {
                        localStorage.setItem('erp_device_id', deviceId);
                        localStorage.setItem('erp_user_id', userId);
                        localStorage.setItem('erp_user_name', name);

                        document.getElementById('status').innerHTML = '<p class="success">✅ تم بنجاح! هذا الهاتف مسجل الآن باسم: ' + name + '</p><p style="color:#8b949e;font-size:14px;margin-top:20px;">يمكنك إغلاق هذه الصفحة الآن واستخدام الكاميرا كل يوم لتسجيل الحضور.</p>';
                    } else {
                        document.getElementById('status').innerHTML = '<p style="color:#ff0055;">❌ ' + data.message + '</p>';
                    }
                } catch (e) {
                    document.getElementById('status').innerHTML = '<p style="color:#ff0055;">❌ خطأ في الاتصال بالخادم. تأكد من إنك متصل بالشبكة.</p>';
                }
            } else {
                document.getElementById('status').innerHTML = '<p style="color:#ff0055;">❌ رابط غير صالح.</p>';
            }
        }

        setupDevice();
    </script>
</body>
</html>
