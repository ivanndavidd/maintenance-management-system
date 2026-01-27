@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2><i class="fas fa-file-alt"></i> Work Reports</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active">Work Reports</li>
                </ol>
            </nav>
        </div>
        <div>
            <a href="{{ route('admin.work-reports.my-reports') }}" class="btn btn-info">
                <i class="fas fa-user"></i> My Reports
            </a>
            <a href="{{ route('admin.work-reports.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Submit New Report
            </a>
        </div>
    </div>

    <!-- Success/Error Messages -->
    <!-- Filters Card -->
    <div class="card mb-4 shadow-sm">
        <div class="card-header bg-white">
            <h5 class="mb-0"><i class="fas fa-filter"></i> Filters</h5>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('admin.work-reports.index') }}">
                <div class="row g-3">
                    <!-- Search -->
                    <div class="col-md-3">
                        <label class="form-label">Search</label>
                        <input type="text" name="search" class="form-control" 
                               placeholder="Report code, work performed..." 
                               value="{{ request('search') }}">
                    </div>

                    <!-- Status Filter -->
                    <div class="col-md-2">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="">All Status</option>
                            @foreach($statuses as $status)
                                <option value="{{ $status }}" {{ request('status') == $status ? 'selected' : '' }}>
                                    {{ ucfirst($status) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- User Filter -->
                    <div class="col-md-3">
                        <label class="form-label">Submitted By</label>
                        <select name="user" class="form-select">
                            <option value="">All Users</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}" {{ request('user') == $user->id ? 'selected' : '' }}>
                                    {{ $user->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Date From -->
                    <div class="col-md-2">
                        <label class="form-label">Date From</label>
                        <input type="date" name="date_from" class="form-control" 
                               value="{{ request('date_from') }}">
                    </div>

                    <!-- Date To -->
                    <div class="col-md-2">
                        <label class="form-label">Date To</label>
                        <input type="date" name="date_to" class="form-control" 
                               value="{{ request('date_to') }}">
                    </div>
                </div>

                <div class="mt-3">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i> Apply Filters
                    </button>
                    <a href="{{ route('admin.work-reports.index') }}" class="btn btn-secondary">
                        <i class="fas fa-redo"></i> Reset
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="card border-warning shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Pending Review</h6>
                            <h3 class="mb-0 text-warning">
                                {{ $reports->where('status', 'pending')->count() }}
                            </h3>
                        </div>
                        <i class="fas fa-clock fa-2x text-warning opacity-25"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="card border-success shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Approved</h6>
                            <h3 class="mb-0 text-success">
                                {{ $reports->where('status', 'approved')->count() }}
                            </h3>
                        </div>
                        <i class="fas fa-check-circle fa-2x text-success opacity-25"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="card border-danger shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Rejected</h6>
                            <h3 class="mb-0 text-danger">
                                {{ $reports->where('status', 'rejected')->count() }}
                            </h3>
                        </div>
                        <i class="fas fa-times-circle fa-2x text-danger opacity-25"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="card border-secondary shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Draft</h6>
                            <h3 class="mb-0 text-secondary">
                                {{ $reports->where('status', 'draft')->count() }}
                            </h3>
                        </div>
                        <i class="fas fa-file fa-2x text-secondary opacity-25"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Reports Table Card -->
    <div class="card shadow-sm">
        <div class="card-header bg-white">
            <h5 class="mb-0"><i class="fas fa-list"></i> Reports List ({{ $reports->total() }} reports)</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>
                                <a href="{{ route('admin.work-reports.index', array_merge(request()->except(['sort_by', 'sort_order', 'page']), [
                                    'sort_by' => 'report_code',
                                    'sort_order' => request('sort_by') == 'report_code' && request('sort_order') == 'asc' ? 'desc' : 'asc'
                                ])) }}" class="text-decoration-none text-dark">
                                    Report Code
                                    @if(request('sort_by') == 'report_code')
                                        <i class="fas fa-sort-{{ request('sort_order') == 'asc' ? 'up' : 'down' }}"></i>
                                    @else
                                        <i class="fas fa-sort text-muted"></i>
                                    @endif
                                </a>
                            </th>
                            <th>Job</th>
                            <th>Machine</th>
                            <th>Submitted By</th>
                            <th>
                                <a href="{{ route('admin.work-reports.index', array_merge(request()->except(['sort_by', 'sort_order', 'page']), [
                                    'sort_by' => 'work_start',
                                    'sort_order' => request('sort_by') == 'work_start' && request('sort_order') == 'asc' ? 'desc' : 'asc'
                                ])) }}" class="text-decoration-none text-dark">
                                    Work Date
                                    @if(request('sort_by') == 'work_start')
                                        <i class="fas fa-sort-{{ request('sort_order') == 'asc' ? 'up' : 'down' }}"></i>
                                    @else
                                        <i class="fas fa-sort text-muted"></i>
                                    @endif
                                </a>
                            </th>
                            <th>Duration</th>
                            <th>
                                <a href="{{ route('admin.work-reports.index', array_merge(request()->except(['sort_by', 'sort_order', 'page']), [
                                    'sort_by' => 'machine_condition',
                                    'sort_order' => request('sort_by') == 'machine_condition' && request('sort_order') == 'asc' ? 'desc' : 'asc'
                                ])) }}" class="text-decoration-none text-dark">
                                    Condition
                                    @if(request('sort_by') == 'machine_condition')
                                        <i class="fas fa-sort-{{ request('sort_order') == 'asc' ? 'up' : 'down' }}"></i>
                                    @else
                                        <i class="fas fa-sort text-muted"></i>
                                    @endif
                                </a>
                            </th>
                            <th>
                                <a href="{{ route('admin.work-reports.index', array_merge(request()->except(['sort_by', 'sort_order', 'page']), [
                                    'sort_by' => 'status',
                                    'sort_order' => request('sort_by') == 'status' && request('sort_order') == 'asc' ? 'desc' : 'asc'
                                ])) }}" class="text-decoration-none text-dark">
                                    Status
                                    @if(request('sort_by') == 'status')
                                        <i class="fas fa-sort-{{ request('sort_order') == 'asc' ? 'up' : 'down' }}"></i>
                                    @else
                                        <i class="fas fa-sort text-muted"></i>
                                    @endif
                                </a>
                            </th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($reports as $report)
                        <tr>
                            <td>
                                <strong class="text-primary">{{ $report->report_code }}</strong>
                            </td>
                            <td>
                                @if($report->job)
                                    <small>
                                        <strong>{{ $report->job->job_code }}</strong><br>
                                        {{ Str::limit($report->job->title, 30) }}
                                    </small>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @if($report->job && $report->job->machine)
                                    <small>
                                        <strong>{{ $report->job->machine->code }}</strong><br>
                                        {{ Str::limit($report->job->machine->name, 25) }}
                                    </small>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                <small>{{ $report->user->name }}</small>
                            </td>
                            <td>
                                <small>{{ $report->work_start->format('d M Y') }}</small>
                            </td>
                            <td>
                                <small>{{ $report->workDurationFormatted }}</small>
                            </td>
                            <td>
                                <span class="badge bg-{{ $report->conditionBadge }}">
                                    {{ ucfirst($report->machine_condition) }}
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-{{ $report->statusBadge }}">
                                    {{ ucfirst($report->status) }}
                                </span>
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="{{ route('admin.work-reports.show', $report) }}" 
                                       class="btn btn-sm btn-info" 
                                       title="View Details">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    
                                    @if($report->status === 'pending')
                                    <button type="button" 
                                            class="btn btn-sm btn-success" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#validateModal{{ $report->id }}"
                                            title="Approve/Reject">
                                        <i class="fas fa-check"></i>
                                    </button>
                                    @endif
                                </div>
                            </td>
                        </tr>

                        <!-- Validate Modal -->
                        @if($report->status === 'pending')
                        <div class="modal fade" id="validateModal{{ $report->id }}" tabindex="-1">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <form action="{{ route('admin.work-reports.validate', $report) }}" method="POST">
                                        @csrf
                                        @method('PATCH')
                                        <div class="modal-header">
                                            <h5 class="modal-title">Review Work Report</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <p>Review report: <strong>{{ $report->report_code }}</strong></p>
                                            
                                            <div class="mb-3">
                                                <label class="form-label">Decision <span class="text-danger">*</span></label>
                                                <select name="status" class="form-select" required>
                                                    <option value="approved">Approve</option>
                                                    <option value="rejected">Reject</option>
                                                </select>
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label">Admin Comments</label>
                                                <textarea name="admin_comments" 
                                                          class="form-control" 
                                                          rows="3"
                                                          placeholder="Optional feedback for the technician..."></textarea>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                            <button type="submit" class="btn btn-primary">Submit Review</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        @endif
                        @empty
                        <tr>
                            <td colspan="9" class="text-center py-4">
                                <i class="fas fa-file-alt fa-3x text-muted mb-3"></i>
                                <p class="text-muted mb-0">No work reports found</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        
        @if($reports->hasPages())
        <div class="card-footer">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    Showing {{ $reports->firstItem() }} to {{ $reports->lastItem() }} of {{ $reports->total() }} reports
                </div>
                <div>
                    {{ $reports->appends(request()->except('page'))->links() }}
                </div>
            </div>
        </div>
        @endif
    </div>
</div>

<style>
.opacity-25 {
    opacity: 0.25;
}

/* Sortable table headers */
.table thead th a {
    display: block;
    width: 100%;
    white-space: nowrap;
    transition: all 0.2s ease;
}

.table thead th a:hover {
    color: #0d6efd !important;
}

.table thead th a i.fa-sort {
    opacity: 0.3;
    transition: opacity 0.2s ease;
}

.table thead th a:hover i.fa-sort {
    opacity: 0.6;
}

.table thead th a i.fa-sort-up,
.table thead th a i.fa-sort-down {
    color: #0d6efd;
}
</style>
@endsection