@extends('layouts.admin')

@section('page-title', 'Edit Job')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="mb-4">
        <h2><i class="fas fa-edit"></i> Edit Maintenance Job</h2>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route($routePrefix.'.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route($routePrefix.'.jobs.index') }}">Maintenance Jobs</a></li>
                <li class="breadcrumb-item active">Edit: {{ $job->job_code }}</li>
            </ol>
        </nav>
    </div>

    <!-- Success Messages -->
    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0"><i class="fas fa-tasks"></i> Edit Job Information</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route($routePrefix.'.jobs.update', $job) }}">
                        @csrf
                        @method('PUT')

                        <!-- Basic Information -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h6 class="border-bottom pb-2 mb-3">Basic Information</h6>
                            </div>

                            <!-- Job Code -->
                            <div class="col-md-6 mb-3">
                                <label for="job_code" class="form-label">Job Code <span class="text-danger">*</span></label>
                                <input type="text" 
                                       class="form-control @error('job_code') is-invalid @enderror" 
                                       id="job_code" 
                                       name="job_code" 
                                       value="{{ old('job_code', $job->job_code) }}" 
                                       required>
                                @error('job_code')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Status -->
                            <div class="col-md-6 mb-3">
                                <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                                <select class="form-select @error('status') is-invalid @enderror" 
                                        id="status" 
                                        name="status" 
                                        required>
                                    <option value="pending" {{ old('status', $job->status) == 'pending' ? 'selected' : '' }}>
                                        Pending
                                    </option>
                                    <option value="in_progress" {{ old('status', $job->status) == 'in_progress' ? 'selected' : '' }}>
                                        In Progress
                                    </option>
                                    <option value="completed" {{ old('status', $job->status) == 'completed' ? 'selected' : '' }}>
                                        Completed
                                    </option>
                                    <option value="cancelled" {{ old('status', $job->status) == 'cancelled' ? 'selected' : '' }}>
                                        Cancelled
                                    </option>
                                </select>
                                @error('status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Title -->
                            <div class="col-md-12 mb-3">
                                <label for="title" class="form-label">Job Title <span class="text-danger">*</span></label>
                                <input type="text" 
                                       class="form-control @error('title') is-invalid @enderror" 
                                       id="title" 
                                       name="title" 
                                       value="{{ old('title', $job->title) }}" 
                                       required>
                                @error('title')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Description -->
                            <div class="col-md-12 mb-3">
                                <label for="description" class="form-label">Description <span class="text-danger">*</span></label>
                                <textarea class="form-control @error('description') is-invalid @enderror" 
                                          id="description" 
                                          name="description" 
                                          rows="4" 
                                          required>{{ old('description', $job->description) }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Machine & Assignment -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h6 class="border-bottom pb-2 mb-3">Machine & Assignment</h6>
                            </div>

                            <!-- Machine -->
                            <div class="col-md-6 mb-3">
                                <label for="machine_id" class="form-label">Machine <span class="text-danger">*</span></label>
                                <select class="form-select @error('machine_id') is-invalid @enderror" 
                                        id="machine_id" 
                                        name="machine_id" 
                                        required>
                                    <option value="">Select Machine</option>
                                    @foreach($machines as $machine)
                                        <option value="{{ $machine->id }}" 
                                                {{ old('machine_id', $job->machine_id) == $machine->id ? 'selected' : '' }}>
                                            {{ $machine->code }} - {{ $machine->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('machine_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Assigned To -->
                            <div class="col-md-6 mb-3">
                                <label for="assigned_to" class="form-label">Assign To</label>
                                <select class="form-select @error('assigned_to') is-invalid @enderror" 
                                        id="assigned_to" 
                                        name="assigned_to">
                                    <option value="">Unassigned</option>
                                    @foreach($users as $user)
                                        <option value="{{ $user->id }}" 
                                                {{ old('assigned_to', $job->assigned_to) == $user->id ? 'selected' : '' }}>
                                            {{ $user->name }} ({{ $user->email }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('assigned_to')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Job Classification -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h6 class="border-bottom pb-2 mb-3">Job Classification</h6>
                            </div>

                            <!-- Type -->
                            <div class="col-md-4 mb-3">
                                <label for="type" class="form-label">Type <span class="text-danger">*</span></label>
                                <select class="form-select @error('type') is-invalid @enderror" 
                                        id="type" 
                                        name="type" 
                                        required>
                                    <option value="preventive" {{ old('type', $job->type) == 'preventive' ? 'selected' : '' }}>
                                        Preventive Maintenance
                                    </option>
                                    <option value="breakdown" {{ old('type', $job->type) == 'breakdown' ? 'selected' : '' }}>
                                        Breakdown Repair
                                    </option>
                                    <option value="corrective" {{ old('type', $job->type) == 'corrective' ? 'selected' : '' }}>
                                        Corrective Maintenance
                                    </option>
                                    <option value="inspection" {{ old('type', $job->type) == 'inspection' ? 'selected' : '' }}>
                                        Inspection
                                    </option>
                                </select>
                                @error('type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Priority -->
                            <div class="col-md-4 mb-3">
                                <label for="priority" class="form-label">Priority <span class="text-danger">*</span></label>
                                <select class="form-select @error('priority') is-invalid @enderror" 
                                        id="priority" 
                                        name="priority" 
                                        required>
                                    <option value="low" {{ old('priority', $job->priority) == 'low' ? 'selected' : '' }}>
                                        Low
                                    </option>
                                    <option value="medium" {{ old('priority', $job->priority) == 'medium' ? 'selected' : '' }}>
                                        Medium
                                    </option>
                                    <option value="high" {{ old('priority', $job->priority) == 'high' ? 'selected' : '' }}>
                                        High
                                    </option>
                                    <option value="critical" {{ old('priority', $job->priority) == 'critical' ? 'selected' : '' }}>
                                        Critical
                                    </option>
                                </select>
                                @error('priority')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Estimated Duration -->
                            <div class="col-md-4 mb-3">
                                <label for="estimated_duration" class="form-label">Estimated Duration (hours)</label>
                                <input type="number" 
                                       class="form-control @error('estimated_duration') is-invalid @enderror" 
                                       id="estimated_duration" 
                                       name="estimated_duration" 
                                       value="{{ old('estimated_duration', $job->estimated_duration) }}" 
                                       min="1">
                                @error('estimated_duration')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Scheduling & Time Tracking -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h6 class="border-bottom pb-2 mb-3">Scheduling & Time Tracking</h6>
                            </div>

                            <!-- Scheduled Date -->
                            <div class="col-md-4 mb-3">
                                <label for="scheduled_date" class="form-label">Scheduled Date</label>
                                <input type="date" 
                                       class="form-control @error('scheduled_date') is-invalid @enderror" 
                                       id="scheduled_date" 
                                       name="scheduled_date" 
                                       value="{{ old('scheduled_date', $job->scheduled_date?->format('Y-m-d')) }}">
                                @error('scheduled_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Started At -->
                            <div class="col-md-4 mb-3">
                                <label for="started_at" class="form-label">Started At</label>
                                <input type="datetime-local" 
                                       class="form-control @error('started_at') is-invalid @enderror" 
                                       id="started_at" 
                                       name="started_at" 
                                       value="{{ old('started_at', $job->started_at?->format('Y-m-d\TH:i')) }}">
                                @error('started_at')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Completed At -->
                            <div class="col-md-4 mb-3">
                                <label for="completed_at" class="form-label">Completed At</label>
                                <input type="datetime-local" 
                                       class="form-control @error('completed_at') is-invalid @enderror" 
                                       id="completed_at" 
                                       name="completed_at" 
                                       value="{{ old('completed_at', $job->completed_at?->format('Y-m-d\TH:i')) }}">
                                @error('completed_at')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Actual Duration -->
                            <div class="col-md-12 mb-3">
                                <label for="actual_duration" class="form-label">Actual Duration (hours)</label>
                                <input type="number" 
                                       class="form-control @error('actual_duration') is-invalid @enderror" 
                                       id="actual_duration" 
                                       name="actual_duration" 
                                       value="{{ old('actual_duration', $job->actual_duration) }}" 
                                       min="1"
                                       placeholder="Hours taken to complete">
                                @error('actual_duration')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Additional Information -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h6 class="border-bottom pb-2 mb-3">Additional Information</h6>
                            </div>

                            <!-- Notes -->
                            <div class="col-md-12 mb-3">
                                <label for="notes" class="form-label">Notes</label>
                                <textarea class="form-control @error('notes') is-invalid @enderror" 
                                          id="notes" 
                                          name="notes" 
                                          rows="3">{{ old('notes', $job->notes) }}</textarea>
                                @error('notes')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Submit Buttons -->
                        <div class="d-flex justify-content-between">
                            <a href="{{ route($routePrefix.'.jobs.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Cancel
                            </a>
                            <button type="submit" class="btn btn-warning text-dark">
                                <i class="fas fa-save"></i> Update Job
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Info Sidebar -->
        <div class="col-lg-4">
            <!-- Current Job Info -->
            <div class="card shadow-sm mb-3">
                <div class="card-header bg-info text-white">
                    <h6 class="mb-0"><i class="fas fa-info-circle"></i> Current Job Info</h6>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled mb-0">
                        <li class="mb-2">
                            <strong>Created:</strong>
                            <small class="d-block text-muted">{{ $job->created_at->format('d M Y, H:i') }}</small>
                        </li>
                        <li class="mb-2">
                            <strong>Last Updated:</strong>
                            <small class="d-block text-muted">{{ $job->updated_at->format('d M Y, H:i') }}</small>
                        </li>
                        <li class="mb-2">
                            <strong>Created By:</strong>
                            <small class="d-block text-muted">{{ $job->creator->name ?? 'N/A' }}</small>
                        </li>
                        <li class="mb-2">
                            <strong>Current Status:</strong>
                            <span class="badge bg-{{ $job->statusBadge }}">
                                {{ ucfirst(str_replace('_', ' ', $job->status)) }}
                            </span>
                        </li>
                        @if($job->isOverdue())
                        <li class="mb-2">
                            <strong class="text-danger">Status:</strong>
                            <span class="badge bg-danger">OVERDUE</span>
                        </li>
                        @endif
                    </ul>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card shadow-sm mb-3">
                <div class="card-header bg-primary text-white">
                    <h6 class="mb-0"><i class="fas fa-bolt"></i> Quick Actions</h6>
                </div>
                <div class="card-body">
                    <a href="{{ route($routePrefix.'.jobs.show', $job) }}" class="btn btn-info btn-sm w-100 mb-2">
                        <i class="fas fa-eye"></i> View Full Details
                    </a>
                    
                    <button type="button" 
                            class="btn btn-warning btn-sm w-100 mb-2" 
                            data-bs-toggle="modal" 
                            data-bs-target="#statusModal">
                        <i class="fas fa-exchange-alt"></i> Quick Status Change
                    </button>
                    
                    <form action="{{ route($routePrefix.'.jobs.destroy', $job) }}" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="submit" 
                                class="btn btn-danger btn-sm w-100"
                                onclick="return confirm('Are you sure you want to delete this job?')">
                            <i class="fas fa-trash"></i> Delete Job
                        </button>
                    </form>
                </div>
            </div>

            <!-- Important Notes -->
            <div class="card shadow-sm">
                <div class="card-header bg-warning text-dark">
                    <h6 class="mb-0"><i class="fas fa-exclamation-triangle"></i> Important</h6>
                </div>
                <div class="card-body">
                    <ul class="small mb-0">
                        <li>Job code must be unique</li>
                        <li>Changing status updates timestamps automatically</li>
                        <li>Actual duration recorded when job completed</li>
                        <li>Cannot delete job with work reports</li>
                        <li>Update machine status if job affects availability</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Quick Status Change Modal -->
<div class="modal fade" id="statusModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route($routePrefix.'.jobs.update-status', $job) }}" method="POST">
                @csrf
                @method('PATCH')
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title"><i class="fas fa-exchange-alt"></i> Update Status</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> 
                        Quick status update for: <strong>{{ $job->job_code }}</strong>
                    </div>

                    <div class="mb-3">
                        <label for="modal_status" class="form-label">New Status <span class="text-danger">*</span></label>
                        <select class="form-select" id="modal_status" name="status" required>
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
                    <button type="submit" class="btn btn-warning text-dark">
                        <i class="fas fa-save"></i> Update Status
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection