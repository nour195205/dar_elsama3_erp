<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'دار السمع ERP' }}</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    
    <!-- Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Main Style -->
    <link rel="stylesheet" href="{{ asset('css/style.css') }}?v={{ time() }}">
    
    <style>
        /* Immediate fallback to avoid 'messed up' look if CSS fails to load */
        body { background-color: #0b0f19; color: white; }
    </style>
</head>
<body class="rtl">
    <aside class="sidebar">
        <div class="logo">
            <div class="logo-icon">
                <i class="fas fa-ear-listen" style="color: #000;"></i>
            </div>
            <div class="logo-text">دار السمع</div>
        </div>

        <nav>
            <ul class="nav-links">
                <li class="nav-item {{ Request::is('dashboard*') ? 'active' : '' }}">
                    <a href="{{ route('dashboard') }}">
                        <i class="fas fa-th-large"></i>
                        <span>لوحة التحكم</span>
                    </a>
                </li>

                <li style="padding: 0.75rem 1.5rem 0.25rem; font-size: 0.7rem; color: rgba(255,255,255,0.25); text-transform: uppercase; letter-spacing: 1px;">العيادة</li>

                @if(auth()->user()->hasPermission('module_patients'))
                <li class="nav-item {{ Request::is('patients*') ? 'active' : '' }}">
                    <a href="{{ route('patients.index') }}">
                        <i class="fas fa-hospital-user"></i>
                        <span>المرضى</span>
                    </a>
                </li>
                @endif
                @if(auth()->user()->hasPermission('module_doctors'))
                <li class="nav-item {{ Request::is('doctors*') ? 'active' : '' }}">
                    <a href="{{ route('doctors.index') }}">
                        <i class="fas fa-user-md"></i>
                        <span>الأطباء</span>
                    </a>
                </li>
                @endif
                @if(auth()->user()->hasPermission('module_test_types'))
                <li class="nav-item {{ Request::is('test-types*') ? 'active' : '' }}">
                    <a href="{{ route('test-types.index') }}">
                        <i class="fas fa-stethoscope"></i>
                        <span>أنواع الفحوصات</span>
                    </a>
                </li>
                @endif
                @if(auth()->user()->hasPermission('module_delegates'))
                <li class="nav-item {{ Request::is('delegates*') ? 'active' : '' }}">
                    <a href="{{ route('delegates.index') }}">
                        <i class="fas fa-handshake"></i>
                        <span>المناديب</span>
                    </a>
                </li>
                @endif

                <li style="padding: 0.75rem 1.5rem 0.25rem; font-size: 0.7rem; color: rgba(255,255,255,0.25); text-transform: uppercase; letter-spacing: 1px;">الإدارة</li>

                @if(auth()->user()->hasPermission('module_finance'))
                <li class="nav-item {{ Request::is('finance*') ? 'active' : '' }}">
                    <a href="{{ route('finance.index') }}">
                        <i class="fas fa-vault"></i>
                        <span>المالية</span>
                    </a>
                </li>
                @endif
                @if(auth()->user()->hasPermission('module_employees'))
                <li class="nav-item {{ Request::is('employees*') ? 'active' : '' }}">
                    <a href="{{ route('employees.index') }}">
                        <i class="fas fa-user-tie"></i>
                        <span>الموظفين</span>
                    </a>
                </li>
                @endif
                @if(auth()->user()->hasPermission('module_activity_logs'))
                <li class="nav-item {{ Request::is('staff/activity-logs*') ? 'active' : '' }}">
                    <a href="{{ route('staff.activity_logs') }}">
                        <i class="fas fa-clipboard-list"></i>
                        <span>سجل النشاط</span>
                    </a>
                </li>
                @endif
                @if(auth()->user()->role === 'admin')
                <li class="nav-item {{ Request::is('permission-groups*') ? 'active' : '' }}">
                    <a href="{{ route('permission-groups.index') }}">
                        <i class="fas fa-layer-group"></i>
                        <span>مجموعات الصلاحيات</span>
                    </a>
                </li>
                @endif
                @if(auth()->user()->hasPermission('module_attendance'))
                <li class="nav-item {{ Request::is('attendance*') ? 'active' : '' }}">
                    <a href="{{ route('attendance.index') }}">
                        <i class="fas fa-fingerprint"></i>
                        <span>الحضور</span>
                    </a>
                </li>
                @endif
                @if(auth()->user()->hasPermission('module_settings'))
                <li class="nav-item {{ Request::is('settings*') ? 'active' : '' }}">
                    <a href="{{ route('settings.index') }}">
                        <i class="fas fa-sliders"></i>
                        <span>الإعدادات</span>
                    </a>
                </li>
                @endif
            </ul>
        </nav>
    </aside>

    <main class="main-content">
        <header>
            <div class="header-title">
                <h1>{{ $title ?? 'لوحة التحكم' }}</h1>
                <p>{{ $subtitle ?? 'مرحباً بك في نظام إدارة عيادة دار السمع' }}</p>
            </div>
            <div class="header-actions" style="display: flex; align-items: flex-start; gap: 1rem; flex-wrap: wrap; justify-content: flex-end;">
                <div class="global-search-wrap" style="position: relative; min-width: 200px; max-width: 320px; flex: 1 1 200px;">
                    <input type="search" id="global-search-input" name="global_q" autocomplete="off" placeholder="بحث في المرضى، الأطباء، الفحوصات…" aria-label="بحث في النظام"
                        style="width: 100%; padding: 0.6rem 0.85rem; border-radius: 10px; background: rgba(255,255,255,0.06); border: 1px solid var(--border-color); color: #fff; font-size: 0.88rem;">
                    <div id="global-search-results" style="display: none; position: absolute; top: calc(100% + 6px); inset-inline-end: 0; width: min(420px, 92vw); max-height: 70vh; overflow: auto; background: #121a2b; border: 1px solid var(--border-color); border-radius: 12px; box-shadow: 0 12px 40px rgba(0,0,0,0.45); z-index: 2000; padding: 0.35rem 0;"></div>
                </div>
                @if(session('success'))
                    <div style="background: rgba(0, 230, 153, 0.1); color: #00e699; padding: 0.5rem 1rem; border-radius: 10px; font-size: 0.85rem; margin-left: 1rem;">
                        <i class="fas fa-check-circle"></i> {{ session('success') }}
                    </div>
                @endif
                @if(session('error'))
                    <div style="background: rgba(255, 77, 77, 0.1); color: #ff4d4d; padding: 0.5rem 1rem; border-radius: 10px; font-size: 0.85rem; margin-left: 1rem;">
                        <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
                    </div>
                @endif
                <div style="display: flex; align-items: center; gap: 0.75rem;">
                    <span style="font-size: 0.85rem; color: var(--text-muted); max-width: 160px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">{{ auth()->user()->name }}</span>
                    <form action="{{ route('logout') }}" method="POST" style="margin: 0;">
                        @csrf
                        <button type="submit" class="btn" style="padding: 0.45rem 0.85rem; font-size: 0.82rem; background: rgba(255,255,255,0.08); color: #fff; border: 1px solid var(--border-color); border-radius: 10px; cursor: pointer;">
                            خروج
                        </button>
                    </form>
                </div>
            </div>
        </header>

        <div class="page-body">
            @yield('content')
        </div>
    </main>

    <script>
    (function () {
        var input = document.getElementById('global-search-input');
        var box = document.getElementById('global-search-results');
        var wrap = input && input.closest ? input.closest('.global-search-wrap') : null;
        if (!input || !box || !wrap) return;

        var timer = null;

        function hide() {
            box.style.display = 'none';
            box.innerHTML = '';
        }

        function esc(s) {
            return String(s == null ? '' : s)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/"/g, '&quot;');
        }

        function section(title, items) {
            if (!items || !items.length) return '';
            var h = '<div style="padding:0.35rem 0.75rem;font-size:0.68rem;color:rgba(255,255,255,0.38);letter-spacing:0.04em;">' + esc(title) + '</div>';
            var links = items.map(function (it) {
                return '<a href="' + esc(it.url) + '" style="display:block;padding:0.5rem 0.85rem;color:#e2e8f0;text-decoration:none;font-size:0.9rem;border-radius:8px;margin:0 0.35rem;" onmouseenter="this.style.background=\'rgba(255,255,255,0.07)\'" onmouseleave="this.style.background=\'transparent\'">' + esc(it.label) + '</a>';
            }).join('');
            return h + links;
        }

        function render(data) {
            var parts = [
                section('المرضى', data.patients),
                section('الأطباء', data.doctors),
                section('المناديب', data.delegates),
                section('أنواع الفحوصات', data.test_types),
                section('الموظفين', data.employees)
            ].filter(Boolean);
            if (!parts.length) {
                box.innerHTML = '<div style="padding:1rem;color:var(--text-muted);font-size:0.9rem;">لا توجد نتائج</div>';
            } else {
                box.innerHTML = parts.join('');
            }
            box.style.display = 'block';
        }

        input.addEventListener('input', function () {
            clearTimeout(timer);
            var q = input.value.trim();
            if (q.length < 2) {
                hide();
                return;
            }
            timer = setTimeout(function () {
                fetch('{{ route('global.search') }}?' + new URLSearchParams({ q: q }).toString())
                    .then(function (r) { return r.json(); })
                    .then(render)
                    .catch(hide);
            }, 300);
        });

        document.addEventListener('click', function (e) {
            if (!wrap.contains(e.target)) hide();
        });

        input.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') hide();
        });
    })();
    </script>

    @stack('scripts')
</body>
</html>
