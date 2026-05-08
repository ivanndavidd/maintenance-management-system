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
        padding: 10px 8px;
        border-radius: 10px;
        transition: all 0.2s;
    }
    .kpi-metric-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }
    .kpi-metric-value { font-size: 24px; font-weight: 800; line-height: 1.2; }
    .kpi-metric-label {
        font-size: 11px; font-weight: 600; text-transform: uppercase;
        letter-spacing: 0.5px; margin-top: 2px; color: #6c757d;
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
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h5 class="mb-1 fw-bold">{{ auth()->user()->hasRole('supervisor_maintenance') ? 'Supervisor Dashboard' : 'Admin Dashboard' }}</h5>
            <p class="text-muted mb-0" style="font-size:13px;">Welcome back, <strong>{{ auth()->user()->name }}</strong>!</p>
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
                                                <div class="kpi-metric-value text-warning" id="cmOpen" style="font-size:22px;">-</div>
                                                <div class="kpi-metric-label">Open</div>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="kpi-metric-card bg-success bg-opacity-10">
                                                <div class="kpi-metric-value text-success" id="cmClosed" style="font-size:22px;">-</div>
                                                <div class="kpi-metric-label">Closed</div>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="kpi-metric-card bg-danger bg-opacity-10">
                                                <div class="kpi-metric-value text-danger" id="cmFurtherRepair" style="font-size:22px;">-</div>
                                                <div class="kpi-metric-label" style="font-size:10px;">Further Repair</div>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="kpi-metric-card bg-secondary bg-opacity-10">
                                                <div class="kpi-metric-value text-secondary" id="cmCancelled" style="font-size:22px;">-</div>
                                                <div class="kpi-metric-label">Cancelled</div>
                                            </div>
                                        </div>
                                    </div>
                                    <div style="height: 130px; position: relative;">
                                        <canvas id="cmKpiChart"></canvas>
                                    </div>
                                    <hr class="my-2">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <small class="text-muted fw-semibold">Severity Breakdown</small>
                                    </div>
                                    <div class="row g-1" id="cmSeverityRow">
                                        <div class="col-4 text-center">
                                            <div class="rounded p-1" style="background:#fff0f0;">
                                                <div class="fw-bold text-danger" id="cmSevCritical" style="font-size:16px;">-</div>
                                                <div style="font-size:9px;color:#dc3545;">Critical</div>
                                            </div>
                                        </div>
                                        <div class="col-4 text-center">
                                            <div class="rounded p-1" style="background:#fffbe6;">
                                                <div class="fw-bold" id="cmSevMedium" style="font-size:16px;color:#ffc107;">-</div>
                                                <div style="font-size:9px;color:#ffc107;">Medium</div>
                                            </div>
                                        </div>
                                        <div class="col-4 text-center">
                                            <div class="rounded p-1" style="background:#f0fff4;">
                                                <div class="fw-bold text-success" id="cmSevLow" style="font-size:16px;">-</div>
                                                <div style="font-size:9px;color:#198754;">Minor</div>
                                            </div>
                                        </div>
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
                            <h5 class="mb-0 fw-bold">{{ $stats['total_cmr'] }}</h5>
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
                            <h5 class="mb-0 fw-bold">{{ $stats['total_pm'] }}</h5>
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
                            <h5 class="mb-0 fw-bold">{{ $stats['total_operators'] }}</h5>
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
                            <h5 class="mb-0 fw-bold">{{ number_format($avgResolutionTime, 1) }}h</h5>
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

    <!-- Maintenance Performance Metrics -->
    @if(auth()->user()->hasRole('admin'))
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-white d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div>
                        <h6 class="mb-0"><i class="fas fa-tachometer-alt text-primary"></i> Maintenance Performance Metrics</h6>
                        <small class="text-muted" id="metricsDateRange"></small>
                    </div>
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <div class="btn-group btn-group-sm">
                            <input type="radio" class="btn-check" name="metricsTimeframe" id="metrics1M" value="1M" checked>
                            <label class="btn btn-outline-primary" for="metrics1M">1M</label>
                            <input type="radio" class="btn-check" name="metricsTimeframe" id="metrics3M" value="3M">
                            <label class="btn btn-outline-primary" for="metrics3M">3M</label>
                            <input type="radio" class="btn-check" name="metricsTimeframe" id="metrics6M" value="6M">
                            <label class="btn btn-outline-primary" for="metrics6M">6M</label>
                            <input type="radio" class="btn-check" name="metricsTimeframe" id="metrics1Y" value="1Y">
                            <label class="btn btn-outline-primary" for="metrics1Y">1Y</label>
                            <input type="radio" class="btn-check" name="metricsTimeframe" id="metricsCustom" value="custom">
                            <label class="btn btn-outline-primary" for="metricsCustom"><i class="fas fa-calendar-alt"></i></label>
                        </div>
                        <div id="metricsCustomRange" style="display:none;" class="d-flex align-items-center gap-1">
                            <input type="date" class="form-control form-control-sm" id="metricsDateFrom" style="max-width:140px;">
                            <span>-</span>
                            <input type="date" class="form-control form-control-sm" id="metricsDateTo" style="max-width:140px;">
                            <button class="btn btn-primary btn-sm" onclick="loadMetrics()">Go</button>
                        </div>
                    </div>
                </div>
                <div class="card-body" id="metricsBody">

                    {{-- Summary Cards Row --}}
                    <div class="row g-2 mb-4">
                        <div class="col-6 col-md-3">
                            <div class="card border-0 h-100" style="background:#f0f9ff;">
                                <div class="card-body py-3 px-3">
                                    <div class="d-flex align-items-center gap-2 mb-1">
                                        <i class="fas fa-clock text-primary"></i>
                                        <small class="text-muted fw-semibold">MTBF</small>
                                    </div>
                                    <div class="fw-bold fs-5 text-primary" id="mtbfOverall">-</div>
                                    <small class="text-muted">hours avg between failures</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="card border-0 h-100" style="background:#fff7f0;">
                                <div class="card-body py-3 px-3">
                                    <div class="d-flex align-items-center gap-2 mb-1">
                                        <i class="fas fa-tools text-warning"></i>
                                        <small class="text-muted fw-semibold">MTTR</small>
                                    </div>
                                    <div class="fw-bold fs-5 text-warning" id="mttrOverall">-</div>
                                    <small class="text-muted">hours avg to repair (<span id="mttrCount">-</span> tickets)</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="card border-0 h-100" style="background:#f0fff4;">
                                <div class="card-body py-3 px-3">
                                    <div class="d-flex align-items-center gap-2 mb-1">
                                        <i class="fas fa-check-circle text-success"></i>
                                        <small class="text-muted fw-semibold">Availability</small>
                                    </div>
                                    <div class="fw-bold fs-5 text-success" id="metricsAvailability">-</div>
                                    <small class="text-muted">uptime estimate</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="card border-0 h-100" style="background:#fff0f0;">
                                <div class="card-body py-3 px-3">
                                    <div class="d-flex align-items-center gap-2 mb-1">
                                        <i class="fas fa-exclamation-triangle text-danger"></i>
                                        <small class="text-muted fw-semibold">Total Failures</small>
                                    </div>
                                    <div class="fw-bold fs-5 text-danger" id="metricsFailures">-</div>
                                    <small class="text-muted">CM tickets in period</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- MTBF & MTTR Trend + MTBF by Group --}}
                    <div class="row g-3 mb-3">
                        <div class="col-12 col-md-8">
                            <div class="card border-0 bg-light">
                                <div class="card-body">
                                    <h6 class="fw-bold mb-3">MTBF & MTTR Trend</h6>
                                    <div style="position:relative; height:220px;">
                                        <canvas id="mtbfMttrTrendChart"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-md-4">
                            <div class="card border-0 bg-light h-100">
                                <div class="card-body">
                                    <h6 class="fw-bold mb-3">MTBF by Group Asset <small class="text-muted fw-normal">(hours)</small></h6>
                                    <div style="position:relative; height:220px;">
                                        <canvas id="mtbfGroupChart"></canvas>
                                    </div>
                                    <small class="text-muted d-block text-center mt-1"><i class="fas fa-info-circle me-1"></i>Higher = more reliable</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- MTTR by Group + Failure Pareto --}}
                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <div class="card border-0 bg-light">
                                <div class="card-body">
                                    <h6 class="fw-bold mb-3">MTTR by Group Asset <small class="text-muted fw-normal">(hours)</small></h6>
                                    <div style="position:relative; height:200px;">
                                        <canvas id="mttrGroupChart"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="card border-0 bg-light">
                                <div class="card-body">
                                    <h6 class="fw-bold mb-3">Failure by Problem Category</h6>
                                    <div style="position:relative; height:200px;">
                                        <canvas id="failureParetoChart"></canvas>
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
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script>
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
        document.getElementById('cmFurtherRepair').textContent = cm.further_repair ?? 0;
        document.getElementById('cmCancelled').textContent = cm.cancelled ?? 0;

        if (cm.severity) {
            document.getElementById('cmSevCritical').textContent = cm.severity.critical ?? 0;
            document.getElementById('cmSevMedium').textContent   = cm.severity.medium   ?? 0;
            document.getElementById('cmSevLow').textContent      = cm.severity.minor    ?? 0;
        }

        if (cmKpiChart) cmKpiChart.destroy();
        const ctx = document.getElementById('cmKpiChart');
        cmKpiChart = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Open', 'Closed', 'Further Repair', 'Cancelled'],
                datasets: [{
                    data: [cm.open, cm.closed, cm.further_repair ?? 0, cm.cancelled ?? 0],
                    backgroundColor: [
                        'rgba(255,193,7,0.8)',
                        'rgba(40,167,69,0.8)',
                        'rgba(220,53,69,0.8)',
                        'rgba(108,117,125,0.8)'
                    ],
                    borderWidth: 2, borderColor: '#fff'
                }]
            },
            options: {
                responsive: true, maintainAspectRatio: false, cutout: '60%',
                plugins: { legend: { position: 'bottom', labels: { font: { size: 10 }, padding: 8 } } }
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

    @if(auth()->user()->hasRole('admin'))
    // ========== Maintenance Performance Metrics ==========
    let chartTrend = null, chartMtbfGroup = null, chartMttrGroup = null, chartCategory = null;

    document.querySelectorAll('input[name="metricsTimeframe"]').forEach(radio => {
        radio.addEventListener('change', function() {
            document.getElementById('metricsCustomRange').style.display =
                this.value === 'custom' ? 'flex' : 'none';
            if (this.value !== 'custom') loadMetrics();
        });
    });

    function loadMetrics() {
        const period = document.querySelector('input[name="metricsTimeframe"]:checked').value;
        let url = '{{ route("admin.dashboard.maintenance-metrics") }}?period=' + period;
        if (period === 'custom') {
            const from = document.getElementById('metricsDateFrom').value;
            const to   = document.getElementById('metricsDateTo').value;
            if (!from || !to) return;
            url += '&date_from=' + from + '&date_to=' + to;
        }

        fetch(url)
            .then(r => r.json())
            .then(data => {
                document.getElementById('metricsDateRange').textContent = data.date_from + ' — ' + data.date_to;

                // Summary cards
                document.getElementById('mtbfOverall').textContent     = data.mtbf.overall_hours + 'h';
                document.getElementById('mttrOverall').textContent     = data.mttr.overall_hours + 'h';
                document.getElementById('mttrCount').textContent       = data.mttr.overall_count;
                document.getElementById('metricsAvailability').textContent = data.availability + '%';
                document.getElementById('metricsFailures').textContent = data.total_failures;

                renderTrendChart(data.trend);
                renderMtbfGroupChart(data.mtbf.by_group);
                renderMttrGroupChart(data.mttr.by_group);
                renderCategoryChart(data.by_category);
            })
            .catch(err => console.error('Metrics load error:', err));
    }

    function renderTrendChart(trend) {
        if (chartTrend) chartTrend.destroy();
        const ctx = document.getElementById('mtbfMttrTrendChart');
        if (!ctx || !trend.length) return;

        const labels   = trend.map(d => d.label);
        const mttrVals = trend.map(d => d.mttr);
        const failVals = trend.map(d => d.failures);

        chartTrend = new Chart(ctx, {
            type: 'line',
            data: {
                labels,
                datasets: [
                    {
                        label: 'MTTR (hours)',
                        data: mttrVals,
                        borderColor: '#fd7e14',
                        backgroundColor: 'rgba(253,126,20,0.1)',
                        tension: 0.4, fill: true,
                        yAxisID: 'yMttr',
                        spanGaps: true,
                    },
                    {
                        label: 'Failures',
                        data: failVals,
                        borderColor: '#dc3545',
                        backgroundColor: 'rgba(220,53,69,0.15)',
                        tension: 0.3, fill: false,
                        yAxisID: 'yFail',
                        borderDash: [4, 3],
                    }
                ]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                plugins: { legend: { position: 'top', labels: { font: { size: 11 }, padding: 10 } } },
                scales: {
                    yMttr: {
                        type: 'linear', position: 'left', beginAtZero: true,
                        title: { display: true, text: 'MTTR (h)', font: { size: 10 } },
                        ticks: { font: { size: 10 } }
                    },
                    yFail: {
                        type: 'linear', position: 'right', beginAtZero: true,
                        title: { display: true, text: 'Failures', font: { size: 10 } },
                        grid: { drawOnChartArea: false },
                        ticks: { font: { size: 10 }, stepSize: 1 }
                    },
                    x: { ticks: { font: { size: 10 }, maxRotation: 45, minRotation: 0 } }
                }
            }
        });
    }

    function renderMtbfGroupChart(byGroup) {
        if (chartMtbfGroup) chartMtbfGroup.destroy();
        const ctx = document.getElementById('mtbfGroupChart');
        if (!ctx) return;

        if (!byGroup.length) {
            ctx.getContext('2d').clearRect(0, 0, ctx.width, ctx.height);
            return;
        }

        const labels = byGroup.map(g => g.group);
        const values = byGroup.map(g => g.avg_hours);

        chartMtbfGroup = new Chart(ctx, {
            type: 'bar',
            data: {
                labels,
                datasets: [{
                    label: 'MTBF (hours)',
                    data: values,
                    backgroundColor: values.map(v =>
                        v >= 100 ? 'rgba(25,135,84,0.75)' :
                        v >= 48  ? 'rgba(255,193,7,0.75)' : 'rgba(220,53,69,0.75)'
                    ),
                    borderRadius: 4,
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    x: { beginAtZero: true, title: { display: true, text: 'Hours', font: { size: 10 } }, ticks: { font: { size: 10 } } },
                    y: { ticks: { font: { size: 10 } } }
                }
            }
        });
    }

    function renderMttrGroupChart(byGroup) {
        if (chartMttrGroup) chartMttrGroup.destroy();
        const ctx = document.getElementById('mttrGroupChart');
        if (!ctx) return;

        if (!byGroup.length) return;

        const sorted = [...byGroup].sort((a, b) => b.avg_hours - a.avg_hours);
        const labels = sorted.map(g => g.group);
        const values = sorted.map(g => g.avg_hours);
        const counts = sorted.map(g => g.ticket_count);

        chartMttrGroup = new Chart(ctx, {
            type: 'bar',
            data: {
                labels,
                datasets: [{
                    label: 'Avg MTTR (hours)',
                    data: values,
                    backgroundColor: values.map(v =>
                        v > 8 ? 'rgba(220,53,69,0.75)' :
                        v > 4 ? 'rgba(255,193,7,0.75)' : 'rgba(25,135,84,0.75)'
                    ),
                    borderRadius: 4,
                }]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            afterLabel: function(ctx) {
                                return `Tickets: ${counts[ctx.dataIndex]}`;
                            }
                        }
                    }
                },
                scales: {
                    y: { beginAtZero: true, title: { display: true, text: 'Hours', font: { size: 10 } }, ticks: { font: { size: 10 } } },
                    x: { ticks: { font: { size: 10 }, maxRotation: 30 } }
                }
            }
        });
    }

    function renderCategoryChart(byCategory) {
        if (chartCategory) chartCategory.destroy();
        const ctx = document.getElementById('failureParetoChart');
        if (!ctx) return;

        if (!byCategory.length) return;

        const categoryLabels = {
            conveyor_totebox: 'Conveyor Totebox',
            conveyor_paket: 'Conveyor Paket',
            lift_merah: 'Lift Merah',
            lift_kuning: 'Lift Kuning',
            chute: 'Chute',
            others: 'Others',
        };

        const labels = byCategory.map(d => categoryLabels[d.category] ?? d.category);
        const counts = byCategory.map(d => d.count);
        const total  = counts.reduce((a, b) => a + b, 0);

        // Cumulative %
        let cumulative = 0;
        const cumulativePct = counts.map(c => {
            cumulative += c;
            return total > 0 ? parseFloat((cumulative / total * 100).toFixed(1)) : 0;
        });

        chartCategory = new Chart(ctx, {
            type: 'bar',
            data: {
                labels,
                datasets: [
                    {
                        type: 'bar',
                        label: 'Failures',
                        data: counts,
                        backgroundColor: 'rgba(13,110,253,0.7)',
                        borderRadius: 4,
                        yAxisID: 'yCount',
                    },
                    {
                        type: 'line',
                        label: 'Cumulative %',
                        data: cumulativePct,
                        borderColor: '#dc3545',
                        backgroundColor: 'transparent',
                        tension: 0.3,
                        pointRadius: 4,
                        yAxisID: 'yPct',
                    }
                ]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: { position: 'top', labels: { font: { size: 11 }, padding: 10 } } },
                scales: {
                    yCount: {
                        type: 'linear', position: 'left', beginAtZero: true,
                        title: { display: true, text: 'Count', font: { size: 10 } },
                        ticks: { font: { size: 10 }, stepSize: 1 }
                    },
                    yPct: {
                        type: 'linear', position: 'right', min: 0, max: 100,
                        title: { display: true, text: '%', font: { size: 10 } },
                        grid: { drawOnChartArea: false },
                        ticks: { font: { size: 10 }, callback: v => v + '%' }
                    },
                    x: { ticks: { font: { size: 10 }, maxRotation: 30 } }
                }
            }
        });
    }

    document.addEventListener('DOMContentLoaded', function() {
        loadMetrics();
    });
    @endif
</script>
@endsection
