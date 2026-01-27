@extends('layouts.pic')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="mb-4">
        <h2><i class="fas fa-tachometer-alt"></i> Dashboard PIC</h2>
        <p class="text-muted">Welcome, {{ auth()->user()->name }}! Monitor and report machine incidents.</p>
    </div>

    <!-- Statistics Cards -->
    <div class="row g-3 mb-4">
        <!-- Incident Reports Stats -->
        <div class="col-md-3">
            <div class="card border-info shadow-sm">
                <div class="card-body">
                    <h6 class="text-muted mb-2">Total Incident Reports</h6>
                    <h3 class="mb-0 text-info">{{ $incidentStats['total'] }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-warning shadow-sm">
                <div class="card-body">
                    <h6 class="text-muted mb-2">Pending</h6>
                    <h3 class="mb-0 text-warning">{{ $incidentStats['pending'] }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-primary shadow-sm">
                <div class="card-body">
                    <h6 class="text-muted mb-2">In Progress</h6>
                    <h3 class="mb-0 text-primary">{{ $incidentStats['in_progress'] }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-danger shadow-sm">
                <div class="card-body">
                    <h6 class="text-muted mb-2">Critical Issues</h6>
                    <h3 class="mb-0 text-danger">{{ $incidentStats['critical'] }}</h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Task Request Stats -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-secondary shadow-sm">
                <div class="card-body">
                    <h6 class="text-muted mb-2">Total Task Requests</h6>
                    <h3 class="mb-0 text-secondary">{{ $taskRequestStats['total'] }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-warning shadow-sm">
                <div class="card-body">
                    <h6 class="text-muted mb-2">Pending Approval</h6>
                    <h3 class="mb-0 text-warning">{{ $taskRequestStats['pending'] }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-success shadow-sm">
                <div class="card-body">
                    <h6 class="text-muted mb-2">Approved</h6>
                    <h3 class="mb-0 text-success">{{ $taskRequestStats['approved'] }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-danger shadow-sm">
                <div class="card-body">
                    <h6 class="text-muted mb-2">Rejected</h6>
                    <h3 class="mb-0 text-danger">{{ $taskRequestStats['rejected'] }}</h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row g-3 mb-4">
        <div class="col-md-6">
            <div class="card shadow-sm border-start border-danger border-4">
                <div class="card-body">
                    <h5 class="card-title">
                        <i class="fas fa-exclamation-triangle text-danger"></i> Report Machine Incident
                    </h5>
                    <p class="card-text text-muted">Found an issue with a machine? Report it immediately for quick action.</p>
                    <a href="{{ route('pic.incident-reports.create') }}" class="btn btn-danger">
                        <i class="fas fa-plus"></i> Create Incident Report
                    </a>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card shadow-sm border-start border-success border-4">
                <div class="card-body">
                    <h5 class="card-title">
                        <i class="fas fa-tasks text-success"></i> Request Maintenance Task
                    </h5>
                    <p class="card-text text-muted">Need a maintenance task to be performed? Submit a request to the team.</p>
                    <a href="{{ route('pic.task-requests.create') }}" class="btn btn-success">
                        <i class="fas fa-plus"></i> Request Task
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Recent Incident Reports -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow-sm">
                <div class="card-header bg-danger text-white">
                    <h6 class="mb-0"><i class="fas fa-exclamation-triangle"></i> Recent Incident Reports</h6>
                </div>
                <div class="card-body">
                    @if($recentIncidents->count() > 0)
                        <div class="list-group list-group-flush">
                            @foreach($recentIncidents as $incident)
                            <a href="{{ route('pic.incident-reports.show', $incident) }}" class="list-group-item list-group-item-action">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div class="flex-grow-1">
                                        <strong>{{ $incident->report_code }}</strong> - {{ $incident->title }}
                                        <br>
                                        <small class="text-muted">
                                            <i class="fas fa-cog"></i> {{ $incident->machine->name ?? 'N/A' }}
                                        </small>
                                    </div>
                                    <div>
                                        <span class="badge bg-{{ $incident->status_badge }}">{{ ucfirst($incident->status) }}</span>
                                        <br>
                                        <span class="badge bg-{{ $incident->severity_badge }}">{{ ucfirst($incident->severity) }}</span>
                                    </div>
                                </div>
                                <small class="text-muted">{{ $incident->created_at->diffForHumans() }}</small>
                            </a>
                            @endforeach
                        </div>
                        <div class="text-center mt-3">
                            <a href="{{ route('pic.incident-reports.index') }}" class="btn btn-sm btn-outline-danger">
                                View All Reports <i class="fas fa-arrow-right"></i>
                            </a>
                        </div>
                    @else
                        <p class="text-center text-muted mb-0">No incident reports yet</p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Recent Task Requests -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow-sm">
                <div class="card-header bg-success text-white">
                    <h6 class="mb-0"><i class="fas fa-tasks"></i> Recent Task Requests</h6>
                </div>
                <div class="card-body">
                    @if($recentTaskRequests->count() > 0)
                        <div class="list-group list-group-flush">
                            @foreach($recentTaskRequests as $task)
                            <a href="{{ route('pic.task-requests.show', $task) }}" class="list-group-item list-group-item-action">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div class="flex-grow-1">
                                        <strong>{{ $task->request_code }}</strong> - {{ $task->title }}
                                        <br>
                                        <small class="text-muted">
                                            <i class="fas fa-cog"></i> {{ $task->machine->name ?? 'General' }}
                                        </small>
                                    </div>
                                    <span class="badge bg-{{ $task->status_badge }}">{{ ucfirst($task->status) }}</span>
                                </div>
                                <small class="text-muted">{{ $task->created_at->diffForHumans() }}</small>
                            </a>
                            @endforeach
                        </div>
                        <div class="text-center mt-3">
                            <a href="{{ route('pic.task-requests.index') }}" class="btn btn-sm btn-outline-success">
                                View All Requests <i class="fas fa-arrow-right"></i>
                            </a>
                        </div>
                    @else
                        <p class="text-center text-muted mb-0">No task requests yet</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Critical Incidents -->
    @if($criticalIncidents->count() > 0)
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm border-danger">
                <div class="card-header bg-danger text-white">
                    <h6 class="mb-0">
                        <i class="fas fa-exclamation-circle"></i> Critical/High Priority Incidents (Unresolved)
                    </h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Report Code</th>
                                    <th>Machine</th>
                                    <th>Title</th>
                                    <th>Severity</th>
                                    <th>Status</th>
                                    <th>Reported</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($criticalIncidents as $incident)
                                <tr>
                                    <td><strong>{{ $incident->report_code }}</strong></td>
                                    <td>{{ $incident->machine->name }}</td>
                                    <td>{{ Str::limit($incident->title, 40) }}</td>
                                    <td><span class="badge bg-{{ $incident->severity_badge }}">{{ ucfirst($incident->severity) }}</span></td>
                                    <td><span class="badge bg-{{ $incident->status_badge }}">{{ ucfirst($incident->status) }}</span></td>
                                    <td>{{ $incident->created_at->diffForHumans() }}</td>
                                    <td>
                                        <a href="{{ route('pic.incident-reports.show', $incident) }}" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection
