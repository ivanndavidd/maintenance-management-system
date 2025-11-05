@extends('layouts.user')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2><i class="fas fa-home"></i> My Dashboard</h2>
            <p class="text-muted mb-0">Welcome back, {{ auth()->user()->name }}!</p>
        </div>
        <div class="text-end">
            <small class="text-muted">
                <i class="fas fa-calendar"></i> {{ now()->format('l, d F Y') }}
            </small>
        </div>
    </div>

    <!-- Urgent Alerts Widget (TOP PRIORITY) -->
    @if($urgentAlerts['active'] > 0)
    <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
        <div class="d-flex align-items-center">
            <div class="flex-shrink-0">
                <i class="fas fa-exclamation-triangle fa-3x"></i>
            </div>
            <div class="flex-grow-1 ms-3">
                <h5 class="alert-heading mb-2">
                    <i class="fas fa-bell"></i> You have {{ $urgentAlerts['active'] }} active urgent alert(s)!
                </h5>
                <p class="mb-2">
                    @if($urgentAlerts['critical'] > 0)
                        <span class="badge bg-danger">{{ $urgentAlerts['critical'] }} CRITICAL</span>
                    @endif
                    @if($urgentAlerts['pending'] > 0)
                        <span class="badge bg-warning text-dark">{{ $urgentAlerts['pending'] }} PENDING</span>
                    @endif
                </p>
                @if(Route::has('user.urgent-alerts.index'))
                    <a href="{{ route('user.urgent-alerts.index') }}" class="btn btn-light btn-sm">
                        <i class="fas fa-eye"></i> View All Alerts
                    </a>
                @else
                    <span class="badge bg-secondary">Module Coming Soon</span>
                @endif
            </div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <!-- Statistics Cards -->
    <div class="row g-3 mb-4">
        <!-- Urgent Alerts Card -->
        <div class="col-xl-3 col-md-6">
            <div class="card border-danger shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-danger mb-1">Active Alerts</h6>
                            <h2 class="mb-0 text-danger">{{ $urgentAlerts['active'] }}</h2>
                            <small class="text-muted">
                                @if($urgentAlerts['critical'] > 0)
                                    <span class="text-danger">{{ $urgentAlerts['critical'] }} Critical</span>
                                @else
                                    No critical alerts
                                @endif
                            </small>
                        </div>
                        <div class="text-danger">
                            <i class="fas fa-exclamation-circle fa-3x opacity-50"></i>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-danger bg-opacity-10 border-0">
                    @if(Route::has('user.urgent-alerts.index'))
                        <a href="{{ route('user.urgent-alerts.index') }}" class="text-danger text-decoration-none small">
                            View Details <i class="fas fa-arrow-right"></i>
                        </a>
                    @else
                        <span class="text-muted small">Module coming soon</span>
                    @endif
                </div>
            </div>
        </div>

        <!-- Pending Tasks Card -->
        <div class="col-xl-3 col-md-6">
            <div class="card border-warning shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-warning mb-1">Pending Tasks</h6>
                            <h2 class="mb-0 text-warning">{{ $tasks['pending'] }}</h2>
                            <small class="text-muted">Awaiting action</small>
                        </div>
                        <div class="text-warning">
                            <i class="fas fa-clock fa-3x opacity-50"></i>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-warning bg-opacity-10 border-0">
                    <a href="{{ route('user.tasks.index') }}" class="text-warning text-decoration-none small">
                        View Tasks <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>
        </div>

        <!-- In Progress Tasks Card -->
        <div class="col-xl-3 col-md-6">
            <div class="card border-primary shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-primary mb-1">In Progress</h6>
                            <h2 class="mb-0 text-primary">{{ $tasks['in_progress'] }}</h2>
                            <small class="text-muted">Currently working</small>
                        </div>
                        <div class="text-primary">
                            <i class="fas fa-tasks fa-3x opacity-50"></i>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-primary bg-opacity-10 border-0">
                    <a href="{{ route('user.tasks.index') }}?status=in_progress" class="text-primary text-decoration-none small">
                        View Tasks <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>
        </div>

        <!-- Completed This Month Card -->
        <div class="col-xl-3 col-md-6">
            <div class="card border-success shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-success mb-1">Completed</h6>
                            <h2 class="mb-0 text-success">{{ $tasks['completed_this_month'] }}</h2>
                            <small class="text-muted">This month</small>
                        </div>
                        <div class="text-success">
                            <i class="fas fa-check-circle fa-3x opacity-50"></i>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-success bg-opacity-10 border-0">
                    <span class="text-success small">
                        <i class="fas fa-chart-line"></i> Completion Rate: {{ $completionRate }}%
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Active Urgent Alerts Section - Only show if routes exist -->
    @if($activeAlerts->count() > 0 && Route::has('user.urgent-alerts.index'))
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-danger text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="fas fa-siren-on"></i> Active Urgent Alerts
            </h5>
            <a href="{{ route('user.urgent-alerts.index') }}" class="btn btn-light btn-sm">
                View All
            </a>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Priority</th>
                            <th>Title</th>
                            <th>Machine</th>
                            <th>Location</th>
                            <th>Status</th>
                            <th>Created</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($activeAlerts as $alert)
                        <tr>
                            <td>
                                @if($alert->priority === 'critical')
                                    <span class="badge bg-danger">CRITICAL</span>
                                @elseif($alert->priority === 'urgent')
                                    <span class="badge bg-warning text-dark">URGENT</span>
                                @else
                                    <span class="badge bg-info">HIGH</span>
                                @endif
                            </td>
                            <td>
                                <strong>{{ $alert->title }}</strong>
                                <br>
                                <small class="text-muted">By: {{ $alert->creator->name }}</small>
                            </td>
                            <td>
                                @if($alert->machine)
                                    <i class="fas fa-cogs"></i> {{ $alert->machine->name }}
                                @else
                                    -
                                @endif
                            </td>
                            <td>
                                <i class="fas fa-map-marker-alt"></i> {{ $alert->location ?? '-' }}
                            </td>
                            <td>{!! $alert->status_badge !!}</td>
                            <td>
                                <small>{{ $alert->created_at->diffForHumans() }}</small>
                            </td>
                            <td>
                                <a href="{{ route('user.urgent-alerts.show', $alert) }}" 
                                   class="btn btn-sm btn-primary">
                                    <i class="fas fa-eye"></i> View
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif

    <div class="row">
        <!-- Recent Tasks -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-tasks"></i> Recent Tasks
                    </h5>
                    <a href="{{ route('user.tasks.index') }}" class="btn btn-light btn-sm">
                        View All
                    </a>
                </div>
                <div class="card-body">
                    @if($recentTasks->count() > 0)
                        <div class="list-group list-group-flush">
                            @foreach($recentTasks as $task)
                            <div class="list-group-item px-0">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1">
                                            @if($task->machine)
                                                <i class="fas fa-cogs text-primary"></i> {{ $task->machine->name }}
                                            @else
                                                General Task
                                            @endif
                                        </h6>
                                        <p class="mb-1 text-muted small">{{ Str::limit($task->description, 60) }}</p>
                                        <small class="text-muted">
                                            <i class="fas fa-calendar"></i> {{ $task->created_at->format('d M Y') }}
                                        </small>
                                    </div>
                                    <div class="text-end ms-2">
                                        @if($task->status === 'pending')
                                            <span class="badge bg-warning text-dark">Pending</span>
                                        @elseif($task->status === 'in_progress')
                                            <span class="badge bg-primary">In Progress</span>
                                        @else
                                            <span class="badge bg-success">Completed</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-muted text-center py-4 mb-0">
                            <i class="fas fa-inbox fa-3x mb-3 d-block"></i>
                            No recent tasks
                        </p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Recent Work Reports -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow-sm">
                <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-file-alt"></i> Recent Reports
                    </h5>
                    <a href="{{ route('user.reports.index') }}" class="btn btn-light btn-sm">
                        View All
                    </a>
                </div>
                <div class="card-body">
                    @if($recentReports->count() > 0)
                        <div class="list-group list-group-flush">
                            @foreach($recentReports as $report)
                            <div class="list-group-item px-0">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1">
                                            Report #{{ $report->id }}
                                        </h6>
                                        <p class="mb-1 text-muted small">
                                            {{ $report->job->machine->name ?? 'N/A' }}
                                        </p>
                                        <small class="text-muted">
                                            <i class="fas fa-calendar"></i> {{ $report->created_at->format('d M Y') }}
                                        </small>
                                    </div>
                                     <div class="text-end ms-2">
                                        @if($report->status === 'draft')
                                            <span class="badge bg-secondary">Draft</span>
                                        @elseif($report->status === 'submitted')
                                            <span class="badge bg-warning text-dark">Submitted</span>
                                        @elseif($report->status === 'approved')
                                            <span class="badge bg-success">Approved</span>
                                        @elseif($report->status === 'revision_needed')
                                            <span class="badge bg-danger">Needs Revision</span>
                                        @else
                                            <span class="badge bg-secondary">{{ ucfirst($report->status) }}</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-muted text-center py-4 mb-0">
                            <i class="fas fa-inbox fa-3x mb-3 d-block"></i>
                            No recent reports
                        </p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Performance Summary -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-chart-line"></i> My Performance
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-md-3">
                            <h3 class="text-primary">{{ $completionRate }}%</h3>
                            <p class="text-muted mb-0">Completion Rate</p>
                        </div>
                        <div class="col-md-3">
                            <h3 class="text-success">{{ $tasks['completed_this_month'] }}</h3>
                            <p class="text-muted mb-0">Completed This Month</p>
                        </div>
                        <div class="col-md-3">
                            <h3 class="text-warning">{{ $tasks['in_progress'] }}</h3>
                            <p class="text-muted mb-0">Active Tasks</p>
                        </div>
                        <div class="col-md-3">
                            <h3 class="text-info">{{ $reports['total'] }}</h3>
                            <p class="text-muted mb-0">Total Reports</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection