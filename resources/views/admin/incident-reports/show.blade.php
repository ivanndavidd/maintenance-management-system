@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="mb-4">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="{{ route('admin.incident-reports.index') }}">Incident Reports</a>
                </li>
                <li class="breadcrumb-item active">{{ $incidentReport->report_code }}</li>
            </ol>
        </nav>
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h2><i class="fas fa-exclamation-triangle"></i> Incident Report Details</h2>
                <p class="text-muted">{{ $incidentReport->report_code }}</p>
            </div>
            <a href="{{ route('admin.incident-reports.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to List
            </a>
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
                            @foreach($incidentReport->attachments as $attachment)
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
            </div>

            <!-- Reporter Info Card -->
            <div class="card shadow-sm">
                <div class="card-header bg-secondary text-white">
                    <h6 class="mb-0"><i class="fas fa-user"></i> Reporter Information</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <small class="text-muted">Name</small>
                            <p class="mb-0 fw-bold">{{ $incidentReport->reporter->name }}</p>
                        </div>
                        <div class="col-md-4">
                            <small class="text-muted">Email</small>
                            <p class="mb-0">{{ $incidentReport->reporter->email }}</p>
                        </div>
                        <div class="col-md-4">
                            <small class="text-muted">Employee ID</small>
                            <p class="mb-0">{{ $incidentReport->reporter->employee_id ?? '-' }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Assign Technician -->
            @if($incidentReport->isPending() || $incidentReport->isAssigned())
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <h6 class="mb-0"><i class="fas fa-user-plus"></i> Assign Technician</h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.incident-reports.assign', $incidentReport) }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Technician</label>
                            <select name="assigned_to" class="form-select @error('assigned_to') is-invalid @enderror" required>
                                <option value="">-- Select Technician --</option>
                                @foreach($technicians as $tech)
                                <option value="{{ $tech->id }}" {{ $incidentReport->assigned_to == $tech->id ? 'selected' : '' }}>
                                    {{ $tech->name }}
                                </option>
                                @endforeach
                            </select>
                            @error('assigned_to')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Notes (optional)</label>
                            <textarea name="admin_notes" class="form-control" rows="3" placeholder="Instructions for the technician...">{{ old('admin_notes', $incidentReport->admin_notes) }}</textarea>
                        </div>

                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-user-check"></i> {{ $incidentReport->assigned_to ? 'Update Assignment' : 'Assign Technician' }}
                        </button>
                    </form>
                </div>
            </div>
            @endif

            <!-- Update Status -->
            @if(!$incidentReport->isPending())
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-warning text-dark">
                    <h6 class="mb-0"><i class="fas fa-sync-alt"></i> Update Status</h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.incident-reports.update-status', $incidentReport) }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">New Status</label>
                            <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                                <option value="assigned" {{ $incidentReport->status == 'assigned' ? 'selected' : '' }}>Assigned</option>
                                <option value="in_progress" {{ $incidentReport->status == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                                <option value="resolved" {{ $incidentReport->status == 'resolved' ? 'selected' : '' }}>Resolved</option>
                                <option value="closed" {{ $incidentReport->status == 'closed' ? 'selected' : '' }}>Closed</option>
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Notes</label>
                            <textarea name="notes" class="form-control" rows="3" placeholder="Resolution notes or updates..."></textarea>
                        </div>

                        <button type="submit" class="btn btn-warning w-100">
                            <i class="fas fa-save"></i> Update Status
                        </button>
                    </form>
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
                                    <h6 class="mb-1">Reported</h6>
                                    <p class="text-muted small mb-0">
                                        {{ $incidentReport->created_at->format('d M Y H:i') }}
                                    </p>
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
                                    <h6 class="mb-1">Assigned</h6>
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
                                    <h6 class="mb-1">Resolved</h6>
                                    <p class="text-muted small mb-0">
                                        {{ $incidentReport->resolved_at->format('d M Y H:i') }}
                                    </p>
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
