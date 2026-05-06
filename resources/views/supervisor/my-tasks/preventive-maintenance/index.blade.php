@extends('layouts.admin')

@section('page-title', 'My PM Tasks')

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
    <div class="row g-2 mb-4">
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center py-2 py-md-3">
                    <h4 class="mb-1 fw-bold">{{ $totalTasks }}</h4>
                    <small class="text-muted">Total Tasks</small>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center py-2 py-md-3">
                    <h4 class="mb-1 fw-bold text-success">{{ $completedTasks }}</h4>
                    <small class="text-muted">Completed</small>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center py-2 py-md-3">
                    <h4 class="mb-1 fw-bold text-primary">{{ $inProgressTasks }}</h4>
                    <small class="text-muted">In Progress</small>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center py-2 py-md-3">
                    <h4 class="mb-1 fw-bold text-warning">{{ $pendingTasks }}</h4>
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
                <div class="col-12 col-md-4 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-outline-primary">
                        <i class="fas fa-search"></i> Filter
                    </button>
                    <a href="{{ route('supervisor.my-tasks.preventive-maintenance') }}" class="btn btn-outline-secondary">
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
        <div class="card-header bg-white" role="button" data-bs-toggle="collapse" data-bs-target="#month-{{ $month }}">
            <div class="d-flex justify-content-between align-items-center">
                <h6 class="mb-0">
                    <i class="fas fa-calendar me-1"></i>{{ $monthDate->format('M Y') }}
                    <span class="badge bg-secondary ms-1">{{ $stats['total'] }}</span>
                </h6>
                <div class="d-flex align-items-center gap-2">
                    <div class="d-none d-sm-flex gap-1">
                        @if($stats['completed'] > 0)
                            <span class="badge bg-success">{{ $stats['completed'] }} done</span>
                        @endif
                        @if($stats['in_progress'] > 0)
                            <span class="badge bg-primary">{{ $stats['in_progress'] }} prog</span>
                        @endif
                        @if($stats['pending'] > 0)
                            <span class="badge bg-warning text-dark">{{ $stats['pending'] }} pend</span>
                        @endif
                    </div>
                    <div style="width: 80px;">
                        <div class="progress" style="height: 6px;">
                            <div class="progress-bar bg-success" style="width: {{ $stats['progress'] }}%"></div>
                        </div>
                        <small class="text-muted" style="font-size:11px;">{{ $stats['progress'] }}%</small>
                    </div>
                    <i class="fas fa-chevron-down text-muted"></i>
                </div>
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
                                        <a href="{{ route('supervisor.my-tasks.preventive-maintenance.task.show', $task->id) }}" class="text-decoration-none text-dark fw-semibold">
                                            {{ $task->task_name }}
                                        </a>
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
                                            @if($reportStatus === 'sparepart_rejected' && $latestReport->sparepart_approval_notes)
                                                <br><small class="text-danger" style="font-size:10px;" title="{{ $latestReport->sparepart_approval_notes }}">
                                                    <i class="fas fa-exclamation-circle"></i> {{ Str::limit($latestReport->sparepart_approval_notes, 40) }}
                                                </small>
                                            @endif
                                            @if($latestReport->timing_label)
                                                <br>
                                                <span class="badge {{ $latestReport->timing_badge_class }} mt-1" style="font-size: 10px;" title="Submitted on {{ $latestReport->submitted_at?->format('d M Y') }}">
                                                    <i class="fas fa-clock"></i> {{ $latestReport->timing_label }}
                                                </span>
                                            @endif
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
                                            <div class="d-flex flex-wrap gap-1">
                                                <select class="form-select form-select-sm" style="min-width: 90px; max-width: 110px;" onchange="updateTaskStatus({{ $task->id }}, this.value, this)">
                                                    <option value="" disabled selected>Status</option>
                                                    <option value="pending" {{ $task->status === 'pending' ? 'disabled' : '' }}>Pending</option>
                                                    <option value="in_progress" {{ $task->status === 'in_progress' ? 'disabled' : '' }}>In Progress</option>
                                                </select>
                                                <button type="button" class="btn btn-sm btn-primary" onclick="openReportModal({{ $task->id }}, '{{ addslashes($task->task_name) }}', '{{ $task->task_date?->format('d M Y') }}', {{ $task->assigned_shift_id ?? 'null' }})" title="Submit Report">
                                                    <i class="fas fa-file-alt"></i>
                                                </button>
                                            </div>
                                        @else
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
            <form id="reportForm_spv" enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-file-alt me-2"></i>Submit PM Report</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-light border mb-3">
                        <strong id="reportTaskName"></strong>
                        <br><small class="text-muted">Date: <span id="reportTaskDate"></span> | Shift: <span id="reportTaskShift"></span></small>
                    </div>
                    <input type="hidden" id="reportTaskId">

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Detail Kegiatan PM <span class="text-danger">*</span></label>
                        <textarea name="description" id="reportDescription" class="form-control" rows="4" required placeholder="Jelaskan detail kegiatan PM yang dilakukan..."></textarea>
                    </div>

                    @include('supervisor.my-tasks.preventive-maintenance.partials.pm-sparepart-usage', ['formId' => 'spv'])

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
                <div class="text-center py-4"><div class="spinner-border text-primary"></div></div>
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
<script>
const reportBaseUrl = '/supervisor/my-tasks/preventive-maintenance/task';

function openReportModal(taskId, taskName, taskDate, shiftId) {
    document.getElementById('reportTaskId').value = taskId;
    document.getElementById('reportTaskName').textContent = taskName;
    document.getElementById('reportTaskDate').textContent = taskDate;
    document.getElementById('reportTaskShift').textContent = shiftId ? 'Shift ' + shiftId : '-';
    document.getElementById('reportDescription').value = '';
    document.getElementById('reportPhotos').value = '';
    document.getElementById('photoPreview').innerHTML = '';

    const noRadio = document.getElementById('spUsageNo_spv');
    if (noRadio) { noRadio.checked = true; noRadio.dispatchEvent(new Event('change')); }
    const rows = document.getElementById('sparepartRows_spv');
    if (rows) rows.innerHTML = '';

    const modalEl = document.getElementById('reportModal');
    const modal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
    modal.show();
}

document.getElementById('reportPhotos').addEventListener('change', function() {
    const preview = document.getElementById('photoPreview');
    preview.innerHTML = '';
    Array.from(this.files).forEach(file => {
        const reader = new FileReader();
        reader.onload = function(e) {
            const img = document.createElement('img');
            img.src = e.target.result;
            img.style.cssText = 'width:80px;height:80px;object-fit:cover;';
            img.className = 'rounded border';
            preview.appendChild(img);
        };
        reader.readAsDataURL(file);
    });
});

document.getElementById('reportForm_spv').addEventListener('submit', function(e) {
    e.preventDefault();
    const taskId = document.getElementById('reportTaskId').value;
    const btn = document.getElementById('submitReportBtn');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Submitting...';

    const formData = new FormData(this);

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
        } else { alert(data.message || 'Failed to submit report'); }
    })
    .catch(() => alert('An error occurred'))
    .finally(() => { btn.disabled = false; btn.innerHTML = '<i class="fas fa-paper-plane me-1"></i> Submit Report'; });
});

function viewReport(taskId, reportId) {
    const modalEl = document.getElementById('viewReportModal');
    const modal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
    const body = document.getElementById('viewReportBody');
    body.innerHTML = '<div class="text-center py-4"><div class="spinner-border text-primary"></div></div>';
    modal.show();

    fetch(`${reportBaseUrl}/${taskId}/report/${reportId}`, { headers: { 'Accept': 'application/json' } })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            const r = data.report;
            let html = `<div class="alert alert-light border"><strong>${r.task.task_name}</strong><br><small class="text-muted">Date: ${r.task.task_date} | Shift: ${r.task.shift ? 'Shift ' + r.task.shift : '-'}</small></div>
                <div class="mb-3"><label class="fw-semibold">Status</label><br><span class="badge ${r.status_badge}">${r.status_label}</span><small class="text-muted ms-2">Submitted by ${r.submitted_by} on ${r.submitted_at}</small></div>`;
            if (r.admin_comments) html += `<div class="alert alert-warning mb-3"><strong>Admin Comments:</strong><br>${r.admin_comments}</div>`;
            if (r.status === 'sparepart_rejected') html += `<div class="alert alert-danger mb-3"><strong><i class="fas fa-times-circle me-1"></i> Penggunaan Sparepart Ditolak</strong><br>${r.sparepart_approval_notes || '-'}<br><small class="text-muted">Ditolak oleh ${r.sparepart_approved_by || '-'} pada ${r.sparepart_approved_at || '-'}</small></div>`;
            html += `<div class="mb-3"><label class="fw-semibold">Detail Kegiatan</label><p class="mb-0">${r.description}</p></div>`;
            if (r.photos && r.photos.length > 0) {
                html += '<div class="mb-3"><label class="fw-semibold">Foto</label><div class="d-flex flex-wrap gap-2">';
                r.photos.forEach(p => { html += `<a href="${p.url}" target="_blank"><img src="${p.url}" style="width:100px;height:100px;object-fit:cover;" class="rounded border"></a>`; });
                html += '</div></div>';
            }
            if (r.further_repair_assets && r.further_repair_assets.length > 0) {
                html += '<div class="mb-3"><label class="fw-semibold">Sparepart</label><div class="table-responsive"><table class="table table-sm table-bordered"><thead><tr><th>Equipment ID</th><th>Name</th><th>Location</th><th>Notes</th></tr></thead><tbody>';
                r.further_repair_assets.forEach(a => { html += `<tr><td>${a.equipment_id}</td><td>${a.asset_name}</td><td>${a.location || '-'}</td><td>${a.notes || '-'}</td></tr>`; });
                html += '</tbody></table></div></div>';
            }
            body.innerHTML = html;
        }
    })
    .catch(() => { body.innerHTML = '<div class="text-center text-danger py-4">Failed to load report</div>'; });
}

function updateTaskStatus(taskId, status, selectEl) {
    if (!status) return;
    fetch(`${reportBaseUrl}/${taskId}/status`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' },
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
            Array.from(selectEl.options).forEach(opt => { opt.disabled = (opt.value === status); });
        } else { alert('Failed to update status'); selectEl.value = ''; }
    })
    .catch(() => { alert('An error occurred'); selectEl.value = ''; });
}
</script>
@endpush
@endsection
