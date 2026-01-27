@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="mb-4">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h2><i class="fas fa-exclamation-triangle"></i> Incident Reports Management</h2>
                <p class="text-muted">Monitor and manage incident reports from PICs</p>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-warning text-dark shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-0">Pending</h6>
                            <h3 class="mb-0">{{ \App\Models\IncidentReport::pending()->count() }}</h3>
                        </div>
                        <i class="fas fa-clock fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-0">In Progress</h6>
                            <h3 class="mb-0">{{ \App\Models\IncidentReport::inProgress()->count() }}</h3>
                        </div>
                        <i class="fas fa-wrench fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-danger text-white shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-0">Critical</h6>
                            <h3 class="mb-0">{{ \App\Models\IncidentReport::critical()->count() }}</h3>
                        </div>
                        <i class="fas fa-fire fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-0">Resolved</h6>
                            <h3 class="mb-0">{{ \App\Models\IncidentReport::resolved()->count() }}</h3>
                        </div>
                        <i class="fas fa-check-circle fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.incident-reports.index') }}" class="row g-3">
                <!-- Search -->
                <div class="col-md-3">
                    <label class="form-label">Search</label>
                    <input type="text" name="search" class="form-control"
                           placeholder="Code, title, machine, reporter..."
                           value="{{ request('search') }}">
                </div>

                <!-- Status Filter -->
                <div class="col-md-2">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">All Status</option>
                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="assigned" {{ request('status') === 'assigned' ? 'selected' : '' }}>Assigned</option>
                        <option value="in_progress" {{ request('status') === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                        <option value="resolved" {{ request('status') === 'resolved' ? 'selected' : '' }}>Resolved</option>
                        <option value="closed" {{ request('status') === 'closed' ? 'selected' : '' }}>Closed</option>
                    </select>
                </div>

                <!-- Severity Filter -->
                <div class="col-md-2">
                    <label class="form-label">Severity</label>
                    <select name="severity" class="form-select">
                        <option value="">All Severity</option>
                        <option value="low" {{ request('severity') === 'low' ? 'selected' : '' }}>Low</option>
                        <option value="medium" {{ request('severity') === 'medium' ? 'selected' : '' }}>Medium</option>
                        <option value="high" {{ request('severity') === 'high' ? 'selected' : '' }}>High</option>
                        <option value="critical" {{ request('severity') === 'critical' ? 'selected' : '' }}>Critical</option>
                    </select>
                </div>

                <!-- Reporter Filter -->
                <div class="col-md-3">
                    <label class="form-label">Reporter (PIC)</label>
                    <select name="reporter_id" class="form-select">
                        <option value="">All Reporters</option>
                        @foreach($reporters as $reporter)
                        <option value="{{ $reporter->id }}" {{ request('reporter_id') == $reporter->id ? 'selected' : '' }}>
                            {{ $reporter->name }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <!-- Buttons -->
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="fas fa-filter"></i> Filter
                    </button>
                    <a href="{{ route('admin.incident-reports.index') }}" class="btn btn-secondary">
                        <i class="fas fa-redo"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Incident Reports Table -->
    @if($incidentReports->count() > 0)
        <div class="card shadow-sm">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Report Code</th>
                                <th>Machine</th>
                                <th>Incident Type</th>
                                <th>Severity</th>
                                <th>Status</th>
                                <th>Reporter</th>
                                <th>Reported At</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($incidentReports as $report)
                            <tr>
                                <td>
                                    <strong>{{ $report->report_code }}</strong>
                                    <br>
                                    <small class="text-muted">{{ Str::limit($report->title, 30) }}</small>
                                </td>
                                <td>
                                    <i class="fas fa-cog"></i> {{ $report->machine->name }}
                                    <br>
                                    <small class="text-muted">{{ $report->machine->code }}</small>
                                </td>
                                <td>{{ $report->incident_type }}</td>
                                <td>
                                    <span class="badge bg-{{ $report->severity_badge }}">
                                        {{ ucfirst($report->severity) }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-{{ $report->status_badge }}">
                                        {{ ucfirst(str_replace('_', ' ', $report->status)) }}
                                    </span>
                                    @if($report->assignedUser)
                                        <br>
                                        <small class="text-muted">
                                            <i class="fas fa-user"></i> {{ $report->assignedUser->name }}
                                        </small>
                                    @endif
                                </td>
                                <td>
                                    {{ $report->reporter->name }}
                                    <br>
                                    <small class="text-muted">{{ $report->reporter->employee_id }}</small>
                                </td>
                                <td>
                                    {{ $report->created_at->format('d M Y') }}
                                    <br>
                                    <small class="text-muted">{{ $report->created_at->format('H:i') }}</small>
                                </td>
                                <td>
                                    <a href="{{ route('admin.incident-reports.show', $report) }}"
                                       class="btn btn-sm btn-outline-primary"
                                       title="View Details">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="d-flex justify-content-between align-items-center mt-3">
                    <div class="text-muted small">
                        Showing {{ $incidentReports->firstItem() }} to {{ $incidentReports->lastItem() }} of {{ $incidentReports->total() }} entries
                    </div>
                    {{ $incidentReports->appends(request()->except('page'))->links() }}
                </div>
            </div>
        </div>
    @else
        <div class="card shadow-sm">
            <div class="card-body text-center py-5">
                <i class="fas fa-exclamation-triangle fa-4x text-muted mb-3"></i>
                <h5 class="text-muted">No incident reports found</h5>
                <p class="text-muted">
                    @if(request()->hasAny(['search', 'status', 'severity', 'reporter_id']))
                        Try adjusting your filters
                    @else
                        No incident reports have been submitted yet
                    @endif
                </p>
                @if(request()->hasAny(['search', 'status', 'severity', 'reporter_id']))
                    <a href="{{ route('admin.incident-reports.index') }}" class="btn btn-primary">
                        <i class="fas fa-redo"></i> Clear Filters
                    </a>
                @endif
            </div>
        </div>
    @endif
</div>
@endsection
