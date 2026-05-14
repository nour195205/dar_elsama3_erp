<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Attendance Screen</title>
    <style>
        :root {
            --primary: #00ffd5;
            --bg-color: #0d1117;
            --card-bg: rgba(22, 27, 34, 0.85);
            --text-color: #c9d1d9;
            --accent: #ff0055;
        }

        body {
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background: radial-gradient(circle at center, #1b222c, #07090b);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: var(--text-color);
            overflow: hidden;
        }

        .container {
            background: var(--card-bg);
            padding: 40px;
            border-radius: 24px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.5), 0 0 20px rgba(0, 255, 213, 0.1);
            text-align: center;
            border: 1px solid rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            width: 400px;
        }

        h1 {
            color: var(--primary);
            text-transform: uppercase;
            letter-spacing: 2px;
            margin-bottom: 5px;
            font-size: 24px;
        }

        p {
            font-size: 14px;
            margin-bottom: 30px;
            color: #8b949e;
        }

        #qrcode {
            margin: 0 auto;
            background: white;
            padding: 15px;
            border-radius: 12px;
            display: inline-block;
            box-shadow: 0 0 20px rgba(0, 255, 213, 0.4);
            transition: all 0.3s ease;
        }

        #qrcode img {
            display: block;
        }

        /* Timer Circle */
        .timer-wrapper {
            position: relative;
            width: 80px;
            height: 80px;
            margin: 30px auto 0;
        }

        svg {
            transform: rotate(-90deg);
        }

        .circle-bg {
            fill: none;
            stroke: rgba(255, 255, 255, 0.1);
            stroke-width: 6;
        }

        .circle-progress {
            fill: none;
            stroke: var(--primary);
            stroke-width: 6;
            stroke-dasharray: 201; /* 2 * pi * r (approx 201 for r=32) */
            stroke-dashoffset: 0;
            transition: stroke-dashoffset 1s linear, stroke 0.3s;
            stroke-linecap: round;
        }

        .time-text {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-size: 22px;
            font-weight: bold;
            color: white;
        }

        .status {
            margin-top: 15px;
            font-size: 13px;
            color: #58a6ff;
            opacity: 0.8;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% { opacity: 0.5; }
            50% { opacity: 1; }
            100% { opacity: 0.5; }
        }

        .spinner {
            display: none;
            color: var(--primary);
            font-size: 18px;
            margin: 50px 0;
        }
    </style>
</head>
<body>

    <div class="container">
        <h1>Scan to Attend</h1>
        <p>Ensure you are connected to the local Wi-Fi</p>
        
        <div id="qrcode"></div>
        <div class="spinner" id="spinner">Generating secure token...</div>

        <div class="timer-wrapper">
            <svg width="80" height="80" viewBox="0 0 80 80">
                <circle class="circle-bg" cx="40" cy="40" r="32"></circle>
                <circle class="circle-progress" id="progressCircle" cx="40" cy="40" r="32"></circle>
            </svg>
            <div class="time-text" id="timeLeft">10</div>
        </div>

        <div class="status">System Online - Local Network</div>
    </div>

    <!-- The QR Library we downloaded locally -->
    <script src="/qrcode.min.js"></script>
    <script>
        const REFRESH_INTERVAL = 10; // seconds
        let secondsLeft = REFRESH_INTERVAL;
        let qrCodeObj = null;

        const timeText = document.getElementById('timeLeft');
        const progressCircle = document.getElementById('progressCircle');
        const qrcodeContainer = document.getElementById('qrcode');
        const spinner = document.getElementById('spinner');

        const maxDash = 201; 

        function updateProgress() {
            const offset = maxDash - (secondsLeft / REFRESH_INTERVAL) * maxDash;
            progressCircle.style.strokeDashoffset = offset;
            
            // Change color to red if time is running out
            if(secondsLeft <= 3) {
                progressCircle.style.stroke = 'var(--accent)';
                timeText.style.color = 'var(--accent)';
            } else {
                progressCircle.style.stroke = 'var(--primary)';
                timeText.style.color = 'white';
            }
        }

        async function fetchNewToken() {
            try {
                const response = await fetch('/api/qr-generate');
                const data = await response.json();
                
                generateQR(data.token);
                
                // Reset timer
                secondsLeft = REFRESH_INTERVAL;
                timeText.textContent = secondsLeft;
                updateProgress();

            } catch (error) {
                console.error("Error fetching token:", error);
            }
        }

        function generateQR(token) {
            qrcodeContainer.innerHTML = ''; 
            
            // Build the absolute URL for the scanner page
            const scanUrl = window.location.origin + '/attend-process?token=' + encodeURIComponent(token);

            qrCodeObj = new QRCode(qrcodeContainer, {
                text: scanUrl,
                width: 200,
                height: 200,
                colorDark : "#0d1117",
                colorLight : "#ffffff",
                correctLevel : QRCode.CorrectLevel.H
            });
        }

        // Initialize
        fetchNewToken();

        // Timer Loop
        setInterval(() => {
            secondsLeft--;
            
            if (secondsLeft <= 0) {
                fetchNewToken();
            } else {
                timeText.textContent = secondsLeft;
                updateProgress();
            }
        }, 1000);
    </script>
</body>
</html>
