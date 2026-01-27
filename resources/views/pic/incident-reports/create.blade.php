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
                <li class="breadcrumb-item active">Report New Incident</li>
            </ol>
        </nav>
        <h2><i class="fas fa-exclamation-triangle"></i> Report New Incident</h2>
        <p class="text-muted">Report a machine incident or malfunction</p>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header bg-danger text-white">
                    <h5 class="mb-0"><i class="fas fa-file-alt"></i> Incident Report Form</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('pic.incident-reports.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <!-- Report Code -->
                        <div class="mb-3">
                            <label class="form-label fw-bold">Report Code</label>
                            <input type="text" name="report_code" class="form-control bg-light"
                                   value="{{ $reportCode }}" readonly>
                            <small class="text-muted">Auto-generated report code</small>
                        </div>

                        <!-- Machine Selection -->
                        <div class="mb-3">
                            <label class="form-label fw-bold">
                                Machine <span class="text-danger">*</span>
                            </label>
                            <select name="machine_id" class="form-select @error('machine_id') is-invalid @enderror" required>
                                <option value="">-- Select Machine --</option>
                                @foreach($machines as $machine)
                                <option value="{{ $machine->id }}" {{ old('machine_id') == $machine->id ? 'selected' : '' }}>
                                    {{ $machine->name }} ({{ $machine->code }})
                                </option>
                                @endforeach
                            </select>
                            @error('machine_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Incident Type -->
                        <div class="mb-3">
                            <label class="form-label fw-bold">
                                Incident Type <span class="text-danger">*</span>
                            </label>
                            <select name="incident_type" class="form-select @error('incident_type') is-invalid @enderror" required>
                                <option value="">-- Select Type --</option>
                                <option value="Breakdown" {{ old('incident_type') == 'Breakdown' ? 'selected' : '' }}>Breakdown</option>
                                <option value="Abnormal Sound" {{ old('incident_type') == 'Abnormal Sound' ? 'selected' : '' }}>Abnormal Sound</option>
                                <option value="Overheating" {{ old('incident_type') == 'Overheating' ? 'selected' : '' }}>Overheating</option>
                                <option value="Leakage" {{ old('incident_type') == 'Leakage' ? 'selected' : '' }}>Leakage</option>
                                <option value="Vibration" {{ old('incident_type') == 'Vibration' ? 'selected' : '' }}>Vibration</option>
                                <option value="Performance Issue" {{ old('incident_type') == 'Performance Issue' ? 'selected' : '' }}>Performance Issue</option>
                                <option value="Safety Hazard" {{ old('incident_type') == 'Safety Hazard' ? 'selected' : '' }}>Safety Hazard</option>
                                <option value="Other" {{ old('incident_type') == 'Other' ? 'selected' : '' }}>Other</option>
                            </select>
                            @error('incident_type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Severity -->
                        <div class="mb-3">
                            <label class="form-label fw-bold">
                                Severity Level <span class="text-danger">*</span>
                            </label>
                            <select name="severity" class="form-select @error('severity') is-invalid @enderror" required>
                                <option value="">-- Select Severity --</option>
                                <option value="low" {{ old('severity') == 'low' ? 'selected' : '' }}>
                                    Low - Minor issue, no immediate action needed
                                </option>
                                <option value="medium" {{ old('severity') == 'medium' ? 'selected' : '' }}>
                                    Medium - Requires attention soon
                                </option>
                                <option value="high" {{ old('severity') == 'high' ? 'selected' : '' }}>
                                    High - Urgent, affects production
                                </option>
                                <option value="critical" {{ old('severity') == 'critical' ? 'selected' : '' }}>
                                    Critical - Machine stopped, immediate action required
                                </option>
                            </select>
                            @error('severity')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Title -->
                        <div class="mb-3">
                            <label class="form-label fw-bold">
                                Incident Title <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="title" class="form-control @error('title') is-invalid @enderror"
                                   value="{{ old('title') }}"
                                   placeholder="Brief summary of the incident"
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
                                      placeholder="Describe what happened, when it started, any unusual observations..."
                                      required>{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Operator Assignment -->
                        <div class="mb-3">
                            <label class="form-label fw-bold">Assign to Operator (Optional)</label>

                            <div class="alert alert-info py-2" role="alert">
                                <small><i class="fas fa-info-circle"></i> Assign operators to work on this incident. You can select multiple operators or assign to everyone.</small>
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
                                <div class="mt-2 p-2 bg-success bg-opacity-10 rounded">
                                    <small class="text-success fw-bold">
                                        <i class="fas fa-user-check"></i>
                                        <span id="selectedCount">0</span> operator(s) selected
                                    </small>
                                </div>
                            </div>

                            <small class="text-muted d-block mt-2">
                                <i class="fas fa-lightbulb"></i> Leave unassigned if admin should assign later
                            </small>
                        </div>

                        <!-- Attachments -->
                        <div class="mb-3">
                            <label class="form-label fw-bold">Attachments (Photos/Videos)</label>
                            <input type="file" name="attachments[]" id="attachmentInput"
                                   class="form-control @error('attachments.*') is-invalid @enderror"
                                   multiple accept="image/*,video/*,.pdf" capture="environment">
                            <small class="text-muted">
                                <i class="fas fa-camera"></i> Take photo directly or upload files (Max 10MB per file)
                            </small>
                            @error('attachments.*')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror

                            <!-- Preview area -->
                            <div id="imagePreview" class="row g-2 mt-2"></div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('pic.incident-reports.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Cancel
                            </a>
                            <button type="submit" class="btn btn-danger">
                                <i class="fas fa-paper-plane"></i> Submit Report
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
                    <h6 class="mb-0"><i class="fas fa-info-circle"></i> Reporting Guidelines</h6>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled">
                        <li class="mb-2">
                            <i class="fas fa-check text-success"></i>
                            Report incidents immediately when discovered
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check text-success"></i>
                            Provide accurate machine information
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check text-success"></i>
                            Include photos/videos if possible
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check text-success"></i>
                            Be specific about what you observed
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check text-success"></i>
                            Select appropriate severity level
                        </li>
                    </ul>
                </div>
            </div>

            <div class="card shadow-sm mt-3">
                <div class="card-header bg-warning text-dark">
                    <h6 class="mb-0"><i class="fas fa-exclamation-triangle"></i> Severity Guide</h6>
                </div>
                <div class="card-body">
                    <div class="mb-2">
                        <span class="badge bg-secondary">Low</span>
                        <small class="d-block">Non-urgent issues</small>
                    </div>
                    <div class="mb-2">
                        <span class="badge bg-info">Medium</span>
                        <small class="d-block">Needs attention but not urgent</small>
                    </div>
                    <div class="mb-2">
                        <span class="badge bg-warning">High</span>
                        <small class="d-block">Affects production, urgent action needed</small>
                    </div>
                    <div class="mb-2">
                        <span class="badge bg-danger">Critical</span>
                        <small class="d-block">Machine stopped, immediate response required</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.operator-item:hover {
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    transform: translateY(-1px);
}

.operator-checkbox:checked + label {
    background-color: #d1e7dd !important;
}
</style>

<script>
// Toggle assign to all
function toggleAssignAll() {
    const checkbox = document.getElementById('assignToAll');
    checkbox.checked = !checkbox.checked;
    handleAssignToAll();
}

// Handle assign to all checkbox
function handleAssignToAll() {
    const assignToAll = document.getElementById('assignToAll');
    const operatorSelectDiv = document.getElementById('operatorSelectDiv');
    const operatorCheckboxes = document.querySelectorAll('.operator-checkbox');

    if (assignToAll.checked) {
        operatorSelectDiv.style.display = 'none';
        // Uncheck all individual operators
        operatorCheckboxes.forEach(cb => cb.checked = false);
        updateSelectedCount();
    } else {
        operatorSelectDiv.style.display = 'block';
    }
}

// Setup assign to all listener
document.getElementById('assignToAll').addEventListener('change', function() {
    handleAssignToAll();
});

// Search functionality
document.getElementById('operatorSearch').addEventListener('input', function(e) {
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
    const selectedCheckboxes = document.querySelectorAll('.operator-checkbox:checked');
    document.getElementById('selectedCount').textContent = selectedCheckboxes.length;
}

// Listen to checkbox changes
document.querySelectorAll('.operator-checkbox').forEach(checkbox => {
    checkbox.addEventListener('change', function() {
        // If any checkbox is checked, uncheck "assign to all"
        if (this.checked) {
            document.getElementById('assignToAll').checked = false;
        }
        updateSelectedCount();
    });
});

// Initialize count
updateSelectedCount();

// Image preview functionality
document.getElementById('attachmentInput').addEventListener('change', function(e) {
    const previewContainer = document.getElementById('imagePreview');
    previewContainer.innerHTML = ''; // Clear previous previews

    const files = Array.from(e.target.files);

    files.forEach((file, index) => {
        if (file.type.startsWith('image/')) {
            const reader = new FileReader();

            reader.onload = function(event) {
                const col = document.createElement('div');
                col.className = 'col-md-3';

                const card = document.createElement('div');
                card.className = 'card';

                const img = document.createElement('img');
                img.src = event.target.result;
                img.className = 'card-img-top';
                img.style.height = '150px';
                img.style.objectFit = 'cover';

                const cardBody = document.createElement('div');
                cardBody.className = 'card-body p-2';
                cardBody.innerHTML = `<small class="text-muted">${file.name}</small>`;

                card.appendChild(img);
                card.appendChild(cardBody);
                col.appendChild(card);
                previewContainer.appendChild(col);
            };

            reader.readAsDataURL(file);
        } else if (file.type.startsWith('video/')) {
            const col = document.createElement('div');
            col.className = 'col-md-3';

            const card = document.createElement('div');
            card.className = 'card';

            const cardBody = document.createElement('div');
            cardBody.className = 'card-body text-center';
            cardBody.innerHTML = `
                <i class="fas fa-video fa-3x text-primary mb-2"></i>
                <br>
                <small class="text-muted">${file.name}</small>
            `;

            card.appendChild(cardBody);
            col.appendChild(card);
            previewContainer.appendChild(col);
        }
    });
});
</script>
@endsection
