<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
 <meta charset="utf-8">
 <meta name="viewport" content="width=device-width, initial-scale=1">
 <meta name="csrf-token" content="{{ csrf_token() }}">
 <title>{{ config('app.name', 'Warehouse Maintenance') }}</title>

 <!-- Favicon -->
 <link rel="icon" type="image/png" href="{{ asset('assets/Blibli_Logo_Symbol_FC_RGB.png') }}">

 <!-- Bootstrap CSS -->
 <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
 <!-- Font Awesome from jsDelivr -->
 <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.5.1/css/all.min.css">

 <style>
 /* Sidebar Styles */
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
 width: 260px;
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
 gap: 0;
 justify-content: center;
 transition: padding 0.3s ease, justify-content 0.3s ease, gap 0.3s ease;
 box-sizing: border-box;
 }

 .sidebar:hover .sidebar-header {
 padding: 15px 10px !important;
 justify-content: flex-start;
 gap: 12px;
 }

 .sidebar-header .logo {
 width: 36px;
 height: 48px;
 flex-shrink: 0;
 }

 .sidebar-header .logo img {
 width: 100%;
 height: 100%;
 object-fit: contain;
 }

 .sidebar-header .text-content {
 opacity: 0;
 max-width: 0;
 overflow: hidden;
 transition: opacity 0.3s ease, max-width 0.3s ease;
 }

 .sidebar:hover .sidebar-header .text-content {
 opacity: 1;
 max-width: 500px;
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
 padding: 12px 15px;
 display: block;
 transition: all 0.3s ease;
 border-left: 3px solid transparent;
 font-size: 14px;
 white-space: nowrap;
 overflow: hidden;
 text-align: center;
 position: relative;
 }

 .sidebar:hover a {
 padding: 12px 20px;
 text-align: left;
 }

 .sidebar a .menu-text {
 opacity: 0;
 max-width: 0;
 display: inline-block;
 transition: opacity 0.3s ease, max-width 0.3s ease;
 overflow: hidden;
 vertical-align: middle;
 }

 .sidebar:hover a .menu-text {
 opacity: 1;
 max-width: 200px;
 }

 .sidebar a:hover {
 background: rgba(0, 0, 0, 0.05);
 border-left-color: #0072FF;
 }

 .sidebar:hover a:hover {
 padding: 12px 20px 12px 25px;
 }

 .sidebar a.active {
 background: rgba(0, 114, 255, 0.1);
 border-left-color: #0072FF;
 font-weight: 600;
 color: #0072FF;
 }

 .sidebar a i {
 width: 25px;
 text-align: center;
 display: inline-block;
 vertical-align: middle;
 }

 .sidebar:hover a i {
 margin-right: 10px;
 }

 .sidebar hr {
 border-color: rgba(0, 0, 0, 0.1);
 margin: 10px 0;
 }

 .sidebar-dropdown {
 cursor: pointer;
 }

 .sidebar-dropdown > a {
 position: relative;
 }

 /* Chevron positioning - always absolute to prevent layout shift */
 .sidebar-dropdown .float-end {
 opacity: 0;
 transition: opacity 0.3s ease;
 position: absolute;
 right: 20px;
 top: 50%;
 transform: translateY(-50%);
 pointer-events: none;
 }

 .sidebar:hover .sidebar-dropdown .float-end {
 opacity: 1;
 }

 .sidebar-submenu {
 display: none;
 background: rgba(0, 0, 0, 0.2);
 border-left: 3px solid rgba(52, 152, 219, 0.3);
 max-height: 0;
 overflow: hidden;
 transition: max-height 0.3s ease;
 }

 .sidebar:hover .sidebar-submenu.show {
 display: block;
 max-height: 500px;
 }

 @keyframes slideDown {
 from {
 opacity: 0;
 max-height: 0;
 }
 to {
 opacity: 1;
 max-height: 500px;
 }
 }

 .sidebar-submenu a {
 padding: 10px 15px;
 font-size: 13px;
 border-left: none;
 text-align: center;
 }

 .sidebar:hover .sidebar-submenu a {
 padding: 10px 20px 10px 55px;
 text-align: left;
 }

 .sidebar:hover .sidebar-submenu a:hover {
 padding: 10px 20px 10px 60px;
 background: rgba(255, 255, 255, 0.15);
 }

 .sidebar-submenu a.active {
 background: rgba(255, 255, 255, 0.2);
 }

 .sidebar-submenu a i {
 width: 20px;
 font-size: 12px;
 }

 /* Sidebar Hover Indicator */
 .sidebar::after {
 content: '»';
 position: absolute;
 right: 5px;
 top: 50%;
 transform: translateY(-50%);
 font-size: 20px;
 color: rgba(255, 255, 255, 0.3);
 transition: opacity 0.3s ease;
 pointer-events: none;
 }

 .sidebar:hover::after {
 opacity: 0;
 }

 /* Main Content - Adjusted for fixed sidebar */
 .main-content {
 margin-left: 70px;
 min-height: 100vh;
 background: #f8f9fa;
 transition: margin-left 0.3s ease, width 0.3s ease;
 width: calc(100% - 70px);
 overflow-x: hidden;
 position: relative;
 z-index: 1;
 }

 /* Push content when sidebar is hovered */
 .sidebar:hover ~ .main-content {
 margin-left: 260px;
 width: calc(100% - 260px);
 }

 /* Ensure container takes full width */
 .main-content .container-fluid {
 max-width: 100%;
 padding-left: 1.5rem;
 padding-right: 1.5rem;
 }

 .navbar-top {
 background: white;
 box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
 position: sticky;
 top: 0;
 z-index: 999;
 }

 /* Badge alignment when sidebar not hovered */
 .sidebar .badge {
 opacity: 0;
 transition: opacity 0.3s ease;
 position: absolute;
 }

 .sidebar:hover .badge {
 opacity: 1;
 position: static;
 transform: none;
 }

 /* Compact Table Styles */
 .table-compact {
 font-size: 0.75rem;
 margin-bottom: 0;
 }

 .table-compact th {
 font-size: 0.7rem;
 font-weight: 600;
 white-space: nowrap;
 padding: 0.4rem 0.3rem;
 vertical-align: middle;
 line-height: 1.2;
 }

 .table-compact td {
 padding: 0.4rem 0.3rem;
 vertical-align: middle;
 line-height: 1.3;
 font-size: 0.75rem;
 }

 .table-compact small {
 font-size: 0.7rem;
 }

 .table-compact .btn-sm {
 padding: 0.15rem 0.3rem;
 font-size: 0.65rem;
 line-height: 1.2;
 }

 .table-compact .badge {
 font-size: 0.65rem;
 padding: 0.2rem 0.35rem;
 line-height: 1;
 }

 .table-compact strong {
 font-size: 0.7rem;
 }

 /* Responsive table improvements */
 .table-responsive {
 overflow-x: auto;
 -webkit-overflow-scrolling: touch;
 width: 100%;
 }

 /* Make tables fit container */
 .table {
 width: 100%;
 margin-bottom: 1rem;
 }

 /* Card responsive */
 .card {
 width: 100%;
 margin-bottom: 1rem;
 }

 /* Ensure rows don't overflow */
 .row {
 margin-left: -0.5rem;
 margin-right: -0.5rem;
 }

 .row > * {
 padding-left: 0.5rem;
 padding-right: 0.5rem;
 }

 /* Make action buttons more compact */
 .btn-group-compact {
 display: flex;
 gap: 2px;
 }

 .btn-group-compact .btn {
 padding: 0.2rem 0.35rem;
 font-size: 0.65rem;
 line-height: 1;
 }

 .btn-group-compact .btn i {
 font-size: 0.7rem;
 }

 /* Prevent horizontal scrolling */
 body {
 overflow-x: hidden;
 }

 /* Make charts and visualizations responsive */
 canvas {
 max-width: 100% !important;
 height: auto !important;
 }

 /* Responsive images */
 img {
 max-width: 100%;
 height: auto;
 }

 /* Form controls full width in cards */
 .card .form-control,
 .card .form-select {
 width: 100%;
 }

 /* Fix Bootstrap Modal z-index to appear above sidebar */
 .modal {
 z-index: 1060 !important;
 }
 .modal-backdrop {
 z-index: 1055 !important;
 }
 .modal-dialog {
 z-index: 1065 !important;
 }

 /* Loading Indicator Styles */
 .loading-overlay {
 display: none;
 position: fixed;
 top: 0;
 left: 0;
 width: 100%;
 height: 100%;
 background: rgba(0, 0, 0, 0.7);
 z-index: 1070;
 justify-content: center;
 align-items: center;
 }
 .loading-overlay.show {
 display: flex;
 }
 .loading-spinner {
 text-align: center;
 color: white;
 }
 .loading-spinner .spinner-border {
 width: 4rem;
 height: 4rem;
 border-width: 0.4em;
 }
 .loading-text {
 margin-top: 1rem;
 font-size: 1.1rem;
 font-weight: 500;
 }
 </style>

 @stack('styles')
</head>
<body>
 <!-- Global Loading Overlay -->
 <div class="loading-overlay" id="globalLoading">
 <div class="loading-spinner">
 <div class="spinner-border text-light" role="status">
 <span class="visually-hidden">Loading... 
 </div>
 <div class="loading-text">Processing...</div>
 </div>
 </div>

 <div class="container-fluid p-0">
 <div class="row g-0">
 <!-- Sidebar -->
 <div class="sidebar" id="sidebar">
 <div class="sidebar-header">
 <div class="logo">
 <img src="{{ asset('assets/Blibli_Logo_Symbol_FC_RGB.svg') }}" alt="Logo">
 </div>
 <div class="text-content">
 <h4>Warehouse Maintenance</h4>
 <small>{{ auth()->user()->hasRole('supervisor_maintenance') ? 'Supervisor Panel' : 'Admin Panel' }}</small>
 </div>
 </div>
 <nav>
 @php
 $routePrefix = auth()->user()->hasRole('supervisor_maintenance') ? 'supervisor' : 'admin';
 @endphp

 <a href="{{ route($routePrefix . '.dashboard') }}" class="{{ request()->routeIs($routePrefix . '.dashboard') || request()->routeIs('admin.dashboard') || request()->routeIs('supervisor.dashboard') ? 'active' : '' }}">
 <i class="fas fa-tachometer-alt"></i><span class="menu-text"> Dashboard</span>
 </a>

 @if(auth()->user()->hasRole('admin'))
 <a href="{{ route('admin.users.index') }}" class="{{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
 <i class="fas fa-users"></i><span class="menu-text"> User Management</span>
 </a>
 @elseif(auth()->user()->hasRole('supervisor_maintenance'))
 <a href="{{ route('supervisor.users.index') }}" class="{{ request()->routeIs('supervisor.users.*') ? 'active' : '' }}">
 <i class="fas fa-users"></i><span class="menu-text"> User Management</span>
 </a>
 @endif

 <a href="{{ route($routePrefix . '.shifts.index') }}" class="{{ request()->routeIs('admin.shifts.*') || request()->routeIs('supervisor.shifts.*') ? 'active' : '' }}">
 <i class="fas fa-calendar-week"></i><span class="menu-text"> Shift Mgmt</span>
 </a>

 <hr class="text-white">

 <!-- Inventory Management Dropdown -->
 <div class="sidebar-dropdown" onclick="toggleInventoryMenu()">
 <a class="{{ request()->routeIs('admin.spareparts.*') || request()->routeIs('admin.tools.*') || request()->routeIs('admin.assets.*') ? 'active' : '' }}">
 <i class="fas fa-warehouse"></i><span class="menu-text"> Inventory Mgmt</span> 
 <i class="fas fa-chevron-down float-end" id="inventoryChevron"></i>
 </a>
 </div>
 <div class="sidebar-submenu {{ request()->routeIs('admin.spareparts.*') || request()->routeIs('admin.tools.*') || request()->routeIs('admin.assets.*') ? 'show' : '' }}" id="inventorySubmenu">
 <a href="{{ route('admin.spareparts.index') }}" class="{{ request()->routeIs('admin.spareparts.*') ? 'active' : '' }}">
 <i class="fas fa-cubes"></i><span class="menu-text"> Spareparts</span> 
 </a>
 <a href="{{ route('admin.tools.index') }}" class="{{ request()->routeIs('admin.tools.*') ? 'active' : '' }}">
 <i class="fas fa-tools"></i><span class="menu-text"> Tools</span> 
 </a>
 <a href="{{ route('admin.assets.index') }}" class="{{ request()->routeIs('admin.assets.*') ? 'active' : '' }}">
 <i class="fas fa-sitemap"></i><span class="menu-text"> Assets</span> 
 </a>
 </div>

 <!-- Purchase Orders -->
 <a href="{{ route('admin.purchase-orders.index') }}" class="{{ request()->routeIs('admin.purchase-orders.*') ? 'active' : '' }}">
 <i class="fas fa-shopping-cart"></i><span class="menu-text"> Purchase Orders</span> 
 </a>

 <!-- Stock Opname -->
 <div class="sidebar-dropdown" onclick="toggleOpnameMenu()">
 <a class="{{ request()->routeIs('admin.opname.*') ? 'active' : '' }}">
 <i class="fas fa-clipboard-check"></i><span class="menu-text"> Stock Opname</span> 
 <i class="fas fa-chevron-down float-end" id="opnameChevron"></i>
 </a>
 </div>
 <div class="sidebar-submenu {{ request()->routeIs('admin.opname.*') ? 'show' : '' }}" id="opnameSubmenu">
 <a href="{{ route('admin.opname.dashboard') }}" class="{{ request()->routeIs('admin.opname.dashboard') ? 'active' : '' }}">
 <i class="fas fa-tachometer-alt"></i><span class="menu-text"> Dashboard</span> 
 </a>
 <a href="{{ route('admin.opname.schedules.index') }}" class="{{ request()->routeIs('admin.opname.schedules.*') && !request()->routeIs('admin.opname.compliance.*') ? 'active' : '' }}">
 <i class="fas fa-calendar-alt"></i><span class="menu-text"> Schedules</span> 
 </a>
 <a href="{{ route('admin.opname.compliance.index') }}" class="{{ request()->routeIs('admin.opname.compliance.*') ? 'active' : '' }}">
 <i class="fas fa-file-alt"></i><span class="menu-text"> Compliance Report</span> 
 </a>
 <a href="{{ route('admin.opname.reports.accuracy') }}" class="{{ request()->routeIs('admin.opname.reports.accuracy') ? 'active' : '' }}">
 <i class="fas fa-chart-pie"></i><span class="menu-text"> Accuracy Report</span> 
 </a>
 {{-- Executions menu removed - NEW SYSTEM uses collaborative execution within schedules --}}
 </div>

 <!-- Stock Adjustments -->
 <a href="{{ route('admin.adjustments.index') }}" class="{{ request()->routeIs('admin.adjustments.*') ? 'active' : '' }}">
 <i class="fas fa-adjust"></i><span class="menu-text"> Stock Adjustments</span>
 @php
 $pendingAdjustments = cache()->remember('pending_adjustments_count', 60, function() {
     return \App\Models\StockAdjustment::where('status', 'pending')->count();
 });
 @endphp
 @if($pendingAdjustments > 0)
     <span class="badge bg-danger rounded-pill ms-auto">{{ $pendingAdjustments }}</span>
 @endif
 </a>

 <hr class="text-white">

 <a href="{{ route('admin.preventive-maintenance.index') }}" class="{{ request()->routeIs('admin.preventive-maintenance.*') ? 'active' : '' }}">
 <i class="fas fa-calendar-check"></i><span class="menu-text"> Preventive Maintenance</span> 
 </a>

 <!-- Corrective Maintenance Dropdown -->
 <div class="sidebar-dropdown" onclick="toggleCmMenu()">
 <a class="{{ request()->routeIs('admin.corrective-maintenance.*') ? 'active' : '' }}">
 <i class="fas fa-tools"></i><span class="menu-text"> Corrective Maint.</span>
 @php
 $pendingCMR = cache()->remember('pending_cmr_count', 60, function() {
 return \App\Models\CorrectiveMaintenanceRequest::whereIn('status', ['pending', 'received'])->count();
 });
 @endphp
 @if($pendingCMR > 0)
 <span class="badge bg-danger text-white ms-2">{{ $pendingCMR }}</span>
 @endif
 <i class="fas fa-chevron-down float-end" id="cmChevron"></i>
 </a>
 </div>
 <div class="sidebar-submenu {{ request()->routeIs('admin.corrective-maintenance.*') ? 'show' : '' }}" id="cmSubmenu">
 <a href="{{ route('admin.corrective-maintenance.index') }}" class="{{ request()->routeIs('admin.corrective-maintenance.index') || request()->routeIs('admin.corrective-maintenance.show') ? 'active' : '' }}">
 <i class="fas fa-list"></i><span class="menu-text"> Tickets</span> 
 </a>
 <a href="{{ route('admin.corrective-maintenance.reports') }}" class="{{ request()->routeIs('admin.corrective-maintenance.reports') ? 'active' : '' }}">
 <i class="fas fa-file-alt"></i><span class="menu-text"> Reports</span> 
 </a>
 </div>

 <hr class="text-white">

 <a href="{{ route('admin.kpi.index') }}" class="{{ request()->routeIs('admin.kpi.*') ? 'active' : '' }}">
 <i class="fas fa-chart-line"></i><span class="menu-text"> KPI Management</span>
 </a>

 <a href="{{ route('admin.help-articles.index') }}" class="{{ request()->routeIs('admin.help-articles.*') ? 'active' : '' }}">
 <i class="fas fa-question-circle"></i><span class="menu-text"> Help Articles</span>
 </a>

 @if(auth()->user()->hasRole('supervisor_maintenance'))
 <hr class="text-white">

 <!-- My Tasks Section for Supervisor Maintenance -->
 <a href="{{ route('supervisor.my-tasks.preventive-maintenance') }}" class="{{ request()->routeIs('supervisor.my-tasks.preventive-maintenance*') ? 'active' : '' }}">
 <i class="fas fa-calendar-check"></i><span class="menu-text"> My PM Tasks</span>
 </a>

 <a href="{{ route('supervisor.my-tasks.corrective-maintenance') }}" class="{{ request()->routeIs('supervisor.my-tasks.corrective-maintenance*') ? 'active' : '' }}">
 <i class="fas fa-tools"></i><span class="menu-text"> My CM Tasks</span>
 @php
 $myPendingCM = cache()->remember('my_pending_cm_' . auth()->id(), 60, function() {
 return \App\Models\CorrectiveMaintenanceRequest::whereHas('technicians', function($q) {
 $q->where('user_id', auth()->id());
 })->where('status', 'in_progress')->count();
 });
 @endphp
 @if($myPendingCM > 0)
 <span class="badge bg-warning text-white ms-2">{{ $myPendingCM }}</span>
 @endif
 </a>

 <a href="{{ route('supervisor.my-tasks.stock-opname') }}" class="{{ request()->routeIs('supervisor.my-tasks.stock-opname*') ? 'active' : '' }}">
 <i class="fas fa-clipboard-check"></i><span class="menu-text"> My Stock Opname</span>
 @php
 $myPendingOpname = cache()->remember('my_pending_opname_' . auth()->id(), 60, function() {
 return \App\Models\StockOpnameSchedule::whereHas('userAssignments', function($q) {
 $q->where('user_id', auth()->id());
 })->where('status', 'active')->count();
 });
 @endphp
 @if($myPendingOpname > 0)
 <span class="badge bg-info text-white ms-2">{{ $myPendingOpname }}</span>
 @endif
 </a>
 @endif

 <hr class="text-white">

 <a href="{{ route('profile.index') }}">
 <i class="fas fa-user"></i><span class="menu-text"> Profile</span> 
 </a>

 <a href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
 <i class="fas fa-sign-out-alt"></i><span class="menu-text"> Logout</span> 
 </a>
 
 <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
 @csrf
 </form>
 </nav>
 </div>

 <!-- Main Content -->
 <div class="main-content" id="mainContent">
 <!-- Top Navbar -->
 <nav class="navbar navbar-top px-4 py-3">
 <div class="d-flex justify-content-between w-100">
 <h5 class="mb-0">@yield('page-title', 'Dashboard')</h5>
 <div>
 <span>Welcome, {{ auth()->user()->name }} 
 <span class="badge bg-success ms-2">
 {{ auth()->user()->roles->first()->name ?? 'User' }}
 
 </div>
 </div>
 </nav>

 <!-- Content -->
 <div class="p-4">
 @if (session('success'))
 <div class="alert alert-success alert-dismissible fade show">
 {{ session('success') }}
 <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
 </div>
 @endif

 @if (session('error'))
 <div class="alert alert-danger alert-dismissible fade show">
 {{ session('error') }}
 <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
 </div>
 @endif

 @yield('content')
 </div>
 </div>
 </div>
 </div>

 <!-- Bootstrap JS -->
 <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

 <script>
 // Toggle submenu functions
 function toggleInventoryMenu() {
 const submenu = document.getElementById('inventorySubmenu');
 const chevron = document.getElementById('inventoryChevron');

 submenu.classList.toggle('show');

 if (submenu.classList.contains('show')) {
 chevron.classList.remove('fa-chevron-down');
 chevron.classList.add('fa-chevron-up');
 } else {
 chevron.classList.remove('fa-chevron-up');
 chevron.classList.add('fa-chevron-down');
 }
 }

 function toggleCmMenu() {
 const submenu = document.getElementById('cmSubmenu');
 const chevron = document.getElementById('cmChevron');

 submenu.classList.toggle('show');

 if (submenu.classList.contains('show')) {
 chevron.classList.remove('fa-chevron-down');
 chevron.classList.add('fa-chevron-up');
 } else {
 chevron.classList.remove('fa-chevron-up');
 chevron.classList.add('fa-chevron-down');
 }
 }

 function toggleOpnameMenu() {
 const submenu = document.getElementById('opnameSubmenu');
 const chevron = document.getElementById('opnameChevron');

 submenu.classList.toggle('show');

 if (submenu.classList.contains('show')) {
 chevron.classList.remove('fa-chevron-down');
 chevron.classList.add('fa-chevron-up');
 } else {
 chevron.classList.remove('fa-chevron-up');
 chevron.classList.add('fa-chevron-down');
 }
 }

 // Auto expand if on inventory or opname page
 document.addEventListener('DOMContentLoaded', function() {
 const inventorySubmenu = document.getElementById('inventorySubmenu');
 const inventoryChevron = document.getElementById('inventoryChevron');

 if (inventorySubmenu && inventorySubmenu.classList.contains('show')) {
 inventoryChevron.classList.remove('fa-chevron-down');
 inventoryChevron.classList.add('fa-chevron-up');
 }

 const cmSubmenu = document.getElementById('cmSubmenu');
 const cmChevron = document.getElementById('cmChevron');

 if (cmSubmenu && cmSubmenu.classList.contains('show')) {
 cmChevron.classList.remove('fa-chevron-down');
 cmChevron.classList.add('fa-chevron-up');
 }

 const opnameSubmenu = document.getElementById('opnameSubmenu');
 const opnameChevron = document.getElementById('opnameChevron');

 if (opnameSubmenu && opnameSubmenu.classList.contains('show')) {
 opnameChevron.classList.remove('fa-chevron-down');
 opnameChevron.classList.add('fa-chevron-up');
 }
 });

 // Global Loading Indicator Functions
 function showLoading(message = 'Processing...') {
 const loadingOverlay = document.getElementById('globalLoading');
 const loadingText = loadingOverlay.querySelector('.loading-text');
 loadingText.textContent = message;
 loadingOverlay.classList.add('show');
 }

 function hideLoading() {
 const loadingOverlay = document.getElementById('globalLoading');
 loadingOverlay.classList.remove('show');
 }

 // Show loading on navigation (links)
 document.addEventListener('DOMContentLoaded', function() {
 // Add loading indicator to all navigation links
 const navLinks = document.querySelectorAll('a:not([data-no-loading]):not([data-bs-toggle])');
 navLinks.forEach(link => {
 link.addEventListener('click', function(e) {
 // Don't show loading for anchor links, javascript:void(0), or target="_blank"
 const href = this.getAttribute('href');
 const target = this.getAttribute('target');
 if (href && href !== '#' && !href.startsWith('javascript:') && !href.startsWith('#') && target !== '_blank') {
 showLoading('Loading...');
 }
 });
 });

 // Add loading indicator to all form submissions
 const forms = document.querySelectorAll('form:not([data-no-loading])');
 forms.forEach(form => {
 form.addEventListener('submit', function(e) {
 // Only show loading if form validation passes
 if (form.checkValidity()) {
 const submitBtn = form.querySelector('button[type="submit"]');
 const loadingMessage = submitBtn ? submitBtn.getAttribute('data-loading-text') || 'Saving...' : 'Saving...';
 showLoading(loadingMessage);
 }
 });
 });

 // Hide loading when page fully loads
 window.addEventListener('pageshow', function() {
 hideLoading();
 });

 // Hide loading when any modal is shown
 document.querySelectorAll('.modal').forEach(modal => {
 modal.addEventListener('show.bs.modal', function() {
 hideLoading();
 });
 });

 // Hide loading when clicking on modal backdrop or buttons inside modal
 document.addEventListener('click', function(e) {
 if (e.target.classList.contains('modal') || e.target.closest('.modal')) {
 hideLoading();
 }
 });
 });
 </script>

 <!-- Global fix for Bootstrap modal backdrop issue -->
 <script>
 document.addEventListener('DOMContentLoaded', function() {
 // Remove any stale backdrops on page load
 document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());

 // Ensure body scroll is restored
 if (!document.querySelector('.modal.show')) {
 document.body.classList.remove('modal-open');
 document.body.style.overflow = '';
 document.body.style.paddingRight = '';
 }
 });
 </script>

 @stack('scripts')
 @yield('scripts')
</body>
</html>