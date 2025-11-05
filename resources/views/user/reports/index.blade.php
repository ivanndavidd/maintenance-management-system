@extends('layouts.user')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2><i class="fas fa-file-alt"></i> My Work Reports</h2>
            <p class="text-muted">View and manage your submitted work reports</p>
        </div>
        <a href="{{ route('user.reports.create') }}" class="btn btn-success">
            <i class="fas fa-plus"></i> Submit New Report
        </a>
    </div>

    <!-- Statistics Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border-info shadow-sm">
                <div class="card-body">
                    <h6 class="text-muted mb-2">Total Reports</h6>
                    <h3 class="mb-0 text-info">{{ $stats['total'] }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-warning shadow-sm">
                <div class="card-body">
                    <h6 class="text-muted mb-2">Submitted</h6>
                    <h3 class="mb-0 text-warning">{{ $stats['submitted'] }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-success shadow-sm">
                <div class="card-body">
                    <h6 class="text-muted mb-2">Approved</h6>
                    <h3 class="mb-0 text-success">{{ $stats['approved'] }}</h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('user.reports.index') }}" class="row g-3">
                <!-- Search -->
                <div class="col-md-3">
                    <label class="form-label">Search</label>
                    <input type="text" name="search" class="form-control" 
                           placeholder="Search reports..." 
                           value="{{ request('search') }}">
                </div>

                <!-- Status Filter -->
                <div class="col-md-2">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">All Status</option>
                        <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="submitted" {{ request('status') === 'submitted' ? 'selected' : '' }}>Submitted</option>
                        <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approved</option>
                        <option value="revision_needed" {{ request('status') === 'revision_needed' ? 'selected' : '' }}>Needs Revision</option>
                    </select>
                </div>

                <!-- Date From -->
                <div class="col-md-2">
                    <label class="form-label">From Date</label>
                    <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                </div>

                <!-- Date To -->
                <div class="col-md-2">
                    <label class="form-label">To Date</label>
                    <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                </div>

                <!-- Buttons -->
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="fas fa-filter"></i> Filter
                    </button>
                    <a href="{{ route('user.reports.index') }}" class="btn btn-secondary">
                        <i class="fas fa-redo"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Reports Table -->
    @if($reports->count() > 0)
        <div class="card shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Report Code</th>
                                <th>Machine</th>
                                <th>Work Period</th>
                                <th>Downtime</th>
                                <th>Condition</th>
                                <th>Status</th>
                                <th>Submitted</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($reports as $report)
                            <tr>
                                <td>
                                    <strong>{{ $report->report_code }}</strong>
                                </td>
                                <td>
                                    @if($report->job && $report->job->machine)
                                        <i class="fas fa-cogs text-primary"></i>
                                        {{ $report->job->machine->name }}
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>
                                    <small>
                                        {{ \Carbon\Carbon::parse($report->work_start)->format('d M Y H:i') }}
                                        <br>
                                        to {{ \Carbon\Carbon::parse($report->work_end)->format('d M Y H:i') }}
                                    </small>
                                </td>
                                <td>
                                    <span class="badge bg-secondary">
                                        {{ $report->downtime_minutes }} min
                                    </span>
                                </td>
                                <td>
                                    @if($report->machine_condition === 'good')
                                        <span class="badge bg-success">Good</span>
                                    @elseif($report->machine_condition === 'fair')
                                        <span class="badge bg-warning text-dark">Fair</span>
                                    @else
                                        <span class="badge bg-danger">Poor</span>
                                    @endif
                                </td>
                                <td>
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
                                </td>
                                <td>
                                    <small>{{ $report->created_at->format('d M Y') }}</small>
                                </td>
                                <td>
                                    <a href="{{ route('user.reports.show', $report) }}" 
                                       class="btn btn-sm btn-primary">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    
                                    @if(in_array($report->status, ['draft', 'submitted', 'revision_needed']))
                                        <a href="{{ route('user.reports.edit', $report) }}" 
                                        class="btn btn-sm btn-warning">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Pagination -->
        <div class="mt-4">
            {{ $reports->links() }}
        </div>
    @else
        <div class="card shadow-sm">
            <div class="card-body text-center py-5">
                <i class="fas fa-inbox fa-4x text-muted mb-3"></i>
                <h5 class="text-muted">No work reports found</h5>
                <p class="text-muted">
                    @if(request()->hasAny(['search', 'status', 'date_from', 'date_to']))
                        Try adjusting your filters
                    @else
                        You haven't submitted any work reports yet
                    @endif
                </p>
                <a href="{{ route('user.reports.create') }}" class="btn btn-success">
                    <i class="fas fa-plus"></i> Submit Your First Report
                </a>
            </div>
        </div>
    @endif
</div>
@endsection