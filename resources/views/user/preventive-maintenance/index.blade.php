@extends('layouts.user')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1"><i class="fas fa-calendar-check"></i> My PM Tasks</h4>
            <p class="text-muted mb-0">View your assigned preventive maintenance tasks</p>
        </div>
    </div>

    <!-- Stats Cards -->
    @if($totalTasks > 0)
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <h3 class="mb-1 fw-bold">{{ $totalTasks }}</h3>
                    <small class="text-muted">Total Tasks</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <h3 class="mb-1 fw-bold text-success">{{ $completedTasks }}</h3>
                    <small class="text-muted">Completed</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <h3 class="mb-1 fw-bold text-primary">{{ $inProgressTasks }}</h3>
                    <small class="text-muted">In Progress</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <h3 class="mb-1 fw-bold text-warning">{{ $pendingTasks }}</h3>
                    <small class="text-muted">Pending</small>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Filter by Month</label>
                    <input type="month" name="month" class="form-control" value="{{ request('month') }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">All Status</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                        <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                    </select>
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <button type="submit" class="btn btn-outline-primary me-2">
                        <i class="fas fa-search"></i> Filter
                    </button>
                    <a href="{{ route('user.preventive-maintenance.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-times"></i> Reset
                    </a>
                </div>
            </form>
        </div>
    </div>

    @if($tasksByMonth->isEmpty())
    <div class="card">
        <div class="card-body text-center py-4 text-muted">
            <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
            No PM tasks assigned to you
        </div>
    </div>
    @endif

    <!-- Tasks grouped by month -->
    @foreach($tasksByMonth as $month => $tasks)
    @php
        $monthDate = \Carbon\Carbon::parse($month . '-01');
        $stats = $monthlyStats[$month];
    @endphp
    <div class="card mb-4">
        <div class="card-header bg-white d-flex justify-content-between align-items-center" role="button" data-bs-toggle="collapse" data-bs-target="#month-{{ $month }}">
            <div>
                <h5 class="mb-0">
                    <i class="fas fa-calendar me-2"></i>{{ $monthDate->format('F Y') }}
                    <span class="badge bg-secondary ms-2">{{ $stats['total'] }} tasks</span>
                </h5>
            </div>
            <div class="d-flex align-items-center gap-3">
                <div class="d-flex gap-2">
                    @if($stats['completed'] > 0)
                        <span class="badge bg-success">{{ $stats['completed'] }} done</span>
                    @endif
                    @if($stats['in_progress'] > 0)
                        <span class="badge bg-primary">{{ $stats['in_progress'] }} in progress</span>
                    @endif
                    @if($stats['pending'] > 0)
                        <span class="badge bg-warning text-dark">{{ $stats['pending'] }} pending</span>
                    @endif
                </div>
                <div style="width: 120px;">
                    <div class="progress" style="height: 8px;">
                        <div class="progress-bar bg-success" style="width: {{ $stats['progress'] }}%"></div>
                    </div>
                    <small class="text-muted">{{ $stats['progress'] }}%</small>
                </div>
                <i class="fas fa-chevron-down"></i>
            </div>
        </div>
        <div class="collapse show" id="month-{{ $month }}">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Date</th>
                                <th>Task Name</th>
                                <th>Description</th>
                                <th>Equipment</th>
                                <th>Shift</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($tasks->sortBy('task_date') as $task)
                                @php
                                    $latestReport = $task->latestReport;
                                    $reportStatus = $latestReport ? $latestReport->status : null;
                                @endphp
                                <tr class="{{ $task->status === 'completed' ? 'table-success' : '' }}">
                                    <td>
                                        <span class="fw-semibold">{{ \Carbon\Carbon::parse($task->task_date)->format('d') }}</span>
                                        <small class="text-muted d-block">{{ \Carbon\Carbon::parse($task->task_date)->format('D') }}</small>
                                    </td>
                                    <td>
                                        {{ $task->task_name }}
                                        @if($task->is_recurring || $task->parent_task_id)
                                            <i class="fas fa-sync-alt text-muted ms-1" style="font-size: 10px;" title="Recurring"></i>
                                        @endif
                                    </td>
                                    <td>
                                        <small class="text-muted">{{ Str::limit($task->task_description, 50) ?? '-' }}</small>
                                    </td>
                                    <td>{{ $task->equipment_type ?? '-' }}</td>
                                    <td>
                                        @if($task->assigned_shift_id)
                                            @php
                                                $shiftColors = [1 => 'primary', 2 => 'info', 3 => 'success'];
                                            @endphp
                                            <span class="badge bg-{{ $shiftColors[$task->assigned_shift_id] ?? 'secondary' }}">
                                                Shift {{ $task->assigned_shift_id }}
                                            </span>
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $task->status_badge }}" id="statusBadge-{{ $task->id }}">
                                            {{ ucfirst(str_replace('_', ' ', $task->status)) }}
                                        </span>
                                        @if($reportStatus)
                                            <br>
                                            <span class="badge {{ $latestReport->getStatusBadgeClass() }} mt-1" style="font-size: 10px;">
                                                <i class="fas fa-file-alt"></i> {{ $latestReport->getStatusLabel() }}
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($reportStatus === 'approved')
                                            <span class="text-success"><i class="fas fa-check-circle"></i> Approved</span>
                                        @elseif($reportStatus === 'submitted')
                                            <span class="text-info"><i class="fas fa-clock"></i> Pending Review</span>
                                        @elseif($reportStatus === 'revision_needed')
                                            <button type="button" class="btn btn-sm btn-warning" onclick="openReportModal({{ $task->id }}, '{{ addslashes($task->task_name) }}', '{{ $task->task_date?->format('d M Y') }}', {{ $task->assigned_shift_id ?? 'null' }})">
                                                <i class="fas fa-edit"></i> Revise
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-info" onclick="viewReport({{ $task->id }}, {{ $latestReport->id }})">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                        @elseif($task->status !== 'completed')
                                            <div class="d-flex gap-1">
                                                <select class="form-select form-select-sm" style="min-width: 120px;" onchange="updateTaskStatus({{ $task->id }}, this.value, this)">
                                                    <option value="" disabled selected>Status</option>
                                                    <option value="pending" {{ $task->status === 'pending' ? 'disabled' : '' }}>Pending</option>
                                                    <option value="in_progress" {{ $task->status === 'in_progress' ? 'disabled' : '' }}>In Progress</option>
                                                </select>
                                                <button type="button" class="btn btn-sm btn-primary" onclick="openReportModal({{ $task->id }}, '{{ addslashes($task->task_name) }}', '{{ $task->task_date?->format('d M Y') }}', {{ $task->assigned_shift_id ?? 'null' }})" title="Submit Report">
                                                    <i class="fas fa-file-alt"></i>
                                                </button>
                                            </div>
                                        @else
                                            {{-- Completed without report --}}
                                            <button type="button" class="btn btn-sm btn-outline-primary" onclick="openReportModal({{ $task->id }}, '{{ addslashes($task->task_name) }}', '{{ $task->task_date?->format('d M Y') }}', {{ $task->assigned_shift_id ?? 'null' }})">
                                                <i class="fas fa-file-alt"></i> Submit Report
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    @endforeach
</div>

<!-- Submit Report Modal -->
<div class="modal fade" id="reportModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="reportForm" enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-file-alt me-2"></i>Submit PM Report</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <!-- Task Info -->
                    <div class="alert alert-light border mb-3">
                        <strong id="reportTaskName"></strong>
                        <br><small class="text-muted">Date: <span id="reportTaskDate"></span> | Shift: <span id="reportTaskShift"></span></small>
                    </div>
                    <input type="hidden" id="reportTaskId">

                    <!-- Description -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Detail Kegiatan PM <span class="text-danger">*</span></label>
                        <textarea name="description" id="reportDescription" class="form-control" rows="4" required placeholder="Jelaskan detail kegiatan PM yang dilakukan..."></textarea>
                    </div>

                    <!-- Further Repair Assets -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Asset yang Butuh Further Repair</label>
                        <small class="text-muted d-block mb-2">Pilih asset yang memerlukan perbaikan lanjutan (opsional)</small>
                        <select id="assetSelect" class="form-select" multiple>
                        </select>
                        <div id="selectedAssetsContainer" class="mt-2"></div>
                    </div>

                    <!-- Photos -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Foto Dokumentasi</label>
                        <input type="file" name="photos[]" id="reportPhotos" class="form-control" multiple accept="image/*">
                        <small class="text-muted">Max 5MB per foto. Bisa pilih beberapa foto.</small>
                        <div id="photoPreview" class="d-flex flex-wrap gap-2 mt-2"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="submitReportBtn">
                        <i class="fas fa-paper-plane me-1"></i> Submit Report
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- View Report Modal -->
<div class="modal fade" id="viewReportModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-file-alt me-2"></i>Report Detail</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="viewReportBody">
                <div class="text-center py-4">
                    <div class="spinner-border text-primary"></div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet">
<style>
    .select2-container--open { z-index: 1060 !important; }
</style>
@endpush

@push('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
const assetSearchUrl = '{{ route("user.preventive-maintenance.assets.search") }}';
const reportBaseUrl = '/user/preventive-maintenance/task';

let selectedAssets = [];

// Initialize Select2 for asset search
$(document).ready(function() {
    $('#assetSelect').select2({
        placeholder: 'Klik untuk memilih asset...',
        allowClear: true,
        width: '100%',
        theme: 'bootstrap-5',
        ajax: {
            url: assetSearchUrl,
            dataType: 'json',
            delay: 250,
            data: function(params) {
                return { q: params.term || '' };
            },
            processResults: function(data) {
                return { results: data.results };
            },
            cache: true,
        },
        minimumInputLength: 0,
    });

    $('#assetSelect').on('select2:select', function(e) {
        const asset = e.params.data;
        if (!selectedAssets.find(a => a.id == asset.id)) {
            selectedAssets.push({ id: asset.id, text: asset.text, notes: '' });
            renderSelectedAssets();
        }
        $(this).val(null).trigger('change');
    });
});

function renderSelectedAssets() {
    const container = document.getElementById('selectedAssetsContainer');
    if (selectedAssets.length === 0) {
        container.innerHTML = '';
        return;
    }
    let html = '<div class="list-group">';
    selectedAssets.forEach((asset, index) => {
        html += `
            <div class="list-group-item">
                <div class="d-flex justify-content-between align-items-start">
                    <div class="flex-grow-1">
                        <strong>${asset.text}</strong>
                        <input type="text" class="form-control form-control-sm mt-1"
                            placeholder="Catatan (opsional)"
                            value="${asset.notes}"
                            onchange="selectedAssets[${index}].notes = this.value">
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-danger ms-2" onclick="removeAsset(${index})">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>`;
    });
    html += '</div>';
    container.innerHTML = html;
}

function removeAsset(index) {
    selectedAssets.splice(index, 1);
    renderSelectedAssets();
}

function openReportModal(taskId, taskName, taskDate, shiftId) {
    document.getElementById('reportTaskId').value = taskId;
    document.getElementById('reportTaskName').textContent = taskName;
    document.getElementById('reportTaskDate').textContent = taskDate;
    document.getElementById('reportTaskShift').textContent = shiftId ? 'Shift ' + shiftId : '-';
    document.getElementById('reportDescription').value = '';
    document.getElementById('reportPhotos').value = '';
    document.getElementById('photoPreview').innerHTML = '';
    selectedAssets = [];
    renderSelectedAssets();
    $('#assetSelect').val(null).trigger('change');

    const modalEl = document.getElementById('reportModal');
    const modal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
    modal.show();
}

// Photo preview
document.getElementById('reportPhotos').addEventListener('change', function() {
    const preview = document.getElementById('photoPreview');
    preview.innerHTML = '';
    Array.from(this.files).forEach(file => {
        const reader = new FileReader();
        reader.onload = function(e) {
            const img = document.createElement('img');
            img.src = e.target.result;
            img.style.width = '80px';
            img.style.height = '80px';
            img.style.objectFit = 'cover';
            img.className = 'rounded border';
            preview.appendChild(img);
        };
        reader.readAsDataURL(file);
    });
});

// Submit report form
document.getElementById('reportForm').addEventListener('submit', function(e) {
    e.preventDefault();

    const taskId = document.getElementById('reportTaskId').value;
    const btn = document.getElementById('submitReportBtn');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Submitting...';

    const formData = new FormData();
    formData.append('description', document.getElementById('reportDescription').value);
    formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);

    // Add photos
    const photos = document.getElementById('reportPhotos').files;
    for (let i = 0; i < photos.length; i++) {
        formData.append('photos[]', photos[i]);
    }

    // Add assets
    selectedAssets.forEach((asset, i) => {
        formData.append(`assets[${i}][id]`, asset.id);
        formData.append(`assets[${i}][notes]`, asset.notes);
    });

    fetch(`${reportBaseUrl}/${taskId}/report`, {
        method: 'POST',
        headers: { 'Accept': 'application/json' },
        body: formData,
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            bootstrap.Modal.getInstance(document.getElementById('reportModal')).hide();
            location.reload();
        } else {
            alert(data.message || 'Failed to submit report');
        }
    })
    .catch(err => {
        alert('An error occurred while submitting the report');
        console.error(err);
    })
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-paper-plane me-1"></i> Submit Report';
    });
});

// View report
function viewReport(taskId, reportId) {
    const modalEl = document.getElementById('viewReportModal');
    const modal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
    const body = document.getElementById('viewReportBody');
    body.innerHTML = '<div class="text-center py-4"><div class="spinner-border text-primary"></div></div>';
    modal.show();

    fetch(`${reportBaseUrl}/${taskId}/report/${reportId}`, {
        headers: { 'Accept': 'application/json' },
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            const r = data.report;
            let html = `
                <div class="alert alert-light border">
                    <strong>${r.task.task_name}</strong><br>
                    <small class="text-muted">Date: ${r.task.task_date} | Shift: ${r.task.shift ? 'Shift ' + r.task.shift : '-'}</small>
                </div>
                <div class="mb-3">
                    <label class="fw-semibold">Status</label><br>
                    <span class="badge ${r.status_badge}">${r.status_label}</span>
                    <small class="text-muted ms-2">Submitted by ${r.submitted_by} on ${r.submitted_at}</small>
                </div>`;

            if (r.admin_comments) {
                html += `<div class="alert alert-warning mb-3">
                    <strong>Admin Comments:</strong><br>${r.admin_comments}
                </div>`;
            }

            html += `<div class="mb-3">
                <label class="fw-semibold">Detail Kegiatan</label>
                <p class="mb-0">${r.description}</p>
            </div>`;

            // Photos
            if (r.photos && r.photos.length > 0) {
                html += '<div class="mb-3"><label class="fw-semibold">Foto</label><div class="d-flex flex-wrap gap-2">';
                r.photos.forEach(p => {
                    html += `<a href="${p.url}" target="_blank"><img src="${p.url}" style="width:100px;height:100px;object-fit:cover;" class="rounded border"></a>`;
                });
                html += '</div></div>';
            }

            // Further repair assets
            if (r.further_repair_assets && r.further_repair_assets.length > 0) {
                html += '<div class="mb-3"><label class="fw-semibold">Further Repair Assets</label><div class="table-responsive"><table class="table table-sm table-bordered"><thead><tr><th>Equipment ID</th><th>Name</th><th>Location</th><th>Notes</th></tr></thead><tbody>';
                r.further_repair_assets.forEach(a => {
                    html += `<tr><td>${a.equipment_id}</td><td>${a.asset_name}</td><td>${a.location || '-'}</td><td>${a.notes || '-'}</td></tr>`;
                });
                html += '</tbody></table></div></div>';
            }

            body.innerHTML = html;
        }
    })
    .catch(() => {
        body.innerHTML = '<div class="text-center text-danger py-4">Failed to load report</div>';
    });
}

// Update task status (for non-completed status changes)
function updateTaskStatus(taskId, status, selectEl) {
    if (!status) return;

    fetch(`${reportBaseUrl}/${taskId}/status`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
        },
        body: JSON.stringify({ status: status })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const badge = document.getElementById('statusBadge-' + taskId);
            const badgeColors = { pending: 'secondary', in_progress: 'warning', completed: 'success' };
            const badgeLabels = { pending: 'Pending', in_progress: 'In Progress', completed: 'Completed' };

            badge.className = 'badge bg-' + (badgeColors[status] || 'secondary');
            badge.textContent = badgeLabels[status] || status;
            selectEl.value = '';
            Array.from(selectEl.options).forEach(opt => {
                opt.disabled = (opt.value === status);
            });
        } else {
            alert('Failed to update status');
            selectEl.value = '';
        }
    })
    .catch(() => {
        alert('An error occurred');
        selectEl.value = '';
    });
}
</script>
@endpush
@endsection
