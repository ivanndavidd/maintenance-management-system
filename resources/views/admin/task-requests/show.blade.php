@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="mb-4">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="{{ route('admin.task-requests.index') }}">Task Requests</a>
                </li>
                <li class="breadcrumb-item active">{{ $taskRequest->request_code }}</li>
            </ol>
        </nav>
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h2><i class="fas fa-file-alt"></i> Task Request Details</h2>
                <p class="text-muted">{{ $taskRequest->request_code }}</p>
            </div>
            <a href="{{ route('admin.task-requests.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to List
            </a>
        </div>
    </div>

    <div class="row">
        <!-- Main Content -->
        <div class="col-lg-8">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="fas fa-info-circle"></i> Request Information</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="text-muted small">Request Code</label>
                            <p class="fw-bold">{{ $taskRequest->request_code }}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small">Status</label>
                            <p>
                                <span class="badge bg-{{ $taskRequest->status_badge }} fs-6">
                                    {{ ucfirst($taskRequest->status) }}
                                </span>
                            </p>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="text-muted small">Requested At</label>
                            <p class="fw-bold">
                                <i class="fas fa-calendar"></i>
                                {{ $taskRequest->created_at->format('d M Y H:i') }}
                                <br>
                                <small class="text-muted">{{ $taskRequest->created_at->diffForHumans() }}</small>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small">Priority</label>
                            <p>
                                <span class="badge bg-{{ $taskRequest->priority_badge }} fs-6">
                                    {{ ucfirst($taskRequest->priority) }}
                                </span>
                            </p>
                        </div>
                    </div>

                    @if($taskRequest->requested_date)
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="text-muted small">Preferred Completion Date</label>
                            <p class="fw-bold">
                                <i class="fas fa-calendar-check"></i>
                                {{ $taskRequest->requested_date->format('d M Y') }}
                            </p>
                        </div>
                    </div>
                    @endif

                    <hr>

                    <!-- Machine Details -->
                    @if($taskRequest->machine)
                    <h6 class="text-success mb-3"><i class="fas fa-cog"></i> Machine Information</h6>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="text-muted small">Machine Name</label>
                            <p class="fw-bold">{{ $taskRequest->machine->name }}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small">Machine Code</label>
                            <p class="fw-bold">{{ $taskRequest->machine->code }}</p>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="text-muted small">Location</label>
                            <p>
                                @if($taskRequest->machine->location)
                                    <i class="fas fa-map-marker-alt text-danger"></i>
                                    {{ $taskRequest->machine->location }}
                                @else
                                    <span class="text-muted">Not specified</span>
                                @endif
                            </p>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small">Machine Status</label>
                            <p>
                                <span class="badge bg-{{ $taskRequest->machine->status === 'operational' ? 'success' : 'danger' }}">
                                    {{ ucfirst($taskRequest->machine->status) }}
                                </span>
                            </p>
                        </div>
                    </div>
                    <hr>
                    @endif

                    <!-- Task Details -->
                    <h6 class="text-success mb-3"><i class="fas fa-clipboard-list"></i> Task Details</h6>
                    <div class="mb-3">
                        <label class="text-muted small">Task Type</label>
                        <p class="fw-bold">{{ $taskRequest->task_type }}</p>
                    </div>

                    <div class="mb-3">
                        <label class="text-muted small">Title</label>
                        <p class="fw-bold">{{ $taskRequest->title }}</p>
                    </div>

                    <div class="mb-3">
                        <label class="text-muted small">Description</label>
                        <div class="bg-light p-3 rounded">
                            <p class="mb-0 text-break">{{ $taskRequest->description }}</p>
                        </div>
                    </div>

                    @if($taskRequest->attachments && count($taskRequest->attachments) > 0)
                    <div class="mb-3">
                        <label class="text-muted small">Attachments</label>
                        <div class="row g-2">
                            @foreach($taskRequest->attachments as $attachment)
                            <div class="col-md-3">
                                <div class="card">
                                    @if(Str::endsWith($attachment, ['.jpg', '.jpeg', '.png', '.gif']))
                                        <img src="{{ asset('storage/' . $attachment) }}" class="card-img-top" alt="Attachment">
                                    @else
                                        <div class="card-body text-center">
                                            <i class="fas fa-file fa-3x text-muted"></i>
                                        </div>
                                    @endif
                                    <div class="card-body p-2">
                                        <a href="{{ asset('storage/' . $attachment) }}" target="_blank" class="btn btn-sm btn-outline-primary w-100">
                                            <i class="fas fa-download"></i> View
                                        </a>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    @if($taskRequest->review_notes)
                    <hr>
                    <div class="mb-3">
                        <label class="text-muted small">Review Notes</label>
                        <div class="alert alert-{{ $taskRequest->isApproved() ? 'success' : ($taskRequest->isRejected() ? 'danger' : 'info') }}">
                            <i class="fas fa-comment-dots"></i>
                            {{ $taskRequest->review_notes }}
                        </div>
                    </div>
                    @endif

                    @if($taskRequest->maintenanceJob)
                    <hr>
                    <div class="mb-3">
                        <label class="text-muted small">Linked Maintenance Job</label>
                        <div class="alert alert-info">
                            <i class="fas fa-link"></i>
                            This request has been converted to a maintenance job.
                            <br>
                            <strong>Job ID:</strong> {{ $taskRequest->maintenanceJob->id }}
                            <br>
                            <a href="{{ route('admin.jobs.show', $taskRequest->maintenanceJob) }}" class="btn btn-sm btn-primary mt-2">
                                <i class="fas fa-eye"></i> View Maintenance Job
                            </a>
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Requester Info Card -->
            <div class="card shadow-sm">
                <div class="card-header bg-secondary text-white">
                    <h6 class="mb-0"><i class="fas fa-user"></i> Requester Information</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <small class="text-muted">Name</small>
                            <p class="mb-0 fw-bold">{{ $taskRequest->requester->name }}</p>
                        </div>
                        <div class="col-md-4">
                            <small class="text-muted">Email</small>
                            <p class="mb-0">{{ $taskRequest->requester->email }}</p>
                        </div>
                        <div class="col-md-4">
                            <small class="text-muted">Employee ID</small>
                            <p class="mb-0">{{ $taskRequest->requester->employee_id ?? '-' }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Approval Actions -->
            @if($taskRequest->isPending())
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-success text-white">
                    <h6 class="mb-0"><i class="fas fa-check-circle"></i> Review Request</h6>
                </div>
                <div class="card-body">
                    <!-- Approve -->
                    <form action="{{ route('admin.task-requests.approve', $taskRequest) }}" method="POST" class="mb-3">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Approval Notes (optional)</label>
                            <textarea name="review_notes" class="form-control" rows="2" placeholder="Optional notes for approval..."></textarea>
                        </div>
                        <button type="submit" class="btn btn-success w-100">
                            <i class="fas fa-check"></i> Approve Request
                        </button>
                    </form>

                    <!-- Reject -->
                    <form action="{{ route('admin.task-requests.reject', $taskRequest) }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Rejection Reason <span class="text-danger">*</span></label>
                            <textarea name="review_notes" class="form-control @error('review_notes') is-invalid @enderror" rows="2" placeholder="Reason for rejection..." required></textarea>
                            @error('review_notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <button type="submit" class="btn btn-danger w-100">
                            <i class="fas fa-times"></i> Reject Request
                        </button>
                    </form>
                </div>
            </div>
            @endif

            <!-- Assign Technician -->
            @if($taskRequest->isApproved() && !$taskRequest->job_id)
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <h6 class="mb-0"><i class="fas fa-user-plus"></i> Assign Technician</h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.task-requests.assign', $taskRequest) }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Technician</label>
                            <select name="assigned_to" class="form-select @error('assigned_to') is-invalid @enderror" required>
                                <option value="">-- Select Technician --</option>
                                @foreach($technicians as $tech)
                                <option value="{{ $tech->id }}" {{ $taskRequest->assigned_to == $tech->id ? 'selected' : '' }}>
                                    {{ $tech->name }}
                                </option>
                                @endforeach
                            </select>
                            @error('assigned_to')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-user-check"></i> Assign Technician
                        </button>
                    </form>
                </div>
            </div>

            <!-- Convert to Maintenance Job -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-warning text-dark">
                    <h6 class="mb-0"><i class="fas fa-wrench"></i> Convert to Job</h6>
                </div>
                <div class="card-body">
                    <p class="small text-muted">
                        Convert this request to a formal maintenance job that will be tracked in the system.
                    </p>
                    <form action="{{ route('admin.task-requests.convert-to-job', $taskRequest) }}" method="POST"
                          onsubmit="return confirm('Convert this task request to a maintenance job?')">
                        @csrf
                        <button type="submit" class="btn btn-warning w-100">
                            <i class="fas fa-exchange-alt"></i> Convert to Maintenance Job
                        </button>
                    </form>
                </div>
            </div>
            @endif

            <!-- Assigned Operators -->
            @if($taskRequest->operators->count() > 0)
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <h6 class="mb-0"><i class="fas fa-users"></i> Assigned Operators</h6>
                </div>
                <div class="card-body">
                    @foreach($taskRequest->operators as $operator)
                    <div class="d-flex align-items-center mb-2 p-2 bg-light rounded">
                        <div class="flex-shrink-0">
                            <div class="bg-success text-white rounded-circle d-flex align-items-center justify-content-center"
                                 style="width: 35px; height: 35px; font-size: 12px; font-weight: bold;">
                                {{ strtoupper(substr($operator->name, 0, 2)) }}
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-2">
                            <div class="fw-bold">{{ $operator->name }}</div>
                            <small class="text-muted">
                                <i class="fas fa-id-badge"></i> {{ $operator->employee_id ?? 'No ID' }}
                            </small>
                        </div>
                    </div>
                    @endforeach

                    @if($taskRequest->completedBy)
                    <div class="mt-3 pt-3 border-top">
                        <small class="text-muted d-block mb-2">Completed by:</small>
                        <div class="d-flex align-items-center p-2 bg-success bg-opacity-10 rounded">
                            <div class="flex-shrink-0">
                                <div class="bg-success text-white rounded-circle d-flex align-items-center justify-content-center"
                                     style="width: 35px; height: 35px; font-size: 12px; font-weight: bold;">
                                    {{ strtoupper(substr($taskRequest->completedBy->name, 0, 2)) }}
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-2">
                                <div class="fw-bold">{{ $taskRequest->completedBy->name }}</div>
                                <small class="text-muted">
                                    {{ $taskRequest->completed_at->format('d M Y H:i') }}
                                </small>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
            @endif

            <!-- Status Timeline -->
            <div class="card shadow-sm">
                <div class="card-header bg-info text-white">
                    <h6 class="mb-0"><i class="fas fa-history"></i> Timeline</h6>
                </div>
                <div class="card-body">
                    <div class="timeline">
                        <!-- Submitted -->
                        <div class="timeline-item mb-3">
                            <div class="d-flex">
                                <div class="me-3">
                                    <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center"
                                         style="width: 40px; height: 40px;">
                                        <i class="fas fa-paper-plane"></i>
                                    </div>
                                </div>
                                <div>
                                    <h6 class="mb-1">Submitted</h6>
                                    <p class="text-muted small mb-0">
                                        {{ $taskRequest->created_at->format('d M Y H:i') }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Reviewed -->
                        @if($taskRequest->reviewed_at)
                        <div class="timeline-item mb-3">
                            <div class="d-flex">
                                <div class="me-3">
                                    <div class="bg-{{ $taskRequest->isApproved() ? 'success' : 'danger' }} text-white rounded-circle d-flex align-items-center justify-content-center"
                                         style="width: 40px; height: 40px;">
                                        <i class="fas fa-{{ $taskRequest->isApproved() ? 'check' : 'times' }}"></i>
                                    </div>
                                </div>
                                <div>
                                    <h6 class="mb-1">{{ $taskRequest->isApproved() ? 'Approved' : 'Rejected' }}</h6>
                                    <p class="text-muted small mb-0">
                                        {{ $taskRequest->reviewed_at->format('d M Y H:i') }}
                                    </p>
                                    @if($taskRequest->reviewer)
                                    <p class="small mb-0">By: {{ $taskRequest->reviewer->name }}</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @endif

                        <!-- Assigned -->
                        @if($taskRequest->assignedUser)
                        <div class="timeline-item mb-3">
                            <div class="d-flex">
                                <div class="me-3">
                                    <div class="bg-info text-white rounded-circle d-flex align-items-center justify-content-center"
                                         style="width: 40px; height: 40px;">
                                        <i class="fas fa-user-check"></i>
                                    </div>
                                </div>
                                <div>
                                    <h6 class="mb-1">Assigned</h6>
                                    <p class="small mb-0">To: {{ $taskRequest->assignedUser->name }}</p>
                                </div>
                            </div>
                        </div>
                        @endif

                        <!-- Converted to Job -->
                        @if($taskRequest->job_id)
                        <div class="timeline-item">
                            <div class="d-flex">
                                <div class="me-3">
                                    <div class="bg-success text-white rounded-circle d-flex align-items-center justify-content-center"
                                         style="width: 40px; height: 40px;">
                                        <i class="fas fa-check-double"></i>
                                    </div>
                                </div>
                                <div>
                                    <h6 class="mb-1">Converted to Job</h6>
                                    <p class="text-muted small mb-0">Maintenance job created</p>
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.timeline-item::before {
    content: '';
    position: absolute;
    left: 20px;
    top: 50px;
    width: 2px;
    height: calc(100% - 50px);
    background: #dee2e6;
}

.timeline-item:last-child::before {
    display: none;
}
</style>
@endsection
