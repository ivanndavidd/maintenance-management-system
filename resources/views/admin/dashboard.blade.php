@extends('layouts.admin')

@push('styles')
<style>
    /* Mini Calendar Styles */
    .mini-calendar {
        background: white;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }

    .calendar-header {
        padding: 15px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 12px 12px 0 0;
        text-align: center;
    }

    .calendar-grid {
        display: grid;
        grid-template-columns: repeat(7, 1fr);
        gap: 6px;
        padding: 8px;
        background: white;
    }

    .calendar-day-header {
        text-align: center;
        padding: 8px 4px;
        font-weight: 600;
        font-size: 10px;
        color: #999;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .calendar-day {
        aspect-ratio: 1;
        border: none;
        padding: 4px;
        position: relative;
        min-height: 45px;
        max-height: 45px;
        background: white;
        border-radius: 8px;
        transition: all 0.2s;
        cursor: pointer;
    }

    .calendar-day:hover {
        background: #f5f5f5;
        transform: translateY(-2px);
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }

    .calendar-day:hover .day-tooltip {
        opacity: 1;
        visibility: visible;
        transform: translateX(-50%);
    }

    .day-tooltip {
        position: absolute;
        bottom: 100%;
        left: 50%;
        transform: translateX(-50%);
        margin-bottom: 8px;
        background: rgba(0, 0, 0, 0.9);
        color: white;
        padding: 8px 12px;
        border-radius: 6px;
        font-size: 11px;
        white-space: nowrap;
        opacity: 0;
        visibility: hidden;
        transition: all 0.2s;
        z-index: 1000;
        pointer-events: none;
        box-shadow: 0 4px 12px rgba(0,0,0,0.3);
    }

    .day-tooltip::after {
        content: '';
        position: absolute;
        top: 100%;
        left: 50%;
        transform: translateX(-50%);
        border: 5px solid transparent;
        border-top-color: rgba(0, 0, 0, 0.9);
    }

    .day-tooltip .tooltip-row {
        display: flex;
        align-items: center;
        gap: 6px;
        margin: 3px 0;
    }

    .day-tooltip .tooltip-dot {
        width: 6px;
        height: 6px;
        border-radius: 50%;
    }

    .calendar-day.other-month {
        background: transparent;
    }

    .calendar-day.other-month .day-number {
        color: #ddd;
    }

    .calendar-day.today {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
    }

    .calendar-day.today .day-number {
        color: white;
        font-weight: 700;
    }

    .day-number {
        font-size: 13px;
        font-weight: 500;
        padding: 2px;
        line-height: 1.2;
        color: #333;
        text-align: center;
    }

    /* Shift Indicator - Triangle in top-right corner */
    .shift-indicator {
        position: absolute;
        top: 0;
        right: 0;
        width: 0;
        height: 0;
        border-style: solid;
        border-width: 0 14px 14px 0;
        border-radius: 0 8px 0 0;
    }

    .shift-indicator.shift-1 {
        border-color: transparent #0d6efd transparent transparent;
    }

    .shift-indicator.shift-2 {
        border-color: transparent #ffc107 transparent transparent;
    }

    .shift-indicator.shift-3 {
        border-color: transparent #dc3545 transparent transparent;
    }

    /* Task Indicators - Circles at bottom */
    .task-indicators {
        position: absolute;
        bottom: 4px;
        left: 50%;
        transform: translateX(-50%);
        display: flex;
        gap: 3px;
        justify-content: center;
    }

    .task-dot {
        width: 7px;
        height: 7px;
        border-radius: 50%;
    }

    /* Month Navigation */
    .month-nav-btn {
        background: rgba(255,255,255,0.2);
        border: none;
        color: white;
        padding: 5px 12px;
        border-radius: 5px;
        cursor: pointer;
        transition: all 0.2s;
    }

    .month-nav-btn:hover {
        background: rgba(255,255,255,0.3);
    }

    .month-nav-btn:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }

    .task-dot.cm {
        background: #ffc107; /* Yellow for Corrective */
    }

    .task-dot.stock {
        background: #0d6efd; /* Blue for Stock Opname */
    }

    .task-dot.pm {
        background: #28a745; /* Green for Preventive */
    }

    /* Today Tasks Card */
    .today-task-item {
        padding: 12px;
        border-left: 4px solid;
        margin-bottom: 10px;
        border-radius: 4px;
        background: #f8f9fa;
        transition: all 0.2s;
    }

    .today-task-item:hover {
        transform: translateX(5px);
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }

    .today-task-item.corrective {
        border-left-color: #ffc107;
        background: #fff8e1;
    }

    .today-task-item.preventive {
        border-left-color: #28a745;
        background: #e8f5e9;
    }

    .today-task-item.stock_opname {
        border-left-color: #0d6efd;
        background: #e7f3ff;
    }

    .task-type-badge {
        font-size: 10px;
        padding: 2px 8px;
        border-radius: 12px;
        font-weight: 600;
        text-transform: uppercase;
    }

    .opacity-25 { opacity: 0.25; }
    .card { transition: transform 0.2s; }
    .card:hover { transform: translateY(-3px); }

    /* KPI Monitor Styles */
    .kpi-timeframe-selector .btn-group .btn {
        padding: 6px 16px;
        font-size: 13px;
        font-weight: 600;
        border-radius: 0;
        transition: all 0.2s;
    }
    .kpi-timeframe-selector .btn-group .btn:first-child { border-radius: 6px 0 0 6px; }
    .kpi-timeframe-selector .btn-group .btn:last-child { border-radius: 0 6px 6px 0; }
    .kpi-timeframe-selector .btn-check:checked + .btn-outline-primary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-color: #667eea;
        color: white;
    }
    .kpi-metric-card {
        text-align: center;
        padding: 20px 15px;
        border-radius: 10px;
        transition: all 0.2s;
    }
    .kpi-metric-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }
    .kpi-metric-value { font-size: 28px; font-weight: 800; line-height: 1.2; }
    .kpi-metric-label {
        font-size: 12px; font-weight: 600; text-transform: uppercase;
        letter-spacing: 0.5px; margin-top: 4px; color: #6c757d;
    }
    .kpi-custom-range { display: none; margin-top: 10px; }
    .kpi-custom-range.show { display: flex; }
    .kpi-loading-overlay {
        position: absolute; top: 0; left: 0; right: 0; bottom: 0;
        background: rgba(255,255,255,0.8); display: flex;
        align-items: center; justify-content: center; z-index: 10; border-radius: 12px;
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2>{{ auth()->user()->hasRole('supervisor_maintenance') ? 'Supervisor Dashboard' : 'Admin Dashboard' }}</h2>
            <p class="text-muted mb-0">Welcome back, <strong>{{ auth()->user()->name }}</strong>!</p>
        </div>
        <div class="text-end">
            <small class="text-muted d-block">{{ Carbon\Carbon::now()->format('l, F d, Y') }}</small>
            <small class="text-muted">{{ Carbon\Carbon::now()->format('h:i A') }}</small>
        </div>
    </div>

    @if(!auth()->user()->hasRole('supervisor_maintenance'))
    <!-- KPI Monitor Section (Admin Only) -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm" style="border-radius: 12px; overflow: hidden;">
                <div class="card-header bg-white py-3">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <div>
                            <h6 class="mb-0 fw-bold" style="color: #2d3748;">
                                <i class="fas fa-chart-bar text-primary"></i> KPI Monitor
                                <span class="badge bg-light text-muted fw-normal ms-1" id="kpiDateRange" style="font-size: 11px;"></span>
                            </h6>
                        </div>
                        <div class="kpi-timeframe-selector">
                            <div class="btn-group" role="group">
                                <input type="radio" class="btn-check" name="kpiTimeframe" id="kpi1M" value="1M" checked>
                                <label class="btn btn-outline-primary" for="kpi1M">1M</label>
                                <input type="radio" class="btn-check" name="kpiTimeframe" id="kpi3M" value="3M">
                                <label class="btn btn-outline-primary" for="kpi3M">3M</label>
                                <input type="radio" class="btn-check" name="kpiTimeframe" id="kpi6M" value="6M">
                                <label class="btn btn-outline-primary" for="kpi6M">6M</label>
                                <input type="radio" class="btn-check" name="kpiTimeframe" id="kpi1Y" value="1Y">
                                <label class="btn btn-outline-primary" for="kpi1Y">1Y</label>
                                <input type="radio" class="btn-check" name="kpiTimeframe" id="kpiCustom" value="custom">
                                <label class="btn btn-outline-primary" for="kpiCustom"><i class="fas fa-calendar-alt"></i> Custom</label>
                            </div>
                        </div>
                    </div>
                    <div class="kpi-custom-range align-items-center gap-2 mt-2" id="kpiCustomRange">
                        <input type="date" class="form-control form-control-sm" id="kpiDateFrom" style="max-width: 160px;">
                        <span class="text-muted mx-1">to</span>
                        <input type="date" class="form-control form-control-sm" id="kpiDateTo" style="max-width: 160px;">
                        <button class="btn btn-sm btn-primary ms-1" onclick="loadKpiData('custom')">
                            <i class="fas fa-search"></i> Apply
                        </button>
                    </div>
                </div>
                <div class="card-body position-relative" id="kpiCardBody">
                    <div class="kpi-loading-overlay" id="kpiLoading" style="display: none;">
                        <div class="text-center">
                            <div class="spinner-border text-primary"></div>
                            <div class="mt-2 text-muted small">Loading KPI data...</div>
                        </div>
                    </div>

                    <div class="row g-4">
                        <!-- KPI 1: PM Tasks -->
                        <div class="col-lg-4">
                            <div class="card border-0 shadow-sm h-100" style="border-radius: 12px;">
                                <div class="card-header bg-success bg-opacity-10 border-0">
                                    <h6 class="mb-0 text-success fw-bold"><i class="fas fa-calendar-check"></i> PM Tasks</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row g-2 mb-3">
                                        <div class="col-4">
                                            <div class="kpi-metric-card bg-success bg-opacity-10">
                                                <div class="kpi-metric-value text-success" id="pmOnTime">-</div>
                                                <div class="kpi-metric-label">On Time</div>
                                            </div>
                                        </div>
                                        <div class="col-4">
                                            <div class="kpi-metric-card bg-warning bg-opacity-10">
                                                <div class="kpi-metric-value text-warning" id="pmLate">-</div>
                                                <div class="kpi-metric-label">Late</div>
                                            </div>
                                        </div>
                                        <div class="col-4">
                                            <div class="kpi-metric-card bg-danger bg-opacity-10">
                                                <div class="kpi-metric-value text-danger" id="pmNotDone">-</div>
                                                <div class="kpi-metric-label">Not Done</div>
                                            </div>
                                        </div>
                                    </div>
                                    <div style="height: 180px; position: relative;">
                                        <canvas id="pmKpiChart"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- KPI 2: CM Tickets -->
                        <div class="col-lg-4">
                            <div class="card border-0 shadow-sm h-100" style="border-radius: 12px;">
                                <div class="card-header bg-primary bg-opacity-10 border-0">
                                    <h6 class="mb-0 text-primary fw-bold"><i class="fas fa-wrench"></i> CM Tickets</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row g-2 mb-3">
                                        <div class="col-6">
                                            <div class="kpi-metric-card bg-warning bg-opacity-10">
                                                <div class="kpi-metric-value text-warning" id="cmOpen">-</div>
                                                <div class="kpi-metric-label">Open</div>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="kpi-metric-card bg-success bg-opacity-10">
                                                <div class="kpi-metric-value text-success" id="cmClosed">-</div>
                                                <div class="kpi-metric-label">Closed</div>
                                            </div>
                                        </div>
                                    </div>
                                    <div style="height: 180px; position: relative;">
                                        <canvas id="cmKpiChart"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- KPI 3: Stock Opname -->
                        <div class="col-lg-4">
                            <div class="card border-0 shadow-sm h-100" style="border-radius: 12px;">
                                <div class="card-header bg-info bg-opacity-10 border-0">
                                    <h6 class="mb-0 text-info fw-bold"><i class="fas fa-clipboard-check"></i> Stock Opname</h6>
                                </div>
                                <div class="card-body">
                                    <div class="text-center mb-2">
                                        <div style="height: 120px; position: relative;">
                                            <canvas id="soAccuracyChart"></canvas>
                                        </div>
                                        <div class="mt-1">
                                            <small class="text-muted">
                                                <span class="text-success fw-bold" id="soAccurate">-</span> accurate /
                                                <span class="text-danger fw-bold" id="soDiscrepancy">-</span> discrepancy
                                            </small>
                                        </div>
                                    </div>
                                    <hr class="my-2">
                                    <div class="row g-2">
                                        <div class="col-6">
                                            <div class="kpi-metric-card bg-danger bg-opacity-10">
                                                <div class="kpi-metric-value text-danger" id="soMissed" style="font-size: 22px;">-</div>
                                                <div class="kpi-metric-label" style="font-size: 10px;">Missed Jobs</div>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="kpi-metric-card bg-secondary bg-opacity-10">
                                                <div class="kpi-metric-value text-secondary" id="soUncovered" style="font-size: 22px;">-</div>
                                                <div class="kpi-metric-label" style="font-size: 10px;">Uncovered Items</div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="text-center mt-1">
                                        <small class="text-muted" id="soUncoveredBreakdown" style="font-size: 10px;"></small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <!-- CMR Card -->
        <div class="col-md-3 mb-3">
            <div class="card border-primary h-100 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2">Corrective Maintenance</h6>
                            <h3 class="mb-0 fw-bold">{{ $stats['total_cmr'] }}</h3>
                            <small>
                                <span class="text-warning">{{ $stats['pending_cmr'] }} pending</span> |
                                <span class="text-primary">{{ $stats['in_progress_cmr'] }} in progress</span> |
                                <span class="text-success">{{ $stats['completed_cmr'] }} completed</span>
                                @if($stats['further_repair_cmr'] > 0)
                                | <span class="text-danger">{{ $stats['further_repair_cmr'] }} further repair</span>
                                @endif
                            </small>
                        </div>
                        <div class="text-primary opacity-25">
                            <i class="fas fa-wrench fa-3x"></i>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-primary text-white">
                    <a href="{{ route($routePrefix . '.corrective-maintenance.index') }}" class="text-white text-decoration-none">
                        View All <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>
        </div>

        <!-- PM Card -->
        <div class="col-md-3 mb-3">
            <div class="card border-success h-100 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2">Preventive Maintenance</h6>
                            <h3 class="mb-0 fw-bold">{{ $stats['total_pm'] }}</h3>
                            <small>
                                <span class="text-secondary">{{ $stats['draft_pm'] }} draft</span> |
                                <span class="text-primary">{{ $stats['active_pm'] }} active</span> |
                                <span class="text-success">{{ $stats['completed_pm'] }} completed</span>
                            </small>
                        </div>
                        <div class="text-success opacity-25">
                            <i class="fas fa-calendar-check fa-3x"></i>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-success text-white">
                    <a href="{{ route($routePrefix . '.preventive-maintenance.index') }}" class="text-white text-decoration-none">
                        View All <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>
        </div>

        <!-- Operators Card -->
        <div class="col-md-3 mb-3">
            <div class="card border-info h-100 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2">Operators</h6>
                            <h3 class="mb-0 fw-bold">{{ $stats['total_operators'] }}</h3>
                            <small class="text-success">{{ $stats['active_operators'] }} Active</small>
                        </div>
                        <div class="text-info opacity-25">
                            <i class="fas fa-users fa-3x"></i>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-info text-white">
                    <a href="{{ route($routePrefix . '.users.index') }}" class="text-white text-decoration-none">
                        View All Users <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>
        </div>

        <!-- Avg Resolution Time Card -->
        <div class="col-md-3 mb-3">
            <div class="card border-warning h-100 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2">Avg Resolution Time</h6>
                            <h3 class="mb-0 fw-bold">{{ number_format($avgResolutionTime, 1) }}h</h3>
                            <small class="text-muted">CMR completed tickets</small>
                        </div>
                        <div class="text-warning opacity-25">
                            <i class="fas fa-clock fa-3x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if(auth()->user()->hasRole('supervisor_maintenance'))
    <!-- Today Tasks & Mini Calendar (For Supervisor Only) -->
    <div class="row g-3 mb-4">
        <!-- Today Tasks -->
        <div class="col-lg-6">
            <div class="card shadow-sm h-100">
                <div class="card-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                    <h5 class="mb-0 text-white">
                        <i class="fas fa-calendar-day text-white"></i> <span class="text-white">Today's Tasks</span>
                        <span class="badge bg-white text-primary ms-2">{{ $todayTasks->count() }}</span>
                    </h5>
                </div>
                <div class="card-body" style="max-height: 500px; overflow-y: auto;">
                    @forelse($todayTasks as $task)
                        <a href="{{ $task['url'] }}" class="text-decoration-none">
                            <div class="today-task-item {{ $task['type'] }}">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div class="flex-grow-1">
                                        <div class="d-flex align-items-center gap-2 mb-1">
                                            <span class="task-type-badge
                                                {{ $task['type'] === 'corrective' ? 'bg-warning text-dark' : '' }}
                                                {{ $task['type'] === 'preventive' ? 'bg-success text-white' : '' }}
                                                {{ $task['type'] === 'stock_opname' ? 'bg-primary text-white' : '' }}
                                            ">
                                                @if($task['type'] === 'corrective')
                                                    <i class="fas fa-wrench"></i> CM
                                                @elseif($task['type'] === 'preventive')
                                                    <i class="fas fa-calendar-check"></i> PM
                                                @else
                                                    <i class="fas fa-boxes"></i> Stock
                                                @endif
                                            </span>
                                            <strong>{{ $task['title'] }}</strong>
                                        </div>
                                        <small class="text-muted">
                                            <i class="fas fa-hashtag"></i> {{ $task['id'] }}
                                        </small>
                                    </div>
                                    <div class="text-end">
                                        <span class="badge bg-{{ $task['status'] === 'pending' ? 'warning' : 'primary' }}">
                                            {{ ucfirst(str_replace('_', ' ', $task['status'])) }}
                                        </span>
                                        @if(isset($task['priority']))
                                            <br>
                                            <small class="badge bg-{{ $task['priority'] === 'high' ? 'danger' : ($task['priority'] === 'medium' ? 'warning' : 'secondary') }} mt-1">
                                                {{ ucfirst($task['priority']) }}
                                            </small>
                                        @endif
                                        @if(isset($task['shift']))
                                            <br>
                                            <small class="badge bg-info mt-1">
                                                Shift {{ $task['shift'] }}
                                            </small>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </a>
                    @empty
                        <div class="text-center text-muted py-5">
                            <i class="fas fa-clipboard-check fa-3x mb-3"></i>
                            <p class="mb-0">No tasks scheduled for today</p>
                            <small>Enjoy your day!</small>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Mini Calendar -->
        <div class="col-lg-6">
            <div class="mini-calendar">
                <div class="calendar-header">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <button class="month-nav-btn" onclick="changeMonth(-1)">
                            <i class="fas fa-chevron-left"></i>
                        </button>
                        <h5 class="mb-0" id="calendarMonthYear">
                            <i class="fas fa-calendar-alt"></i> <span id="currentMonthDisplay">{{ now()->format('F Y') }}</span>
                        </h5>
                        <button class="month-nav-btn" onclick="changeMonth(1)">
                            <i class="fas fa-chevron-right"></i>
                        </button>
                    </div>
                    <small>My Schedule</small>
                </div>
                <div class="p-2">
                    <!-- Legend -->
                    <div class="mb-3 p-2 rounded" style="background: #f8f9fa; font-size: 11px;">
                        <div class="d-flex gap-3 flex-wrap justify-content-center align-items-center">
                            <div class="d-flex align-items-center gap-1">
                                <span class="task-dot cm"></span>
                                <span style="color: #6c757d;">CM</span>
                            </div>
                            <div class="d-flex align-items-center gap-1">
                                <span class="task-dot stock"></span>
                                <span style="color: #6c757d;">Stock</span>
                            </div>
                            <div class="d-flex align-items-center gap-1">
                                <span class="task-dot pm"></span>
                                <span style="color: #6c757d;">PM</span>
                            </div>
                            <div style="border-left: 1px solid #dee2e6; height: 15px; margin: 0 4px;"></div>
                            <div class="d-flex align-items-center gap-1">
                                <span style="color: #0d6efd; font-size: 11px;">▲</span>
                                <span style="color: #6c757d;">S1</span>
                            </div>
                            <div class="d-flex align-items-center gap-1">
                                <span style="color: #ffc107; font-size: 11px;">▲</span>
                                <span style="color: #6c757d;">S2</span>
                            </div>
                            <div class="d-flex align-items-center gap-1">
                                <span style="color: #dc3545; font-size: 11px;">▲</span>
                                <span style="color: #6c757d;">S3</span>
                            </div>
                        </div>
                    </div>

                    <!-- Calendar Grid -->
                    <div class="calendar-grid" id="calendarGrid">
                        <!-- Will be populated by JavaScript -->
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Charts Row -->
    <div class="row mb-4">
        <!-- CMR Trend -->
        <div class="col-md-8 mb-3">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="fas fa-chart-line"></i> CMR Trend (Last 7 Days)</h6>
                </div>
                <div class="card-body">
                    <canvas id="cmrTrendChart" height="80"></canvas>
                </div>
            </div>
        </div>

        <!-- CMR by Status -->
        <div class="col-md-4 mb-3">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="fas fa-chart-pie"></i> CMR by Status</h6>
                </div>
                <div class="card-body">
                    <div style="position: relative; height: 200px;">
                        <canvas id="cmrStatusChart"></canvas>
                    </div>
                    <div class="mt-3">
                        @foreach($cmrByStatus as $status => $count)
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="badge bg-{{ $status == 'completed' ? 'success' : ($status == 'in_progress' ? 'primary' : ($status == 'pending' ? 'warning' : 'secondary')) }}">
                                {{ ucfirst(str_replace('_', ' ', $status)) }}
                            </span>
                            <strong class="small">{{ $count }}</strong>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent CMR Tickets -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="fas fa-clipboard-list"></i> Recent CMR Tickets</h6>
                </div>
                <div class="card-body p-0">
                    @if($recentCmr->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-sm table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Ticket</th>
                                    <th>Equipment</th>
                                    <th>Priority</th>
                                    <th>Status</th>
                                    <th>Technicians</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentCmr as $cmr)
                                <tr>
                                    <td><strong>{{ $cmr->ticket_number }}</strong></td>
                                    <td>{{ $cmr->equipment_name ?? '-' }}</td>
                                    <td><span class="badge {{ $cmr->getPriorityBadgeClass() }}">{{ ucfirst($cmr->priority) }}</span></td>
                                    <td><span class="badge {{ $cmr->getStatusBadgeClass() }}">{{ ucfirst(str_replace('_', ' ', $cmr->status)) }}</span></td>
                                    <td>{{ $cmr->technician_names }}</td>
                                    <td><small>{{ $cmr->created_at->diffForHumans() }}</small></td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="text-center py-4">
                        <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                        <p class="text-muted mb-0">No recent tickets</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script>
    // CMR Trend Chart
    const trendCtx = document.getElementById('cmrTrendChart');
    if (trendCtx) {
        new Chart(trendCtx, {
            type: 'line',
            data: {
                labels: {!! json_encode(array_column($last7DaysTrend, 'date')) !!},
                datasets: [{
                    label: 'Created',
                    data: {!! json_encode(array_column($last7DaysTrend, 'created')) !!},
                    borderColor: 'rgb(75, 192, 192)',
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    tension: 0.4,
                    fill: true
                }, {
                    label: 'Completed',
                    data: {!! json_encode(array_column($last7DaysTrend, 'completed')) !!},
                    borderColor: 'rgb(54, 162, 235)',
                    backgroundColor: 'rgba(54, 162, 235, 0.2)',
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                plugins: { legend: { position: 'top' } },
                scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } }
            }
        });
    }

    // CMR Status Doughnut
    const statusCtx = document.getElementById('cmrStatusChart');
    if (statusCtx) {
        new Chart(statusCtx, {
            type: 'doughnut',
            data: {
                labels: {!! json_encode(array_map(fn($s) => ucfirst(str_replace('_', ' ', $s)), array_keys($cmrByStatus->toArray()))) !!},
                datasets: [{
                    data: {!! json_encode(array_values($cmrByStatus->toArray())) !!},
                    backgroundColor: [
                        'rgba(40, 167, 69, 0.8)',
                        'rgba(0, 123, 255, 0.8)',
                        'rgba(255, 193, 7, 0.8)',
                        'rgba(108, 117, 125, 0.8)',
                        'rgba(220, 53, 69, 0.8)'
                    ],
                    borderWidth: 2,
                    borderColor: '#fff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } }
            }
        });
    }

    @if(auth()->user()->hasRole('supervisor_maintenance'))
    // Calendar functionality for supervisor
    let currentMonth = {{ now()->month }};
    let currentYear = {{ now()->year }};
    const userId = {{ auth()->id() }};

    // Initial calendar data from server
    let calendarDataCache = @json($calendarData);

    // Load calendar on page load
    document.addEventListener('DOMContentLoaded', function() {
        renderCalendar();
    });

    function changeMonth(direction) {
        currentMonth += direction;

        if (currentMonth > 12) {
            currentMonth = 1;
            currentYear++;
        } else if (currentMonth < 1) {
            currentMonth = 12;
            currentYear--;
        }

        // Update display
        const monthNames = ['January', 'February', 'March', 'April', 'May', 'June',
                            'July', 'August', 'September', 'October', 'November', 'December'];
        document.getElementById('currentMonthDisplay').textContent = `${monthNames[currentMonth - 1]} ${currentYear}`;

        // Re-render calendar with cached data (no API call needed)
        renderCalendar();
    }

    function renderCalendar() {
        const grid = document.getElementById('calendarGrid');
        grid.innerHTML = '';

        // Add day headers
        const dayHeaders = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
        dayHeaders.forEach(day => {
            const header = document.createElement('div');
            header.className = 'calendar-day-header';
            header.textContent = day;
            grid.appendChild(header);
        });

        // Calculate calendar dates
        const firstDay = new Date(currentYear, currentMonth - 1, 1);
        const lastDay = new Date(currentYear, currentMonth, 0);
        const daysInMonth = lastDay.getDate();
        const startingDayOfWeek = firstDay.getDay(); // 0 = Sunday

        // Add days from previous month
        const prevMonth = currentMonth === 1 ? 12 : currentMonth - 1;
        const prevYear = currentMonth === 1 ? currentYear - 1 : currentYear;
        const daysInPrevMonth = new Date(prevYear, prevMonth, 0).getDate();

        for (let i = startingDayOfWeek - 1; i >= 0; i--) {
            const day = daysInPrevMonth - i;
            const dateStr = formatDate(prevYear, prevMonth, day);
            addDayCell(day, dateStr, false, false);
        }

        // Add days of current month
        const today = new Date();
        for (let day = 1; day <= daysInMonth; day++) {
            const dateStr = formatDate(currentYear, currentMonth, day);
            const isToday = (day === today.getDate() && currentMonth === (today.getMonth() + 1) && currentYear === today.getFullYear());
            addDayCell(day, dateStr, true, isToday);
        }

        // Add days from next month to complete the grid
        const totalCells = grid.children.length - 7; // Minus day headers
        const remainingCells = (Math.ceil(totalCells / 7) * 7) - totalCells;
        const nextMonth = currentMonth === 12 ? 1 : currentMonth + 1;
        const nextYear = currentMonth === 12 ? currentYear + 1 : currentYear;

        for (let day = 1; day <= remainingCells; day++) {
            const dateStr = formatDate(nextYear, nextMonth, day);
            addDayCell(day, dateStr, false, false);
        }
    }

    function addDayCell(dayNumber, dateStr, isCurrentMonth, isToday) {
        const dayData = calendarDataCache[dateStr] || { shift: null, tasks: [] };

        const cell = document.createElement('div');
        cell.className = 'calendar-day';
        if (!isCurrentMonth) cell.classList.add('other-month');
        if (isToday) cell.classList.add('today');

        // Shift indicator (triangle)
        if (dayData.shift) {
            const shiftIndicator = document.createElement('div');
            shiftIndicator.className = `shift-indicator shift-${dayData.shift}`;
            cell.appendChild(shiftIndicator);
        }

        // Day number
        const dayNum = document.createElement('div');
        dayNum.className = 'day-number';
        dayNum.textContent = dayNumber;
        cell.appendChild(dayNum);

        // Task indicators (dots)
        if (dayData.tasks && dayData.tasks.length > 0) {
            const indicators = document.createElement('div');
            indicators.className = 'task-indicators';

            dayData.tasks.forEach(taskType => {
                const dot = document.createElement('div');
                dot.className = `task-dot ${taskType}`;
                indicators.appendChild(dot);
            });

            cell.appendChild(indicators);

            // Create tooltip with task counts
            const tooltip = createTooltip(dayData.tasks);
            cell.appendChild(tooltip);
        }

        document.getElementById('calendarGrid').appendChild(cell);
    }

    function createTooltip(tasks) {
        const tooltip = document.createElement('div');
        tooltip.className = 'day-tooltip';

        // Count each task type
        const taskCounts = {
            cm: tasks.filter(t => t === 'cm').length,
            stock: tasks.filter(t => t === 'stock').length,
            pm: tasks.filter(t => t === 'pm').length
        };

        let tooltipContent = '';

        if (taskCounts.cm > 0) {
            tooltipContent += `
                <div class="tooltip-row">
                    <span class="tooltip-dot" style="background: #ffc107;"></span>
                    <span>CM: ${taskCounts.cm}</span>
                </div>
            `;
        }

        if (taskCounts.stock > 0) {
            tooltipContent += `
                <div class="tooltip-row">
                    <span class="tooltip-dot" style="background: #0d6efd;"></span>
                    <span>Stock: ${taskCounts.stock}</span>
                </div>
            `;
        }

        if (taskCounts.pm > 0) {
            tooltipContent += `
                <div class="tooltip-row">
                    <span class="tooltip-dot" style="background: #28a745;"></span>
                    <span>PM: ${taskCounts.pm}</span>
                </div>
            `;
        }

        tooltip.innerHTML = tooltipContent;
        return tooltip;
    }

    function formatDate(year, month, day) {
        return `${year}-${String(month).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
    }
    @endif

    @if(!auth()->user()->hasRole('supervisor_maintenance'))
    // ========== KPI Monitor ==========
    let pmKpiChart = null;
    let cmKpiChart = null;
    let soAccuracyChart = null;
    let currentAccuracyPercent = 0;

    document.querySelectorAll('input[name="kpiTimeframe"]').forEach(radio => {
        radio.addEventListener('change', function() {
            const period = this.value;
            const customRange = document.getElementById('kpiCustomRange');
            if (period === 'custom') {
                customRange.classList.add('show');
            } else {
                customRange.classList.remove('show');
                loadKpiData(period);
            }
        });
    });

    function loadKpiData(period) {
        const loading = document.getElementById('kpiLoading');
        loading.style.display = 'flex';

        let url = `{{ route('admin.dashboard.kpi-data') }}?period=${period}`;
        if (period === 'custom') {
            const dateFrom = document.getElementById('kpiDateFrom').value;
            const dateTo = document.getElementById('kpiDateTo').value;
            if (!dateFrom || !dateTo) {
                alert('Please select both start and end dates.');
                loading.style.display = 'none';
                return;
            }
            url += `&date_from=${dateFrom}&date_to=${dateTo}`;
        }

        fetch(url, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        })
        .then(res => res.json())
        .then(data => {
            updatePmKpi(data.pm);
            updateCmKpi(data.cm);
            updateStockOpnameKpi(data.stock_opname);
            document.getElementById('kpiDateRange').textContent = `${data.date_from} ~ ${data.date_to}`;
            loading.style.display = 'none';
        })
        .catch(err => {
            console.error('KPI load error:', err);
            loading.style.display = 'none';
        });
    }

    function updatePmKpi(pm) {
        document.getElementById('pmOnTime').textContent = pm.on_time;
        document.getElementById('pmLate').textContent = pm.late;
        document.getElementById('pmNotDone').textContent = pm.not_done;

        if (pmKpiChart) pmKpiChart.destroy();
        const ctx = document.getElementById('pmKpiChart');
        pmKpiChart = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['On Time', 'Late', 'Not Done'],
                datasets: [{
                    data: [pm.on_time, pm.late, pm.not_done],
                    backgroundColor: ['rgba(40,167,69,0.8)', 'rgba(255,193,7,0.8)', 'rgba(220,53,69,0.8)'],
                    borderWidth: 2, borderColor: '#fff'
                }]
            },
            options: {
                responsive: true, maintainAspectRatio: false, cutout: '60%',
                plugins: { legend: { position: 'bottom', labels: { font: { size: 11 }, padding: 12 } } }
            }
        });
    }

    function updateCmKpi(cm) {
        document.getElementById('cmOpen').textContent = cm.open;
        document.getElementById('cmClosed').textContent = cm.closed;

        if (cmKpiChart) cmKpiChart.destroy();
        const ctx = document.getElementById('cmKpiChart');
        cmKpiChart = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Open', 'Closed'],
                datasets: [{
                    data: [cm.open, cm.closed],
                    backgroundColor: ['rgba(255,193,7,0.8)', 'rgba(40,167,69,0.8)'],
                    borderWidth: 2, borderColor: '#fff'
                }]
            },
            options: {
                responsive: true, maintainAspectRatio: false, cutout: '60%',
                plugins: { legend: { position: 'bottom', labels: { font: { size: 11 }, padding: 12 } } }
            }
        });
    }

    function updateStockOpnameKpi(so) {
        document.getElementById('soAccurate').textContent = so.accuracy.accurate;
        document.getElementById('soDiscrepancy').textContent = so.accuracy.discrepancy;
        document.getElementById('soMissed').textContent = so.missed_jobs;
        document.getElementById('soUncovered').textContent = so.uncovered.total;
        document.getElementById('soUncoveredBreakdown').textContent =
            `(${so.uncovered.spareparts} spareparts, ${so.uncovered.tools} tools, ${so.uncovered.assets} assets)`;

        currentAccuracyPercent = so.accuracy.percent;

        if (soAccuracyChart) soAccuracyChart.destroy();
        const ctx = document.getElementById('soAccuracyChart');
        soAccuracyChart = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Accurate', 'Discrepancy'],
                datasets: [{
                    data: [so.accuracy.accurate, so.accuracy.discrepancy],
                    backgroundColor: ['rgba(40,167,69,0.8)', 'rgba(220,53,69,0.8)'],
                    borderWidth: 2, borderColor: '#fff'
                }]
            },
            options: {
                responsive: true, maintainAspectRatio: false, cutout: '70%',
                plugins: { legend: { display: false } }
            },
            plugins: [{
                id: 'centerText',
                beforeDraw: function(chart) {
                    const { ctx, width, height } = chart;
                    ctx.restore();
                    const fontSize = (height / 80).toFixed(2);
                    ctx.font = `bold ${fontSize}em sans-serif`;
                    ctx.textBaseline = 'middle';
                    ctx.textAlign = 'center';
                    const text = currentAccuracyPercent + '%';
                    ctx.fillStyle = currentAccuracyPercent >= 90 ? '#28a745' :
                                    currentAccuracyPercent >= 70 ? '#ffc107' : '#dc3545';
                    ctx.fillText(text, width / 2, height / 2);
                    ctx.save();
                }
            }]
        });
    }

    // Load KPI data on page load
    document.addEventListener('DOMContentLoaded', function() {
        loadKpiData('1M');
    });
    @endif
</script>
@endsection
