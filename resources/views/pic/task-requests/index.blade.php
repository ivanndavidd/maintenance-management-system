@extends('layouts.pic')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="mb-4">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h2><i class="fas fa-tasks"></i> My Task Requests</h2>
                <p class="text-muted">Request maintenance tasks for machines</p>
            </div>
            <a href="{{ route('pic.task-requests.create') }}" class="btn btn-success">
                <i class="fas fa-plus"></i> Request New Task
            </a>
        </div>
    </div>

    <!-- Filters -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('pic.task-requests.index') }}" class="row g-3">
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
                        <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approved</option>
                        <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
                        <option value="assigned" {{ request('status') === 'assigned' ? 'selected' : '' }}>Assigned</option>
                        <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                    </select>
                </div>

                <!-- Priority Filter -->
                <div class="col-md-3">
                    <label class="form-label">Priority</label>
                    <select name="priority" class="form-select">
                        <option value="">All Priority</option>
                        <option value="low" {{ request('priority') === 'low' ? 'selected' : '' }}>Low</option>
                        <option value="medium" {{ request('priority') === 'medium' ? 'selected' : '' }}>Medium</option>
                        <option value="high" {{ request('priority') === 'high' ? 'selected' : '' }}>High</option>
                        <option value="urgent" {{ request('priority') === 'urgent' ? 'selected' : '' }}>Urgent</option>
                    </select>
                </div>

                <!-- Buttons -->
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="fas fa-filter"></i> Filter
                    </button>
                    <a href="{{ route('pic.task-requests.index') }}" class="btn btn-secondary">
                        <i class="fas fa-redo"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Task Requests Table -->
    @if($taskRequests->count() > 0)
        <div class="card shadow-sm">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Request Code</th>
                                <th>Task Title</th>
                                <th>Machine</th>
                                <th>Priority</th>
                                <th>Status</th>
                                <th>Requested Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($taskRequests as $task)
                            <tr>
                                <td>
                                    <strong>{{ $task->request_code }}</strong>
                                    <br>
                                    <small class="text-muted">{{ $task->task_type }}</small>
                                </td>
                                <td>{{ Str::limit($task->title, 40) }}</td>
                                <td>
                                    @if($task->machine)
                                        <i class="fas fa-cog"></i> {{ $task->machine->name }}
                                        <br>
                                        <small class="text-muted">{{ $task->machine->code }}</small>
                                    @else
                                        <span class="text-muted">General Task</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-{{ $task->priority_badge }}">
                                        {{ ucfirst($task->priority) }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-{{ $task->status_badge }}">
                                        {{ ucfirst($task->status) }}
                                    </span>
                                    @if($task->reviewer)
                                        <br>
                                        <small class="text-muted">by {{ $task->reviewer->name }}</small>
                                    @endif
                                </td>
                                <td>
                                    @if($task->requested_date)
                                        <small>{{ $task->requested_date->format('d M Y') }}</small>
                                    @else
                                        <small class="text-muted">ASAP</small>
                                    @endif
                                    <br>
                                    <small class="text-muted">Created: {{ $task->created_at->format('d M Y') }}</small>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('pic.task-requests.show', $task) }}"
                                           class="btn btn-outline-primary"
                                           title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        @if($task->isPending())
                                        <form action="{{ route('pic.task-requests.destroy', $task) }}"
                                              method="POST"
                                              class="d-inline"
                                              onsubmit="return confirm('Are you sure you want to cancel this request?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger" title="Cancel Request">
                                                <i class="fas fa-times"></i>
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
                        Showing {{ $taskRequests->firstItem() }} to {{ $taskRequests->lastItem() }} of {{ $taskRequests->total() }} entries
                    </div>
                    {{ $taskRequests->appends(request()->except('page'))->links() }}
                </div>
            </div>
        </div>
    @else
        <div class="card shadow-sm">
            <div class="card-body text-center py-5">
                <i class="fas fa-clipboard-list fa-4x text-muted mb-3"></i>
                <h5 class="text-muted">No task requests found</h5>
                <p class="text-muted">
                    @if(request()->hasAny(['search', 'status', 'priority']))
                        Try adjusting your filters
                    @else
                        You haven't requested any maintenance tasks yet
                    @endif
                </p>
                @if(request()->hasAny(['search', 'status', 'priority']))
                    <a href="{{ route('pic.task-requests.index') }}" class="btn btn-primary">
                        <i class="fas fa-redo"></i> Clear Filters
                    </a>
                @else
                    <a href="{{ route('pic.task-requests.create') }}" class="btn btn-success">
                        <i class="fas fa-plus"></i> Request New Task
                    </a>
                @endif
            </div>
        </div>
    @endif
</div>
@endsection
