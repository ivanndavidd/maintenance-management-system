@extends('layouts.admin')

@section('page-title', 'PM Reports')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1"><i class="fas fa-file-alt"></i> PM Task Reports</h4>
            <p class="text-muted mb-0">Review and manage preventive maintenance reports</p>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Filter by Month</label>
                    <input type="month" name="month" class="form-control" value="{{ request('month') }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Report Status</label>
                    <select name="report_status" class="form-select">
                        <option value="">All</option>
                        <option value="submitted" {{ request('report_status') == 'submitted' ? 'selected' : '' }}>Submitted (Pending Review)</option>
                        <option value="approved" {{ request('report_status') == 'approved' ? 'selected' : '' }}>Approved</option>
                        <option value="revision_needed" {{ request('report_status') == 'revision_needed' ? 'selected' : '' }}>Revision Needed</option>
                        <option value="no_report" {{ request('report_status') == 'no_report' ? 'selected' : '' }}>No Report</option>
                    </select>
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <button type="submit" class="btn btn-outline-primary me-2">
                        <i class="fas fa-search"></i> Filter
                    </button>
                    <a href="{{ route($routePrefix . '.preventive-maintenance.reports') }}" class="btn btn-outline-secondary">
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
            No PM tasks found
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
                    @if($stats['approved'] > 0)
                        <span class="badge bg-success">{{ $stats['approved'] }} approved</span>
                    @endif
                    @if($stats['submitted'] > 0)
                        <span class="badge bg-info">{{ $stats['submitted'] }} pending review</span>
                    @endif
                    @if($stats['revision_needed'] > 0)
                        <span class="badge bg-warning text-dark">{{ $stats['revision_needed'] }} revision</span>
                    @endif
                    @if($stats['no_report'] > 0)
                        <span class="badge bg-secondary">{{ $stats['no_report'] }} no report</span>
                    @endif
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
                                <th>Assigned To</th>
                                <th>Shift</th>
                                <th>Task Status</th>
                                <th>Report Status</th>
                                <th>Further Repair</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($tasks->sortBy('task_date') as $task)
                                @php
                                    $latestReport = $task->latestReport;
                                    $reportStatus = $latestReport ? $latestReport->status : null;
                                @endphp
                                <tr>
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
                                    <td>{{ $task->assignedUser->name ?? '-' }}</td>
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
                                        <span class="badge bg-{{ $task->status_badge }}">
                                            {{ ucfirst(str_replace('_', ' ', $task->status)) }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($reportStatus)
                                            <span class="badge {{ $latestReport->getStatusBadgeClass() }}">
                                                {{ $latestReport->getStatusLabel() }}
                                            </span>
                                        @else
                                            <span class="badge bg-secondary">No Report</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($latestReport && $latestReport->furtherRepairAssets && $latestReport->furtherRepairAssets->count() > 0)
                                            <span class="badge bg-warning text-dark">
                                                <i class="fas fa-tools"></i> {{ $latestReport->furtherRepairAssets->count() }} asset(s)
                                            </span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($latestReport)
                                            <button type="button" class="btn btn-sm btn-outline-info" onclick="viewReport({{ $latestReport->id }})" title="View Report">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                        @else
                                            <span class="text-muted">-</span>
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

<!-- Report Detail & Review Modal -->
<div class="modal fade" id="reportDetailModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-file-alt me-2"></i>Report Detail</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="reportDetailBody">
                <div class="text-center py-4"><div class="spinner-border text-primary"></div></div>
            </div>
        </div>
    </div>
</div>

<!-- Create CM Ticket Modal -->
<div class="modal fade" id="createCmModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-wrench me-2"></i>Create CM Ticket</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info">
                    <strong>Asset:</strong> <span id="cmAssetInfo"></span>
                </div>
                <input type="hidden" id="cmReportId">
                <input type="hidden" id="cmAssetId">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Additional Notes</label>
                    <textarea id="cmNotes" class="form-control" rows="3" placeholder="Catatan tambahan (opsional)"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="confirmCreateCm" onclick="confirmCreateCmTicket()">
                    <i class="fas fa-plus me-1"></i> Create CM Ticket
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
const reportShowUrl = '{{ route($routePrefix . ".preventive-maintenance.reports.show", ":id") }}';
const reportReviewUrl = '{{ route($routePrefix . ".preventive-maintenance.reports.review", ":id") }}';
const reportCreateCmUrl = '{{ route($routePrefix . ".preventive-maintenance.reports.create-cm", ":id") }}';
const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

let currentReportId = null;

function viewReport(reportId) {
    currentReportId = reportId;
    const modalEl = document.getElementById('reportDetailModal');
    const modal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
    const body = document.getElementById('reportDetailBody');
    body.innerHTML = '<div class="text-center py-4"><div class="spinner-border text-primary"></div></div>';
    modal.show();

    fetch(reportShowUrl.replace(':id', reportId), { headers: { 'Accept': 'application/json' } })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            const r = data.report;
            let html = '';

            // Task info
            html += `<div class="alert alert-light border">
                <div class="row">
                    <div class="col-md-6">
                        <strong><i class="fas fa-tasks me-1"></i> ${r.task.task_name}</strong><br>
                        <small class="text-muted">Date: ${r.task.task_date} | Shift: ${r.task.shift ? 'Shift ' + r.task.shift : '-'}</small>
                    </div>
                    <div class="col-md-6 text-md-end">
                        <small class="text-muted">Assigned to: <strong>${r.task.assigned_to}</strong></small><br>
                        <small class="text-muted">Submitted by: ${r.submitted_by} on ${r.submitted_at}</small>
                    </div>
                </div>
            </div>`;

            // Status
            html += `<div class="mb-3">
                <label class="fw-semibold d-block">Report Status</label>
                <span class="badge ${r.status_badge}">${r.status_label}</span>`;
            if (r.reviewed_by) {
                html += `<small class="text-muted ms-2">Reviewed by ${r.reviewed_by} on ${r.reviewed_at}</small>`;
            }
            html += `</div>`;

            // Admin comments
            if (r.admin_comments) {
                html += `<div class="alert alert-warning mb-3">
                    <strong><i class="fas fa-comment me-1"></i> Review Comments:</strong><br>${r.admin_comments}
                </div>`;
            }

            // Description
            html += `<div class="mb-3">
                <label class="fw-semibold">Detail Kegiatan</label>
                <div class="border rounded p-3 bg-light">${r.description.replace(/\n/g, '<br>')}</div>
            </div>`;

            // Photos
            if (r.photos && r.photos.length > 0) {
                html += `<div class="mb-3">
                    <label class="fw-semibold">Foto Dokumentasi (${r.photos.length})</label>
                    <div class="d-flex flex-wrap gap-2">`;
                r.photos.forEach(p => {
                    html += `<a href="${p.url}" target="_blank" class="position-relative">
                        <img src="${p.url}" style="width:120px;height:120px;object-fit:cover;" class="rounded border">
                    </a>`;
                });
                html += `</div></div>`;
            }

            // Further repair assets
            if (r.further_repair_assets && r.further_repair_assets.length > 0) {
                html += `<div class="mb-3">
                    <label class="fw-semibold"><i class="fas fa-tools me-1"></i> Further Repair Assets</label>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered">
                            <thead class="table-light">
                                <tr><th>Equipment ID</th><th>Name</th><th>Location</th><th>Notes</th><th>Action</th></tr>
                            </thead>
                            <tbody>`;
                r.further_repair_assets.forEach(a => {
                    html += `<tr>
                        <td><code>${a.equipment_id}</code></td>
                        <td>${a.asset_name}</td>
                        <td>${a.location || '-'}</td>
                        <td>${a.notes || '-'}</td>
                        <td>
                            <button type="button" class="btn btn-sm btn-outline-primary" onclick="openCreateCmModal(${r.id}, ${a.id}, '${a.equipment_id} - ${a.asset_name.replace(/'/g, "\\'")}')">
                                <i class="fas fa-wrench me-1"></i> Create CM
                            </button>
                        </td>
                    </tr>`;
                });
                html += `</tbody></table></div></div>`;
            }

            // Review actions (only if status is 'submitted')
            if (r.status === 'submitted') {
                html += `<hr>
                <div class="mb-3">
                    <label class="fw-semibold">Review Actions</label>
                    <div class="mb-2">
                        <textarea id="reviewComments" class="form-control" rows="2" placeholder="Comments (required for revision request)"></textarea>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-success" onclick="reviewReport(${r.id}, 'approved')">
                            <i class="fas fa-check me-1"></i> Approve
                        </button>
                        <button type="button" class="btn btn-warning" onclick="reviewReport(${r.id}, 'revision_needed')">
                            <i class="fas fa-redo me-1"></i> Request Revision
                        </button>
                    </div>
                </div>`;
            }

            body.innerHTML = html;
        }
    })
    .catch(() => { body.innerHTML = '<div class="text-center text-danger py-4">Failed to load report</div>'; });
}

function reviewReport(reportId, status) {
    const comments = document.getElementById('reviewComments')?.value || '';

    if (status === 'revision_needed' && !comments.trim()) {
        alert('Please provide comments when requesting revision.');
        return;
    }

    const btn = event.target;
    btn.disabled = true;
    const originalHtml = btn.innerHTML;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';

    fetch(reportReviewUrl.replace(':id', reportId), {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json',
        },
        body: JSON.stringify({ status: status, admin_comments: comments }),
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            bootstrap.Modal.getInstance(document.getElementById('reportDetailModal')).hide();
            location.reload();
        } else {
            alert(data.message || 'Failed to review report');
        }
    })
    .catch(() => alert('An error occurred'))
    .finally(() => { btn.disabled = false; btn.innerHTML = originalHtml; });
}

function openCreateCmModal(reportId, assetId, assetInfo) {
    document.getElementById('cmReportId').value = reportId;
    document.getElementById('cmAssetId').value = assetId;
    document.getElementById('cmAssetInfo').textContent = assetInfo;
    document.getElementById('cmNotes').value = '';
    const cmModalEl = document.getElementById('createCmModal');
    const cmModal = bootstrap.Modal.getInstance(cmModalEl) || new bootstrap.Modal(cmModalEl);
    cmModal.show();
}

function confirmCreateCmTicket() {
    const reportId = document.getElementById('cmReportId').value;
    const assetId = document.getElementById('cmAssetId').value;
    const notes = document.getElementById('cmNotes').value;
    const btn = document.getElementById('confirmCreateCm');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Creating...';

    fetch(reportCreateCmUrl.replace(':id', reportId), {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json',
        },
        body: JSON.stringify({ asset_id: assetId, notes: notes }),
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            bootstrap.Modal.getInstance(document.getElementById('createCmModal')).hide();
            alert('CM Ticket ' + data.ticket_number + ' created successfully!');
        } else {
            alert(data.message || 'Failed to create CM ticket');
        }
    })
    .catch(() => alert('An error occurred'))
    .finally(() => { btn.disabled = false; btn.innerHTML = '<i class="fas fa-plus me-1"></i> Create CM Ticket'; });
}
</script>
@endpush
@endsection
