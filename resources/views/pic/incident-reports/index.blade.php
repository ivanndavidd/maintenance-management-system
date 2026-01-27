@extends('layouts.pic')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="mb-4">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h2><i class="fas fa-exclamation-triangle"></i> My Incident Reports</h2>
                <p class="text-muted">Track and manage machine incident reports</p>
            </div>
            <a href="{{ route('pic.incident-reports.create') }}" class="btn btn-danger">
                <i class="fas fa-plus"></i> Report New Incident
            </a>
        </div>
    </div>

    <!-- Filters -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('pic.incident-reports.index') }}" class="row g-3">
                <!-- Search -->
                <div class="col-md-4">
                    <label class="form-label">Search</label>
                    <input type="text" name="search" class="form-control"
                           placeholder="Search by code, title, machine..."
                           value="{{ request('search') }}">
                </div>

                <!-- Status Filter -->
                <div class="col-md-3">
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
                <div class="col-md-3">
                    <label class="form-label">Severity</label>
                    <select name="severity" class="form-select">
                        <option value="">All Severity</option>
                        <option value="low" {{ request('severity') === 'low' ? 'selected' : '' }}>Low</option>
                        <option value="medium" {{ request('severity') === 'medium' ? 'selected' : '' }}>Medium</option>
                        <option value="high" {{ request('severity') === 'high' ? 'selected' : '' }}>High</option>
                        <option value="critical" {{ request('severity') === 'critical' ? 'selected' : '' }}>Critical</option>
                    </select>
                </div>

                <!-- Buttons -->
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="fas fa-filter"></i> Filter
                    </button>
                    <a href="{{ route('pic.incident-reports.index') }}" class="btn btn-secondary">
                        <i class="fas fa-redo"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Reports Table -->
    @if($reports->count() > 0)
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
                                <th>Assigned To</th>
                                <th>Reported At</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($reports as $report)
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
                                </td>
                                <td>
                                    @if($report->assignedUser)
                                        <i class="fas fa-user"></i> {{ $report->assignedUser->name }}
                                    @else
                                        <span class="text-muted">Not assigned</span>
                                    @endif
                                </td>
                                <td>
                                    <small>{{ $report->created_at->format('d M Y') }}</small>
                                    <br>
                                    <small class="text-muted">{{ $report->created_at->format('H:i') }}</small>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('pic.incident-reports.show', $report) }}"
                                           class="btn btn-outline-primary"
                                           title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        @if($report->isPending())
                                        <a href="{{ route('pic.incident-reports.edit', $report) }}"
                                           class="btn btn-outline-warning"
                                           title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('pic.incident-reports.destroy', $report) }}"
                                              method="POST"
                                              class="d-inline"
                                              onsubmit="return confirm('Are you sure you want to delete this report?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger" title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="d-flex justify-content-between align-items-center mt-3">
                    <div class="text-muted small">
                        Showing {{ $reports->firstItem() }} to {{ $reports->lastItem() }} of {{ $reports->total() }} entries
                    </div>
                    {{ $reports->appends(request()->except('page'))->links() }}
                </div>
            </div>
        </div>
    @else
        <div class="card shadow-sm">
            <div class="card-body text-center py-5">
                <i class="fas fa-inbox fa-4x text-muted mb-3"></i>
                <h5 class="text-muted">No incident reports found</h5>
                <p class="text-muted">
                    @if(request()->hasAny(['search', 'status', 'severity']))
                        Try adjusting your filters
                    @else
                        You haven't reported any incidents yet
                    @endif
                </p>
                @if(request()->hasAny(['search', 'status', 'severity']))
                    <a href="{{ route('pic.incident-reports.index') }}" class="btn btn-primary">
                        <i class="fas fa-redo"></i> Clear Filters
                    </a>
                @else
                    <a href="{{ route('pic.incident-reports.create') }}" class="btn btn-danger">
                        <i class="fas fa-plus"></i> Report New Incident
                    </a>
                @endif
            </div>
        </div>
    @endif
</div>
@endsection
