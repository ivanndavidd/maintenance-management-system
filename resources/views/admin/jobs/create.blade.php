@extends('layouts.admin')

@section('page-title', 'Create Job')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="mb-4">
        <h2><i class="fas fa-plus-circle"></i> Create Maintenance Job</h2>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route($routePrefix.'.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route($routePrefix.'.jobs.index') }}">Maintenance Jobs</a></li>
                <li class="breadcrumb-item active">Create</li>
            </ol>
        </nav>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-tasks"></i> Job Information</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route($routePrefix.'.jobs.store') }}">
                        @csrf

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
                                       value="{{ old('job_code', $jobCode) }}" 
                                       readonly>
                                @error('job_code')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">Auto-generated job code</small>
                            </div>

                            <!-- Status -->
                            <div class="col-md-6 mb-3">
                                <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                                <select class="form-select @error('status') is-invalid @enderror" 
                                        id="status" 
                                        name="status" 
                                        required>
                                    <option value="pending" {{ old('status', 'pending') == 'pending' ? 'selected' : '' }}>
                                        Pending
                                    </option>
                                    <option value="in_progress" {{ old('status') == 'in_progress' ? 'selected' : '' }}>
                                        In Progress
                                    </option>
                                    <option value="completed" {{ old('status') == 'completed' ? 'selected' : '' }}>
                                        Completed
                                    </option>
                                    <option value="cancelled" {{ old('status') == 'cancelled' ? 'selected' : '' }}>
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
                                       value="{{ old('title') }}" 
                                       placeholder="e.g., Monthly preventive maintenance for Forklift"
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
                                          placeholder="Detailed description of the maintenance work..."
                                          required>{{ old('description') }}</textarea>
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
                                                {{ old('machine_id') == $machine->id ? 'selected' : '' }}
                                                data-status="{{ $machine->status }}">
                                            {{ $machine->code }} - {{ $machine->name }}
                                            @if($machine->status != 'operational')
                                                ({{ ucfirst($machine->status) }})
                                            @endif
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
                                        <option value="{{ $user->id }}" {{ old('assigned_to') == $user->id ? 'selected' : '' }}>
                                            {{ $user->name }} ({{ $user->email }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('assigned_to')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">Leave empty to assign later</small>
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
                                    <option value="">Select Type</option>
                                    <option value="preventive" {{ old('type') == 'preventive' ? 'selected' : '' }}>
                                        Preventive Maintenance
                                    </option>
                                    <option value="breakdown" {{ old('type') == 'breakdown' ? 'selected' : '' }}>
                                        Breakdown Repair
                                    </option>
                                    <option value="corrective" {{ old('type') == 'corrective' ? 'selected' : '' }}>
                                        Corrective Maintenance
                                    </option>
                                    <option value="inspection" {{ old('type') == 'inspection' ? 'selected' : '' }}>
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
                                    <option value="">Select Priority</option>
                                    <option value="low" {{ old('priority') == 'low' ? 'selected' : '' }}>
                                        Low
                                    </option>
                                    <option value="medium" {{ old('priority', 'medium') == 'medium' ? 'selected' : '' }}>
                                        Medium
                                    </option>
                                    <option value="high" {{ old('priority') == 'high' ? 'selected' : '' }}>
                                        High
                                    </option>
                                    <option value="critical" {{ old('priority') == 'critical' ? 'selected' : '' }}>
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
                                       value="{{ old('estimated_duration') }}" 
                                       min="1"
                                       placeholder="e.g., 4">
                                @error('estimated_duration')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Scheduling -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h6 class="border-bottom pb-2 mb-3">Scheduling</h6>
                            </div>

                            <!-- Scheduled Date -->
                            <div class="col-md-12 mb-3">
                                <label for="scheduled_date" class="form-label">Scheduled Date</label>
                                <input type="date"
                                       class="form-control @error('scheduled_date') is-invalid @enderror"
                                       id="scheduled_date"
                                       name="scheduled_date"
                                       value="{{ old('scheduled_date') }}"
                                       min="{{ date('Y-m-d') }}">
                                @error('scheduled_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">Target completion date</small>
                            </div>
                        </div>

                        <!-- Recurring Settings (Only for Preventive & Inspection) -->
                        <div class="row mb-4" id="recurring-section" style="display: none;">
                            <div class="col-12">
                                <h6 class="border-bottom pb-2 mb-3">
                                    <i class="fas fa-redo"></i> Recurring Schedule
                                </h6>
                            </div>

                            <!-- Enable Recurring -->
                            <div class="col-md-12 mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input"
                                           type="checkbox"
                                           id="is_recurring"
                                           name="is_recurring"
                                           value="1"
                                           {{ old('is_recurring') ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_recurring">
                                        <strong>Enable Recurring Job</strong>
                                        <small class="d-block text-muted">Automatically create new jobs based on schedule</small>
                                    </label>
                                </div>
                            </div>

                            <!-- Recurring Options (Hidden until enabled) -->
                            <div id="recurring-options" style="display: none;">
                                <!-- Recurrence Type -->
                                <div class="col-md-6 mb-3">
                                    <label for="recurrence_type" class="form-label">Repeat Every <span class="text-danger">*</span></label>
                                    <select class="form-select @error('recurrence_type') is-invalid @enderror"
                                            id="recurrence_type"
                                            name="recurrence_type">
                                        <option value="">Select Frequency</option>
                                        <option value="daily" {{ old('recurrence_type') == 'daily' ? 'selected' : '' }}>
                                            Daily
                                        </option>
                                        <option value="weekly" {{ old('recurrence_type') == 'weekly' ? 'selected' : '' }}>
                                            Weekly
                                        </option>
                                        <option value="monthly" {{ old('recurrence_type') == 'monthly' ? 'selected' : '' }}>
                                            Monthly
                                        </option>
                                        <option value="yearly" {{ old('recurrence_type') == 'yearly' ? 'selected' : '' }}>
                                            Yearly
                                        </option>
                                    </select>
                                    @error('recurrence_type')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Recurrence Interval -->
                                <div class="col-md-6 mb-3">
                                    <label for="recurrence_interval" class="form-label">Interval <span class="text-danger">*</span></label>
                                    <input type="number"
                                           class="form-control @error('recurrence_interval') is-invalid @enderror"
                                           id="recurrence_interval"
                                           name="recurrence_interval"
                                           value="{{ old('recurrence_interval', 1) }}"
                                           min="1"
                                           placeholder="1">
                                    @error('recurrence_interval')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted" id="interval-hint">e.g., 1 for every day/week/month/year</small>
                                </div>

                                <!-- Recurrence End Date (Optional) -->
                                <div class="col-md-12 mb-3">
                                    <label for="recurrence_end_date" class="form-label">End Date (Optional)</label>
                                    <input type="date"
                                           class="form-control @error('recurrence_end_date') is-invalid @enderror"
                                           id="recurrence_end_date"
                                           name="recurrence_end_date"
                                           value="{{ old('recurrence_end_date') }}"
                                           min="{{ date('Y-m-d') }}">
                                    @error('recurrence_end_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">Leave empty for indefinite recurrence</small>
                                </div>

                                <!-- Recurring Summary -->
                                <div class="col-md-12">
                                    <div class="alert alert-info" id="recurring-summary">
                                        <i class="fas fa-info-circle"></i> <strong>Summary:</strong>
                                        <span id="summary-text">Configure recurring settings above</span>
                                    </div>
                                </div>
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
                                          rows="3" 
                                          placeholder="Additional notes or special instructions...">{{ old('notes') }}</textarea>
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
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Create Job
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Info Sidebar -->
        <div class="col-lg-4">
            <!-- Job Type Info -->
            <div class="card shadow-sm mb-3">
                <div class="card-header bg-info text-white">
                    <h6 class="mb-0"><i class="fas fa-info-circle"></i> Job Types</h6>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled mb-0">
                        <li class="mb-2">
                            <span class="badge bg-success">Preventive:</span>
                            <small class="d-block text-muted">Regular scheduled maintenance to prevent breakdowns</small>
                        </li>
                        <li class="mb-2">
                            <span class="badge bg-danger">Breakdown:</span>
                            <small class="d-block text-muted">Emergency repair for machine failure</small>
                        </li>
                        <li class="mb-2">
                            <span class="badge bg-warning text-dark">Corrective:</span>
                            <small class="d-block text-muted">Fix issues found during inspection</small>
                        </li>
                        <li class="mb-2">
                            <span class="badge bg-info">Inspection:</span>
                            <small class="d-block text-muted">Routine check and assessment</small>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Priority Levels -->
            <div class="card shadow-sm mb-3">
                <div class="card-header bg-warning text-dark">
                    <h6 class="mb-0"><i class="fas fa-exclamation-triangle"></i> Priority Levels</h6>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled mb-0">
                        <li class="mb-2">
                            <span class="badge bg-danger">Critical:</span>
                            <small class="d-block text-muted">Immediate action required, safety risk</small>
                        </li>
                        <li class="mb-2">
                            <span class="badge bg-warning text-dark">High:</span>
                            <small class="d-block text-muted">Important, complete within 24-48 hours</small>
                        </li>
                        <li class="mb-2">
                            <span class="badge bg-info">Medium:</span>
                            <small class="d-block text-muted">Normal priority, complete within 1 week</small>
                        </li>
                        <li class="mb-2">
                            <span class="badge bg-secondary">Low:</span>
                            <small class="d-block text-muted">Can be scheduled flexibly</small>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Quick Tips -->
            <div class="card shadow-sm">
                <div class="card-header bg-success text-white">
                    <h6 class="mb-0"><i class="fas fa-lightbulb"></i> Quick Tips</h6>
                </div>
                <div class="card-body">
                    <ul class="small mb-0">
                        <li>Job code is auto-generated and unique</li>
                        <li>Assign jobs to technicians based on their expertise</li>
                        <li>Set realistic estimated duration for better planning</li>
                        <li>Use scheduled date for deadline tracking</li>
                        <li>Add detailed description for clarity</li>
                        <li>Critical priority for safety-related issues</li>
                        <li>Preventive maintenance should be scheduled regularly</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Auto-suggest job title based on machine selection
document.getElementById('machine_id').addEventListener('change', function() {
    const selectedOption = this.options[this.selectedIndex];
    const machineText = selectedOption.text;
    const typeSelect = document.getElementById('type');
    const titleInput = document.getElementById('title');

    if (this.value && typeSelect.value && !titleInput.value) {
        const type = typeSelect.options[typeSelect.selectedIndex].text;
        titleInput.value = `${type} - ${machineText}`;
    }
});

document.getElementById('type').addEventListener('change', function() {
    const machineSelect = document.getElementById('machine_id');
    const selectedOption = machineSelect.options[machineSelect.selectedIndex];
    const machineText = selectedOption.text;
    const titleInput = document.getElementById('title');

    if (machineSelect.value && this.value && !titleInput.value) {
        const type = this.options[this.selectedIndex].text;
        titleInput.value = `${type} - ${machineText}`;
    }

    // Show/hide recurring section based on job type
    toggleRecurringSection(this.value);
});

// Show/hide recurring section based on job type
function toggleRecurringSection(jobType) {
    const recurringSection = document.getElementById('recurring-section');

    if (jobType === 'preventive' || jobType === 'inspection') {
        recurringSection.style.display = 'block';
    } else {
        recurringSection.style.display = 'none';
        // Reset recurring checkbox
        document.getElementById('is_recurring').checked = false;
        document.getElementById('recurring-options').style.display = 'none';
    }
}

// Toggle recurring options visibility
document.getElementById('is_recurring').addEventListener('change', function() {
    const recurringOptions = document.getElementById('recurring-options');

    if (this.checked) {
        recurringOptions.style.display = 'block';
        // Make recurrence_type required when recurring is enabled
        document.getElementById('recurrence_type').setAttribute('required', 'required');
        document.getElementById('recurrence_interval').setAttribute('required', 'required');
    } else {
        recurringOptions.style.display = 'none';
        // Remove required when disabled
        document.getElementById('recurrence_type').removeAttribute('required');
        document.getElementById('recurrence_interval').removeAttribute('required');
    }

    updateRecurringSummary();
});

// Update interval hint based on recurrence type
document.getElementById('recurrence_type').addEventListener('change', function() {
    const intervalHint = document.getElementById('interval-hint');
    const type = this.value;

    switch(type) {
        case 'daily':
            intervalHint.textContent = 'e.g., 1 for every day, 2 for every 2 days';
            break;
        case 'weekly':
            intervalHint.textContent = 'e.g., 1 for every week, 2 for every 2 weeks';
            break;
        case 'monthly':
            intervalHint.textContent = 'e.g., 1 for every month, 3 for every 3 months';
            break;
        case 'yearly':
            intervalHint.textContent = 'e.g., 1 for every year, 2 for every 2 years';
            break;
    }

    updateRecurringSummary();
});

document.getElementById('recurrence_interval').addEventListener('input', updateRecurringSummary);
document.getElementById('recurrence_end_date').addEventListener('change', updateRecurringSummary);

// Update recurring summary
function updateRecurringSummary() {
    const isRecurring = document.getElementById('is_recurring').checked;
    const type = document.getElementById('recurrence_type').value;
    const interval = document.getElementById('recurrence_interval').value;
    const endDate = document.getElementById('recurrence_end_date').value;
    const summaryText = document.getElementById('summary-text');

    if (!isRecurring) {
        summaryText.textContent = 'Recurring is disabled';
        return;
    }

    if (!type || !interval) {
        summaryText.textContent = 'Configure recurring settings above';
        return;
    }

    let summary = `This job will repeat every ${interval > 1 ? interval + ' ' : ''}`;

    switch(type) {
        case 'daily':
            summary += interval > 1 ? 'days' : 'day';
            break;
        case 'weekly':
            summary += interval > 1 ? 'weeks' : 'week';
            break;
        case 'monthly':
            summary += interval > 1 ? 'months' : 'month';
            break;
        case 'yearly':
            summary += interval > 1 ? 'years' : 'year';
            break;
    }

    if (endDate) {
        summary += ` until ${new Date(endDate).toLocaleDateString()}`;
    } else {
        summary += ' indefinitely';
    }

    summaryText.textContent = summary;
}

// Initialize on page load (for old values)
document.addEventListener('DOMContentLoaded', function() {
    const typeSelect = document.getElementById('type');
    if (typeSelect.value) {
        toggleRecurringSection(typeSelect.value);
    }

    const isRecurringCheckbox = document.getElementById('is_recurring');
    if (isRecurringCheckbox.checked) {
        document.getElementById('recurring-options').style.display = 'block';
        updateRecurringSummary();
    }
});
</script>
@endsection