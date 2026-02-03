@extends('layouts.user')

@push('styles')
<style>
    .date-card {
        border: 2px solid #dee2e6;
        border-radius: 8px;
        margin-bottom: 1.5rem;
    }
    .date-card-header {
        background: linear-gradient(135deg, #2c3e50, #3498db);
        color: white;
        padding: 12px 15px;
        border-radius: 6px 6px 0 0;
    }
    .cleaning-group-card {
        border: 1px solid #dee2e6;
        border-radius: 6px;
        margin: 10px;
    }
    .cleaning-group-header {
        background: linear-gradient(135deg, #1e3c72, #2a5298);
        color: white;
        padding: 10px 15px;
        border-radius: 5px 5px 0 0;
    }
    .pm-table th { background: #f8f9fa; font-weight: 600; vertical-align: middle; }
    .pm-table td { vertical-align: middle; }
    .progress-card { border-left: 4px solid; }
    .progress-card.completed { border-left-color: #28a745; }
    .progress-card.pending { border-left-color: #6c757d; }
    .progress-card.in-progress { border-left-color: #ffc107; }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">
                <i class="fas fa-calendar-check text-primary me-2"></i>
                {{ $schedule->title ?: 'PM Schedule' }}
            </h4>
            <p class="text-muted mb-0">
                <i class="fas fa-calendar me-1"></i>
                {{ $schedule->scheduled_month->format('F Y') }}
                <span class="badge bg-{{ $schedule->status_badge }} ms-2">{{ ucfirst($schedule->status) }}</span>
            </p>
        </div>
        <a href="{{ route('user.preventive-maintenance.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back
        </a>
    </div>

    <!-- Statistics Cards -->
    @php $stats = $schedule->task_stats; @endphp
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card progress-card pending">
                <div class="card-body text-center">
                    <h3 class="mb-0">{{ $stats['total'] }}</h3>
                    <small class="text-muted">Total Tasks</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card progress-card completed">
                <div class="card-body text-center">
                    <h3 class="mb-0 text-success">{{ $stats['completed'] }}</h3>
                    <small class="text-muted">Completed</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card progress-card in-progress">
                <div class="card-body text-center">
                    <h3 class="mb-0 text-warning">{{ $stats['in_progress'] }}</h3>
                    <small class="text-muted">In Progress</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body text-center">
                    <h3 class="mb-0">{{ $stats['progress'] }}%</h3>
                    <div class="progress mt-2" style="height: 8px;">
                        <div class="progress-bar bg-success" style="width: {{ $stats['progress'] }}%"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Date Filter -->
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="fas fa-calendar-alt me-2"></i>Schedule Dates</h5>
            <div class="input-group input-group-sm" style="width: auto;">
                <span class="input-group-text bg-light"><i class="fas fa-search"></i></span>
                <input type="date" id="filterDateInput" class="form-control form-control-sm" style="width: 160px;" oninput="filterDates()" min="{{ $schedule->scheduled_month->startOfMonth()->format('Y-m-d') }}" max="{{ $schedule->scheduled_month->endOfMonth()->format('Y-m-d') }}">
                <button class="btn btn-outline-secondary" onclick="clearDateFilter()" id="clearFilterBtn" style="display:none;" title="Clear filter">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
    </div>

    <!-- Dates with their structure -->
    @foreach($schedule->scheduleDates as $scheduleDate)
        <div class="date-card" data-date="{{ $scheduleDate->schedule_date->format('Y-m-d') }}">
            <div class="date-card-header d-flex justify-content-between align-items-center">
                <div>
                    <i class="fas fa-calendar-day me-2"></i>
                    <strong>{{ $scheduleDate->schedule_date->format('l, d F Y') }}</strong>
                    <span class="badge bg-light text-dark ms-2">{{ $scheduleDate->cleaningGroups->count() }} Cleaning Groups</span>
                </div>
            </div>
            <div class="p-3">
                @foreach($scheduleDate->cleaningGroups as $cleaningGroup)
                    <div class="cleaning-group-card">
                        <div class="cleaning-group-header">
                            <i class="fas fa-broom me-2"></i>
                            <strong>{{ $cleaningGroup->name }}</strong>
                            <span class="badge bg-light text-dark ms-2">{{ $cleaningGroup->sprGroups->count() }} SPR</span>
                        </div>
                        <div class="p-2">
                            @foreach($cleaningGroup->sprGroups as $sprGroup)
                                <div class="card mb-2">
                                    <div class="card-header bg-light py-2">
                                        <i class="fas fa-folder text-primary me-2"></i>
                                        <strong>{{ $sprGroup->name }}</strong>
                                        <span class="badge bg-secondary ms-2">{{ $sprGroup->tasks->count() }} tasks</span>
                                    </div>
                                    <div class="card-body p-0">
                                        <table class="table table-sm table-hover pm-table mb-0">
                                            <thead>
                                                <tr>
                                                    <th>Task</th>
                                                    <th width="100">Frequency</th>
                                                    <th width="150">Equipment</th>
                                                    <th width="120">Shift</th>
                                                    <th width="120">Status</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($sprGroup->tasks as $task)
                                                    <tr>
                                                        <td>
                                                            {{ $task->task_name }}
                                                            @if($task->task_description)
                                                                <br><small class="text-muted">{{ $task->task_description }}</small>
                                                            @endif
                                                        </td>
                                                        <td><span class="badge bg-light text-dark border">{{ $task->frequency_label }}</span></td>
                                                        <td>
                                                            @if($task->equipment_type)
                                                                <span class="badge bg-light text-dark">{{ $task->equipment_type }}</span>
                                                            @else
                                                                <span class="text-muted">-</span>
                                                            @endif
                                                        </td>
                                                        <td>
                                                            @if($task->assigned_shift_id)
                                                                <span class="badge bg-primary">{{ $task->assigned_shift_name }}</span>
                                                            @else
                                                                <span class="text-muted">-</span>
                                                            @endif
                                                        </td>
                                                        <td>
                                                            <select class="form-select form-select-sm" onchange="updateStatus({{ $task->id }}, this.value)" style="width: 120px;">
                                                                <option value="pending" {{ $task->status == 'pending' ? 'selected' : '' }}>Pending</option>
                                                                <option value="in_progress" {{ $task->status == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                                                                <option value="completed" {{ $task->status == 'completed' ? 'selected' : '' }}>Completed</option>
                                                            </select>
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="5" class="text-center text-muted py-3">No tasks yet</td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            @endforeach

                            @if($cleaningGroup->sprGroups->count() == 0)
                                <div class="text-center text-muted py-3">No SPR groups yet.</div>
                            @endif
                        </div>
                    </div>
                @endforeach

                @if($scheduleDate->standaloneTasks->count() > 0)
                    <div class="card mb-2">
                        <div class="card-header bg-light py-2">
                            <i class="fas fa-clipboard-list text-secondary me-2"></i>
                            <strong>Standalone Tasks</strong>
                            <span class="badge bg-secondary ms-2">{{ $scheduleDate->standaloneTasks->count() }} tasks</span>
                        </div>
                        <div class="card-body p-0">
                            <table class="table table-sm table-hover pm-table mb-0">
                                <thead>
                                    <tr>
                                        <th>Task</th>
                                        <th width="100">Frequency</th>
                                        <th width="150">Equipment</th>
                                        <th width="120">Shift</th>
                                        <th width="120">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($scheduleDate->standaloneTasks as $task)
                                        <tr>
                                            <td>
                                                {{ $task->task_name }}
                                                @if($task->task_description)
                                                    <br><small class="text-muted">{{ $task->task_description }}</small>
                                                @endif
                                            </td>
                                            <td><span class="badge bg-light text-dark border">{{ $task->frequency_label }}</span></td>
                                            <td>
                                                @if($task->equipment_type)
                                                    <span class="badge bg-light text-dark">{{ $task->equipment_type }}</span>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($task->assigned_shift_id)
                                                    <span class="badge bg-primary">{{ $task->assigned_shift_name }}</span>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                <select class="form-select form-select-sm" onchange="updateStatus({{ $task->id }}, this.value)" style="width: 120px;">
                                                    <option value="pending" {{ $task->status == 'pending' ? 'selected' : '' }}>Pending</option>
                                                    <option value="in_progress" {{ $task->status == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                                                    <option value="completed" {{ $task->status == 'completed' ? 'selected' : '' }}>Completed</option>
                                                </select>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif

                @if($scheduleDate->cleaningGroups->count() == 0 && $scheduleDate->standaloneTasks->count() == 0)
                    <div class="text-center text-muted py-3">No tasks for this date.</div>
                @endif
            </div>
        </div>
    @endforeach

    @if($schedule->scheduleDates->count() == 0)
        <div class="card">
            <div class="card-body text-center py-4">
                <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                <p class="text-muted">No schedule dates yet.</p>
            </div>
        </div>
    @endif
</div>

@push('scripts')
<script>
const csrfToken = '{{ csrf_token() }}';

function filterDates() {
    const filterValue = document.getElementById('filterDateInput').value;
    const clearBtn = document.getElementById('clearFilterBtn');
    const dateCards = document.querySelectorAll('.date-card');

    clearBtn.style.display = filterValue ? 'inline-block' : 'none';

    dateCards.forEach(card => {
        card.style.display = (!filterValue || card.dataset.date === filterValue) ? '' : 'none';
    });
}

function clearDateFilter() {
    document.getElementById('filterDateInput').value = '';
    filterDates();
}

function updateStatus(taskId, status) {
    fetch(`/user/preventive-maintenance/task/${taskId}/status`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
        body: JSON.stringify({ status: status })
    })
    .then(response => response.json())
    .then(data => {
        if (!data.success) {
            alert('Failed to update status');
            location.reload();
        }
    })
    .catch(() => { alert('An error occurred'); location.reload(); });
}
</script>
@endpush
@endsection
