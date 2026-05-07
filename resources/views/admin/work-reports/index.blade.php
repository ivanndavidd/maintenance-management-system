@extends('layouts.admin')

@section('page-title', 'Work Reports')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h5><i class="fas fa-file-alt"></i> Work Reports</h5>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route($routePrefix.'.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active">Work Reports</li>
                </ol>
            </nav>
        </div>
        <div>
            <a href="{{ route($routePrefix.'.work-reports.my-reports') }}" class="btn btn-sm btn-info">
            <i class="fas fa-user"></i><span class="btn-text"> My Reports</span>
            </a>
            <a href="{{ route($routePrefix.'.work-reports.create') }}" class="btn btn-sm btn-primary">
            <i class="fas fa-plus"></i><span class="btn-text"> Submit New Report</span>
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
            <form method="GET" action="{{ route($routePrefix.'.work-reports.index') }}">
                <div class="row g-2">
                    <div class="col-12 col-md-3">
                        <label class="form-label">Search</label>
                        <input type="text" name="search" class="form-control form-control-sm"
                               placeholder="Report code, work performed..."
                               value="{{ request('search') }}">
                    </div>
                    <div class="col-6 col-md-2">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select form-select-sm">
                            <option value="">All Status</option>
                            @foreach($statuses as $status)
                                <option value="{{ $status }}" {{ request('status') == $status ? 'selected' : '' }}>
                                    {{ ucfirst($status) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-6 col-md-3">
                        <label class="form-label">Submitted By</label>
                        <select name="user" class="form-select form-select-sm">
                            <option value="">All Users</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}" {{ request('user') == $user->id ? 'selected' : '' }}>
                                    {{ $user->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-6 col-md-2">
                        <label class="form-label">Date From</label>
                        <input type="date" name="date_from" class="form-control form-control-sm"
                               value="{{ request('date_from') }}">
                    </div>
                    <div class="col-6 col-md-2">
                        <label class="form-label">Date To</label>
                        <input type="date" name="date_to" class="form-control form-control-sm"
                               value="{{ request('date_to') }}">
                    </div>
                </div>
                <div class="mt-2 d-flex gap-2">
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="fas fa-search"></i><span class="btn-text"> Filter</span>
                    </button>
                    <a href="{{ route($routePrefix.'.work-reports.index') }}" class="btn btn-secondary btn-sm">
                        <i class="fas fa-redo"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row g-2 mb-4">
        <div class="col-6 col-md-3">
            <div class="card border-warning shadow-sm">
                <div class="card-body py-2">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1" style="font-size:12px;">Pending</h6>
                            <h5 class="mb-0 text-warning">{{ $reports->where('status', 'pending')->count() }}</h5>
                        </div>
                        <i class="fas fa-clock fa-2x text-warning opacity-25"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-success shadow-sm">
                <div class="card-body py-2">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1" style="font-size:12px;">Approved</h6>
                            <h5 class="mb-0 text-success">{{ $reports->where('status', 'approved')->count() }}</h5>
                        </div>
                        <i class="fas fa-check-circle fa-2x text-success opacity-25"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-danger shadow-sm">
                <div class="card-body py-2">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1" style="font-size:12px;">Rejected</h6>
                            <h5 class="mb-0 text-danger">{{ $reports->where('status', 'rejected')->count() }}</h5>
                        </div>
                        <i class="fas fa-times-circle fa-2x text-danger opacity-25"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-secondary shadow-sm">
                <div class="card-body py-2">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1" style="font-size:12px;">Draft</h6>
                            <h5 class="mb-0 text-secondary">{{ $reports->where('status', 'draft')->count() }}</h5>
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
                                <a href="{{ route($routePrefix.'.work-reports.index', array_merge(request()->except(['sort_by', 'sort_order', 'page']), [
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
                            <th class="d-none d-md-table-cell">Job</th>
                            <th class="d-none d-lg-table-cell">Machine</th>
                            <th class="d-none d-md-table-cell">Submitted By</th>
                            <th class="d-none d-md-table-cell">
                                <a href="{{ route($routePrefix.'.work-reports.index', array_merge(request()->except(['sort_by', 'sort_order', 'page']), [
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
                            <th class="d-none d-lg-table-cell">Duration</th>
                            <th class="d-none d-lg-table-cell">
                                <a href="{{ route($routePrefix.'.work-reports.index', array_merge(request()->except(['sort_by', 'sort_order', 'page']), [
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
                                <a href="{{ route($routePrefix.'.work-reports.index', array_merge(request()->except(['sort_by', 'sort_order', 'page']), [
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
                                <div class="d-md-none">
                                    @if($report->job)
                                        <small class="text-muted">{{ $report->job->job_code }}</small>
                                    @endif
                                    <br><small class="text-muted">{{ $report->work_start->format('d M Y') }}</small>
                                </div>
                            </td>
                            <td class="d-none d-md-table-cell">
                                @if($report->job)
                                    <small>
                                        <strong>{{ $report->job->job_code }}</strong><br>
                                        {{ Str::limit($report->job->title, 30) }}
                                    </small>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td class="d-none d-lg-table-cell">
                                @if($report->job && $report->job->machine)
                                    <small>
                                        <strong>{{ $report->job->machine->code }}</strong><br>
                                        {{ Str::limit($report->job->machine->name, 25) }}
                                    </small>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td class="d-none d-md-table-cell">
                                <small>{{ $report->user->name }}</small>
                            </td>
                            <td class="d-none d-md-table-cell">
                                <small>{{ $report->work_start->format('d M Y') }}</small>
                            </td>
                            <td class="d-none d-lg-table-cell">
                                <small>{{ $report->workDurationFormatted }}</small>
                            </td>
                            <td class="d-none d-lg-table-cell">
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
                                    <a href="{{ route($routePrefix.'.work-reports.show', $report) }}" 
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
                                    <form action="{{ route($routePrefix.'.work-reports.validate', $report) }}" method="POST">
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