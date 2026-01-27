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
                <li class="breadcrumb-item active">Request New Task</li>
            </ol>
        </nav>
        <h2><i class="fas fa-tasks"></i> Request New Maintenance Task</h2>
        <p class="text-muted">Submit a maintenance task request to the team</p>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="fas fa-file-alt"></i> Task Request Form</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('pic.task-requests.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <!-- Request Code -->
                        <div class="mb-3">
                            <label class="form-label fw-bold">Request Code</label>
                            <input type="text" name="request_code" class="form-control bg-light"
                                   value="{{ $requestCode }}" readonly>
                            <small class="text-muted">Auto-generated request code</small>
                        </div>

                        <!-- Machine Selection (Optional) -->
                        <div class="mb-3">
                            <label class="form-label fw-bold">
                                Machine (Optional)
                            </label>
                            <select name="machine_id" class="form-select @error('machine_id') is-invalid @enderror">
                                <option value="">-- Select Machine (if specific) --</option>
                                @foreach($machines as $machine)
                                <option value="{{ $machine->id }}" {{ old('machine_id') == $machine->id ? 'selected' : '' }}>
                                    {{ $machine->name }} ({{ $machine->code }})
                                </option>
                                @endforeach
                            </select>
                            @error('machine_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Leave empty for general maintenance tasks</small>
                        </div>

                        <!-- Task Type -->
                        <div class="mb-3">
                            <label class="form-label fw-bold">
                                Task Type <span class="text-danger">*</span>
                            </label>
                            <select name="task_type" class="form-select @error('task_type') is-invalid @enderror" required>
                                <option value="">-- Select Task Type --</option>
                                <option value="Preventive Maintenance" {{ old('task_type') == 'Preventive Maintenance' ? 'selected' : '' }}>
                                    Preventive Maintenance
                                </option>
                                <option value="Corrective Maintenance" {{ old('task_type') == 'Corrective Maintenance' ? 'selected' : '' }}>
                                    Corrective Maintenance
                                </option>
                                <option value="Inspection" {{ old('task_type') == 'Inspection' ? 'selected' : '' }}>
                                    Inspection
                                </option>
                                <option value="Calibration" {{ old('task_type') == 'Calibration' ? 'selected' : '' }}>
                                    Calibration
                                </option>
                                <option value="Cleaning" {{ old('task_type') == 'Cleaning' ? 'selected' : '' }}>
                                    Cleaning
                                </option>
                                <option value="Parts Replacement" {{ old('task_type') == 'Parts Replacement' ? 'selected' : '' }}>
                                    Parts Replacement
                                </option>
                                <option value="Upgrade" {{ old('task_type') == 'Upgrade' ? 'selected' : '' }}>
                                    Upgrade
                                </option>
                                <option value="Other" {{ old('task_type') == 'Other' ? 'selected' : '' }}>
                                    Other
                                </option>
                            </select>
                            @error('task_type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Priority -->
                        <div class="mb-3">
                            <label class="form-label fw-bold">
                                Priority Level <span class="text-danger">*</span>
                            </label>
                            <select name="priority" class="form-select @error('priority') is-invalid @enderror" required>
                                <option value="">-- Select Priority --</option>
                                <option value="low" {{ old('priority') == 'low' ? 'selected' : '' }}>
                                    Low - Can be scheduled later
                                </option>
                                <option value="medium" {{ old('priority', 'medium') == 'medium' ? 'selected' : '' }}>
                                    Medium - Normal priority
                                </option>
                                <option value="high" {{ old('priority') == 'high' ? 'selected' : '' }}>
                                    High - Should be done soon
                                </option>
                                <option value="urgent" {{ old('priority') == 'urgent' ? 'selected' : '' }}>
                                    Urgent - Needs immediate attention
                                </option>
                            </select>
                            @error('priority')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Title -->
                        <div class="mb-3">
                            <label class="form-label fw-bold">
                                Task Title <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="title" class="form-control @error('title') is-invalid @enderror"
                                   value="{{ old('title') }}"
                                   placeholder="Brief summary of the task needed"
                                   required>
                            @error('title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Description -->
                        <div class="mb-3">
                            <label class="form-label fw-bold">
                                Detailed Description <span class="text-danger">*</span>
                            </label>
                            <textarea name="description" class="form-control @error('description') is-invalid @enderror"
                                      rows="5"
                                      placeholder="Describe the maintenance task needed, expected outcome, any specific requirements..."
                                      required>{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Requested Date -->
                        <div class="mb-3">
                            <label class="form-label fw-bold">
                                Preferred Completion Date (Optional)
                            </label>
                            <input type="date" name="requested_date" class="form-control @error('requested_date') is-invalid @enderror"
                                   value="{{ old('requested_date') }}"
                                   min="{{ date('Y-m-d') }}">
                            @error('requested_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Leave empty if ASAP or flexible</small>
                        </div>

                        <!-- Attachments -->
                        <div class="mb-3">
                            <label class="form-label fw-bold">Attachments (Optional)</label>
                            <input type="file" name="attachments[]" class="form-control @error('attachments.*') is-invalid @enderror"
                                   multiple accept="image/*,.pdf">
                            <small class="text-muted">Upload supporting documents or photos (Max 5MB per file)</small>
                            @error('attachments.*')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Operator Assignment -->
                        <div class="mb-3">
                            <label class="form-label fw-bold">Assign to Operator (Optional)</label>

                            <div class="alert alert-info py-2" role="alert">
                                <small><i class="fas fa-info-circle"></i> You can assign specific operators or leave empty for admin assignment.</small>
                            </div>

                            <!-- Assign to All Button -->
                            <div class="card border-primary mb-3" style="cursor: pointer;" onclick="toggleAssignAll()">
                                <div class="card-body p-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="assign_to_all" id="assignToAll" value="1">
                                        <label class="form-check-label w-100" for="assignToAll" style="cursor: pointer;">
                                            <div class="d-flex align-items-center">
                                                <div class="flex-shrink-0">
                                                    <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center"
                                                         style="width: 40px; height: 40px;">
                                                        <i class="fas fa-users"></i>
                                                    </div>
                                                </div>
                                                <div class="flex-grow-1 ms-3">
                                                    <strong class="text-primary">Assign to ALL Operators</strong>
                                                    <br>
                                                    <small class="text-muted">All operators will see this task. First to complete marks it done.</small>
                                                </div>
                                            </div>
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <!-- Specific Operators Selection -->
                            <div id="operatorSelectDiv">
                                <label class="form-label">Or select specific operators:</label>

                                <!-- Search Box -->
                                <div class="input-group mb-2">
                                    <span class="input-group-text bg-white">
                                        <i class="fas fa-search text-muted"></i>
                                    </span>
                                    <input type="text" id="operatorSearch" class="form-control" placeholder="Search operators by name or ID...">
                                </div>

                                <!-- Operators List with Checkboxes -->
                                <div class="border rounded p-2 bg-light" style="max-height: 300px; overflow-y: auto;" id="operatorsList">
                                    @foreach($operators as $operator)
                                    <div class="operator-item bg-white rounded p-2 mb-2 border" style="transition: all 0.2s;">
                                        <div class="form-check">
                                            <input class="form-check-input operator-checkbox" type="checkbox"
                                                   name="operator_ids[]" value="{{ $operator->id }}"
                                                   id="operator{{ $operator->id }}"
                                                   data-name="{{ strtolower($operator->name) }}"
                                                   data-employee="{{ strtolower($operator->employee_id ?? '') }}">
                                            <label class="form-check-label w-100 d-flex align-items-center"
                                                   for="operator{{ $operator->id }}" style="cursor: pointer;">
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
                                            </label>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>

                                <!-- Selected Count -->
                                <div class="mt-2">
                                    <small class="text-muted">
                                        <span id="selectedCount">0</span> operator(s) selected
                                    </small>
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('pic.task-requests.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Cancel
                            </a>
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-paper-plane"></i> Submit Request
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Help Sidebar -->
        <div class="col-lg-4">
            <div class="card shadow-sm">
                <div class="card-header bg-info text-white">
                    <h6 class="mb-0"><i class="fas fa-info-circle"></i> Request Guidelines</h6>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled">
                        <li class="mb-2">
                            <i class="fas fa-check text-success"></i>
                            Select appropriate task type
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check text-success"></i>
                            Set realistic priority level
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check text-success"></i>
                            Provide clear and detailed description
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check text-success"></i>
                            Specify machine if task is machine-specific
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check text-success"></i>
                            Request will be reviewed by admin
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check text-success"></i>
                            You'll be notified of approval/rejection
                        </li>
                    </ul>
                </div>
            </div>

            <div class="card shadow-sm mt-3">
                <div class="card-header bg-warning text-dark">
                    <h6 class="mb-0"><i class="fas fa-lightbulb"></i> Task Types</h6>
                </div>
                <div class="card-body">
                    <div class="mb-2">
                        <strong>Preventive:</strong>
                        <small class="d-block">Scheduled maintenance to prevent failures</small>
                    </div>
                    <div class="mb-2">
                        <strong>Corrective:</strong>
                        <small class="d-block">Fix existing problems or defects</small>
                    </div>
                    <div class="mb-2">
                        <strong>Inspection:</strong>
                        <small class="d-block">Check condition and identify issues</small>
                    </div>
                    <div class="mb-2">
                        <strong>Parts Replacement:</strong>
                        <small class="d-block">Replace worn or damaged components</small>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm mt-3">
                <div class="card-header bg-secondary text-white">
                    <h6 class="mb-0"><i class="fas fa-flag"></i> Priority Guide</h6>
                </div>
                <div class="card-body">
                    <div class="mb-2">
                        <span class="badge bg-secondary">Low</span>
                        <small class="d-block">Can wait, not time-sensitive</small>
                    </div>
                    <div class="mb-2">
                        <span class="badge bg-info">Medium</span>
                        <small class="d-block">Normal scheduling, within 1-2 weeks</small>
                    </div>
                    <div class="mb-2">
                        <span class="badge bg-warning">High</span>
                        <small class="d-block">Should be done soon, within days</small>
                    </div>
                    <div class="mb-2">
                        <span class="badge bg-danger">Urgent</span>
                        <small class="d-block">Immediate attention required</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Toggle assign to all operators
function toggleAssignAll() {
    const assignToAll = document.getElementById('assignToAll');
    const operatorSelectDiv = document.getElementById('operatorSelectDiv');
    const operatorCheckboxes = document.querySelectorAll('.operator-checkbox');

    if (assignToAll.checked) {
        operatorSelectDiv.style.display = 'none';
        operatorCheckboxes.forEach(checkbox => {
            checkbox.checked = false;
            checkbox.disabled = true;
        });
    } else {
        operatorSelectDiv.style.display = 'block';
        operatorCheckboxes.forEach(checkbox => {
            checkbox.disabled = false;
        });
    }
    updateSelectedCount();
}

// Search operators
document.getElementById('operatorSearch')?.addEventListener('input', function(e) {
    const searchTerm = e.target.value.toLowerCase();
    const operatorItems = document.querySelectorAll('.operator-item');

    operatorItems.forEach(item => {
        const checkbox = item.querySelector('.operator-checkbox');
        const name = checkbox.dataset.name;
        const employee = checkbox.dataset.employee;

        if (name.includes(searchTerm) || employee.includes(searchTerm)) {
            item.style.display = 'block';
        } else {
            item.style.display = 'none';
        }
    });
});

// Update selected count
function updateSelectedCount() {
    const assignToAll = document.getElementById('assignToAll');
    if (assignToAll && assignToAll.checked) {
        document.getElementById('selectedCount').textContent = 'All';
        return;
    }

    const checkedBoxes = document.querySelectorAll('.operator-checkbox:checked');
    document.getElementById('selectedCount').textContent = checkedBoxes.length;
}

// Add event listeners to all operator checkboxes
document.querySelectorAll('.operator-checkbox').forEach(checkbox => {
    checkbox.addEventListener('change', updateSelectedCount);
});

// Add click event to assign to all card
document.getElementById('assignToAll')?.addEventListener('change', function() {
    toggleAssignAll();
});

// Initial count
updateSelectedCount();
</script>
@endsection
