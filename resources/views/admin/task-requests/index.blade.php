@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="mb-4">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h2><i class="fas fa-tasks"></i> Task Requests Management</h2>
                <p class="text-muted">Review and manage task requests from PICs</p>
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
                            <h3 class="mb-0">{{ \App\Models\TaskRequest::pending()->count() }}</h3>
                        </div>
                        <i class="fas fa-clock fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-0">Approved</h6>
                            <h3 class="mb-0">{{ \App\Models\TaskRequest::approved()->count() }}</h3>
                        </div>
                        <i class="fas fa-check fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-danger text-white shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-0">Urgent</h6>
                            <h3 class="mb-0">{{ \App\Models\TaskRequest::urgent()->count() }}</h3>
                        </div>
                        <i class="fas fa-fire fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-0">Assigned</h6>
                            <h3 class="mb-0">{{ \App\Models\TaskRequest::assigned()->count() }}</h3>
                        </div>
                        <i class="fas fa-user-check fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.task-requests.index') }}" class="row g-3">
                <!-- Search -->
                <div class="col-md-3">
                    <label class="form-label">Search</label>
                    <input type="text" name="search" class="form-control"
                           placeholder="Code, title, machine, requester..."
                           value="{{ request('search') }}">
                </div>

                <!-- Status Filter -->
                <div class="col-md-2">
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
                <div class="col-md-2">
                    <label class="form-label">Priority</label>
                    <select name="priority" class="form-select">
                        <option value="">All Priority</option>
                        <option value="low" {{ request('priority') === 'low' ? 'selected' : '' }}>Low</option>
                        <option value="medium" {{ request('priority') === 'medium' ? 'selected' : '' }}>Medium</option>
                        <option value="high" {{ request('priority') === 'high' ? 'selected' : '' }}>High</option>
                        <option value="urgent" {{ request('priority') === 'urgent' ? 'selected' : '' }}>Urgent</option>
                    </select>
                </div>

                <!-- Requester Filter -->
                <div class="col-md-3">
                    <label class="form-label">Requester (PIC)</label>
                    <select name="requester_id" class="form-select">
                        <option value="">All Requesters</option>
                        @foreach($requesters as $requester)
                        <option value="{{ $requester->id }}" {{ request('requester_id') == $requester->id ? 'selected' : '' }}>
                            {{ $requester->name }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <!-- Buttons -->
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="fas fa-filter"></i> Filter
                    </button>
                    <a href="{{ route('admin.task-requests.index') }}" class="btn btn-secondary">
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
                                <th>Requester</th>
                                <th>Requested Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($taskRequests as $request)
                            <tr>
                                <td>
                                    <strong>{{ $request->request_code }}</strong>
                                    <br>
                                    <small class="text-muted">{{ $request->task_type }}</small>
                                </td>
                                <td>{{ Str::limit($request->title, 40) }}</td>
                                <td>
                                    @if($request->machine)
                                        <i class="fas fa-cog"></i> {{ $request->machine->name }}
                                        <br>
                                        <small class="text-muted">{{ $request->machine->code }}</small>
                                    @else
                                        <span class="text-muted">General Task</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-{{ $request->priority_badge }}">
                                        {{ ucfirst($request->priority) }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-{{ $request->status_badge }}">
                                        {{ ucfirst($request->status) }}
                                    </span>
                                    @if($request->reviewer)
                                        <br>
                                        <small class="text-muted">by {{ $request->reviewer->name }}</small>
                                    @endif
                                    @if($request->assignedUser)
                                        <br>
                                        <small class="text-muted">
                                            <i class="fas fa-user"></i> {{ $request->assignedUser->name }}
                                        </small>
                                    @endif
                                </td>
                                <td>
                                    {{ $request->requester->name }}
                                    <br>
                                    <small class="text-muted">{{ $request->requester->employee_id }}</small>
                                </td>
                                <td>
                                    @if($request->requested_date)
                                        {{ $request->requested_date->format('d M Y') }}
                                        <br>
                                        <small class="text-muted">Preferred date</small>
                                    @else
                                        <small class="text-muted">ASAP</small>
                                        <br>
                                        <small class="text-muted">{{ $request->created_at->format('d M Y') }}</small>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('admin.task-requests.show', $request) }}"
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
                    @if(request()->hasAny(['search', 'status', 'priority', 'requester_id']))
                        Try adjusting your filters
                    @else
                        No task requests have been submitted yet
                    @endif
                </p>
                @if(request()->hasAny(['search', 'status', 'priority', 'requester_id']))
                    <a href="{{ route('admin.task-requests.index') }}" class="btn btn-primary">
                        <i class="fas fa-redo"></i> Clear Filters
                    </a>
                @endif
            </div>
        </div>
    @endif
</div>
@endsection
