<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تسجيل الحضور</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Arial, sans-serif; background: #0d1117; color: #c9d1d9; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .card { background: #161b22; padding: 40px; border-radius: 16px; text-align: center; border: 1px solid #1f6feb; box-shadow: 0 0 20px rgba(31,111,235,0.2); width: 90%; max-width: 400px; }
        h1 { color: #58a6ff; font-size: 24px; }
        .spinner { border: 4px solid rgba(255,255,255,0.1); border-left-color: #58a6ff; border-radius: 50%; width: 40px; height: 40px; animation: spin 1s linear infinite; margin: 0 auto 20px auto; }
        @keyframes spin { 100% { transform: rotate(360deg); } }
        #status { font-size: 18px; font-weight: bold; margin-top: 20px;}
        .error { color: #ff0055; border-color: #ff0055; }
        .success { color: #2ea043; border-color: #2ea043; }
    </style>
</head>
<body>
    <div class="card" id="card">
        <h1>جار تسجيل حضورك...</h1>
        <div id="loader" class="spinner"></div>
        <div id="status"></div>
    </div>

    <script>
        async function processAttendance() {
            const urlParams = new URLSearchParams(window.location.search);
            const token = urlParams.get('token');
            const userId = localStorage.getItem('erp_user_id');
            const deviceId = localStorage.getItem('erp_device_id');
            const statusEl = document.getElementById('status');
            const cardEl = document.getElementById('card');
            const loaderEl = document.getElementById('loader');

            if (!token) {
                statusEl.innerHTML = '❌ لم يتم العثور على الكود.';
                loaderEl.style.display = 'none';
                return;
            }

            if (!userId || !deviceId) {
                cardEl.style.borderColor = '#ff0055';
                statusEl.innerHTML = '❌ هذا الهاتف غير مسجل! برجاء مسح كود "الإعداد" من الإدارة أولاً.';
                statusEl.className = 'error';
                loaderEl.style.display = 'none';
                return;
            }

            try {
                const response = await fetch('/api/attend', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        user_id: userId,
                        qr_content: token,
                        device_id: deviceId
                    })
                });

                const data = await response.json();
                loaderEl.style.display = 'none';

                if (response.ok) {
                    cardEl.style.borderColor = '#2ea043';
                    statusEl.innerHTML = `<span style="font-size:50px">✅</span><br><br>${data.message}`;
                    statusEl.className = 'success';
                } else {
                    cardEl.style.borderColor = '#ff0055';
                    statusEl.innerHTML = `❌ ${data.message}`;
                    statusEl.className = 'error';
                }
            } catch (error) {
                loaderEl.style.display = 'none';
                statusEl.innerHTML = '❌ خطأ في الاتصال بالسيرفر. تأكد أنك على نفس الواي فاي.';
                statusEl.className = 'error';
            }
        }

        // Run automatically
        setTimeout(processAttendance, 500); 
    </script>
</body>
</html>
