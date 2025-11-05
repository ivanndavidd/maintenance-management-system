@extends('layouts.user')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="mb-4">
        <h2><i class="fas fa-tasks"></i> My Tasks</h2>
        <p class="text-muted">View and manage your assigned maintenance tasks</p>
    </div>

    <!-- Statistics Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-info shadow-sm">
                <div class="card-body">
                    <h6 class="text-muted mb-2">Total Tasks</h6>
                    <h3 class="mb-0 text-info">{{ $stats['total'] }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-warning shadow-sm">
                <div class="card-body">
                    <h6 class="text-muted mb-2">Pending</h6>
                    <h3 class="mb-0 text-warning">{{ $stats['pending'] }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-primary shadow-sm">
                <div class="card-body">
                    <h6 class="text-muted mb-2">In Progress</h6>
                    <h3 class="mb-0 text-primary">{{ $stats['in_progress'] }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-success shadow-sm">
                <div class="card-body">
                    <h6 class="text-muted mb-2">Completed</h6>
                    <h3 class="mb-0 text-success">{{ $stats['completed'] }}</h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('user.tasks.index') }}" class="row g-3">
                <!-- Search -->
                <div class="col-md-4">
                    <label class="form-label">Search</label>
                    <input type="text" name="search" class="form-control" 
                           placeholder="Search by machine or description..." 
                           value="{{ request('search') }}">
                </div>

                <!-- Status Filter -->
                <div class="col-md-2">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">All Status</option>
                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="in_progress" {{ request('status') === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                        <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                    </select>
                </div>

                <!-- Priority Filter -->
                <div class="col-md-2">
                    <label class="form-label">Priority</label>
                    <select name="priority" class="form-select">
                        <option value="">All Priority</option>
                        <option value="urgent" {{ request('priority') === 'urgent' ? 'selected' : '' }}>Urgent</option>
                        <option value="high" {{ request('priority') === 'high' ? 'selected' : '' }}>High</option>
                        <option value="normal" {{ request('priority') === 'normal' ? 'selected' : '' }}>Normal</option>
                        <option value="low" {{ request('priority') === 'low' ? 'selected' : '' }}>Low</option>
                    </select>
                </div>

                <!-- Sort -->
                <div class="col-md-2">
                    <label class="form-label">Sort By</label>
                    <select name="sort" class="form-select">
                        <option value="created_at" {{ request('sort') === 'created_at' ? 'selected' : '' }}>Date Created</option>
                        <option value="priority" {{ request('sort') === 'priority' ? 'selected' : '' }}>Priority</option>
                        <option value="due_date" {{ request('sort') === 'due_date' ? 'selected' : '' }}>Due Date</option>
                    </select>
                </div>

                <!-- Buttons -->
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="fas fa-filter"></i> Filter
                    </button>
                    <a href="{{ route('user.tasks.index') }}" class="btn btn-secondary">
                        <i class="fas fa-redo"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Tasks Grid -->
    @if($tasks->count() > 0)
        <div class="row g-3">
            @foreach($tasks as $task)
            <div class="col-md-6 col-lg-4">
                <div class="card shadow-sm h-100">
                    <!-- Card Header with Status -->
                    <div class="card-header d-flex justify-content-between align-items-center
                        {{ $task->status === 'pending' ? 'bg-warning text-dark' : '' }}
                        {{ $task->status === 'in_progress' ? 'bg-primary text-white' : '' }}
                        {{ $task->status === 'completed' ? 'bg-success text-white' : '' }}">
                        <span class="fw-bold">
                            @if($task->status === 'pending')
                                <i class="fas fa-clock"></i> Pending
                            @elseif($task->status === 'in_progress')
                                <i class="fas fa-spinner fa-spin"></i> In Progress
                            @else
                                <i class="fas fa-check-circle"></i> Completed
                            @endif
                        </span>
                        
                        <!-- Priority Badge -->
                        @if($task->priority === 'urgent')
                            <span class="badge bg-danger">URGENT</span>
                        @elseif($task->priority === 'high')
                            <span class="badge bg-warning text-dark">HIGH</span>
                        @elseif($task->priority === 'normal')
                            <span class="badge bg-info">NORMAL</span>
                        @else
                            <span class="badge bg-secondary">LOW</span>
                        @endif
                    </div>

                    <div class="card-body">
                        <!-- Machine Name -->
                        <h5 class="card-title">
                            <i class="fas fa-cogs text-primary"></i>
                            {{ $task->machine->name ?? 'General Task' }}
                        </h5>

                        @if($task->machine && $task->machine->code)
                            <p class="text-muted small mb-2">
                                <i class="fas fa-barcode"></i> {{ $task->machine->code }}
                            </p>
                        @endif

                        <!-- Description -->
                        <p class="card-text text-muted">
                            {{ Str::limit($task->description, 100) }}
                        </p>

                        <!-- Task Details -->
                        <div class="mb-2">
                            <small class="text-muted">
                                <i class="fas fa-user"></i> Assigned by: 
                                <strong>{{ $task->assignedBy->name ?? 'System' }}</strong>
                            </small>
                        </div>

                        <div class="mb-2">
                            <small class="text-muted">
                                <i class="fas fa-calendar"></i> Created: 
                                {{ $task->created_at->format('d M Y') }}
                            </small>
                        </div>

                        @if($task->due_date)
                        <div class="mb-2">
                            <small class="{{ $task->due_date->isPast() && $task->status !== 'completed' ? 'text-danger' : 'text-muted' }}">
                                <i class="fas fa-clock"></i> Due: 
                                {{ $task->due_date->format('d M Y') }}
                                @if($task->due_date->isPast() && $task->status !== 'completed')
                                    <span class="badge bg-danger ms-1">OVERDUE</span>
                                @endif
                            </small>
                        </div>
                        @endif

                        @if($task->estimated_time)
                        <div class="mb-3">
                            <small class="text-muted">
                                <i class="fas fa-hourglass-half"></i> Est. Time: 
                                {{ $task->estimated_time }} hours
                            </small>
                        </div>
                        @endif
                    </div>

                    <div class="card-footer bg-light">
                        <a href="{{ route('user.tasks.show', $task) }}" class="btn btn-primary btn-sm w-100">
                            <i class="fas fa-eye"></i> View Details
                        </a>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        <!-- Pagination -->
        <div class="mt-4">
            {{ $tasks->links() }}
        </div>
    @else
        <div class="card shadow-sm">
            <div class="card-body text-center py-5">
                <i class="fas fa-inbox fa-4x text-muted mb-3"></i>
                <h5 class="text-muted">No tasks found</h5>
                <p class="text-muted">
                    @if(request()->hasAny(['search', 'status', 'priority']))
                        Try adjusting your filters
                    @else
                        You don't have any assigned tasks at the moment
                    @endif
                </p>
                @if(request()->hasAny(['search', 'status', 'priority']))
                    <a href="{{ route('user.tasks.index') }}" class="btn btn-primary">
                        <i class="fas fa-redo"></i> Clear Filters
                    </a>
                @endif
            </div>
        </div>
    @endif
</div>
@endsection