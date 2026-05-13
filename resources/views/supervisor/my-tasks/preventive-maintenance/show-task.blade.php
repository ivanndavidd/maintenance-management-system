@extends('layouts.admin')

@section('page-title', 'PM Task Detail')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">
                <i class="fas fa-tasks text-primary me-2"></i>
                {{ $task->task_name }}
            </h4>
            <p class="text-muted mb-0">
                <i class="fas fa-calendar me-1"></i>
                {{ \Carbon\Carbon::parse($task->task_date)->format('l, d F Y') }}
                @if($task->assigned_shift_id)
                    @php $shiftColors = [1 => 'primary', 2 => 'info', 3 => 'success']; @endphp
                    <span class="badge bg-{{ $shiftColors[$task->assigned_shift_id] ?? 'secondary' }} ms-2">
                        Shift {{ $task->assigned_shift_id }}
                    </span>
                @endif
            </p>
        </div>
        <a href="{{ route('supervisor.my-tasks.preventive-maintenance') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i><span class="btn-text"> Back</span>
        </a>
    </div>

    <div class="row">
        <!-- Task Detail -->
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Task Detail</h5>
                </div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-4">Task Name</dt>
                        <dd class="col-sm-8">{{ $task->task_name }}</dd>

                        @if($task->task_description)
                        <dt class="col-sm-4">Description</dt>
                        <dd class="col-sm-8">{{ $task->task_description }}</dd>
                        @endif

                        <dt class="col-sm-4">Date</dt>
                        <dd class="col-sm-8">{{ \Carbon\Carbon::parse($task->task_date)->format('d F Y') }}</dd>

                        @if($task->equipment_type)
                        <dt class="col-sm-4">Equipment Type</dt>
                        <dd class="col-sm-8"><span class="badge bg-light text-dark border">{{ $task->equipment_type }}</span></dd>
                        @endif

                        <dt class="col-sm-4">Status</dt>
                        <dd class="col-sm-8">
                            <span class="badge bg-{{ $task->status_badge }}" id="statusBadge">
                                {{ ucfirst(str_replace('_', ' ', $task->status)) }}
                            </span>
                        </dd>

                        @if($task->is_recurring || $task->parent_task_id)
                        <dt class="col-sm-4">Recurring</dt>
                        <dd class="col-sm-8"><i class="fas fa-sync-alt text-muted me-1"></i> Yes</dd>
                        @endif
                    </dl>
                </div>
            </div>

            <!-- Report Section -->
            @php $latestReport = $task->latestReport; @endphp
            @if($latestReport)
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-file-alt me-2"></i>Submitted Report</h5>
                    <span class="badge {{ $latestReport->getStatusBadgeClass() }}">{{ $latestReport->getStatusLabel() }}</span>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="fw-semibold d-block mb-1">Detail Kegiatan</label>
                        <div class="border rounded p-3 bg-light">{{ $latestReport->description }}</div>
                    </div>

                    @if($latestReport->photos && count($latestReport->photos) > 0)
                    <div class="mb-3">
                        <label class="fw-semibold d-block mb-1">Foto ({{ count($latestReport->photos) }})</label>
                        <div class="d-flex flex-wrap gap-2">
                            @foreach($latestReport->photos as $photo)
                            <a href="{{ Storage::url($photo['path']) }}" target="_blank">
                                <img src="{{ Storage::url($photo['path']) }}" style="width:100px;height:100px;object-fit:cover;" class="rounded border">
                            </a>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    @if($latestReport->furtherRepairAssets->count() > 0)
                    <div class="mb-3">
                        <label class="fw-semibold d-block mb-1">Sparepart</label>
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Equipment ID</th>
                                        <th>Asset Name</th>
                                        <th>Notes</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($latestReport->furtherRepairAssets as $asset)
                                    <tr>
                                        <td>{{ $asset->equipment_id ?? '-' }}</td>
                                        <td>{{ $asset->asset_name }}</td>
                                        <td>{{ $asset->pivot->notes ?? '-' }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @endif

                    @if($latestReport->admin_comments)
                    <div class="alert alert-warning">
                        <strong><i class="fas fa-comment me-1"></i> Review Comments:</strong><br>
                        {{ $latestReport->admin_comments }}
                    </div>
                    @endif

                    <small class="text-muted">
                        Submitted by {{ $latestReport->submitter->name ?? '-' }}
                        on {{ $latestReport->submitted_at?->format('d M Y, H:i') }}
                    </small>
                    @if($latestReport->timing_label)
                        <span class="badge {{ $latestReport->timing_badge_class }} ms-2">
                            <i class="fas fa-clock me-1"></i>{{ $latestReport->timing_label }}
                        </span>
                    @endif
                </div>
            </div>
            @endif

            <!-- Submit / Update Report -->
            @if($task->status === 'in_progress' && !$latestReport)
            <div class="alert alert-warning">
                <i class="fas fa-lock me-1"></i>
                <strong>Task sedang dikerjakan oleh orang lain.</strong> Laporan tidak bisa disubmit saat task dalam status <em>In Progress</em>.
            </div>
            @endif
            @if(!$latestReport && $task->status !== 'in_progress' || ($latestReport && in_array($latestReport->status, ['revision_needed', 'sparepart_rejected'])))
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-plus-circle me-2"></i>
                        {{ $latestReport ? 'Resubmit Report' : 'Submit Report' }}
                    </h5>
                </div>
                <div class="card-body">
                    <form id="reportForm_showspv" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Detail Kegiatan <span class="text-danger">*</span></label>
                            <textarea name="description" class="form-control" rows="5" required placeholder="Jelaskan kegiatan PM yang telah dilakukan..."></textarea>
                        </div>

                        @include('supervisor.my-tasks.preventive-maintenance.partials.pm-sparepart-usage', ['formId' => 'showspv'])

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Foto Dokumentasi</label>
                            <input type="file" name="photos[]" class="form-control" multiple accept="image/*" id="reportPhotos">
                            <small class="text-muted">Max 5MB per file.</small>
                            <div id="photoPreview" class="d-flex flex-wrap gap-2 mt-2"></div>
                        </div>

                        <button type="submit" class="btn btn-primary" id="submitBtn">
            <i class="fas fa-paper-plane me-1"></i><span class="btn-text"> Submit Report</span>
                        </button>
                    </form>
                </div>
            </div>
            @endif
        </div>

        <!-- Sidebar: Update Status -->
        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-edit me-2"></i>Update Status</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        @if($task->status !== 'in_progress' && $task->status !== 'completed')
                        <button class="btn btn-outline-warning" onclick="updateStatus('in_progress')">
            <i class="fas fa-play me-1"></i><span class="btn-text"> Mark In Progress</span>
                        </button>
                        @endif
                        @if($task->status !== 'completed')
                        <button class="btn btn-outline-success" onclick="updateStatus('completed')">
            <i class="fas fa-check me-1"></i><span class="btn-text"> Mark Completed</span>
                        </button>
                        @endif
                        @if($task->status !== 'pending')
                        <button class="btn btn-outline-secondary" onclick="updateStatus('pending')">
            <i class="fas fa-undo me-1"></i><span class="btn-text"> Reset to Pending</span>
                        </button>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Activity Log -->
            @if($task->logs->count() > 0)
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-history me-2"></i>Activity Log</h5>
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                        @foreach($task->logs->take(10) as $log)
                        <li class="list-group-item py-2 px-3">
                            <small class="d-block fw-semibold">{{ ucfirst(str_replace('_', ' ', $log->action)) }}</small>
                            <small class="text-muted">{{ $log->user->name ?? '-' }} &bull; {{ $log->created_at->format('d M Y, H:i') }}</small>
                            @if($log->notes)
                            <small class="text-muted d-block">{{ $log->notes }}</small>
                            @endif
                        </li>
                        @endforeach
                    </ul>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script>
const csrfToken = '{{ csrf_token() }}';
const taskId = {{ $task->id }};
const updateStatusUrl = '/supervisor/my-tasks/preventive-maintenance/task/' + taskId + '/status';
const storeReportUrl = '/supervisor/my-tasks/preventive-maintenance/task/' + taskId + '/report';

document.getElementById('reportPhotos')?.addEventListener('change', function() {
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

function updateStatus(status) {
    fetch(updateStatusUrl, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
        body: JSON.stringify({ status })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) location.reload();
        else alert(data.message || 'Failed to update status');
    })
    .catch(() => alert('An error occurred'));
}

const reportForm = document.getElementById('reportForm_showspv');
if (reportForm) {
    reportForm.addEventListener('submit', function(e) {
        e.preventDefault();
        const btn = document.getElementById('submitBtn');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Submitting...';

        const formData = new FormData(this);

        fetch(storeReportUrl, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
            body: formData
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert(data.message || 'Failed to submit report');
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-paper-plane me-1"></i> Submit Report';
            }
        })
        .catch(() => {
            alert('An error occurred');
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-paper-plane me-1"></i> Submit Report';
        });
    });
}
</script>
@endpush
@endsection
