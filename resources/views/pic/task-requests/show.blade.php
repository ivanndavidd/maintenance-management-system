@extends('layouts.pic')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="mb-4">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="{{ route('pic.task-requests.index') }}">Task Requests</a>
                </li>
                <li class="breadcrumb-item active">{{ $taskRequest->request_code }}</li>
            </ol>
        </nav>
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h2><i class="fas fa-file-alt"></i> Task Request Details</h2>
                <p class="text-muted">{{ $taskRequest->request_code }}</p>
            </div>
            <a href="{{ route('pic.task-requests.index') }}" class="btn btn-secondary">
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
                        <label class="text-muted small">Admin Review Notes</label>
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
                        </div>
                    </div>
                    @endif
                </div>

                <!-- Actions -->
                @if($taskRequest->isPending())
                <div class="card-footer bg-light">
                    <form action="{{ route('pic.task-requests.destroy', $taskRequest) }}"
                          method="POST"
                          onsubmit="return confirm('Are you sure you want to cancel this request? This action cannot be undone.')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-times"></i> Cancel Request
                        </button>
                    </form>
                </div>
                @endif
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Status Timeline -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-info text-white">
                    <h6 class="mb-0"><i class="fas fa-history"></i> Request Timeline</h6>
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
                                    <h6 class="mb-1">Request Submitted</h6>
                                    <p class="text-muted small mb-0">
                                        {{ $taskRequest->created_at->format('d M Y H:i') }}
                                    </p>
                                    <p class="small mb-0">By: {{ $taskRequest->requester->name }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Pending -->
                        @if($taskRequest->isPending())
                        <div class="timeline-item mb-3">
                            <div class="d-flex">
                                <div class="me-3">
                                    <div class="bg-warning text-dark rounded-circle d-flex align-items-center justify-content-center"
                                         style="width: 40px; height: 40px;">
                                        <i class="fas fa-clock"></i>
                                    </div>
                                </div>
                                <div>
                                    <h6 class="mb-1">Awaiting Admin Review</h6>
                                    <p class="text-muted small mb-0">Pending approval</p>
                                </div>
                            </div>
                        </div>
                        @endif

                        <!-- Reviewed (Approved/Rejected) -->
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
                                    <h6 class="mb-1">
                                        Request {{ $taskRequest->isApproved() ? 'Approved' : 'Rejected' }}
                                    </h6>
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
                                    <h6 class="mb-1">Assigned to Technician</h6>
                                    <p class="small mb-0">{{ $taskRequest->assignedUser->name }}</p>
                                </div>
                            </div>
                        </div>
                        @endif

                        <!-- Completed -->
                        @if($taskRequest->isCompleted())
                        <div class="timeline-item">
                            <div class="d-flex">
                                <div class="me-3">
                                    <div class="bg-success text-white rounded-circle d-flex align-items-center justify-content-center"
                                         style="width: 40px; height: 40px;">
                                        <i class="fas fa-check-double"></i>
                                    </div>
                                </div>
                                <div>
                                    <h6 class="mb-1">Task Completed</h6>
                                    <p class="text-muted small mb-0">Work finished successfully</p>
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

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

            <!-- Requester Info -->
            <div class="card shadow-sm">
                <div class="card-header bg-secondary text-white">
                    <h6 class="mb-0"><i class="fas fa-user"></i> Requester Information</h6>
                </div>
                <div class="card-body">
                    <div class="mb-2">
                        <small class="text-muted">Name</small>
                        <p class="mb-0 fw-bold">{{ $taskRequest->requester->name }}</p>
                    </div>
                    <div class="mb-2">
                        <small class="text-muted">Email</small>
                        <p class="mb-0">{{ $taskRequest->requester->email }}</p>
                    </div>
                    @if($taskRequest->requester->employee_id)
                    <div class="mb-2">
                        <small class="text-muted">Employee ID</small>
                        <p class="mb-0">{{ $taskRequest->requester->employee_id }}</p>
                    </div>
                    @endif
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
