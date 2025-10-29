@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2><i class="fas fa-file-alt"></i> Work Report Details</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.work-reports.index') }}">Work Reports</a></li>
                    <li class="breadcrumb-item active">{{ $workReport->report_code }}</li>
                </ol>
            </nav>
        </div>
        <div>
            @if($workReport->user_id === auth()->id() && in_array($workReport->status, ['draft', 'pending']))
            <a href="{{ route('admin.work-reports.edit', $workReport) }}" class="btn btn-warning">
                <i class="fas fa-edit"></i> Edit Report
            </a>
            @endif
            <a href="{{ route('admin.work-reports.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to List
            </a>
        </div>
    </div>

    <!-- Success Messages -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row">
        <!-- Report Profile Card -->
        <div class="col-lg-4 mb-4">
            <div class="card shadow-sm">
                <div class="card-body text-center">
                    <div class="report-icon-large bg-primary text-white mx-auto mb-3">
                        <i class="fas fa-file-alt fa-3x"></i>
                    </div>
                    <h4 class="mb-1">{{ $workReport->report_code }}</h4>
                    <p class="text-muted mb-3">Work Report</p>
                    
                    <div class="mb-3">
                        <span class="badge bg-{{ $workReport->statusBadge }} px-3 py-2 me-1">
                            <i class="fas fa-{{ $workReport->status == 'pending' ? 'clock' : ($workReport->status == 'approved' ? 'check-circle' : ($workReport->status == 'rejected' ? 'times-circle' : 'file')) }}"></i>
                            {{ ucfirst($workReport->status) }}
                        </span>
                        <span class="badge bg-{{ $workReport->conditionBadge }} px-3 py-2">
                            {{ ucfirst($workReport->machine_condition) }}
                        </span>
                    </div>

                    <hr>

                    <div class="text-start">
                        <!-- Job Info -->
                        <div class="mb-3">
                            <h6 class="text-muted mb-2"><i class="fas fa-wrench"></i> Maintenance Job</h6>
                            @if($workReport->job)
                                <p class="mb-1"><strong>{{ $workReport->job->job_code }}</strong></p>
                                <p class="mb-0">{{ $workReport->job->title }}</p>
                            @else
                                <p class="text-muted">No job linked</p>
                            @endif
                        </div>

                        <hr>

                        <!-- Machine Info -->
                        <div class="mb-3">
                            <h6 class="text-muted mb-2"><i class="fas fa-cogs"></i> Machine</h6>
                            @if($workReport->job && $workReport->job->machine)
                                <p class="mb-1"><strong>{{ $workReport->job->machine->code }}</strong></p>
                                <p class="mb-0">{{ $workReport->job->machine->name }}</p>
                            @else
                                <p class="text-muted">No machine assigned</p>
                            @endif
                        </div>

                        <hr>

                        <!-- Technician -->
                        <div class="mb-3">
                            <h6 class="text-muted mb-2"><i class="fas fa-user"></i> Submitted By</h6>
                            <p class="mb-0"><strong>{{ $workReport->user->name }}</strong></p>
                            <small class="text-muted">{{ $workReport->user->email }}</small>
                        </div>

                        @if($workReport->validator)
                        <hr>
                        <div class="mb-3">
                            <h6 class="text-muted mb-2"><i class="fas fa-user-check"></i> Reviewed By</h6>
                            <p class="mb-0"><strong>{{ $workReport->validator->name }}</strong></p>
                            <small class="text-muted">{{ $workReport->validated_at->format('d M Y, H:i') }}</small>
                        </div>
                        @endif
                    </div>

                    @if($workReport->status === 'pending' && auth()->user()->hasRole(['admin', 'super-admin']))
                    <hr>
                    <div class="d-grid gap-2">
                        <button type="button" 
                                class="btn btn-success btn-sm" 
                                data-bs-toggle="modal" 
                                data-bs-target="#validateModal">
                            <i class="fas fa-check"></i> Approve/Reject
                        </button>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Report Details -->
        <div class="col-lg-8">
            <!-- Work Schedule Card -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-clock"></i> Work Schedule</h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-md-4 mb-3">
                            <div class="p-3 bg-light rounded">
                                <h6 class="text-muted mb-2">Work Started</h6>
                                <h5 class="text-primary mb-0">{{ $workReport->work_start->format('d M Y') }}</h5>
                                <small class="text-muted">{{ $workReport->work_start->format('H:i') }}</small>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="p-3 bg-light rounded">
                                <h6 class="text-muted mb-2">Work Completed</h6>
                                <h5 class="text-success mb-0">{{ $workReport->work_end->format('d M Y') }}</h5>
                                <small class="text-muted">{{ $workReport->work_end->format('H:i') }}</small>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="p-3 bg-light rounded">
                                <h6 class="text-muted mb-2">Total Duration</h6>
                                <h5 class="text-info mb-0">{{ $workReport->workDurationFormatted }}</h5>
                                @if($workReport->downtime_minutes)
                                <small class="text-muted">Downtime: {{ $workReport->downtime_minutes }} min</small>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Work Details Card -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="fas fa-tasks"></i> Work Details</h5>
                </div>
                <div class="card-body">
                    <!-- Work Performed -->
                    <div class="mb-4">
                        <h6 class="text-muted mb-2">Work Performed</h6>
                        <p class="mb-0">{{ $workReport->work_performed }}</p>
                    </div>

                    @if($workReport->issues_found)
                    <hr>
                    <div class="mb-4">
                        <h6 class="text-muted mb-2">Issues Found</h6>
                        <div class="alert alert-warning mb-0">
                            <i class="fas fa-exclamation-triangle"></i>
                            {{ $workReport->issues_found }}
                        </div>
                    </div>
                    @endif

                    @if($workReport->recommendations)
                    <hr>
                    <div class="mb-4">
                        <h6 class="text-muted mb-2">Recommendations</h6>
                        <div class="alert alert-info mb-0">
                            <i class="fas fa-lightbulb"></i>
                            {{ $workReport->recommendations }}
                        </div>
                    </div>
                    @endif

                    <hr>
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="text-muted mb-2">Machine Condition After Work</h6>
                            <span class="badge bg-{{ $workReport->conditionBadge }} px-3 py-2">
                                {{ ucfirst($workReport->machine_condition) }}
                            </span>
                        </div>
                        @if($workReport->downtime_minutes)
                        <div class="col-md-6">
                            <h6 class="text-muted mb-2">Machine Downtime</h6>
                            <p class="mb-0"><strong>{{ $workReport->downtime_minutes }} minutes</strong></p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Admin Comments -->
            @if($workReport->admin_comments)
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-{{ $workReport->status === 'approved' ? 'success' : 'danger' }} text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-comment"></i> 
                        Admin {{ $workReport->status === 'approved' ? 'Approval' : 'Rejection' }} Comments
                    </h5>
                </div>
                <div class="card-body">
                    <p class="mb-0">{{ $workReport->admin_comments }}</p>
                </div>
            </div>
            @endif

            <!-- Attachments -->
            @if($workReport->attachments && count($workReport->attachments) > 0)
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="fas fa-images"></i> Attachments ({{ count($workReport->attachments) }} photos)</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        @foreach($workReport->attachments as $attachment)
                        <div class="col-md-4">
                            <div class="card">
                                <a href="{{ Storage::url($attachment['path']) }}" target="_blank">
                                    <img src="{{ Storage::url($attachment['path']) }}" 
                                         class="card-img-top" 
                                         alt="Attachment"
                                         style="height: 200px; object-fit: cover;">
                                </a>
                                <div class="card-body p-2">
                                    <small class="text-muted">{{ $attachment['original_name'] }}</small>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif

            <!-- Timeline -->
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="fas fa-history"></i> Report Timeline</h5>
                </div>
                <div class="card-body">
                    <div class="timeline">
                        <!-- Created -->
                        <div class="timeline-item">
                            <div class="timeline-marker bg-primary">
                                <i class="fas fa-plus text-white"></i>
                            </div>
                            <div class="timeline-content">
                                <h6 class="mb-1">Report Created</h6>
                                <p class="mb-1 text-muted">Created by {{ $workReport->user->name }}</p>
                                <small class="text-muted">
                                    <i class="fas fa-clock"></i>
                                    {{ $workReport->created_at->format('d M Y, H:i') }}
                                </small>
                            </div>
                        </div>

                        <!-- Work Period -->
                        <div class="timeline-item">
                            <div class="timeline-marker bg-info">
                                <i class="fas fa-wrench text-white"></i>
                            </div>
                            <div class="timeline-content">
                                <h6 class="mb-1">Work Performed</h6>
                                <p class="mb-1 text-muted">Duration: {{ $workReport->workDurationFormatted }}</p>
                                <small class="text-muted">
                                    <i class="fas fa-clock"></i>
                                    {{ $workReport->work_start->format('d M Y, H:i') }} - {{ $workReport->work_end->format('d M Y, H:i') }}
                                </small>
                            </div>
                        </div>

                        @if($workReport->validated_at)
                        <!-- Validated -->
                        <div class="timeline-item">
                            <div class="timeline-marker bg-{{ $workReport->status === 'approved' ? 'success' : 'danger' }}">
                                <i class="fas fa-{{ $workReport->status === 'approved' ? 'check' : 'times' }} text-white"></i>
                            </div>
                            <div class="timeline-content">
                                <h6 class="mb-1">Report {{ ucfirst($workReport->status) }}</h6>
                                <p class="mb-1 text-muted">Reviewed by {{ $workReport->validator->name }}</p>
                                <small class="text-muted">
                                    <i class="fas fa-clock"></i>
                                    {{ $workReport->validated_at->format('d M Y, H:i') }}
                                </small>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Validate Modal -->
@if($workReport->status === 'pending' && auth()->user()->hasRole(['admin', 'super-admin']))
<div class="modal fade" id="validateModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.work-reports.validate', $workReport) }}" method="POST">
                @csrf
                @method('PATCH')
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="fas fa-check-circle"></i> Review Work Report</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> 
                        Reviewing report: <strong>{{ $workReport->report_code }}</strong>
                    </div>

                    <div class="mb-3">
                        <label for="status" class="form-label">Decision <span class="text-danger">*</span></label>
                        <select class="form-select" id="status" name="status" required>
                            <option value="">Select Decision</option>
                            <option value="approved">Approve Report</option>
                            <option value="rejected">Reject Report</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="admin_comments" class="form-label">Comments</label>
                        <textarea class="form-control" 
                                  id="admin_comments" 
                                  name="admin_comments" 
                                  rows="4"
                                  placeholder="Optional feedback for the technician..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-check"></i> Submit Review
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

<style>
.report-icon-large {
    width: 120px;
    height: 120px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
}

/* Timeline Styles */
.timeline {
    position: relative;
    padding-left: 50px;
}

.timeline::before {
    content: '';
    position: absolute;
    left: 20px;
    top: 0;
    bottom: 0;
    width: 2px;
    background: #dee2e6;
}

.timeline-item {
    position: relative;
    margin-bottom: 30px;
}

.timeline-item:last-child {
    margin-bottom: 0;
}

.timeline-marker {
    position: absolute;
    left: -30px;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 3px solid white;
    box-shadow: 0 0 0 2px #dee2e6;
}

.timeline-content {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.timeline-content h6 {
    color: #495057;
    font-weight: 600;
}
</style>
@endsection