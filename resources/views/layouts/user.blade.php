<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Warehouse Maintenance') }} - User</title>

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('assets/Blibli_Logo_Symbol_FC_RGB.png') }}">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome from jsDelivr -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.5.1/css/all.min.css">
    
    <style>
        :root {
            /* Variabel warna tetap sama */
            --sidebar-width: 250px;
            --sidebar-collapsed-width: 70px;
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
            width: 70px;
            height: 100vh;
            background: linear-gradient(180deg, #e2e8f0 0%, #cbd5e0 100%);
            color: #1a202c;
            overflow-y: auto;
            overflow-x: hidden;
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
            z-index: 1000;
            transition: width 0.3s ease;
            padding: 0 !important;
        }

        .sidebar:hover {
            width: 280px;
            box-shadow: 2px 0 20px rgba(0, 0, 0, 0.3);
        }

        .sidebar::-webkit-scrollbar {
            width: 6px;
        }

        .sidebar::-webkit-scrollbar-track {
            background: rgba(0, 0, 0, 0.05);
        }

        .sidebar::-webkit-scrollbar-thumb {
            background: rgba(0, 0, 0, 0.2);
            border-radius: 3px;
        }

        .sidebar::-webkit-scrollbar-thumb:hover {
            background: rgba(0, 0, 0, 0.3);
        }

        .sidebar-header {
            padding: 15px 0 !important;
            margin: 0 !important;
            width: 100%;
            background: rgba(255, 255, 255, 0.5);
            border-bottom: 1px solid rgba(0, 0, 0, 0.1);
            white-space: nowrap;
            overflow: hidden;
            display: flex;
            align-items: center;
            box-sizing: border-box;
        }

        .sidebar-header .logo {
            width: 36px;
            height: 48px;
            min-width: 36px;
            flex-shrink: 0;
            margin-left: 17px;
        }

        .sidebar-header .logo img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        .sidebar-header .text-content {
            opacity: 0;
            width: 0;
            overflow: hidden;
            transition: opacity 0.3s ease, width 0.3s ease, margin-left 0.3s ease;
            margin-left: 0;
        }

        .sidebar:hover .sidebar-header .text-content {
            opacity: 1;
            width: 200px;
            margin-left: 12px;
        }

        .sidebar-header h4 {
            margin: 0;
            font-size: 16px;
            font-weight: 700;
            white-space: nowrap;
            line-height: 1.2;
        }

        .sidebar-header small {
            color: rgba(0, 0, 0, 0.6);
            font-size: 11px;
            white-space: nowrap;
            display: block;
        }

        .sidebar nav {
            padding: 0;
            margin: 0;
        }

        .sidebar a {
            color: #2d3748;
            text-decoration: none;
            padding: 12px 0;
            padding-left: 22px;
            display: flex;
            align-items: center;
            border-left: 3px solid transparent;
            font-size: 14px;
            white-space: nowrap;
            overflow: hidden;
            position: relative;
        }

        .sidebar a .menu-text {
            opacity: 0;
            width: 0;
            overflow: hidden;
            transition: opacity 0.3s ease, width 0.3s ease, margin-left 0.3s ease;
            margin-left: 0;
        }

        .sidebar:hover a .menu-text {
            opacity: 1;
            width: 180px;
            margin-left: 12px;
        }

        .sidebar a:hover {
            background: rgba(0, 0, 0, 0.05);
            border-left-color: #0072FF;
        }

        .sidebar a.active {
            background: rgba(0, 114, 255, 0.1);
            border-left-color: #0072FF;
            font-weight: 600;
            color: #0072FF;
        }

        .sidebar a i {
            width: 25px;
            min-width: 25px;
            text-align: center;
            flex-shrink: 0;
        }

        .sidebar hr {
            border-color: rgba(0, 0, 0, 0.1);
            margin: 10px 0;
        }

        /* Badge alignment when sidebar collapsed - hide badge */
        .sidebar .badge {
            display: none;
        }

        /* Badge alignment when sidebar expanded - show badge */
        .sidebar:hover .badge {
            display: inline-block;
            font-size: 11px;
            padding: 2px 6px;
            margin-left: auto;
            margin-right: 45px;
            flex-shrink: 0;
        }

        /* Main Content - Adjusted for fixed sidebar */
        .main-content {
            margin-left: 70px;
            min-height: 100vh;
            padding: 25px;
            background-color: #f8fafc;
            transition: margin-left 0.3s ease, width 0.3s ease;
            width: calc(100% - 70px);
            overflow-x: hidden;
            position: relative;
            z-index: 1;
        }

        /* Push content when sidebar is hovered */
        .sidebar:hover ~ .main-content {
            margin-left: 280px;
            width: calc(100% - 280px);
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
            <div class="logo">
                <img src="{{ asset('assets/Blibli_Logo_Symbol_FC_RGB.svg') }}" alt="Logo">
            </div>
            <div class="text-content">
                <h4>Warehouse Maintenance</h4>
                <small>User Panel</small>
            </div>
        </div>

        <!-- Sidebar Menu -->
        <nav>
            <a href="{{ route('user.dashboard') }}" class="{{ request()->routeIs('user.dashboard') ? 'active' : '' }}">
                <i class="fas fa-home"></i><span class="menu-text"> Dashboard</span>
            </a>

            <a href="{{ route('user.corrective-maintenance.index') }}" class="{{ request()->routeIs('user.corrective-maintenance.*') ? 'active' : '' }}">
                <i class="fas fa-tools"></i><span class="menu-text"> Corrective Maintenance</span>
                @php
                    $pendingCM = cache()->remember('pending_cm_user_' . auth()->id(), 60, function() {
                        return \App\Models\CorrectiveMaintenanceRequest::whereHas('technicians', function($q) {
                            $q->where('user_id', auth()->id());
                        })->where('status', 'in_progress')->count();
                    });
                @endphp
                @if($pendingCM > 0)
                    <span class="badge bg-warning text-white ms-2">{{ $pendingCM }}
                @endif
            </a>

            <a href="{{ route('user.stock-opname.index') }}" class="{{ request()->routeIs('user.stock-opname.*') ? 'active' : '' }}">
                <i class="fas fa-clipboard-check"></i><span class="menu-text"> Stock Opname</span>
                @php
                    $pendingOpname = cache()->remember('pending_opname_user_' . auth()->id(), 60, function() {
                        return \App\Models\StockOpnameSchedule::whereHas('userAssignments', function($q) {
                            $q->where('user_id', auth()->id());
                        })->where('status', 'active')->count();
                    });
                @endphp
                @if($pendingOpname > 0)
                    <span class="badge bg-info text-white ms-2">{{ $pendingOpname }}
                @endif
            </a>

            <a href="{{ route('user.preventive-maintenance.index') }}" class="{{ request()->routeIs('user.preventive-maintenance.*') ? 'active' : '' }}">
                <i class="fas fa-calendar-check"></i><span class="menu-text"> Preventive Maintenance</span>
            </a>

            <hr class="text-white">

            <a href="{{ route('user.help.index') }}" class="{{ request()->routeIs('user.help.*') ? 'active' : '' }}">
                <i class="fas fa-question-circle"></i><span class="menu-text"> Help & Support</span>
            </a>

            <a href="{{ route('profile.index') }}" class="{{ request()->routeIs('profile.*') ? 'active' : '' }}">
                <i class="fas fa-user"></i><span class="menu-text"> My Profile</span>
            </a>

            <hr class="text-white">

            <a href="{{ route('logout') }}"
               onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                <i class="fas fa-sign-out-alt"></i><span class="menu-text"> Logout</span>
            </a>

            <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                @csrf
            </form>
        </nav>
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