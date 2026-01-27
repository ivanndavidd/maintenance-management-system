@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2><i class="fas fa-tasks"></i> Job Details</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.jobs.index') }}">Maintenance Jobs</a></li>
                    <li class="breadcrumb-item active">{{ $job->job_code }}</li>
                </ol>
            </nav>
        </div>
        <div>
            <a href="{{ route('admin.jobs.edit', $job) }}" class="btn btn-warning">
                <i class="fas fa-edit"></i> Edit Job
            </a>
            <a href="{{ route('admin.jobs.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to List
            </a>
        </div>
    </div>

    <!-- Success Messages -->
    <div class="row">
        <!-- Job Profile Card -->
        <div class="col-lg-4 mb-4">
            <div class="card shadow-sm">
                <div class="card-body text-center">
                    <div class="job-icon-large bg-primary text-white mx-auto mb-3">
                        <i class="fas fa-tasks fa-3x"></i>
                    </div>
                    <h4 class="mb-1">{{ $job->title }}</h4>
                    <p class="text-muted mb-3">{{ $job->job_code }}</p>
                    
                    <div class="mb-3">
                        <span class="badge bg-{{ $job->statusBadge }} px-3 py-2 me-1">
                            <i class="fas fa-{{ $job->status == 'pending' ? 'clock' : ($job->status == 'in_progress' ? 'spinner' : ($job->status == 'completed' ? 'check-circle' : 'times-circle')) }}"></i>
                            {{ ucfirst(str_replace('_', ' ', $job->status)) }}
                        </span>
                        <span class="badge bg-{{ $job->priorityBadge }} px-3 py-2 me-1">
                            {{ ucfirst($job->priority) }}
                        </span>
                        <span class="badge bg-{{ $job->typeBadge }} px-3 py-2">
                            {{ ucfirst($job->type) }}
                        </span>
                    </div>

                    @if($job->isOverdue())
                        <div class="alert alert-danger mb-3">
                            <i class="fas fa-exclamation-triangle"></i>
                            <strong>OVERDUE!</strong> This job is past its scheduled date.
                        </div>
                    @endif

                    <hr>

                    <div class="text-start">
                        <!-- Machine Info -->
                        <div class="mb-3">
                            <h6 class="text-muted mb-2"><i class="fas fa-cogs"></i> Machine</h6>
                            @if($job->machine)
                                <p class="mb-1"><strong>{{ $job->machine->code }}</strong></p>
                                <p class="mb-1">{{ $job->machine->name }}</p>
                                @if($job->machine->category)
                                    <small class="text-muted">Category: {{ $job->machine->category->name }}</small>
                                @endif
                            @else
                                <p class="text-muted">No machine assigned</p>
                            @endif
                        </div>

                        <hr>

                        <!-- Assigned User -->
                        <div class="mb-3">
                            <h6 class="text-muted mb-2"><i class="fas fa-user"></i> Assigned To</h6>
                            @if($job->assignedUser)
                                <p class="mb-0"><strong>{{ $job->assignedUser->name }}</strong></p>
                                <small class="text-muted">{{ $job->assignedUser->email }}</small>
                            @else
                                <span class="badge bg-secondary">Unassigned</span>
                            @endif
                        </div>

                        <hr>

                        <!-- Created By -->
                        <div class="mb-3">
                            <h6 class="text-muted mb-2"><i class="fas fa-user-circle"></i> Created By</h6>
                            <p class="mb-0"><strong>{{ $job->creator->name }}</strong></p>
                            <small class="text-muted">{{ $job->created_at->format('d M Y, H:i') }}</small>
                        </div>
                    </div>

                    <hr>

                    <div class="d-grid gap-2">
                        <button type="button" 
                                class="btn btn-warning btn-sm" 
                                data-bs-toggle="modal" 
                                data-bs-target="#statusModal">
                            <i class="fas fa-exchange-alt"></i> Update Status
                        </button>
                        <a href="{{ route('admin.jobs.edit', $job) }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-edit"></i> Edit Job Details
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Job Details & Timeline -->
        <div class="col-lg-8">
            <!-- Job Metrics -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-chart-bar"></i> Job Metrics</h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-md-3 mb-3">
                            <div class="p-3 bg-light rounded">
                                <h4 class="text-primary mb-0">{{ $metrics['estimated_duration'] }}</h4>
                                <small class="text-muted">Est. Duration (hrs)</small>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="p-3 bg-light rounded">
                                <h4 class="text-success mb-0">{{ $metrics['actual_duration'] }}</h4>
                                <small class="text-muted">Actual Duration (hrs)</small>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="p-3 bg-light rounded">
                                <h4 class="text-{{ $metrics['duration_variance'] > 0 ? 'danger' : 'success' }} mb-0">
                                    {{ $metrics['duration_variance'] > 0 ? '+' : '' }}{{ $metrics['duration_variance'] }}
                                </h4>
                                <small class="text-muted">Variance (hrs)</small>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="p-3 bg-light rounded">
                                <h4 class="text-info mb-0">{{ $metrics['total_reports'] }}</h4>
                                <small class="text-muted">Work Reports</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Job Information -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="fas fa-info-circle"></i> Job Information</h5>
                </div>
                <div class="card-body">
                    <!-- Description -->
                    <div class="mb-3">
                        <h6 class="text-muted">Description</h6>
                        <p class="mb-0">{{ $job->description }}</p>
                    </div>

                    <hr>

                    <!-- Schedule & Dates -->
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="text-muted mb-3">Schedule & Timeline</h6>
                            
                            <p class="mb-2">
                                <strong>Scheduled Date:</strong>
                                @if($job->scheduled_date)
                                    <span class="d-block">{{ $job->scheduled_date->format('d M Y') }}</span>
                                    <small class="text-muted">{{ $job->scheduled_date->diffForHumans() }}</small>
                                @else
                                    <span class="text-muted d-block">Not scheduled</span>
                                @endif
                            </p>

                            <p class="mb-2">
                                <strong>Started At:</strong>
                                @if($job->started_at)
                                    <span class="d-block">{{ $job->started_at->format('d M Y, H:i') }}</span>
                                @else
                                    <span class="text-muted d-block">Not started</span>
                                @endif
                            </p>

                            <p class="mb-2">
                                <strong>Completed At:</strong>
                                @if($job->completed_at)
                                    <span class="d-block">{{ $job->completed_at->format('d M Y, H:i') }}</span>
                                @else
                                    <span class="text-muted d-block">Not completed</span>
                                @endif
                            </p>
                        </div>

                        <div class="col-md-6">
                            <h6 class="text-muted mb-3">Classification</h6>
                            
                            <p class="mb-2">
                                <strong>Type:</strong>
                                <span class="badge bg-{{ $job->typeBadge }}">{{ ucfirst($job->type) }}</span>
                            </p>

                            <p class="mb-2">
                                <strong>Priority:</strong>
                                <span class="badge bg-{{ $job->priorityBadge }}">{{ ucfirst($job->priority) }}</span>
                            </p>

                            <p class="mb-2">
                                <strong>Status:</strong>
                                <span class="badge bg-{{ $job->statusBadge }}">{{ ucfirst(str_replace('_', ' ', $job->status)) }}</span>
                            </p>
                        </div>
                    </div>

                    @if($job->notes)
                    <hr>
                    <div>
                        <h6 class="text-muted">Additional Notes</h6>
                        <p class="mb-0">{{ $job->notes }}</p>
                    </div>
                    @endif

                    <hr>

                    <div class="row">
                        <div class="col-md-6">
                            <p class="mb-0">
                                <small class="text-muted">
                                    <strong>Created:</strong> {{ $job->created_at->format('d M Y, H:i') }}
                                </small>
                            </p>
                        </div>
                        <div class="col-md-6 text-end">
                            <p class="mb-0">
                                <small class="text-muted">
                                    <strong>Last Updated:</strong> {{ $job->updated_at->format('d M Y, H:i') }}
                                </small>
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Timeline -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="fas fa-history"></i> Job Timeline</h5>
                </div>
                <div class="card-body">
                    @if(count($timeline) > 0)
                        <div class="timeline">
                            @foreach($timeline as $event)
                            <div class="timeline-item">
                                <div class="timeline-marker bg-{{ $event['color'] }}">
                                    <i class="fas {{ $event['icon'] }} text-white"></i>
                                </div>
                                <div class="timeline-content">
                                    <h6 class="mb-1">{{ $event['title'] }}</h6>
                                    <p class="mb-1 text-muted">{{ $event['description'] }}</p>
                                    <small class="text-muted">
                                        <i class="fas fa-clock"></i>
                                        {{ $event['date']->format('d M Y, H:i') }} 
                                        ({{ $event['date']->diffForHumans() }})
                                    </small>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-muted text-center mb-0">No timeline events yet</p>
                    @endif
                </div>
            </div>

            <!-- Work Reports Section (Placeholder) -->
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="fas fa-file-alt"></i> Work Reports</h5>
                </div>
                <div class="card-body">
                    <div class="text-center py-4">
                        <i class="fas fa-file-alt fa-3x text-muted mb-3"></i>
                        <p class="text-muted mb-0">No work reports submitted yet</p>
                        <small class="text-muted">Work reports will appear here once submitted</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Status Update Modal -->
<div class="modal fade" id="statusModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.jobs.update-status', $job) }}" method="POST">
                @csrf
                @method('PATCH')
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title"><i class="fas fa-exchange-alt"></i> Update Job Status</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> 
                        Updating status for: <strong>{{ $job->job_code }}</strong>
                    </div>

                    <div class="mb-3">
                        <label for="status" class="form-label">New Status <span class="text-danger">*</span></label>
                        <select class="form-select" id="status" name="status" required>
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

                    <div class="alert alert-warning">
                        <small>
                            <i class="fas fa-info-circle"></i>
                            Status changes will automatically update timestamps:
                            <ul class="mb-0 mt-2">
                                <li><strong>In Progress:</strong> Sets started_at timestamp</li>
                                <li><strong>Completed:</strong> Sets completed_at timestamp</li>
                            </ul>
                        </small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning text-dark">
                        <i class="fas fa-save"></i> Update Status
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.job-icon-large {
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