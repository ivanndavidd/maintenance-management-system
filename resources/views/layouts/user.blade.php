<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Warehouse Maintenance') }} - User</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            /* Variabel warna tetap sama */
            --sidebar-width: 250px;
            --primary-color: #4f46e5;
            --primary-gradient: linear-gradient(135deg, #4f46e5, #6366f1);
            --sidebar-bg: #1e293b;
            --sidebar-hover: #334155;
            --sidebar-active: #3b82f6;
            --sidebar-text: #e2e8f0;
            --sidebar-muted: #94a3b8;
        }
        
        body {
            font-family: 'Inter', 'Segoe UI', sans-serif;
            background-color: #f1f5f9;
        }

       /* Sidebar */
        .sidebar {
        position: fixed;
        top: 0;
        left: 0;
        width: var(--sidebar-width);
        height: 100vh;
        background: var(--sidebar-bg);
        color: var(--sidebar-text);
        display: flex;
        flex-direction: column;
        /* PERUBAHAN: Bayangan dibuat lebih halus dan modern */
        box-shadow: 5px 0 20px rgba(0, 0, 0, 0.2); 
        transition: all 0.3s ease;
        z-index: 1000;
        }

        /* Header (Tidak berubah) */
        .sidebar-header {
        background: var(--primary-gradient);
        text-align: center;
        padding: 25px 15px;
        color: white;
        box-shadow: inset 0 -1px 0 rgba(255, 255, 255, 0.15);
        }
        .sidebar-header i { /* Sedikit disesuaikan */
        font-size: 30px;
        margin-bottom: 10px;
        }
        .sidebar-header h4 {
        margin: 0;
        font-weight: 600;
        font-size: 17px;
        }
        .sidebar-header small {
        opacity: 0.9;
        font-size: 13px;
        }

        /* User Info (Tidak berubah) */
        .user-info {
        text-align: center;
        padding: 20px 15px;
        border-bottom: 1px solid rgba(255, 255, 255, 0.08);
        
        }
        .avatar {
        width: 70px;
        height: 70px;
        border-radius: 50%;
        margin: 0 auto 10px;
        background: var(--primary-gradient);
        color: white;
        font-weight: bold;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        box-shadow: 0 3px 6px rgba(0, 0, 0, 0.2);
        }
        .user-info strong {
        display: block;
        font-size: 15px;
        }
        .user-info p {
        font-size: 13px;
        color: var(--sidebar-muted) !important;
        }
        .user-info small.text-muted {
            color: var(--sidebar-muted) !important;
            opacity: 0.9; /* Kita buat sedikit lebih redup dari nama */
        }

        /* Menu */
        .sidebar-menu {
        flex: 1;
        /* PERUBAHAN: Padding di semua sisi agar link "pill" tidak menempel */
        padding: 15px; 
        }

        .sidebar-menu a {
        display: flex;
        align-items: center;
        color: var(--sidebar-text);
        text-decoration: none;
        /* PERUBAHAN: Padding disesuaikan untuk "pill" */
        padding: 13px 18px; 
        font-size: 15px;
        font-weight: 500;
        /* PERUBAHAN: Transisi lebih halus (0.3s) */
        transition: all 0.3s ease; 
        /* Hapus border-left, ganti dengan border-radius */
        border-left: 3px solid transparent; 
        /* BARU: Membuat sudut membulat (tampilan "pill") */
        border-radius: 8px; 
        /* BARU: Memberi jarak antar link menu */
        margin-bottom: 5px; 
        }

        .sidebar-menu a i {
        width: 22px;
        margin-right: 12px;
        font-size: 17px;
        opacity: 0.85;
        /* BARU: Transisi untuk icon */
        transition: opacity 0.3s; 
        }

        .sidebar-menu a:hover {
        background: var(--sidebar-hover);
        color: #fff;
        /* BARU: Efek "pop" kecil saat di-hover */
        transform: translateX(4px); 
        }
        /* BARU: Ikon jadi lebih jelas saat di-hover */
        .sidebar-menu a:hover i {
        opacity: 1; 
        }

        .sidebar-menu a.active {
        /* PERUBAHAN: Latar belakang solid yang kuat */
        background: var(--sidebar-active); 
        /* PERUBAHAN: Teks putih agar kontras */
        color: #ffffff; 
        /* PERUBAHAN: Hapus border kiri */
        border-left-color: transparent; 
        font-weight: 600;
        /* PERUBAHAN: Bayangan untuk memberi kedalaman */
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.25);
        /* BARU: Reset transform dari hover */
        transform: translateX(0); 
        }

        /* BARU: Ikon pada link aktif juga lebih jelas */
        .sidebar-menu a.active i {
        opacity: 1;
        }

        .menu-badge {
        margin-left: auto;
        background: #ef4444;
        padding: 3px 7px;
        border-radius: 12px;
        font-size: 11px;
        font-weight: bold;
        color: white;
        }

        hr {
        /* PERUBAHAN: Garis lebih tipis dan margin disesuaikan */
        border-color: rgba(255, 255, 255, 0.1); 
        margin: 15px; 
        }

        /* Main Content (Tidak berubah) */
        .main-content {
        margin-left: var(--sidebar-width);
        min-height: 100vh;
        padding: 25px;
        background-color: #f8fafc;
        transition: margin-left 0.3s;
        }

        /* Mobile */
        @media (max-width: 768px) {
            .sidebar {
                left: -100%;
            }

            .sidebar.show {
                left: 0;
            }

            .main-content {
                margin-left: 0;
            }

            .mobile-toggle {
                display: flex !important;
            }
        }

        .mobile-toggle {
            display: none;
            position: fixed;
            bottom: 20px;
            right: 20px;
            width: 55px;
            height: 55px;
            border-radius: 50%;
            background: var(--primary-gradient);
            border: none;
            color: white;
            font-size: 22px;
            align-items: center;
            justify-content: center;
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.25);
            z-index: 1200;
        }
    </style>


    @stack('styles')
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <!-- Sidebar Header -->
        <div class="sidebar-header">
            <i class="fas fa-warehouse fa-2x mb-2"></i>
            <h4>Warehouse Maintenance</h4>
            <small>User Panel</small>
        </div>

        <!-- User Info -->
        <div class="user-info">
            <div class="avatar">
                {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
            </div>
            <div class="text-center">
                <strong>{{ auth()->user()->name }}</strong>
                <p class="mb-0 small text-muted">{{ auth()->user()->email }}</p>
                @if(auth()->user()->employee_id)
                    <small class="text-muted">
                        <i class="fas fa-id-card"></i> {{ auth()->user()->employee_id }}
                    </small>
                @endif
            </div>
        </div>

        <!-- Sidebar Menu -->
        <div class="sidebar-menu">
            <a href="{{ route('user.dashboard') }}" class="{{ request()->routeIs('user.dashboard') ? 'active' : '' }}">
                <i class="fas fa-home"></i>
                <span>Dashboard</span>
            </a>

            @if(Route::has('user.urgent-alerts.index'))
            <a href="{{ route('user.urgent-alerts.index') }}" class="{{ request()->routeIs('user.urgent-alerts.*') ? 'active' : '' }}">
                <i class="fas fa-exclamation-triangle"></i>
                <span>Urgent Alerts</span>
                <span class="menu-badge bg-danger">New</span>
            </a>
            @endif

            <a href="{{ route('user.tasks.index') }}" class="{{ request()->routeIs('user.tasks.*') ? 'active' : '' }}">
                <i class="fas fa-tasks"></i>
                <span>My Tasks</span>
            </a>

            <a href="{{ route('user.reports.index') }}" class="{{ request()->routeIs('user.reports.*') ? 'active' : '' }}">
                <i class="fas fa-file-alt"></i>
                <span>My Work Reports</span>
            </a>

            <hr style="border-color: rgba(255, 255, 255, 0.2); margin: 10px 20px;">

            <a href="{{ route('user.help.index') }}" class="{{ request()->routeIs('user.help.*') ? 'active' : '' }}">
                <i class="fas fa-question-circle"></i>
                <span>Help & Support</span>
            </a>

            <a href="{{ route('profile.index') }}" class="{{ request()->routeIs('profile.*') ? 'active' : '' }}">
                <i class="fas fa-user"></i>
                <span>My Profile</span>
            </a>

            <hr style="border-color: rgba(255, 255, 255, 0.2); margin: 10px 20px;">

            <a href="{{ route('logout') }}"
               onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                <i class="fas fa-sign-out-alt"></i>
                <span>Logout</span>
            </a>

            <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                @csrf
            </form>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Success/Error Messages -->
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show">
                <i class="fas fa-check-circle"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="fas fa-exclamation-triangle"></i>
                <strong>Oops!</strong> There were some problems:
                <ul class="mb-0 mt-2">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <!-- Page Content -->
        @yield('content')
    </div>

    <!-- Mobile Toggle Button -->
    <button class="mobile-toggle" onclick="toggleSidebar()">
        <i class="fas fa-bars"></i>
    </button>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('show');
        }

        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', function(event) {
            const sidebar = document.getElementById('sidebar');
            const toggle = document.querySelector('.mobile-toggle');
            
            if (window.innerWidth <= 768) {
                if (!sidebar.contains(event.target) && !toggle.contains(event.target)) {
                    sidebar.classList.remove('show');
                }
            }
        });
    </script>

    @stack('scripts')
</body>
</html>