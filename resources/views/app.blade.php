<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>دار السمع ERP</title>
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">
    <!-- Icons (Phosphor Icons or FontAwesome, using Phosphor for modern look) -->
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <!-- Custom CSS -->
    <link rel="stylesheet" href="/css/style.css">
</head>
<body class="dark-theme">
    
    <!-- Login Screen -->
    <div id="login-screen" class="screen">
        <div class="login-card">
            <div class="logo-container">
                <i class="ph ph-ear"></i>
                <h1>دار السمع</h1>
                <p>نظام إدارة العيادات</p>
            </div>
            <form id="login-form">
                <div class="input-group">
                    <label>البريد الإلكتروني</label>
                    <input type="email" id="login-email" required placeholder="admin@admin.com">
                </div>
                <div class="input-group">
                    <label>كلمة المرور</label>
                    <input type="password" id="login-password" required placeholder="••••••••">
                </div>
                <button type="submit" class="btn-primary" id="login-btn">تسجيل الدخول</button>
            </form>
        </div>
    </div>

    <!-- Main App Structure (Hidden until logged in) -->
    <div id="app-container" class="hidden">
        
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <i class="ph ph-ear"></i>
                <h2>دار السمع</h2>
            </div>
            
            <nav class="sidebar-nav">
                <a href="#" data-route="dashboard" class="nav-item active">
                    <i class="ph ph-squares-four"></i> الرئيسية
                </a>
                <a href="#" data-route="patients" class="nav-item">
                    <i class="ph ph-users"></i> المرضى
                </a>
                <a href="#" data-route="doctors" class="nav-item">
                    <i class="ph ph-stethoscope"></i> الأطباء
                </a>
                <a href="#" data-route="test-types" class="nav-item">
                    <i class="ph ph-list-dashes"></i> أنواع الفحوصات
                </a>
                <a href="#" data-route="finance" class="nav-item">
                    <i class="ph ph-currency-circle-dollar"></i> الحسابات
                </a>
                <a href="#" data-route="hr" class="nav-item">
                    <i class="ph ph-identification-badge"></i> شؤون الموظفين
                </a>
                <a href="#" data-route="attendance-kiosk" class="nav-item">
                    <i class="ph ph-qr-code"></i> شاشة الحضور
                </a>
                <a href="#" data-route="delegates" class="nav-item">
                    <i class="ph ph-briefcase"></i> المناديب
                </a>
            </nav>

            <div class="sidebar-footer">
                <button id="logout-btn" class="btn-outline">
                    <i class="ph ph-sign-out"></i> خروج
                </button>
            </div>
        </aside>

        <!-- Main Content Area -->
        <main class="main-content">
            <header class="top-header">
                <h2 id="page-title">الرئيسية</h2>
                <div class="header-actions">
                    <span id="current-time"></span>
                    <button id="theme-toggle" class="icon-btn">
                        <i class="ph ph-sun"></i>
                    </button>
                </div>
            </header>

            <div id="router-view" class="content-body">
                <!-- Dynamic Content injected here -->
            </div>
        </main>
    </div>

    <!-- Toast Notifications Container -->
    <div id="toast-container"></div>

    <!-- JS Logic -->
    <script src="/js/app.js"></script>
</body>
</html>
