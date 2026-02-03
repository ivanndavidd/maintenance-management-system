@extends('layouts.admin')

@section('page-title', 'Job Management')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2><i class="fas fa-tasks"></i> Maintenance Jobs</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active">Maintenance Jobs</li>
                </ol>
            </nav>
        </div>
        <div>
            <a href="{{ route('admin.jobs.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Create New Job
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
            <form method="GET" action="{{ route('admin.jobs.index') }}">
                <div class="row g-3">
                    <!-- Search -->
                    <div class="col-md-3">
                        <label class="form-label">Search</label>
                        <input type="text" name="search" class="form-control" 
                               placeholder="Job code, title..." 
                               value="{{ request('search') }}">
                    </div>

                    <!-- Status Filter -->
                    <div class="col-md-2">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="">All Status</option>
                            @foreach($statuses as $status)
                                <option value="{{ $status }}" {{ request('status') == $status ? 'selected' : '' }}>
                                    {{ ucfirst(str_replace('_', ' ', $status)) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Priority Filter -->
                    <div class="col-md-2">
                        <label class="form-label">Priority</label>
                        <select name="priority" class="form-select">
                            <option value="">All Priorities</option>
                            @foreach($priorities as $priority)
                                <option value="{{ $priority }}" {{ request('priority') == $priority ? 'selected' : '' }}>
                                    {{ ucfirst($priority) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Type Filter -->
                    <div class="col-md-2">
                        <label class="form-label">Type</label>
                        <select name="type" class="form-select">
                            <option value="">All Types</option>
                            @foreach($types as $type)
                                <option value="{{ $type }}" {{ request('type') == $type ? 'selected' : '' }}>
                                    {{ ucfirst($type) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Overdue Filter -->
                    <div class="col-md-1">
                        <label class="form-label">Overdue</label>
                        <select name="overdue" class="form-select">
                            <option value="">All</option>
                            <option value="1" {{ request('overdue') == '1' ? 'selected' : '' }}>
                                Yes
                            </option>
                        </select>
                    </div>

                    <!-- Machine Filter -->
                    <div class="col-md-2">
                        <label class="form-label">Machine</label>
                        <select name="machine" class="form-select">
                            <option value="">All Machines</option>
                            @foreach($machines as $machine)
                                <option value="{{ $machine->id }}" {{ request('machine') == $machine->id ? 'selected' : '' }}>
                                    {{ $machine->code }} - {{ $machine->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="row g-3 mt-2">
                    <!-- Assigned To Filter -->
                    <div class="col-md-3">
                        <label class="form-label">Assigned To</label>
                        <select name="assigned_to" class="form-select">
                            <option value="">All Users</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}" {{ request('assigned_to') == $user->id ? 'selected' : '' }}>
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

                    <div class="col-md-5 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary me-2">
                            <i class="fas fa-search"></i> Apply Filters
                        </button>
                        <a href="{{ route('admin.jobs.index') }}" class="btn btn-secondary">
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
                                {{ $jobs->where('status', 'pending')->count() }}
                            </h3>
                        </div>
                        <i class="fas fa-clock fa-2x text-warning opacity-25"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="card border-primary shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">In Progress</h6>
                            <h3 class="mb-0 text-primary">
                                {{ $jobs->where('status', 'in_progress')->count() }}
                            </h3>
                        </div>
                        <i class="fas fa-spinner fa-2x text-primary opacity-25"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="card border-success shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Completed</h6>
                            <h3 class="mb-0 text-success">
                                {{ $jobs->where('status', 'completed')->count() }}
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
                            <h6 class="text-muted mb-1">Overdue</h6>
                            <h3 class="mb-0 text-danger">
                                {{ $jobs->filter(fn($job) => $job->isOverdue())->count() }}
                            </h3>
                        </div>
                        <i class="fas fa-exclamation-triangle fa-2x text-danger opacity-25"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Jobs Table Card -->
    <div class="card shadow-sm">
        <div class="card-header bg-white">
            <h5 class="mb-0"><i class="fas fa-list"></i> Jobs List ({{ $jobs->total() }} jobs)</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>
                                <a href="{{ route('admin.jobs.index', array_merge(request()->except(['sort_by', 'sort_order', 'page']), [
                                    'sort_by' => 'job_code',
                                    'sort_order' => request('sort_by') == 'job_code' && request('sort_order') == 'asc' ? 'desc' : 'asc'
                                ])) }}" class="text-decoration-none text-dark">
                                    Job Code
                                    @if(request('sort_by') == 'job_code')
                                        <i class="fas fa-sort-{{ request('sort_order') == 'asc' ? 'up' : 'down' }}"></i>
                                    @else
                                        <i class="fas fa-sort text-muted"></i>
                                    @endif
                                </a>
                            </th>
                            <th>
                                <a href="{{ route('admin.jobs.index', array_merge(request()->except(['sort_by', 'sort_order', 'page']), [
                                    'sort_by' => 'title',
                                    'sort_order' => request('sort_by') == 'title' && request('sort_order') == 'asc' ? 'desc' : 'asc'
                                ])) }}" class="text-decoration-none text-dark">
                                    Title
                                    @if(request('sort_by') == 'title')
                                        <i class="fas fa-sort-{{ request('sort_order') == 'asc' ? 'up' : 'down' }}"></i>
                                    @else
                                        <i class="fas fa-sort text-muted"></i>
                                    @endif
                                </a>
                            </th>
                            <th>Machine</th>
                            <th>
                                <a href="{{ route('admin.jobs.index', array_merge(request()->except(['sort_by', 'sort_order', 'page']), [
                                    'sort_by' => 'type',
                                    'sort_order' => request('sort_by') == 'type' && request('sort_order') == 'asc' ? 'desc' : 'asc'
                                ])) }}" class="text-decoration-none text-dark">
                                    Type
                                    @if(request('sort_by') == 'type')
                                        <i class="fas fa-sort-{{ request('sort_order') == 'asc' ? 'up' : 'down' }}"></i>
                                    @else
                                        <i class="fas fa-sort text-muted"></i>
                                    @endif
                                </a>
                            </th>
                            <th>
                                <a href="{{ route('admin.jobs.index', array_merge(request()->except(['sort_by', 'sort_order', 'page']), [
                                    'sort_by' => 'priority',
                                    'sort_order' => request('sort_by') == 'priority' && request('sort_order') == 'asc' ? 'desc' : 'asc'
                                ])) }}" class="text-decoration-none text-dark">
                                    Priority
                                    @if(request('sort_by') == 'priority')
                                        <i class="fas fa-sort-{{ request('sort_order') == 'asc' ? 'up' : 'down' }}"></i>
                                    @else
                                        <i class="fas fa-sort text-muted"></i>
                                    @endif
                                </a>
                            </th>
                            <th>Assigned To</th>
                            <th>
                                <a href="{{ route('admin.jobs.index', array_merge(request()->except(['sort_by', 'sort_order', 'page']), [
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
                            <th>
                                <a href="{{ route('admin.jobs.index', array_merge(request()->except(['sort_by', 'sort_order', 'page']), [
                                    'sort_by' => 'scheduled_date',
                                    'sort_order' => request('sort_by') == 'scheduled_date' && request('sort_order') == 'asc' ? 'desc' : 'asc'
                                ])) }}" class="text-decoration-none text-dark">
                                    Scheduled
                                    @if(request('sort_by') == 'scheduled_date')
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
                        @forelse($jobs as $job)
                        <tr class="{{ $job->isOverdue() ? 'table-danger' : '' }}">
                            <td>
                                <strong class="text-primary">{{ $job->job_code }}</strong>
                                @if($job->isOverdue())
                                    <i class="fas fa-exclamation-circle text-danger ms-1" title="Overdue"></i>
                                @endif
                            </td>
                            <td>
                                <strong>{{ Str::limit($job->title, 40) }}</strong>
                            </td>
                            <td>
                                @if($job->machine)
                                    <small>
                                        <strong>{{ $job->machine->code }}</strong><br>
                                        {{ Str::limit($job->machine->name, 25) }}
                                    </small>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-{{ $job->typeBadge }}">
                                    {{ ucfirst($job->type) }}
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-{{ $job->priorityBadge }}">
                                    {{ ucfirst($job->priority) }}
                                </span>
                            </td>
                            <td>
                                @if($job->assignedUser)
                                    <small>{{ $job->assignedUser->name }}</small>
                                @else
                                    <span class="badge bg-secondary">Unassigned</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-{{ $job->statusBadge }}">
                                    {{ ucfirst(str_replace('_', ' ', $job->status)) }}
                                </span>
                            </td>
                            <td>
                                @if($job->scheduled_date)
                                    @php
                                        $now = now();
                                        $scheduledDate = $job->scheduled_date;
                                        $diffInDays = (int) $now->diffInDays($scheduledDate, false);
                                        $isOverdue = $job->isOverdue();
                                        $isNearDeadline = !$isOverdue && $diffInDays >= 0 && $diffInDays <= 3;

                                        if ($isOverdue) {
                                            $daysLate = abs($diffInDays);
                                            $tooltipText = 'Overdue by ' . $daysLate . ' day' . ($daysLate != 1 ? 's' : '') . ' (Since ' . $scheduledDate->format('d M Y') . ')';
                                            $badgeClass = 'bg-danger';
                                            $iconClass = 'fa-exclamation-circle';
                                        } elseif ($isNearDeadline) {
                                            $tooltipText = $diffInDays . ' day' . ($diffInDays != 1 ? 's' : '') . ' remaining until deadline';
                                            $badgeClass = 'bg-warning';
                                            $iconClass = 'fa-clock';
                                        } else {
                                            $tooltipText = $diffInDays . ' day' . ($diffInDays != 1 ? 's' : '') . ' remaining';
                                            $badgeClass = '';
                                            $iconClass = '';
                                        }
                                    @endphp
                                    <div class="d-flex align-items-center">
                                        <small>{{ $job->scheduled_date->format('d M Y') }}</small>
                                        @if($isOverdue || $isNearDeadline)
                                            <span class="badge {{ $badgeClass }} ms-2"
                                                  data-bs-toggle="tooltip"
                                                  data-bs-placement="top"
                                                  title="{{ $tooltipText }}">
                                                <i class="fas {{ $iconClass }}"></i>
                                                @if($isOverdue)
                                                    {{ abs($diffInDays) }}d late
                                                @else
                                                    {{ $diffInDays }}d left
                                                @endif
                                            </span>
                                        @else
                                            <span class="text-muted ms-2"
                                                  data-bs-toggle="tooltip"
                                                  data-bs-placement="top"
                                                  title="{{ $tooltipText }}"
                                                  style="cursor: help;">
                                                <i class="fas fa-info-circle"></i>
                                            </span>
                                        @endif
                                    </div>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="{{ route('admin.jobs.show', $job) }}" 
                                       class="btn btn-sm btn-info" 
                                       title="View Details">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.jobs.edit', $job) }}" 
                                       class="btn btn-sm btn-warning" 
                                       title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    
                                    <div class="btn-group" role="group">
                                        <button type="button" 
                                                class="btn btn-sm btn-secondary dropdown-toggle" 
                                                data-bs-toggle="dropdown">
                                            <i class="fas fa-ellipsis-v"></i>
                                        </button>
                                        <ul class="dropdown-menu">
                                            <li>
                                                <button type="button" 
                                                        class="dropdown-item" 
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#statusModal{{ $job->id }}">
                                                    <i class="fas fa-exchange-alt"></i> Change Status
                                                </button>
                                            </li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li>
                                                <form action="{{ route('admin.jobs.destroy', $job) }}" 
                                                      method="POST" 
                                                      onsubmit="return confirm('Are you sure you want to delete this job?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="dropdown-item text-danger">
                                                        <i class="fas fa-trash"></i> Delete
                                                    </button>
                                                </form>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </td>
                        </tr>

                        <!-- Status Update Modal -->
                        <div class="modal fade" id="statusModal{{ $job->id }}" tabindex="-1">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <form action="{{ route('admin.jobs.update-status', $job) }}" method="POST">
                                        @csrf
                                        @method('PATCH')
                                        <div class="modal-header">
                                            <h5 class="modal-title">Update Job Status</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <p>Update status for: <strong>{{ $job->job_code }}</strong></p>
                                            <div class="mb-3">
                                                <label class="form-label">New Status</label>
                                                <select name="status" class="form-select" required>
                                                    <option value="pending" {{ $job->status == 'pending' ? 'selected' : '' }}>
                                                        Pending
                                                    </option>
                                                    <option value="in_progress" {{ $job->status == 'in_progress' ? 'selected' : '' }}>
                                                        In Progress
                                                    </option>
                                                    <option value="completed" {{ $job->status == 'completed' ? 'selected' : '' }}>
                                                        Completed
                                                    </option>
                                                    <option value="cancelled" {{ $job->status == 'cancelled' ? 'selected' : '' }}>
                                                        Cancelled
                                                    </option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                            <button type="submit" class="btn btn-primary">Update Status</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center py-4">
                                <i class="fas fa-tasks fa-3x text-muted mb-3"></i>
                                <p class="text-muted mb-0">No maintenance jobs found</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        
        @if($jobs->hasPages())
        <div class="card-footer">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    Showing {{ $jobs->firstItem() }} to {{ $jobs->lastItem() }} of {{ $jobs->total() }} jobs
                </div>
                <div>
                    {{ $jobs->appends(request()->except('page'))->links() }}
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

/* Deadline badges */
.badge {
    font-size: 0.7rem;
    padding: 0.25rem 0.5rem;
}

.badge i {
    font-size: 0.7rem;
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