@extends('layouts.pic')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="mb-4">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="{{ route('pic.incident-reports.index') }}">Incident Reports</a>
                </li>
                <li class="breadcrumb-item active">{{ $incidentReport->report_code }}</li>
            </ol>
        </nav>
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h2><i class="fas fa-file-alt"></i> Incident Report Details</h2>
                <p class="text-muted">{{ $incidentReport->report_code }}</p>
            </div>
            <div>
                @if($incidentReport->isPending())
                <a href="{{ route('pic.incident-reports.edit', $incidentReport) }}" class="btn btn-warning">
                    <i class="fas fa-edit"></i> Edit Report
                </a>
                @endif
                <a href="{{ route('pic.incident-reports.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to List
                </a>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Main Content -->
        <div class="col-lg-8">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-danger text-white">
                    <h5 class="mb-0"><i class="fas fa-info-circle"></i> Report Information</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="text-muted small">Report Code</label>
                            <p class="fw-bold">{{ $incidentReport->report_code }}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small">Status</label>
                            <p>
                                <span class="badge bg-{{ $incidentReport->status_badge }} fs-6">
                                    {{ ucfirst(str_replace('_', ' ', $incidentReport->status)) }}
                                </span>
                            </p>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="text-muted small">Reported At</label>
                            <p class="fw-bold">
                                <i class="fas fa-calendar"></i>
                                {{ $incidentReport->created_at->format('d M Y H:i') }}
                                <br>
                                <small class="text-muted">{{ $incidentReport->created_at->diffForHumans() }}</small>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small">Severity</label>
                            <p>
                                <span class="badge bg-{{ $incidentReport->severity_badge }} fs-6">
                                    {{ ucfirst($incidentReport->severity) }}
                                </span>
                            </p>
                        </div>
                    </div>

                    <hr>

                    <!-- Machine Details -->
                    <h6 class="text-danger mb-3"><i class="fas fa-cog"></i> Machine Information</h6>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="text-muted small">Machine Name</label>
                            <p class="fw-bold">{{ $incidentReport->machine->name }}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small">Machine Code</label>
                            <p class="fw-bold">{{ $incidentReport->machine->code }}</p>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="text-muted small">Location</label>
                            <p>
                                @if($incidentReport->machine->location)
                                    <i class="fas fa-map-marker-alt text-danger"></i>
                                    {{ $incidentReport->machine->location }}
                                @else
                                    <span class="text-muted">Not specified</span>
                                @endif
                            </p>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small">Machine Status</label>
                            <p>
                                <span class="badge bg-{{ $incidentReport->machine->status === 'operational' ? 'success' : 'danger' }}">
                                    {{ ucfirst($incidentReport->machine->status) }}
                                </span>
                            </p>
                        </div>
                    </div>

                    <hr>

                    <!-- Assigned Operators -->
                    @if($incidentReport->operators->count() > 0)
                    <h6 class="text-danger mb-3"><i class="fas fa-users"></i> Assigned Operators</h6>
                    <div class="mb-3">
                        <div class="d-flex flex-wrap gap-2">
                            @foreach($incidentReport->operators as $operator)
                            <span class="badge bg-info fs-6">
                                <i class="fas fa-user"></i> {{ $operator->name }}
                            </span>
                            @endforeach
                        </div>
                    </div>

                    @if($incidentReport->completed_by)
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i>
                        <strong>Completed by:</strong> {{ $incidentReport->completedBy->name }}
                        <br>
                        <small>{{ $incidentReport->completed_at->format('d M Y H:i') }}</small>
                    </div>
                    @endif

                    <hr>
                    @endif

                    <!-- Incident Details -->
                    <h6 class="text-danger mb-3"><i class="fas fa-clipboard-list"></i> Incident Details</h6>
                    <div class="mb-3">
                        <label class="text-muted small">Incident Type</label>
                        <p class="fw-bold">{{ $incidentReport->incident_type }}</p>
                    </div>

                    <div class="mb-3">
                        <label class="text-muted small">Title</label>
                        <p class="fw-bold">{{ $incidentReport->title }}</p>
                    </div>

                    <div class="mb-3">
                        <label class="text-muted small">Description</label>
                        <div class="bg-light p-3 rounded">
                            <p class="mb-0 text-break">{{ $incidentReport->description }}</p>
                        </div>
                    </div>

                    @if($incidentReport->attachments && count($incidentReport->attachments) > 0)
                    <div class="mb-3">
                        <label class="text-muted small">Attachments</label>
                        <div class="row g-2">
                            @foreach($incidentReport->attachments as $index => $attachment)
                            <div class="col-md-3">
                                <div class="card">
                                    @if(Str::endsWith($attachment, ['.jpg', '.jpeg', '.png', '.gif']))
                                        <img src="{{ asset('storage/' . $attachment) }}" class="card-img-top" alt="Attachment">
                                    @elseif(Str::endsWith($attachment, ['.mp4', '.mov', '.avi']))
                                        <video class="card-img-top" controls>
                                            <source src="{{ asset('storage/' . $attachment) }}" type="video/mp4">
                                        </video>
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

                    @if($incidentReport->admin_notes)
                    <hr>
                    <div class="mb-3">
                        <label class="text-muted small">Admin Notes</label>
                        <div class="alert alert-info">
                            <i class="fas fa-comment-dots"></i>
                            {{ $incidentReport->admin_notes }}
                        </div>
                    </div>
                    @endif

                    @if($incidentReport->resolution_notes)
                    <hr>
                    <div class="mb-3">
                        <label class="text-muted small">Resolution Notes</label>
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle"></i>
                            {{ $incidentReport->resolution_notes }}
                        </div>
                    </div>
                    @endif
                </div>

                <!-- Actions -->
                @if($incidentReport->isPending())
                <div class="card-footer bg-light">
                    <form action="{{ route('pic.incident-reports.destroy', $incidentReport) }}"
                          method="POST"
                          onsubmit="return confirm('Are you sure you want to delete this report? This action cannot be undone.')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-trash"></i> Delete Report
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
                    <h6 class="mb-0"><i class="fas fa-history"></i> Report Timeline</h6>
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
                                    <h6 class="mb-1">Report Submitted</h6>
                                    <p class="text-muted small mb-0">
                                        {{ $incidentReport->created_at->format('d M Y H:i') }}
                                    </p>
                                    <p class="small mb-0">By: {{ $incidentReport->reporter->name }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Assigned -->
                        @if($incidentReport->assigned_at)
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
                                    <p class="text-muted small mb-0">
                                        {{ $incidentReport->assigned_at->format('d M Y H:i') }}
                                    </p>
                                    @if($incidentReport->assignedUser)
                                    <p class="small mb-0">To: {{ $incidentReport->assignedUser->name }}</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @endif

                        <!-- In Progress -->
                        @if($incidentReport->isInProgress())
                        <div class="timeline-item mb-3">
                            <div class="d-flex">
                                <div class="me-3">
                                    <div class="bg-warning text-dark rounded-circle d-flex align-items-center justify-content-center"
                                         style="width: 40px; height: 40px;">
                                        <i class="fas fa-wrench"></i>
                                    </div>
                                </div>
                                <div>
                                    <h6 class="mb-1">Work in Progress</h6>
                                    <p class="text-muted small mb-0">Technician is working on this issue</p>
                                </div>
                            </div>
                        </div>
                        @endif

                        <!-- Pending -->
                        @if($incidentReport->isPending())
                        <div class="timeline-item">
                            <div class="d-flex">
                                <div class="me-3">
                                    <div class="bg-warning text-dark rounded-circle d-flex align-items-center justify-content-center"
                                         style="width: 40px; height: 40px;">
                                        <i class="fas fa-clock"></i>
                                    </div>
                                </div>
                                <div>
                                    <h6 class="mb-1">Awaiting Assignment</h6>
                                    <p class="text-muted small mb-0">Waiting for admin to assign technician</p>
                                </div>
                            </div>
                        </div>
                        @endif

                        <!-- Resolved -->
                        @if($incidentReport->resolved_at)
                        <div class="timeline-item">
                            <div class="d-flex">
                                <div class="me-3">
                                    <div class="bg-success text-white rounded-circle d-flex align-items-center justify-content-center"
                                         style="width: 40px; height: 40px;">
                                        <i class="fas fa-check"></i>
                                    </div>
                                </div>
                                <div>
                                    <h6 class="mb-1">Issue Resolved</h6>
                                    <p class="text-muted small mb-0">
                                        {{ $incidentReport->resolved_at->format('d M Y H:i') }}
                                    </p>
                                    @if($incidentReport->resolver)
                                    <p class="small mb-0">By: {{ $incidentReport->resolver->name }}</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Reporter Info -->
            <div class="card shadow-sm">
                <div class="card-header bg-secondary text-white">
                    <h6 class="mb-0"><i class="fas fa-user"></i> Reporter Information</h6>
                </div>
                <div class="card-body">
                    <div class="mb-2">
                        <small class="text-muted">Name</small>
                        <p class="mb-0 fw-bold">{{ $incidentReport->reporter->name }}</p>
                    </div>
                    <div class="mb-2">
                        <small class="text-muted">Email</small>
                        <p class="mb-0">{{ $incidentReport->reporter->email }}</p>
                    </div>
                    @if($incidentReport->reporter->employee_id)
                    <div class="mb-2">
                        <small class="text-muted">Employee ID</small>
                        <p class="mb-0">{{ $incidentReport->reporter->employee_id }}</p>
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
