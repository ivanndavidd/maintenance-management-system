@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <!-- ========== PAGE HEADER ========== -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2>📊 Admin Dashboard</h2>
            <p class="text-muted mb-0">Welcome back, <strong>{{ auth()->user()->name }}</strong>!</p>
        </div>
        <div class="text-end">
            <small class="text-muted d-block">{{ Carbon\Carbon::now()->format('l, F d, Y') }}</small>
            <small class="text-muted">{{ Carbon\Carbon::now()->format('h:i A') }}</small>
        </div>
    </div>

    <!-- ========== INDUSTRIAL MAINTENANCE METRICS (MTBF, MTTR, OEE) ========== -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-info shadow-sm">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="fas fa-chart-line"></i> Industrial Maintenance Metrics</h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <!-- MTBF -->
                        <div class="col-md-2 mb-3 mb-md-0">
                            <div class="border-end pe-3">
                                <h2 class="text-primary mb-0 fw-bold">{{ number_format($metrics['mtbf'], 1) }}</h2>
                                <small class="text-muted">hours</small>
                                <h6 class="mt-2 mb-0 fw-bold text-uppercase">MTBF</h6>
                                <small class="text-muted">Mean Time Between Failures</small>
                            </div>
                        </div>
                        
                        <!-- MTTR -->
                        <div class="col-md-2 mb-3 mb-md-0">
                            <div class="border-end pe-3">
                                <h2 class="text-warning mb-0 fw-bold">{{ number_format($metrics['mttr'], 1) }}</h2>
                                <small class="text-muted">hours</small>
                                <h6 class="mt-2 mb-0 fw-bold text-uppercase">MTTR</h6>
                                <small class="text-muted">Mean Time To Repair</small>
                            </div>
                        </div>
                        
                        <!-- OEE -->
                        <div class="col-md-2 mb-3 mb-md-0">
                            <div class="border-end pe-3">
                                <h2 class="mb-0 fw-bold text-{{ $metrics['oee'] >= 85 ? 'success' : ($metrics['oee'] >= 70 ? 'warning' : 'danger') }}">
                                    {{ number_format($metrics['oee'], 1) }}%
                                </h2>
                                <h6 class="mt-2 mb-0 fw-bold text-uppercase">OEE</h6>
                                <small class="text-muted">Overall Equipment Effectiveness</small>
                                @if($metrics['oee'] >= 85)
                                    <div class="mt-1"><span class="badge bg-success">Excellent</span></div>
                                @elseif($metrics['oee'] >= 70)
                                    <div class="mt-1"><span class="badge bg-warning text-dark">Good</span></div>
                                @else
                                    <div class="mt-1"><span class="badge bg-danger">Needs Improvement</span></div>
                                @endif
                            </div>
                        </div>
                        
                        <!-- Availability -->
                        <div class="col-md-2 mb-3 mb-md-0">
                            <div class="border-end pe-3">
                                <h2 class="text-info mb-0 fw-bold">{{ number_format($metrics['availability'], 1) }}%</h2>
                                <h6 class="mt-2 mb-0 fw-bold text-uppercase">Availability</h6>
                                <small class="text-muted">Machine Uptime</small>
                            </div>
                        </div>
                        
                        <!-- Performance -->
                        <div class="col-md-2 mb-3 mb-md-0">
                            <div class="border-end pe-3">
                                <h2 class="text-success mb-0 fw-bold">{{ number_format($metrics['performance'], 1) }}%</h2>
                                <h6 class="mt-2 mb-0 fw-bold text-uppercase">Performance</h6>
                                <small class="text-muted">Efficiency Rate</small>
                            </div>
                        </div>
                        
                        <!-- Quality -->
                        <div class="col-md-2">
                            <h2 class="text-primary mb-0 fw-bold">{{ number_format($metrics['quality'], 1) }}%</h2>
                            <h6 class="mt-2 mb-0 fw-bold text-uppercase">Quality</h6>
                            <small class="text-muted">Success Rate</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ========== COST TRACKING ========== -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-success shadow-sm">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="fas fa-money-bill-wave"></i> Cost Tracking (This Month)</h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-md-3 mb-3 mb-md-0">
                            <i class="fas fa-box fa-2x text-primary mb-2"></i>
                            <h4 class="text-primary fw-bold mb-0">Rp {{ number_format($costMetrics['parts_cost'], 0, ',', '.') }}</h4>
                            <small class="text-muted">Parts Cost</small>
                        </div>
                        <div class="col-md-3 mb-3 mb-md-0">
                            <i class="fas fa-clock fa-2x text-warning mb-2"></i>
                            <h4 class="text-warning fw-bold mb-0">{{ number_format($costMetrics['labor_hours'], 1) }} hrs</h4>
                            <small class="text-muted">Labor Hours</small>
                        </div>
                        <div class="col-md-3 mb-3 mb-md-0">
                            <i class="fas fa-user-hard-hat fa-2x text-info mb-2"></i>
                            <h4 class="text-info fw-bold mb-0">Rp {{ number_format($costMetrics['labor_cost'], 0, ',', '.') }}</h4>
                            <small class="text-muted">Labor Cost</small>
                        </div>
                        <div class="col-md-3">
                            <i class="fas fa-calculator fa-2x text-success mb-2"></i>
                            <h4 class="text-success fw-bold mb-0">Rp {{ number_format($costMetrics['total_cost'], 0, ',', '.') }}</h4>
                            <small class="text-muted">Total Maintenance Cost</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ========== MTBF & MTTR TREND (LAST 30 DAYS) ========== -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-chart-area"></i> MTBF & MTTR Trend (Last 30 Days)
                    </h5>
                </div>
                <div class="card-body">
                    <canvas id="metricsTrendChart" height="80"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- ========== STATISTICS CARDS ========== -->
    <div class="row mb-4">
        <!-- Total Jobs Card -->
        <div class="col-md-3 mb-3">
            <div class="card border-primary h-100 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2">Total Jobs</h6>
                            <h3 class="mb-0 fw-bold">{{ $stats['total_jobs'] }}</h3>
                            <small class="text-muted">
                                <span class="text-warning">⏳ {{ $stats['pending_jobs'] }}</span> | 
                                <span class="text-primary">⚙️ {{ $stats['in_progress_jobs'] }}</span> | 
                                <span class="text-success">✓ {{ $stats['completed_jobs'] }}</span>
                            </small>
                        </div>
                        <div class="text-primary opacity-25">
                            <i class="fas fa-tasks fa-3x"></i>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-primary text-white">
                    <a href="{{ route('admin.jobs.index') }}" class="text-white text-decoration-none">
                        View All Jobs <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>
        </div>

        <!-- Machines Card -->
        <div class="col-md-3 mb-3">
            <div class="card border-success h-100 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2">Machines</h6>
                            <h3 class="mb-0 fw-bold">{{ $stats['total_machines'] }}</h3>
                            <small class="text-success fw-bold">
                                ✓ {{ $stats['operational_machines'] }} Operational
                            </small>
                            <br>
                            <small class="text-danger fw-bold">
                                ✗ {{ $stats['breakdown_machines'] }} Breakdown
                            </small>
                        </div>
                        <div class="text-success opacity-25">
                            <i class="fas fa-cogs fa-3x"></i>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-success text-white">
                    <a href="{{ route('admin.machines.index') }}" class="text-white text-decoration-none">
                        View All Machines <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>
        </div>

        <!-- Parts Inventory Card -->
        <div class="col-md-3 mb-3">
            <div class="card border-warning h-100 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2">Parts Inventory</h6>
                            <h3 class="mb-0 fw-bold">{{ $stats['total_parts'] }}</h3>
                            <small class="text-warning fw-bold">
                                ⚠ {{ $stats['low_stock_parts'] }} Low Stock
                            </small>
                            <br>
                            <small class="text-muted">
                                Value: Rp {{ number_format($stats['total_parts_value'], 0, ',', '.') }}
                            </small>
                        </div>
                        <div class="text-warning opacity-25">
                            <i class="fas fa-box fa-3x"></i>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-warning text-dark">
                    <a href="{{ route('admin.parts.index') }}" class="text-dark text-decoration-none">
                        View Inventory <i class="fas fa-arrow-right"></i>
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
                            <small class="text-success">
                                {{ $stats['active_operators'] }} Active
                            </small>
                            <br>
                            <small class="text-muted">
                                Reports Today: {{ $stats['total_reports_today'] }}
                            </small>
                        </div>
                        <div class="text-info opacity-25">
                            <i class="fas fa-users fa-3x"></i>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-info text-white">
                    <a href="{{ route('admin.users.index') }}" class="text-white text-decoration-none">
                        View All Users <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- ========== URGENT ALERTS (COMPACT VERSION) ========== -->
    @if($urgentMachines->count() > 0 || $urgentJobs->count() > 0)
    <div class="row mb-4">
        <!-- Urgent Machines -->
        @if($urgentMachines->count() > 0)
        <div class="col-md-4 mb-3">
            <div class="card border-danger shadow-sm">
                <div class="card-header bg-danger text-white py-2">
                    <h6 class="mb-0 small"><i class="fas fa-exclamation-triangle"></i> Urgent Machines</h6>
                </div>
                <div class="card-body p-0" style="max-height: 250px; overflow-y: auto;">
                    <div class="list-group list-group-flush">
                        @foreach($urgentMachines->take(3) as $machine)
                        <div class="list-group-item py-2">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <strong class="small">{{ $machine->name }}</strong>
                                    <br>
                                    <small class="text-muted">{{ $machine->code }}</small>
                                </div>
                                <span class="badge bg-{{ $machine->status == 'breakdown' ? 'danger' : 'warning' }} small">
                                    {{ ucfirst($machine->status) }}
                                </span>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                <div class="card-footer py-2">
                    <a href="{{ route('admin.machines.index') }}" class="text-decoration-none small">
                        View All <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>
        </div>
        @endif

        <!-- High Priority Jobs -->
        @if($urgentJobs->count() > 0)
        <div class="col-md-4 mb-3">
            <div class="card border-warning shadow-sm">
                <div class="card-header bg-warning text-dark py-2">
                    <h6 class="mb-0 small"><i class="fas fa-fire"></i> High Priority Jobs</h6>
                </div>
                <div class="card-body p-0" style="max-height: 250px; overflow-y: auto;">
                    <div class="list-group list-group-flush">
                        @foreach($urgentJobs->take(3) as $job)
                        <div class="list-group-item py-2">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <strong class="small">{{ Str::limit($job->title, 30) }}</strong>
                                    <br>
                                    <small class="text-muted">{{ $job->machine->name ?? 'N/A' }}</small>
                                </div>
                                <span class="badge bg-danger small">HIGH</span>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                <div class="card-footer py-2">
                    <a href="{{ route('admin.jobs.index') }}" class="text-decoration-none small">
                        View All <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>
        </div>
        @endif

        <!-- Jobs by Status - COMPACT -->
        <div class="col-md-4 mb-3">
            <div class="card shadow-sm">
                <div class="card-header bg-white py-2">
                    <h6 class="mb-0 small"><i class="fas fa-chart-pie"></i> Jobs by Status</h6>
                </div>
                <div class="card-body p-3" style="height: 320px;">
                    <div style="height: 180px; position: relative;">
                        <canvas id="jobsStatusChart"></canvas>
                    </div>
                    <div class="mt-2">
                        @foreach($jobsByStatus as $status => $count)
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="badge bg-{{ $status == 'completed' ? 'success' : ($status == 'in_progress' ? 'primary' : ($status == 'pending' ? 'warning' : 'secondary')) }} small">
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
    @else
    <!-- Jika tidak ada alerts -->
    <div class="row mb-4">
        <div class="col-md-4 mb-3 offset-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-white py-2">
                    <h6 class="mb-0 small"><i class="fas fa-chart-pie"></i> Jobs by Status</h6>
                </div>
                <div class="card-body p-3" style="max-height: 250px;">
                    <canvas id="jobsStatusChart" height="150"></canvas>
                    <div class="mt-2">
                        @foreach($jobsByStatus as $status => $count)
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="badge bg-{{ $status == 'completed' ? 'success' : ($status == 'in_progress' ? 'primary' : ($status == 'pending' ? 'warning' : 'secondary')) }} small">
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
    @endif

    <!-- Low Stock Parts - COMPACT TABLE -->
    @if($lowStockParts->count() > 0)
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card border-warning shadow-sm">
                <div class="card-header bg-warning text-dark py-2">
                    <h6 class="mb-0 small"><i class="fas fa-box-open"></i> Low Stock Alert ({{ $lowStockParts->count() }} items)</h6>
                </div>
                <div class="card-body p-0" style="max-height: 200px; overflow-y: auto;">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover mb-0">
                            <thead class="table-light sticky-top">
                                <tr>
                                    <th class="small">Code</th>
                                    <th class="small">Part Name</th>
                                    <th class="small">Stock</th>
                                    <th class="small">Min</th>
                                    <th class="small">Status</th>
                                    <th class="small">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($lowStockParts->take(5) as $part)
                                <tr>
                                    <td class="small"><strong>{{ $part->code }}</strong></td>
                                    <td class="small">{{ Str::limit($part->name, 30) }}</td>
                                    <td class="small">{{ $part->stock_quantity }} {{ $part->unit }}</td>
                                    <td class="small">{{ $part->minimum_stock }}</td>
                                    <td>
                                        <span class="badge bg-{{ $part->stock_quantity == 0 ? 'danger' : 'warning' }} text-dark small">
                                            {{ $part->stock_quantity == 0 ? 'OUT' : 'LOW' }}
                                        </span>
                                    </td>
                                    <td>
                                        <a href="{{ route('admin.parts.edit', $part) }}" class="btn btn-sm btn-primary py-0">
                                            <small>Restock</small>
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer py-2">
                    <a href="{{ route('admin.parts.index') }}" class="text-decoration-none small">
                        View All Parts <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- ========== JOBS TREND CHART - FULL WIDTH ========== -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="fas fa-chart-line"></i> Jobs Trend (Last 7 Days)</h6>
                </div>
                <div class="card-body">
                    <canvas id="jobsTrendChart" height="80"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- ========== RECENT ACTIVITIES ========== -->
    <div class="row mb-4">
        <!-- Recent Work Reports -->
        <div class="col-md-6 mb-3">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="fas fa-clipboard-list"></i> Recent Work Reports</h6>
                </div>
                <div class="card-body p-0">
                    @if($recentReports->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-sm table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Operator</th>
                                    <th>Machine</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentReports as $report)
                                <tr>
                                    <td>{{ $report->user->name ?? 'N/A' }}</td>
                                    <td>{{ $report->job->machine->name ?? 'N/A' }}</td>
                                    <td>
                                        <span class="badge bg-{{ $report->status == 'completed' ? 'success' : ($report->status == 'in_progress' ? 'primary' : 'warning') }}">
                                            {{ ucfirst($report->status) }}
                                        </span>
                                    </td>
                                    <td>
                                        <small>{{ $report->created_at->diffForHumans() }}</small>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="text-center py-4">
                        <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                        <p class="text-muted mb-0">No recent reports</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Top Performers This Month -->
        <div class="col-md-6 mb-3">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="fas fa-trophy"></i> Top Performers (This Month)</h6>
                </div>
                <div class="card-body p-0">
                    @if($topOperators->count() > 0)
                    <div class="list-group list-group-flush">
                        @foreach($topOperators as $index => $operator)
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <span class="badge bg-{{ $index < 3 ? 'success' : 'secondary' }} me-2">
                                    #{{ $index + 1 }}
                                </span>
                                <strong>{{ $operator->name }}</strong>
                                @if($index === 0)
                                    <i class="fas fa-crown text-warning ms-1"></i>
                                @endif
                            </div>
                            <span class="badge bg-primary rounded-pill">
                                {{ $operator->completed_this_month }} completed
                            </span>
                        </div>
                        @endforeach
                    </div>
                    @else
                    <div class="text-center py-4">
                        <i class="fas fa-user-clock fa-3x text-muted mb-3"></i>
                        <p class="text-muted mb-0">No completed tasks this month</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- ========== PERFORMANCE OVERVIEW ========== -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="fas fa-tachometer-alt"></i> Performance Overview</h6>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-md-3 mb-3 mb-md-0">
                            <i class="fas fa-clock fa-2x text-primary mb-2"></i>
                            <h3 class="text-primary fw-bold">{{ number_format($avgCompletionTime, 1) }}h</h3>
                            <small class="text-muted">Avg Completion Time</small>
                        </div>
                        <div class="col-md-3 mb-3 mb-md-0">
                            <i class="fas fa-check-circle fa-2x text-success mb-2"></i>
                            <h3 class="text-success fw-bold">{{ $stats['completed_jobs'] }}</h3>
                            <small class="text-muted">Total Completed Jobs</small>
                        </div>
                        <div class="col-md-3 mb-3 mb-md-0">
                            <i class="fas fa-clipboard-check fa-2x text-warning mb-2"></i>
                            <h3 class="text-warning fw-bold">{{ $stats['completed_reports_today'] }}</h3>
                            <small class="text-muted">Reports Completed Today</small>
                        </div>
                        <div class="col-md-3">
                            <i class="fas fa-chart-pie fa-2x text-info mb-2"></i>
                            <h3 class="text-info fw-bold">{{ number_format(($stats['operational_machines'] / max($stats['total_machines'], 1)) * 100, 1) }}%</h3>
                            <small class="text-muted">Machine Uptime</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

<!-- ========== CHART.JS SCRIPTS ========== -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script>
    // ===== 1. MTBF & MTTR Trend Chart (Last 30 Days) =====
    const metricsTrendCtx = document.getElementById('metricsTrendChart');
    
    if (metricsTrendCtx) {
        new Chart(metricsTrendCtx, {
            type: 'line',
            data: {
                labels: {!! json_encode(array_column($metricsTrend, 'date')) !!},
                datasets: [{
                    label: 'MTBF (Mean Time Between Failures)',
                    data: {!! json_encode(array_column($metricsTrend, 'mtbf')) !!},
                    borderColor: 'rgb(75, 192, 192)',
                    backgroundColor: 'rgba(75, 192, 192, 0.1)',
                    borderWidth: 3,
                    tension: 0.4,
                    fill: true,
                    yAxisID: 'y',
                    pointRadius: 4,
                    pointHoverRadius: 6
                }, {
                    label: 'MTTR (Mean Time To Repair)',
                    data: {!! json_encode(array_column($metricsTrend, 'mttr')) !!},
                    borderColor: 'rgb(255, 159, 64)',
                    backgroundColor: 'rgba(255, 159, 64, 0.1)',
                    borderWidth: 3,
                    tension: 0.4,
                    fill: true,
                    yAxisID: 'y1',
                    pointRadius: 4,
                    pointHoverRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                interaction: {
                    mode: 'index',
                    intersect: false,
                },
                plugins: {
                    legend: {
                        position: 'top',
                        labels: {
                            font: {
                                size: 14,
                                weight: 'bold'
                            },
                            padding: 20
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                let label = context.dataset.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                label += context.parsed.y.toFixed(1) + ' hours';
                                return label;
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        type: 'linear',
                        display: true,
                        position: 'left',
                        title: {
                            display: true,
                            text: 'MTBF (hours)',
                            font: {
                                size: 14,
                                weight: 'bold'
                            }
                        },
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(75, 192, 192, 0.1)'
                        }
                    },
                    y1: {
                        type: 'linear',
                        display: true,
                        position: 'right',
                        title: {
                            display: true,
                            text: 'MTTR (hours)',
                            font: {
                                size: 14,
                                weight: 'bold'
                            }
                        },
                        beginAtZero: true,
                        grid: {
                            drawOnChartArea: false,
                        },
                    }
                }
            }
        });
    }

    // ===== 2. Jobs Trend Chart (Last 7 Days) =====
    const jobsTrendCtx = document.getElementById('jobsTrendChart');
    
    if (jobsTrendCtx) {
        new Chart(jobsTrendCtx, {
            type: 'line',
            data: {
                labels: {!! json_encode(array_column($last7DaysJobs, 'date')) !!},
                datasets: [{
                    label: 'Created Jobs',
                    data: {!! json_encode(array_column($last7DaysJobs, 'count')) !!},
                    borderColor: 'rgb(75, 192, 192)',
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    tension: 0.4,
                    fill: true
                }, {
                    label: 'Completed Jobs',
                    data: {!! json_encode(array_column($last7DaysJobs, 'completed')) !!},
                    borderColor: 'rgb(54, 162, 235)',
                    backgroundColor: 'rgba(54, 162, 235, 0.2)',
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false,
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        });
    }

        // ===== 3. Jobs by Status Doughnut Chart =====
        const jobsStatusCtx = document.getElementById('jobsStatusChart');

        if (jobsStatusCtx) {
            new Chart(jobsStatusCtx, {
                type: 'doughnut',
                data: {
                    labels: {!! json_encode(array_map(function($status) { 
                        return ucfirst(str_replace('_', ' ', $status)); 
                    }, array_keys($jobsByStatus->toArray()))) !!},
                    datasets: [{
                        data: {!! json_encode(array_values($jobsByStatus->toArray())) !!},
                        backgroundColor: [
                            'rgba(40, 167, 69, 0.8)',   // Completed - Green
                            'rgba(0, 123, 255, 0.8)',   // In Progress - Blue
                            'rgba(255, 193, 7, 0.8)',   // Pending - Yellow
                            'rgba(108, 117, 125, 0.8)'  // Cancelled - Gray
                        ],
                        borderWidth: 2,
                        borderColor: '#fff'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false, // ✅ CHANGED FROM true TO false
                    plugins: {
                        legend: {
                            display: false
                        },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                let label = context.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                label += context.parsed + ' jobs';
                                return label;
                            }
                        }
                    }
                }
            }
        });
    }
</script>

<style>
    .opacity-25 {
        opacity: 0.25;
    }
    
    .card {
        transition: transform 0.2s;
    }
    
    .card:hover {
        transform: translateY(-3px);
    }
    
    .badge {
        font-weight: 600;
    }
    
    /* Compact card styles */
    .small {
        font-size: 0.875rem !important;
    }
    
    .card-header.py-2 {
        padding: 0.5rem 1rem !important;
    }
    
    .card-footer.py-2 {
        padding: 0.5rem 1rem !important;
    }
    
    .list-group-item.py-2 {
        padding: 0.5rem 1rem !important;
    }
    
    /* Scrollbar styling */
    .card-body::-webkit-scrollbar {
        width: 6px;
    }
    
    .card-body::-webkit-scrollbar-track {
        background: #f1f1f1;
    }
    
    .card-body::-webkit-scrollbar-thumb {
        background: #888;
        border-radius: 3px;
    }
    
    .card-body::-webkit-scrollbar-thumb:hover {
        background: #555;
    }
    
    /* Sticky table header */
    .sticky-top {
        position: sticky;
        top: 0;
        z-index: 10;
        background: #f8f9fa !important;
    }
</style>
@endsection