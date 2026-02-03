@extends('layouts.admin')

@section('page-title', 'My Work Reports')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2><i class="fas fa-user-edit"></i> My Work Reports</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.work-reports.index') }}">Work Reports</a></li>
                    <li class="breadcrumb-item active">My Reports</li>
                </ol>
            </nav>
        </div>
        <div>
            <a href="{{ route('admin.work-reports.index') }}" class="btn btn-secondary">
                <i class="fas fa-list"></i> All Reports
            </a>
            <a href="{{ route('admin.work-reports.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Submit New Report
            </a>
        </div>
    </div>

    <!-- Success/Error Messages -->
    <!-- Quick Filter -->
    <div class="card mb-4 shadow-sm">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.work-reports.my-reports') }}">
                <div class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label">Filter by Status</label>
                        <select name="status" class="form-select">
                            <option value="">All Status</option>
                            @foreach($statuses as $status)
                                <option value="{{ $status }}" {{ request('status') == $status ? 'selected' : '' }}>
                                    {{ ucfirst($status) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-filter"></i> Apply Filter
                        </button>
                        <a href="{{ route('admin.work-reports.my-reports') }}" class="btn btn-secondary">
                            <i class="fas fa-redo"></i> Reset
                        </a>
                    </div>
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
                            <h6 class="text-muted mb-1">Pending</h6>
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

    <!-- My Reports Table -->
    <div class="card shadow-sm">
        <div class="card-header bg-white">
            <h5 class="mb-0"><i class="fas fa-list"></i> My Submitted Reports ({{ $reports->total() }} reports)</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Report Code</th>
                            <th>Job</th>
                            <th>Machine</th>
                            <th>Work Date</th>
                            <th>Duration</th>
                            <th>Condition</th>
                            <th>Status</th>
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
                                @if($report->status === 'rejected' && $report->admin_comments)
                                    <button type="button" 
                                            class="btn btn-sm btn-link p-0" 
                                            data-bs-toggle="tooltip" 
                                            title="{{ $report->admin_comments }}">
                                        <i class="fas fa-info-circle text-danger"></i>
                                    </button>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="{{ route('admin.work-reports.show', $report) }}" 
                                       class="btn btn-sm btn-info" 
                                       title="View Details">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    
                                    @if(in_array($report->status, ['draft', 'pending']))
                                    <a href="{{ route('admin.work-reports.edit', $report) }}" 
                                       class="btn btn-sm btn-warning" 
                                       title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    @endif
                                    
                                    @if($report->status === 'draft')
                                    <form action="{{ route('admin.work-reports.destroy', $report) }}" 
                                          method="POST" 
                                          onsubmit="return confirm('Are you sure you want to delete this draft report?')"
                                          style="display: inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-4">
                                <i class="fas fa-file-alt fa-3x text-muted mb-3"></i>
                                <p class="text-muted mb-2">You haven't submitted any work reports yet</p>
                                <a href="{{ route('admin.work-reports.create') }}" class="btn btn-primary">
                                    <i class="fas fa-plus"></i> Submit Your First Report
                                </a>
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
                    {{ $reports->links() }}
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
</style>

<script>
// Initialize Bootstrap tooltips
document.addEventListener('DOMContentLoaded', function() {
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});
</script>
@endsection