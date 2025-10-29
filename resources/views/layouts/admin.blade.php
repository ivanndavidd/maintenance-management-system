<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <meta name="csrf-token" content="{{ csrf_token() }}" />
        <title>{{ config('app.name', 'Warehouse Maintenance') }}</title>

        <!-- Bootstrap CSS -->
        <link
            href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"
            rel="stylesheet"
        />
        <!-- Font Awesome -->
        <link
            rel="stylesheet"
            href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"
        />

        <style>
            .sidebar {
                min-height: 100vh;
                background: #2c3e50;
                color: white;
            }
            .sidebar a {
                color: #ecf0f1;
                text-decoration: none;
                padding: 10px 15px;
                display: block;
            }
            .sidebar a:hover,
            .sidebar a.active {
                background: #34495e;
            }
            .main-content {
                min-height: 100vh;
                background: #f8f9fa;
            }
            .navbar-top {
                background: white;
                box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            }
        </style>
    </head>
    <body>
        <div class="container-fluid">
            <div class="row">
                <!-- Sidebar -->
                <div class="col-md-2 p-0 sidebar">
                    <div class="p-3">
                        <h4>🏭 Warehouse</h4>
                        <small>Maintenance System</small>
                    </div>
                    <hr />
                    <nav>
                        <a
                            href="{{ route('admin.dashboard') }}"
                            class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}"
                        >
                            <i class="fas fa-tachometer-alt"></i>
                            Dashboard
                        </a>
                        <a
                            href="{{ route('admin.users.index') }}"
                            class="{{ request()->routeIs('admin.users.*') ? 'active' : '' }}"
                        >
                            <i class="fas fa-users"></i>
                            User Management
                        </a>
                        <a
                            href="{{ route('admin.jobs.index') }}"
                            class="{{ request()->routeIs('admin.jobs.*') ? 'active' : '' }}"
                        >
                            <i class="fas fa-wrench"></i>
                            Maintenance Jobs
                        </a>
                        <a
                            href="{{ route('admin.reports.index') }}"
                            class="{{ request()->routeIs('admin.reports.*') ? 'active' : '' }}"
                        >
                            <i class="fas fa-file-alt"></i>
                            Work Reports
                        </a>
                        <a
                            href="{{ route('admin.machines.index') }}"
                            class="{{ request()->routeIs('admin.machines.*') ? 'active' : '' }}"
                        >
                            <i class="fas fa-cogs"></i>
                            Equipment
                        </a>
                        <a
                            href="{{ route('admin.parts.index') }}"
                            class="{{ request()->routeIs('admin.parts.*') ? 'active' : '' }}"
                        >
                            <i class="fas fa-boxes"></i>
                            Parts Inventory
                        </a>
                        <hr />
                        <a href="{{ route('profile.index') }}">
                            <i class="fas fa-user"></i>
                            Profile
                        </a>
                        <a
                            href="{{ route('logout') }}"
                            onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
                        >
                            <i class="fas fa-sign-out-alt"></i>
                            Logout
                        </a>
                        <form
                            id="logout-form"
                            action="{{ route('logout') }}"
                            method="POST"
                            class="d-none"
                        >
                            @csrf
                        </form>
                    </nav>
                </div>

                <!-- Main Content -->
                <div class="col-md-10 p-0 main-content">
                    <!-- Top Navbar -->
                    <nav class="navbar navbar-top px-4 py-3">
                        <div class="d-flex justify-content-between w-100">
                            <h5 class="mb-0">@yield('page-title', 'Dashboard')</h5>
                            <div>
                                <span>Welcome, {{ auth()->user()->name }}</span>
                                <span class="badge bg-success ms-2">
                                    {{ auth()->user()->roles->first()->name ?? 'User' }}
                                </span>
                            </div>
                        </div>
                    </nav>

                    <!-- Content -->
                    <div class="p-4">
                        @if (session('success'))
                            <div class="alert alert-success alert-dismissible fade show">
                                {{ session('success') }}
                                <button
                                    type="button"
                                    class="btn-close"
                                    data-bs-dismiss="alert"
                                ></button>
                            </div>
                        @endif

                        @if (session('error'))
                            <div class="alert alert-danger alert-dismissible fade show">
                                {{ session('error') }}
                                <button
                                    type="button"
                                    class="btn-close"
                                    data-bs-dismiss="alert"
                                ></button>
                            </div>
                        @endif

                        @yield('content')
                    </div>
                </div>
            </div>
        </div>

        <!-- Bootstrap JS -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
        @yield('scripts')
    </body>
</html>
